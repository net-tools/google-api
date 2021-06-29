<?php
/**
 * Manager
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;




use \Nettools\GoogleAPI\ServiceWrappers\PeopleService;
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;



/**
 * Helper class to handle contacts sync between Google contacts and your own contacts list
 *
 * See what kind of update we have to do :
 *
 *   present in feed with syncToken 	|  clientside update flag 	|       sync direction     	|              remarks             
 * -------------------------------------|---------------------------|---------------------------|----------------------------------------
 *                  yes                 |           not set         |  google -> clientside		| google side update to send to client
 *                  yes                 |           set             |       conflict !          | contact modified on both sides ; have to deal with conflict
 *                  no                  |           set             |  clientside -> google     | clientside update to send to Google 
 *                  no                  |           not set         |        no sync            | nothing to do
 *
 * Deletions are done on any side, even if the contact has been modified on the other side (deletion is considered high-priority).
 */
class Manager
{
    /**
     * PeopleService object 
     *
     * @var \Nettools\GoogleAPI\ServiceWrappers\PeopleService 
     */
    protected $_service = NULL;
	
	/** 
     * Interface object to get on-the-fly data from sync client 
     *
     * @var ClientInterface 
     */
	protected $_clientInterface = NULL;

	
	
	/** 
	 * Cache for Google objects
	 * 
	 * @var \Nettools\Core\Containers\Cache;
	 */
	protected $_gCache = NULL;
	
	
	/** 
     * Google user whose contacts must be synced 
     *
     * @var string 
     */
	public $user = 'people/me';
	
	/** 
     * Google group whose contacts must be synced 
     *
     * @var string 
     */
	public $group = NULL;
	
	/**
	 * Comma separated string values of Person fields to read
	 * 
	 * @var string
	 */
	public $personFields = '';
	
	
	
	/** 
     * Sync contacts from client repository to Google 
     *
     * @var int 
     */
	const ONE_WAY_TO_GOOGLE = 1;

	/**
     * Sync contacts from Google to client repository 
     * 
     * @var int 
     */
	const ONE_WAY_FROM_GOOGLE = 2;
	
	/** 
     * Sync contacts both ways 
     *
     * @var int 
     */
	const TWO_WAY = 3;
    
    /** 
     * Delete contacts on Googleside that have been removed from client repository 
     *
     * @var int 
     */
    const ONE_WAY_DELETE_TO_GOOGLE = 4;
    
    /** 
     * Delete contacts on client repository that have been removed on Google side 
     *
     * @var int 
     */
    const ONE_WAY_DELETE_FROM_GOOGLE = 8;
    
    /**  
     * Delete contacts on both sides 
     * 
     * @var int 
     */
    const TWO_WAY_DELETE = 12;
	
	
	const REQUEST_UPDATE = 'update';
	const REQUEST_CONFLICT = 'conflict';
	const REQUEST_DELETE = 'delete';
	
	
	
	
    /**
     * Magic method to handle read access to properties
     *
     * @param string $k Property name
     * @return mixed
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if property `$k` does not exist in `$this`
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
        else
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' does not exist in class '" . __CLASS__ . "'.");
    }
    
    
    
    /**
     * Provide log context
     * 
     * The implementation provides default values (familyName, givenName, id) but the `ClientInterface` object may add/customize them
     *
     * @param \Google\Service\PeopleService\Person $c
     * @return string[] Returns an associative array with log context values
     */
    protected function getLogContext(\Google\Service\PeopleService\Person $c)
    {
        return $this->_clientInterface->getLogContext($c, array(
                                                                'familyName'    => $c->getNames()[0]->familyName,
                                                                'givenName'     => $c->getNames()[0]->givenName,
                                                                'id'            => $c->resourceName
                                                            ));
    }
    
    
    
    /** 
     * Provides default placeholders for log context
     *
     * @return string Return default log context placeholders, such as {familyName} or {id}
     */
    protected function addDefaultLogContextPlaceholders()
    {
        return ' : [{familyName} {givenName} {id}]';
    }
    
    
    
