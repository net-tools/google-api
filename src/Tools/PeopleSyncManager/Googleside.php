<?php
/**
 * Googleside
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;



/**
 * Interface for any Google-related stuff
 */
interface Googleside
{
	/**
	 * Compute a md5 hash from a Google-side contact
	 *
	 * @param \Google\Service\PeopleService\Person $c A `Google\Service\PeopleService\Person` object 
	 * @return string
	 */
	function md5(\Google\Service\PeopleService\Person $c);
	
	
	
	/**
	 * Get the current sync token
	 *
	 * @return null|string Returns the sync token or NULL if not defined
	 */
	function getSyncToken();
	
	
	
	/**
	 * Set the current sync token
	 *
	 * @param string $token
	 */
	function setSyncToken($token);
	
	
	
    /**
     * During sync clientside -> Google, send a request back to clientside to acknowledge contact being successfuly updated to Google from clientside.
     *
     * The clientside may use this callback to cancel an update flag.
     *
	 * @param \Google\Service\PeopleService\Person $c 
	 * @return bool|string Returns true if the clientside has acknowledged the update on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function contactUpdated(\Google\Service\PeopleService\Person $c);
 
    
    
    /**
     * During sync clientside -> Google, send a request back to clientside to acknowledge contact being successfuly created to Google from clientside.
     *
     * The clientside may use this callback to get the new contact id, so that further changes can be tracked.
     *
	 * @param Res\Created $cobj Object passed from listCreated ; its contact property may have been updated with any relevant data (such as editlink)
	 * @return bool|string Returns true if the clientside has acknowledged the creation on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function contactCreated(Res\Created $cobj);
    
    
    
    /**
     * During delete clientside -> Google, send a request back to clientside to acknowledge contact being successfuly deleted to Google from clientside.
     *
     * The clientside may use this callback to remove the "contact to delete" flag or to do any other cleaning stuff.
     *
	 * @param Res\Deleted $cobj Object passed from listDeleted
	 * @return bool|string Returns true if the clientside has acknowledged the deletion on Google side or a string with an error message otherwise (does not halt the sync)
     */
    function contactDeleted(Res\Deleted $cobj);	
}
