<?php
/**
 * BatchResponse
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Batch top-level response (embedding several responses)
 */
class BatchResponse extends \Nettools\GoogleAPI\Services\Misc\MiscObject
{
	protected $_xml;
	protected $_expectedClass;
	
	
	
	/** 
	 * Constructor
	 * 
	 * @param \stdClass[] $requests Array of object litterals with data about each request in the batch
	 * @param string $expectedClass Classname of entries of batch (either \Nettools\GoogleAPI\Services\Contacts\Contact or \Nettools\GoogleAPI\Services\Contacts\Group)
	 */
	public function __construct(\SimpleXMLElement $xml, $expectedClass)
	{
		$this->_xml = $xml;
		$this->_expectedClass = $expectedClass;
	}
	
	
	
	/** 
	 * Parse the batch response and get an array of individual response entries for each batch item response
	 *
	 * @return \Nettools\GoogleAPI\Services\Contacts\BatchEntryResponse[]
	 */
	public function getEntries()
	{
		$ret = [];
		
		
		// for each response entry in the batch
		foreach( $this->_xml->entry as $entry )
		{
			$batch_nodes = $entry->children('batch', true);
			
			// read http error code for this batche lement
			$id = (string) $batch_nodes->id;
			$httpcode = (string)$batch_nodes->status->attributes()->code;
						
			// if success for this batch element
			if ( in_array($httpcode, ['200', '201']) )
			{
				// create the expected class and assign the xml properties
				$obj = new $this->_expectedClass();
				$obj->assignXmlEntry($entry);
			}
			else
				$obj = NULL;
			
			
			// build the array to be returned
			$ret[$id] = new BatchEntryResponse($httpcode, (string)$batch_nodes->status->attributes()->reason, (string)$batch_nodes->operation->attributes()->type, $obj);
		}
		
		
		return $ret;
	}
	
}

?>