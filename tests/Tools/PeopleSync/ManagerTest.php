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