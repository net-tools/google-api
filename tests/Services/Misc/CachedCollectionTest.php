<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\IteratorCollection;
use \Nettools\GoogleAPI\Services\Misc\CachedCollection;







class CachedCollectionTest extends \PHPUnit\Framework\TestCase
{
    private function __iterator(array $a)
    {
        foreach ( $a as $item )
            yield $item;
    }
    
    
    public function testCollection()
    {
        $col = new CachedCollection(new IteratorCollection($this->__iterator(['item1', 'item2'])));
        
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
		
		
		// we use a cache iterator, so the rewind operation is allowed
		$it->rewind();
		
		// loop again
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