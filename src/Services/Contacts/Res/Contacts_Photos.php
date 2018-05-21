<?php
/**
 * Contacts_Photos
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts\Res;



/**
 * Contacts photos resource
 */
class Contacts_Photos extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/**
     * Get contact photo
     *
     * @param string $photolink Link of contact photo to get (see $contact->links and fetch the link whose REL attribute equals to 'http://schemas.google.com/contacts/2008/rel#photo')
     * @return \Nettools\GoogleAPI\Services\Contacts\Photo Returns a Photo object (containing body and contentType properties)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function get($photolink)
	{
        return new \Nettools\GoogleAPI\Services\Contacts\Photo($this->service->sendRequestRaw(
                                        // verb
                                        'get', 

                                        // url
                                        $photolink 
                                    ));
    }
    
    
    
	/**
     * Update/create a photo
     *
     * @param \Nettools\GoogleAPI\Services\Contacts\Photo $photo Photo object (content-type and body as a binary string)
     * @param string $etag Etag property of photo to update, as read in the photo link etag property ; to omit this security feature, pass '*' as $etag value
     * @return bool Returns true when the upload is finished
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function update(\Nettools\GoogleAPI\Services\Contacts\Photo $photo, $photolink, $etag = '*')
	{
        $this->service->sendRequest(
                            // verb
                            'put', 

                            // url
                            $photolink,

                            // guzzle request options
                            array(
                                // body
                                'body' => $photo->body,

                                // headers
                                'headers' => array(
                                                'Content-Type'  => $photo->contentType,
                                                'If-Match' => $etag
                                            )
                            )
                        );
        
        return true;
    }
    
    
    
	/**
     * Delete a contact photo
     *
     * @param string $photolink Photo link of contact to delete (see $contact->links and fetch the link whose REL attribute equals to 'http://schemas.google.com/contacts/2008/rel#photo')
     * @param string $etag Etag property of contact with photo to delete, as read in the photo link etag property ; to omit this security feature, pass '*' as $etag value
     * @return bool Always returns True, as if some error occurs, an exception is thrown
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function delete($photolink, $etag = '*')
	{
        $this->service->sendRequest(
                            // verb
                            'delete', 

                            // url
                            $photolink, 

                            // guzzle request options
                            array(
                                // headers
                                'headers' => array(
                                                'If-Match' => $etag
                                            )
                            )
                        );
        
        return true;
    }
}

?>