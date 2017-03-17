<?php
/**
 * Group
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Group object
 */
class Group extends Element
{
    protected $_systemGroup;
    
    
    
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception if a property is read-only
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array_merge(['systemGroup'], parent::_getReadonlyProperties());
    }

    
    
    /**
     * Create a new Group from a XML entry
     *
     * @param \SimpleXMLElement $xml Group XML data
     * @return Contact Returns a new Group object
     */
    static public function fromXmlEntry(\SimpleXMLElement $xml)
    {
        // create empty group
        $g = new Group();
        
        // assign properties
        $g->assignXmlEntry($xml);
        
        return $g;
    }

    
    
    /**
     * Assign Group properties from a XML entry
     *
     * @param \SimpleXMLElement $xml XML data
     */
    public function assignXmlEntry(\SimpleXMLElement $xml)
    {
        // call Element method
        parent::assignXmlEntry($xml);
        
        
		// read complex values with gd or gContact namespace
		$gcontact_nodes = $xml->children('gContact', true);

		if ( $gcontact_nodes->systemGroup )
            $this->_systemGroup = (string) $gcontact_nodes->systemGroup->attributes()->id;
    }
    

        
    /**
     * Get a XML-formatted string of the Group object
     * 
     * @return string Group as a XML-formatted string
     * @throws \Nettools\GoogleAPI\Exceptions\ServiceException Thrown if contact XML cannot be parsed
     */
    public function asXml()
    {
        // get XML data to update with contact properties
        if ( !$this->_xml )
            $xml = "<?xml version='1.0' encoding='UTF-8'?><entry xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005'></entry>";
        else
            $xml = $this->_xml;
        
        
        // check that we have the required namespace prefix definitions in root tag (this is not the case if the xml comes from a contacts/groups list)
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
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("XML data for group '$this->title' cannot be normalized");
        
        
        // get the SimpleXMLElement
        $xml = simplexml_load_string($xml);
        if ( $xml === FALSE )
            throw new \Nettools\GoogleAPI\Exceptions\ServiceException("XML data for group '$this->title' cannot be parsed");
        
        
        // assign properties to xml object
        $this->toXml($xml);
        return $xml->asXML();
    }
    
    
}

?>