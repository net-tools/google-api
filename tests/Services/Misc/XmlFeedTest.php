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
        $it = $col->getIterator();
        $this->assertEquals(false, $it->valid());
    }


    public function testFeed()
    {
        $xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8' ?><root><item><value>entry1value</value></item><item><value>entry2value</value></item></root>");

        $col = new XmlFeed($xml, XmlFeedClass::class, 'item');
        $it = $col->getIterator();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(0, $it->key());
        $this->assertInstanceOf(XmlFeedClass::class, $it->current());
        $this->assertEquals('entry1value', $it->current()->value);
        
        $it->next();
        $this->assertEquals(true, $it->valid());
        $this->assertEquals(1, $it->key());
        $this->assertInstanceOf(XmlFeedClass::class, $it->current());
        $this->assertEquals('entry2value', $it->current()->value);
        
        $it->next();
        $this->assertEquals(false, $it->valid());
    }

}

?>