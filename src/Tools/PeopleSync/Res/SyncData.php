<?php
/**
 * SyncData
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSync\Res;





/**
 * Class to provide updated flag and md5 value for a client-side contact
 */
final class SyncData
{
	/**
	 * @var bool
	 */
    public $updated;
	
	
	/**  
	 * @var string
	 */
	public $md5;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param bool $updated
	 * @param string $md5
	 */
	public function __construct($updated, $md5)
	{
		$this->updated = $updated;
		$this->md5 = $md5;
	}
}
