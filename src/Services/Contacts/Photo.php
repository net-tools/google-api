<?php
/**
 * Photo
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Photo object
 */
class Photo extends \Nettools\GoogleAPI\Services\Misc\Object
{
    protected $_contentType;
    protected $_body;
    
    
    
    /**
     * Constructor of contact photo
     *
     * @param \Stdclass $obj Litteral object with properties contentType and body
     */
    public function __construct(\Stdclass $obj)
    {
        $this->_contentType = $obj->contentType;
        $this->_body = $obj->body;
    }
    
    
    /** 
     * Create a Photo object from a content-type and an image as a binary string
     * 
     * @param string $contentType
     * @param string $image
     * @return Photo
     */
    static function fromData($contentType, $image)
    {
        return new Photo((object)['contentType'=>$contentType, 'body'=>$image]);
    }
}

?>