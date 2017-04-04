<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Services\Misc\ArrayProperty;





class ContactTest extends \PHPUnit\Framework\TestCase
{
    protected $contact;
    protected $xml;
    
    
    public function setUp()
    {
        // regular group
        $this->xml = simplexml_load_string(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my contact</title>
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
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="liz@gmail.com" displayName="E. Bennet"/>
    <gd:email label="sweet home"
        address="liz@example.org"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#work" primary="true">(206)555-1212</gd:phoneNumber>
    <gd:phoneNumber label="sweet home">(206)555-1213</gd:phoneNumber>
    <gd:im address="liz@gmail.com"
        protocol="http://schemas.google.com/g/2005#GOOGLE_TALK"
        primary="true"
        rel="http://schemas.google.com/g/2005#home"/>
    <gd:im address="liz.work@gmail.com"
        protocol="http://schemas.google.com/g/2005#SKYPE"
        rel="http://schemas.google.com/g/2005#work"/>
    <gd:structuredPostalAddress
          rel="http://schemas.google.com/g/2005#work"
          primary="true">
        <gd:city>Mountain View</gd:city>
        <gd:street>1600 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>1600 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:structuredPostalAddress
          label="sweet home">
        <gd:city>Mountain View</gd:city>
        <gd:street>100 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>100 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:extendedProperty name="pet" value="hamster" />
    <gContact:event rel="anniversary">
        <gd:when startTime="2000-01-01" />
    </gContact:event>
    <gContact:relation rel="assistant">Suzy</gContact:relation>
    <gContact:relation rel="manager">Boss</gContact:relation>
    <gContact:website href="http://blog.user.com" primary="true" rel="blog" />
    <gContact:website href="http://me.homepage.com" label="Testing site" />
    <gContact:userDefinedField key="key1" value="value1" />
    <gContact:userDefinedField key="key2" value="value2" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
</entry>
XML
          );
        $this->contact = Contact::fromFeed($this->xml);
    }
    
    
    public function testContact()
    {
        // reading properties
        $this->assertEquals('my contact', $this->contact->title);
        $this->assertEquals('my id', $this->contact->id);
        $this->assertEquals(strtotime('2017-04-01'), $this->contact->updated);
        $this->assertEquals('notes', $this->contact->content);
        $this->assertEquals('"my etag"', $this->contact->etag);
        
        
        // testing links
        $links = $this->contact->links;
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
        $this->assertEquals('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId', $links[2]->href);
        
        
        // testing emails
        $emails = $this->contact->emails;
        $this->assertCount(2, $emails);
        $this->assertEquals((object)['address'=>'liz@gmail.com', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $emails[0]);
        $this->assertEquals((object)['address'=>'liz@example.org', 'primary'=>false, 'label'=>'sweet home'], $emails[1]);
        
        
        // testing phone numbers
        $phones = $this->contact->phoneNumbers;
        $this->assertCount(2, $phones);
        $this->assertEquals((object)['phoneNumber'=>'(206)555-1212', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $phones[0]);
        $this->assertEquals((object)['phoneNumber'=>'(206)555-1213', 'primary'=>false, 'label'=>'sweet home'], $phones[1]);
        
        
        // testing ims
        $ims = $this->contact->ims;
        $this->assertCount(2, $ims);
        $this->assertEquals((object)['address'=>'liz@gmail.com', 'primary'=>true, 'protocol'=>'http://schemas.google.com/g/2005#GOOGLE_TALK', 'rel'=>'http://schemas.google.com/g/2005#home'], $ims[0]);
        $this->assertEquals((object)['address'=>'liz.work@gmail.com', 'primary'=>false, 'protocol'=>'http://schemas.google.com/g/2005#SKYPE', 'rel'=>'http://schemas.google.com/g/2005#work'], $ims[1]);
        
        
        // testing structured postal address
        $addrs = $this->contact->structuredPostalAddresses;
        $this->assertCount(2, $addrs);
        $this->assertEquals((object)['formattedAddress'=>'1600 Amphitheatre Pkwy Mountain View', 'city'=>'Mountain View', 'street'=>'1600 Amphitheatre Pkwy', 'region'=>'CA', 'postcode'=>'94043', 'country'=>'United States', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $addrs[0]);
        $this->assertEquals((object)['formattedAddress'=>'100 Amphitheatre Pkwy Mountain View', 'city'=>'Mountain View', 'street'=>'100 Amphitheatre Pkwy', 'region'=>'CA', 'postcode'=>'94043', 'country'=>'United States', 'primary'=>false, 'label'=>'sweet home'], $addrs[1]);
        
        
        // testing events
        $events = $this->contact->events;
        $this->assertCount(1, $events);
        $this->assertEquals((object)['when'=>'2000-01-01', 'rel'=>'anniversary'], $events[0]);
        
        
        // testing relations
        $relations = $this->contact->relations;
        $this->assertCount(2, $relations);
        $this->assertEquals((object)['relation'=>'Suzy', 'rel'=>'assistant'], $relations[0]);
        $this->assertEquals((object)['relation'=>'Boss', 'rel'=>'manager'], $relations[1]);
        
        
        // testing websites
        $webs = $this->contact->websites;
        $this->assertCount(2, $webs);
        $this->assertEquals((object)['href'=>'http://blog.user.com', 'primary'=>true, 'rel'=>'blog'], $webs[0]);
        $this->assertEquals((object)['href'=>'http://me.homepage.com', 'primary'=>false, 'label'=>'Testing site'], $webs[1]);
        
         
        // testing extendedProperties
        $epfields = $this->contact->extendedProperties;
        $this->assertCount(1, $epfields);
        $this->assertEquals((object)['name'=>'pet', 'value'=>'hamster'], $epfields[0]);
        
         
        // testing userdefinedfields
        $ufields = $this->contact->userDefinedFields;
        $this->assertCount(2, $ufields);
        $this->assertEquals((object)['key'=>'key1', 'value'=>'value1'], $ufields[0]);
        $this->assertEquals((object)['key'=>'key2', 'value'=>'value2'], $ufields[1]);
        
         
        // testing groupsMembershipInfo
        $groups = $this->contact->groupsMembershipInfo;
        $this->assertCount(1, $groups);
        $this->assertEquals('http://www.google.com/m8/feeds/groups/userEmail/base/groupId', $groups[0]);
    }    
    
    

    public function testUpdateContact()
    {
        // updating contact
        $this->contact->title = 'UPDATED title';
        $this->contact->content = 'UPDATED content';
        $this->contact->emails[] = (object)['address'=>'lizzie@gmail.com', 'primary'=>false, 'rel'=>'http://schemas.google.com/g/2005#home'];
        $this->contact->phoneNumbers[] = (object)['phoneNumber'=>'09090909', 'primary'=>false, 'label'=>'my old pager'];
        $this->contact->ims[] = (object)['address'=>'lizzie@gmail.com', 'primary'=>false, 'protocol'=>'http://schemas.google.com/g/2005#GOOGLE_TALK', 'rel'=>'http://schemas.google.com/g/2005#home'];
        unset($this->contact->structuredPostalAddresses[1]);
        $this->contact->events[] = (object)['when'=>'2000-12-31', 'rel'=>'other'];
        $this->contact->relations[] = (object)['relation'=>'Al', 'rel'=>'partner'];
        $this->contact->websites[] = (object)['href'=>'http://work.office.net', 'primary'=>false, 'rel'=>'work'];
        $this->contact->userDefinedFields = [(object)['key'=>'key0', 'value'=>'value0']];   // testing setting an array to an ArrayProperty : object ArrayProperty will be created
        $this->contact->extendedProperties = [(object)['name'=>'hobby', 'value'=>'music']];   // testing setting an array to an ArrayProperty : object ArrayProperty will be created
        $this->contact->groupsMembershipInfo[] = 'http://www.google.com/m8/feeds/groups/userEmail/base/groupId2';
        
                 
        
        // reading properties
        $this->assertEquals('UPDATED title', $this->contact->title);
        $this->assertEquals('UPDATED content', $this->contact->content);
        
        
        // testing emails
        $emails = $this->contact->emails;
        $this->assertCount(3, $emails);
        $this->assertEquals((object)['address'=>'liz@gmail.com', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $emails[0]);
        $this->assertEquals((object)['address'=>'liz@example.org', 'primary'=>false, 'label'=>'sweet home'], $emails[1]);
        $this->assertEquals((object)['address'=>'lizzie@gmail.com', 'primary'=>false, 'rel'=>'http://schemas.google.com/g/2005#home'], $emails[2]);
        
        
        // testing phone numbers
        $phones = $this->contact->phoneNumbers;
        $this->assertCount(3, $phones);
        $this->assertEquals((object)['phoneNumber'=>'(206)555-1212', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $phones[0]);
        $this->assertEquals((object)['phoneNumber'=>'(206)555-1213', 'primary'=>false, 'label'=>'sweet home'], $phones[1]);
        $this->assertEquals((object)['phoneNumber'=>'09090909', 'primary'=>false, 'label'=>'my old pager'], $phones[2]);
        
        
        // testing ims
        $ims = $this->contact->ims;
        $this->assertCount(3, $ims);
        $this->assertEquals((object)['address'=>'liz@gmail.com', 'primary'=>true, 'protocol'=>'http://schemas.google.com/g/2005#GOOGLE_TALK', 'rel'=>'http://schemas.google.com/g/2005#home'], $ims[0]);
        $this->assertEquals((object)['address'=>'liz.work@gmail.com', 'primary'=>false, 'protocol'=>'http://schemas.google.com/g/2005#SKYPE', 'rel'=>'http://schemas.google.com/g/2005#work'], $ims[1]);
        $this->assertEquals((object)['address'=>'lizzie@gmail.com', 'primary'=>false, 'protocol'=>'http://schemas.google.com/g/2005#GOOGLE_TALK', 'rel'=>'http://schemas.google.com/g/2005#home'], $ims[2]);
        
        
        // testing structured postal address
        $addrs = $this->contact->structuredPostalAddresses;
        $this->assertCount(1, $addrs);
        $this->assertEquals((object)['formattedAddress'=>'1600 Amphitheatre Pkwy Mountain View', 'city'=>'Mountain View', 'street'=>'1600 Amphitheatre Pkwy', 'region'=>'CA', 'postcode'=>'94043', 'country'=>'United States', 'primary'=>true, 'rel'=>'http://schemas.google.com/g/2005#work'], $addrs[0]);
        
        
        // testing events
        $events = $this->contact->events;
        $this->assertCount(2, $events);
        $this->assertEquals((object)['when'=>'2000-01-01', 'rel'=>'anniversary'], $events[0]);
        $this->assertEquals((object)['when'=>'2000-12-31', 'rel'=>'other'], $events[1]);
        
        
        // testing relations
        $relations = $this->contact->relations;
        $this->assertCount(3, $relations);
        $this->assertEquals((object)['relation'=>'Suzy', 'rel'=>'assistant'], $relations[0]);
        $this->assertEquals((object)['relation'=>'Boss', 'rel'=>'manager'], $relations[1]);
        $this->assertEquals((object)['relation'=>'Al', 'rel'=>'partner'], $relations[2]);
        
        
        // testing websites
        $webs = $this->contact->websites;
        $this->assertCount(3, $webs);
        $this->assertEquals((object)['href'=>'http://blog.user.com', 'primary'=>true, 'rel'=>'blog'], $webs[0]);
        $this->assertEquals((object)['href'=>'http://me.homepage.com', 'primary'=>false, 'label'=>'Testing site'], $webs[1]);
        $this->assertEquals((object)['href'=>'http://work.office.net', 'primary'=>false, 'rel'=>'work'], $webs[2]);
        
         
        // testing userdefinedfields
        $ufields = $this->contact->userDefinedFields;
        $this->assertCount(1, $ufields);
        
        // we check that the assignment $this->contact->userDefinedFields = [(object)['key'=>'key0', 'value'=>'value0']] convert the array to an ArrayProperty
        $this->assertInstanceOf(ArrayProperty::class, $ufields); 
        $this->assertEquals((object)['key'=>'key0', 'value'=>'value0'], $ufields[0]);

		
        // testing extendedProperties
        $epfields = $this->contact->extendedProperties;
        $this->assertCount(1, $epfields);
       
        // we check that the assignment $this->contact->extendedProperties = [(object)['name'=>'hobby', 'value'=>'music']] convert the array to an ArrayProperty
        $this->assertInstanceOf(ArrayProperty::class, $epfields); 
        $this->assertEquals((object)['name'=>'hobby', 'value'=>'music'], $epfields[0]);
        
                 
        // testing groupsMembershipInfo
        $groups = $this->contact->groupsMembershipInfo;
        $this->assertCount(2, $groups);
        $this->assertEquals('http://www.google.com/m8/feeds/groups/userEmail/base/groupId', $groups[0]);
        $this->assertEquals('http://www.google.com/m8/feeds/groups/userEmail/base/groupId2', $groups[1]);
        
        
        return $this->contact;
    }    
    
    

    /**
     * @depends testUpdateContact
     */
    public function testAsxml(Contact $contact)
    {
        // get xml string
        $xml = $contact->asXml();
        $expected = <<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>UPDATED title</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>UPDATED content</content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="liz@gmail.com" displayName="E. Bennet"/>
    <gd:email label="sweet home"
        address="liz@example.org"/>
    <gd:email rel="http://schemas.google.com/g/2005#home"
        address="lizzie@gmail.com"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#work" primary="true">(206)555-1212</gd:phoneNumber>
    <gd:phoneNumber label="sweet home">(206)555-1213</gd:phoneNumber>
    <gd:phoneNumber label="my old pager">09090909</gd:phoneNumber>
    <gd:im address="liz@gmail.com"
        protocol="http://schemas.google.com/g/2005#GOOGLE_TALK"
        primary="true"
        rel="http://schemas.google.com/g/2005#home"/>
    <gd:im address="liz.work@gmail.com"
        protocol="http://schemas.google.com/g/2005#SKYPE"
        rel="http://schemas.google.com/g/2005#work"/>
    <gd:im address="lizzie@gmail.com"
        protocol="http://schemas.google.com/g/2005#GOOGLE_TALK"
        rel="http://schemas.google.com/g/2005#home"/>
    <gd:extendedProperty name="hobby" value="music" />
    <gd:structuredPostalAddress
          rel="http://schemas.google.com/g/2005#work"
          primary="true">
        <gd:city>Mountain View</gd:city>
        <gd:street>1600 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>1600 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gContact:event rel="anniversary">
        <gd:when startTime="2000-01-01" />
    </gContact:event>
    <gContact:event rel="other">
        <gd:when startTime="2000-12-31" />
    </gContact:event>
    <gContact:relation rel="assistant">Suzy</gContact:relation>
    <gContact:relation rel="manager">Boss</gContact:relation>
    <gContact:relation rel="partner">Al</gContact:relation>
    <gContact:website href="http://blog.user.com" primary="true" rel="blog" />
    <gContact:website href="http://me.homepage.com" label="Testing site" />
    <gContact:website href="http://work.office.net" rel="work" />
    <gContact:userDefinedField key="key0" value="value0" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId2'/>
</entry>
XML;
                

        $expected_o = simplexml_load_string($expected);
		$xml_o = simplexml_load_string($xml);
        
		
		// checking simple values
		$this->assertEquals((string)$expected_o->content, (string)$xml_o->content);		
		$this->assertEquals((string)$expected_o->title, (string)$xml_o->title);
		$this->assertEquals((string)$expected_o->id, (string)$xml_o->id);
		$this->assertEquals((string)$expected_o->updated, (string)$xml_o->updated);
		
		
		// checking multiple values - LINKS
		$i = 0;
		foreach ( $expected_o->link as $expected )
		{
			$x = $xml_o->link[$i];
			$this->assertEquals((string)$expected->rel, (string)$x->rel);
			$this->assertEquals((string)$expected->type, (string)$x->type);
			$this->assertEquals((string)$expected->href, (string)$x->href);
			$i++;
		}
		

		// checking multiple values - EMAILS
		$i = 0;
		foreach ( $expected_o->children('gd', true)->email as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gd', true)->email[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$this->assertEquals((string)$expected_attr->address, (string)$x_attr->address);
			if ( $expected_attr->primary )
				$this->assertEquals((string)$expected_attr->primary, (string)$x_attr->primary);
			else
				$this->assertEquals('false', (string) $x_attr->primary);
			$i++;
		}
		

		// checking multiple values - PHONENUMBERS
		$i = 0;
		foreach ( $expected_o->children('gd', true)->phoneNumber as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gd', true)->phoneNumber[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$this->assertEquals((string)$expected, (string)$xml_o->children('gd', true)->phoneNumber[$i]);
			if ( $expected_attr->primary )
				$this->assertEquals((string)$expected_attr->primary, (string)$x_attr->primary);
			else
				$this->assertEquals('false', (string) $x_attr->primary);
			$i++;
		}
		

		// checking multiple values - IMS
		$i = 0;
		foreach ( $expected_o->children('gd', true)->im as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gd', true)->im[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$this->assertEquals((string)$expected_attr->protocol, (string)$x_attr->protocol);
			$this->assertEquals((string)$expected_attr->address, (string)$x_attr->address);
			if ( $expected_attr->primary )
				$this->assertEquals((string)$expected_attr->primary, (string)$x_attr->primary);
			else
				$this->assertEquals('false', (string) $x_attr->primary);
			$i++;
		}
		

		// checking multiple values - STRUCTUREDPOSTALADDRESSES
		$i = 0;
		foreach ( $expected_o->children('gd', true)->structuredPostalAddress as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gd', true)->structuredPostalAddress[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));

            $gd_expected = $expected->children('gd', true);
            $gd_x = $xml_o->children('gd', true)->structuredPostalAddress[$i]->children('gd', true);
            
            $this->assertEquals((string)$gd_expected->city, (string)$gd_x->city);
            $this->assertEquals((string)$gd_expected->street, (string)$gd_x->street);
            $this->assertEquals((string)$gd_expected->region, (string)$gd_x->region);
            $this->assertEquals((string)$gd_expected->postcode, (string)$gd_x->postcode);
            $this->assertEquals((string)$gd_expected->country, (string)$gd_x->country);
            $this->assertEquals((string)$gd_expected->formattedAddress, (string)$gd_x->formattedAddress);
            
            
            if ( $expected_attr->primary )
				$this->assertEquals((string)$expected_attr->primary, (string)$x_attr->primary);
			else
				$this->assertEquals('false', (string) $x_attr->primary);
			$i++;
		}
        

        // checking multiple values - EVENTS
		$i = 0;
		foreach ( $expected_o->children('gContact', true)->event as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gContact', true)->event[$i]->attributes();

            $gd_expected = $expected->children('gd', true);
            $gd_x = $xml_o->children('gContact', true)->event[$i]->children('gd', true);
            $this->assertEquals((string)$gd_expected->when, (string)$gd_x->when);
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$i++;
		}
		

		// checking multiple values - RELATIONS
		$i = 0;
		foreach ( $expected_o->children('gContact', true)->relation as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gContact', true)->relation[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$this->assertEquals((string)$expected, (string)$xml_o->children('gContact', true)->relation[$i]);
			$i++;
		}
		

		// checking multiple values - WEBSITES
		$i = 0;
		foreach ( $expected_o->children('gContact', true)->website as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gContact', true)->website[$i]->attributes();
            
			$this->assertEquals((string)($expected_attr->rel?$expected_attr->rel:$expected_attr->label), (string)($expected_attr->rel?$x_attr->rel:$x_attr->label));
			$this->assertEquals((string)$expected_attr->href, (string)$x_attr->href);
            if ( $expected_attr->primary )
				$this->assertEquals((string)$expected_attr->primary, (string)$x_attr->primary);
			else
				$this->assertEquals('false', (string) $x_attr->primary);
			$i++;
		}
		

		// checking multiple values - USERDEFINEDFIELDS
		$i = 0;
		foreach ( $expected_o->children('gContact', true)->userDefinedField as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gContact', true)->userDefinedField[$i]->attributes();
            
			$this->assertEquals((string)$expected_attr->key, (string)$x_attr->key);
			$this->assertEquals((string)$expected_attr->value, (string)$x_attr->value);
			$i++;
		}
		

		// checking multiple values - EXTENDEDPROPERTIES
		$i = 0;
		foreach ( $expected_o->children('gd', true)->extendedProperty as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gd', true)->extendedProperty[$i]->attributes();

            $this->assertEquals((string)$expected_attr->name, (string)$x_attr->name);
			$this->assertEquals((string)$expected_attr->value, (string)$x_attr->value);
			$i++;
		}
		

		// checking multiple values - GROUPSMEMBERSHIPINFO
		$i = 0;
		foreach ( $expected_o->children('gContact', true)->groupMembershipInfo as $expected )
		{
			$expected_attr = $expected->attributes();
			$x_attr = $xml_o->children('gContact', true)->groupMembershipInfo[$i]->attributes();
            
			$this->assertEquals((string)$expected_attr->href, (string)$x_attr->href);
			$i++;
		}
	}
}


?>