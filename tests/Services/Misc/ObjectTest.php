<?php

namespace Nettools\GoogleAPI\Tests;




use \Nettools\GoogleAPI\Services\Misc\Object;



class OTest extends Object
{
    protected $_prop1 = NULL;
    protected $_prop2 = 'prop2';
}




class ObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testObject()
    {
        $o = new OTest();
        
        
        $this->assertEquals(NULL, $o->prop1);
        $this->assertEquals('prop2', $o->prop2);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $x = $o->prop3;  // property does not exist in class OTest, exception should be thrown
    }
    
    
    public function testObjectWriteAccess()
    {
        $o = new OTest();

        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $o->prop3 = 'yyyy';  // write access is always denied
    }
    
       
}

?>