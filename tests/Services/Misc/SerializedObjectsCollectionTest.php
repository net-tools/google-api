<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;
use \Nettools\GoogleAPI\Services\Misc\SerializedObjectsCollection;



class SerializedObjectTest
{
    public $value;
    
    
    public function __construct($item)
    {
        $this->value = "from serialized item '$item'";
    }
    
    
    static function fromFeed($item)
    {
        return new SerializedObjectTest($item);
    }
}




class NoFromFeedFunction
{
}




class SerializedObjectsCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testWrongClassNoFromFeedFunction()
    {
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $col = new SerializedObjectsCollection(new ArrayCollection([]), NoFromFeedFunction::class);
    }

    
    public function testEmptyCollection()
    {
        $col = new SerializedObjectsCollection(new ArrayCollection([]), SerializedObjectTest::class);
        $it = $col->getIterator();
        $this->assertEquals(false, $it->valid());
    }

    
    public function testCollection()
    {
        $col = new SerializedObjectsCollection(new ArrayCollection(['item1', 'item2']), SerializedObjectTest::class);
        $it = $col->getIterator();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(0, $it->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $it->current());
        $this->assertEquals("from serialized item 'item1'", $it->current()->value);
        
        $it->next();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(1, $it->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $it->current());
        $this->assertEquals("from serialized item 'item2'", $it->current()->value);
        
        $it->next();
        $this->assertEquals(false, $it->valid());
		
		
		// we can reset the SerializedObjectsCollection iterator and loop again ; this will rewind the ArrayCollection iterator, which is allowed 
        $it = $col->getIterator();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(0, $it->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $it->current());
        $this->assertEquals("from serialized item 'item1'", $it->current()->value);
        
        $it->next();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(1, $it->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $it->current());
        $this->assertEquals("from serialized item 'item2'", $it->current()->value);
        
        $it->next();
        $this->assertEquals(false, $it->valid());

		
		// a yield iterator (from SerializedObjectsCollection::getIterator()) can't be rewind
		$this->expectException(\Exception::class);
		$it->rewind();
    }
       
    
}

?>