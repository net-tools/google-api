<?php
/**
 * AbstractContacts
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;



/**
 * Interface to client-side contacts
 */
abstract class AbstractContacts implements Contacts
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
	function getLogContext(\Google\Service\PeopleService\Person $c, array $context)
	{
		return $context;
	}

	
	
	/**
	 * Get sync data for a client-side contact : update flag and md5
	 *
	 * On the client contacts repository, client side updates must be tracked ; if the client updates a contact, he's required
	 * to set a flag on the client info, so that we can know that we have to send clientside updates to Google. If the flag is not set, it means there has
	 * been no clientside updates since last sync.
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return null|SyncData Returns NULL if no row found (google orphan), or Res\SyncData object (with updated and md5 properties)
	 */
	abstract function getSyncData(\Google\Service\PeopleService\Person $c);
	
	
	
    /**
     * Get an list of updated contacts on clientside (will be later synced to Google)
	 *
	 * We return an array of object litterals (resourceName, md5, text) ; text makes it possible to display real contact name during exception or log without having the full contact record
     *
     * @return Res\Updated[]|\Iterator Returns an iterator or an array of Res\Updated objects (with resourceName, md5, text properties)
     */
    abstract function listUpdated();
	
	
    
    /**
     * Get an list of created contacts on clientside (will be later synced to Google)
	 *
	 * We return an array of litteral objects (clientId, contact), with contact being a \Google\Service\PeopleService\Person object
     *
     * @return Res\Created[]|\Iterator Returns an iterator or an array of Res\Created objects (clientId, contact properties)
     */
    abstract function listCreated();
	
	

    /**
     * Get a list of deleted contacts ids on clientside (will be sync-deleted to Google)
	 *
	 * We return an array of litteral object (resourceName, text) ; text makes it possible to display relevant info during feedback and exception handling
     *
     * @return Res\Deleted[]|\Iterator Returns an iterator or an array of Res\Deleted objects (resourceName, text properties) of contacts to delete google side
     */
    abstract function listDeleted();
	
	
    
	/**
	 * Update contact clientside from a Google-side Person object
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return bool|string Returns true if the clientside has updated the contact successfuly, a string with an error message otherwise (not halting the sync)
	 */
	abstract function update(\Google\Service\PeopleService\Person $c);
    
    
	
	/**
	 * Delete a contact client-side
	 *	 
	 * @param \Google\Service\PeopleService\Person $c Contact object to delete clientside
	 * @return bool|string Returns true if the clientside has deleted the contact successfuly, a string with an error message otherwise (does not halt the sync)
	 */
	abstract function delete(\Google\Service\PeopleService\Person $c);
	
	
	
    /**
     * Update a Google-side Person object with values from clientside
	 *
	 * This object will be later synced to Google with any updates from client-side
	 *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return bool|string Returns True or a string if an error occurs
     */
    abstract function mergeInto(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Request the clientside system to raise the "updated" flag for a contact (so that it will sync clientside -> google at next sync)
	 *
	 * @param \Google\Service\PeopleService\Person $c
	 * @return bool|string Returns true if the clientside has raised the 'updated' flag contact successfuly, a string with an error message otherwise (not halting the sync)
	 */
	abstract function requestUpdate(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Request the clientside system to revoke the "updated" flag for a contact (so that it will sync clientside -> google at next sync)
	 *
	 * @param \Google\Service\PeopleService\Person $c
	 * @return bool|string Returns true if the clientside has revoked the 'updated' flag contact successfuly, a string with an error message otherwise (not halting the sync)
	 */
	abstract function cancelUpdate(\Google\Service\PeopleService\Person $c);
	
}
