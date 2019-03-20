<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Services\Contacts\Batch;
use \Nettools\GoogleAPI\Services\Contacts\BatchEntryResponse;
use \Nettools\GoogleAPI\Services\Contacts_Service;




class BatchTest extends \PHPUnit\Framework\TestCase
{
    protected $contact;
    protected $xml;
    
    
    public function setUp() :void
    {
      
    }
    
    
	
    public function testBatch()
    {
		$xml1 = '<entry gd:etag=\'"etag"\'><batch:id>id.1</batch:id><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xml2 = '<entry><batch:id>id.2</batch:id><batch:operation type=\'update\'/><id>update_id</id></entry>';
		$feed = "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>";
		$feed .= "\n" . $xml1 . "\n" . $xml2 . "\n</feed>";
		
		
		$xmlr1 = '<entry><batch:id>id.1</batch:id><batch:status code=\'200\' reason=\'insert ok\'/><batch:operation type=\'insert\'/><id>insert_id</id></entry>';
		$xmlr2 = '<entry><batch:id>id.2</batch:id><batch:status code=\'400\' reason=\'update ko\'/><batch:operation type=\'update\'/></entry>';
		
		$feedr = "<?xml version='1.0' encoding='UTF-8'?><feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch'>";
		$feedr .= "\n" . $xmlr1 . "\n" . $xmlr2 . "\n</feed>";
		$resp = simplexml_load_string($feedr);
		
		
		// create service stub
		$service_stub = $this->createMock(Contacts_Service::class);	
		$service_stub->expects($this->once())->method('sendRequest')->with(
						$this->equalTo('POST'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/user%40gmail.com/full/batch'), 
						$this->equalTo(
								array(
									'body' => $feed,
									'headers' => ['Content-Type'  => 'application/atom+xml']
								)
							)
					)
					->willReturn($resp);

		
		
		$batch = new Batch($service_stub, Batch::BATCH_CONTACTS, Contact::class, 'user@gmail.com');
		
        $this->assertEquals(true, $batch->isEmpty());
        
		$body1 = '<entry><id>insert_id</id></entry>';
		$body2 = '<entry><id>update_id</id></entry>';

        $this->assertEquals($batch, $batch->add('id.1', 'insert', $body1, '"etag"'));
        $this->assertEquals($batch, $batch->add('id.2', 'update', $body2, NULL));
        
		$resp = $batch->execute();
		$this->assertEquals(true, is_array($resp));
		$this->assertEquals(2, count($resp));
		
		$this->assertInstanceOf(BatchEntryResponse::class, $resp['id.1']);
		$this->assertInstanceOf(BatchEntryResponse::class, $resp['id.2']);
        
        $this->assertEquals(200, $resp['id.1']->httpCode);
        $this->assertEquals('insert ok', $resp['id.1']->reason);
        $this->assertEquals('insert', $resp['id.1']->operationType);
        $this->assertEquals('insert_id', $resp['id.1']->entry->id);
        $this->assertEquals(400, $resp['id.2']->httpCode);
        $this->assertEquals('update ko', $resp['id.2']->reason);
        $this->assertEquals('update', $resp['id.2']->operationType);
        $this->assertEquals(NULL, $resp['id.2']->entry);
    }    
    
    
}


?>