<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;





class ArrayCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyCollection()
    {
        $col = new ArrayCollection([]);
        $this->assertEquals(0, $col->count());
        $this->assertEquals(false, $col->valid());
    }

    
    public function testCollection()
    {
        $col = new ArrayCollection(['item1', 'item2']);
        $this->assertEquals(2, $col->count());
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(0, $col->key());
        $this->assertEquals('item1', $col->current());
        
        $col->next();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(1, $col->key());
        $this->assertEquals('item2', $col->current());
        
        $col->next();
        $this->assertEquals(false, $col->valid());
        
        $col->rewind();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(0, $col->key());
        $this->assertEquals('item1', $col->current());
        
    }
       
    
}

?>