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
    /**
     * Array of entries 
     * 
     * @var array
     */
    protected $_feed = NULL;

    
    
    /**
     * Constructor of collection
     *
     * @param array $feed Array to iterate
     */ 
	public function __construct(array $feed)
    {
        $this->_feed = $feed;
    }

    
    
    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->_feed);
    }

    
    
    /**
     * Get current item of iterator
     *
     * @return mixed Returns the current item 
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