<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Tools\ContactsSyncManager\Manager;
use \Nettools\GoogleAPI\Tools\ContactsSyncManager\ClientInterface;



class ManagerTest extends \PHPUnit\Framework\TestCase
{
	private function __guzzleResponseContacts()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
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
  <entry gd:etag="etag-0">
    <id>http://www.google.com/m8/feeds/contacts/userEmail/base/123456</id>
    <updated>2008-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2008-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>John Doe</title>
    <gd:name>
      <gd:fullName>John Doe</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/123456"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/123456"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/123456"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">456</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="hamster"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/userEmail/base/groupId"/>
  </entry>
</feed>
XML
			);
		
		
		return $stub_guzzle_response;
	}
	
	

	private function __guzzleResponseDeletedContacts()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:openSearch="http://a9.com/-/spec/opensearch/1.1/"
    xmlns:gContact="http://schemas.google.com/contact/2008"
    xmlns:batch="http://schemas.google.com/gdata/batch"
    xmlns:gd="http://schemas.google.com/g/2005"
    gd:etag="feedEtag">
  <id>me@gmail.com</id>
  <updated>2008-12-10T10:04:15.446Z</updated>
  <category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact"/>
  <link rel="http://schemas.google.com/g/2005#feed" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full"/>
  <link rel="http://schemas.google.com/g/2005#post" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full"/>
  <link rel="http://schemas.google.com/g/2005#batch" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/batch"/>
  <link rel="self" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full?max-results=25"/>
  <author>
    <name>User</name>
    <email>me@gmail.com</email>
  </author>
  <generator version="1.0" uri="http://www.google.com/m8/feeds">
    Contacts
  </generator>
  <openSearch:totalResults>1</openSearch:totalResults>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <entry gd:etag="etag-0">
    <id>http://www.google.com/m8/feeds/contacts/me@gmail.com/base/123456</id>
    <updated>2008-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2008-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>John Doe</title>
    <gd:name>
      <gd:fullName>John Doe</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/me@gmail.com/123456"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">456</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="hamster"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/me@gmail.com/base/groupId"/>
  </entry>
  <entry gd:etag="etag-0">
    <id>http://www.google.com/m8/feeds/contacts/me@gmail.com/base/7890123</id>
    <gd:deleted />
  </entry>
</feed>
XML
			);
		
		
		return $stub_guzzle_response;
	}
	
	

	private function __guzzleResponseContact()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"etag1"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>John Doe</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <gd:name>
      <gd:fullName>John Doe</gd:fullName>
    </gd:name>
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
    <gd:extendedProperty name="pet" value="hamster" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
