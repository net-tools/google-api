<?php
/**
 * Request
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSync\Res;





/**
 * Class for a deferred request
 */
final class Request
{
	/**
	 * @var string
	 */
    public $kind;
	
	
	/**  
	 * @var \Google\Service\PeopleService\Person
	 */
	public $contact;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param string $kind
	 * @param \Google\Service\PeopleService\Person $c
	 */
	public function __construct($kind, \Google\Service\PeopleService\Person $c)
	{
		$this->kind = $kind;
		$this->contact = $c;
	}
}
