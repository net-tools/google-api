<?php
/**
 * AbstractCollection
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract collection class to hold an iterable structure of items
 *
 * Iteration can be done by iterating directly on the object `foreach ( $coll as $item )` or through it's iterator accessor : `foreach ( $coll->getIterator() as $item )`.
 */
abstract class AbstractCollection implements Collection
{
    /**
     * Iterator created to walk through the collection directly by iterating through object
     * 
     * @var \Iterator
     */
    protected $_selfIterator = NULL;
    
    
    /**
     * Create or get the self iterator
     * 
     * @return \Iterator
     */
    protected function getSelfIterator()
    {
        if ( is_null($this->_selfIterator) )
            $this->_selfIterator = $this->getIterator();
        
        return $this->_selfIterator;
    }
    
    
    /**
     * Get an iterator for collection
     * 
     * @return \Iterator
     */
    abstract function getIterator();

    
    /**
     * Get current item of collection iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->getSelfIterator()->current();
    }
    
    
    /**
     * Get current key of collection iterator
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return $this->getSelfIterator()->key();
    }
    
    
    /**
     * Move to next item of collection iterator
     */
    public function next(): void
    {
        $this->getSelfIterator()->next();
    }
    
    
    /**
     * Reset collection iterator
     */
    public function rewind(): void
    {
        $this->getSelfIterator()->rewind();
    }
    
    
    /**
     * Check if iterator is valid
     *
     * @return bool True if iterator is valid or false (no more items to iterate)
     */
    public function valid(): bool
    {
        return $this->getSelfIterator()->valid();
    }
}

?>