<?php
/**
 * Contacts
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts\Resources;



/**
 * Contacts resource
 */
class Contacts extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/**
     * Get contacts list
     *
     * @param string $userid User id to fetch contacts from
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\ListContacts Returns a contacts list object (iterable collection object)
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function getList($userid = 'default', $optparams = array())
	{
        // encoding @ character to urlencoding
		$userid = str_replace('@', '%40', $userid);
        
        
        return new \Nettools\GoogleAPI\Services\Contacts\ListContacts(
                $this->service->sendRequest(
                            // verb
                            'get', 
            
                            // url
                            "https://www.google.com/m8/feeds/contacts/$userid/full", 
            
                            // optparams (query property of guzzlehttp)
                            array_merge( 
                                    array(
                                        'max-results' => '10000'
                                    ),

                                    $optparams
                                )
                            )
                        );
    }
    
    
    
	/**
     * Get a contact
     *
     * @param string $selflink selflink of contact to get (see $contact->links and fetch the link whose REL attribute equals to 'self')
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function get($selflink, $optparams = array())
	{
        return \Nettools\GoogleAPI\Services\Contacts\Contact::fromXmlEntry(
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
     * Update a contact
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @param bool $overwrite Set this parameter to true to force updates even if the data on the server is more recent
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object with any updates applied
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function update(\Nettools\GoogleAPI\Services\Contacts\Contact $contact, $overwrite = false, $optparams = array())
	{
        // checking that we have the edit uri
        if ( !$contact->linkRel('edit') || !$contact->linkRel('edit')->href )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Contact object doesn't have a link tag with rel='edit' attribute.");
            
                
        return \Nettools\GoogleAPI\Services\Contacts\Contact::fromXmlEntry(
                    $this->service->sendRequest(
                                        // verb
                                        'put', 

                                        // url
                                        $contact->linkRel('edit')->href,

                                        // optparams (query property of guzzlehttp)
                                        $optparams,
            
                                        // headers
                                        array(
                                            'Content-Type'  => 'application/atom+xml',
                                            'If-Match' => $overwrite ? '*' : $contact->etag
                                        ),
            
                                        // body
                                        $contact->asXml()
                                    )
                );
    }
    
    
    
	/**
     * Create a contact
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @param string $userid Userid or 'default' special value
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function create(\Nettools\GoogleAPI\Services\Contacts\Contact $contact, $userid = 'default', $optparams = array())
	{
        return \Nettools\GoogleAPI\Services\Contacts\Contact::fromXmlEntry(
                    $this->service->sendRequest(
                                        // verb
                                        'post', 

                                        // url
                                        "https://www.google.com/m8/feeds/contacts/" . str_replace('@', '%40', $userid) . "/full",

                                        // optparams (query property of guzzlehttp)
                                        $optparams,
            
                                        // headers
                                        array(
                                            'Content-Type'  => 'application/atom+xml'
                                        ),
            
                                        // body
                                        $contact->asXml()
                                    )
                );
    }
    
    
    
	/**
     * Delete a contact
     *
     * @param string $editlink editLink of contact to delete (see $contact->links and fetch the link whose REL attribute equals to 'edit')
     * @param string $etag Etag property of contact to delete, as read in the $contact->etag property ; to omit this security feature, pass '*' as $etag value
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