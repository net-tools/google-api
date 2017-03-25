<?php


use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;
use \Nettools\GoogleAPI\Services\Misc\Object;




class MyClassInCollection
{
    public $prop = 'property';
}





class ArrayCollectionTest extends PHPUnit\Framework\TestCase
{
    public function testEmptyCollection()
    {
        $col = new ArrayCollection([], 'MyClassInCollection');
        $this->assertEquals(false, $col->valid());
    }
       
}

?>