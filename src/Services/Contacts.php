<?php
/**
 * Contacts
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services;



/**
 * Contacts service
 *
 * Provides helper functions to get/set extended properties. Other method calls are transfered to the underlying service object.
 */
class Contacts extends Service
{
    protected $_contacts = NULL;
    protected $_contacts_photos = NULL;
    protected $_groups = NULL;
    
    
	const CONTACTS = "http://www.google.com/m8/feeds/";
	const CONTACTS_READONLY = "https://www.googleapis.com/auth/contacts.readonly";

    
    
    
    /**
     * Constructor of Contacts service 
     * 
     * @param \Google_Client $client Google_Client object to send requests with
     */
    public function __construct(\Google_Client $client)
    {
        parent::__construct($client);
        
        // create resources
        $this->_contacts = new Contacts\Resources\Contacts($this);
        $this->_contacts_photos = new Contacts\Resources\Contacts_Photos($this);
        $this->_groups = new Contacts\Resources\Groups($this);
    }

    
    
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array_merge(['contacts', 'groups', 'contacts_photos'], parent::_getReadonlyProperties()); 
    }
    
    
	/**
     * Send a request to an URL and return raw result
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @param string[] $headers Array of headers for request (associative array header-name => header-value)
     * @param string $body Body of request (as a string or any type accepted by Guzzle)
     * @return \Stdclass Returns the API response as an object with properties body and contentType
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function sendRequestRaw($verb, $url, $optparams = array(), $headers = array(), $body = NULL)
	{
        $headers = array_merge(array('GData-Version'=>'3.0'), $headers);
        $resp = parent::sendRequest($verb, $url, $optparams, $headers, $body);
        
        return (object) ['body' => (string)($resp->getBody()), 'contentType' => $resp->getHeader('Content-Type')[0]];
	}
    
    
	/**
     * Send a request to an URL
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @param string[] $headers Array of headers for request (associative array header-name => header-value)
     * @param string $body Body of request (as a string or any type accepted by Guzzle)
     * @return string Returns the API response as a SimpleXMLElement
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function sendRequest($verb, $url, $optparams = array(), $headers = array(), $body = NULL)
	{
        $headers = array_merge(array('GData-Version'=>'3.0'), $headers);
        return simplexml_load_string((string)(parent::sendRequest($verb, $url, $optparams, $headers, $body)->getBody()));
	}
	

}

?>