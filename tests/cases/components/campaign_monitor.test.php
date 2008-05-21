<?php

class FakeCampaignMonitorController {}

class CampaignMonitorTestCase extends CakeTestCase {

	function testSetClientId() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->setClientId(null);
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->setClientId(324121);
		$this->assertTrue($result);
		
		$result = $this->CampaignMonitorComponentTest->setClientId('324121');
		$this->assertTrue($result);
	}

	function testAddSubscriber() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->addSubscriber('test1@test');
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->addSubscriber('test2@test.com');
		$this->assertTrue($result);
		
		$result = $this->CampaignMonitorComponentTest->addSubscriber(1241);
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->addSubscriber('test2@test.com', null, null, true);
		$this->assertTrue($result);
		
		$result = $this->CampaignMonitorComponentTest->addSubscriber('test3@test.com', 'Test Email');
		$this->assertTrue($result);
	}
	
	function testGetSingleSubscriber() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->getSingleSubscriber('test1@test');
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->getSingleSubscriber('test2@test.com');
		$this->assertIsA($result, 'array');
	}
	
	function testCheckSubscription() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->checkSubscription('test1@test');
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->checkSubscription('test2@test.com');
		$this->assertTrue($result);
	}

	function testRemoveSubscriber() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->removeSubscriber('test1@test');
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->removeSubscriber('test2@test.com');
		$this->assertTrue($result);
	}
	
	function testGetTypeFromList() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->getTypeFromList();
		$this->assertIsA($result, 'array');
		
		$result = $this->CampaignMonitorComponentTest->getTypeFromList(null, null, 'unsubscribed');
		$this->assertIsA($result, 'array');
		
		$result = $this->CampaignMonitorComponentTest->getTypeFromList(null, null, 'bounced');
		$this->assertIsA($result, 'array');
		
		$result = $this->CampaignMonitorComponentTest->getTypeFromList('12/29/1981', null, 'bounced');
		$this->assertFalse($result);
		
		$result = $this->CampaignMonitorComponentTest->getTypeFromList(date('Y-m-d H:i:s', strtotime('-2 years')));
		$this->assertIsA($result, 'array');
	}

	function testGetLists() {
		App::import('Component', 'CampaignMonitor');
		
		$controller = new FakeCampaignMonitorController();
		$this->CampaignMonitorComponentTest = new CampaignMonitorComponent();
		
		$this->CampaignMonitorComponentTest->startup(&$controller);
		
		$result = $this->CampaignMonitorComponentTest->getLists();
		$this->assertIsA($result, 'array');
		
		$result = $this->CampaignMonitorComponentTest->getLists(null);
		$this->assertIsA($result, 'array');
		
		$result = $this->CampaignMonitorComponentTest->getLists(null, true);
		$this->assertIsA($result, 'array');
	}
}