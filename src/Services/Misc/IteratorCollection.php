<?php
/**
 * IteratorCollection
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Services\Misc;




/**
 * Class for collection of items iterable through an object implementing `Iterator` interface
 */
class IteratorCollection implements Collection 
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

    
    /**
     * Get current item of collection iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->_iterator->current();
    }
    
    
    /**
     * Get current key of collection iterator
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return $this->_iterator->key();
    }
    
    
    /**
     * Move to next item of collection iterator
     */
    public function next(): void
    {
        $this->_iterator->next();
    }
    
    
    /**
     * Reset collection iterator
     */
    public function rewind(): void
    {
        $this->_iterator->rewind();
    }
    
    
    /**
     * Check if iterator is valid
     *
     * @return bool True if iterator is valid or false (no more items to iterate)
     */
    public function valid(): bool
    {
        return $this->_iterator->valid();
    }
}

?>