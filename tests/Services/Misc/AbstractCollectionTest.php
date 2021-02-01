<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\AbstractCollection;
use \PHPUnit\Framework\TestCase;





class AbstractCollectionTest extends TestCase
{
    private function __iterator(array $a)
    {
        foreach ( $a as $item )
            yield $item;
    }
    
    
    public function testEmptyCollection()
    {
        $col = $this->getMockBuilder(AbstractCollection::class)->onlyMethods(['getIterator'])->getMock();
        $col->method('getIterator')->willReturn($this->__iterator([]));
        
        // testing
        $this->assertInstanceOf(\Iterator::class, $col);
        $this->assertEquals(false, $col->valid());
    }
    
    
    public function testCollection()
    {
        $col = $this->getMockBuilder(AbstractCollection::class)->onlyMethods(['getIterator'])->getMock();
        $col->method('getIterator')->willReturn($this->__iterator(['A', 'B', 'C']));
        
        // testing
        $this->assertInstanceOf(\Iterator::class, $col);
        $this->assertEquals(true, $col->valid());
        $this->assertEquals('A', $col->current());
        $col->next();
        $this->assertEquals('B', $col->current());
        $col->next();
        $this->assertEquals('C', $col->current());
        $col->next();
        $this->assertEquals(false, $col->valid());

		// a yield iterator can't be rewind
		$this->expectException(\Exception::class);
        $col->rewind();
	}
    
}

?>