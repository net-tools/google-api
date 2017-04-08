<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\Job;



class JobTest extends \PHPUnit\Framework\TestCase
{
    public function testJob()
    {
        $p = Job::fromFeed((object)['id'=>'1234']);
		$this->assertEquals('1234', $p->id);
    }
       
      
    
}

?>