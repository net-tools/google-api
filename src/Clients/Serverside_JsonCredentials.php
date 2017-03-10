<?php
/**
 * Serverside_JsonCredentials
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Clients;




/**
 * Class to interface with Google APIs server-side with account credentials stored in a json file
 */
class Serverside_JsonCredentials extends Serverside
{
    /**
     * Constructor of interface to Google APIs with server-side business
     *
     * @param string $jsonfile Path to json credentials (to download from Google Developper Console)
     * @param string[] $scopes Array of strings with scopes (such as Google_Service_Calendar::CALENDAR_READONLY)
     * @param string[] $others Associative array of Google API config values
     */
    public function __construct($jsonfile, $scopes, $others = array())
    {
        // credentials are stored in a json file
        $others['authConfig'] = $jsonfile;
        
        // call parent constructor with parameters defined and merged with optional ones
        parent::__construct($scopes, $others);
    }
}

?>