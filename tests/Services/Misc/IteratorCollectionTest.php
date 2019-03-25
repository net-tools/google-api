<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\IteratorCollection;
use \PHPUnit\Framework\TestCase;





class IteratorCollectionTest extends TestCase
{
    private function __iterator(array $a)
    {
        foreach ( $a as $v )
            yield $v;
    }


    public function testEmptyCollection()
    {
        $col = new IteratorCollection($this->__iterator([]));
        $it = $col->getIterator();
        $this->assertEquals(false, $it->valid());
    }

    
    public function testCollection()
    {
        $col = new IteratorCollection($this->__iterator(['item1', 'item2']));
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
    }
       
    
}

?>