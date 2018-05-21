<?php
/**
 * Manager
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\ContactsSyncManager;




use \Nettools\GoogleAPI\Services\Contacts_Service;
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;
use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Services\Contacts\Batch;




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
 * Deletions are done on any side, even if the contact has been modified on the other side (deletion is considered high-priority).
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
     * Provide log context
     * 
     * The implementation provides default values (familyName, givenName, id) but the `ClientInterface` object may add/customize them
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c
     * @return string[] Returns an associative array with log context values
     */
    protected function getLogContext(Contact $c)
    {
        return $this->_clientInterface->getLogContext($c, array(
                                                                'familyName'    => $c->familyName,
                                                                'givenName'     => $c->givenName,
                                                                'id'            => $c->id
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
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c Contact as context
     */
    protected function logWithContact(\Psr\Log\LoggerInterface $log, $level, $msg, Contact $c)
    {
        $log->$level($msg . $this->addDefaultLogContextPlaceholders(), $this->getLogContext($c)); 
    }
	
	
	
	/**
	 * Sync contacts from Google to clientside
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param int $lastSyncTime Unix timestamp of last sync
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncFromGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log, $lastSyncTime)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// preparing request parameters
		$optparams = ['updated-min'=>date('c', $lastSyncTime)];
		if ( $this->group )
			$optparams['group'] = $this->group;
		
		
		// log
		$log->info('-- Begin SYNC Google -> clientside');
		
		
		// getting a list of google contacts updated since last sync
    	$feed = $service->contacts->getList($this->user, $optparams);
	
		foreach ( $feed as $c )
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


					// if etag google = etag client side, no update on Google
					if ( $contact_etag_updflag->etag == $c->etag )
					{
						// if no update on client side, contact is already synced (we have it in the feed because he has been synced during the last update)
						if ( !$contact_etag_updflag->clientsideUpdateFlag )
                            $this->logWithContact($log, 'notice', 'Already synced', $c);
						else
							// if update flag set, there are updates to send to Google
							$this->logWithContact($log, 'notice', 'Contact updated clientside, to update with clientside -> Google sync', $c);


						// in both case, we only sync contact from Google TO client so we are not interested by sending updates from clientside to Google
						continue;
					}
					else
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
				catch (\Google_Exception $e)
				{
					// convert Google_Exception to SyncException, get message from API and throw a new exception
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
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function syncToGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log)
	{
		// no error at the beginning of sync process
		$count = 0;
        $cache = [];
        $error = false;
		
		
		// log
		$log->info('-- Begin SYNC clientside -> Google');
		
		
		// getting a list of clientside contacts to update google-side
    	$feed = $this->_clientInterface->getUpdatedContactsClientside($service);
	
        
        // creating a batch
        $batch = $service->contacts->createBatch($this->user);
        foreach ( $feed as $c )
        {
            $count++;
            

            // if no edit link, this is a new Contact
            if ( $c->contact->linkRel('edit') == FALSE )
            {
                // caching Contact object creation with the $count as id
                $cache[$count] = $c->contact;                
                $service->contacts->batchCreate($batch, 'CREATE-' . $count, $c->contact, $this->user);
            }
            else
                // detect etag mismatch
                if ( $c->contact->etag != $c->etag )
                {
					$this->logWithContact($log, 'error', 'Conflict, updates on both sides', $c->contact);
                    $error = true;
                }
                else
                {
                    // caching Contact object with it's ID as key ; will be used later when reading batch responses
                    $cache[$c->contact->id] = $c->contact;
                    
                    // etag are the same (google etag and client-side last known etag), we can update google-side
                    $service->contacts->batchUpdate($batch, 'UPDATE-' . $c->contact->id, $c->contact, false);
                }
        }
        
        
        
        // execute batch
        $rbatch = $batch->execute();
        
        
        // handle batch responses
        foreach ( $rbatch as $bid => $bresp )
        {
            // recreate ID of Contact object through batch ID and fetch the Contact object from cache
            try
            {
                $c = $cache[substr(strstr($bid, '-'),1)];
                if ( !$c )
                    throw new \Exception('Error fetching contact from cache with ID \'' . substr(strstr($bid, '-'),1) . '\'');
            
            
                // notify clientside
                if ( !$bresp->success() )
                    throw new SyncException("Google API error during contact update : " . $bresp->reason, $c);
                
				$st = $this->_clientInterface->acknowledgeContactUpdatedGoogleside($bresp->entry, $c->linkRel('edit') == FALSE);

                
                // if we arrive here, we have a clientside update sent successfuly to Google
                if ( $st === TRUE )
                    $this->logWithContact($log, 'info', $bresp->operationType, $c);
                else
                    // if error during clientside acknowledgment, log as warning
                    throw new SyncException("Clientside acknowledgment sync error '$st'", $c);
            }
            catch (\Exception $e)
            {
                $error = true;
                if ( $c )
                    $this->logWithContact($log, 'error', ($e instanceof \Google_Exception)?ExceptionHelper::getMessageFor($e):$e->getMessage(), $c);
                else
                    $log->error(($e instanceof \Google_Exception)?ExceptionHelper::getMessageFor($e):$e->getMessage());
            }
        }
			        
                
		
		// log number of contacts processed
		$log->info("-- End SYNC clientside -> Google : $count contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts to Google (contacts removed from clientside)
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteToGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log)
	{
		// no error at the beginning of sync process
		$count = 0;
		
		
		// log
		$log->info('-- Begin DELETE clientside -> Google');
		
		// create a dummy contact we will use to notify the clientside about deletion
		$dummyxml = simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
	<id>contactId</id>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
</entry>
XML
			);
		
		
		// getting a list of clientside contacts id to deleted google-side
    	$feed = $this->_clientInterface->getDeletedContactsClientside();
        
        // creating a batch
        $batch = $service->contacts->createBatch($this->user);
        foreach ( $feed as $c )
        {
            $count++;
            $service->contacts->batchDelete($batch, 'DELETE-' . $c, $c);
        }
        
        
        // execute batch
        $rbatch = $batch->execute();
        $error = false;
        
        
        // handle batch responses
        foreach ( $rbatch as $bid => $bresp )
        {
            try
            {
                // reconstruct editlink of contact deleted
                $c = substr(strstr($bid, '-'),1); 
                    
                // creating dummy contact
                foreach ( $dummyxml->link as $lnk )
                    $lnk->attributes()->href = $c;

                $dummyxml->id = str_replace(array('https', 'full'), array('http', 'base'), $c);	// convert dummy id from edit link (no https and full=>base in url)
                $dummyc = Contact::fromFeed($dummyxml);

                // notify clientside
                if ( !$bresp->success() )
                    throw new SyncException("Google API error during contact deletion : " . $bresp->reason, $dummyc);

                $st = $this->_clientInterface->acknowledgeContactDeletedGoogleside($dummyc);

                
                // if we arrive here, we have a clientside deletion sent successfuly to Google
                if ( $st === TRUE )
                    $this->logWithContact($log, 'info', 'Deleted', $dummyc);
                else
                    // if error during clientside acknowledgment, log as warning
                    throw new SyncException("Clientside acknowledgment deletion error '$st'", $dummyc);
            }
            catch (\Exception $e)
            {
                $error = true;
                $this->logWithContact($log, 'error', ($e instanceof \Google_Exception)?ExceptionHelper::getMessageFor($e):$e->getMessage(), $dummyc);
            }
        }
			
		
		
		// log number of contacts processed
		$log->info("-- End DELETE clientside -> Google : $count contacts processed");

		return !$error;
    }
	
	
	
	/**
	 * Delete contacts from Google to clientside
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service 
	 * @param \Psr\Log\LoggerInterface $log Log object ; if none desired, set it to an instance of \Psr\Log\NullLogger class.
	 * @param int $lastSyncTime Unix timestamp of last sync
	 * @return bool Returns True if success, false if an error occured
	 */
	protected function deleteFromGoogle(Contacts_Service $service, \Psr\Log\LoggerInterface $log, $lastSyncTime)
	{
		// no error at the beginning of sync process
		$error = false;
		$count = 0;
		
		
		// log
		$log->info('-- Begin DELETE Google -> clientside');
		
		

		// preparing request parameters
		$optparams = ['updated-min'=>date('c', $lastSyncTime)];
		$optparams['showdeleted'] = 'true';
		if ( $this->group )
			$optparams['group'] = $this->group;
		
		
		
		// create a dummy contact we will use to notify the clientside about deletion
		$dummyxml = simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
	<id>contactId</id>
	<gd:deleted />
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
</entry>
XML
			);
		

		
		// getting a list of google contacts updated AND deleted since last sync
    	$feed = $service->contacts->getList($this->user, $optparams);
	
		foreach ( $feed as $c )
		{
			try
			{
				// we ignore contacts not deleted
				if ( !$c->deleted )
					continue;
				
				$count++;
				
				
				// creating dummy contact (id and links property are read-only)
				foreach ( $dummyxml->link as $lnk )
					// convert edit link to dummy id (http=>https and base=>full in url)
					$lnk->attributes()->href = str_replace(array('http', 'base'), array('https', 'full'), $c->id);

				$dummyxml->id = $c->id;
				$dummyc = Contact::fromFeed($dummyxml);
			
				
				try
				{
					// get etag and update flag from client
					$contact_etag_updflag = $this->_clientInterface->getContactInfoClientside($dummyc);

					// if contact not found clientside, we have nothing to do !
					if ( $contact_etag_updflag === FALSE )
					{
						// log google orphan but it's not an error since both sides don't have this contact any more
						$this->logWithContact($log, 'notice', 'Deleted Google orphan', $dummyc);
						continue;
					}


					// if we arrive here, we have a Google deletion to send to clientside
					$st = $this->_clientInterface->deleteContactClientside($dummyc);
					if ( $st === TRUE )
						$this->logWithContact($log, 'info', 'Deleted', $dummyc);
					else
						// if error during clientside update, log as warning
						throw new SyncException("Clientside deletion error '$st'", $dummyc);
				}
				// catch service error and continue to next contact
				catch (\Google_Exception $e)
				{
					// convert Google_Exception to SyncException, get message from API and throw a new exception
					throw new SyncException(ExceptionHelper::getMessageFor($e), $dummyc);
				}
				catch (\Exception $e)
				{
					// convert unexcepted Exception (thrown most probably from clientside) to a SyncException, 
					// to have contact context and throw a new exception halting the sync
					throw new HaltSyncException($e->getMessage(), $dummyc);
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
	 * @param int $lastSyncTime Unix timestamp of last sync
	 * @return bool Returns True if success, false if an error occured
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
		if ( $noerr && ($this->kind & self::ONE_WAY_DELETE_FROM_GOOGLE) )
			$noerr = $this->deleteFromGoogle($service, $log, $lastSyncTime);

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
	 * @param int $kind Kind of sync (see constants from class)
	 * @param mixed[] $params Associative array of parameters to set to corresponding object properties
     */
    public function __construct(\Google_Client $client, ClientInterface $clientInterface, $kind, array $params = [])
    {
        $this->_client = $client;
		$this->_clientInterface = $clientInterface;
		$this->kind = $kind;
		
		
		// setting sync parameters
		foreach ( $params as $k=>$v )
			if ( property_exists($this, $k) )
				$this->$k = $v;
    }
}

?>