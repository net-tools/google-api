<?php
/**
 * Groups
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts\Resources;



/**
 * Groups resource
 */
class Groups extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/**
     * Get groups list
     *
     * @param string $userid User id to fetch groups from
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\ListGroups Returns a groups list object (iterable collection object)
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function getList($userid = 'default', $optparams = array())
	{
        // encoding @ character to urlencoding
		$userid = str_replace('@', '%40', $userid);
        
        
        return new \Nettools\GoogleAPI\Services\Contacts\ListGroups(
                $this->service->sendRequest(
                            // verb
                            'get', 
            
                            // url
                            "https://www.google.com/m8/feeds/groups/$userid/full", 
            
                            // optionnal parameters
                            $optparams
                        )
                    );
    }
    
    
    
	/**
     * Get a group
     *
     * @param string $selflink selflink of group to get (see $group->links and fetch the link whose REL attribute equals to 'self')
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a contact object
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function get($selflink, $optparams = array())
	{
        return \Nettools\GoogleAPI\Services\Contacts\Group::fromXmlEntry(
                    $this->service->sendRequest(
                                        // verb
                                        'get', 

                                        // url
                                        $selflink, 

                                        // optparams (query property of guzzlehttp)
                                        $optparams
                                    )
                );
    }
    
    
    
	/**
     * Update a group
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Group $group Group object
     * @param bool $overwrite Set this parameter to true to force updates even if the data on the server is more recent
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Group Returns a Group object with any updates applied
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function update(\Nettools\GoogleAPI\Services\Contacts\Group $group, $overwrite = false, $optparams = array())
	{
        // checking that we have the edit uri
        if ( !$group->linkRel('edit') || !$group->linkRel('edit')->href )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Group object doesn't have a link tag with rel='edit' attribute.");
            
        
        return \Nettools\GoogleAPI\Services\Contacts\Group::fromXmlEntry(
                    $this->service->sendRequest(
                                        // verb
                                        'put', 

                                        // url
                                        $group->linkRel('edit')->href,

                                        // optparams (query property of guzzlehttp)
                                        $optparams,
            
                                        // headers
                                        array(
                                            'Content-Type'  => 'application/atom+xml',
                                            'If-Match' => $overwrite ? '*' : $group->etag
                                        ),
            
                                        // body
                                        $group->asXml()
                                    )
                );
    }
    
    
    
	/**
     * Create a group
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Group $group Group object
     * @param string $userid Userid or 'default' special value
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Group Returns a Group object
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function create(\Nettools\GoogleAPI\Services\Contacts\Group $group, $userid = 'default', $optparams = array())
	{
        return \Nettools\GoogleAPI\Services\Contacts\Group::fromXmlEntry(
                    $this->service->sendRequest(
                                        // verb
                                        'post', 

                                        // url
                                        "https://www.google.com/m8/feeds/groups/" . str_replace('@', '%40', $userid) . "/full",

                                        // optparams (query property of guzzlehttp)
                                        $optparams,
            
                                        // headers
                                        array(
                                            'Content-Type'  => 'application/atom+xml'
                                        ),
            
                                        // body
                                        $group->asXml()
                                    )
                );
    }
    
    
    
	/**
     * Delete a group
     *
     * @param string $editlink editLink of group to delete (see $group->links and fetch the link whose REL attribute equals to 'edit')
     * @param string $etag Etag property of group to delete, as read in the $group->etag property ; to omit this security feature, pass '*' as $etag value
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return bool Always returns True, as if some error occurs, an exception is thrown
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function delete($editlink, $etag = '*', $optparams = array())
	{
        $this->service->sendRequest(
                            // verb
                            'delete', 

                            // url
                            $editlink, 

                            // optparams (query property of guzzlehttp)
                            $optparams,

                            // headers
                            array(
                                'If-Match' => $etag
                            )
                        );
        
        return true;
    }
}

?>