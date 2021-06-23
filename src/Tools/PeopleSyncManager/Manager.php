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
     * Kind of sync : one-way from Google, one-way to Google, two-way 
     *
     * @var int 
     */
	public $kind = 0;
	
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
	 * Sync contacts from Google to clientside
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param string $lastSyncToken Sync token
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncFromGoogle(\Psr\Log\LoggerInterface $log, $lastSyncToken)
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


					// if update on Google AND also on client side we have a conflict we can't handle
					if ( $contact_etag_updflag->clientsideUpdateFlag )
						throw new SyncException('Conflict, updates on both sides', $c);


					// if we arrive here, we have a Google update to send to clientside ; no conflict detected ; contact exists clientside
					$st = $this->_clientInterface->updateContactClientside($c);
					if ( $st === TRUE )
						$this->logWithContact($log, 'info', 'Synced', $c);
					else
						throw new SyncException("Clientside sync error '$st'", $c);
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
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncToGoogle(\Psr\Log\LoggerInterface $log)
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

				// update clientside -> google
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
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteFromGoogle(\Psr\Log\LoggerInterface $log, $lastSyncToken)
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
		
		
		
		// filtrer et traiter les suppressions
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
					$st = $this->_clientInterface->deleteContactClientside($c);
					if ( $st === TRUE )
						$this->logWithContact($log, 'info', 'Deleted from Google to client-side', $c);
					else
						// if error during clientside update, log as warning
						throw new SyncException("Clientside deletion error '$st'", $c);
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
	 * Sync contacts, according to `$kind` property
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param string $lastSyncToken Last sync token
	 * @return bool returns false if an error occured
	 */
	public function sync(\Psr\Log\LoggerInterface $log, $lastSyncToken)
	{
		$noerr = true;
		
		
		// if syncing from Google
		if ( $this->kind & self::ONE_WAY_FROM_GOOGLE )
			$noerr = $this->syncFromGoogle($log, $lastSyncToken);
		
		// if syncing to Google (and no error previously)
		if ( $noerr && ($this->kind & self::ONE_WAY_TO_GOOGLE) )
			$noerr = $this->syncToGoogle($log);
		
		// if deleting contacts clientside from Google (and no error previously)
		if ( $noerr && ($this->kind & self::ONE_WAY_DELETE_FROM_GOOGLE) )
			$noerr = $this->deleteFromGoogle($log, $lastSyncToken);

		// if deleting contacts to Google (and no error previously)
		if ( $noerr && ($this->kind & self::ONE_WAY_DELETE_TO_GOOGLE) )
			$noerr = $this->deleteToGoogle($log);
		
        
		return $noerr;
	}
    
    
	
    /**
     * Constructor of contacts sync manager
     * 
	 * @param \Nettools\GoogleAPI\ServiceWrappers\PeopleService $service People service wrapper
	 * @param ClientInterface $clientInterface Interface to exchange information with the client
	 * @param int $kind Kind of sync (see constants from class)
	 * @param mixed[] $params Associative array of parameters to set to corresponding object properties
     */
    public function __construct(PeopleService $service, ClientInterface $clientInterface, $kind, array $params = [])
    {
        $this->_service = $service;
		$this->_clientInterface = $clientInterface;
		$this->kind = $kind;
		
		
		// setting sync parameters
		foreach ( $params as $k=>$v )
			if ( property_exists($this, $k) )
				$this->$k = $v;
    }
}

?>