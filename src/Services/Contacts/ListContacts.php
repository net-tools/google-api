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
 *
 * This class is iterable either through a call to `getIterator()` or directly through a `foreach` construction. However, the `Iterator` object being
 * built on-the-fly thanks to a yield statement, the iterator can't be rewind. So, if you want to search many times in the contacts list returned here,
 * you have to use a cache mechanism, or use `\Nettools\GoogleAPI\Services\Misc\CachedCollection`.
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
        parent::__construct($xml, \Nettools\GoogleAPI\Services\Contacts\Contact::class, 'entry');
    }
}

?>