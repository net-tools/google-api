<?php
/**
 * ListPrinters
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;



/**
 * Printers list response
 */
class ListPrinters extends \Nettools\GoogleAPI\Services\Misc\ArrayCollection
{
    /**
     * Constructor of collection
     *
     * @param \Stdclass[] $printers Array of printer objects
     */ 
	public function __construct(array $printers)
    {
        parent::__construct($printers, \Nettools\GoogleAPI\Services\CloudPrint\Printer::class);
    }
}

?>