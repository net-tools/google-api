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
class Photo extends \Nettools\GoogleAPI\Services\Misc\Payload
{
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