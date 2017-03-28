<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\XmlFeed;





class XmlFeedClass 
{
    public $value;
    
    
    public function __construct($val)
    {
        $this->value = $val;
    }
    
    
    static function fromFeed(\SimpleXMLElement $item)
    {
        return new XmlFeedClass((string) $item->value);
    }
}



class XmlFeedTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyFeed()
    {
        $xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8' ?><root></root>");
        $col = new XmlFeed($xml, XmlFeedClass::class, 'item');
        $this->assertEquals(false, $col->valid());
    }


    public function testFeed()
    {
        $xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8' ?><root><item><value>entry1value</value></item><item><value>entry2value</value></item></root>");

        $col = new XmlFeed($xml, XmlFeedClass::class, 'item');
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(0, $col->key());
        $this->assertInstanceOf(XmlFeedClass::class, $col->current());
        $this->assertEquals('entry1value', $col->current()->value);
        
        $col->next();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(1, $col->key());
        $this->assertInstanceOf(XmlFeedClass::class, $col->current());
        $this->assertEquals('entry2value', $col->current()->value);
        
        $col->next();
        $this->assertEquals(false, $col->valid());
        
        $col->rewind();
        $this->assertEquals(true, $col->valid());
        $this->assertEquals(0, $col->key());
        $this->assertInstanceOf(XmlFeedClass::class, $col->current());
        $this->assertEquals('entry1value', $col->current()->value);
    }

}

?>