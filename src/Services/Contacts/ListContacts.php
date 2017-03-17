<?php
/**
 * ListContacts
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Contacts;



/**
 * Contacts list response
 */
class ListContacts extends \Nettools\GoogleAPI\Services\Misc\XmlFeed
{
    /**
     * Constructor of collection
     *
     * @param \SimpleXMLElement $xml Xml tree to parse as a collection
     */ 
	public function __construct(\SimpleXMLElement $xml)
    {
        parent::__construct($xml, '\\Nettools\\GoogleAPI\\Services\\Contacts\\Contact', 'entry');
    }
}

?>