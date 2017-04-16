<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\Collection;





class CollectionTest extends \PHPUnit\Framework\TestCase
{
    private function __iterator(array $a)
    {
        foreach ( $a as $item )
            yield $item;
    }
    
    
    public function testEmptyCollection()
    {
        $col = $this->getMockBuilder(Collection::class)->setMethods(['getIterator'])->getMock();
        $col->method('getIterator')->willReturn($this->__iterator([]));
        
        // testing
        $this->assertInstanceOf('Iterator', $col);
        $this->assertEquals(false, $col->valid());
    }
    
    
    public function testCollection()
    {
        $col = $this->getMockBuilder(Collection::class)->setMethods(['getIterator'])->getMock();
        $col->method('getIterator')->willReturn($this->__iterator(['A', 'B', 'C']));
        
        // testing
        $this->assertInstanceOf('Iterator', $col);
        $this->assertEquals(true, $col->valid());
        $this->assertEquals('A', $col->current());
        $col->next();
        $this->assertEquals('B', $col->current());
        $col->next();
        $this->assertEquals('C', $col->current());
        $col->next();
        $this->assertEquals(false, $col->valid());

    }
       
    
}

?>