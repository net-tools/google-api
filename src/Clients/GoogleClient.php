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
        else
            throw new \Nettools\GoogleAPI\Exceptions\ClientException("Property '$k' does not exist in '" . get_class($this) . "'.");
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
        // detect if service is implemented in Google API library
        $class = "\\Google_Service_$sname";
        if ( class_exists($class) )
        {
            // create the Google service
            $service = new $class($this->_client);

            // create the service wrapper classname
            $swrapperclass = '\\Nettools\\GoogleAPI\\ServiceWrappers\\' . $sname;

            // if our library defines this service wrapper, using it
            if ( class_exists($swrapperclass) )
                return new $swrapperclass($service);
            else
                // otherwise we return the Google service as created
                return $service;
        }
        
        // if no corresponding service in Google library
        else
        {
            // create our service implementation classname
            $sclass = '\\Nettools\\GoogleAPI\\Services\\' . $sname;
            if ( class_exists($sclass) )
                return new $sclass($this->_client);
            else
                throw \Nettools\GoogleAPI\Exceptions\ClientException("Service '$sname' is not implemented either in Google library or Nettools\\GoogleAPI.");
        }
    }
}

?>