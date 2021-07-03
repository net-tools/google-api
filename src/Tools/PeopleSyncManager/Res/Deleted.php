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
	
	
	
	/** 
	 * Returns a list of Deleted object created from a Json array
	 *
	 * @param string $json
	 * @return Deleted[]
	 */
	static function listFromJson($json)
	{
		$ret = [];
		
		$lst = json_decode($json);
		foreach ( $lst as $item )
			$ret[] = new Deleted($item->resourceName, $item->text);
		
		
		return $ret;
	}
}
