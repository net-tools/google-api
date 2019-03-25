<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\ListGroups;
use \PHPUnit\Framework\TestCase;



class ListGroupsTest extends TestCase
{
    protected $groups;
    protected $xml;
    
    
    public function setUp() :void
    {
        // regular group
        $this->xml = simplexml_load_string(<<<XML
<feed xmlns='http://www.w3.org/2005/Atom'
    xmlns:openSearch='http://a9.com/-/spec/opensearch/1.1/'
    xmlns:gContact='http://schemas.google.com/contact/2008'
    xmlns:batch='http://schemas.google.com/gdata/batch'
    xmlns:gd='http://schemas.google.com/g/2005'
    gd:etag='feedEtag'>
  <id>jo@gmail.com</id>
  <updated>2008-12-10T10:44:43.955Z</updated>
  <category scheme='http://schemas.google.com/g/2005#kind'
    term='http://schemas.google.com/contact/2008#group'/>
  <title>Jo March's Contact Groups</title>
  <link rel='alternate' type='text/html'
    href='http://www.google.com/'/>
  <link rel='http://schemas.google.com/g/2005#feed'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full'/>
  <link rel='http://schemas.google.com/g/2005#post'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full'/>
  <link rel='http://schemas.google.com/g/2005#batch'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full/batch'/>
  <link rel='self'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full?max-results=25'/>
  <author>
    <name>Jo March</name>
    <email>jo@gmail.com</email>
  </author>
  <generator version='1.0'
    uri='http://www.google.com/m8/feeds'>Contacts</generator>
  <openSearch:totalResults>5</openSearch:totalResults>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <entry>
    <id>http://www.google.com/m8/feeds/groups/userEmail/base/6</id>
    <updated>1970-01-01T00:00:00.000Z</updated>
    <category scheme='http://schemas.google.com/g/2005#kind'
      term='http://schemas.google.com/contact/2008#group'/>
    <title>System Group: My Contacts</title>
    <content>System Group: My Contacts</content>
    <link rel='self' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/6'/>
    <gContact:systemGroup id='Contacts'/>
  </entry>
  <entry gd:etag='Etag'>
    <id>http://www.google.com/m8/feeds/groups/userEmail/base/groupId</id>
    <updated>2008-12-10T04:44:37.324Z</updated>
    <category scheme='http://schemas.google.com/g/2005#kind'
      term='http://schemas.google.com/contact/2008#group'/>
    <title>joggers</title>
    <content>joggers</content>
    <link rel='self' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/groupId'/>
    <link rel='edit' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/groupId'/>
  </entry>
</feed>
XML
          );
        $this->groups = new ListGroups($this->xml);
    }
    
    
    public function testGroups()
    {
		$system_group = $joggers_group = NULL;
		
		foreach ( $this->groups->getIterator() as $group )
		{
			$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $group);
			
			if ( $group->id == 'http://www.google.com/m8/feeds/groups/userEmail/base/6' )
				$system_group = $group;
			if ( $group->id == 'http://www.google.com/m8/feeds/groups/userEmail/base/groupId' )
				$joggers_group = $group;
		}
		
		
		// checking that 2 groups have been identified
		$this->assertNotNull($system_group);
		$this->assertNotNull($joggers_group);
		
		
		// checking properties
		$this->assertEquals('joggers', $joggers_group->title);
		$this->assertEquals('joggers', $joggers_group->content);
		$this->assertEquals(strtotime('2008-12-10T04:44:37.324Z'), $joggers_group->updated);
		$this->assertEquals('self', $joggers_group->links[0]->rel);
		$this->assertEquals('application/atom+xml', $joggers_group->links[0]->type);
		$this->assertEquals('https://www.google.com/m8/feeds/groups/userEmail/full/groupId', $joggers_group->links[0]->href);
		$this->assertEquals('edit', $joggers_group->links[1]->rel);
		$this->assertEquals('application/atom+xml', $joggers_group->links[1]->type);
		$this->assertEquals('https://www.google.com/m8/feeds/groups/userEmail/full/groupId', $joggers_group->links[1]->href);
    }    
    
    
        
}

?>