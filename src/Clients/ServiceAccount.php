<?php
/**
 * ServiceAccount
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Clients;




/**
 * Class to interface with Google APIs with a service account (credentials stored in a json file)
 */
class ServiceAccount extends GoogleClient
{
    /**
     * Constructor of interface to Google APIs with a service account (not dealing with user's data, but interacts with the google dev account data)
     *
     * @param string $jsonfile Path to json credentials of service account (to download from Google Developper Console)
     * @param string[] $scopes Array of strings with scopes (such as 'https://www.googleapis.com/auth/devstorage.readonly')
     * @param string[] $others Associative array of Google API config values
     */
    public function __construct($jsonfile, $scopes, $others = array())
    {
        // credentials are stored in a json file ; the json file will contain a type=service_account line, so the useApplicationDefaultCredentials() method
        // will be called on Google\Client to initialize the service account credentials
        $others['authConfig'] = $jsonfile;
        
        // scopes to request
        $others['scopes'] = $scopes;
        
        
        // call parent constructor with parameters defined and merged with optionnal ones
        parent::__construct($others);
    }
}

?>