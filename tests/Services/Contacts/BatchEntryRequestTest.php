<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\BatchEntryRequest;
use \PHPUnit\Framework\TestCase;





class BatchEntryRequestTest extends TestCase
{
	public function testBatchEntryRequest()
	{
		$req1 = new BatchEntryRequest('id.1', 'insert', '<entry><id>insert_id</id></entry>', '"etag"');
		$req2 = new BatchEntryRequest('id.2', 'update', '<entry><id>update_id</id></entry>', NULL);

		$xml1 = '<entry gd:etag=\'"etag"\'><batch:id>id.1</batch:id><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xml2 = '<entry><batch:id>id.2</batch:id><batch:operation type=\'update\'/><id>update_id</id></entry>';
		
		
		// testing the batch entry is built as expected
		$this->assertEquals($xml1, $req1->asXml());	
		$this->assertEquals($xml2, $req2->asXml());	

		// testing magic method __toString
		$this->assertEquals($xml1, ''.$req1);	
	}
}


?>