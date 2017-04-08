<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\CloudPrint_Service;




class CloudPrint_ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty1()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new CloudPrint_Service($stub_client);
				
        $service->printers = null;
    }
	
	
	
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty2()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new CloudPrint_Service($stub_client);
				
        $service->jobs = null;
    }


	
	
	public function testSendRequest()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('{"success":true, "printer":{"id":"123", "title":"prn1"}}');

		
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
									'headers' => ['X-header-test'=>1234]
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->sendRequest('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\Stdclass::class, $resp);
		$this->assertEquals('123', (string)$resp->printer->id);
	}       
	

	
		
	
}

?>