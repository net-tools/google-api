<?php
/**
 * Batch
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Batch helper
 */
class Batch 
{
	const BATCH_NS 		= 'http://schemas.google.com/gdata/batch';
	const BATCH_CONTACTS= 'https://www.google.com/m8/feeds/contacts/default/full/batch';
	const BATCH_GROUPS	= 'https://www.google.com/m8/feeds/groups/default/full/batch';
	
	
	protected $_entries = array();
	protected $_service;
	protected $_expectedClass;
	protected $_url;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts_Service $service
	 * @param string $url
	 * @param string $expectedClass Classname of entries of batch (either \Nettools\GoogleAPI\Services\Contacts\Contact or \Nettools\GoogleAPI\Services\Contacts\Group)
	 * @param string $userid Userid or special value 'default'
	 */
	public function __construct(\Nettools\GoogleAPI\Services\Contacts_Service $service, $url, $expectedClass, $userid = 'default')
	{
		$this->_service = $service;
		$this->_expectedClass = $expectedClass;
		
		// encoding @ character to urlencoding
		$userid = str_replace('@', '%40', $userid);		
		$this->_url = str_replace('default', $userid, $url);
	}
	 
	
	
	
	/**
	 * Add a new request to the batch
	 * 
	 * @param string $id Batch ID
	 * @param string $operation_type Kind of request (insert, query, update, delete)
	 * @param string $xml Xml request as a string
     * @param string $etag Etag property of contact, as read in the $contact->etag property ; to omit this security feature, pass '*' as $etag value
	 * @return \Nettools\GoogleAPI\Services\Contacts\Batch Return self reference for chaining calls
	 */
    public function add($id, $operation_type, $xml, $etag = NULL)
	{
		$this->_entries[] = new BatchEntryRequest($id, $operation_type, $xml, $etag);
		return $this;
	}
	
	
	
	/** 
	 * Test if something in the batch
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return count($this->_entries) == 0;
	}
	

	
	/**
	 * Execute batch
	 *
	 * @return \Nettools\GoogleAPI\Services\Contacts\BatchEntryResponse[] Returns an associative array of responses : keys are IDs of each batch request item, values are BatchEntryResponse objects
     * @throws \Google_Service_Exception Thrown if an error occured during the batch request
	 */
	public function execute()
	{
		// if batch empty, returnin an empty array
		if ( $this->isEmpty() )
			return [];
		
		
		// creating top-level batch request as a XML feed
		$breq = new BatchRequest($this->_entries);
		
		// send request
		$feed = $this->_service->sendRequest('POST', $this->_url, 
												[
													'body'=>$breq->getFeed(),
													'headers' => array(
															'Content-Type'  => 'application/atom+xml'
														)
												]);
		
		// get a BatchResponse object
		$bresp = new BatchResponse($feed, $this->_expectedClass);
		return $bresp->getEntries();
	}
}

?>