<?php
/**
 * Contact
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



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
    
    
    protected $_familyName;
    protected $_givenName;
    protected $_fullName;
    protected $_emails = array();
    protected $_phoneNumbers = array();
    protected $_structuredPostalAddresses = array();
    protected $_groupsMembershipInfo = array();
    
    
    
    /**
     * Get the emails whose REL attribute matches the method $rel parameter
     *
     * @param string $rel Rel attribute (http://schemas.google.com/g/2005#work, http://schemas.google.com/g/2005#home, etc.)
     * @return bool|\Stdclass[] Array of email objects with their REL value equal to $rel or FALSE if no email with the $rel REL attribute
     */
    public function emailRel($rel)
    {
        $ret = array();
        foreach ( $this->_emails as $e )
            if ( $e->rel == $rel )
                $ret[] = $e;
        
        return count($ret) ? $ret : FALSE;
    }
    
    
    /**
     * Get the addresses whose REL attribute matches the method $rel parameter
     *
     * @param string $rel Rel attribute (http://schemas.google.com/g/2005#work, http://schemas.google.com/g/2005#home, etc.)
     * @return bool|\Stdclass[] Array of addresses objects with their REL value equal to $rel or FALSE if no address with the $rel REL attribute
     */
    public function addressesRel($rel)
    {
        $ret = array();
        foreach ( $this->_structuredPostalAddresses as $e )
            if ( $e->rel == $rel )
                $ret[] = $e;
        
        return count($ret) ? $ret : FALSE;
    }
    
    
    /**
     * Get the phone numbers whose REL attribute matches the method $rel parameter
     *
     * @param string $rel Rel attribute (http://schemas.google.com/g/2005#work, http://schemas.google.com/g/2005#home, etc.)
     * @return bool|\Stdclass[] Array of phone numbers objects with their REL value equal to $rel or FALSE if no phone number with the $rel REL attribute
     */
    public function phoneNumbersRel($rel)
    {
        $ret = array();
        foreach ( $this->_phoneNumbers as $e )
            if ( $e->rel == $rel )
                $ret[] = $e;
        
        return count($ret) ? $ret : FALSE;
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
		$xpath_phones = $xml->xpath('//gd:phoneNumber');
		foreach ( $xpath_phones as $x )
			unset($x[0]);

        $xpath_addresses = $xml->xpath('//gd:structuredPostalAddress');
		foreach ( $xpath_addresses as $x )
			unset($x[0]);

        $xpath_emails = $xml->xpath('//gd:email');
		foreach ( $xpath_emails as $x )
			unset($x[0]);

    
        // rebuild NAME entry
		$gdname = $xml->addChild('name', '', self::GD_NS);
		if ( $this->_familyName )
			$gdname->addChild('familyName', $this->_familyName, self::GD_NS);
		if ( $this->_givenName )
			$gdname->addChild('givenName', $this->_givenName, self::GD_NS);
		
        
        
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
				$xml->addChild('phoneNumber', $phone->phoneNumber, self::GD_NS)->addAttribute(isset($phone->rel) ? 'rel':'label', isset($phone->rel) ? $phone->rel : $phone->label);

        
        // rebuild addresses
		if ( $this->_structuredPostalAddresses )
		{
			foreach ( $this->_structuredPostalAddresses as $addr )
			{
				$gdaddr = $xml->addChild('structuredPostalAddress', '', self::GD_NS);
				$gdaddr->addAttribute($addr->rel ? 'rel':'label', $addr->rel ? $addr->rel : $addr->label);
				$gdaddr->addChild('city', $addr->city, self::GD_NS);
				$gdaddr->addChild('postcode', $addr->postcode, self::GD_NS);
				
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
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if contact XML cannot be parsed
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
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("XML data for contact '$this->title' cannot be normalized");
        
        
        // get the SimpleXMLElement
        $xml = simplexml_load_string($xml);
        if ( $xml === FALSE )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("XML data for contact '$this->title' cannot be parsed");
        
        
        // assign properties to xml object
        $this->toXml($xml);
        return $xml->asXML();
    }
    
    
    /**
     * Assign Contact properties from a XML entry
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


		// read complex values which may have multiple values
		$emails = array();
		$phones = array();
		$addresses = array();
		$groups = array();
		

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
				$phones[] = (object)array('phoneNumber' => (string)$ph, is_null($phone->rel) ? 'label':'rel' => is_null($phone->rel) ? (string)$phone->label : (string)$phone->rel);
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
										is_null($attr->rel) ? 'label':'rel' => is_null($attr->rel) ? (string) $attr->label : (string) $attr->rel
									);
			}
									

		if ( $gcontact_nodes->groupMembershipInfo )
			foreach ( $gcontact_nodes->groupMembershipInfo as $group )
				$groups[] = (string) $group->attributes()->href;
				        
        
		$this->_emails = $emails;
        $this->_phoneNumbers = $phones;
        $this->_structuredPostalAddresses = $addresses;
        $this->_groupsMembershipInfo = $groups;
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