<?php
/**
 * MessagePart
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers\Gmail;



/**
 * Class to hold data about a Gmail message part
 */
class MessagePart
{
    protected $_mimeType = NULL;
    protected $_headers = NULL;
    
    
    /**
     * Magic accessor to protected properties
     *
     * @param string $k Property name
     * @return string Property value
     */     
    public function __get($k)
    {
        return $this->{"_$k"};
    }
    
    
    /**
     * Constructor
     * 
     * @param string $mimeType 
     * @param \Google_Service_Gmail_MessagePartHeader[] Array of headers objects (with name & value properties)
     */
    public function __construct($mimeType, $headers)
    {
        $this->_mimeType = $mimeType;
        $this->_headers = $headers;
    }
}

?>