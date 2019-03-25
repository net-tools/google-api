<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Group;
use \Nettools\GoogleAPI\Services\Contacts\BatchResponse;
use \Nettools\GoogleAPI\Services\Contacts\BatchEntryResponse;
use \PHPUnit\Framework\TestCase;





class BatchResponseTest extends TestCase
{
	public function testBatchResponse()
	{
		$xml1 = '<entry><batch:id>id.1</batch:id><batch:status code=\'200\' reason=\'insert ok\'/><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xml2 = '<entry><batch:id>id.2</batch:id><batch:status code=\'201\' reason=\'update ok\'/><batch:operation type=\'update\'/><id>update_id</id></entry>';
		
		$feed = "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>";
		$feed .= $xml1 . "\n" . $xml2 . "\n</feed>";
		
		// create the batch response
		$req = new BatchResponse(simplexml_load_string($feed), Group::class);
		$entries = $req->getEntries();
		$this->assertEquals(true, is_array($entries));	
		
		$this->assertInstanceOf(BatchEntryResponse::class, $entries['id.1']);
		$this->assertInstanceOf(BatchEntryResponse::class, $entries['id.2']);
		
		$this->assertEquals(200, $entries['id.1']->httpCode);
		$this->assertEquals(201, $entries['id.2']->httpCode);
		$this->assertEquals('insert ok', $entries['id.1']->reason);
		$this->assertEquals('update ok', $entries['id.2']->reason);
		$this->assertEquals('insert', $entries['id.1']->operationType);
		$this->assertEquals('update', $entries['id.2']->operationType);
		$this->assertInstanceOf(Group::class, $entries['id.1']->entry);
		$this->assertInstanceOf(Group::class, $entries['id.2']->entry);
		$this->assertEquals('insert_id', $entries['id.1']->entry->id);
		$this->assertEquals('update_id', $entries['id.2']->entry->id);
	}
	
	
	public function testBatchResponseOnError()
	{
		$xml1 = '<entry><batch:id>id.1</batch:id><batch:status code=\'200\' reason=\'insert ok\'/><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xml2 = '<entry><batch:id>id.2</batch:id><batch:status code=\'400\' reason=\'update ko\'/><batch:operation type=\'update\'/></entry>';
		
		$feed = "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>";
		$feed .= $xml1 . "\n" . $xml2 . "\n</feed>";
		
		// create the batch response
		$req = new BatchResponse(simplexml_load_string($feed), Group::class);
		$entries = $req->getEntries();
		$this->assertEquals(true, is_array($entries));	
		
		$this->assertInstanceOf(BatchEntryResponse::class, $entries['id.1']);
		$this->assertInstanceOf(BatchEntryResponse::class, $entries['id.2']);
		
		$this->assertEquals(200, $entries['id.1']->httpCode);
		$this->assertEquals(400, $entries['id.2']->httpCode);
		$this->assertEquals('insert ok', $entries['id.1']->reason);
		$this->assertEquals('update ko', $entries['id.2']->reason);
		$this->assertEquals('insert', $entries['id.1']->operationType);
		$this->assertEquals('update', $entries['id.2']->operationType);
		$this->assertEquals('insert_id', $entries['id.1']->entry->id);
		$this->assertEquals(NULL, $entries['id.2']->entry);
	}
}


?>