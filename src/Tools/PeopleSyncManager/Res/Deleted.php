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
	 * Returns an iterator of Deleted object created from a Json array
	 *
	 * @param string $json
     * @return \Iterator Returns an iterator of Deleted objects (resourceName, text properties) of contacts to delete google side
	 */
	static function listFromJson($json)
	{
		$lst = json_decode($json);
		foreach ( $lst as $item )
			yield new Deleted($item->resourceName, $item->text);
	}
}
