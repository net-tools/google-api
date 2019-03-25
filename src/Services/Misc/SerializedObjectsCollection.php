<?php
/**
 * SerializedObjectsCollection
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Services\Misc;




/**
 * Class for collection of serialized items
 *
 * Items are fetched from a underlying collection, which stores items in a serializable format 
 * (json, xml, etc.) ; when the collection is iterated, an object of class `_feedOfClass` is created
 * from the current serialized item.
 */
class SerializedObjectsCollection extends AbstractCollection 
{
    /**
     * Underlying collection to iterate and whose items will be converted to objects during iteration
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
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if class `$classname` doesn't have a `fromFeed` static method
     */ 
	public function __construct(Collection $coll, $classname)
    {
        // underlying collection
        $this->_collection = $coll;
        
        // object class of serialized items
        $this->_feedOfClass = $classname;
        
        // check $classname implements a fromFeed static function
        if ( !method_exists($classname, 'fromFeed') )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Class '$classname' doesn't have a static 'fromFeed' method.");
    }

    
    
    /**
     * Get an iterator for collection
     *
     * Each item from underlying collection is converted to an object of `$this->_feedOfClass`.     
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        // classname to create
        $class = $this->_feedOfClass;

        foreach ( $this->_collection->getIterator() as $item )
            yield $class::fromFeed($item);
    }

}

?>