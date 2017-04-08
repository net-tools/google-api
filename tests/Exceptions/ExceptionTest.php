<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Exceptions\Exception;
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;




class ExceptionTest extends \PHPUnit\Framework\TestCase
{
	public function throwExceptionTest()
	{
		throw new Exception('Exception thrown');
	}
	

	public function throwGoogleServiceExceptionTest()
	{
		$json = <<<JSON

{
 "error": {
  "errors": [
   {
    "domain": "global",
    "reason": "required",
    "message": "Login Required",
    "locationType": "header",
    "location": "Authorization"
   }
  ],
  "code": 401,
  "message": "Login Required"
 }
}
JSON;
		
		throw new \Google_Service_Exception($json);
	}
	

	public function throwGoogleServiceExceptionTest2()
	{
		$json = <<<JSON

{
 "error": "not allowed",
 "error_description": "request is not allowed"
}
JSON;
		
		throw new \Google_Service_Exception($json);
	}

	
	public function testException()
    {
		try
		{
			// throw a Google_Exception (exception message is a string)
			$this->throwExceptionTest();
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
		}
		catch(\Exception $e)
		{
			$this->assertEquals('Exception thrown', ExceptionHelper::getMessageFor($e));
		}

	
	
		try
		{
			// throw a Google_Service_Exception (exception message is a json-encoded string ; error property is an object (with code and message sub-properties))
			$this->throwGoogleServiceExceptionTest();
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
		}
		catch(\Exception $e)
		{
			$this->assertEquals('Login Required (code 401)', ExceptionHelper::getMessageFor($e));
		}

	
	
		try
		{
			// throw a Google_Service_Exception (exception message is a json-encoded string ; error property is string and we may have an error_description property)
			$this->throwGoogleServiceExceptionTest2();
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
		}
		catch(\Exception $e)
		{
			$this->assertEquals('not allowed (request is not allowed)', ExceptionHelper::getMessageFor($e));
		}
	}
    
    
}

?>