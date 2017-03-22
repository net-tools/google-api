<?php
/**
 * Payload
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Payload object
 */
class Payload extends \Nettools\GoogleAPI\Services\Misc\Object
{
    protected $_contentType;
    protected $_body;
    
    
    
    /**
     * Constructor of data object (download/upload)
     *
     * @param \Stdclass $obj Litteral object with properties contentType and body
     */
    public function __construct(\Stdclass $obj)
    {
        $this->_contentType = $obj->contentType;
        $this->_body = $obj->body;
    }
}

?>