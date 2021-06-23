<?php
/**
 * MessageBody
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers\Gmail;



/**
 * Body part ; holds data about a Gmail message body
 */
class MessageBody extends MessagePart
{
    protected $_body = NULL;
    
    
    /**
     * Constructor
     * 
     * @param string $body
     * @param string $mimeType 
     * @param \Google\Service\Gmail\MessagePartHeader[] Array of headers objects (with name & value properties)
     */
    public function __construct($body, $mimeType, $headers)
    {
        parent::__construct($mimeType, $headers);
        $this->_body = $body;
    }
}




?>