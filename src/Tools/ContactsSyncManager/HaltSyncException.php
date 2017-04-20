<?php
/**
 * HaltSyncException
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Tools\ContactsSyncManager;


use \Nettools\GoogleAPI\Services\Contacts\Contact;




/**
 * Class for a sync exception which halts the sync process
 */
class HaltSyncException extends SyncException
{
}


?>