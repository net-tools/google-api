<?php
/**
 * Payload
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Payload object with content-type and body
 */
class Payload extends \Nettools\GoogleAPI\Services\Misc\MiscObject
{
    protected $_contentType;
    protected $_body;
    
    
    
    /**
     * Constructor of data object (download/upload)
     *
     * @param \Stdclass|Payload $obj Litteral object with properties contentType and body or another payload object
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if $obj does not inherit from one of the acceptable class
     */
    public function __construct($obj)
    {
		if ( ($obj instanceof \Stdclass) || ($obj instanceof Payload) )
		{
        	$this->_contentType = $obj->contentType;
        	$this->_body = $obj->body;
		}
		else
			throw new \Nettools\GoogleAPI\Exceptions\Exception("Class '" . get_class($this) . "' constructor except a litteral Stdclass object or another Payload object.");
    }
}

?>