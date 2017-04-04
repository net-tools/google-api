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
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\ServiceException
     */
    public function testWrongClassNoFromFeedFunction()
    {
        $col = new SerializedObjectsCollection(new ArrayCollection([]), NoFromFeedFunction::class);
    }

    
    public function testEmptyCollection()
    {
        $col = new SerializedObjectsCollection(new ArrayCollection([]), SerializedObjectTest::class);
        $this->assertEquals(false, $col->valid());
        $this->assertEquals(0, $col->count());
    }

    
    public function testCollection()
    {
        $col = new SerializedObjectsCollection(new ArrayCollection(['item1', 'item2']), SerializedObjectTest::class);
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(2, $col->count());
        $this->assertEquals(0, $col->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $col->current());
        $this->assertEquals("from serialized item 'item1'", $col->current()->value);
        
        $col->next();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(1, $col->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $col->current());
        $this->assertEquals("from serialized item 'item2'", $col->current()->value);
        
        $col->next();
        $this->assertEquals(false, $col->valid());
        
        $col->rewind();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(0, $col->key());
        $this->assertInstanceOf(SerializedObjectTest::class, $col->current());
        $this->assertEquals("from serialized item 'item1'", $col->current()->value);
    }
       
    
}

?>