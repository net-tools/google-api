<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\Misc\MutableObject;
use \PHPUnit\Framework\TestCase;



class MOTest extends MutableObject
{
    protected $_prop1 = NULL;
    protected $_prop2 = 'prop2';
    protected $_propRO = 'readonly';
    

    protected function _getReadonlyProperties()
    {
        return ['propRO'];
    }

}




class MutableObjectTest extends TestCase
{
    public function testObject()
    {
        $o = new MOTest();
        
        
        $this->assertEquals(NULL, $o->prop1);
        $this->assertEquals('prop2', $o->prop2);
        
        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $x = $o->prop3;  // property does not exist in class MOTest, exception should be thrown
        $x = $x;
    }
    
    
    public function testMutableObjectWriteAccess()
    {
        $o = new MOTest();
        
        $o->prop2 = 'prop2updated';
        $this->assertEquals('prop2updated', $o->prop2);

        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $o->prop3 = 'yyyy';  // property does not exist in class MOTest : exception should be thrown
    }
     
    
    public function testMutableObjectReadOnlyProperties()
    {
        $o = new MOTest();
        
        $this->assertEquals('readonly', $o->propRO);

        $this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);
        $o->propRO = 'yyyy';  // property is mentionned to be readonly : exception should be thrown
    }
   
       
}

?>