<?php
/**
 * ArrayProperty
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class to store elements and implementing interfaces ArrayAccess and Iterator
 *
 * Used as container for emails, phoneNumbers, etc.
 */
class ArrayProperty implements \Iterator, \ArrayAccess
{
    /**
     * Array of items
     * 
     * @var array
     */
    protected $_array = NULL;

    
    
    /**
     * Constructor 
     *
     * @param array $array 
     */ 
	public function __construct(array $array)
    {
        $this->_array = $array;
    }


    
    /**
     * Check if an offset exists in the array property
     * 
     * @param int $offset
     * @return bool
     */
    public function offsetExists ($offset)
    {
        return isset($this->_array[$offset]);
    }
    
    
    
    /** 
     * Get an item from array
     * 
     * @param int $offset
     * @return mixed Item at offset `$offset`
     */
    public function offsetGet ($offset)
    {
        return isset($this->_array[$offset]) ? $this->_array[$offset] : null;
    }
    
    
    
    /** 
     * Set an item to an array
     * 
     * @param int $offset
     * @param mixed Item to set at offset `$offset`
     */
    public function offsetSet ($offset, $value)
    {
        if ( is_null($offset) )
            $this->_array[] = $value;
        else
            $this->_array[$offset] = $value;
    }

    
    
    /** 
     * Unset an item in an array
     * 
     * @param int $offset
     */
    public function offsetUnset ($offset)
    {
        unset($this->_array[$offset]);
    }
    
    
        
    /**
     * Get current item of iterator
     *
     * @return mixed Returns the current item 
     */
    public function current()
    {
        return current($this->_array);
    }

    
    
    /**
     * Get current key of iterator
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_array);
    }
    
    
    
    /**
     * Move iterator to next item
     */
    public function next()
    {
        next($this->_array);
    }
    
    
        
    /**
     * Reset iterator to first item
     */
    public function rewind()
    {
        reset($this->_array);
    }
    
    
    
    /**
     * Test iterator validity
     *
     * @return bool
     */
    public function valid()
    {
        // we are out of the array if current() return FALSE (assuming that our array doesn't have false values)
        return current($this->_array) !== FALSE;
    }
}

?>