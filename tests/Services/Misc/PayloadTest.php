<?php


use \Nettools\GoogleAPI\Services\Misc\Payload;





class PayloadTest extends PHPUnit\Framework\TestCase
{
    public function testObject()
    {
        $o = new Payload((object)['body'=>'THE BODY', 'contentType'=>'text/plain']);
        
        $this->assertEquals('THE BODY', $o->body);
        $this->assertEquals('text/plain', $o->contentType);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\ServiceException::class);
        $x = $o->prop3;  // property does not exist in class Payload, exception should be thrown
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