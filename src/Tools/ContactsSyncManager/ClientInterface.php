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
	 * Get log context data about a `Contact` object ; mainly used to provide context info when logging events.
	 * 
     * The default implementation provides 'familyName', 'givenName' and 'id' properties from the `Contact` object ; however, the client-side may
     * customize this data by updating values or adding new context values.
     *
	 * The implementation MUST NOT expect every `Contact $c` property being set ; in some cases, such as deletions sync, the `$c` parameter
	 * may contain only `id` and `links` properties.
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
     * @param string[] $context Default log context provided by the default implementation
	 * @return string[] Log context as an associative array ; if no additions/updates, return the `$context` parameter
	 */
	function getLogContext(Contact $c, array $context);
	
	
	
	/**
	 * Get sync data about a contact
	 *
	 * On the client contacts repository, Google etag for each contact must be stored. It makes it possible to detect updates on Google-side (since the 
	 * etag is updated by Google when a contact info changes). Client side updates must also be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @return \Stdclass|bool Returns an object with `etag` and `clientsideUpdateFlag` properties ; if the Contact is not found client-side, return FALSE (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function getContactInfoClientside(Contact $c);
	
	
	
	/**
	 * Send Google contact to clientside
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @return bool|string Returns true if the clientside has updated the contact successfuly, a string with an error message otherwise (not halting the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function updateContactClientside(Contact $c);
    
    
    
    /**
     * Get an list of updated contacts on clientside (will be synced to Google)
	 *
	 * We return an array of litteral objects with `contact` and `etag` properties (respectively of `Contact` object and string type).
	 * The `etag` property must be set with the last google etag known and stored clientside for a contact. 
	 * We can detect updates on both sides by comparing google-side etag and last known etag clientside. If etags match, we can send the update ;
	 * if etags don't match, there has been an update google-side the client-side is not aware of.
     *
     * @param \Nettools\GoogleAPI\Services\Contacts_Service $service Contacts service object to use to get Contacts objects
     * @return \Stdclass[]|\Iterator Returns an iterator or an array of litteral objects with `contact` and `etag` properties 
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
     */
    function getUpdatedContactsClientside(Contacts_Service $service);
	
	
    
    /**
     * During sync clientside -> Google, send a request back to clientside to acknowledge contact being successfuly created/updated to Google from clientside.
     *
     * The clientside may use this callback to get the new contact etag and its id, so that further changes can be tracked.
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c 
	 * @param bool $created Is set to TRUE if the contact is new, false otherwise (contact updated)
	 * @return bool|string Returns true if the clientside has acknowledged the update on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function acknowledgeContactUpdatedGoogleside(Contact $c, $created);
    
    
    
    /**
     * Get an list of deleted contacts on clientside (will be sync-deleted to Google)
     *
     * @return string[] Returns an array of contacts edit links to delete (value of link with 'edit' rel attribute)
     */
    function getDeletedContactsClientside();
	
	
    
    /**
     * During delete clientside -> Google, send a request back to clientside to acknowledge contact being successfuly deleted to Google from clientside.
     *
     * The clientside may use this callback to remove the "contact to delete" flag or to do any other cleaning stuff.
	 * The implementation MUST NOT expect every `Contact $c` property being set ; only the `id` and `links` property are defined
	 * so that the clientside can acknowledge the deletion google-side through the ID or edit link (link whose `rel` attribute equals `edit`).
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c A `Contact` object with only its `id` and `links` properties set
	 * @return bool|string Returns true if the clientside has acknowledged the deletion on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function acknowledgeContactDeletedGoogleside(Contact $c);
	
	
	
	/**
	 * Delete Google contact clientside
	 *
	 * The implementation MUST NOT expect every `Contact $c` property being set ; only the `id` and `links` property are defined
	 * so that the clientside can acknowledge the deletion google-side through the ID or edit link (link whose `rel` attribute equals `edit`).
	 *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Contact $c A `Contact` object with only its `id` and `links` properties set
	 * @return bool|string Returns true if the clientside has deleted the contact successfuly, a string with an error message otherwise (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function deleteContactClientside(Contact $c);
}

?>