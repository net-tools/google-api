<?php
/**
 * ClientInterface
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;



use \Nettools\GoogleAPI\ServiceWrappers\PeopleService;




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
	 * @param \Google\Service\PeopleService\Person $c 
     * @param string[] $context Default log context provided by the default implementation
	 * @return string[] Log context as an associative array ; if no additions/updates, return the `$context` parameter
	 */
	function getLogContext(\Google\Service\PeopleService\Person $c, array $context);
	
	
	
	/**
	 * Get sync data about a contact
	 *
	 * On the client contacts repository, client side updates must be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return \Stdclass|bool Returns an object with `clientsideUpdateFlag` property ; if the Contact is not found client-side, return FALSE (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function getContactInfoClientside(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Send Google contact to clientside
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return bool|string Returns true if the clientside has updated the contact successfuly, a string with an error message otherwise (not halting the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function updateContactClientside(\Google\Service\PeopleService\Person $c);
    
    
    
    /**
     * Get an list of updated contacts on clientside (will be synced to Google)
	 *
	 * We return an array of litteral objects with `contact` property (`Google\Service\PeopleService\Person` object).
     *
     * @param \Nettools\GoogleAPI\ServiceWrappers\PeopleService $service People service wrapper object to use 
     * @return \Stdclass[]|\Iterator Returns an iterator or an array of litteral objects with `contact` property
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
     */
    function getUpdatedContactsClientside(PeopleService $service);
	
	
    
    /**
     * During sync clientside -> Google, send a request back to clientside to acknowledge contact being successfuly created/updated to Google from clientside.
     *
     * The clientside may use this callback to get the new contact id, so that further changes can be tracked.
     *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @param bool $created Is set to TRUE if the contact is new, false otherwise (contact updated)
	 * @return bool|string Returns true if the clientside has acknowledged the update on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function acknowledgeContactUpdatedGoogleside(\Google\Service\PeopleService\Person $c, $created);
    
    
    
    /**
     * Get a list of deleted contacts ids on clientside (will be sync-deleted to Google)
     *
     * @return string[] Returns an array of contacts resourceName values to delete
     */
    function getDeletedContactsClientside();
	
	
    
    /**
     * During delete clientside -> Google, send a request back to clientside to acknowledge contact being successfuly deleted to Google from clientside.
     *
     * The clientside may use this callback to remove the "contact to delete" flag or to do any other cleaning stuff.
     *
	 * @param string $resourceName The `resourceName` value of Person entry deleted
	 * @return bool|string Returns true if the clientside has acknowledged the deletion on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function acknowledgeContactDeletedGoogleside($resourceName);
	
	
	
	/**
	 * Delete Google contact clientside
	 *	 *
	 * @param \Google\Service\PeopleService\Person $c A `Google\Service\PeopleService\Person` object 
	 * @return bool|string Returns true if the clientside has deleted the contact successfuly, a string with an error message otherwise (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function deleteContactClientside(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Compute a md5 hash from a Google-side contact
	 *
	 * @param \Google\Service\PeopleService\Person $c A `Google\Service\PeopleService\Person` object 
	 * @return string
	 */
	function md5Googleside(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Compute a md5 hash from a client-side contact
	 *
	 * @param string $resourceName The Google-side ID of contact to look for in client-side database
	 * @return string
	 */
	function md5Clientside($resourceName);
	
	
	
	/**
	 * Handle conflict by backupping some values from client-side contact, before update googleside -> clientside ; values will be later restored
	 *
	 * Thus we may merge updates on both sides 1) by preventing some values to be overwritten 2) by sending back thoses values on the other side
	 *
	 * @param \Google\Service\PeopleService\Person $c A `Google\Service\PeopleService\Person` object 
	 * @param string[] Array of contact values keys to preserve
	 * @return string|string[] Returns an associative array of backupped values for this contact, or a string with error message
	 */
	function conflictHandlingBackupContactValuesClientside(\Google\Service\PeopleService\Person $c, array $preserve);
	
	
	
	/**
	 * Handle conflict by restoring some values from client-side contact, after update googleside -> clientside ; values previously backupped are restored
	 *
	 * Thus we may merge updates on both sides 1) by preventing some values to be overwritten 2) by sending back thoses values on the other side
	 *
	 * @param \Google\Service\PeopleService\Person $c A `Google\Service\PeopleService\Person` object 
	 * @param string[] $values An associative array of backupped values for this contact
	 * @return string|bool Returns True if success, a string with error message otherwise
	 */
	function conflictHandlingRestoreContactValuesClientside(\Google\Service\PeopleService\Person $c, array $values);
}

?>