<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\ListContacts;



class ListContactsTest extends \PHPUnit\Framework\TestCase
{
    protected $contacts;
    protected $xml;
    
    
    public function setUp()
    {
        // regular group
        $this->xml = simplexml_load_string(<<<XML
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:openSearch="http://a9.com/-/spec/opensearch/1.1/"
    xmlns:gContact="http://schemas.google.com/contact/2008"
    xmlns:batch="http://schemas.google.com/gdata/batch"
    xmlns:gd="http://schemas.google.com/g/2005"
    gd:etag="feedEtag">
  <id>userEmail</id>
  <updated>2008-12-10T10:04:15.446Z</updated>
  <category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact"/>
  <link rel="http://schemas.google.com/g/2005#feed" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full"/>
  <link rel="http://schemas.google.com/g/2005#post" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full"/>
  <link rel="http://schemas.google.com/g/2005#batch" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full/batch"/>
  <link rel="self" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full?max-results=25"/>
  <author>
    <name>User</name>
    <email>userEmail</email>
  </author>
  <generator version="1.0" uri="http://www.google.com/m8/feeds">
    Contacts
  </generator>
  <openSearch:totalResults>1</openSearch:totalResults>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <entry gd:etag="contactEtag">
    <id>http://www.google.com/m8/feeds/contacts/userEmail/base/contactId</id>
    <updated>2008-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2008-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>Fitzwilliam Darcy</title>
    <gd:name>
      <gd:fullName>Fitzwilliam Darcy</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">456</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="hamster"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/userEmail/base/groupId"/>
  </entry>
  <entry gd:etag="contactEtag2">
    <id>http://www.google.com/m8/feeds/contacts/userEmail/base/contactId2</id>
    <updated>2009-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2009-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>John Darcy</title>
    <gd:name>
      <gd:fullName>John Darcy</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId2"
        gd:etag="photoEtag2"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId2"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId2"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">123</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="dog"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/userEmail/base/groupId2"/>
  </entry>
</feed>
XML
          );
        $this->contacts = new ListContacts($this->xml);
    }
    
    
    public function testContacts()
    {
		$c1 = $c2 = NULL;
		
		
		foreach ( $this->contacts->getIterator() as $contact )
		{
			$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $contact);
			
			if ( $contact->id == 'http://www.google.com/m8/feeds/contacts/userEmail/base/contactId' )
				$c1 = $contact;
			if ( $contact->id == 'http://www.google.com/m8/feeds/contacts/userEmail/base/contactId2' )
				$c2 = $contact;
		}
		
		
		// checking that 2 contact have been identified
		$this->assertNotNull($c1);
		$this->assertNotNull($c2);
		
		
		// checking properties
		$this->assertEquals('http://www.google.com/m8/feeds/contacts/userEmail/base/contactId', $c1->id);
		$this->assertEquals('Fitzwilliam Darcy', $c1->title);
		$this->assertEquals('Fitzwilliam Darcy', $c1->fullName);
		$this->assertEquals(3, count($c1->links));
		$this->assertEquals('photoEtag', $c1->links[0]->etag);
		$this->assertEquals(1, count($c1->phoneNumbers));
		$this->assertEquals('456', $c1->phoneNumbers[0]->phoneNumber);
		$this->assertEquals('http://schemas.google.com/g/2005#home', $c1->phoneNumbers[0]->rel);
		$this->assertEquals(1, count($c1->extendedProperties));
		$this->assertEquals('hamster', $c1->extendedProperties[0]->value);
		$this->assertEquals(1, count($c1->groupsMembershipInfo));
		$this->assertEquals('http://www.google.com/m8/feeds/groups/userEmail/base/groupId', $c1->groupsMembershipInfo[0]);
		
		
		// checking properties
		$this->assertEquals('http://www.google.com/m8/feeds/contacts/userEmail/base/contactId2', $c2->id);
		$this->assertEquals('John Darcy', $c2->title);
		$this->assertEquals('John Darcy', $c2->fullName);
		$this->assertEquals(3, count($c2->links));
		$this->assertEquals('photoEtag2', $c2->links[0]->etag);
		$this->assertEquals(1, count($c2->phoneNumbers));
		$this->assertEquals('123', $c2->phoneNumbers[0]->phoneNumber);
		$this->assertEquals('http://schemas.google.com/g/2005#home', $c2->phoneNumbers[0]->rel);
		$this->assertEquals(1, count($c2->extendedProperties));
		$this->assertEquals('dog', $c2->extendedProperties[0]->value);
		$this->assertEquals(1, count($c2->groupsMembershipInfo));
		$this->assertEquals('http://www.google.com/m8/feeds/groups/userEmail/base/groupId2', $c2->groupsMembershipInfo[0]);

    }    
    
    
        
}

?>