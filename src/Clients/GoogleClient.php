<?php
/**
 * GoogleClient
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Clients;




/**
 * Class to interface with Google APIs
 */
class GoogleClient
{
    /**
    * @var \Google_Client
    */
    protected $_client;
    
    
    
    /** 
     * Magic accessor to protected properties
     *
     * @param string $k Property name
     * @return mixed
     */
    public function __get($k)
    {
        if ( property_exists($this, "_$k") )
            return $this->{"_$k"};
    }
    
    
    /**
     * Constructor of interface to Google APIs
     *
     * @param string[] $initvalues Associative array of Google API config values (such as clientId, clientSecret, etc.)
     */
    public function __construct($initvalues = array())
    {
        $this->_client = new \Google_Client();
        
        foreach ( $initvalues as $k => $v )
            if ( method_exists($this->_client, 'set' . ucfirst($k)) )
                $this->_client->{'set' . ucfirst($k)}($v);
    }
    
    
    /**
     * Obtenir un service 
     *
     * @param string $sname Service name (for example, 'Calendar' or 'Drive')
     * @return ServicesWrappers\ServiceWrapper|\Google_Service Returns a service wrapper (such as ServiceWrappers\Calendar) if available or an object directly created from Google API library (\Google_Service_xxxxx where xxxxx is the service name)
     */
    public function getService($sname)
    {
        // creating the underlying Google Service
        $class = "\\Google_Service_$sname";
        $service = new $class($this->_client);
        
        // creating the service name with namespace
        $sclass = '\\Nettools\\GoogleAPI\\ServiceWrappers\\' . $sname;

        // if our library defines this service, using it
        if ( class_exists($sclass) )
            return new $sclass($service);
        else
            return $service;
    }
}

?>