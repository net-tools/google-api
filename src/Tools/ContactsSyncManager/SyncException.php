<?php
/**
 * SyncException
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\ContactsSyncManager;


use \Nettools\GoogleAPI\Services\Contacts\Contact;




class SyncException extends \Exception
{
	/**
	 * Contact being synced
	 *
	 * @var \Nettools\GoogleAPI\Services\Contacts\Contact
	 */
	protected $_contact;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $msg Exception message
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact being synced
	 */
	public function __construct($msg, Contact $contact)
	{
		$this->_contact = $contact;
	}
	
	
	/**
	 * Get contact whose sync process raised an exception
	 *
	 * @return \Nettools\GoogleAPI\Services\Contacts\Contact
	 */
	public function getContact()
	{
		return $this->_contact;
	}
}


?>