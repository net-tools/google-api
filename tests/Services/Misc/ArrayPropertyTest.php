<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\ArrayProperty;





class ArrayPropertyTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyArray()
    {
        $a = new ArrayProperty([]);
        $this->assertEquals(0, $a->count());
        $this->assertEquals(false, $a->valid());
        $this->assertEquals(false, $a->offsetExists(0));
        $this->assertEquals(false, $a->offsetGet(0));
        $this->assertEquals(false, $a[0]);
    }

    
    public function testArray()
    {
        $a = new ArrayProperty(['item1', 'item2']);
        $this->assertEquals(true, $a->valid());
        $this->assertEquals(2, $a->count());
        $this->assertEquals(true, $a->offsetExists(0));
        $this->assertEquals(true, $a->offsetExists(1));
        $this->assertEquals(0, $a->key());
        $this->assertEquals('item1', $a->current());
        $this->assertEquals('item1', $a->offsetGet(0));
        $this->assertEquals('item2', $a->offsetGet(1));
        $this->assertEquals('item1', $a[0]);
        $this->assertEquals('item2', $a[1]);
		
		
        $a->next();
        $this->assertEquals(true, $a->valid());
        $this->assertEquals(1, $a->key());
        $this->assertEquals('item2', $a->current());
        
        $a->next();
        $this->assertEquals(false, $a->valid());
        
        $a->rewind();
        $this->assertEquals(true, $a->valid());
        $this->assertEquals(0, $a->key());
        $this->assertEquals('item1', $a->current());
		
		
		// test set/unset
		$a->offsetSet(0, 'updated item0');
		$this->assertEquals('updated item0', $a->offsetGet(0));
		$this->assertEquals('updated item0', $a[0]);
		$a[1] = 'updated item1';
		$this->assertEquals('updated item1', $a[1]);
		$a->offsetUnset(0);
        $this->assertEquals(1, $a->count());
        $this->assertEquals(false, $a->offsetExists(0));
    }
       
    
}

?>