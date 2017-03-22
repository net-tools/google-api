<?php
/**
 * Printer
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\CloudPrint;



/**
 * Printer object
 */
class Printer extends \Nettools\GoogleAPI\Services\Misc\Proxy
{
    /**
     * Create a printer from an entry
     *
     * @param \Stdclass $printer Object entry
     * @return Printer Returns a new Printer object
     */
    static public function fromFeed(\Stdclass $printer)
    {
        return new Printer($printer);
    }
}

?>