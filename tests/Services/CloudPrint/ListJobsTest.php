<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\ListJobs;
use \Nettools\GoogleAPI\Services\Misc\Collection;




class ListJobsTest extends \PHPUnit\Framework\TestCase
{
    public function testListJobs()
    {
		$lst = new ListJobs(array((object)['id'=>'prn1', 'title'=>'test 1'], (object)['id'=>'prn2', 'title'=>'test 2']));
		$this->assertInstanceOf(Collection::class, $lst);
		
        $it = $lst->getIterator();
		$this->assertInstanceOf(\Iterator::class, $it);
        
		$prn = $it->current();
		$this->assertEquals('prn1', $prn->id);
    }
       
      
    
}

?>