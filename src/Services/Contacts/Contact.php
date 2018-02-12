<?php
/**
 * Contact
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



use \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty;
use \Nettools\GoogleAPI\Services\Misc\ArrayProperty;





/**
 * Contact object
 */
class Contact extends Element
{
	const GD_NS 		= 'http://schemas.google.com/g/2005';
	const GCONTACT_NS 	= 'http://schemas.google.com/contact/2008';
	const TYPE_HOME 	= "http://schemas.google.com/g/2005#home";
	const TYPE_WORK 	= "http://schemas.google.com/g/2005#work";
	const TYPE_MOBILE 	= "http://schemas.google.com/g/2005#mobile";
    const TYPE_PHOTO    = 'http://schemas.google.com/contacts/2008/rel#photo';
    
    
    
    /** @var string */
    protected $_familyName;

    /** @var string */
    protected $_givenName;

    /** @var string */
    protected $_fullName;

    /** @var string */
    protected $_nickName;

    /** @var string */
    protected $_birthday;

    /** 
     * Object with orgName, orgTitle, rel/label properties 
     * 
     * @var \Stdclass  
     */
    protected $_organization;
    
    /**
     * Array of objects with relation and rel/label properties
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty
     */
    protected $_relations = NULL;

    /**
     * Array of objects with address, primary and rel/label properties
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty
     */
    protected $_emails = NULL;

    /** 
     * Array of objects with when and rel/label properties 
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty 
     */
    protected $_events = NULL;

    /** 
     * Array of objects with address, protocol and rel/label properties 
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty 
     */
    protected $_ims = NULL;

    /**
     * Array of objects with href and rel/label properties
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty 
     */
    protected $_websites = NULL;

    /**
     * Array of objects with phoneNumber, uri and rel/label properties
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty
     */
    protected $_phoneNumbers = NULL;

    /** 
     * Array of objects with city, postcode, formattedAddress, street, region, country and rel/label properties
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\RelLabelArrayProperty  
     */
    protected $_structuredPostalAddresses = NULL;
    
    /** 
     * Array of group IDs
     * 
     * @var \Nettools\GoogleAPI\Services\Misc\ArrayProperty
     */
    protected $_groupsMembershipInfo = NULL;

    /** 
     * Array of objects with key and value properties
     *
     * @var \Nettools\GoogleAPI\Services\Misc\ArrayProperty
     */
    protected $_userDefinedFields = NULL;

