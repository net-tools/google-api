<?php
/**
 * SerializedObjectsCollection
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Services\Misc;




/**
 * Class for collection of serialized items
 *
 * Items are fetched from a underlying collection, which stores items in a serializable format 
 * (json, xml, etc.) ; when the collection is iterated, an object of class `_feedOfClass`is created
 * from the current serialized item.
 */
class SerializedObjectsCollection extends Collection 
{
    /** 
     * Underlying collection
     *
     * @var Collection
     */
    protected $_collection = NULL;
    
    
    /** 
     * Classname of object contained in collection ; those object will be created on the fly during iterations 
     *
     * @var string 
     */
    protected $_feedOfClass = NULL;

    
    
    /**
     * Constructor of collection
     *
     * @param Collection $coll Collection of serialized items to iterate
     * @param string $classname Class name of objects from feed
     */ 
	public function __construct(Collection $coll, $classname)
    {
        $this->_collection = $coll;
        $this->_feedOfClass = $classname;
        
        if ( !method_exists($classname, 'fromFeed') )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Class '$classname' doesn't have a static 'fromFeed' method.");
    }

    
    
    /**
     * Get number of items in collection
     *
     * @return int 
     */
    public function count()
    {
        return $this->_collection->count();
    }

    
    
    /**
     * Get current item of iterator
     *
     * @return mixed Returns an object of class $this->$_feedOfClass
     */
    public function current()
    {
        $class = $this->_feedOfClass;
        return $class::fromFeed($this->_collection->current());
    }

    
    /**
     * Get current key of iterator
     *
     * @return mixed
     */
    public function key()
    {
        return $this->_collection->key();
    }
    
    
    /**
     * Move iterator to next item
     */
    public function next()
    {
        $this->_collection->next();
    }
    
        
    /**
     * Reset iterator to first item
     */
    public function rewind()
    {
        return $this->_collection->rewind();
    }
    
    
    /**
     * Test iterator validity
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_collection->valid();
    }
}

?>