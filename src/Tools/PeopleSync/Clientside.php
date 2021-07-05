<?php
/**
 * Clientside
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */


namespace Nettools\GoogleAPI\Tools\PeopleSync;




/**
 * Class to interact with client system
 */
class Clientside
{
	/**
	 * @var Contacts
	 */
	public $contacts;
	
	
	/**
	 * @var Conflicts
	 */
	public $conflicts;
	
	
	
	/**
	 * Constructor
	 *
	 * @param Contacts $contacts Object to deal with client-side contacts
	 * @param Conflicts $conflicts Object to deal with client-side contacts conflicts
	 */
	public function __construct(Contacts $contacts, Conflicts $conflicts)
	{
		$this->contacts = $contacts;
		$this->conflicts = $conflicts;
	}
}

?>