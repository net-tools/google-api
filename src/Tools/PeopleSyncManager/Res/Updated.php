<?php
/**
 * Updated
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSyncManager\Res;





/**
 * Class for an updated contact
 */
final class Updated
{
	/**
	 * @var string
	 */
    public $resourceName;
	
	
	/**  
	 * @var string
	 */
	public $md5;
	
	
	/**  
	 * @var text
	 */
	public $text;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param string $resourceName
	 * @param string $md5
	 * @param string $text Short description of contact (to be use mainly in exception handling)
	 */
	public function __construct($resourceName, $md5, $text)
	{
		$this->resourceName = $resourceName;
		$this->md5 = $md5;
		$this->text = $text;
	}
}
