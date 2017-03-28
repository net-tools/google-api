<?php
/**
 * Proxy
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract class to interface with an object litteral.
 *
 * Acts as a proxy ; for example, useful to force type to Printer instead of dealing with Stdclass.
 */
abstract class Proxy
{
    /** @var \Stdclass Object proxied */
    protected $_object = NULL;
    
    
    
    /** 
     * Constructor
     * 
     * @param \Stdclass $object Object to proxy
     */
    public function __construct(\Stdclass $object)
    {
        $this->_object = $object;
    }
    
    
    
    /**
     * Magic method to read properties from underlying object
     *
     * @param string $k Property name
     * @return mixed
     */
    public function __get($k)
    {
        return $this->_object->$k;
    }
    
    
    
    /**
     * Magic method to write properties from underlying object
     *
     * @param string $k Property name
     * @param mixed $v Property value
     */
    public function __set($k, $v)
    {
        $this->_object->$k = $v;
    }
}

?>