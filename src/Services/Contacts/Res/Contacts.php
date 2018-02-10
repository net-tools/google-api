<?php
/**
 * Contacts
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts\Res;



use \Nettools\GoogleAPI\Services\Contacts\Batch;



/**
 * Contacts resource
 */
class Contacts extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/** 
	 * Create a contacts batch
	 *
	 * @param string $userid Userid of special value 'default'
	 * @return \Nettools\GoogleAPI\Services\Contacts\Batch 
	 */
	public function createBatch($userid = 'default')
	{
		return new Batch($this->service, Batch::BATCH_CONTACTS, \Nettools\GoogleAPI\Services\Contacts\Contact::class, $userid);
	}
	
	
	
	/**
     * Get contacts list
     *
     * @param string $userid User id to fetch contacts from
     * @param string[] $optparams Associative array of querystring parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\Contacts\ListContacts Returns a contacts list object (iterable collection object)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
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
            
                            // guzzle request options
                            array(
                                // query for facultative requests options (such as Q )
                                'query' => 
                                    // querystring (query property of guzzlehttp)
                                    array_merge( 
                                        array(
                                            'max-results' => '10000'
                                        ),

                                        $optparams
                                    )
                            )
                        )
                    );
    }
    
    
    
	/**
     * Batch get a contact
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Batch $batch Batch object to add the request to
	 * @param string $batchid ID of batch request
     * @param string $selflink selfLink of contact to get (see $contact->links and fetch the link whose REL attribute equals to 'self')
     * @return \Nettools\GoogleAPI\Services\Contacts\Batch Returns the batch object (for chaining)
     */
	public function batchGet(Batch $batch, $batchid, $selflink)
	{
		return $batch->add($batchid, 'query', "<id>$selflink</id>");
	}
    
    
    
	/**
     * Get a contact
     *
     * @param string $selflink selflink of contact to get (see $contact->links and fetch the link whose REL attribute equals to 'self')
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function get($selflink)
	{
        return \Nettools\GoogleAPI\Services\Contacts\Contact::fromFeed(
                    $this->service->sendRequest(
                                        // verb
                                        'get', 

                                        // url
                                        $selflink
                                    )
                );
    }
    
    
    
	/**
     * Batch update a contact
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Batch $batch Batch object to add the request to
	 * @param string $batchid ID of batch request
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @param bool $overwrite Set this parameter to true to force updates even if the data on the server is more recent
     * @return \Nettools\GoogleAPI\Services\Contacts\Batch Returns the batch object (for chaining)
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if request cannot be submitted (usually due to wrong parameters)
     */
	public function batchUpdate(Batch $batch, $batchid, \Nettools\GoogleAPI\Services\Contacts\Contact $contact, $overwrite = false)
	{
        // checking that we have the edit uri
        if ( !$contact->linkRel('edit') || !$contact->linkRel('edit')->href )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Contact object doesn't have a link tag with rel='edit' attribute.");


		
		// get contact as XML string
		$xml = $contact->asXml();
		$xml = preg_replace('/<entry [^>]*/', '<entry', trim(preg_replace('/<\?xml[^>]*>/', '', $xml)));
		$xml = str_replace('<entry>', "<entry><category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/g/2008#contact'/>", $xml);
		return $batch->add($batchid, 'update', $xml, $overwrite ? '*' : $contact->etag);
    }
    
    
    
	/**
     * Update a contact
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @param bool $overwrite Set this parameter to true to force updates even if the data on the server is more recent
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object with any updates applied
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if request cannot be submitted (usually due to wrong parameters)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function update(\Nettools\GoogleAPI\Services\Contacts\Contact $contact, $overwrite = false)
	{
        // checking that we have the edit uri
        if ( !$contact->linkRel('edit') || !$contact->linkRel('edit')->href )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Contact object doesn't have a link tag with rel='edit' attribute.");
            
                
        return \Nettools\GoogleAPI\Services\Contacts\Contact::fromFeed(
                    $this->service->sendRequest(
                                        // verb
                                        'put', 

                                        // url
                                        $contact->linkRel('edit')->href,

                                        // guzzle request options
                                        array(
                                            // body
                                            'body' => $contact->asXml(),
            
                                            // headers
                                            'headers' => array(
                                                            'Content-Type'  => 'application/atom+xml',
                                                            'If-Match' => $overwrite ? '*' : $contact->etag
                                                        )
                                        )
                                    )
                );
    }
    
    
    
	/**
     * Create a contact (batch)
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Batch $batch Batch object to add the request to
	 * @param string $batchid ID of batch request
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @return \Nettools\GoogleAPI\Services\Contacts\Batch Returns the batch object (for chaining)
     */
	public function batchCreate(Batch $batch, $batchid, \Nettools\GoogleAPI\Services\Contacts\Contact $contact)
	{
		// get contact as XML string
		$xml = $contact->asXml();
		
		// remove namespace from entry xml
		$xml = preg_replace('/<entry [^>]*/', '<entry', trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml)));
		$xml = str_replace('<entry>', "<entry><category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/g/2008#contact'/>", $xml);
		return $batch->add($batchid, 'insert', $xml);
    }
    
    
    
	/**
     * Create a contact
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Contact $contact Contact object
     * @param string $userid Userid or 'default' special value
     * @return \Nettools\GoogleAPI\Services\Contacts\Contact Returns a Contact object
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function create(\Nettools\GoogleAPI\Services\Contacts\Contact $contact, $userid = 'default')
	{
		return \Nettools\GoogleAPI\Services\Contacts\Contact::fromFeed(
					$this->service->sendRequest(
										// verb
										'post', 

										// url
										"https://www.google.com/m8/feeds/contacts/" . str_replace('@', '%40', $userid) . "/full",

										// guzzle request options
										array(
											// body
											'body' => $contact->asXml(),

											// headers
											'headers' => array(
															'Content-Type'  => 'application/atom+xml'
														)
										)
									)
				);
    }
    
    
    
	/**
     * Batch delete a contact
     *
	 * @param \Nettools\GoogleAPI\Services\Contacts\Batch $batch Batch object to add the request to
	 * @param string $batchid ID of batch request
     * @param string $editlink editLink of contact to delete (see $contact->links and fetch the link whose REL attribute equals to 'edit')
     * @param string $etag Etag property of contact to delete, as read in the $contact->etag property ; to omit this security feature, pass '*' as $etag value
     * @return \Nettools\GoogleAPI\Services\Contacts\Batch Returns the batch object (for chaining)
     */
	public function batchDelete(Batch $batch, $batchid, $editlink, $etag = '*')
	{
		return $batch->add($batchid, 'delete', "<entry><id>$editlink</id></entry>", $etag);
	}
    
    
    
	/**
     * Delete a contact
     *
     * @param string $editlink editLink of contact to delete (see $contact->links and fetch the link whose REL attribute equals to 'edit')
     * @param string $etag Etag property of contact to delete, as read in the $contact->etag property ; to omit this security feature, pass '*' as $etag value
     * @return bool Always returns True, as if some error occurs, an exception is thrown
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function delete($editlink, $etag = '*')
	{
        $this->service->sendRequest(
                            // verb
                            'delete', 

                            // url
                            $editlink, 

                            // guzzle request options
                            array(
                                // headers
                                'headers' => array('If-Match' => $etag)
                            )
                        );
        
        return true;
    }
}

?>