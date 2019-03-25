<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Misc\Payload;
use \PHPUnit\Framework\TestCase;





class PayloadTest extends TestCase
{
    public function testObject()
    {
        $o = new Payload((object)['body'=>'THE BODY', 'contentType'=>'text/plain']);
        
        $this->assertEquals('THE BODY', $o->body);
        $this->assertEquals('text/plain', $o->contentType);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $x = $o->prop3;  // property does not exist in class Payload, exception should be thrown
        $x = $x;
    }
 
    
	
    public function testPayloadClone()
    {
        $o = new Payload((object)['body'=>'THE BODY', 'contentType'=>'text/plain']);
        $o2 = new Payload($o);
        
        $this->assertEquals($o->body, $o2->body);
        $this->assertEquals($o->contentType, $o2->contentType);
    }
 
    

    public function testPayloadTypeError()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
		
		
        $o = new Payload('1');
        $o = $o;
    }
 
	
	
    public function testObjectNoConstructor()
    {
		$this->expectException(\ArgumentCountError::class);
		
		
        $o = new Payload();
        $o = $o;
    }
 
}

?>