<?php
/**
 * Collection
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Abstract class for collection of items
 */
abstract class Collection implements \Iterator
{
    abstract public function current();
    abstract public function key();
    abstract public function next();
    abstract public function rewind();
    abstract public function valid();
}

?>