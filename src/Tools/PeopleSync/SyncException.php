<?php
/**
 * SyncException
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\PeopleSync;




/**
 * Class for sync exception
 *
 * Stores contact context, but does not halt the sync process. To halt process, throw a `HaltSyncException` instead.
 */
class SyncException extends \Exception
{
	/**
	 * Contact being synced
	 *
	 * @var \Google\Service\PeopleService\Person
	 */
	protected $_contact;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $msg Exception message
	 * @param \Google\Service\PeopleService\Person $contact Contact being synced
	 */
	public function __construct($msg, \Google\Service\PeopleService\Person $contact)
	{
		parent::__construct($msg);
		$this->_contact = $contact;
	}
	
	
	/**
	 * Get contact whose sync process raised an exception
	 *
	 * @return \Google\Service\PeopleService\Person
	 */
	public function getContact()
	{
		return $this->_contact;
	}
}


?>