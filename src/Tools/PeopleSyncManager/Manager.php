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
	const REQUEST_INVERT = 'invert';
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
     * The implementation provides default values (familyName, givenName, resourceName) but the `ClientInterface` object may add/customize them
     *
     * @param \Google\Service\PeopleService\Person $c
     * @return string[] Returns an associative array with log context values
     */
    protected function getLogContext(\Google\Service\PeopleService\Person $c)
    {
        return $this->_clientInterface->getLogContext($c, array(
                                                                'familyName'    => $c->getNames()[0]->familyName,
                                                                'givenName'     => $c->getNames()[0]->givenName,
                                                                'resourceName'  => $c->resourceName
                                                            ));
    }
    
    
    
    /** 
     * Provides default placeholders for log context
     *
     * @return string Return default log context placeholders, such as {familyName} or {resourceName}
     */
    protected function addDefaultLogContextPlaceholders()
    {
        return ' : [{familyName} {givenName} ({resourceName})]';
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
	 * Create a dummy Person object to log or use in exception that requires a Person object
	 *
	 * @return \Google\Service\PeopleService\Person
	 */
	protected function createDummyLogPerson($resourceName, $text)
	{
		return new \Google\Service\PeopleService\Person(['resourceName' => $resourceName, 'names'=>[ ['familyName'=>$text]] ]);
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
	 * Sets new sync token for further calls ; to be called only when sync successfull
	 *
	 * @param \Psr\Log\LoggerInterface $log
	 * @return bool Returns true if no error
	 */
	protected function setNextSyncToken(\Psr\Log\LoggerInterface $log)
	{
		try
		{
			// preparing request parameters
			$optparams = ['personFields' => $this->personFields, 'requestSyncToken'	=> true];


			// read sync token from client-side ; if we have it, include it in api call
			$lastSyncToken = $this->_clientInterface->getSyncToken();
			if ( !is_null($lastSyncToken) )
				$optparams['syncToken'] = $lastSyncToken;


			try
			{
				// reading feed and ask for new synctoken
				if ( $this->group )
					$feed = $this->_service->getGroupContacts($this->user, $this->group, $optparams);
				else
					$feed = $this->_service->getAllContacts($this->user, $optparams);


				// setting synctoken client-side
				$this->_clientInterface->setSyncToken($feed->nextSyncToken);
				$log->info('Setting new sync token');
				
				// success
				return true;
			}
			catch (\Google\Exception $e)
			{
				// convert Google\Exception to Exception, get message from API and throw a new exception
				throw new \Exception(ExceptionHelper::getMessageFor($e));
			}
		}
		catch(\Throwable $e)
		{
			$log->critical("Can't set new sync token : '" . $e->getMessage() ."'");
			return false;
		}
	}
	
	
	
	/**
	 * Sync contacts from Google to clientside
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param bool $confirm Set it to true to confirm google->clientside updates
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncFromGoogle(\Psr\Log\LoggerInterface $log, $confirm, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin SYNC Google -> clientside');
		
		
		try
		{
			// read sync token
			$lastSyncToken = $this->_clientInterface->getSyncToken();
			if ( is_null($lastSyncToken) )
			{
				$log->critical('No sync token ; sync halted');
				return false;
			}



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
						// we ignore deleted contacts
						if ( $c->getMetadata() && $c->getMetadata()->deleted )
							continue;


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
								throw new NotBlockingSyncException("Clientside update error : '$st'", $c);
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
					catch (\Throwable $e)
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
		}
		catch (\Throwable $e)
		{
			// catching exceptions (most probably those raised during pre-feed loop)
			$error = true;
			
			$log->critical($e->getMessage());
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
     * @param string $logprefix String to insert before any log output (may be used during nested calls)
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncToGoogle(\Psr\Log\LoggerInterface $log, array &$confirmRequests, $logprefix = '')
	{
		// no error at the beginning of sync process
		$count = 0;
        $error = false;
		
		
		// log
		$log->info($logprefix . '-- Begin SYNC clientside -> Google');
		
		
		try
		{
			// getting a list of clientside contacts to update google-side (resourceName, text, md5) object litterals
			$feed = $this->_clientInterface->getUpdatedContactsClientside();

			foreach ( $feed as $cobj )
			{
				// create dummy log Person
				$logc = $this->createDummyLogPerson($cobj->resourceName, $cobj->text);
				
				
				try
				{
					$count++;
					

					// if contact already in a pending confirm request (delete/update/conflict), ignoring this sync
					if ( $this->testContactPendingConfirmRequest($cobj->resourceName, $confirmRequests) )
					{
						// ignoring conflict being handled in deferred requests
						$this->logWithContact($log, 'info', $logprefix . 'Skipping client-side to Google sync, contact being processed in deferred confirm request', $logc);
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
						throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $logc);
					}


					// now that we have a Person object, try/catch exception with contact arg
					try
					{
						// if no update required
						if ( $this->_clientInterface->md5Googleside($c) == $cobj->md5 )
						{
							$this->logWithContact($log, 'info', $logprefix . 'No update required', $c);

							// acknowledgment client side for an update operation (may be used to unset update flag)
							$st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($c);
							if ( is_string($st) )
								throw new NotBlockingSyncException("Clientside with no update needed acknowledgment sync error '$st'", $c);

							continue;	
						}



						// merging google contact with updates from clientside
						$st = $this->_clientInterface->updateContactObjectFromClientside($c);
						if ( is_string($st) )
							throw new NotBlockingSyncException("Error during contact updates merging from client-side '$st'", $c);


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
							$this->logWithContact($log, 'info', $logprefix . 'UPDATE', $newc);
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
					catch (\Throwable $e)
					{
						// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
						// to have contact context and throw a new exception halting the sync
						throw new HaltSyncException($e->getMessage(), $c);
					}
				}
				catch (NotBlockingSyncException $e)
				{
					$error = true;
					$this->logWithContact($log, 'error', $logprefix . $e->getMessage(), $e->getContact());
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
							$this->logWithContact($log, 'info', $logprefix . 'CREATE', $newc);
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
					catch (\Throwable $e)
					{
						// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
						// to have contact context and throw a new exception halting the sync
						throw new HaltSyncException($e->getMessage(), $cnobj->contact);
					}			
				}
				catch (NotBlockingSyncException $e)
				{
					$error = true;
					
					$this->logWithContact($log, 'error', $logprefix . $e->getMessage(), $e->getContact());
					continue;
				}				
			}
		}
		catch(HaltSyncException $e)
		{
			$error = true;
			
			$this->logWithContact($log, 'critical', $logprefix . $e->getMessage(), $e->getContact());
		}
		catch (\Throwable $e)
		{
			// catching exceptions (most probably those raised during feed getter : getUpdatedContactsClientside or getCreatedContactsClientside)
			$error = true;
			
			$log->critical($logprefix . $e->getMessage());
		}
		
		
		// log number of contacts processed
		$log->info($logprefix . "-- End SYNC clientside -> Google : $count contacts processed");

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
		
		
		
		try
		{
			// getting a list of clientside contacts id to deleted google-side
			$feed = $this->_clientInterface->getDeletedContactsClientside();

			foreach ( $feed as $cobj )
			{
				try
				{
					// create dummy log contact
					$logc = $this->createDummyLogPerson($cobj->resourceName, $cobj->text);


					try
					{
						$count++;


						// deleting to google
						$this->_service->people->deleteContact($cobj->resourceName);

						// updating cache
						$this->_gCache->unregister($cobj->resourceName);


						// acknowledging on clientside
						$st = $this->_clientInterface->acknowledgeContactDeletedGoogleside($cobj);


						// if we arrive here, we have a clientside deletion sent successfuly to Google
						if ( $st === TRUE )
							$this->logWithContact($log, 'info', 'Deleted to Google from client-side', $logc);
						else
							// if error during clientside acknowledgment, log as warning
							throw new NotBlockingSyncException("Clientside acknowledgment deletion error '$st'", $logc);
					}
					// catch service error and continue to next contact
					catch (\Google\Exception $e)
					{
						// convert Google\Exception to NotBlockingSyncException, get message from API and throw a new exception
						throw new NotBlockingSyncException(ExceptionHelper::getMessageFor($e), $logc);
					}
					catch (\Throwable $e)
					{
						// convert unexcepted Exception (thrown most probably from clientside) to a HaltSyncException, 
						// to have contact context and throw a new exception halting the sync
						throw new HaltSyncException($e->getMessage(), $logc);
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
		}
		catch (\Throwable $e)
		{
			// catching exceptions (most probably those raised during feed getter : getDeletedContactsClientside)
			$error = true;
			
			$log->critical($e->getMessage());
		}
        
        	
		
		// log number of contacts processed
		$log->info("-- End DELETE clientside -> Google : $count contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts from Google to clientside
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param bool $confirm Set it to true to confirm google->clientside deletions
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteFromGoogle(\Psr\Log\LoggerInterface $log, $confirm, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin DELETE Google -> clientside');
		
		

		try
		{
			// read sync token
			$lastSyncToken = $this->_clientInterface->getSyncToken();
			if ( is_null($lastSyncToken) )
			{
				$log->critical('No sync token ; sync halted');
				return false;
			}



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
					try
					{
						// we ignore contacts not deleted
						if ( !$c->getMetadata() )
							continue;

						if ( !$c->getMetadata()->deleted )
							continue;

						$count++;



						// get sync data from contact clientside, just to know if it exists or not (exist we get md5 and updated properties, doesn't exist, we get NULL)
						$contact_data = $this->_clientInterface->getSyncDataForClientsideContact($c);

						// if contact not found clientside, we have nothing to do !
						if ( $contact_data === NULL )
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
					catch (\Throwable $e)
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
		}
		catch (\Throwable $e)
		{
			// catching exceptions (most probably those raised during pre-feed loop)
			$error = true;
			
			$log->critical($e->getMessage());
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
	 * @param object[] Array of object litterals (kind, contact, [preserve]) describing differed requests
	 * @return bool Returns true if success, false if an error occured
	 */
	public function executeRequests(\Psr\Log\LoggerInterface $log, array $requests)
	{
		$count = 0;
		$error = false;
		$needsSyncToGoogle = false;
		
		// begin sync
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
								throw new NotBlockingSyncException("Clientside update error : '$st'", $req->contact);
							
							break;
                            
							
						
						// if invert request
						case self::REQUEST_INVERT:
							
							// asking client-side to raise the 'updated' flag for this contact, so that it will be synced client-side -> google
							$st = $this->_clientInterface->requestClientsideContactUpdate($req->contact);
							if ( $st === TRUE )
								$this->logWithContact($log, 'info', 'Synced (INVERT deferred request scheduled on client-side ; see log below)', $req->contact);
							else
								throw new NotBlockingSyncException("Clientside 'updated' flag raising error : '$st'", $req->contact);
							
							
							// trigger a sync after loop
							$needsSyncToGoogle = true;
							
							break;
                            
							
						
						// if conflict request (an update Googleside->clientside followed by a partial update of clientside values to Googleside)
						case self::REQUEST_CONFLICT:
							
							// if conflict merging both sides (some client values mustn't be overwritten by google->clientside sync), backup some values from clientside,
							// do sync google->client-side, and restore backupped values to client-side, and ask for a clientside -> google sync to achieve conflict sync
							if ( count($req->preserve) )
							{
								// get an associative array of client-side values to preserve
								$values = $this->_clientInterface->conflictHandlingBackupContactValuesClientside($req->contact, $req->preserve);
								if ( is_string($values) )
									throw new NotBlockingSyncException("Clientside conflict handling error during client-side values backup : '$st'", $req->contact);
								
							
								// update contact client-side
								$st = $this->_clientInterface->updateContactClientside($req->contact);
								if ( $st === TRUE )
								{
									// restore values that have been overwritten during conflict update with old ones backupped before syncing googleside -> clientside
									$st = $this->_clientInterface->conflictHandlingRestoreContactValuesClientside($req->contact, $values);

									if ( $st === TRUE )
									{
										// log conflict being handled
										$this->logWithContact($log, 'info', 'Conflict being handled (merging values), another sync is called automatically to achieve conflict handling (see log below)', $req->contact);
										$needsSyncToGoogle = true;
									}
									else
										throw new NotBlockingSyncException("Clientside conflict handling error during client-side backupped values restore : '$st'", $req->contact);
								}
								else
									throw new NotBlockingSyncException("Clientside update error : '$st'", $req->contact);
							}
							
							
							// if conflict but only one side values are kept (clientside values are all overwritten by google-side values), this is a
							// classic update google->clientside sync
							else
							{
								// update contact client-side ; no further sync is needed (no backupped values restored)
								$st = $this->_clientInterface->updateContactClientside($req->contact);
								if ( $st === TRUE )
								{
									// unsetting client-side 'updated' flag ; as a further clientside->google sync is not needed, we have to remove the flag here
									// (if the clientside->google sync was required, the flag would have been removed through acknowledgeContactUpdatedGoogleside call)
									$st = $this->_clientInterface->cancelClientsideContactUpdate($req->contact);
									if ( $st === TRUE )
										// log conflict being handled
										$this->logWithContact($log, 'info', 'Conflict handled for contact (no merging), no further sync needed', $req->contact);
									else
										throw new NotBlockingSyncException("Clientside canceling 'updated' flag error : '$st'", $req->contact);
								}
								else
									throw new NotBlockingSyncException("Clientside update error : '$st'", $req->contact);
							}
							
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
				catch (\Throwable $e)
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
		
		
		
		// if another sync is needed to achieve conflict merging
		if( !$error && $needsSyncToGoogle )
		{
			$log->info("-- Begin auto-triggered second SYNC clientside -> Google (conflict handling)");
			
			
			// call sync client-side -> google to achieve merging (in case of preserved values from clientside)
			// if error occurs, they will be handled through log during call ; syncToGoogle return false
			$dummyreqs = [];
			$error = !$this->syncToGoogle($log, $dummyreqs, '....');
			

			$log->info("-- End auto-triggered second SYNC clientside -> Google (conflict handling)");
		}

		
		// if no error, setting new sync token
		if ( !$error )
			return $this->setNextSyncToken($log);
		else
			return false;
	}
	
	
	
	/**
	 * Sync contacts, according to `$kind` argument
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param int $kind Type of sync (may combine values ONE_WAY_FROM_GOOGLE, ONE_WAY_TO_GOOGLE, ONE_WAY_DELETE_FROM_GOOGLE, ONE_WAY_DELETE_TO_GOOGLE)
	 * @param bool $confirm Set to true to confirm Google->ClientSide requests (updates and deletions)
	 * @return bool If $confirm = false, returns True if success, false if an error occured ; if $confirm = true, returns an array of update requests, false if an error occured
	 */
	public function sync(\Psr\Log\LoggerInterface $log, $kind, $confirm = false)
	{
		$noerr = true;
		$confirmRequests = [];
		
		
		// if syncing from Google
		if ( $kind & self::ONE_WAY_FROM_GOOGLE )
			$noerr = $this->syncFromGoogle($log, $confirm, $confirmRequests);
		
		// if syncing to Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_TO_GOOGLE) )
			$noerr = $this->syncToGoogle($log, $confirmRequests);
		
		// if deleting contacts clientside from Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_DELETE_FROM_GOOGLE) )
			$noerr = $this->deleteFromGoogle($log, $confirm, $confirmRequests);

		// if deleting contacts to Google (and no error previously)
		if ( $noerr && ($kind & self::ONE_WAY_DELETE_TO_GOOGLE) )
			$noerr = $this->deleteToGoogle($log);
		
		
		// if no error
		if ( $noerr )
		{
			// if confirm mode off or on but no request to confirm, setting new sync token
			if ( (!$confirm) || (count($confirmRequests)==0) )
				return $this->setNextSyncToken($log);

		
			// if confirm mode on and some requests to confirm, returning them (all other values for $confirm and count($confirmRequests)  are handled in previous line)
			return $confirmRequests;
		}
		else
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