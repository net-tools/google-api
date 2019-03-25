<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\Resource;
use \PHPUnit\Framework\TestCase;



class MyResourceTest extends Resource 
{
    protected $_prop1 = 'prop1';
}




class ResourceTest extends TestCase
{
    public function testProperties()
    {
        $serviceStub = $this->createMock(\Nettools\GoogleAPI\Services\Service::class);
        $o = new MyResourceTest($serviceStub);
        $this->assertEquals('prop1', $o->prop1);
        $this->assertInstanceOf(\Nettools\GoogleAPI\Services\Service::class, $o->service); // using __get accessor

        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $x = $o->undefinedProperty;  // property does not exist in class, exception should be thrown
        $x = $x;
    }
       
    

	public function testReadOnlyProperties()
    {
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
		$serviceStub = $this->createMock(\Nettools\GoogleAPI\Services\Service::class);
        $o = new MyResourceTest($serviceStub);
        $o->prop1 = 'new';  // no write access to properties
    }
       
    

	public function testNonExistentProperty()
    {
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $serviceStub = $this->createMock(\Nettools\GoogleAPI\Services\Service::class);
        $o = new MyResourceTest($serviceStub);
        $o->undefinedProperty = 'k';
    }
       
}

?>