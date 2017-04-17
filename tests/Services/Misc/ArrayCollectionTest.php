<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;





class ArrayCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyCollection()
    {
        $col = new ArrayCollection([]);
        $it = $col->getIterator();
        $this->assertEquals(false, $it->valid());
    }

    
    public function testCollection()
    {
        $col = new ArrayCollection(['item1', 'item2']);
        $it = $col->getIterator();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(0, $it->key());
        $this->assertEquals('item1', $it->current());
        
        $it->next();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(1, $it->key());
        $this->assertEquals('item2', $it->current());
        
        $it->next();
        $this->assertEquals(false, $it->valid());
		
		// rewind is allowed since we use ArrayObject::getIterator(), an iterator that can be rewind
		$it->rewind();
        $this->assertEquals(true, $it->valid());
    }

    
    public function testCollectionForeach()
    {
        $col = new ArrayCollection(['item1', 'item2']);
        
		$t = [];
		foreach ( $col as $item )
			$t[] = $item;
		
		$this->assertEquals(['item1', 'item2'], $t);

		// the second foreach will rewind the collection and its array iterator (through ArrayObject::getIterator())
		$t = [];
		foreach ( $col as $item )
			$t[] = $item;

		$this->assertEquals(['item1', 'item2'], $t);
    }
       
    
}

?>