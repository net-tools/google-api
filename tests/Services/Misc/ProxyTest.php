<?php


use \Nettools\GoogleAPI\Services\Misc\Proxy;
use \Nettools\GoogleAPI\Services\Misc\Object;




class OProxyTest extends Proxy
{
}





class ProxyTest extends PHPUnit\Framework\TestCase
{
    public function testProxy()
    {
        $o = new OProxyTest((object)['prop1'=>NULL, 'prop2'=>'prop2']);
        
        $this->assertEquals(NULL, $o->prop1);
        $this->assertEquals('prop2', $o->prop2);
        $this->assertEquals(NULL, $o->prop3); // non existent propery in litteral object
    }
       
    
    /**
     * @expectedException ArgumentCountError
     */
    public function testProxyNoConstructor()
    {
        $o = new OProxyTest();
    }
       
}

?>