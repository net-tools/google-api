<?php
/**
 * Service
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services;



/**
 * Abstract class for a Google API service we implement in our library.
 *
 * This is the case for Google Contacts or Cloud Print, which don't have a corresponding service in the Google library.
 */
class Service
{
    /** @var \Google_Client Google client object */
    protected $_client = NULL;
    
    /** @var float Default connection timeout */
    protected $_connectTimeout = 5.0;

    /** @var float Default request timeout */
    protected $_timeout = 30;
	
	
    /**
     * Magic method to read properties
     *
     * @param string $k Property name
     * @return mixed
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if property $k does not exist in object
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
        else
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Property '$k' does not exist in '" . get_class($this) . "'.");
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
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if property $k does not exist in object
     */
    public function __set($k, $v)
    {
        // detect read-only properties and forbid their assignement
        if ( in_array($k, $this->_getReadonlyProperties()) )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Property '$k' is read-only in class '" . get_class($this) . "'.");

        // if property exists
        if ( property_exists($this, "_$k") )
            $this->{"_$k"} = $v;
        else
            // otherwise
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("Property '$k' does not exist in '" . get_class($this) . "'.");
    }
    
    
	/**
     * Send a request to an URL
     *
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE)
     * @param string $url Url to send request to
     * @param string[] $optparams Array of querystring parameters for request, as defined in the API protocol reference
     * @param string[] $headers Array of headers for request (associative array header-name => header-value)
     * @param string $body Body of request (as a string or any type accepted by Guzzle)
     * @return \Psr\Http\Message\ResponseInterface Returns the API response 
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if an error occured during the request
     */
	public function sendRequest($verb, $url, $optparams = array(), $headers = array(), $body = NULL)
	{
		// authorizing the connection by inserting headers with appropriate credentials
		$httpClient = $this->_client->authorize();

        // send request
		$resp = $httpClient->$verb($url, 
                                    array_merge(
                                        $body ? array('body'=>$body):array(),
                                        
                                        array(
                                            'query'	=> $optparams,
                                            'headers' => $headers,
                                            'timeout' => $this->timeout,
                                            'connect_timeout' => $this->connectTimeout
                                        ) 
                                    )
								);

		// if no error, load XML response and return it
		if ( in_array($resp->getStatusCode(), array(200,201)) )
			return $resp;
		else
			throw new \Nettools\GoogleAPI\Exceptions\ServiceException((string)$resp->getBody() . " (HTTP CODE " . $resp->getStatusCode() . ")");
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