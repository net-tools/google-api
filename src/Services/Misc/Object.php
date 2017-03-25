<?php
/**
 * Object
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract class for an object with strict access control to its properties (no read access for non-existant properties and no write access at all)
 */
abstract class Object
{
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
     * Magic method to forbid write access to properties
     *
     * @param string $k Property name
     * @param string $v Property value
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Always thrown since we don't allow write access
     */
    public function __set($k, $v)
    {
        throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Write access is forbidden for class '" . get_class($this) . "'.");
    }
}

?>