<?php
/**
 * Service
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services;



/**
 * Abstract class for a Google API service we implement in our library.
 *
 * This is the case for Google Contacts or Cloud Print, which don't have a corresponding service in the Google library.
 */
abstract class Service
{
    /**
     * Google client object
     *
     * @var \Google_Client 
     */
    protected $_client = NULL;
    
    
    /** 
     * Default connection timeout 
     *
     * @var float 
     */
    protected $_connectTimeout = 10.0;

    
    /** 
     * Default request timeout 
     * 
     * @var float 
     */
    protected $_timeout = 30;
	
	
    
    /**
     * Magic method to read properties
     *
     * @param string $k Property name
     * @return mixed
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if property $k does not exist in object
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
        else
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' does not exist in '" . get_class($this) . "'.");
    }
    
    
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception if a property is read-only
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return ['client']; 
    }
    
    
    /**
     * Magic method to set properties
     *
     * @param string $k Property name
     * @param string $v Property value
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if property $k does not exist in object
     */
    public function __set($k, $v)
    {
        // detect read-only properties and forbid their assignement
        if ( in_array($k, $this->_getReadonlyProperties()) )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' is read-only in class '" . get_class($this) . "'.");

        // if property exists
        if ( property_exists($this, "_$k") )
            $this->{"_$k"} = $v;
        else
            // otherwise
            throw new \Nettools\GoogleAPI\Exceptions\Exception("Property '$k' does not exist in '" . get_class($this) . "'.");
    }
    
    
    /**
     * Get an exception from an API error (may be XML, string or JSON encoded)
     *
     * @param \Psr\Http\Message\ResponseInterface $response API response to parse
     * @param int $httpErrorCode HTTP error code (404, 403, etc.)
     * @return \Google_Service_Exception Returns an exception to be thrown ; contains json-encoded error as message, and the error code
     */
    abstract protected function _getException(\Psr\Http\Message\ResponseInterface $response, $httpErrorCode);
    
    
	/**
     * Send a request to an URL
     *
     * Request parameters (see Guzzle library) are given in the options associative array.
     * Supported keys are :
     * 
     * - string[] query : URI querystring as an assocative array
     * - string[] form_params : request as form items (request is sent with Content-Type header `application/x-www-form-urlencoded`) ; associative array of param names and values
     * - string[] headers : associative array of header names and values
     * - string|Psr\Http\Message\StreamInterface|resource body : request body (for a POST, PUT or PATCH verb) ; can be a string, a `Psr\Http\Message\StreamInterface` or fopen resource ; do not mix it with form_params or multipart
     * - string[][] multipart : send request as multipart/form-data ; usually used when uploading a file ; array of associative arrays with name (required), contents (required), headers, filename keys
     *
     * Refer to http://docs.guzzlephp.org/en/latest/request-options.html for more details about available options.
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param array $options Associative array of request options ; see below for available options
     * @return \Psr\Http\Message\ResponseInterface Returns the API response 
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function sendRequest($verb, $url, array $options = array())
	{
		// authorizing the connection by inserting headers with appropriate credentials
		$httpClient = $this->_client->authorize();

        // send request
		$resp = $httpClient->request($verb, $url, 
                                    array_merge(
                                        array(
                                            'timeout' => $this->timeout,
                                            'connect_timeout' => $this->connectTimeout
                                        ),
			
										$options
                                    )
								);
		
		
        if ( $this->requestSuccessful($resp) )
            return $resp;
        else
            throw $this->_getException($resp, $resp->getStatusCode());
	}
    
    
    
    /**
     * Check if a request has been successful 
     * 
     * @param \Psr\Http\Message\ResponseInterface $response API response to check
     * @return bool Returns true if the $response is successful (http code 200 or 201)
     */
    public function requestSuccessful (\Psr\Http\Message\ResponseInterface $response)
    {
		return in_array($response->getStatusCode(), array(200,201));
    }
    
    
    
    /**
     * Constructor of service 
     * 
     * @param \Google_Client $client Google_Client object to send requests with
     */
    public function __construct(\Google_Client $client)
    {
        $this->_client = $client;
    }
}

?>