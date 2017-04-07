<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\Service;



class ServiceTest extends \PHPUnit\Framework\TestCase
{
    protected $stub;
    
    
    
    public function setUp()
    {
        $client = $this->createMock(\Google_Client::class);
        $this->stub = $this->getMockBuilder(Service::class)->setConstructorArgs(array($client))->setMethods(['_getException'])->getMock();
    }
    
    
    public function testService()
    {
        // test access to protected properties
        $this->assertInstanceOf(\Google_Client::class, $this->stub->client);
        $this->assertEquals(5.0, $this->stub->connectTimeout);
        $this->assertEquals(30, $this->stub->timeout);
    }
    
    
    
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testInexistentProperty()
    {
        $x = $this->stub->not_a_property;
    }
    
        
    
    public function testProperties()
    {
        $this->stub->connectTimeout = 6.0;
        $this->stub->timeout = 20;
        
        $this->assertEquals(6.0, $this->stub->connectTimeout);
        $this->assertEquals(20, $this->stub->timeout);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $this->stub->client = NULL; // read-only property : exception thrown
    }
    
	
	
	public function testSendRequest()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('body');

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'query'=> ['q'=>'john', 'max-results'=>100],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		// creating service stub : implementing only abstract methods
        $stub_service = $this->getMockBuilder(Service::class)->setConstructorArgs(array($stub_client))->setMethods(['_getException'])->getMock();
		
		// sending request
		$resp = $stub_service->sendRequest('get', 'my.url.com', array('query'=>['q'=>'john', 'max-results'=>100]));
		$this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $resp);
		$this->assertEquals('body', $resp->getBody());
	}
    
	
	
	public function testSendRequestOverrideTimeout()
	{
		// creating stub for guzzle response ; response is OK (http 201)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(201);

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
									'timeout' => 10
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		// creating service stub : implementing only abstract methods
        $stub_service = $this->getMockBuilder(Service::class)->setConstructorArgs(array($stub_client))->setMethods(['_getException'])->getMock();
		
		// sending request
		$resp = $stub_service->sendRequest('get', 'my.url.com', array('timeout'=>10));
	}
       
	
    
	/**
	* @expectedException \Nettools\GoogleAPI\Exceptions\Exception
	*/
	public function testSendRequestUnsuccessfull()
	{
		// creating stub for guzzle response ; response is not OK (http 500)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(500);

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		// creating service stub : implementing only abstract methods
        $stub_service = $this->getMockBuilder(Service::class)->setConstructorArgs(array($stub_client))->setMethods(['_getException'])->getMock();
		$stub_service->method('_getException')->willReturn(new \Nettools\GoogleAPI\Exceptions\Exception('Test error'));
		
		// sending request
		$resp = $stub_service->sendRequest('get', 'my.url.com');
	}
       
       
       
}

?>