<?php
/**
 * MessageAttachment
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers\Gmail;



/**
 * Attachment or embedded attachement (inline) from a gmail message
 */
class MessageAttachment extends MessagePart
{
    protected $_id = NULL;
    protected $_name = NULL;
    
    
    /**
     * Constructor
     * 
     * @param string $id ID of attachement as provided by the API, so that we may get its actual content at a later time
     * @param string $name Filename of attachement or Content-ID of the inline attachment
     * @param string $mimeType 
     * @param \Google_Service_Gmail_MessagePartHeader[] Array of headers objects (with name & value properties)
     */
    public function __construct($id, $name, $mimeType, $headers)
    {
        parent::__construct($mimeType, $headers);
        $this->_id = $id;
        $this->_name = $name;
    }
}




?>