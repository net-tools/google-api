<?php
/**
 * ListJobs
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;


use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;
use \Nettools\GoogleAPI\Services\Misc\SerializedObjectsCollection;



/**
 * Jobs list response
 */
class ListJobs extends SerializedObjectsCollection
{
    /**
     * Constructor of collection
     *
     * @param \Stdclass[] $jobs Array of job objects
     */ 
	public function __construct(array $jobs)
    {
        parent::__construct(new ArrayCollection($jobs), \Nettools\GoogleAPI\Services\CloudPrint\Job::class);
    }
}

?>