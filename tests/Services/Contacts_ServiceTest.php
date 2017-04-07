<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\Contacts_Service;




class Contacts_ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty1()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->contacts = null;
    }
	
	

    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty2()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->contacts_photos = null;
    }
	
	

    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty3()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->groups = null;
    }
		

	
	public function testSendRequestRaw()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('plain text here');
		$stub_guzzle_response->method('getHeader')->willReturn(['text/plain']);	
		$stub_guzzle_response->expects($this->once())->method('getHeader')->with('Content-Type');

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'X-header-test'=>1234]
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->sendRequestRaw('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Misc\Payload::class, $resp);
		$this->assertEquals('plain text here', $resp->body);
		$this->assertEquals('text/plain', $resp->contentType);
	}       
		

	
	public function testSendRequest()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('<entry><item>123</item></entry>');

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'X-header-test'=>1234]
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->sendRequest('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\SimpleXMLElement::class, $resp);
		$this->assertEquals('123', (string)$resp->item);
	}       
	

	
	public function testGroupsGetList()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
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
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/me%40gmail.com/full'), 
						$this->equalTo(
								array(
									'query'=> ['q'=>'john'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->getList('me@gmail.com', ['q'=>'john']);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\ListGroups::class, $resp);
		
		// checking 2 groups
		$this->assertCount(2, $resp);
		$resp->rewind();
		$group1 = $resp->current();
		$resp->next();
		$group2 = $resp->current();
		
		$this->assertEquals('System Group: My Contacts', $group1->title);
		$this->assertEquals('joggers', $group2->title);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
	}
	

	
	public function testGroupsGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my group</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->get('http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('my group', $resp->title);
		$this->assertEquals('"my etag"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
		return $resp;
	}       
	

	
	public function testGroupsCreate()
	{
		// creating a group
		$group = new \Nettools\GoogleAPI\Services\Contacts\Group();
		$group->title = "group title";
				
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>group title</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/default/full'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml'],
									'body'=>$group->asXml()
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->create($group);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('group title', $resp->title);
		$this->assertEquals('"my etag"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
	}       
		

	
	/**
	 * @depends testGroupsGet
	 */
	public function testGroupsUpdate($group)
	{
		// modifying group
		$group->title = "updated title";
		
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag2"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>updated title</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-05-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/userEmail/full/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml', 'If-Match'=>'"my etag"'],
									'body'=>$group->asXml()
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->update($group);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('updated title', $resp->title);
		$this->assertEquals('"my etag2"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
		return $resp;
	}       
	

	
	/**
	 * @depends testGroupsUpdate
	 */
	public function testGroupsDelete($group)
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/userEmail/full/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag2"']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->delete($group->linkRel('edit')->href, $group->etag);
		$this->assertEquals(true, $resp);
		
	}       
	

	
	public function testPhotoGet()
	{
		$img = 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAACnVBMVEUAAAC3NyeCSxQAAAAAAADSZiMAAAB4v+7MWCcEAAC+jTx+xPHOcChVpt6iJSNsuuxsuuzXXydsuuzGTieLIyB4GhpuHxxxvOxqGRdRExK4OicLAgItCgrxxx3edijghieJzfV8yPbc1L2bgUWTPy+wMCd8t+LbZifGmC/w311teZvJTyiXMCfGYjPOVCjG19lWl9OEHB3CSijntyvmmSe1NieBIBy0QCW+TCifPS3HkWWZIyJoreGezu3cayfZrUmqKya/RCj75BK9m4NLDxHUaijggCj86Q7gfyqzYCnDVSq9hiw2DgsuDAuzQSnfcCeTMh6IwOlgFxbVWyfXYifISifRUSfBQSjSVSctVp7YbCjYZifUVyfMTCcdG027OyevLSZYsOhQrOMuf8QydLo0arP875MoRIYlOXgqndolj88xX6glRY0iM28fJlwqHEXIaS6VOC7glSjdgijadCjbbijGRijJVidetOlCqOJJqOE5pN4kmtk2jc1DickzYq1XcqIqS5AlQIOvroAzQXb56W777GYiLWMfIVU2Mk+8qkkbFkVdNz+kRzOORjHHXi+xUi702y3RbCvbeingnyjfjijjgCi9RijjmibmqiTvxR1Dodq8yNWQss5Nks6Mpc0qhskwk8dCnMVfkMX788NchsA0iryxv7pirLnd3LVKj6/z661SeKw7Y6dGaqZMeaJ8hqAoUZ3r4pvfx5paf5ppj5EsTZCtk4yBqYr/8XxcZHd7cXY7SXJLUm2PdGV3dV1LSFvRf1lqTln87VT861CNXkj01kP540GfYEAkG0Ctdz3NdD19bTxTHjy/lTtsODq3eTjNiDbMdjVpWzVwJzWUVjH00DCqVS+rhC3UjizVgirehynpvCLqsiL12RyCIu6zAAAAU3RSTlMA/gQcERAIRy8m/TL++vbz69nRxMKrqJmXc09CPDErHRcJ/ff08/Ls6ufm5ubl5dzb0c7KxcG5uLe2sqyqoZqTjo2LioiGfGVWVFJRTEw/PTQpHrmhBvoAAAIFSURBVCjPYoADBW52dm4FBjSgyC4uJMDPLyAkzq6IEGVi4LbhrUpKrKhITOJVsw2Eict5sBvU1S5akZSYmFQ1e95cfWdWiISrrlbD+q1dO6orK6sza+sWL1eykgcbZBFacOnilK6FNZmZNVnJKUvnrN5szwSUYBUOKS0pnsG1ZdWyrKwFySmp85ekacoCJfzVo6NKiot4+k/vTgYKp2avW5l22B0oYRcSElUyeeqtg9vXpKRmR0ZGbmve2ebEwCAvGBwSHTX59ox9a+vrsyMb09MbmlvTjDkY5AQjgkOiogpO7doYCRRuysjdsKktjcuTgUMvLDgkZHpvS3pOTg5QuD1v/5HjrcregDFwaMdGBJeH9h/KyGjZuwco3nHm2MSJGrJA15aFxUQER086cfTCTa4DeXGd567dm2UC9LxjeGhYTDDQBSHlhT2dHXGX7866M8UN6FwpRrBMeeGVs/lxcXEnb8ycWcTjBfK5KWN4LFBmwvn87u78nqtFU4sLVPwYQFpUQXoigoMn9PVdnzRtWlR0ryUrOBQdEhjDy0CaQkKiS0tDQiKMOCDhHmSdwBgfHhobFgF0RHBEqHkANAJZZET5QFJloaGxsdMLRWRYmCAybMzSLoZ8CUC5+HhGHTFfZjZY5LKxcEpLSoiKmIlJSPpwskDEocaxsTAzc3IyM7OwQY0BANeqq70ox7BgAAAAAElFTkSuQmCC';
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn($img);
		$stub_guzzle_response->method('getHeader')->willReturn(['image/png']);	
		$stub_guzzle_response->expects($this->once())->method('getHeader')->with('Content-Type');		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts_photos->get('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Photo::class, $resp);
		
		$this->assertEquals($img, $resp->body);
		$this->assertEquals('image/png', $resp->contentType);
		

		return $resp;
	}       
	

	
	public function testPhotoUpdate()
	{
		$img = 'xxxxxxx';
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'body' => $img,
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"etag-photo"', 'Content-Type'=>'image/jpeg']
								)
							)
					);
				
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$photo = \Nettools\GoogleAPI\Services\Contacts\Photo::fromData('image/jpeg', $img);
		$resp = $service->contacts_photos->update($photo, 'https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId', '"etag-photo"');
		$this->assertEquals(true, $resp);
	}       
	

	
	public function testPhotoDelete()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'*']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts_photos->delete('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId');	// using generic etag *
		$this->assertEquals(true, $resp);
	}       
	

	
	public function testContactsGetList()
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
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/me%40gmail.com/full'), 
						$this->equalTo(
								array(
									'query'=> ['q'=>'john', 'max-results' => '10000'],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->getList('me@gmail.com', ['q'=>'john']);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\ListContacts::class, $resp);
		
		// checking 2 contacts
		$this->assertCount(2, $resp);
		$resp->rewind();
		$contact1 = $resp->current();
		$resp->next();
		$contact2 = $resp->current();

		$this->assertEquals('Fitzwilliam Darcy', $contact1->title);
		$this->assertEquals('John Darcy', $contact2->title);
		
		
		// other properties from XML have already been tested thouroughly in ListContactsTest
	}
	

	
	public function testContactsGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
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
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->get('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		
		$this->assertEquals('my contact', $resp->title);
		
		
		// other properties from XML have already been tested thouroughly in ContactTest
		return $resp;		
	}
	

	
	/**
	 * @depends testContactsGet
	 */	
	public function testContactsUpdate($contact)
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my contact renamed</title>
    <id>my id</id>
    <updated>2017-05-01</updated>
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
    <gContact:userDefinedField key="key1" value="value1" />
    <gContact:userDefinedField key="key2" value="value2" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
