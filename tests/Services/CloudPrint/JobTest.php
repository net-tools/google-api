<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\Job;
use \PHPUnit\Framework\TestCase;



class JobTest extends TestCase
{
    public function testJob()
    {
        $p = Job::fromFeed((object)['id'=>'1234']);
		$this->assertEquals('1234', $p->id);
    }
       
      
    
}

?>