    /**
     * Log a message with contact context placeholders
     *
	 * @param \Psr\Log\LoggerInterface $log Log object
     * @param string $level Error level (from `\Psr\Log\LogLevel`)
     * @param string $msg Message string to log
     * @param \Google\Service\PeopleService\Person $c Contact as context
     */
    protected function logWithContact(\Psr\Log\LoggerInterface $log, $level, $msg, \Google\Service\PeopleService\Person $c)
    {
        $log->$level($msg . $this->addDefaultLogContextPlaceholders(), $this->getLogContext($c)); 
    }
	
	
	
    /**
     * Log a message with contact resourceNamea and text identifier
     *
	 * @param \Psr\Log\LoggerInterface $log Log object
     * @param string $level Error level (from `\Psr\Log\LogLevel`)
     * @param string $msg Message string to log
     * @param string $resourceName Resource name to log
	 * @param string $text Contact label
     */
    protected function logWithResourceNameLabel(\Psr\Log\LoggerInterface $log, $level, $msg, $resourceName, $text)
    {
        $log->$level($msg . " : [{label} ({resourceName})]", ['resourceName' => $resourceName, 'label' => $text]); 
    }
	
	
	
	/**
	 * Create an update request
	 *
	 * @param \Google\Service\PeopleService\Person $c Contact from Google to create an update request for
	 * @return object Returns an object litteral with kind, contact properties
	 */
	protected function createUpdateRequest(\Google\Service\PeopleService\Person $c)
	{
		return $this->createRequest($c, self::REQUEST_UPDATE);
	}
	
	
	
	/**
	 * Create a conflict request
	 *
	 * @param \Google\Service\PeopleService\Person $c Contact from Google to create a conflict request for
	 * @return object Returns an object litteral with kind, contact properties
	 */
	protected function createConflictRequest(\Google\Service\PeopleService\Person $c)
	{
		$v = $this->createRequest($c, self::REQUEST_CONFLICT);
		$v->preserve = [];
		
		return $v;
	}
	
	
	
	/**
	 * Create a delete request
	 *
	 * @param \Google\Service\PeopleService\Person $c Contact from Google to create a delete request for
	 * @return object Returns an object litteral with kind, contact properties
	 */
	protected function createDeleteRequest(\Google\Service\PeopleService\Person $c)
	{
		return $this->createRequest($c, self::REQUEST_DELETE);
	}
	
	
	
	/**
	 * Create an update, conflict or delete request (depending on $kind argument)
	 *
	 * @param \Google\Service\PeopleService\Person $c Contact from Google to create a request for
	 * @param string $kind 'update', 'conflict' or 'delete' string
	 * @return object Returns an object litteral with kind, contact properties
	 */
	protected function createRequest(\Google\Service\PeopleService\Person $c, $kind)
	{
		return (object)[
				'kind'		=> $kind,
				'contact'	=> $c
			];
	}
	
	
	
	/**
	 * Test if a contact is being queued in an update/delete/conflict request
	 * 
	 * @param string $resourceName Contact resourceName to look for
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool
	 **/
	protected function testContactPendingConfirmRequest($resourceName, array &$confirmRequests)
	{
		foreach ( $confirmRequests as $req )
			if ( $req->contact->resourceName == $resourceName )
				return true;
		
		return false;
	}
	
	
	
