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
     * Underlying array
     *
     * @var mixed[]
     */
    protected $_array;
    
    
    
    /**
     * Constructor of collection through an array
     *
     * @param array $feed Array of objects
     */ 
	public function __construct(array $feed)
    {
        $this->_array = $feed;
    }
    
    
    /**
     * Get the iterator of collection
     * 
     * @return \Iterator
     */
    public function getIterator()
    {
        foreach ( $this->_array as $a )
            yield $a;
    }
}

?>