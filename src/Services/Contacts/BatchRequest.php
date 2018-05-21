<?php
/**
 * BatchRequest
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Batch top-level request (embedding several requests)
 */
class BatchRequest extends \Nettools\GoogleAPI\Services\Misc\MiscObject
{
	protected $_requests;
	
	
	
	/** 
	 * Constructor
	 * 
	 * @param \Nettools\GoogleAPI\Services\Contacts\BatchEntryRequest[] $requests Array of request entries
	 */
	public function __construct(array $requests)
	{
		$this->_requests = $requests;
	}
	
	
	
	/**
	 * Build the batch feed
	 *
	 * @return string 
	 */
	public function getFeed()
	{
		// create a feed of entries for each batch request
		$req = implode("\n", $this->_requests);
		
		// creating the top-level xml feed with requests
		return "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>\n" . $req . "\n</feed>";
	}
	
}

?>