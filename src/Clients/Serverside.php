<?php
/**
 * Serverside
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */



namespace Nettools\GoogleAPI\Clients;




/**
 * Class to interface with Google APIs server-side
 * 
 * The web app access user data with his explicit consentement, and we have to deal with accesstokens and authorization process 
 */
abstract class Serverside extends GoogleClient
{
    /**
     * Constructor of interface to Google APIs with server-side business
     *
     * @param string[] $scopes Array of strings with scopes (such as Google\Service\Calendar::CALENDAR_READONLY)
     * @param string[] $others Associative array of Google API config values
     */
    public function __construct($scopes, $others = array())
    {
        // scopes to request
        $others['scopes'] = $scopes;
        
        // the redirect URI is the URI where Google will point the user after successful authorization ; by default, this is the script URL
		if ( !array_key_exists('redirectUri', $others) )
        	$others['redirectUri'] = 'https://' . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['PHP_SELF'], '/');

        // the user will see a screen informing him about exactly which access rights he is granting your application
        //$others['prompt'] = 'force';
        
        
        // call parent constructor with parameters defined and merged with optional ones
        parent::__construct($others);
    }
    
    
    /**
     * Set the access token in the Google\Client object
     * 
     * Method is provided as a convenient way of setting the access token
     *
     * @param string|string[] Access token
     */
    public function setAccessToken($token)
    {
        $this->_client->setAccessToken($token);
    }
    
    
    /**
     * Begin authorization process
     *
     * The script will be halted and the user redirected to Google login page so that he could grant your application access to his personnal data.
     * When returning back from Google login, the redirectUri will be called with a CODE querystring parameter you have to exchange for an access token
     * by calling endAuthorizationProcess().
     * 
     * @param bool $returnUrl If this parameter equals to True, the authorization process URL will be returned as a string ; if false (default value), the script is redirected automatically to the URL
     */
    public function beginAuthorizationProcess($returnUrl = false)
    {
        $url = $this->_client->createAuthUrl();
        
        if ( $returnUrl )
            return $url;
        else
        {
            header('Location: ' . $url);
            die();
        }
    }
    
    
    /**
     * Are we dealing with an authorization process ?
     *
     * @return bool Returns true if we are coming back from Google login (authorization process live)
     */
    public function isAuthorizationProcessLive()
    {
        return isset($_GET['code']);
    }
    
    
    /**
     * End authorization process
     *
     * When coming back from Google login, the redirectUri is be called with a CODE querystring parameter ; we exchange it for an access token
     *
     * @return string[] The access token
     */
    public function endAuthorizationProcess()
    {
        return $this->_client->fetchAccessTokenWithAuthCode($_GET['code']);
    }
}

?>