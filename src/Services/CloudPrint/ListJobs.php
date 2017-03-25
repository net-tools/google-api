<?php
/**
 * ListJobs
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;



/**
 * Jobs list response
 */
class ListJobs extends \Nettools\GoogleAPI\Services\Misc\ArrayCollection
{
    /**
     * Constructor of collection
     *
     * @param \Stdclass[] $jobs Array of job objects
     */ 
	public function __construct(array $jobs)
    {
        parent::__construct($jobs, \Nettools\GoogleAPI\Services\CloudPrint\Job::class);
    }
}

?>