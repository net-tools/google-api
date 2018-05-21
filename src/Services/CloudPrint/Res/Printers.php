<?php
/**
 * Printers
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint\Res;



/**
 * Printers resource
 */
class Printers extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/**
     * List printers available to the authenticated user or search a specific printer belonging to the authenticated user
     *
     * Parameters for search can be found in the API protocol reference. Among others, there are the Q and TYPE parameters.
     *
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\CloudPrint\ListPrinters Returns a printers list object (iterable collection object)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function search($optparams = array())
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\ListPrinters(
                $this->service->sendRequest(
                            // verb
                            'post', 
            
                            // url
                            'https://www.google.com/cloudprint/search', 
            
                            // guzzle request options
                            array(
                                // form_params for facultative requests options (such as Q or TYPE)
                                'form_params' => $optparams
                            )
                        )
            
                        ->printers
            );
    }
    
    
    
	/**
     * Get a printer
     *
     * @param string $printerid ID of printer to get
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\CloudPrint\Printer Returns a Printer object
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function get($printerid, $optparams = array())
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\Printer(
                $this->service->sendRequest(
                            // verb
                            'post', 

                            // url
                            'https://www.google.com/cloudprint/printer', 

                            // guzzle request options
                            array(
                                // form_params for facultative requests options (such as Q or TYPE)
                                'form_params' => array_merge(array('printerid'=>$printerid), $optparams)
                            )
                        )->printers[0]
            );
    }
    
}

?>