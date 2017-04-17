<?php
/**
 * Manager
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\ContactsSyncManager;




use \Nettools\GoogleAPI\Services\Contacts_Service;
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;




/**
 * Helper class to handle contacts sync between Google contacts and your own contacts list
 *
 * See what kind of update we have to do :
 *
 *   etag google = etag clientside ? 	|  clientside update flag 	|       sync direction     	|              remarks             
 * -------------------------------------|---------------------------|---------------------------|----------------------------------------
 *             		no					|			not set			|  google -> clientside		| google side update to send to client
 *             		yes					|			not set			|        no sync       		| recently updated contact google-side after a client side update ; no sync needed
 *             		yes					|			set				|  clientside -> google		| clientside update to send to Google 
 *             		no					|			set				|  		conflict !  		| contact modified on both sides ; that's an error
 *
 */
class Manager
{
    /**
     * Google client object 
     *
     * @var \Google_Client 
     */
    protected $_client = NULL;
	
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
	public $user = 'default';
	
	/** 
     * Google group whose contacts must be synced 
     *
     * @var string 
     */
	public $group = NULL;
	
	
	
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
	 * Sync contacts from Google to clientside
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param int $lastSyncTime Timestamp of last sync
	 * @return bool Returns True if success, false if an non-critical error occured
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if a critical error occured (sync process is halted as soon as the error occurs)
	 */
	protected function syncFromGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log, $lastSyncTime)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// preparing request parameters
		$optparams = ['updated-min'=>$lastSyncTime];
		if ( $this->group )
			$optparams['group'] = $group;
		
		
		// log
		$log->info('-- Begin SYNC Google -> clientside');
		
		
		// getting a list of google contacts updated sinc last sync
    	$feed = $service->contacts->getList($this->user, $optparams);
	
		foreach ( $feed as $c )
		{
			try
			{
				$count++;
				
				
				// get etag and update flag from client
				$contact_etag_updflag = $this->_clientInterface->getContactInfoClientside($c);
				
				// if contact not found clientside
				if ( $contact_etag_updflag == FALSE )
				{
					// log error and set a flag for the error (since we don't stop the sync)
					$log->error('Google orphan', $this->_clientInterface->getContext($c));
					$error = true;
					continue;
				}
				
				
				// if etag google = etag client side, no update on Google
				if ( $contact_etag_updflag->etag == $c->etag )
				{
					// if no update on client side, contact is already synced (we have it in the feed because he has been synced during the last update)
					if ( !$contact_etag_updflag->clientsideUpdateFlag )
						$log->info('Already synced', $this->_clientInterface->getContext($c));
					else
						// if update flag set, there are updates to send to Google
						$log->info('Contact updated clientside', $this->_clientInterface->getContext($c));
					
					
					// in both case, we only sync contact from Google TO client so we are not interested by sending updates from clientside to Google
					continue;
				}
				else
					// if update on Google AND also on client side we have a conflict we can't handle
					if ( $contact_etag_updflag->clientsideUpdateFlag )
					{
						$log->error('Conflict : updates on both sides', $this->_clientInterface->getContext($c));
						$error = true;
						continue;
					}
				

				
				// if we arrive here, we have a Google update to send to clientside ; no conflict detected ; contact exists clientside
				$st = $this->_clientInterface->updateContactClientside($c);
				if ( $st === TRUE )
					$log->info('Synced', $this->_clientInterface->getContext($c));
				else
				{
					// if error during clientside update, log and halt sync
					$log->critical("Clientside sync error : '$st'", $this->_clientInterface->getContext($c));
					throw new \Nettools\GoogleAPI\Exceptions\Exception('Clientside sync error');
				}
			
				
			}
			// catch service error and continue to next contact
			catch (Google_Exception $e)
			{
				// log error and set a flag for the error (since we don't stop the sync)
				$log->error(ExceptionHelper::getMessageFor($e), $this->_clientInterface->getContext($c));
				$error = true;
				continue;
			}
		}
		
		
		// log number of contacts processed
		$log->info("-- End SYNC Google -> clientside : '$count' contacts processed");

		return !$error;
	}
	
	
	
	/**
	 * Sync contacts from clientside to Google
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @return bool Returns True if success, false if an non-critical error occured
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if a critical error occured (sync process is halted as soon as the error occurs)
	 */
	protected function syncToGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin SYNC clientside -> Google');
		
		
		// getting a list of clientside contacts to update google-side
    	$feed = $this->_clientInterface->getUpdatedContactsClientside($service);
	
		foreach ( $feed as $c )
		{
			try
			{
				$count++;
				
				
                // if no edit link, this is a new Contact
                if ( $c->linkRel('edit') == FALSE )
                    $contact = $service->contacts->create($c, $this->user);
                else
                    $contact = $service->contacts->update($c, $c->etag);
			
                
                // notify clientside
                $st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($contact);

                
                // if we arrive here, we have a clientside update sent successfuly to Google
                if ( $st === TRUE )
                    $log->info('Synced', $this->_clientInterface->getContext($c));
                else
                {
                    // if error during clientside acknowledgment, log and halt sync
                    $log->critical("Clientside acknowledgment sync error : '$st'", $this->_clientInterface->getContext($c));
                    throw new \Nettools\GoogleAPI\Exceptions\Exception('Clientside acknowledgment sync error');
                }
				
			}
			// catch service error and continue to next contact
			catch (Google_Exception $e)
			{
				// log error and set a flag for the error (since we don't stop the sync)
				$log->error(ExceptionHelper::getMessageFor($e), $this->_clientInterface->getContext($c));
				$error = true;
				continue;
			}
		}
		
		
		// log number of contacts processed
		$log->info("-- End SYNC clientside -> Google : '$count' contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts to Google (contacts removed from clientside)
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @return bool Returns True if success, false if an non-critical error occured
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if a critical error occured (sync process is halted as soon as the error occurs)
	 */
	protected function deleteToGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin DELETE clientside -> Google');
		
		
		// getting a list of clientside contacts id to deleted google-side
    	$feed = $this->_clientInterface->getDeletedContactsClientside();
	
		foreach ( $feed as $c )
		{
			try
			{
				$count++;
				
				
                // deleting contact
                $service->contacts->delete($c);
			
                
                // notify clientside
                $st = $this->_clientInterface->acknowledgeContactDeletedGoogleside($c);

                
                // if we arrive here, we have a clientside deletion sent successfuly to Google
                if ( $st === TRUE )
                    $log->info('Deleted', ["[$c]"]);
                else
                {
                    // if error during clientside acknowledgment, log and halt sync
                    $log->critical("Clientside acknowledgment deletion error : '$st'", ["[$c]"]);
                    throw new \Nettools\GoogleAPI\Exceptions\Exception('Clientside acknowledgment deletion error');
                }
				
			}
			// catch service error and continue to next contact
			catch (Google_Exception $e)
			{
				// log error and set a flag for the error (since we don't stop the sync)
				$log->error(ExceptionHelper::getMessageFor($e), $this->_clientInterface->getContext($c));
				$error = true;
				continue;
			}
		}
		
		
		// log number of contacts processed
		$log->info("-- End DELETE clientside -> Google : '$count' contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Sync contacts, according to `$kind` property
	 *
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param int $lastSyncTime Timestamp of last sync
	 * @return bool Returns True if success, false if an non-critical error occured
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if a critical error occured
	 */
	public function sync(\Psr\Log\LoggerInterface $log, $lastSyncTime)
	{
		// create service
		$service = new Contacts_Service($this->_client);
		$noerr = true;
		
		
		// if syncing from Google
		if ( $this->kind & self::ONE_WAY_FROM_GOOGLE )
			$noerr = $this->syncFromGoogle($service, $log, $lastSyncTime);
		
		// if syncing to Google (and no error previously)
		if ( $noerr && ($this->kind & self::ONE_WAY_TO_GOOGLE) )
			$noerr = $this->syncToGoogle($service, $log);
		
		// if deleting contacts clientside from Google (and no error previously)
		/*if ( $noerr && ($this->kind & self::ONE_WAY_DELETE_FROM_GOOGLE) )
			$noerr = $this->deleteFromGoogle($service, $log);*/

		// if deleting contacts to Google (and no error previously)
		if ( $noerr && ($this->kind & self::ONE_WAY_DELETE_TO_GOOGLE) )
			$noerr = $this->deleteToGoogle($service, $log);
		
        
		return $noerr;
	}
    
    
	
    /**
     * Constructor of contacts sync manager
     * 
     * @param \Google_Client $client Google client to send requests with
	 * @param ClientInterface $clientInterface Interface to exchange information with the client
	 * @param mixed[] $params Associative array of parameters to set to corresponding object properties
     */
    public function __construct(\Google_Client $client, ClientInterface $clientInterface, array $params = [])
    {
        $this->_client = $client;
		$this->_clientInterface = $clientInterface;
		
		
		// setting sync parameters
		foreach ( $params as $k=>$v )
			if ( property_exists($this, $k) )
				$this->$k = $v;
    }
}

?>