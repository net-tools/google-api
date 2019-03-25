<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Group;
use \Nettools\GoogleAPI\Services\Contacts\BatchEntryResponse;
use \PHPUnit\Framework\TestCase;





class BatchEntryResponseTest extends TestCase
{
	public function testBatchEntryResponse()
	{
		$g = new Group();
		$g->title = 'test';
		$r = new BatchEntryResponse('200', 'batch item success', 'insert', $g);
		
		$this->assertEquals(200, $r->httpCode);
		$this->assertEquals('batch item success', $r->reason);
		$this->assertEquals('insert', $r->operationType);
		$this->assertEquals($g, $r->entry);

	
		$r = new BatchEntryResponse('200', 'batch item success', 'delete', NULL);
		
		$this->assertEquals(200, $r->httpCode);
		$this->assertEquals('batch item success', $r->reason);
		$this->assertEquals('delete', $r->operationType);
		$this->assertEquals(NULL, $r->entry);
	
	}
}


?>