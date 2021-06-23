<?php
/**
 * ExceptionHelper
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Exceptions;



/**
 * Helper class to extract messages from Google\Service\Exception which doesn't always return consistent messages
 */
final class ExceptionHelper 
{
	private function __construct()
	{}
	
	
	/**
	 * Get a message for a Google API exception
	 *
	 * @param \Google\Exception $e
	 * @return string
	 */
	public static function getMessageFor(\Google\Exception $e)
	{
		if ( $e instanceof \Google\Service\Exception )
		{
			// json decode
			$json = json_decode($e->getMessage());
			if ( is_null($json) )
				return $e->getMessage();
			
			// if error property is a string
			if ( is_string($json->error) )
				return "$json->error ($json->error_description)";
				
			else
			{
				if ( $json->error->message )
					return "{$json->error->message} (code {$json->error->code})";
				else 
					return $e->getMessage();
			}
/*
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
}*/
		}
		else
			return $e->getMessage();
	}
}

?>