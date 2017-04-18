<?php
/**
 * ClientInterface
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\ContactsSyncManager;



use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Services\Contacts_Service;




/**
 * Interface to interact with client code 
 */
interface ClientInterface
{
	/**
	 * Get a string describing the contact being synced (may be name + familyname or any other meaningful identifier)
	 *
	 * Mainly used to provide context info when logging events.
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @return string[] Returns an array describing the contact being synced
	 */
	function getContext(Contact $c);
	
	
	
	/**
	 * Get sync data about a contact
	 *
	 * On the client contacts repository, Google etag for each contact must be stored. It makes it possible to detect updates on Google-side (since the 
	 * etag is updated by Google when a contact info changes). Client side updates must also be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @return \Stdclass|bool Returns an object with `etag` and `clientsideUpdateFlag` properties ; if the Contact is not found client-side, return FALSE
	 */
	function getContactInfoClientside(Contact $c);
	
	
	
	/**
	 * Send Google contact to clientside
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @return bool|string Returns true if the clientside has updated the contact successfuly, a string with an error message otherwise
	 */
	function updateContactClientside(Contact $c);
    
    
    
    /**
     * Get an list of updated contacts on clientside (will be synced to Google)
     *
     * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service object to use to get Contacts objects
     * @return Contact[]|\Iterator Returns an array of Contact objects or an Iterator of Contact objects to send to Google
     */
    function getUpdatedContactsClientside(Contacts_Service $service);
	
	
    
    /**
     * During sync clientside -> Google, send a request back to clientside to acknowledge contact being successfuly created/updated to Google from clientside.
     *
     * The clientside may use this callback to get the new contact etag and its id, so that further changes can be tracked.
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @param bool $created Is set to TRUE if the contact is new, false otherwise (contact updated)
	 * @return bool|string Returns true if the clientside has acknowledged the update on Google side or a string with an error message otherwise
     */
    function acknowledgeContactUpdatedGoogleside(Contact $c, $created);
    
    
    
    /**
     * Get an list of deleted contacts on clientside (will be sync-deleted to Google)
     *
     * @return string[] Returns an array of contacts id (value of link with 'edit' rel attribute)
     */
    function getDeletedContactsClientside();
	
	
    
    /**
     * During delete clientside -> Google, send a request back to clientside to acknowledge contact being successfuly deleted to Google from clientside.
     *
     * The clientside may use this callback to remove the "contact to delete" flag or to do any other cleaning stuff.
     *
	 * @param Contact $c A contact object with only its link properties set, so that the clientside can acknowledge the deletion google-side
	 * @return bool|string Returns true if the clientside has acknowledged the deletion on Google side or a string with an error message otherwise
     */
    function acknowledgeContactDeletedGoogleside(Contact $c);
}

?>