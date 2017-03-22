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
    /**
     * Constructor of collection
     *
     * @param \SimpleXMLElement $xml Xml tree to parse as a collection
     * @param string $classname Class name of objects from feed
     * @param string $collectionProperty Property name of $xml to treat as a collection
     */ 
	public function __construct(\SimpleXMLElement $xml, $classname, $collectionProperty = 'entry')
    {
        $feed = [];
        
        // enumerate entries in an array so that it could be iterable
        foreach ( $xml->{$collectionProperty} as $entry )
            $feed[] = $entry;

        
        parent::__construct($feed, $classname);
    }
}

?>