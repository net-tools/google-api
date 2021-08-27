<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\Service;
use \PHPUnit\Framework\TestCase;
use \Nettools\GoogleAPI\Tools\PeopleSync\Manager;







class ManagerTest extends TestCase
{
	protected $p1;
	protected $p2;
	
	
	public function setUp(): void
	{
		$this->p1 = new \Google\Service\PeopleService\Person([]);
		$this->p1->resourceName = 'https://www.google.com/editlink/ref1';
		$this->p1->setNames([new \Google\Service\PeopleService\Name()]);
		$this->p1->names[0]->familyName = 'doe';
		$this->p1->names[0]->givenName = 'john';		

		$this->p2 = new \Google\Service\PeopleService\Person([]);
		$this->p2->resourceName = 'https://www.google.com/editlink/ref2';
		$this->p2->setNames([new \Google\Service\PeopleService\Name()]);
		$this->p2->names[0]->familyName = 'smith';
		$this->p2->names[0]->givenName = 'henry';		
	}
	

	
   	public function testConstructor()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$cside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Clientside::class);
		
		$manager = new Manager($peopleservice, $gside, $cside, ['group'=>'gr', 'personFields'=>'names']);
		
		$this->assertEquals('gr', $manager->group);
		$this->assertEquals('names', $manager->personFields);
	}

	
	
       
    public function testSyncFromGoogle()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5updatetodo');
		
		$peopleservice
			->expects($this->exactly(2))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])],
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5client'));		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, false);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
	}
		
      

    public function testSyncFromGoogleUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5updatetodo');
		
		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5client'));		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			)
			->will($this->returnCallback(function($c) 
				{
					if ( 'https://www.google.com/editlink/ref1' == $c->resourceName )
						throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
				}
			));
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, false);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);	// an error occured
		$this->assertEquals(false, $log->checkNoError('Clientside update error : Error here'));
	}
		
      
  
       
    public function testSyncFromGoogleMd5Eq()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->will($this->onConsecutiveCalls('md5client', 'md5update'));
		
		$peopleservice
			->expects($this->exactly(2))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])],
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5client'));		
		$contacts
			->expects($this->exactly(1))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p2)]
			);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, false);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
	}
		
 
	

       
    public function testSyncFromGoogleConflict()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5-g');
		
		$peopleservice
			->expects($this->exactly(1))	// called once since there will be an error ; no new sync token asked if an error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(true, 'md5client'));		
		$contacts
			->expects($this->exactly(0))
			->method('update');
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, false);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $log->checkNoError('Conflict, updates on both sides'));
		$this->assertEquals(false, $ret);
	}
		
 
	

       
    public function testSyncFromGoogleConflictConfirmModeOn()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5-g');
		
		$peopleservice
			->expects($this->exactly(1))	// called once since confirm mode on
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(true, 'md5client'));		
		$contacts
			->expects($this->exactly(0))
			->method('update');
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_array($ret));
		$this->assertEquals(true, $log->checkNoError());
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[0]);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[1]);
		
		$this->assertEquals('conflict', $ret[0]->kind);
		$this->assertEquals($this->p1, $ret[0]->contact);
		$this->assertEquals('conflict', $ret[1]->kind);
		$this->assertEquals($this->p2, $ret[1]->contact);
	}
		
	

       
    public function testSyncFromGoogleUpdateConfirmModeOn()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5-g');
		
		$peopleservice
			->expects($this->exactly(1))	// called once since confirm mode on
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5client'));		
		$contacts
			->expects($this->exactly(0))
			->method('update');
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_FROM_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_array($ret));
		$this->assertEquals(true, $log->checkNoError());
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[0]);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[1]);
		
		$this->assertEquals('update', $ret[0]->kind);
		$this->assertEquals($this->p1, $ret[0]->contact);
		$this->assertEquals('update', $ret[1]->kind);
		$this->assertEquals($this->p2, $ret[1]->contact);
	}
		
     	

       
    public function testSyncToGoogleUpdated()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		$upds = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p1->resourceName, 'md5c', 'text'), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p2->resourceName, 'md5c', 'text2')
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5-g');
		$gside
			->expects($this->exactly(2))
			->method('contactUpdated')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn($upds);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(2))
			->method('mergeInto')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		
		
		$batchgetResponse = new \Google\Service\PeopleService\GetPeopleResponse();
		$batchgetResponse->setResponses([
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p1, 'requestedResourceName'=>$this->p1->resourceName]),
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p2, 'requestedResourceName'=>$this->p2->resourceName])
		]);
		
		$batchupdateResponse = new \Google\Service\PeopleService\BatchUpdateContactsResponse();
		$batchupdateResponse->setUpdateResult([
			$this->p1->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p1, 'requestedResourceName'=>$this->p1->resourceName]),
			$this->p2->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p2, 'requestedResourceName'=>$this->p2->resourceName])
		]);
				
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('getBatchGet')
			->with(
				$this->equalTo(['personFields'=>'names', 'resourceNames'=>[$this->p1->resourceName, $this->p2->resourceName]])				
			)
			->willReturn($batchgetResponse);
		
		$people
			->expects($this->once())
			->method('batchUpdateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchUpdateContactsRequest(
										[
											'contacts'	=> [$this->p1->resourceName => $this->p1, $this->p2->resourceName => $this->p2],
											'updateMask'=> 'names',
											'readMask'	=> 'names'
										]
									)
				)
			)
			->willReturn($batchupdateResponse);
		
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(1))	// called once to set sync token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_TO_GOOGLE, true);
		

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
     	
	
	     
    public function testSyncToGoogleUpdatedUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		$upds = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p1->resourceName, 'md5c', 'text'), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p2->resourceName, 'md5c', 'text2')
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5-g');
		$gside
			->expects($this->exactly(1))
			->method('contactUpdated')
			->withConsecutive(
				[$this->equalTo($this->p2)]
			);
		
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn($upds);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(2))
			->method('mergeInto')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			)
			->will($this->returnCallback(function($c){
				if ( $c->resourceName == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		
		
		$batchgetResponse = new \Google\Service\PeopleService\GetPeopleResponse();
		$batchgetResponse->setResponses([
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p1, 'requestedResourceName'=>$this->p1->resourceName]),
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p2, 'requestedResourceName'=>$this->p2->resourceName])
		]);

		$batchupdateResponse = new \Google\Service\PeopleService\BatchUpdateContactsResponse();
		$batchupdateResponse->setUpdateResult([
			$this->p2->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p2, 'requestedResourceName'=>$this->p2->resourceName])
		]);
		
					
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('getBatchGet')
			->with(
				$this->equalTo(['personFields'=>'names', 'resourceNames'=>[$this->p1->resourceName, $this->p2->resourceName]])				
			)
			->willReturn($batchgetResponse);
		
		$people
			->expects($this->once())
			->method('batchUpdateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchUpdateContactsRequest(
										[
											'contacts'	=> [$this->p2->resourceName => $this->p2],
											'updateMask'=> 'names',
											'readMask'	=> 'names'
										]
									)
				)
			)
			->willReturn($batchupdateResponse);

		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(0))	// called once to set sync token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_TO_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);	// an error occured
		$this->assertEquals(false, $log->checkNoError('Error during update acknowledgement or merge into Person object : Error here'));
	}
	
	
	
		
    public function testSyncToGoogleCreated()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		$p3 = new \Google\Service\PeopleService\Person([]);
		$p3->setNames([new \Google\Service\PeopleService\Name()]);
		$p3->names[0]->familyName = 'harold';
		$p3->names[0]->givenName = 'ted';		
		$p3->resourceName = 'https://www.google.com/editlink/ref3';
		$p4 = new \Google\Service\PeopleService\Person([]);
		$p4->setNames([new \Google\Service\PeopleService\Name()]);
		$p4->names[0]->familyName = 'gavin';
		$p4->names[0]->givenName = 'hale';		
		$p4->resourceName = 'https://www.google.com/editlink/ref4';
		
		$created = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Created('id3', $p3), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Created('id4', $p4)
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('contactCreated')
			->withConsecutive(
				[$this->equalTo($created[0])],
				[$this->equalTo($created[1])]
			);
		
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn($created);

		
		
		$batchcreateResponse = new \Google\Service\PeopleService\BatchCreateContactsResponse();
		$batchcreateResponse->setCreatedPeople([
			new \Google\Service\PeopleService\PersonResponse(['person'=>$p3, 'requestedResourceName'=>$p3->resourceName]),
			new \Google\Service\PeopleService\PersonResponse(['person'=>$p4, 'requestedResourceName'=>$p4->resourceName])
		]);

		$batchupdateUDefResponse = new \Google\Service\PeopleService\BatchUpdateContactsResponse();
		$batchupdateUDefResponse->setUpdateResult([
			$p3->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$p3, 'requestedResourceName'=>$p3->resourceName]),
			$p4->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$p4, 'requestedResourceName'=>$p4->resourceName])
		]);
		
					
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('batchCreateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchCreateContactsRequest(
										[
											'contacts'	=> [
																new \Google\Service\PeopleService\ContactToCreate(['contactPerson' => $p3]),
																new \Google\Service\PeopleService\ContactToCreate(['contactPerson' => $p4])
														   ],
											'readMask'	=> 'names,userDefined'
										]
									)
				)
			)
			->willReturn($batchcreateResponse);

		$people
			->expects($this->once())
			->method('batchUpdateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchUpdateContactsRequest(
										[
											'contacts'	=> [$p3->resourceName => $p3, $p4->resourceName => $p4],
											'updateMask'=> 'userDefined'
										]
									)
				)
			)
			->willReturn($batchupdateUDefResponse);


		
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(1))	// called once to set sync token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_TO_GOOGLE, true);

	
		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
		
    	
	
    public function testSyncToGoogleCreatedUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		$p3 = new \Google\Service\PeopleService\Person([]);
		$p3->setNames([new \Google\Service\PeopleService\Name()]);
		$p3->names[0]->familyName = 'harold';
		$p3->names[0]->givenName = 'ted';		
		$p3->resourceName = 'https://www.google.com/editlink/ref3';
		$p4 = new \Google\Service\PeopleService\Person([]);
		$p4->setNames([new \Google\Service\PeopleService\Name()]);
		$p4->names[0]->familyName = 'gavin';
		$p4->names[0]->givenName = 'hale';		
		$p4->resourceName = 'https://www.google.com/editlink/ref4';
		
		$created = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Created('id3', $p3), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Created('id4', $p4)
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('contactCreated')
			->withConsecutive(
				[$this->equalTo($created[0])],
				[$this->equalTo($created[1])]
			)
			->will($this->returnCallback(function($c){
				if ( $c->contact->resourceName == 'https://www.google.com/editlink/ref3' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn($created);
		
		
		
		$batchcreateResponse = new \Google\Service\PeopleService\BatchCreateContactsResponse();
		$batchcreateResponse->setCreatedPeople([
			new \Google\Service\PeopleService\PersonResponse(['person'=>$p3, 'requestedResourceName'=>$p3->resourceName]),
			new \Google\Service\PeopleService\PersonResponse(['person'=>$p4, 'requestedResourceName'=>$p4->resourceName])
		]);

		$batchupdateUDefResponse = new \Google\Service\PeopleService\BatchUpdateContactsResponse();
		$batchupdateUDefResponse->setUpdateResult([
			$p3->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$p3, 'requestedResourceName'=>$p3->resourceName]),
			$p4->resourceName => new \Google\Service\PeopleService\PersonResponse(['person'=>$p4, 'requestedResourceName'=>$p4->resourceName])
		]);
		
					
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('batchCreateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchCreateContactsRequest(
										[
											'contacts'	=> [
																new \Google\Service\PeopleService\ContactToCreate(['contactPerson' => $p3]),
																new \Google\Service\PeopleService\ContactToCreate(['contactPerson' => $p4])
														   ],
											'readMask'	=> 'names,userDefined'
										]
									)
				)
			)
			->willReturn($batchcreateResponse);

		$people
			->expects($this->once())
			->method('batchUpdateContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchUpdateContactsRequest(
										[
											'contacts'	=> [$p3->resourceName => $p3, $p4->resourceName => $p4],
											'updateMask'=> 'userDefined'
										]
									)
				)
			)
			->willReturn($batchupdateUDefResponse);


		
				
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(0))	// error occured, no set token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_TO_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside acknowledgment sync error : Error here'));
	}
		
		
     	       
    public function testSyncToGoogleNoUpdateRequired()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		$upds = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p1->resourceName, 'md5c', 'text'), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Updated($this->p2->resourceName, 'md5c', 'text2')
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('md5')
			->willReturn('md5c');
		$gside
			->expects($this->exactly(2))
			->method('contactUpdated')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn($upds);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn([]);
		
		
		$batchgetResponse = new \Google\Service\PeopleService\GetPeopleResponse();
		$batchgetResponse->setResponses([
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p1, 'requestedResourceName'=>$this->p1->resourceName]),
			new \Google\Service\PeopleService\PersonResponse(['person'=>$this->p2, 'requestedResourceName'=>$this->p2->resourceName])
		]);
	
					
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('getBatchGet')
			->with(
				$this->equalTo(['personFields'=>'names', 'resourceNames'=>[$this->p1->resourceName, $this->p2->resourceName]])				
			)
			->willReturn($batchgetResponse);
		
				
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(1))	// called once to set sync token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_TO_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}

	
	
	
    public function testDeleteToGoogle()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		$deleted = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Deleted('ref3', 'text 3'), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Deleted('ref4', 'text 4')
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('contactDeleted')
			->withConsecutive(
				[$this->equalTo($deleted[0])],
				[$this->equalTo($deleted[1])]
			);
		
		$contacts
			->expects($this->exactly(1))
			->method('listDeleted')
			->willReturn($deleted);
		
		
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('batchDeleteContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchDeleteContactsRequest(
										[
											'resourceNames'	=> [$deleted[0]->resourceName, $deleted[1]->resourceName]
										]
									)
				)
			)
			->willReturn(new \Google\Service\PeopleService\PeopleEmpty());

		
				
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(1))	// called once to set sync token
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_DELETE_TO_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}		

	

      
    public function testDeleteToGoogleUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		$deleted = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Deleted('ref3', 'text 3'), 
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Deleted('ref4', 'text 4')
		];
		
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		$gside
			->expects($this->exactly(2))
			->method('contactDeleted')
			->withConsecutive(
				[$this->equalTo($deleted[0])],
				[$this->equalTo($deleted[1])]
			)
			->will($this->returnCallback(function($c){
				if ( $c->resourceName == 'ref3' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		$contacts
			->expects($this->exactly(1))
			->method('listDeleted')
			->willReturn($deleted);
		
		
		
		$people = $this->createMock(\Google\Service\PeopleService\Resource\People::class);
		$people
			->expects($this->once())
			->method('batchDeleteContacts')
			->with(
				$this->equalTo(new \Google\Service\PeopleService\BatchDeleteContactsRequest(
										[
											'resourceNames'	=> [$deleted[0]->resourceName, $deleted[1]->resourceName]
										]
									)
				)
			)
			->willReturn(new \Google\Service\PeopleService\PeopleEmpty());

		
		$peopleservice->people = $people;
		$peopleservice
			->expects($this->exactly(0))	// error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_DELETE_TO_GOOGLE, true);

		// conflict, so this is an error, $ret == false
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside acknowledgment deletion error : Error here'));
	}		

	
	
	
    public function testDeleteFromGoogle()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$this->p1->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		$this->p2->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		
		$peopleservice
			->expects($this->exactly(2))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])],
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5c'));		
		$contacts
			->expects($this->exactly(2))
			->method('delete')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			);
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_DELETE_FROM_GOOGLE, false);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
  
		

    public function testDeleteFromGoogleUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$this->p1->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		$this->p2->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));
		
		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5c'));		
		$contacts
			->expects($this->exactly(2))
			->method('delete')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->will($this->returnCallback(function($c){
				if ( $c == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_DELETE_FROM_GOOGLE, false);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside deletion error : Error here'));
	}
		
	
  
       
    public function testDeleteFromGoogleConfirmModeOn()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$this->p1->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		$this->p2->setMetadata(new \Google\Service\PeopleService\PersonMetadata(['deleted'=>true]));
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		
		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names'])]
			)
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('getSyncData')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->willReturn(new \Nettools\GoogleAPI\Tools\PeopleSync\Res\SyncData(false, 'md5c'));		
		$contacts
			->expects($this->exactly(0))
			->method('delete');
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->sync($log, Manager::ONE_WAY_DELETE_FROM_GOOGLE, true);

		// confirm mode on
		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_array($ret));
		$this->assertEquals(true, $log->checkNoError());
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[0]);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Tools\PeopleSync\Res\Request::class, $ret[1]);
		
		$this->assertEquals('delete', $ret[0]->kind);
		$this->assertEquals($this->p1, $ret[0]->contact);
		$this->assertEquals('delete', $ret[1]->kind);
		$this->assertEquals($this->p2, $ret[1]->contact);
	}


	

    public function testDeferredUpdate()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('update', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('update', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
      
	
 
    public function testDeferredUpdateUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(0))	// error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			)
			->will($this->returnCallback(function($c){
				if ( $c->resourceName == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('update', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('update', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside update error : Error here'));
	}
		

	
	
	
    public function testDeferredDelete()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('delete')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			);
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('delete', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('delete', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
      	


    public function testDeferredDeleteUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		
		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(0))	//error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('delete')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->will($this->returnCallback(function($c){
				if ( $c == 'https://www.google.com/editlink/ref1' ) 
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('delete', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('delete', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside deletion error : Error here'));
	}
		
	
	

    public function testDeferredInvert()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('requestUpdate')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			);
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn([]);
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('invert', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('invert', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		

	
	

    public function testDeferredInvertUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(0))	// error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('requestUpdate')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->will($this->returnCallback(function($c){
				if ( $c == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		$contacts
			->expects($this->exactly(0))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(0))
			->method('listCreated')
			->willReturn([]);
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('invert', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('invert', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError("Clientside 'updated' flag raising error : Error here"));
	}
		
	
	
	

    public function testDeferredConflictPreserve()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		$contacts
			->expects($this->exactly(1))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(1))
			->method('listCreated')
			->willReturn([]);
		
		$conflicts
			->expects($this->exactly(2))
			->method('backupContactValues')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName), $this->equalTo(['name'])],
				[$this->equalTo($this->p2->resourceName), $this->equalTo(['name', 'surname'])]
			)
			->will($this->onConsecutiveCalls(['name'=>'lloyd'], ['name'=>'grant', 'surname'=>'lee']));
		$conflicts
			->expects($this->exactly(2))
			->method('restoreContactValues')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName), $this->equalTo(['name'=>'lloyd'])],
				[$this->equalTo($this->p2->resourceName), $this->equalTo(['name'=>'grant', 'surname'=>'lee'])]
			);
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p1, ['name']),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p2, ['name', 'surname'])
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		
	
	
	

    public function testDeferredConflictPreserveUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(0))		// error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		$contacts
			->expects($this->exactly(0))
			->method('listUpdated')
			->willReturn([]);
		$contacts
			->expects($this->exactly(0))
			->method('listCreated')
			->willReturn([]);
		
		$conflicts
			->expects($this->exactly(2))
			->method('backupContactValues')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName), $this->equalTo(['name'])],
				[$this->equalTo($this->p2->resourceName), $this->equalTo(['name', 'surname'])]
			)
			->will($this->onConsecutiveCalls(['name'=>'lloyd'], ['name'=>'grant', 'surname'=>'lee']));
		$conflicts
			->expects($this->exactly(2))
			->method('restoreContactValues')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName), $this->equalTo(['name'=>'lloyd'])],
				[$this->equalTo($this->p2->resourceName), $this->equalTo(['name'=>'grant', 'surname'=>'lee'])]
			)
			->will($this->returnCallback(function($c){
				if ( $c == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));
		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p1, ['name']),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p2, ['name', 'surname'])
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside conflict handling error : Error here'));
	}
		
	
	
	

    public function testDeferredConflictOverwrite()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(1))
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		$contacts
			->expects($this->exactly(2))
			->method('cancelUpdate')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			);

		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(true, $ret);
		$this->assertEquals(true, $log->checkNoError());
	}
		

	

    public function testDeferredConflictOverwriteUserException()
	{
		$peopleservice = $this->createMock(\Nettools\GoogleAPI\ServiceWrappers\PeopleService::class);
		$gside = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Googleside::class);
		$contacts = $this->getMockBuilder(\Nettools\GoogleAPI\Tools\PeopleSync\AbstractContacts::class)->setMethodsExcept(['getLogContext'])->getMock();
		$conflicts = $this->createMock(\Nettools\GoogleAPI\Tools\PeopleSync\Conflicts::class);
		$cside = new \Nettools\GoogleAPI\Tools\PeopleSync\Clientside($contacts, $conflicts);
		$log = new SyncLog();
		
		
		// mocking
		$conns = new \Google\Service\PeopleService\ListConnectionsResponse();
		$conns->setConnections([$this->p1, $this->p2]);
		$conns->nextSyncToken = 'tok2';
		

		$gside
			->method('getSyncToken')
			->willReturn('token');
		$gside
			->method('setSyncToken')
			->with($this->equalTo('tok2'));

		$peopleservice
			->expects($this->exactly(0))		// error occured
			->method('getAllContacts')
			->withConsecutive(
				[$this->equalTo('people/me'), $this->equalTo(['syncToken' => 'token', 'personFields' => 'names', 'requestSyncToken'=>true])]
			)
			//->will($this->onConsecutiveCalls())
			->willReturn($conns);
		
		$contacts
			->expects($this->exactly(2))
			->method('update')
			->withConsecutive(
				[$this->equalTo($this->p1)],
				[$this->equalTo($this->p2)]
			);
		$contacts
			->expects($this->exactly(2))
			->method('cancelUpdate')
			->withConsecutive(
				[$this->equalTo($this->p1->resourceName)],
				[$this->equalTo($this->p2->resourceName)]
			)
			->will($this->returnCallback(function($c){
				if ( $c == 'https://www.google.com/editlink/ref1' )
					throw new \Nettools\GoogleAPI\Tools\PeopleSync\UserException('Error here');
			}));

		
		
		$reqs = [
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p1),
			new \Nettools\GoogleAPI\Tools\PeopleSync\Res\Request('conflict', $this->p2)
		];
		
		
		$manager = new Manager($peopleservice, $gside, $cside, ['personFields'=>'names']);
		$ret = $manager->executeRequests($log, $reqs);

		$this->assertEquals(true, $log->checkCriticalOrPhpUnitAssertions());
		$this->assertEquals(true, is_bool($ret));
		$this->assertEquals(false, $ret);
		$this->assertEquals(false, $log->checkNoError('Clientside conflict handling error : Error here'));
	}

}








// log class
class SyncLog extends \Psr\Log\AbstractLogger
{
	public $entries = [];
	

    
	public function log($level, $message, array $context = array())
	{
		$this->entries[] = (object)['level'=>$level, 'message'=>$message, 'context'=>$context];
	}
	
	
	public function checkCriticalOrPhpUnitAssertions()
	{
		foreach ( $this->entries as $e )
			if ( $e->level == 'critical' )
				throw new \Exception($e->message . "\n" . print_r($context,true));
		
		return true;
	}

	
	public function checkNoError($msg = '')
	{
		//print_r($this->entries);
		foreach ( $this->entries as $e )
			if ( $e->level == 'error' )
				if ( !$msg )
					return false;
				else
					if ( is_int(strpos($e->message, $msg)) )
						return false;
		
		return true;
	}
}






?>