<?php
/**
 * ClientInterface
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;



use \Nettools\GoogleAPI\ServicesWrappers\PeopleService;




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
	 * @param \Google_Service_PeopleService_Person $c 
     * @param string[] $context Default log context provided by the default implementation
	 * @return string[] Log context as an associative array ; if no additions/updates, return the `$context` parameter
	 */
	function getLogContext(\Google_Service_PeopleService_Person $c, array $context);
	
	
	
	/**
	 * Get sync data about a contact
	 *
	 * On the client contacts repository, client side updates must be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param \Google_Service_PeopleService_Person $c 
	 * @return \Stdclass|bool Returns an object with `clientsideUpdateFlag` property ; if the Contact is not found client-side, return FALSE (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function getContactInfoClientside(\Google_Service_PeopleService_Person $c);
	
	
	
	/**
	 * Send Google contact to clientside
	 *
	 * @param \Google_Service_PeopleService_Person $c 
	 * @return bool|string Returns true if the clientside has updated the contact successfuly, a string with an error message otherwise (not halting the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function updateContactClientside(\Google_Service_PeopleService_Person $c);
    
    
    
    /**
     * Get an list of updated contacts on clientside (will be synced to Google)
	 *
	 * We return an array of litteral objects with `contact` property (`Google_Service_PeopleService_Person` object).
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
	 * @param \Google_Service_PeopleService_Person $c 
	 * @param bool $created Is set to TRUE if the contact is new, false otherwise (contact updated)
	 * @return bool|string Returns true if the clientside has acknowledged the update on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function acknowledgeContactUpdatedGoogleside(\Google_Service_PeopleService_Person $c, $created);
    
    
    
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
	 * @param \Google_Service_PeopleService_Person $c A `Google_Service_PeopleService_Person` object 
	 * @return bool|string Returns true if the clientside has deleted the contact successfuly, a string with an error message otherwise (does not halt the sync)
	 * @throws \Exception If the clientside wants to halt the sync, a exception of class `Exception` should be thrown
	 */
	function deleteContactClientside(\Google_Service_PeopleService_Person $c);
}

?>