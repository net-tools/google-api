<?php
/**
 * IteratorCollection
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Services\Misc;




/**
 * Class for collection of items iterable through an object implementing `Iterator` interface
 */
class IteratorCollection extends Collection 
{
    /**
     * Underlying iterator
     *
     * @var \Iterator
     */
    protected $_iterator;
    
    
    
    /**
     * Constructor of collection through an iterator
     *
     * @param \Iterator $feed Iterator
     */ 
	public function __construct(\Iterator $feed)
    {
        $this->_iterator = $feed;
    }
    
    
    /**
     * Get the iterator of collection
     * 
     * @return array|\Iterator
     */
    public function getIterator()
    {
        return $this->_iterator;
    }
}

?>