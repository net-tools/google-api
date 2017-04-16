<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Tools\ContactsSyncManager\Manager;
use \Nettools\GoogleAPI\Tools\ContactsSyncManager\ClientInterface;



class ManagerTest extends \PHPUnit\Framework\TestCase
{

    // test constructor optionnal parameters
	public function testManagerConstructorParams()
	{
        $stub_client = $this->createMock(\Google_Client::class);
        
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        
        
        $m = new Manager($stub_client, $cintf,
                            array(
                                'user'  => 'user@gmail.com',
                                'kind'  => Manager::ONE_WAY_TO_GOOGLE,
                                'group' => 'my group'
                            )
                        );
        
        $this->assertEquals('user@gmail.com', $m->user);
        $this->assertEquals(Manager::ONE_WAY_TO_GOOGLE, $m->kind);
        $this->assertEquals('my group', $m->group);
        
        
        // test default parameters
        $m = new Manager($stub_client, $cintf);
        
        $this->assertEquals('default', $m->user);
        $this->assertEquals(0, $m->kind);
        $this->assertEquals(NULL, $m->group);
	}
    

    // test constructor optionnal parameters
	public function testManager()
	{
		// creating stub for guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);

		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'query'=> ['updated-min'=>0, 'max-results'=>10000],
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)->willReturn($stub_guzzle_response);

        
		// creating stub for guzzle client
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
        // creating client interface
        $cintf = $this->createMock(ClientInterface::class);
        $cintf->method('getContext')->willReturn('John Doe');
        
        // get Contact info clientside
        $cintf->method('getContactInfoClientside')
            // checking type of argument
            ->with($this->isInstanceOf(Contact::class))
            ->willReturn((object)['etag'=>'etag-updated', 'clientsideUpdateFlag'=>false]);
                
        $m = new Manager($stub_client, $cintf, ['kind'=>Manager::ONE_WAY_FROM_GOOGLE]);

        $r = $m->sync(new \Psr\Log\NullLogger(), 0);
        $this->assertEquals(true, $r);
	}
	
	
}

?>