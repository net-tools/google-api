<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\Payload;





class PayloadTest extends \PHPUnit\Framework\TestCase
{
    public function testObject()
    {
        $o = new Payload((object)['body'=>'THE BODY', 'contentType'=>'text/plain']);
        
        $this->assertEquals('THE BODY', $o->body);
        $this->assertEquals('text/plain', $o->contentType);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\ServiceException::class);
        $x = $o->prop3;  // property does not exist in class Payload, exception should be thrown
    }
 
    
	
    public function testPayloadClone()
    {
        $o = new Payload((object)['body'=>'THE BODY', 'contentType'=>'text/plain']);
        $o2 = new Payload($o);
        
        $this->assertEquals($o->body, $o2->body);
        $this->assertEquals($o->contentType, $o2->contentType);
    }
 
    

	/**
	 * @expectedException \Nettools\GoogleAPI\Exceptions\ServiceException
	 */
    public function testPayloadTypeError()
    {
        $o = new Payload('1');
    }
 
	
	
	/**
     * @expectedException ArgumentCountError
     */
    public function testObjectNoConstructor()
    {
        $o = new Payload();
    }
 
}

?>