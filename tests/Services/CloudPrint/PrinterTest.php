<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\Printer;
use \PHPUnit\Framework\TestCase;



class PrinterTest extends TestCase
{
    public function testPrinter()
    {
        $p = Printer::fromFeed((object)['id'=>'1234']);
		$this->assertEquals('1234', $p->id);
    }
       
      
    
}

?>