    /** 
     * Array of objects with name and value properties
     *
     * @var \Nettools\GoogleAPI\Services\Misc\ArrayProperty
     */
    protected $_extendedProperties = NULL;

    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_emails = new RelLabelArrayProperty([]);
        $this->_relations = new RelLabelArrayProperty([]);
        $this->_events = new RelLabelArrayProperty([]);
        $this->_ims = new RelLabelArrayProperty([]);
        $this->_websites = new RelLabelArrayProperty([]);
        $this->_phoneNumbers = new RelLabelArrayProperty([]);
        $this->_structuredPostalAddresses = new RelLabelArrayProperty([]);
        $this->_groupsMembershipInfo = new ArrayProperty([]);
        $this->_userDefinedFields = new ArrayProperty([]);
        $this->_extendedProperties = new ArrayProperty([]);
    }
    
    

    /**
     * Magic method to set properties
     *
     * For ArrayProperty properties, we check if the argument to set is an array ; if so we convert it to an ArrayProperty or a RelLabelArrayProperty
     *
     * @param string $k Property name
     * @param ArrayProperty|array|mixed $v Value to set to property `$k`
	 * @throw \Nettools\GoogleAPI\Exceptions\Exception Thrown if the `$v` value cannot be assigned to property `$k`
     */     
    public function __set($k, $v)
    {
        // if we attempt to set one of the ArrayProperty properties
        if ( in_array($k, ['emails', 'relations', 'events', 'ims', 'websites', 'phoneNumbers', 'structuredPostalAddresses', 'groupsMembershipInfo', 'userDefinedFields', 'extendedProperties']) )
            if ( is_object($v) && ($v instanceof ArrayProperty) )
                $this->{"_$k"} = $v;
            else
            if ( is_array($v) )
				if ( in_array($k, ['emails', 'relations', 'events', 'ims', 'websites', 'phoneNumbers', 'structuredPostalAddresses']) )
                	$this->{"_$k"} = new RelLabelArrayProperty($v);
				else
                	$this->{"_$k"} = new ArrayProperty($v);
            else
                throw new \Nettools\GoogleAPI\Exceptions\Exception("Assigning a value of type '" . gettype($v) . "' to ArrayProperty '$k' is not allowed.");
        else
            parent::__set($k, $v);
    }
        
    
        
    /**
     * Update a SimpleXMLElement object with the Contact properties
     * 
     * @param \SimpleXMLElement $xml XML object to update with Contact properties
     */
    public function toXml(\SimpleXMLElement $xml)
    {
        // call Element method
        parent::toXml($xml);
        
		
        // erase nodes which are unique with children ; we will rebuilt them from scratch
		$gdnodes = $xml->children('gd', true);
		unset($gdnodes->name[0]);
		
        
        // erase nodes which more than 1 occurence (such as structuredPostalAddress or email ; they can have several rel atttributes)
		$xml->registerXPathNamespace('gd', self::GD_NS);
        
		$xpath = $xml->xpath('//gd:phoneNumber');
		foreach ( $xpath as $x )
			unset($x[0]);

        $xpath = $xml->xpath('//gd:structuredPostalAddress');
		foreach ( $xpath as $x )
			unset($x[0]);

        $xpath = $xml->xpath('//gd:email');
		foreach ( $xpath as $x )
			unset($x[0]);

        $xpath = $xml->xpath('//gd:organization');
		foreach ( $xpath as $x )
			unset($x[0]);

        $xpath = $xml->xpath('//gd:im');
		foreach ( $xpath as $x )
			unset($x[0]);

		$xpath = $xml->xpath('//gd:extendedProperty');
		foreach ( $xpath as $x )
			unset($x[0]);

		

		$xml->registerXPathNamespace('gContact', self::GCONTACT_NS);
        
		$xpath = $xml->xpath('//gContact:nickname');
		foreach ( $xpath as $x )
			unset($x[0]);
        
		$xpath = $xml->xpath('//gContact:birthday');
		foreach ( $xpath as $x )
			unset($x[0]);

        $xpath = $xml->xpath('//gContact:event');
		foreach ( $xpath as $x )
			unset($x[0]);
        
		$xpath = $xml->xpath('//gContact:relation');
		foreach ( $xpath as $x )
			unset($x[0]);
        
		$xpath = $xml->xpath('//gContact:website');
		foreach ( $xpath as $x )
			unset($x[0]);

		$xpath = $xml->xpath('//gContact:userDefinedField');
		foreach ( $xpath as $x )
			unset($x[0]);

       
        // rebuild NAME entry
		if ( $this->_familyName || $this->_givenName )
		{
			$gdname = $xml->addChild('name', '', self::GD_NS);
			if ( $this->_familyName )
				$gdname->addChild('familyName', $this->_familyName, self::GD_NS);
			if ( $this->_givenName )
				$gdname->addChild('givenName', $this->_givenName, self::GD_NS);
		}
		
        
        // nickname
        if ( $this->_nickName )
            $xml->addChild('nickname', $this->_nickName, self::GCONTACT_NS);
        
        // birthday
        if ( $this->_birthday )
            $xml->addChild('birthday', '', self::GCONTACT_NS)->addAttribute('when', $this->_birthday);
        
        // organization
        if ( $this->_organization )
        {
            $org = $xml->addChild('organization', '', self::GD_NS);
            $org->addAttribute(isset($this->_organization->rel) ? 'rel':'label', isset($this->_organization->rel) ? $this->_organization->rel : $this->_organization->label);
            $org->addChild('orgName', $this->_organization->orgName);
            $org->addChild('orgTitle', $this->_organization->orgTitle);
        }
        
        
        
		// rebuild emails (one or more emails)
        if ( $this->_emails )
		{
			foreach ( $this->_emails as $email )
			{
				$gdemail = $xml->addChild('email', '', self::GD_NS);
				$gdemail->addAttribute(isset($email->rel) ? 'rel':'label', isset($email->rel) ? $email->rel : $email->label);
				$gdemail->addAttribute('primary', $email->primary ? 'true':'false');
				$gdemail->addAttribute('address', $email->address);
			}
		}
		
        
        // rebuild phones
		if ( $this->_phoneNumbers )
			foreach ( $this->_phoneNumbers as $phone )
            {
                $p = $xml->addChild('phoneNumber', $phone->phoneNumber, self::GD_NS);
				$p->addAttribute(isset($phone->rel) ? 'rel':'label', isset($phone->rel) ? $phone->rel : $phone->label);
				$p->addAttribute('primary', $phone->primary ? 'true':'false');
                
                if ( $phone->uri )
                    $p->addAttribute('uri', $phone->uri);
            }
		
        
        // rebuild ims
		if ( $this->_ims )
			foreach ( $this->_ims as $im )
            {
                $i = $xml->addChild('im', '', self::GD_NS);
                $i->addAttribute('address', $im->address);
                $i->addAttribute('protocol', $im->protocol);
				$i->addAttribute(isset($im->rel) ? 'rel':'label', isset($im->rel) ? $im->rel : $im->label);
				$i->addAttribute('primary', $im->primary ? 'true':'false');
            }

        
        // rebuild events
		if ( $this->_events )
			foreach ( $this->_events as $ev )
            {
                $e = $xml->addChild('event', '', self::GCONTACT_NS);
                $e->addChild('when', '', self::GD_NS)->addAttribute('startTime', $ev->when);
				$e->addAttribute(isset($ev->rel) ? 'rel':'label', isset($ev->rel) ? $ev->rel : $ev->label);
            }
		
        
        // rebuild relations
		if ( $this->_relations )
			foreach ( $this->_relations as $rel )
            {
                $r = $xml->addChild('relation', $rel->relation, self::GCONTACT_NS);
                $r->addAttribute(isset($rel->rel) ? 'rel':'label', isset($rel->rel) ? $rel->rel : $rel->label);
            }
		
        
        // rebuild websites
		if ( $this->_websites )
			foreach ( $this->_websites as $web )
            {
                $w = $xml->addChild('website', '', self::GCONTACT_NS);
                $w->addAttribute(isset($web->rel) ? 'rel':'label', isset($web->rel) ? $web->rel : $web->label);
                $w->addAttribute('href', $web->href);
				$w->addAttribute('primary', $web->primary ? 'true':'false');
            }
		
        
        // rebuild userDefinedFields
		if ( $this->_userDefinedFields )
			foreach ( $this->_userDefinedFields as $ufield )
            {
                $u = $xml->addChild('userDefinedField', '', self::GCONTACT_NS);
                $u->addAttribute('key', $ufield->key);
                $u->addAttribute('value', $ufield->value);
            }
		
        
        // rebuild extendedProperties
		if ( $this->_extendedProperties )
			foreach ( $this->_extendedProperties as $epfield )
            {
                $e = $xml->addChild('extendedProperty', '', self::GD_NS);
                $e->addAttribute('name', $epfield->name);
                $e->addAttribute('value', $epfield->value);
            }

        
        // rebuild addresses
		if ( $this->_structuredPostalAddresses )
		{
			foreach ( $this->_structuredPostalAddresses as $addr )
			{
				$gdaddr = $xml->addChild('structuredPostalAddress', '', self::GD_NS);
				$gdaddr->addAttribute($addr->rel ? 'rel':'label', $addr->rel ? $addr->rel : $addr->label);
				$gdaddr->addChild('city', $addr->city, self::GD_NS);
				$gdaddr->addChild('postcode', $addr->postcode, self::GD_NS);
				$gdaddr->addAttribute('primary', $addr->primary ? 'true':'false');
				
				if ( $addr->formattedAddress )
				    $gdaddr->addChild('formattedAddress', $addr->formattedAddress, self::GD_NS);
				if ( $addr->street )
					$gdaddr->addChild('street', $addr->street, self::GD_NS);
				if ( $addr->region )
					$gdaddr->addChild('region', $addr->region, self::GD_NS);
				if ( $addr->country )
					$gdaddr->addChild('country', $addr->country, self::GD_NS);
			}
		}



        
		// handle groups (first removing all groups data)
		$xml->registerXPathNamespace('gContact', self::GCONTACT_NS);
        $xpath_groups = $xml->xpath('//gContact:groupMembershipInfo');
		foreach ( $xpath_groups as $x )
			unset($x[0]);
        
        foreach ( $this->_groupsMembershipInfo as $group )
            $xml->addChild('groupMembershipInfo', '', self::GCONTACT_NS)->addAttribute('href', $group);
    }

    

    /**
     * Get a XML-formatted string of the Contact object
     * 
     * @return string Contact as a XML-formatted string
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if contact XML cannot be parsed
     */
    public function asXml()
    {
        // get XML data to update with contact properties
        if ( !$this->_xml )
            $xml = "<?xml version='1.0' encoding='UTF-8'?><entry xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005'></entry>";
        else
            $xml = $this->_xml;
        
        
        // check that we have the required namespace prefix definitions in root tag (this is not the case if the xml comes from a contacts list)
        if ( preg_match('/<entry([^>]*)>/', $xml, $regs) )
        {
            $norm = '';
            
            // in $regs[1] we have all attributes of xml root (ENTRY tag)
            if ( strpos($regs[1], 'xmlns=') === FALSE )
                $norm .= " xmlns='http://www.w3.org/2005/Atom'";
            if ( strpos($regs[1], 'xmlns:gContact=') === FALSE )
                $norm .= " xmlns:gContact='http://schemas.google.com/contact/2008'";
            if ( strpos($regs[1], 'xmlns:gd=') === FALSE )
                $norm .= " xmlns:gd='http://schemas.google.com/g/2005'";
            
            // adding required namespaces definitions
            $xml = preg_replace('/<entry([^>]*)>/', '<entry$1' . $norm . '>', $xml);
        }
        else
            throw new \Nettools\GoogleAPI\Exceptions\Exception("XML data for contact '$this->title' cannot be normalized");
        
        
        // get the SimpleXMLElement
        $xml = simplexml_load_string($xml);
        if ( $xml === FALSE )
            throw new \Nettools\GoogleAPI\Exceptions\Exception("XML data for contact '$this->title' cannot be parsed");
        
        
        // assign properties to xml object
        $this->toXml($xml);
        return $xml->asXML();
    }
    
    
	
    /**
     * Assign Contact properties from a XML entry to an empty Contact object
     *
     * @param \SimpleXMLElement $xml XML data
     */
    public function assignXmlEntry(\SimpleXMLElement $xml)
    {
        // call Element method
        parent::assignXmlEntry($xml);
        
        
		// read complex values with gd or gContact namespace
		$gd_nodes = $xml->children('gd', true);
		$gcontact_nodes = $xml->children('gContact', true);
		$this->_familyName = (string) $gd_nodes->name->familyName;
		$this->_givenName = (string) $gd_nodes->name->givenName;
		$this->_fullName = (string) $gd_nodes->name->fullName;
        $this->_nickName = (string) $gcontact_nodes->nickname;
        
        if ( $gcontact_nodes->birthday )
            $this->_birthday = (string) $gcontact_nodes->birthday->attributes()->when;
        
        
        if ( $gd_nodes->organization )
        {
            $org = $gd_nodes->organization;
            $this->_organization = (object)array('orgName'=>(string)$org->orgName, 'orgTitle'=>(string)$org->orgTitle, 'rel'=>(string)$org->attributes()->rel);
        }


		// read complex values which may have multiple values
		$emails = array();
		$phones = array();
		$addresses = array();
		$groups = array();
        $ims = array();
        $events = array();
        $relations = array();
        $websites = array();
        $userDefinedFields = array();
        $extendedProperties = array();
		

		if ( $gd_nodes->email )
			foreach ( $gd_nodes->email as $em )
			{
				$email = $em->attributes();
				$emails[] = (object)array('address' => (string)$email->address, 'primary'=>$email->primary ? true:false, is_null($email->rel) ? 'label':'rel' => is_null($email->rel) ? (string)$email->label : (string)$email->rel);
			}

		if ( $gd_nodes->phoneNumber )
			foreach ( $gd_nodes->phoneNumber as $ph )
			{
				$phone = $ph->attributes();
                $p = (object)array('phoneNumber' => (string)$ph, 'primary'=>$phone->primary ? true:false, is_null($phone->rel) ? 'label':'rel' => is_null($phone->rel) ? (string)$phone->label : (string)$phone->rel);
            
                if ( $uri = (string)$phone->uri )
                    $p->uri = $uri;
                
                $phones[] = $p;
			}

		if ( $gd_nodes->structuredPostalAddress )
			foreach ( $gd_nodes->structuredPostalAddress as $addr )
			{
				$attr = $addr->attributes();
				$addresses[] = (object)array(
										'formattedAddress' => (string) $addr->formattedAddress, 
										'street' => $addr->street ? (string) $addr->street : '',
                                        'region' => (string) $addr->region,
                                        'country' => (string) $addr->country,
										'postcode' => (string) $addr->postcode,
										'city' => (string) $addr->city,
                                        'primary' => $attr->primary ? true:false, 
										is_null($attr->rel) ? 'label':'rel' => is_null($attr->rel) ? (string) $attr->label : (string) $attr->rel
									);
			}
									
		if ( $gd_nodes->im )
			foreach ( $gd_nodes->im as $imnode )
			{
				$im = $imnode->attributes();
				$ims[] = (object)array('address' => (string)$im->address, 'primary'=>$im->primary ? true:false, 'protocol' => (string)$im->protocol, is_null($im->rel) ? 'label':'rel' => is_null($im->rel) ? (string)$im->label : (string)$im->rel);
			}


		if ( $gd_nodes->extendedProperty )
			foreach ( $gd_nodes->extendedProperty as $epfield )
			{
                $field = $epfield->attributes();
				$extendedProperties[] = (object)array('name' => (string)$field->name, 'value' => (string) $field->value);
			}


		if ( $gcontact_nodes->event )
			foreach ( $gcontact_nodes->event as $ev )
			{
				$event = $ev->children('gd', true);
				$events[] = (object)array('when' => (string)$event->attributes()->startTime, is_null($ev->attributes()->rel) ? 'label':'rel' => is_null($ev->attributes()->rel) ? (string)$ev->attributes()->label : (string)$ev->attributes()->rel);
			}


		if ( $gcontact_nodes->relation )
			foreach ( $gcontact_nodes->relation as $rel )
			{
				$relation = $rel->attributes();
				$relations[] = (object)array('relation' => (string)$rel, is_null($relation->rel) ? 'label':'rel' => is_null($relation->rel) ? (string)$relation->label : (string)$relation->rel);
			}


		if ( $gcontact_nodes->website )
			foreach ( $gcontact_nodes->website as $web )
			{
				$website = $web->attributes();
				$websites[] = (object)array('href' => (string)$website->href, 'primary'=>$website->primary ? true:false, is_null($website->rel) ? 'label':'rel' => is_null($website->rel) ? (string)$website->label : (string)$website->rel);
			}


		if ( $gcontact_nodes->userDefinedField )
			foreach ( $gcontact_nodes->userDefinedField as $ufield )
			{
                $field = $ufield->attributes();
				$userDefinedFields[] = (object)array('key' => (string)$field->key, 'value' => (string) $field->value);
			}


		if ( $gcontact_nodes->groupMembershipInfo )
			foreach ( $gcontact_nodes->groupMembershipInfo as $group )
				$groups[] = (string) $group->attributes()->href;
				        
        
		$this->_emails = new RelLabelArrayProperty($emails);
		$this->_ims = new RelLabelArrayProperty($ims);
        $this->_events = new RelLabelArrayProperty($events);
        $this->_relations = new RelLabelArrayProperty($relations);
        $this->_websites = new RelLabelArrayProperty($websites);
        $this->_phoneNumbers = new RelLabelArrayProperty($phones);
        $this->_structuredPostalAddresses = new RelLabelArrayProperty($addresses);
        $this->_userDefinedFields = new ArrayProperty($userDefinedFields);
        $this->_extendedProperties = new ArrayProperty($extendedProperties);
        $this->_groupsMembershipInfo = new ArrayProperty($groups);
    }
    
    
    /**
     * Create a new contact from a XML entry
     *
     * @param \SimpleXMLElement $xml Contact XML data
     * @return Contact Returns a new Contact object
     */
    static public function fromFeed(\SimpleXMLElement $xml)
    {
        // create empty contact
        $c = new Contact();
        
        // assign properties
        $c->assignXmlEntry($xml);
        
        return $c;
    }
}

?>