<?php
/**
 * SyncException
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;




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
	 * @var \Google_Service_PeopleService_Person
	 */
	protected $_contact;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $msg Exception message
	 * @param \Google_Service_PeopleService_Person $contact Contact being synced
	 */
	public function __construct($msg, \Google_Service_PeopleService_Person $contact)
	{
		parent::__construct($msg);
		$this->_contact = $contact;
	}
	
	
	/**
	 * Get contact whose sync process raised an exception
	 *
	 * @return \Google_Service_PeopleService_Person
	 */
	public function getContact()
	{
		return $this->_contact;
	}
}


?>