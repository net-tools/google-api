<?php
/**
 * Created
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSync\Res;



/**
 * Class for a created contact
 */
final class Created
{
	/**  
	 * @var string
	 */
	public $id;
	
	
	/**  
	 * @var \Google\Service\PeopleService\Person
	 */
	public $contact;
	
	
	
	/** 
	 * Constructor
	 *
	 * @param string $id
	 * @param string \Google\Service\PeopleService\Person $contact
	 */
	public function __construct($id, \Google\Service\PeopleService\Person $contact)
	{
		$this->id = $id;
		$this->contact = $contact;
	}
}
