<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\JobPayload;



class JobPayloadTest extends \PHPUnit\Framework\TestCase
{
    public function testJobPayload()
    {
        $p = JobPayload::fromData('application/pdf', 'xyz');
		$this->assertEquals('application/pdf', $p->contentType);
		$this->assertEquals('xyz', $p->body);
    }
       
      
    
}

?>