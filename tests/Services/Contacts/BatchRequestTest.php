<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\BatchRequest;
use \Nettools\GoogleAPI\Services\Contacts\BatchEntryRequest;
use \PHPUnit\Framework\TestCase;





class BatchRequestTest extends TestCase
{
	public function testBatchRequest()
	{
		$arr = [new BatchEntryRequest('id.1', 'insert', '<entry><id>insert_id</id></entry>', '"etag"'),
				new BatchEntryRequest('id.2', 'update', '<entry><id>update_id</id></entry>', NULL)];
		
		$req = new BatchRequest($arr);

		$xml1 = '<entry gd:etag=\'"etag"\'><batch:id>id.1</batch:id><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xml2 = '<entry><batch:id>id.2</batch:id><batch:operation type=\'update\'/><id>update_id</id></entry>';
		
		$feed = "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>";
		$feed .= "\n" . $xml1 . "\n" . $xml2 . "\n</feed>";
		
		// testing the batch feed is built as expected
		$this->assertEquals($feed, $req->getFeed());	
	}
}


?>