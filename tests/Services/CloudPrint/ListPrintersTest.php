<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\ListPrinters;



class ListPrintersTest extends \PHPUnit\Framework\TestCase
{
    public function testListPrinters()
    {
		$lst = new ListPrinters(array((object)['id'=>'prn1', 'title'=>'printer 1'], (object)['id'=>'prn2', 'title'=>'printer 2']));
		
		$this->assertInstanceOf(\Iterator::class, $lst);
		$this->assertInstanceOf(\Countable::class, $lst);
		
		$prn = $lst->current();
		
		$this->assertEquals('prn1', $prn->id);
    }
       
      
    
}

?>