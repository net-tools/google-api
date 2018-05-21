<?php
/**
 * Resource
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class for service resource
 *
 * For example `$contacts_service->contacts` and `$contacts_service->groups`.
 */
class Resource extends MiscObject
{
    /** 
     * Service object which owns this resource 
     * 
     * @var \Nettools\GoogleAPI\Services\Service
     */
    protected $_service = NULL;

    
	
    /**
     * Constructor of service resource
     * 
     * @param \Nettools\GoogleAPI\Services\Service $service Service object which owns this resource
     */
    public function __construct(\Nettools\GoogleAPI\Services\Service $service)
    {
        $this->_service = $service;
    }
}

?>