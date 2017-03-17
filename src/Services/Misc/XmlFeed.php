<?php
/**
 * XmlFeed
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class for collection of items from an XML feed
 */
class XmlFeed extends ArrayCollection 
{
    /** @var string Classname of object contained in feed ; those object will be created on the fly during iterations */
    protected $_feedOfClass = NULL;
    
    
    /**
     * Constructor of collection
     *
     * @param \SimpleXMLElement $xml Xml tree to parse as a collection
     * @param string $classname Class name of objects from feed
     * @param string $collectionProperty Property name of $xml to treat as a collection
     */ 
	public function __construct(\SimpleXMLElement $xml, $classname, $collectionProperty = 'entry')
    {
        $this->_feed = array();
        $this->_feedOfClass = $classname;
        
        // enumerate entries in an array so that it could be iterable
        foreach ( $xml->{$collectionProperty} as $entry )
            $this->_feed[] = $entry;
    }

    
    /**
     * Get current item of iterator
     *
     * @return mixed Returns an object of class $this->$_feedOfClass
     */
    public function current()
    {
        $class = $this->_feedOfClass;
        return $class::fromXmlEntry(parent::current());
    }
}

?>