<?php
/**
 * JobPayload
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;



/**
 * JobPayload object : represents a job to print, download from CloudPrint service
 */
class JobPayload extends \Nettools\GoogleAPI\Services\Misc\Payload
{
    /** 
     * Create a JobPayload object from a content-type and a binary string
     * 
     * @param string $contentType
     * @param string $bin
     * @return JobPayload
     */
    static function fromData($contentType, $bin)
    {
        return new JobPayload((object)['contentType'=>$contentType, 'body'=>$bin]);
    }
}

?>