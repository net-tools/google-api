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
 *                  yes                 |           set             |       conflict !          | contact modified on both sides ; that's an error
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
     * Log a message with contact resourceName identifier
     *
	 * @param \Psr\Log\LoggerInterface $log Log object
     * @param string $level Error level (from `\Psr\Log\LogLevel`)
     * @param string $msg Message string to log
     * @param string $resourceName Resource name to log
     */
    protected function logWithResourceName(\Psr\Log\LoggerInterface $log, $level, $msg, $resourceName)
    {
        $log->$level($msg . " : [{resourceName}]", ['resourceName' => $resourceName]); 
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
		return $this->createRequest($c, self::REQUEST_CONFLICT);
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
	 * @param \Google\Service\PeopleService\Person $c Contact from Google to create a request for
	 * @return bool
	 **/
	protected function testContactPendingConfirmRequest(\Google\Service\PeopleService\Person $c, array &$confirmRequests)
	{
		foreach ( $confirmRequests as $req )
			if ( $req->contact->resourceName == $c->resourceName )
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


					// get etag and update flag from client
					$contact_etag_updflag = $this->_clientInterface->getContactInfoClientside($c);

					// if contact not found clientside
					if ( $contact_etag_updflag === FALSE )
						throw new SyncException('Google orphan', $c);


					// checking both sides with md5 hashes ; if equals, no meaningful data modified, skipping contact, no matter what is the client-side update flag
					if ( $this->_clientInterface->md5Googleside($c) == $this->_clientInterface->md5Clientside($c->resourceName) )
					{
						$this->logWithContact($log, 'info', 'Contact skipped, no update detected', $c);
						continue;
					}
					

					
					// if update proved with md5 mismatch on Google AND also on client side we have a conflict we can't handle, unless confirm mode on
					if ( $contact_etag_updflag->clientsideUpdateFlag )
						if ( !$confirm )
							throw new SyncException('Conflict, updates on both sides', $c);
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
							throw new SyncException("Clientside sync error '$st'", $c);
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
					// convert Google\Exception to SyncException, get message from API and throw a new exception
					throw new SyncException(ExceptionHelper::getMessageFor($e), $c);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a SyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $c);
				}
			}
			catch (SyncException $e)
			{
				$error = true;
				
				if ( $e instanceof HaltSyncException )
				{
					$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
					break; // stop sync
				}
				else
				{
					$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
					continue; // continue loop and sync
				}
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
	 * @param bool $confirm Set it to true to confirm google->clientside updates
	 * @param array $confirmRequests Array of requests to confirm
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncToGoogle(\Psr\Log\LoggerInterface $log, $confirm, array &$confirmRequests)
	{
		// no error at the beginning of sync process
		$count = 0;
        $error = false;
		
		
		// log
		$log->info('-- Begin SYNC clientside -> Google');
		
		
		// getting a list of clientside contacts to update google-side
    	$feed = $this->_clientInterface->getUpdatedContactsClientside($this->_service);
	
        
        // creating a batch
        foreach ( $feed as $c )
        {
			try
			{
				$count++;


				// if no resourceName, this is a new Contact
				if ( !$c->contact->resourceName )
					$newc = $this->_service->people->createContact($c->contact, ['personFields' => $this->personFields]);

				// update clientside -> google, unless confirm request queued for this contact
				else
					if ( $confirm && $this->testContactPendingConfirmRequest($c->contact, $confirmRequests) )
						// ignoring conflict being handled in deferred requests
						continue;
					else
						$newc = $this->_service->people->updateContact($c->contact->resourceName, $c->contact, 
																[
																	'updatePersonFields'	=> $this->personFields, 
																	'personFields'			=> $this->personFields
																]);


				// acknowledgment client side
				$st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($newc, !$c->contact->resourceName);


				// if we arrive here, we have a clientside update sent successfuly to Google
				if ( $st === TRUE )
					$this->logWithContact($log, 'info', $c->contact->resourceName?'UPDATE':'CREATE', $newc);
				else
					// if error during clientside acknowledgment, log as warning
					throw new SyncException("Clientside acknowledgment sync error '$st'", $newc);
			}
            catch (\Exception $e)
            {
                $error = true;
                $this->logWithContact($log, 'error', ($e instanceof \Google\Exception)?ExceptionHelper::getMessageFor($e):$e->getMessage(), $c->contact);
            }
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
        
        foreach ( $feed as $resname )
        {
            $count++;
			
			try
			{
				// deleting to google
            	$this->_service->people->deleteContact($resname);
				
				// acknowledging on clientside
				$st = $this->_clientInterface->acknowledgeContactDeletedGoogleside($resname);
								
                // if we arrive here, we have a clientside deletion sent successfuly to Google
                if ( $st === TRUE )
                    $this->logWithResourceName($log, 'info', 'Deleted to Google from client-side', $resname);
                else
                    // if error during clientside acknowledgment, log as warning
                    throw new \Exception("Clientside acknowledgment deletion error '$st' for '$resname'");
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
					// get update flag from client
					$contact_etag_updflag = $this->_clientInterface->getContactInfoClientside($c);

					// if contact not found clientside, we have nothing to do !
					if ( $contact_etag_updflag === FALSE )
					{
						// log google orphan but it's not an error since both sides don't have this contact any more
						$this->logWithContact($log, 'notice', 'Deleted Google orphan', $c);
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
							throw new SyncException("Clientside deletion error '$st'", $c);
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
					// convert Google\Exception to SyncException, get message from API and throw a new exception
					throw new SyncException(ExceptionHelper::getMessageFor($e), $c);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a SyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $c);
				}
			}
			catch (SyncException $e)
			{
				$error = true;
				
				if ( $e instanceof HaltSyncException )
				{
					$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
					break; // stop sync
				}
				else
				{
					$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
					continue; // continue loop and sync
				}
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
								throw new SyncException("Clientside sync error '$st'", $req->contact);
							
							break;
							
						
						// if delete request
						case self::REQUEST_DELETE:
							// Google deletion to handle client-side
							$st = $this->_clientInterface->deleteContactClientside($req->contact);
							if ( $st === TRUE )
								$this->logWithContact($log, 'info', 'Synced (DELETE deferred request on client-side)', $req->contact);
							else
								// if error during clientside update, log as warning
								throw new SyncException("Clientside deletion error '$st'", $req->contact);
							
							break;
							
							
						// unkown request
						default:
							throw new HaltSyncException("Unknown deferred request kind '$req->kind'", $req->contact);
					}
				}
						
				// catch service error and continue to next contact
				catch (\Google\Exception $e)
				{
					// convert Google\Exception to SyncException, get message from API and throw a new exception
					throw new SyncException(ExceptionHelper::getMessageFor($e), $c);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a SyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $c);
				}
			}
			catch (SyncException $e)
			{
				$error = true;
				
				if ( $e instanceof HaltSyncException )
				{
					$this->logWithContact($log, 'critical', $e->getMessage(), $e->getContact());
					break; // stop sync
				}
				else
				{
					$this->logWithContact($log, 'error', $e->getMessage(), $e->getContact());
					continue; // continue loop and sync
				}
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
			$noerr = $this->syncToGoogle($log, $confirm, $confirmRequests);
		
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
		
		
		// setting sync parameters
		foreach ( $params as $k=>$v )
			if ( property_exists($this, $k) )
				$this->$k = $v;
    }
}

?>