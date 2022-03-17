<?php
/**
 * ArrayProperty
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class to store elements and implement interfaces ArrayAccess, Iterator and Countable
 *
 * Used as container for emails, phoneNumbers, etc.
 */
class ArrayProperty implements \Iterator, \ArrayAccess, \Countable
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
     * Search a value in the array property
     * 
     * @param mixed $value
     * @return int|bool Returns index of value found, or FALSE if not found
     */
    public function search($value)
    {
        return array_search($value, $this->_array);
    }

    
    
    /**
     * Get number of items in array property
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_array);
    }


    
    /**
     * Check if an offset exists in the array property
     * 
     * @param int $offset
     * @return bool
     */
    public function offsetExists (mixed $offset): bool
    {
        return array_key_exists($offset, $this->_array);
    }
    
    
    
    /** 
     * Get an item from array
     * 
     * @param int $offset
     * @return mixed Item at offset `$offset`
     */
    public function offsetGet (mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->_array[$offset] : null;
    }
    
    
    
    /** 
     * Set an item to an array
     * 
     * @param int $offset
     * @param mixed Item to set at offset `$offset`
     */
    public function offsetSet (mixed $offset, mixed $value): void
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
    public function offsetUnset (mixed $offset): void
    {
        unset($this->_array[$offset]);
		
		// reset numeric keys
		$this->_array = array_values($this->_array);
    }
    
    
        
    /**
     * Get current item of iterator
     *
     * @return mixed Returns the current item 
     */
    public function current(): mixed
    {
        return current($this->_array);
    }

    
    
    /**
     * Get current key of iterator
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->_array);
    }
    
    
    
    /**
     * Move iterator to next item
     */
    public function next(): void
    {
        next($this->_array);
    }
    
    
        
    /**
     * Reset iterator to first item
     */
    public function rewind(): void
    {
        reset($this->_array);
    }
    
    
    
    /**
     * Test iterator validity
     *
     * @return bool
     */
    public function valid(): bool
    {
        // we are out of the array if current() return FALSE (assuming that our array doesn't have false values)
        return current($this->_array) !== FALSE;
    }
}

?>