<?php

namespace Nettools\GoogleAPI\Tests;


use \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty;





class RelLabelArrayPropertyTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyArray()
    {
        $a = new RelLabelArrayProperty([]);
        $this->assertEquals(0, $a->count());
		$this->assertEquals([], $a->rel('test'));
		$this->assertEquals([], $a->label('test'));
    }

    
    public function testArray()
    {
		$o1 = (object)['value'=>'1', 'rel'=>'my rel'];
		$o2 = (object)['value'=>'2', 'rel'=>'my rel'];
		$o3 = (object)['value'=>'3', 'label'=>'my label'];
        $a = new RelLabelArrayProperty([$o1, $o2, $o3]);
				
		$this->assertEquals([$o1, $o2], $a->rel('my rel'));
		$this->assertEquals([$o3], $a->label('my label'));
		$this->assertEquals([], $a->rel('my other rel'));
    }
       
    
}

?>