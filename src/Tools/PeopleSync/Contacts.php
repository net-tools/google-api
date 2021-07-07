<?php
/**
 * Contacts
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSync;



/**
 * Interface to client-side contacts
 */
interface Contacts
{
	/**
	 * Get log context data about a `Person` object ; mainly used to provide context info when logging events.
	 * 
     * The default implementation already provides 'familyName', 'givenName' and 'resourceName' properties from the `Person` object inside the $context array ; 
	 * however, the client-side may customize this data by updating values or adding new context values.
     *
	 * @param \Google\Service\PeopleService\Person $c 
     * @param string[] $context Default log context provided by the default implementation (familyName, givenName, resourceName)
	 * @return string[] Log context as an associative array ; if no additions/updates, return the `$context` parameter
	 */
	function getLogContext(\Google\Service\PeopleService\Person $c, array $context);

	
	
	/**
	 * Get sync data for a client-side contact : update flag and md5
	 *
	 * On the client contacts repository, client side updates must be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param string $resourceName
	 * @return null|SyncData Returns NULL if no row found (google orphan), or Res\SyncData object (with updated and md5 properties)
	 */
	function getSyncData($resourceName);
	
	
	
    /**
     * Get an list of updated contacts on clientside (will be later synced to Google)
	 *
	 * We return an array of object litterals (resourceName, md5, text) ; text makes it possible to display real contact name during exception or log without having the full contact record
     *
     * @return Res\Updated[]|\Iterator Returns an iterator or an array of Res\Updated objects (with resourceName, md5, text properties)
     */
    function listUpdated();
	
	
    
    /**
     * Get an list of created contacts on clientside (will be later synced to Google)
	 *
	 * We return an array of litteral objects (clientId, contact), with contact being a \Google\Service\PeopleService\Person object
     *
     * @return Res\Created[]|\Iterator Returns an iterator or an array of Res\Created objects (clientId, contact properties)
     */
    function listCreated();
	
	

    /**
     * Get a list of deleted contacts ids on clientside (will be sync-deleted to Google)
	 *
	 * We return an array of litteral object (resourceName, text) ; text makes it possible to display relevant info during feedback and exception handling
     *
     * @return Res\Deleted[]|\Iterator Returns an iterator or an array of Res\Deleted objects (resourceName, text properties) of contacts to delete google side
     */
    function listDeleted();
	
	
    
	/**
	 * Update contact clientside from a Google-side Person object
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @throws UserException Exception thrown if an error occured
	 */
	function update(\Google\Service\PeopleService\Person $c);
    
    
	
	/**
	 * Create contact clientside from a Google-side Person object
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @throws UserException Exception thrown if an error occured
	 * @throws UnsupportedException Exception thrown if this operation is not supported by clientside
	 */
	function create(\Google\Service\PeopleService\Person $c);
    
    
	
	/**
	 * Delete a contact client-side
	 *	 
	 * @param string $resourceName
	 * @throws UserException Exception thrown if an error occured
	 * @throws UnsupportedException Exception thrown if this operation is not supported by clientside
	 */
	function delete($resourceName);
	
	
	
    /**
     * Update a Google-side Person object with values from clientside
	 *
	 * This object will be later synced to Google with any updates from client-side
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @throws UserException Exception thrown if an error occured
     */
    function mergeInto(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Request the clientside system to raise the "updated" flag for a contact (so that it will sync clientside -> google at next sync)
	 *
	 * @param string $resourceName
	 * @throws UserException Exception thrown if an error occured
	 */
	function requestUpdate($resourceName);
	
	
	
	/**
	 * Request the clientside system to revoke the "updated" flag for a contact (so that it will sync clientside -> google at next sync)
	 *
	 * @param string $resourceName
	 * @throws UserException Exception thrown if an error occured
	 */
	function cancelUpdate($resourceName);
	
}