</entry>
XML
			);
		
		
		// modifying contact
		$contact->title = 'my contact renamed';
		$contact->relations = [];
		$contact->websites = [];
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'body' => $contact->asXml(),
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag"', 'Content-Type'=>'application/atom+xml']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->update($contact);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		
		$this->assertEquals('my contact renamed', $resp->title);
		
		
		// other properties from XML have already been tested thouroughly in ContactTest
		return $resp;		
	}
	

	
	public function testContactsCreate()
	{
		// creating a contact
		$contact = new \Nettools\GoogleAPI\Services\Contacts\Contact();
		$contact->title = 'john';
		$contact->emails[] = (object)['address'=>'john@mail.com', 'rel'=>'http://schemas.google.com/g/2005#work', 'primary'=>'true'];
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>john</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content></content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="john@mail.com" />

</entry>
XML
			);
		
		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'body' => $contact->asXml(),
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->create($contact);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		$this->assertEquals('john', $resp->title);
		$this->assertCount(1, $resp->emails);
		$this->assertEquals('my id', $resp->id);
		$this->assertCount(3, $resp->links);
	}
	

	
	public function testContactsDelete()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		
		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag to delete"']
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->delete('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId', '"my etag to delete"');
		$this->assertEquals(true, $resp);
	}
		
	
}

?>