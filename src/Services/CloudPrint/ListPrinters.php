<?php
/**
 * ListPrinters
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;


use \Nettools\GoogleAPI\Services\Misc\ArrayCollection;
use \Nettools\GoogleAPI\Services\Misc\SerializedObjectsCollection;




/**
 * Printers list response
 */
class ListPrinters extends \Nettools\GoogleAPI\Services\Misc\SerializedObjectsCollection
{
    /**
     * Constructor of collection
     *
     * @param \Stdclass[] $printers Array of printer objects
     */ 
	public function __construct(array $printers)
    {
        parent::__construct(new ArrayCollection($printers), \Nettools\GoogleAPI\Services\CloudPrint\Printer::class);
    }
}

?>