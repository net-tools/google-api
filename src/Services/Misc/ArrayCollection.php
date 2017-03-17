<?php
/**
 * ArrayCollection
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class for collection of items stored in an array
 */
class ArrayCollection extends Collection 
{
    /** @var array Array of entries */
    protected $_feed = NULL;
    

    /**
     * Get current item of iterator
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_feed);
    }
    
        
    /**
     * Get current key of iterator
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_feed);
    }
    
    
    /**
     * Move iterator to next item
     */
    public function next()
    {
        next($this->_feed);
    }
    
        
    /**
     * Reset iterator to first item
     */
    public function rewind()
    {
        reset($this->_feed);
    }
    
    
    /**
     * Test iterator validity
     *
     * @return bool
     */
    public function valid()
    {
        // we are out of the array if current() return FALSE (assuming that our feed array doesn't have false values)
        return current($this->_feed) !== FALSE;
    }
}

?>