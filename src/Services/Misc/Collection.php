<?php
/**
 * Collection
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Collection interface  to hold an iterable structure of items
 *
 * Iteration can be done by iterating directly on the object `foreach ( $coll as $item )` or through it's iterator accessor : `foreach ( $coll->getIterator() as $item )`.
 */
interface Collection extends \Iterator
{
    /**
     * Get an iterator for collection
     * 
     * @return \Iterator
     */
    function getIterator();
}

?>