<?php
/**
 * Jobs
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint\Res;



/**
 * Jobs resource
 */
class Jobs extends \Nettools\GoogleAPI\Services\Misc\Resource
{
	/**
     * List jobs of the authenticated user or search for jobs for a specific printer belonging to the authenticated user
     *
     * Parameters for search can be found in the API protocol reference. Among others, there are the Q and STATUS parameters.
     *
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Nettools\GoogleAPI\Services\CloudPrint\ListJobs Returns a jobs list object (iterable collection object)
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function search($optparams = array())
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\ListJobs(
                $this->service->sendRequest(
                            // verb
                            'post', 
            
                            // url
                            'https://www.google.com/cloudprint/jobs', 
            
                            // guzzle request options
                            array(
                                // form_params for facultative requests options (such as Q or STATUS)
                                'form_params' => $optparams
                            )
                        )
            
                        ->jobs
            );
    }
      
    
    
	/**
     * Get a print job 
     *
     * @param string $jobid ID of print job
     * @return \Nettools\GoogleAPI\Services\CloudPrint\Job Returns a job object
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function get($jobid)
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\Job(
            
                // get a litteral object with body and contentType properties
                $this->service->sendRequest(
                            // verb
                            'post', 
            
                            // url
                            'https://www.google.com/cloudprint/job', 
            
                            // guzzle request options
                            array(
                                // form_params 
                                'form_params' => array('jobid'=>$jobid)
                            )
                        )
            
                        ->job
            );
    }
    
    
    
	/**
     * Submit a print job
     *
     * @param string $printerid ID of printer to send job to
     * @param string $title Title of print job
     * @param string $content Content to print (as a string)
     * @param string $contentType Content-type to convert from (if `url`, then $content is a link to a public ressource ; otherwise, `application/pdf` is recommanded)
     * @param string $ticket Print ticket in CJT format ; defaults to `{"version": "1.0", "print": {}}`
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference; must be an array of associative array : [ ['name'=>'xxx', 'contents'=>'yyyy'] ]
     * @return bool Returns true if the job has been deleted ; if not, an exception is thrown
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function submit($printerid, $title, $content, $contentType = '', $ticket = '{"version": "1.0", "print": {}}', $optparams = array())
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\Job(
                $this->service->sendRequest(
                    // verb
                    'post', 

                    // url
                    'https://www.google.com/cloudprint/submit', 

            
                    // guzzle request options
                    array(
                        // multipart
                        'multipart' => 
                            array_merge(
                                array(
                                    array(
                                        'name'      => 'printerid',
                                        'contents'  => $printerid
                                    ),

                                    array(
                                        'name'      => 'title',
                                        'contents'  => $title
                                    ),

                                    array(
                                        'name'      => 'ticket',
                                        'contents'  => $ticket
                                    ),

                                    array(
                                        'name'      => 'contentType',
                                        'contents'  => ''
                                    ),

                                    array(
                                        'name'      => 'content',
                                        'contents'  => $content,
                                        'filename'  => 'upload',
                                        'headers'   => ['Content-Type'=>$contentType]
                                    )
                                ),
            
                                // optional request parameters, such as 'tag'
                                $optparams
                            )
                    )
                )
            
                ->job
            );
    }
   
    
    
	/**
     * Submit a print job from an URL
     *
     * @param string $printerid ID of printer to send job to
     * @param string $title Title of print job
     * @param string $url Url of document to download and print
     * @param string $ticket Print ticket in CJT format ; defaults to `{"version": "1.0", "print": {}}`
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return bool Returns true if the job has been deleted ; if not, an exception is thrown
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function submitUrl($printerid, $title, $url, $ticket = '{"version": "1.0", "print": {}}', $optparams = array())
	{
        return new \Nettools\GoogleAPI\Services\CloudPrint\Job(
                $this->service->sendRequest(
                    // verb
                    'post', 

                    // url
                    'https://www.google.com/cloudprint/submit', 
            
                    // guzzle request options
                    array(
                        // form_params for facultative requests options (such as Q or TYPE)
                        'form_params' => 
                            array_merge(
                                array(
                                    'printerid'     => $printerid,
                                    'title'         => $title,
                                    'ticket'        => $ticket,
                                    'content'       => $url,
                                    'contentType'   => 'url'
                                ),
            
                                // array of optional parameters, such as 'tag'
                                $optparams
                            )
                    )
                ) 
            
                ->job
            );
    }
   
    
    
	/**
     * Delete a job
     *
     * @param string $job ID of job to delete
     * @return bool Returns true if the job has been deleted ; if not, an exception is thrown
     * @throws \Google_Service_Exception Thrown if an error occured during the request
     */
	public function delete($jobid)
	{
        $this->service->sendRequest(
                    // verb
                    'post', 

                    // url
                    'https://www.google.com/cloudprint/deletejob', 
            
                    // guzzle request options
                    array(
                        // form_params
                        'form_params' => array('jobid'=>$jobid)
                    )
                );
        
        
        return true;
    }
}

?>