<?php
/**
 * Job
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;



/**
 * Job object
 */
class Job extends \Nettools\GoogleAPI\Services\Misc\Proxy
{
    /**
     * Create a job from an entry
     *
     * @param \Stdclass $job Object entry as an anonymous object
     * @return Job Returns a new Job object
     */
    static public function fromFeed(\Stdclass $job)
    {
        return new Job($job);
    }
}

?>