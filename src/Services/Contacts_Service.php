<?php
/**
 * Contacts_Service
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services;



/**
 * Contacts service
 *
 * Please refer to Contacts.README.md on package root for instructions.
 */
class Contacts_Service extends Service
{
    protected $_contacts = NULL;
    protected $_contacts_photos = NULL;
    protected $_groups = NULL;
    
    
    /** @var string Scope for read/write access */
	const CONTACTS = "http://www.google.com/m8/feeds/";

    /** @var string Scope for read-only access */
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
        $this->_contacts = new Contacts\Res\Contacts($this);
        $this->_contacts_photos = new Contacts\Res\Contacts_Photos($this);
        $this->_groups = new Contacts\Res\Groups($this);
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
     * Get an exception from a string-formatted or xml-formatted error from Contacts API
     *
     * @param \Psr\Http\Message\ResponseInterface $response API response to check
     * @param int $httpErrorCode Code erreur HTTP (404, 403, etc.)
     * @return \Google_Service_Exception Returns an exception object to be thrown
     */
    protected function _getException(\Psr\Http\Message\ResponseInterface $response, $httpErrorCode)
    {
        $body = (string)$response->getBody();
        
        
        // if error in xml format
        if ( is_int(strpos($body, '<?xml')) )
        {
            $xml = simplexml_load_string($body);
            $code = $response->getStatusCode();
            $msg = (string)$xml->error->internalReason;
        }
        
        // if error in html format
        else
        if ( is_int(strpos($body, '<p><b>')) )
        {
            if ( preg_match('#<p><b>([0-9]+)\\.</b>#', $body, $regs) )
                $code = $regs[1];
            if ( preg_match('#<p>([a-zA-Z].*)</p>#s', $body, $regs) )
                $msg = strip_tags($regs[1]);
        }
        
        // fallback to default message
        else
            $msg = $body;

        
        $e = (object)array();
        $e->error = (object)array(
                                'errors' => [(object)['message'=>$msg]],
                                'code' => $code ? (int)$code : $httpErrorCode,
                                'message' => $msg
                            );
        return new \Google_Service_Exception(json_encode($e, JSON_PRETTY_PRINT), $code?(int)$code:$httpErrorCode);
    }
    
    
	/**
     * Send a request to an URL and get raw result (body and contentType)
     *
     * Request parameters (see Guzzle library) are given in the options associative array.
     * Supported keys are :
     * 
     * - string query : URI querystring
     * - string[][] form_params : request as form items (request is sent with Content-Type header `application/x-www-form-urlencoded`) ; associative array of param names and values
     * - string[][] headers : associative array of header names and values
     * - string|Psr\Http\Message\StreamInterface|resource body : request body (for a POST, PUT or PATCH verb) ; can be a string, a `Psr\Http\Message\StreamInterface` or fopen resource ; do not mix it with form_params or multipart
     * - string[][] multipart : send request as multipart/form-data ; usually used when uploading a file ; array of associative arrays with name (required), contents (required), headers, filename keys
     *
     * Refer to http://docs.guzzlephp.org/en/latest/request-options.html for more details about available options.
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param array Associative array of request options ; see below for available options
     * @return \Stdclass Returns the API response as an object with properties body and contentType
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function sendRequestRaw($verb, $url, array $options = array())
	{
        // add gdata-version header
        $options['headers'] or $options['headers'] = array();
        $options['headers']['GData-Version'] = '3.0';
        
        $resp = parent::sendRequest($verb, $url, $options);
        
        return (object) ['body' => (string)($resp->getBody()), 'contentType' => $resp->getHeader('Content-Type')[0]];
	}
    
    
	/**
     * Send a request to an URL
     *
     * Request parameters (see Guzzle library) are given in the options associative array.
     * Supported keys are :
     * 
     * - string query : URI querystring
     * - string[][] form_params : request as form items (request is sent with Content-Type header `application/x-www-form-urlencoded`) ; associative array of param names and values
     * - string[][] headers : associative array of header names and values
     * - string|Psr\Http\Message\StreamInterface|resource body : request body (for a POST, PUT or PATCH verb) ; can be a string, a `Psr\Http\Message\StreamInterface` or fopen resource ; do not mix it with form_params or multipart
     * - string[][] multipart : send request as multipart/form-data ; usually used when uploading a file ; array of associative arrays with name (required), contents (required), headers, filename keys
     *
     * Refer to http://docs.guzzlephp.org/en/latest/request-options.html for more details about available options.
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param array Associative array of request options ; see below for available options
     * @return string Returns the API response as a SimpleXMLElement
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function sendRequest($verb, $url, array $options = array())
	{
        // add gdata-version header
        $options['headers'] or $options['headers'] = array();
        $options['headers']['GData-Version'] = '3.0';

        $resp = parent::sendRequest($verb, $url, $options);
        return simplexml_load_string((string)($resp->getBody()));
	}
	

}

?>