	/**
	 * Sync contacts from Google to clientside
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param string $lastSyncToken Sync token
	 * @param bool $confirm Set it to true to confirm google->clientside updates
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncFromGoogle(\Psr\Log\LoggerInterface $log, $lastSyncToken, $confirm, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin SYNC Google -> clientside');
		
		
		// preparing request parameters
		$optparams = ['syncToken' => $lastSyncToken, 'personFields' => $this->personFields];
		if ( $this->group )
			$feed = $this->_service->getGroupContacts($this->user, $this->group, $optparams);
		else
			$feed = $this->_service->getAllContacts($this->user, $optparams);
		
		
	
		foreach ( $feed->connections as $c )
		{
			try
			{
				try
				{
					$count++;
					
					
					// cache contact in case another sync (clientside -> google) needs it
					$this->_gCache->register($c->resourceName, $c);


					// get update flag from client to detect conflicts or contact not found
					$contact_data = $this->_clientInterface->getSyncDataForClientsideContact($c);

					// if contact not found clientside
					if ( $contact_data === NULL )
						throw new NotBlockingSyncException('Google orphan', $c);


					// checking both sides with md5 hashes ; if equals, no meaningful data modified, skipping contact, no matter what is the client-side update flag
					if ( $this->_clientInterface->md5Googleside($c) == $contact_data->md5 )
					{
						$this->logWithContact($log, 'info', 'Contact skipped, no update detected', $c);
						continue;
					}
					

					
					// if update proved with md5 mismatch on Google AND also on client side we have a conflict we can't handle, unless confirm mode on
					if ( $contact_data->updated )
						if ( !$confirm )
							throw new NotBlockingSyncException('Conflict, updates on both sides', $c);
						else
						{
							$this->logWithContact($log, 'info', 'Deferred CONFLICT sync request', $c);
							$confirmRequests[] = $this->createConflictRequest($c);
							continue;
						}
				
					
					
					// if we arrive here, we have a Google update to send to clientside ; no conflict detected ; contact exists clientside
					if ( !$confirm )
					{
						$st = $this->_clientInterface->updateContactClientside($c);
						if ( $st === TRUE )
							$this->logWithContact($log, 'info', 'Synced', $c);
						else
							throw new NotBlockingSyncException("Clientside sync error '$st'", $c);
					}
					else
					{
						$this->logWithContact($log, 'info', 'Deferred UPDATE sync request', $c);
						$confirmRequests[] = $this->createUpdateRequest($c);
						continue;
					}
				}
						
				// catch service error and continue to next contact
				catch (\Google\Exception $e)
				{
					// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
					throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $c);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $c);
				}
			}
			catch (NotBlockingSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
				continue; // continue loop and sync
			}
			catch(HaltSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
				break; // stop sync
			}
		}
		
		
		// log number of contacts processed
		$log->info("-- End SYNC Google -> clientside : $count contacts processed");

		return !$error;
	}
	
	
	
	/**
	 * Sync contacts from clientside to Google
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncToGoogle(\Psr\Log\LoggerInterface $log, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$count = 0;
        $error = false;
		
		
		// log
		$log->info('-- Begin SYNC clientside -> Google');
		
		
		try
		{
			// getting a list of clientside contacts to update google-side (resourceName, text, md5) object litterals
			$feed = $this->_clientInterface->getUpdatedContactsClientside();

			foreach ( $feed as $cobj )
			{
				try
				{
					$count++;


					// if contact already in a pending confirm request (delete/update/conflict), ignoring this sync
					if ( $this->testContactPendingConfirmRequest($cobj->resourceName, $confirmRequests) )
					{
						// ignoring conflict being handled in deferred requests
						$this->logWithResourceNameLabel($log, 'info', 'Skipping client-side to Google sync, contact being processed in deferred confirm request', $cobj->resourceName, $cobj->text);
						continue;
					}



					try
					{
						// getting google-side contact through cache (if read previously during google->clientside sync) or directly from api
						$c = $this->_gCache->get($cobj->resourceName);
						if ( $c === FALSE )
							$c = $this->_service->people->get($cobj->resourceName, ['personFields' => $this->personFields]);
					}
					catch (\Google\Exception $e)
					{
						// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
						// creating a dummy Person object sync SyncException requires it ; currently, we don't have a Person object
						throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), new \Google\Service\PeopleService\Person(['names'=>['familyName'=>$cobj->text]]));
					}


					// now that we have a Person object, try/catch exception with contact arg
					try
					{
						// if no update required
						if ( $this->_clientInterface->md5Googleside($c) == $cobj->md5 )
						{
							$this->logWithContact($log, 'info', 'No update required', $c);

							// acknowledgment client side for an update operation (may be used to unset update flag)
							$st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($c);
							if ( is_string($st) )
								throw new NotBlockingSyncException("Clientside with no update needed acknowledgment sync error '$st'", $c);

							continue;	
						}



						// merging google contact with updates from clientside
						$st = $this->_clientInterface->updateContactObjectFromClientside($c);
						if ( is_string($st) )
							throw new NotBlockingSyncException('Error during contact updates merging from client-side : ' . $st, $c);


						// updating google-side
						$newc = $this->_service->people->updateContact($c->resourceName, $c, 
																[
																	'updatePersonFields'	=> $this->personFields, 
																	'personFields'			=> $this->personFields
																]);
						// updating cache
						$this->_gCache->unregister($c->resourceName);
						$this->_gCache->register($c->resourceName, $newc);


						// acknowledgment client side for an update operation
						$st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($newc);


						// if we arrive here, we have a clientside update sent successfuly to Google
						if ( $st === TRUE )
							$this->logWithContact($log, 'info', 'UPDATE', $newc);
						else
							// if error during clientside acknowledgment, log as warning
							throw new NotBlockingSyncException("Clientside acknowledgment sync error '$st'", $newc);
					}
					// catch service error and continue to next contact
					catch (\Google\Exception $e)
					{
						// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
						throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $c);
					}
					catch (\Exception $e)
					{
						// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
						// to have contact context and throw a new exception halting the sync
						throw new HaltSyncException($e->getMessage(), $c);
					}
				}
				catch (NotBlockingSyncException $e)
				{
					$error = true;
					$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
					continue;
				}
			}




			// getting a list of clientside created contacts, getting object litterals array (clientId, contact)
			$feed = $this->_clientInterface->getCreatedContactsClientside();
			foreach ( $feed as $cnobj )
			{
				try
				{
					$count++;

					try
					{
						// creating contact
						$newc = $this->_service->people->createContact($cnobj->contact, ['personFields' => $this->personFields]);

						// updating cache
						$this->_gCache->register($c->resourceName, $newc);
						
						// acknowledgment client side for a create operation
						$st = $this->_clientInterface->acknowledgeContactCreatedGoogleside($cnobj->clientId, $newc);


						// if we arrive here, we have a clientside update sent successfuly to Google
						if ( $st === TRUE )
							$this->logWithContact($log, 'info', 'CREATE', $newc);
						else
							// if error during clientside acknowledgment, log as warning
							throw new NotBlockingSyncException("Clientside acknowledgment sync error '$st'", $newc);
					}
					// catch service error and continue to next contact
					catch (\Google\Exception $e)
					{
						// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
						throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $cnobj->contact);
					}
					catch (\Exception $e)
					{
						// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
						// to have contact context and throw a new exception halting the sync
						throw new HaltSyncException($e->getMessage(), $cnobj->contact);
					}			
				}
				catch (NotBlockingSyncException $e)
				{
					$error = true;
					
					$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
					continue;
				}				
			}
		}
		catch(HaltSyncException $e)
		{
			$error = true;
			
			$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
		}
		
		
		// log number of contacts processed
		$log->info("-- End SYNC clientside -> Google : $count contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts to Google (contacts removed from clientside)
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteToGoogle(\Psr\Log\LoggerInterface $log)
	{
		// no error at the beginning of sync process
		$count = 0;
		$error = false;
		
		// log
		$log->info('-- Begin DELETE clientside -> Google');
		
		
		// getting a list of clientside contacts id to deleted google-side
    	$feed = $this->_clientInterface->getDeletedContactsClientside();
        
        foreach ( $feed as $cobj )
        {
            $count++;
			
			try
			{
				// deleting to google
            	$this->_service->people->deleteContact($cobj->resourceName);
				
				// updating cache
				$this->_gCache->unregister($cobj->resourceName);

				
				// acknowledging on clientside
				$st = $this->_clientInterface->acknowledgeContactDeletedGoogleside($cobj);
								
                // if we arrive here, we have a clientside deletion sent successfuly to Google
                if ( $st === TRUE )
                    $this->logWithResourceNameLabel($log, 'info', 'Deleted to Google from client-side', $cobj->resourceName, $cobj->text);
                else
                    // if error during clientside acknowledgment, log as warning
                    throw new \Exception("Clientside acknowledgment deletion error '$st' for '$cobj->text ($cobj->resourceName)'");
			}
            catch (\Exception $e)
            {
                $error = true;
                $log->error(($e instanceof \Google\Exception)?ExceptionHelper::getMessageFor($e):$e->getMessage());
            }
        }
        
        	
		
		// log number of contacts processed
		$log->info("-- End DELETE clientside -> Google : $count contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts from Google to clientside
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param string $lastSyncToken Last sync token
	 * @param bool $confirm Set it to true to confirm google->clientside deletions
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteFromGoogle(\Psr\Log\LoggerInterface $log, $lastSyncToken, $confirm, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin DELETE Google -> clientside');
		
		

		// preparing request parameters
		$optparams = ['syncToken' => $lastSyncToken, 'personFields' => $this->personFields];
		
		if ( $this->group )
			$feed = $this->_service->getGroupContacts($this->user, $this->group, $optparams);
		else
			$feed = $this->_service->getAllContacts($this->user, $optparams);
		
		
		
		// filter and handle deletions
		foreach ( $feed->connections as $c )
		{
			try
			{
				// we ignore contacts not deleted
				if ( !$c->getMetadata() )
					continue;
				
				if ( !$c->getMetadata()->deleted )
					continue;
				
				$count++;
				
				
				
				try
				{
					// get update flag from client, just to know if it exists or not (exist : we get 0 or 1, doesn't exist, we get NULL)
					$contact_updflag = $this->_clientInterface->getSyncRequiredForClientsideContact($c);

					// if contact not found clientside, we have nothing to do !
					if ( $contact_updflag === NULL )
					{
						// log google orphan but it's not an error since both sides don't have this contact any more
						$this->logWithContact($log, 'notice', 'Deleted Google contact already deleted client-side', $c);
						continue;
					}

							

					// if we arrive here, we have a Google deletion to send to clientside
					if ( !$confirm )
					{
						$st = $this->_clientInterface->deleteContactClientside($c);
						if ( $st === TRUE )
							$this->logWithContact($log, 'info', 'Deleted from Google to client-side', $c);
						else
							// if error during clientside update, log as warning
							throw new NotBlockingSyncException("Clientside deletion error '$st'", $c);
					}
					else
					{
						$this->logWithContact($log, 'info', 'Deferred DELETE sync request', $c);
						$confirmRequests[] = $this->createDeleteRequest($c);
					}					
				}
				// catch service error and continue to next contact
				catch (\Google\Exception $e)
				{
					// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
					throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $c);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $c);
				}
			}
			catch (HaltSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
				break; // stop sync
			}
			catch (NotBlockingSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
				continue; // continue loop and sync
			}
		}
		
		
		// log number of contacts processed
		$log->info("-- End DELETE Google -> clientside : $count contacts processed");

		return !$error;
	}
	
	
	
	/**
	 * Execute requests that have been confirmed by user
	 *
	 * During sync, if $confirm argument is set to true, the sync method returns an array of update or delete requests to be confirmed ; to cancel an update/deletion, remove it from the array
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 */
	public function executeRequests(\Psr\Log\LoggerInterface $log, array $requests)
	{
		$count = 0;
		$error = false;
		$log->info('-- Begin SYNC Google -> clientside (deferred sync requests)');

		
		foreach ( $requests as $req )
		{
			// update
			try
			{
				try
				{
					$count++;
					
					
					// handle request
					switch ( $req->kind )
					{
						// if update request
						case self::REQUEST_UPDATE:
							// update contact client-side
							$st = $this->_clientInterface->updateContactClientside($req->contact);
							if ( $st === TRUE )
								$this->logWithContact($log, 'info', 'Synced (UPDATE deferred request on client-side)', $req->contact);
							else
								throw new NotBlockingSyncException("Clientside sync error '$st'", $req->contact);
							
							break;
							
						
						// if conflict request (an update Googleside->clientside followed by a partial update of clientside values to Googleside)
						case self::REQUEST_CONFLICT:
							
							// get an associative array of client-side values to preserve
							$values = $this->_clientInterface->conflictHandlingBackupContactValuesClientside($req->contact, $req->preserve);
							if ( is_string($values) )
								throw new NotBlockingSyncException("Clientside conflict handling error '$st'", $req->contact);
								
							
							// update contact client-side
							$st = $this->_clientInterface->updateContactClientside($req->contact);
							if ( $st === TRUE )
							{
								// restore values that have been overwritten during conflict update with old ones backuped before syncing googleside -> clientside
								$st = $this->_clientInterface->conflictHandlingRestoreContactValuesClientside($req->contact, $values);

								if ( $st === TRUE )
									$this->logWithContact($log, 'info', 'Synced (CONFLICT deferred request handling needs another sync to achieve updates merging process)', $req->contact);
								else
									throw new NotBlockingSyncException("Clientside conflict handling error '$st'", $req->contact);
							}
							else
								throw new NotBlockingSyncException("Clientside sync error '$st'", $req->contact);
							
							break;
							
						
						// if delete request
						case self::REQUEST_DELETE:
							// Google deletion to handle client-side
							$st = $this->_clientInterface->deleteContactClientside($req->contact);
							if ( $st === TRUE )
								$this->logWithContact($log, 'info', 'Synced (DELETE deferred request on client-side)', $req->contact);
							else
								// if error during clientside update, log as warning
								throw new NotBlockingSyncException("Clientside deletion error '$st'", $req->contact);
							
							break;
							
							
						// unkown request
						default:
							throw new NotBlockingSyncException("Unknown deferred request kind '$req->kind'", $req->contact);
					}
				}
						
				// catch service error and continue to next contact
				catch (\Google\Exception $e)
				{
					// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
					throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $req->contact);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $req->contact);
				}
			}
			catch (HaltSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
				break; // stop sync
			}
			catch (NotBlockingSyncException $e)
			{
				$error = true;
				
				$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
				continue; // continue loop and sync
			}
			
		}
		
		
		// log number of contacts processed
		$log->info("-- End SYNC Google -> clientside (deferred sync requests) : $count contacts updated");

		return !$error;
	}
	
	
	
	/**
	 * Sync contacts, according to `$kind` argument
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param string $lastSyncToken Last sync token
	 * @param int $kind Type of sync (may combine values ONE_WAY_FROM_GOOGLE, ONE_WAY_TO_GOOGLE, ONE_WAY_DELETE_FROM_GOOGLE, ONE_WAY_DELETE_TO_GOOGLE)
	 * @param bool $confirm Set to true to confirm Google->ClientSide requests (updates and deletions)
	 * @return bool If $confirm = false, returns True if success, false if an error occured ; if $confirm = true, returns an array of update requests, false if an error occured
	 */
	public function sync(\Psr\Log\LoggerInterface $log, $lastSyncToken, $kind, $confirm = false)
	{
		$noerr = true;
		$confirmRequests = [];
		
		
		// if syncing from Google
		if ( $kind & self::ONE_WAY_FROM_GOOGLE )
			$noerr = $this->syncFromGoogle($log, $lastSyncToken, $confirm, $confirmRequests);
		
		// if syncing to Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_TO_GOOGLE) )
			$noerr = $this->syncToGoogle($log, $confirmRequests);
		
		// if deleting contacts clientside from Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_DELETE_FROM_GOOGLE) )
			$noerr = $this->deleteFromGoogle($log, $lastSyncToken, $confirm, $confirmRequests);

		// if deleting contacts to Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_DELETE_TO_GOOGLE) )
			$noerr = $this->deleteToGoogle($log);
		
        
		if ( $noerr && $confirm )
			return $confirmRequests;

		return $noerr;
	}
    
    
	
    /**
     * Constructor of contacts sync manager
     * 
	 * @param \Nettools\GoogleAPI\ServiceWrappers\PeopleService $service People service wrapper
	 * @param ClientInterface $clientInterface Interface to exchange information with the client
	 * @param mixed[] $params Associative array of parameters to set to corresponding object properties
     */
    public function __construct(PeopleService $service, ClientInterface $clientInterface, array $params = [])
    {
        $this->_service = $service;
		$this->_clientInterface = $clientInterface;
		$this->_gCache = new \Nettools\Core\Containers\Cache();
		
		
		// setting sync parameters
		foreach ( $params as $k=>$v )
			if ( property_exists($this, $k) )
				$this->$k = $v;
    }
}

?>