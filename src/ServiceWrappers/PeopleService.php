<?php
/**
 * PeopleService
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers;



/**
 * PeopleService helper
 *
 * Provides helper functions to PeopleService API
 */
class PeopleService extends ServiceWrapper
{  
	const TYPE_HOME = 'home';
	const TYPE_MOBILE = 'mobile';
	
	const USER_CONTACT_GROUP_TYPE = 'USER_CONTACT_GROUP';
	const SYSTEM_CONTACT_GROUP_TYPE = 'SYSTEM_CONTACT_GROUP';
	const SYSTEM_CONTACT_GROUP_MYCONTACTS = 'contactGroups/myContacts';
	const SYSTEM_CONTACT_GROUP_ALL = 'contactGroups/all';
	const SYSTEM_CONTACT_GROUP_FAMILY = 'contactGroups/family';
	const SYSTEM_CONTACT_GROUP_COWORKERS = 'contactGroups/coworkers';
	const SYSTEM_CONTACT_GROUP_BLOCKED = 'contactGroups/blocked';
	const SYSTEM_CONTACT_GROUP_STARRED = 'contactGroups/starred';
	const SYSTEM_CONTACT_GROUP_CHATBUDDIES = 'contactGroups/chatBuddies';
	

	/**
     * Get a list of all groups IDs, with optional parameters (query)
     * 
     * Don't make a mistake by thinking 'listAllGroups' means listing all groups  with no filtering options. 'All' means we want to
     * fetch the entire list in one call, and not bother with page tokens. To select groups to include, use any filter available 
     * through the $optparams parameter (see API reference for a list of filters and syntax).
     *
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Google_Service_PeopleService_ContactGroup[] An array of contact group entries
     */
	public function listAllContactGroups($optparams = array())
	{
		// prise en compte de la pagination
		$pageToken = NULL;
		
		// tableau résultant
		$groups = array();
		
		
		do
		{
			// if received a token for next page, include it in the $optparams
			if ($pageToken)
				$optparams['pageToken'] = $pageToken;
			
			// request
			$groupsResponse = $this->_service->contactGroups->listContactGroups($optparams);
			
			// if request ok
			if ( $gr = $groupsResponse->getContactGroups() )
			{
				$groups = array_merge($groups, $gr);
				$pageToken = $groupsResponse->nextPageToken;
			}
		} 
		while ($pageToken);
		
		
		return $groups;
	}
	
	
	
	
	/**
	 * Test if a given contact is a member of a group with resource identifier $resname
	 *
	 * @param \Google_Service_PeopleService_Person $c Contact to test group membership
	 * @param string $resname Resource name of group
	 * @return bool
	 */
	public static function isContactMemberOfGroup(\Google_Service_PeopleService_Person $c, $resname)
	{
		foreach ( $c->getMemberships() as $gr )
			if ( $gr->getContactGroupMembership() && ($gr->getContactGroupMembership()->contactGroupResourceName == $resname) )
				return true;
			
			
		return false;
	}
	
	
	
	/**
	 * Remove a group membership of a contact 
	 *
	 * @param \Google_Service_PeopleService_Person $c Contact to remove group membership
	 * @param string $resname Resource name of group
	 */
	public static function removeContactGroupMembership(\Google_Service_PeopleService_Person $c, $resname)
	{
		$memberships = $c->getMemberships();
		foreach ( $memberships as $k => $m )
			if ( $m->getContactGroupMembership() && ($m->getContactGroupMembership()->contactGroupResourceName == $resname) )
			{
				unset ($memberships[$k]);
				$c->setMemberships($memberships);
				break;
			}
	}		
	
	
	
	/**
	 * Add a group membership to a contact 
	 *
	 * @param \Google_Service_PeopleService_Person $c Contact to add group membership
	 * @param string $resname Resource name of group
	 */
	public static function addContactGroupMembership(\Google_Service_PeopleService_Person $c, $resname)
	{
		$memberships = $c->getMemberships();
		if ( $memberships == NULL )
			$memberships = [];
		
		$memberships[] = new \Google_Service_PeopleService_Membership(
			[
				'contactGroupMembership' => [
						'contactGroupResourceName' => $resname
					]
			]);

		$c->setMemberships($memberships);
	}		
	
	
	
	/**
	 * Get items of an object list with given type
	 * 
	 * @param \Google_Model[] $items Array of items to test
	 * @param string $type Value of 'type' property to search for
	 * @return \Google_Model[] Array of object having their 'type' property equal to $type
	 */		
	public static function getOfType($items, $type)
	{
		if ( is_array($items) )
			return array_filter($items, function($item) use ($type)
								{
									return $item->type == $type;
								});
		
		return [];
	}
    

	
	/**
	 * Get a list of contacts (with handling of pagination)
	 *
	 * @param string $resname Resource name to query contact to ; only 'people/me' is valid
	 * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
	 * @return \Stdclass Return a litteral object with a `connections[]` property (array of \Google_Service_PeopleService_Person - contact entries), `totalItems` (int), `nextSyncToken` (string)
	 */
	public function getAllContacts($resname, $optparams = array())
	{
		// prise en compte de la pagination
		$pageToken = NULL;
		
		// littéral objet renvoyé
		$ret = (object)['connections' => [], 'totalItems' => null, 'nextSyncToken' => null];
		
		
		do
		{
			// if received a token for next page, include it in the $optparams
			if ($pageToken)
				$optparams['pageToken'] = $pageToken;
			
			// request
			$contactsResponse = $this->_service->people_connections->listPeopleConnections($resname, $optparams);
			
			
			// if request ok
			if ( $ct = $contactsResponse->getConnections() )
			{
				$ret->connections = array_merge($ret->connections, $ct);
				$pageToken = $contactsResponse->nextPageToken;
			}
		} 
		while ($pageToken);

		
		// store nextSyncToken
		if ( $contactsResponse->nextSyncToken )
			$ret->nextSyncToken = $contactsResponse->nextSyncToken;
		
		$ret->totalItems = $contactsResponse->totalItems;		
		
		
		
		return $ret;
	}
    

	
	
	/**
	 * Get a list of contacts of a given group (with handling of pagination)
	 *
	 * @param string $resname Resource name to query contact from ; only 'people/me' is valid
	 * @param string $gresname Resource name of group
	 * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
	 * @return \Stdclass Return a litteral object with a `connections[]` property (array of \Google_Service_PeopleService_Person - contact entries), `totalItems` (int), `nextSyncToken` (string)
	 */
	public function getGroupContacts($resname, $gresname, $optparams = array())
	{
		// ajouter membership dans optparams
		if ( $optparams['personFields'] )
			$optparams['personFields'] .= ',memberships';
		else
			$optparams['personFields'] = 'memberships';
		
		
		// obtenir tous les contacts et filtrer ensuite
		$response = $this->getAllContacts($resname, $optparams);
		
		// ne garder que les contacts qui appartiennent au groupe demandé
		$response->connections = array_filter($contacts->connections, function($c) use ($gresname){
				return $this->isContactMemberOfGroup($c, $gresname);
			});
		$response->totalItems = count($response->connections);
		
		
		return $response;
	}
	

}


?>