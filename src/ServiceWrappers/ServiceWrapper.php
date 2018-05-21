<?php
/**
 * ServiceWrapper
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers;



/**
 * Service wrapper around a Google API service
 */
class ServiceWrapper
{
    /** @var \Google_Service Google service object ; may be set with any Google_Service sub-classes, such as Google_Service_Calendar, etc. */
    protected $_service = NULL;
	
	
    /**
     * Magic method to transfer method calls to underlying Google service (except for method defined here)
     *
     * @param string $name Method name
     * @param mixed[] $arguments Array of parameters (indexed array, not associative)
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ( method_exists($this, $name) )
            return $this->$name($arguments);
        else
            return $this->_service->$name($arguments);
    }
    
    
    /**
     * Magic method to get a property from underlying Google service (except for properties defined here)
     *
     * @param string $k Property name
     * @return mixed
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
        else
            return $this->_service->$k;
    }
    
    
    /**
     * Constructor of service helper 
     * 
     * @param \Google_Service $service Google service to send requests to
     */
    public function __construct(\Google_Service $service)
    {
        $this->_service = $service;
    }
}

?>