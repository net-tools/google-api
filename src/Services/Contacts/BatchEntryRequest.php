<?php
/**
 * BatchEntryRequest
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Batch entry inner request 
 */
class BatchEntryRequest extends \Nettools\GoogleAPI\Services\Misc\MiscObject
{
	protected $_id;
	protected $_operationType;
	protected $_etag;
	protected $_body;
	
	
	
	/** 
	 * Constructor
	 * 
	 * @param string $id Batch ID
	 * @param string $operationType Kind of request (insert, query, update, delete)
	 * @param string $body Request as a XML string
     * @param string $etag Etag property of contact, as read in the $contact->etag or $group->etag property ; to omit this security feature, pass '*' as $etag value
	 */
	public function __construct($id, $operationType, $body, $etag)
	{
		$this->_id = $id;
		$this->_operationType = $operationType;
		$this->_body = $body;
		$this->_etag = $etag;
	}
	
	
	
	/**
	 * Magic method
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->asXml();
	}
	
	
	
	/**
	 * Build the batch entry as XML with required batch tags (id, operationType, etag attribute)
	 *
	 * @return string 
	 */
	public function asXml()
	{
		$xml = $this->_body;

		// update entry for batch processing
		if ( $this->_etag )
			$xml = preg_replace('|<entry>|', "<entry gd:etag='{$this->_etag}'>", $xml, 1);

		// add batch:xxx nodes
		$xml = preg_replace('|>|', "><batch:id>{$this->_id}</batch:id><batch:operation type='{$this->_operationType}'/>", $xml, 1);
		return $xml;
	}
	
}

?>