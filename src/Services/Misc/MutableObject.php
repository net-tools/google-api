<?php
/**
 * MutableObject
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract class for an object with strict access control to its properties (no write access for non-existant properties)
 */
abstract class MutableObject extends MiscObject
{
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception if a property is read-only
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array();
    }
    
    
    /**
     * Magic method to write properties
     *
     * @param string $k Property name
     * @param string $v Property value
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if property $k does not exist in object
     */
    public function __set($k, $v)
    {
        // detect read-only properties and forbid their assignement
        if ( in_array($k, $this->_getReadonlyProperties()) )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' is read-only in class '" . get_class($this) . "'.");

        if ( property_exists($this, "_$k") )
            $this->{"_$k"} = $v;
        else
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' does not exist in '" . get_class($this) . "'.");
    }
}

?>