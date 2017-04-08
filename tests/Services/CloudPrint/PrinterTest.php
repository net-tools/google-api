<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\CloudPrint\Printer;



class PrinterTest extends \PHPUnit\Framework\TestCase
{
    public function testPrinter()
    {
        $p = Printer::fromFeed((object)['id'=>'1234']);
		$this->assertEquals('1234', $p->id);
    }
       
      
    
}

?>