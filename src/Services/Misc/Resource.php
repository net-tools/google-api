<?php
/**
 * Resource
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract class for service resource ; for example `$contacts_service->contacts` and `$contacts_service->groups`.
 */
class Resource
{
    /** @var \Nettools\GoogleAPI\Services\Service Service object */
    protected $_service = NULL;
	
	
    /**
     * Magic method to read properties
     *
     * @param string $k Property name
     * @return mixed
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if property $k does not exist in object
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
        else
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Property '$k' does not exist in '" . get_class($this) . "'.");
    }
    
    
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