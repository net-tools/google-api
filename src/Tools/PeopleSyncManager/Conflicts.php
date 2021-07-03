<?php
/**
 * Conflicts
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Tools\PeopleSyncManager;



/**
 * Interface to deal with conflicts when mergin Google-side People with client-side contacts
 */
interface Conflicts
{
	/**
	 * Handle conflict by backupping some values from client-side contact, before update googleside -> clientside ; values will be later restored
	 *
	 * Thus we may merge updates on both sides 1) by preventing some values to be overwritten 2) by sending back thoses values on the other side
	 *
	 * @param string $resourceName
	 * @param string[] Array of contact values keys to preserve
	 * @return string|string[] Returns an associative array of backupped values for this contact, or a string with error message
	 */
	function backupContactValues($resourceName, array $preserve);
	
	
	
	/**
	 * Handle conflict by restoring some values from client-side contact, after update googleside -> clientside ; values previously backupped are restored
	 *
	 * Thus we may merge updates on both sides 1) by preventing some values to be overwritten 2) by sending back thoses values on the other side
	 *
	 * @param string $resourceName
	 * @param string[] $values An associative array of backupped values for this contact
	 * @return string|bool Returns True if success, a string with error message otherwise
	 */
	function restoreContactValues($resourceName, array $values);
}
