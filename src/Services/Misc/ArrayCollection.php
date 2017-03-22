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
    
    /** @var string Classname of object contained in feed ; those object will be created on the fly during iterations */
    protected $_feedOfClass = NULL;

    
    
    /**
     * Constructor of collection
     *
     * @param array $feed Array to iterate
     * @param string $classname Class name of objects from feed
     */ 
	public function __construct(array $feed, $classname)
    {
        $this->_feed = $feed;
        $this->_feedOfClass = $classname;
    }

    
    
    /**
     * Get current item of iterator
     *
     * @return mixed Returns an object of class $this->$_feedOfClass
     */
    public function current()
    {
        $class = $this->_feedOfClass;
        return $class::fromFeed(current($this->_feed));
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