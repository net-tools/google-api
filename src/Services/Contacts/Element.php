<?php
/**
 * Element
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Element object (ancestor class for Contact and Group classes)
 */
abstract class Element extends \Nettools\GoogleAPI\Services\Misc\MutableObject
{
    /** @var string Xml data of contact from Google API, if available */
    protected $_xml;
    
    
    /** @var string */
    protected $_title;

    /** @var string */
    protected $_id;

    /** @var int */
    protected $_updated;

    /** @var bool */
    protected $_deleted;

    /** @var string */
    protected $_content;

    /** @var string */
    protected $_etag;

    /**
     * Array of objects with type, href and rel properties
     * 
     * @var \Stdclass[] 
     */
    protected $_links = array();
    
    
    
    /**
     * Get a list of read-only properties, so that __set magic accessor could throw an exception if a property is read-only
     *
     * @return string[] Array of read-only property names
     */
    protected function _getReadonlyProperties()
    {
       return array_merge(['xml', 'updated', 'etag', 'id', 'deleted'], parent::_getReadonlyProperties());
    }
    
    
    /**
     * Get the link whose REL attribute matches the method $rel parameter
     *
     * @param string $rel Rel attribute (self, edit, etc.)
     * @return bool|\Stdclass Link object with its REL value equal to $rel or FALSE if link with the $rel REL attribute not found
     */
    public function linkRel($rel)
    {
        foreach ( $this->_links as $l )
            if ( $l->rel == $rel )
                return $l;
        
        return FALSE;
    }
    

    
    /**
     * Update a SimpleXMLElement object with the Element properties
     *
     * Only read/write properties are updated in the argument. Read-only properties are not set to $xml argument, since they are set during a `assignXmlEntry` call and are not updatable by user.
     * 
     * @param \SimpleXMLElement $xml XML object to update with Element properties
     */
    public function toXml(\SimpleXMLElement $xml)
    {
        // simple values for updatable properties only (we ignore links, id, etc.)
		$xml->content = $this->_content ? $this->_content : '';
		$xml->title = $this->_title ? $this->_title : ''; 
    }

    
    /**
     * Get a XML-formatted string of an Element object
     * 
     * @return string Object as a XML-formatted string
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if contact XML cannot be parsed
     */
    abstract public function asXml();
    
    
    
    /**
     * Assign Element properties from a XML entry
     *
     * @param \SimpleXMLElement $xml XML data
     */
    public function assignXmlEntry(\SimpleXMLElement $xml)
    {
        // set the XML data so that the contact could be updated later by an update request (by modifying the existing XML, we make sure we don't miss some undocumented properties)
        $this->_xml = $xml->asXML();
        
        // define simple values
        $this->_title = (string)$xml->title;
        $this->_id = (string)$xml->id;
        $this->_updated = strtotime((string)$xml->updated);
        $this->_content = (string) $xml->content;
        $this->_etag = (string) $xml->attributes('gd', true)->etag;
        $this->_deleted = $xml->children('gd', true)->deleted ? true : false;


		// read complex values which may have multiple values
		$links = array();
		
		if ( $xml->link )
			foreach ( $xml->link as $link )
			{
				$linkattributes = $link->attributes(); 
				$linko = (object)array('type' => (string)$linkattributes->type, 'href' => (string)$linkattributes->href, 'rel' => (string)$linkattributes->rel);
                
                // some links may have a gd:etag attribute (photo link)
                if ( $etag = (string)$link->attributes('gd', true)->etag )
                    $linko->etag = $etag;
                
                $links[] = $linko;
			}
        $this->_links = $links;
    }
}

?>