<?php
/**
 * CachedCollection
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Cached collection so that one-time iterators can be rewind
 *
 * Iteration can be done by iterating directly on the object `foreach ( $coll as $item )` or through it's iterator accessor : `foreach ( $coll->getIterator() as $item )`.
 */
class CachedCollection extends ArrayCollection
{
    /**
     * Constructor of cached collection
     *
     * @param Collection $col 
     */ 
	public function __construct(Collection $col)
    {
		$arr = [];
		foreach ( $col->getIterator() as $item )
			$arr[] = $item;
		
        parent::__construct($arr);
    }
}

?>