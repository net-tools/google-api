<?php
/**
 * BatchEntryResponse
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Batch entry response
 */
class BatchEntryResponse extends \Nettools\GoogleAPI\Services\Misc\MiscObject 
{
	protected $_httpCode;
	protected $_reason;
	protected $_entry;
	protected $_operationType;
	
	
	/** 
	 * Constructor
	 * 
	 * @param string $httpCode
	 * @param string $reason
	 * @param string $operationType Either insert, query, update, delete
	 * @param \Nettools\GoogleAPI\Services\Contacts\Element|null $entry
	 */
	public function __construct($httpCode, $reason, $operationType, $entry)
	{
		$this->_httpCode = $httpCode;
		$this->_reason = $reason;
		$this->_entry = $entry;
		$this->_operationType = $operationType;
		
		if ( $entry && !($entry instanceof \Nettools\GoogleAPI\Services\Contacts\Element) )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Entry is not of class '\\Nettools\\GoogleAPI\\Services\\Contacts\\Element'.");
	}
	
	
	
	/**
	 * See if batch request for this batch item was successfull (httpCode property)
	 *
	 * @return bool
	 */
	public function success()
	{
		return in_array((int)$this->_httpCode, [200, 201]);
	}
	
	
	
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception if a property is read-only
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array('httpCode', 'reason', 'entry', 'operationType');
    }
}

?>