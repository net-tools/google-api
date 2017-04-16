<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\ListPrinters;
use \Nettools\GoogleAPI\Services\Misc\Collection;




class ListPrintersTest extends \PHPUnit\Framework\TestCase
{
    public function testListPrinters()
    {
		$lst = new ListPrinters(array((object)['id'=>'prn1', 'title'=>'printer 1'], (object)['id'=>'prn2', 'title'=>'printer 2']));
		$this->assertInstanceOf(Collection::class, $lst);
		
        $it = $lst->getIterator();
		$this->assertInstanceOf(\Iterator::class, $it);
        
		$prn = $it->current();
		$this->assertEquals('prn1', $prn->id);
    }
       
      
    
}

?>