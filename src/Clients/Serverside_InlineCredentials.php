<?php
/**
 * Serverside_InlineCredentials
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Clients;




/**
 * Class to interface with Google APIs server-side with clientId and clientSecret credentials
 * 
 * The web app access user data with his explicit consentement, and we have to deal with accesstokens and authorization process 
 */
class Serverside_InlineCredentials extends Serverside
{
    /**
     * Constructor of interface to Google APIs with server-side business
     *
     * @param string $clientid Credentials from Google Developper console
     * @param string $clientsecret Credentials from Google Developper console
     * @param string[] $scopes Array of strings with scopes (such as Google_Service_Calendar::CALENDAR_READONLY)
     * @param string[] $others Associative array of Google API config values
     */
    public function __construct($clientid, $clientsecret, $scopes, $others = array())
    {
        // credentials
        $others['clientId'] = $clientid;
        $others['clientSecret'] = $clientsecret;
        
        // call parent constructor with parameters defined and merged with optional ones
        parent::__construct($scopes, $others);
    }
}

?>