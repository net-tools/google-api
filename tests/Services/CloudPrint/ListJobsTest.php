<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\ListJobs;



class ListJobsTest extends \PHPUnit\Framework\TestCase
{
    public function testListJobs()
    {
		$lst = new ListJobs(array((object)['id'=>'prn1', 'title'=>'test 1'], (object)['id'=>'prn2', 'title'=>'test 2']));
		
		$this->assertInstanceOf(\Iterator::class, $lst);
		$this->assertInstanceOf(\Countable::class, $lst);
		
		$prn = $lst->current();
		
		$this->assertEquals('prn1', $prn->id);
    }
       
      
    
}

?>