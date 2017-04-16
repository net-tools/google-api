<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\CloudPrint_Service;




class CloudPrint_ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty1()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new CloudPrint_Service($stub_client);
				
        $service->printers = null;
    }
	
	
	
    /**
     * @expectedException \Nettools\GoogleAPI\Exceptions\Exception
     */
    public function testReadOnlyProperty2()
    {
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new CloudPrint_Service($stub_client);
				
        $service->jobs = null;
    }


	
	
	public function testSendRequest()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('{"success":true, "printer":{"id":"123", "title":"prn1"}}');

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30,
									'headers' => ['X-header-test'=>1234]
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->sendRequest('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\Stdclass::class, $resp);
		$this->assertEquals('123', (string)$resp->printer->id);
	}       
	
	
	
	public function testSendRequestJsonException()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('{"success":false, "errorCode":991, "message":"Error occured"}');

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
        
        try
        {
            $resp = $service->sendRequest('get', 'my.url.com');
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
        }
        catch(\Exception $e)
        {
            $this->assertInstanceOf(\Google_Service_Exception::class, $e);
            $json = json_decode($e->getMessage());
            
            $this->assertNotNull($json);
            $this->assertEquals(991, $json->error->code);
            $this->assertEquals("Error occured", $json->error->message);
        }
	}       
	
	
	
	public function testSendRequestHtmlException()
	{
		// creating stub for guzzle response ; response is KO (HTTP code 403)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(403);
		$stub_guzzle_response->method('getBody')->willReturn(<<<HEREDOC
<HTML>
<HEAD>
<TITLE>User credentials required</TITLE>
</HEAD>
<BODY BGCOLOR="#FFFFFF" TEXT="#000000">
<H1>User credentials required</H1>
<H2>Error 403</H2>
</BODY>
</HTML>
HEREDOC
            );

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
        
        try
        {
            $resp = $service->sendRequest('get', 'my.url.com');
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
        }
        catch(\Exception $e)
        {
            $this->assertInstanceOf(\Google_Service_Exception::class, $e);
            $json = json_decode($e->getMessage());
            
            $this->assertNotNull($json);
            $this->assertEquals(403, $json->error->code);
            $this->assertEquals("User credentials required", $json->error->message);
        }
	}       
	


	public function testPrintersSearch()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "request": {
  "time": "0",
  "params": {
   "q": [
    "brother"
   ],
   "connection_status": [
    ""
   ],
   "use_cdd": [
    "true"
   ],
   "extra_fields": [
    "connectionStatus"
   ],
   "type": [
    ""
   ]
  },
  "user": "me@gmail.com",
  "users": [
   "me@gmail.com"
  ]
 },
 "printers": [
  {
   "isTosAccepted": false,
   "displayName": "Brother DCP-L2520DW series",
   "supportUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc492\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "description": "",
   "type": "GOOGLE",
   "ownerId": "assistancemultimedia63@gmail.com",
   "uuid": "00000000-0000-0000-0000-000000000000",
   "manufacturer": "Brother",
   "gcpVersion": "2.0",
   "ownerName": "Me",
   "defaultDisplayName": "",
   "model": "Brother DCP-L2520DW series",
   "id": "00000000-0000-0000-0000-000000000000",
   "firmware": "L",
   "setupUrl": "http://www.brother.com/E-ftp/gcp/en/index.html",
   "certificationId": "00000000",
   "local_settings": {
    "current": {
     "xmpp_timeout_value": 300,
     "local_discovery": true,
     "access_token_enabled": true,
     "printer/local_printing_enabled": true
    }
   },
   "capsHash": "3977497403",
   "updateTime": "1491574079107",
   "tags": [
    "^recent",
    "^own",
    "^can_share",
    "^can_update",
    "^can_delete",
    "Brother DCP-L2520DW series"
   ],
   "proxy": "000000000000000_00-00-00-00-00-00",
   "createTime": "1479317802217",
   "updateUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc492\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "name": "Brother DCP-L2520DW series",
   "connectionStatus": "OFFLINE",
   "status": "",
   "accessTime": "1491211036144"
  },
  {
   "isTosAccepted": false,
   "displayName": "Brother MFC-J5620DW",
   "supportUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc517\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "description": "",
   "type": "GOOGLE",
   "ownerId": "me@gmail.com",
   "uuid": "00000000-0000-0000-0000-000000000000",
   "manufacturer": "Brother",
   "gcpVersion": "2.0",
   "ownerName": "Me",
   "defaultDisplayName": "",
   "model": "Brother MFC-J5620DW",
   "id": "00000000-0000-0000-0000-000000000000",
   "firmware": "N",
   "setupUrl": "http://www.brother.com/E-ftp/gcp/en/index.html",
   "local_settings": {
    "current": {
     "xmpp_timeout_value": 300,
     "local_discovery": true,
     "access_token_enabled": true,
     "printer/local_printing_enabled": true
    }
   },
   "capsHash": "4155790593",
   "updateTime": "1491320860187",
   "tags": [
    "^recent",
    "^own",
    "^can_share",
    "^can_update",
    "^can_delete",
    "Brother MFC-J5620DW"
   ],
   "proxy": "000000000000000_00-00-00-00-00-00",
   "createTime": "1486986199206",
   "updateUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc517\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "name": "Brother MFC-J5620DW",
   "connectionStatus": "OFFLINE",
   "status": "idle",
   "accessTime": "1491211874632"
  }
 ],
 "xsrf_token": "AIp00DiTUMeS8ezA1lmv_tdtzwllkz5d7w:1491642418190"
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/search'), 
						$this->equalTo(
								array(
									'form_params'=> ['q'=>'brother'],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->printers->search(['q'=>'brother']);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\ListPrinters::class, $resp);
		
		$prn = $resp->getIterator()->current();
		$this->assertEquals('Brother DCP-L2520DW series', $prn->displayName);
	}
	


	public function testPrintersGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "request": {
  "time": "0",
  "params": {
   "input_type": [
    ""
   ],
   "use_cdd": [
    "true"
   ],
   "printerid": [
    "00000000-0000-0000-0000-000000000000"
   ],
   "extra_fields": [
    ""
   ]
  },
  "user": "me@gmail.com",
  "users": [
   "me@gmail.com"
  ]
 },
 "printers": [
  {
   "isTosAccepted": false,
   "access": [
    {
     "role": "OWNER",
     "scope": "me@gmail.com",
     "name": "Me",
     "membership": "MANAGER",
     "type": "USER",
     "email": "me@gmail.com"
    }
   ],
   "displayName": "Brother DCP-L2520DW series",
   "supportUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc492\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "description": "",
   "type": "GOOGLE",
   "ownerId": "me@gmail.com",
   "uuid": "00000000-0000-0000-0000-000000000000",
   "manufacturer": "Brother",
   "gcpVersion": "2.0",
   "ownerName": "Me",
   "defaultDisplayName": "",
   "model": "Brother DCP-L2520DW series",
   "id": "00000000-0000-0000-0000-000000000000",
   "firmware": "L",
   "setupUrl": "http://www.brother.com/E-ftp/gcp/en/index.html",
   "capabilities": {
    "printer": {
     "cover": [
      {
       "vendor_id": "cover",
       "type": "COVER"
      }
     ],
     "pwg_raster_config": {
      "document_type_supported": [
       "SGRAY_8"
      ],
      "document_resolution_supported": [
       {
        "cross_feed_dir": 300,
        "feed_dir": 300
       }
      ]
     },
     "copies": {
      "default": 1,
      "max": 999
     },
     "media_size": {
      "option": [
       {
        "height_microns": 297000,
        "is_continuous_feed": false,
        "name": "ISO_A4",
        "width_microns": 210000,
        "custom_display_name": "A4",
        "is_default": true
       },
       {
        "height_microns": 279400,
        "is_continuous_feed": false,
        "name": "NA_LETTER",
        "width_microns": 215900,
        "custom_display_name": "Letter",
        "is_default": false
       },
       {
        "height_microns": 355600,
        "is_continuous_feed": false,
        "name": "NA_LEGAL",
        "width_microns": 215900,
        "custom_display_name": "Legal",
        "is_default": false
       },
       {
        "height_microns": 266700,
        "is_continuous_feed": false,
        "name": "NA_EXECUTIVE",
        "width_microns": 184150,
        "custom_display_name": "Executive",
        "is_default": false
       },
       {
        "height_microns": 210000,
        "is_continuous_feed": false,
        "name": "ISO_A5",
        "width_microns": 148000,
        "custom_display_name": "A5",
        "is_default": false
       },
       {
        "height_microns": 235000,
        "is_continuous_feed": false,
        "name": "ISO_A5_EXTRA",
        "width_microns": 174000,
        "custom_display_name": "A5 L",
        "is_default": false
       },
       {
        "height_microns": 148000,
        "is_continuous_feed": false,
        "name": "ISO_A6",
        "width_microns": 105000,
        "custom_display_name": "A6",
        "is_default": false
       },
       {
        "height_microns": 250000,
        "is_continuous_feed": false,
        "name": "ISO_B5",
        "width_microns": 176000,
        "custom_display_name": "B5",
        "is_default": false
       },
       {
        "height_microns": 241300,
        "is_continuous_feed": false,
        "name": "NA_NUMBER_10",
        "width_microns": 104775,
        "custom_display_name": "Com-10",
        "is_default": false
       },
       {
        "height_microns": 220000,
        "is_continuous_feed": false,
        "name": "PRC_5",
        "width_microns": 110000,
        "custom_display_name": "DL",
        "is_default": false
       },
       {
        "height_microns": 229000,
        "is_continuous_feed": false,
        "name": "ISO_C5",
        "width_microns": 162000,
        "custom_display_name": "C5",
        "is_default": false
       },
       {
        "height_microns": 177800,
        "is_continuous_feed": false,
        "name": "NA_MONARCH",
        "width_microns": 98425,
        "custom_display_name": "Monarch",
        "is_default": false
       },
       {
        "height_microns": 127000,
        "is_continuous_feed": false,
        "name": "NA_INDEX_3X5",
        "width_microns": 76200,
        "custom_display_name": "3X5",
        "is_default": false
       },
       {
        "height_microns": 330200,
        "is_continuous_feed": false,
        "name": "NA_FOOLSCAP",
        "width_microns": 215900,
        "custom_display_name": "Folio",
        "is_default": false
       }
      ]
     },
     "marker": [
      {
       "color": {
        "type": "BLACK"
       },
       "vendor_id": "Black",
       "type": "TONER"
      }
     ],
     "vendor_capability": [
      {
       "id": "PageMediaType",
       "display_name": "Media Type",
       "type": "SELECT",
       "select_cap": {
        "option": [
         {
          "display_name": "Normal",
          "is_default": true,
          "value": "Plain"
         },
         {
          "display_name": "Fin",
          "is_default": false,
          "value": "Thin"
         },
         {
          "display_name": "\u00c9pais",
          "is_default": false,
          "value": "Thick"
         },
         {
          "display_name": "Lourd",
          "is_default": false,
          "value": "Thicker"
         },
         {
          "display_name": "Enveloppes",
          "is_default": false,
          "value": "EnvelopePlain"
         },
         {
          "display_name": "Env. \u00e9paisses",
          "is_default": false,
          "value": "EnvelopeThick"
         },
         {
          "display_name": "Env. fines",
          "is_default": false,
          "value": "EnvelopeThin"
         },
         {
          "display_name": "Papier recycl\u00e9",
          "is_default": false,
          "value": "RecycledPaper"
         },
         {
          "display_name": "Etiquette",
          "is_default": false,
          "value": "Label"
         },
         {
          "display_name": "Papier fort",
          "is_default": false,
          "value": "Bond"
         }
        ]
       }
      },
      {
       "id": "PageResolution",
       "display_name": "PageResolution",
       "type": "SELECT",
       "select_cap": {
        "option": [
         {
          "display_name": "600",
          "is_default": true,
          "value": "600dpi"
         },
         {
          "display_name": "HQ1200",
          "is_default": false,
          "value": "HQ1200"
         },
         {
          "display_name": "300",
          "is_default": false,
          "value": "300dpi"
         }
        ]
       }
      },
      {
       "id": "JobInputBin",
       "display_name": "Paper Source",
       "type": "SELECT",
       "select_cap": {
        "option": [
         {
          "display_name": "Auto",
          "is_default": true,
          "value": "AutoSelect"
         },
         {
          "display_name": "Intro. manuelle",
          "is_default": false,
          "value": "Manual"
         },
         {
          "display_name": "Bac 1",
          "is_default": false,
          "value": "TrayMain"
         }
        ]
       }
      }
     ],
     "input_tray_unit": [
      {
       "vendor_id": "tray",
       "type": "INPUT_TRAY"
      }
     ],
     "duplex": {
      "option": [
       {
        "type": "NO_DUPLEX",
        "is_default": true
       },
       {
        "type": "LONG_EDGE",
        "is_default": false
       },
       {
        "type": "SHORT_EDGE",
        "is_default": false
       }
      ]
     },
     "supported_content_type": [
      {
       "content_type": "image/pwg-raster"
      }
     ],
     "output_bin_unit": [
      {
       "vendor_id": "bin",
       "type": "OUTPUT_BIN"
      }
     ],
     "dpi": {
      "option": [
       {
        "horizontal_dpi": 300,
        "vertical_dpi": 300,
        "is_default": true
       }
      ]
     },
     "media_path": [
      {
       "vendor_id": "media_path"
      }
     ]
    },
    "version": "1.0"
   },
   "certificationId": "19989767",
   "local_settings": {
    "current": {
     "xmpp_timeout_value": 300,
     "local_discovery": true,
     "access_token_enabled": true,
     "printer/local_printing_enabled": true
    }
   },
   "capsHash": "3977497403",
   "updateTime": "1491574079107",
   "tags": [
    "^recent",
    "^own",
    "^can_share",
    "^can_update",
    "^can_delete",
    "Brother DCP-L2520DW series"
   ],
   "proxy": "000000000000000_00-00-00-00-00-00",
   "createTime": "1479317802217",
   "updateUrl": "http://solutions.brother.com/cgi-bin/solutions.cgi?MDL\u003dmfc492\u0026LNG\u003dfr\u0026SRC\u003dDEVICE",
   "name": "Brother DCP-L2520DW series",
   "status": "",
   "accessTime": "1491211036144"
  }
 ],
 "xsrf_token": "AIp00DgnjE2GFx-sBdybM0zhDJ68AFAjQw:1491643097528"
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/printer'), 
						$this->equalTo(
								array(
									'form_params'=> ['printerid'=>'00000000-0000-0000-0000-000000000000'],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->printers->get('00000000-0000-0000-0000-000000000000');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\Printer::class, $resp);
		
		$this->assertEquals('00000000-0000-0000-0000-000000000000', $resp->id);
		$this->assertEquals('Brother DCP-L2520DW series', $resp->displayName);
	}
	


	public function testJobsSearch()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "request": {
  "time": "0",
  "params": {
   "owner": [
    ""
   ],
   "q": [
    ""
   ],
   "offset": [
    ""
   ],
   "limit": [
    ""
   ],
   "printerid": [
    ""
   ],
   "sortorder": [
    ""
   ],
   "status": [
    ""
   ]
  },
  "user": "me@gmail.com",
  "users": [
   "me@gmail.com"
  ]
 },
 "xsrf_token": "AIp00DhgNg_pnfSPFLKADJ7NtrBUyPPpaA:1491643428966",
 "jobs": [
  {
   "ticketUrl": "https://www.google.com/cloudprint/ticket?jobid\u003d09090090-0000-0000-0000-000000000000",
   "printerType": "GOOGLE",
   "printerName": "Brother DCP-L2520DW series",
   "errorCode": "",
   "updateTime": "1491574079341",
   "title": "Test job",
   "message": "",
   "ownerId": "me@gmail.com",
   "tags": [
    "^own"
   ],
   "uiState": {
    "summary": "DONE",
    "progress": "Pages imprim\u00e9es\u00a0: 1"
   },
   "numberOfPages": 1,
   "createTime": "1491574044324",
   "semanticState": {
    "delivery_attempts": 1,
    "pages_printed": 1,
    "state": {
     "type": "DONE"
    },
    "version": "1.0"
   },
   "printerid": "00000000-0000-0000-0000-000000000000",
   "fileUrl": "https://www.google.com/cloudprint/download?id\u003d09090090-0000-0000-0000-000000000000",
   "id": "99999999-0000-0000-0000-000000000000",
   "rasterUrl": "https://www.google.com/cloudprint/download?id\u003d09090090-0000-0000-0000-000000000000\u0026forcepwg\u003d1",
   "contentType": "application/pdf",
   "status": "DONE"
  }
 ],
 "range": {
  "jobsTotal": "1",
  "jobsCount": 1
 }
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/jobs'), 
						$this->equalTo(
								array(
									'form_params' => [],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->jobs->search();
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\ListJobs::class, $resp);
		
		$job = $resp->getIterator()->current();
		$this->assertEquals('99999999-0000-0000-0000-000000000000', $job->id);
	}
	


	public function testJobsGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "request": {
  "time": "0",
  "params": {
   "jobid": [
    "99999999-0000-0000-0000-000000000000"
   ]
  },
  "user": "me@gmail.com",
  "users": [
   "me@gmail.com"
  ]
 },
 "xsrf_token": "AIp00DgTKH2lyrVTiRtD4MK2mmoL1Gke_g:1491643786448",
 "job": {
  "ticketUrl": "https://www.google.com/cloudprint/ticket?jobid\u003d09090090-0000-0000-0000-000000000000",
  "printerType": "GOOGLE",
  "printerName": "Brother DCP-L2520DW series",
  "errorCode": "",
  "updateTime": "1491574079341",
  "title": "Test job",
  "message": "",
  "ownerId": "me@gmail.com",
  "tags": [
   "^own"
  ],
  "uiState": {
   "summary": "DONE",
   "progress": "Pages imprim\u00e9es\u00a0: 1"
  },
  "numberOfPages": 1,
  "createTime": "1491574044324",
  "semanticState": {
   "delivery_attempts": 1,
   "pages_printed": 1,
   "state": {
    "type": "DONE"
   },
   "version": "1.0"
  },
  "printerid": "00000000-0000-0000-0000-000000000000",
  "fileUrl": "https://www.google.com/cloudprint/download?id\u003d09090090-0000-0000-0000-000000000000",
  "id": "99999999-0000-0000-0000-000000000000",
  "rasterUrl": "https://www.google.com/cloudprint/download?id\u003d09090090-0000-0000-0000-000000000000\u0026forcepwg\u003d1",
  "contentType": "application/pdf",
  "status": "DONE"
 }
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/job'), 
						$this->equalTo(
								array(
									'form_params' => ['jobid'=>'99999999-0000-0000-0000-000000000000'],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->jobs->get('99999999-0000-0000-0000-000000000000');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\Job::class, $resp);
		
		$this->assertEquals('99999999-0000-0000-0000-000000000000', $resp->id);
	}
	


	public function testJobsDelete()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "request": {
  "time": "0",
  "params": {
   "jobid": [
    "99999999-0000-0000-0000-000000000000"
   ]
  },
  "user": "me@gmail.com",
  "users": [
   "me@gmail.com"
  ]
 },
 "xsrf_token": "AIp00DgTKH2lyrVTiRtD4MK2mmoL1Gke_g:1491643786448"
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/deletejob'), 
						$this->equalTo(
								array(
									'form_params' => ['jobid'=>'99999999-0000-0000-0000-000000000000'],
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->jobs->delete('99999999-0000-0000-0000-000000000000');
		$this->assertEquals(true, $resp);
	}
	


	public function testJobsSubmit()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "xsrf_token": "AIp00DhgAGn-qZQ9jxdR1i2u7Ifw0WYqag:1491644643807",
 "message": "T\u00e2che d'impression ajout\u00e9e",
 "job": {
  "ticketUrl": "https://www.google.com/cloudprint/ticket?jobid\u003dec534727-0000-0000-0000-000000000000",
  "printerName": "",
  "errorCode": "",
  "updateTime": "1491644645972",
  "title": "test",
  "message": "",
  "ownerId": "me@gmail.com",
  "tags": [
   "^own"
  ],
  "uiState": {
   "summary": "QUEUED",
   "progress": "Tentatives de livraisons\u00a0: 1"
  },
  "numberOfPages": 16,
  "createTime": "1491644643976",
  "semanticState": {
   "delivery_attempts": 1,
   "state": {
    "type": "QUEUED"
   },
   "version": "1.0"
  },
  "printerid": "99999999-0000-0000-0000-000000000000",
  "fileUrl": "https://www.google.com/cloudprint/download?id\u003dec534727-0000-0000-0000-000000000000",
  "id": "11111111-0000-0000-0000-000000000000",
  "rasterUrl": "https://www.google.com/cloudprint/download?id\u003dec534727-0000-0000-0000-000000000000\u0026forcepwg\u003d1",
  "contentType": "application/pdf",
  "status": "QUEUED"
 }
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/submit'), 
						$this->equalTo(
								array(
									'multipart' => array(
											['name'=>'printerid', 'contents'=>'00000000-0000-0000-0000-000000000000'],
											['name'=>'title', 'contents'=>'Test job'],
											['name'=>'ticket', 'contents'=>'{"version": "1.0", "print": {}}'],
											['name'=>'contentType', 'contents'=>''],
											['name'=>'content', 'contents'=>'%PDF', 'filename'=>'upload', 'headers'=>['Content-Type'=>'application/pdf']]
										),
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->jobs->submit('00000000-0000-0000-0000-000000000000', 'Test job', '%PDF', 'application/pdf');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\Job::class, $resp);
		
		$this->assertEquals('11111111-0000-0000-0000-000000000000', $resp->id);
	}
		


	public function testJobsSubmitUrl()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<JSON
{
 "success": true,
 "xsrf_token": "AIp00DhgAGn-qZQ9jxdR1i2u7Ifw0WYqag:1491644643807",
 "message": "T\u00e2che d'impression ajout\u00e9e",
 "job": {
  "ticketUrl": "https://www.google.com/cloudprint/ticket?jobid\u003dec534727-0000-0000-0000-000000000000",
  "printerName": "",
  "errorCode": "",
  "updateTime": "1491644645972",
  "title": "test",
  "message": "",
  "ownerId": "me@gmail.com",
  "tags": [
   "^own"
  ],
  "uiState": {
   "summary": "QUEUED",
   "progress": "Tentatives de livraisons\u00a0: 1"
  },
  "numberOfPages": 16,
  "createTime": "1491644643976",
  "semanticState": {
   "delivery_attempts": 1,
   "state": {
    "type": "QUEUED"
   },
   "version": "1.0"
  },
  "printerid": "99999999-0000-0000-0000-000000000000",
  "fileUrl": "https://www.google.com/cloudprint/download?id\u003dec534727-0000-0000-0000-000000000000",
  "id": "11111111-0000-0000-0000-000000000000",
  "rasterUrl": "https://www.google.com/cloudprint/download?id\u003dec534727-0000-0000-0000-000000000000\u0026forcepwg\u003d1",
  "contentType": "application/pdf",
  "status": "QUEUED"
 }
}
JSON
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$stub_guzzle->method('request')->willReturn($stub_guzzle_response);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/cloudprint/submit'), 
						$this->equalTo(
								array(
									'form_params' => array(
											'printerid' => '00000000-0000-0000-0000-000000000000',
											'title' => 'Test job',
											'ticket' => '{"version": "1.0", "print": {}}',
											'contentType' => 'url',
											'content' => 'http://www.host.com/doc.pdf'
										),
									'connect_timeout' => 5.0,
									'timeout' => 30
								)
							)
					);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new CloudPrint_Service($stub_client);
		$resp = $service->jobs->submitUrl('00000000-0000-0000-0000-000000000000', 'Test job', 'http://www.host.com/doc.pdf');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\CloudPrint\Job::class, $resp);
		
		$this->assertEquals('11111111-0000-0000-0000-000000000000', $resp->id);
	}
	


	
	
	
	
}

?>