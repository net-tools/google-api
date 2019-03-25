<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Element;
use \PHPUnit\Framework\TestCase;



class ElementTest extends TestCase
{
    protected $stub;
    protected $xml;
    
    
    public function setUp() :void
    {
        // stub since Element has an abstract method 'asXml'
        $this->stub = $this->getMockForAbstractClass(Element::class);
        
        // preparing xml data
        $this->xml = simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005'>
    <title>my title</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
</entry>
XML
          );
    }
    
    
    public function testElement()
    {
        $this->stub->assignXmlEntry($this->xml);
        
        // reading properties
        $this->assertEquals('my title', $this->stub->title);
        $this->assertEquals('my id', $this->stub->id);
        $this->assertEquals(false, $this->stub->deleted);
        $this->assertEquals(strtotime('2017-04-01'), $this->stub->updated);
        $this->assertEquals('notes', $this->stub->content);
        $this->assertEquals('"my etag"', $this->stub->etag);
        
        
        // testing 3 links
        $links = $this->stub->links;
        $this->assertCount(3, $links);

        $this->assertEquals('image/*', $links[0]->type);
        $this->assertEquals('http://schemas.google.com/contacts/2008/rel#photo', $links[0]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/photos/media/userEmail/contactId', $links[0]->href);
        $this->assertEquals('photoEtag', $links[0]->etag);

        $this->assertEquals('application/atom+xml', $links[1]->type);
        $this->assertEquals('self', $links[1]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId', $links[1]->href);
        
        $this->assertEquals('application/atom+xml', $links[2]->type);
        $this->assertEquals('edit', $links[2]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId', $links[1]->href);
        
        
        // modifying read/write properties
        $this->stub->title = 'updated title';
        $this->stub->content = 'updated notes';
        $this->assertEquals('updated title', $this->stub->title);
        $this->assertEquals('updated notes', $this->stub->content);
        
        
        // updating XML
        $this->stub->toXml($this->xml);
        $this->assertEquals('updated title',(string) $this->xml->title);
        $this->assertEquals('updated notes',(string) $this->xml->content);
    }
       
    

	public function testReadOnlyPropertyId()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->stub->id = 'updated id';
    }
 
    
    
    public function testReadOnlyPropertyUpdated()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->stub->updated = '2017-01-02';
    }
 
    
    
    public function testReadOnlyPropertyXml()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->stub->xml = '2017-01-02';
    }
  
    
    
    public function testReadOnlyPropertyEtag()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->stub->etag = 'new etag';
    }
  
    
    
    public function testReadOnlyPropertyDeleted()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->stub->deleted = true;
    }
       
      
    
}

?>