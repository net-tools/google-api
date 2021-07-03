<?php
/**
 * Deleted
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSyncManager\Res;




/**
 * Class for a deleted contact
 */
final class Deleted
{
	/**
	 * @var string
	 */
    public $resourceName;
	
	
	/**  
	 * @var text
	 */
	public $text;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param string $resourceName
	 * @param string $text Short description of contact (to be use mainly in exception handling)
	 */
	public function __construct($resourceName, $text)
	{
		$this->resourceName = $resourceName;
		$this->text = $text;
	}
}
