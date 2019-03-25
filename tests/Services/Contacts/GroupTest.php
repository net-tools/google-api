<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Group;
use \PHPUnit\Framework\TestCase;



class GroupTest extends TestCase
{
    protected $systemgroup;
    protected $group;
    protected $xml;
    
    
	
    public function setUp() :void
    {
        // system group
        $this->systemgroup = Group::fromFeed(simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my group</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <gContact:systemGroup id='Contacts'/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
          ));
        
        
        // regular group
        $this->xml = simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my group</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
          );
        $this->group = Group::fromFeed($this->xml);
    }
    
    
		
    public function testGroup()
    {
        // reading properties
        $this->assertEquals('my group', $this->group->title);
        $this->assertEquals('my id', $this->group->id);
        $this->assertEquals(strtotime('2017-04-01'), $this->group->updated);
        $this->assertEquals('notes', $this->group->content);
        $this->assertEquals('"my etag"', $this->group->etag);
        
        
        // testing links
        $links = $this->group->links;
        $this->assertCount(2, $links);

        $this->assertEquals('application/atom+xml', $links[0]->type);
        $this->assertEquals('self', $links[0]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/groups/userEmail/full/groupId', $links[0]->href);

        $this->assertEquals('application/atom+xml', $links[1]->type);
        $this->assertEquals('edit', $links[1]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/groups/userEmail/full/groupId', $links[1]->href);
        
        
        // modifying read/write properties
        $this->group->title = 'updated title';
        $this->group->content = 'updated notes';
        $this->assertEquals('updated title', $this->group->title);
        $this->assertEquals('updated notes', $this->group->content);
        

        // updating XML
        $this->group->toXml($this->xml);
        $this->assertEquals('updated title',(string) $this->xml->title);
        $this->assertEquals('updated notes',(string) $this->xml->content);
    }    
    
    
    public function testSystemGroup()
    {
        // reading properties
        $this->assertEquals('Contacts', $this->systemgroup->systemGroup);
        
        
        $links = $this->systemgroup->links;
        $this->assertCount(1, $links);

        $this->assertEquals('application/atom+xml', $links[0]->type);
        $this->assertEquals('self', $links[0]->rel);
        $this->assertEquals('https://www.google.com/m8/feeds/groups/userEmail/full/groupId', $links[0]->href);
    }
    
    
    
    public function testAsxml()
    {
        $g = new Group();
        $g->title = 'my group';
        $g->content = 'notes';

        // get xml string
        $xml = $g->asXml();
        $expected = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<entry xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005'>
	<content>notes</content>
	<title>my group</title>
</entry>
XML;

        $expected_o = simplexml_load_string($expected);
		$xml_o = simplexml_load_string($xml);
        
		$this->assertEquals($expected_o->content, $xml_o->content);		
		$this->assertEquals($expected_o->title, $xml_o->title);
    }
       
    
    
    public function testReadOnlyPropertySystemgroup()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->systemgroup->systemGroup = 'forbiden';
    }
       
    

	public function testReadOnlyPropertySystemgroup2()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $this->group->systemGroup = 'forbiden';
    }
 
    
        
}

?>