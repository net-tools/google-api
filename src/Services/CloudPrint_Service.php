<?php
/**
 * CloudPrint_Service
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services;



/**
 * CloudPrint service
 *
 * Please refer to Cloudprint.README.md on package root for instructions.
 */
class CloudPrint_Service extends Service
{
    /** @var string Scope for managing print jobs */
    const CLOUDPRINT = 'https://www.googleapis.com/auth/cloudprint';
    

    
    protected $_printers = NULL;
    protected $_jobs = NULL;
    
    
    
    
    /**
     * Constructor of CloudPrint service 
     * 
     * @param \Google_Client $client Google_Client object to send requests with
     */
    public function __construct(\Google_Client $client)
    {
        parent::__construct($client);
        
        // create resources
        $this->_printers = new CloudPrint\Res\Printers($this);
        $this->_jobs = new CloudPrint\Res\Jobs($this);
    }

    
    
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array_merge(['printers', 'jobs'], parent::_getReadonlyProperties()); 
    }
    
    
    /**
     * Get an exception from a json-formatted error from CloudPrint API
     *
     * @param \Psr\Http\Message\ResponseInterface $response API response to check
     * @param int $httpErrorCode Code erreur HTTP (404, 403, etc.)
     * @return \Google_Service_Exception Returns an exception object to be thrown
     */
    protected function _getException(\Psr\Http\Message\ResponseInterface $response, $httpErrorCode)
    {
        $body = (string)$response->getBody();

        
        // if error in json format
        if ( $json = json_decode($body) )
        {
            $code = $json->errorCode;
            $msg = $json->message;
        }

        // if error in html format
        else
        if ( is_int(strpos($body, '<H2>Error')) )
        {
            if ( preg_match('#<H2>Error ([0-9]+)</H2>#', $body, $regs) )
                $code = $regs[1];
            if ( preg_match('#<H1>(.*)</H1>#', $body, $regs) )
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
     * @return \Stdclass Returns the API response as an object (JSON-formatted string decoded)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function sendRequest($verb, $url, array $options = array())
	{
        $psr_response = parent::sendRequest($verb, $url, $options);
        $resp = json_decode((string)$psr_response->getBody());
        
        if ( $resp )
            if( !$resp->success )
                throw $this->_getException($psr_response, $resp->errorCode);
            else
                return $resp;
        else   
            throw new Google_Service_Exception((string)$psr_response->getBody(), 0);
	}
	

}

?>