</entry>
XML
          );

		return $stub_guzzle_response;
	}
	
	

    // test constructor optionnal parameters
	public function testManagerConstructorParams()
	{
        $stub_client = $this->createMock(\Google_Client::class);
        
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        
        
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_TO_GOOGLE,
                            array(
                                'user'  => 'user@gmail.com',
                                'group' => 'my group'
                            )
                        );
        
        $this->assertEquals('user@gmail.com', $m->user);
        $this->assertEquals(Manager::ONE_WAY_TO_GOOGLE, $m->kind);
        $this->assertEquals('my group', $m->group);
        
        
        // test default parameters
        $m = new Manager($stub_client, $cintf, 0);
        
        $this->assertEquals('default', $m->user);
        $this->assertEquals(NULL, $m->group);
	}
    

	
	
	
	/*
	 *
	 * ============ FROM GOOGLE -> CLIENTSIDE ===========
	 *
	 */
	

	public function testManagerFromGoogleNoSyncNeeded()
	{
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'query'=> ['updated-min'=>0, 'max-results'=>'10000'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)->willReturn($this->__guzzleResponseContacts());

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get Contact info clientside
        $cintf->method('getContactInfoClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn((object)['etag'=>'etag-0', 'clientsideUpdateFlag'=>false]);
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_FROM_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}
    

	public function testManagerFromGoogleSyncNeededOK()
	{
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'query'=> ['updated-min'=>0, 'max-results'=>'10000'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)->willReturn($this->__guzzleResponseContacts());

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get Contact info clientside
        $cintf->method('getContactInfoClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn((object)['etag-updated'=>'etag-0', 'clientsideUpdateFlag'=>false]);
                
        // update Contact clientside
        $cintf->expects($this->once())->method('updateContactClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn(true);
                

		$m = new Manager($stub_client, $cintf, Manager::ONE_WAY_FROM_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}
    

	public function testManagerFromGoogleSyncNeededKO()
	{
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'query'=> ['updated-min'=>0, 'max-results'=>'10000'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)->willReturn($this->__guzzleResponseContacts());

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get Contact info clientside
        $cintf->method('getContactInfoClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn((object)['etag-updated'=>'etag-0', 'clientsideUpdateFlag'=>false]);
                
        // update Contact clientside
        $cintf->method('updateContactClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn(false);
                

		$m = new Manager($stub_client, $cintf, Manager::ONE_WAY_FROM_GOOGLE);

		// the updateContactClientside function returned false, so we have a sync error
        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
		$this->assertEquals(0, $r);
	}
	
	
	
	
	/*
	 *
	 * ============ CLIENTSIDE -> TO GOOGLE ===========
	 *
	 */	

	public function testManagerToGoogleNoSyncNeeded()
	{
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get a list of updated contacts clientside
        $cintf->method('getUpdatedContactsClientside')
            // checking type of argument
            ->with($this->isInstanceOf(\Nettools\GoogleAPI\Services\Contacts_Service::class))
            ->willReturn([]);
		
		
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		
		
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_TO_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}

	
	public function testManagerToGoogleSyncNeededOK()
	{
		$newc = new Contact();
		$newc->title = 'John Doe';
		$newc->familyName = 'Doe';
		$newc->givenName = 'John';

$updxml = <<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"etag1"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>Marty Doe</title>
    <id>http://www.google.com/m8/feeds/contacts/me@gmail.com/base/123456</id>
    <updated>2017-04-01</updated>
    <content>update here</content>
    <gd:name>
      <gd:fullName>John Doe</gd:fullName>
    </gd:name>
	<link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/me@gmail.com/full/123456"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/me@gmail.com/base/groupId'/>
</entry>
XML;

		$updc = Contact::fromFeed(simplexml_load_string($updxml));
		
		
		// creating stub for guzzle response for updated contact ; response is OK (http 200)
		$stub_guzzle_response_upd = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response_upd->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response_upd->method('getBody')->willReturn($updxml);
		
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe / Marty Doe']);
        
        // get a list of updated contacts clientside
        $cintf->method('getUpdatedContactsClientside')
            // checking type of argument
            ->with($this->isInstanceOf(\Nettools\GoogleAPI\Services\Contacts_Service::class))
            ->willReturn([(object)['contact'=>$newc], (object)['contact'=>$updc, 'etag'=>'"etag1"']]);
		
		// acknowledge contact updated on google
        $cintf->method('acknowledgeContactUpdatedGoogleside')
            // checking type of argument
            ->withConsecutive(
				[
					$this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
							function($contact)
							{
								// here, we check that the $contact comes with links provided by google, whereas the Contact object provided by
								// the ClientInterface doesn't have any link properties (new contact created from scratch)
								return ($contact->title == 'John Doe') && ($contact->linkRel('edit')->href);
							}
						))
				],
				[
					$this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
							function($contact)
							{
								return ($contact->title == 'Marty Doe') && ($contact->linkRel('edit')->href);
							}
						))
				])
            ->willReturn(true);

		
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->exactly(2))->method('request')
					->withConsecutive(
						[
							$this->equalTo('post'), 
							$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
							$this->equalTo(
									array(
										'connect_timeout' => 5.0,
										'timeout' => 30,
										'body' => $newc->asXml(),
										'headers' => ['GData-Version'=>'3.0', 'Content-Type'  => 'application/atom+xml']
									)
								)
						],
						[
							$this->equalTo('put'), 
							$this->equalTo('https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456'), 
							$this->equalTo(
									array(
										'connect_timeout' => 5.0,
										'timeout' => 30,
										'body' => $updc->asXml(),
										'headers' => ['If-Match'=>'*', 'GData-Version'=>'3.0', 'Content-Type'  => 'application/atom+xml']
									)
								)
						]
					)->will($this->onConsecutiveCalls($this->__guzzleResponseContact(), $stub_guzzle_response_upd));

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_TO_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}
    

	
	public function testManagerToGoogleSyncNeededEtagMismatch()
	{
		$newc = new Contact();
		$newc->title = 'John Doe';
		$newc->familyName = 'Doe';
		$newc->givenName = 'John';

$updxml = <<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"etag1"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>Marty Doe</title>
    <id>http://www.google.com/m8/feeds/contacts/me@gmail.com/base/123456</id>
    <updated>2017-04-01</updated>
    <content>update here</content>
    <gd:name>
      <gd:fullName>John Doe</gd:fullName>
    </gd:name>
	<link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/me@gmail.com/123456"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456"/>
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/me@gmail.com/base/groupId'/>
</entry>
XML;

		$updc = Contact::fromFeed(simplexml_load_string($updxml));
		
		
		// creating stub for guzzle response for updated contact ; response is OK (http 200)
		$stub_guzzle_response_upd = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response_upd->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response_upd->method('getBody')->willReturn($updxml);
		
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe / Marty Doe']);
        
        // get a list of updated contacts clientside
        $cintf->method('getUpdatedContactsClientside')
            // checking type of argument
            ->with($this->isInstanceOf(\Nettools\GoogleAPI\Services\Contacts_Service::class))
            ->willReturn([(object)['contact'=>$newc], (object)['contact'=>$updc, 'etag'=>'"etag0000"']]);
		
		// acknowledge contact updated on google
        $cintf->method('acknowledgeContactUpdatedGoogleside')
            // checking type of argument
            ->withConsecutive(
				[
					$this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
							function($contact)
							{
								// here, we check that the $contact comes with links provided by google, whereas the Contact object provided by
								// the ClientInterface doesn't have any link properties (new contact created from scratch)
								return ($contact->title == 'John Doe') && ($contact->linkRel('edit')->href);
							}
						))
				],
				[
					$this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
							function($contact)
							{
								return ($contact->title == 'Marty Doe') && ($contact->linkRel('edit')->href);
							}
						))
				])
            ->willReturn(true);

		
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')
					->with(
							$this->equalTo('post'), 
							$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
							$this->equalTo(
									array(
										'connect_timeout' => 5.0,
										'timeout' => 30,
										'body' => $newc->asXml(),
										'headers' => ['GData-Version'=>'3.0', 'Content-Type'  => 'application/atom+xml']
									)
								)
					)->willReturn($this->__guzzleResponseContact());	// the request is called only once because the contact update fails (etags mismatch)

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_TO_GOOGLE);

		// the sync fails because etags mismatch
        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(false, $r);
	}
    

	
	public function testManagerToGoogleSyncNeededKO()
	{
		$newc = new Contact();
		$newc->title = 'John Doe';
		$newc->familyName = 'Doe';
		$newc->givenName = 'John';
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get a list of updated contacts clientside
        $cintf->method('getUpdatedContactsClientside')
            // checking type of argument
            ->with($this->isInstanceOf(\Nettools\GoogleAPI\Services\Contacts_Service::class))
            ->willReturn([(object)['contact'=>$newc]]);
		
		// acknowledge contact updated on google
        $cintf->method('acknowledgeContactUpdatedGoogleside')
            // checking type of argument
            ->with($this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
						function($contact)
						{
							return ($contact->title == 'John Doe') && ($contact->linkRel('edit')->href);
						}
					)))
            ->willReturn(false);

		
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'body' => $newc->asXml(),
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'  => 'application/atom+xml']
								)
							)
					)->willReturn($this->__guzzleResponseContact());

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_TO_GOOGLE);

		// the acknowledgeContactUpdatedGoogleside function returned false, so we have a sync error
		$r = $m->sync(new \Psr\Log\NullLogger(), 0);
		$this->assertEquals(false, $r);
	}
    

	
		
	
	/*
	 *
	 * ============ CLIENTSIDE -> TO GOOGLE DELETIONS ===========
	 *
	 */	

	public function testManagerDeleteToGoogleNoSyncNeeded()
	{
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get a list of updated contacts clientside
        $cintf->method('getDeletedContactsClientside')
            ->willReturn([]);
		
		
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		
		
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_DELETE_TO_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}

	
	public function testManagerDeleteToGoogleSyncNeededOK()
	{
		// creating stub for guzzle response for deleted contact ; response is OK (http 200)
		$stub_guzzle_response_del = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response_del->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response_del->method('getBody')->willReturn('');
		
		
		// creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe deletion']);
        
        // get a list of deleted contacts clientside
        $cintf->method('getDeletedContactsClientside')
            ->willReturn(['https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456']);
		
		// acknowledge contact updated on google
        $cintf->method('acknowledgeContactDeletedGoogleside')
            // checking type of argument
            ->with($this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
						function($contact)
						{
							return ($contact->linkRel('edit')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456')
									&&
									($contact->linkRel('self')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456')
									&&
									($contact->id == 'http://www.google.com/m8/feeds/contacts/me@gmail.com/base/123456');
						}
					)))
            ->willReturn(true);

		
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')
					->with(
							$this->equalTo('delete'), 
							$this->equalTo('https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456'), 
							$this->equalTo(
									array(
										'connect_timeout' => 5.0,
										'timeout' => 30,
										'headers' => ['If-Match'=>'*', 'GData-Version'=>'3.0']
									)
								)
						)
					->willReturn($stub_guzzle_response_del);

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_DELETE_TO_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}

	
	public function testManagerDeleteToGoogleSyncNeededKO()
	{
		// creating stub for guzzle response for deleted contact ; response is OK (http 200)
		$stub_guzzle_response_del = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response_del->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response_del->method('getBody')->willReturn('');
		
		
		// creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe deletion']);
        
        // get a list of deleted contacts clientside
        $cintf->method('getDeletedContactsClientside')
            ->willReturn(['https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456']);
		
		// acknowledge contact updated on google
        $cintf->method('acknowledgeContactDeletedGoogleside')
            // checking type of argument
            ->with($this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
						function($contact)
						{
							return $contact->linkRel('edit')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456'
									&&
									$contact->linkRel('self')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456'
									&&
									$contact->id == 'http://www.google.com/m8/feeds/contacts/me@gmail.com/base/123456';
						}
					)))
            ->willReturn(false);

		
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')
					->with(
							$this->equalTo('delete'), 
							$this->equalTo('https://www.google.com/m8/feeds/contacts/me@gmail.com/full/123456'), 
							$this->equalTo(
									array(
										'connect_timeout' => 5.0,
										'timeout' => 30,
										'headers' => ['If-Match'=>'*', 'GData-Version'=>'3.0']
									)
								)
						)
					->willReturn($stub_guzzle_response_del);

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
                
        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_DELETE_TO_GOOGLE);

		// the acknowledgeContactDeletedGoogleside function returned false, so we have a sync error
        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
		$this->assertEquals(false, $r);	// 1 sync, 1 error
	}
    

	
	
	
	/*
	 *
	 * ============ DELETE FROM GOOGLE -> CLIENTSIDE ===========
	 *
	 */

	public function testManagerDeleteFromGoogle()
	{
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'query'=> ['updated-min'=>0, 'max-results'=>'10000', 'showdeleted'=>'true'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)->willReturn($this->__guzzleResponseDeletedContacts());

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn(['John Doe']);
        
        // get Contact info clientside
        $cintf->method('getContactInfoClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn((object)['etag'=>'etag-0', 'clientsideUpdateFlag'=>false]);

		
        // delete Contact clientside ; called only once, even if 2 contacts are in the feed (only 1 has the deleted flag)
        $cintf->expects($this->once())->method('deleteContactClientside')
            // checking type of argument
            ->with($this->logicalAnd($this->isInstanceOf(Contact::class), $this->callback(
						function($contact)
						{
							return $contact->linkRel('edit')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/7890123'
									&&
									$contact->linkRel('self')->href == 'https://www.google.com/m8/feeds/contacts/me@gmail.com/full/7890123'
									&&
									$contact->id == 'http://www.google.com/m8/feeds/contacts/me@gmail.com/base/7890123';
						}
					)))
            ->willReturn(true);
                

        $m = new Manager($stub_client, $cintf, Manager::ONE_WAY_DELETE_FROM_GOOGLE);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}
    
	
    		
}

?>