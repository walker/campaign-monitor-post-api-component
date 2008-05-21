<?php

uses('http_socket');
uses('xml');
uses('validation');

class CampaignMonitorComponent extends Object {
	var $camp_mon;

	// Set up your API key
	var $api_key;
	var $client_id;
	var $campaign_id;
	var $list_id;
	var $url = 'http://app.campaignmonitor.com/api/api.asmx';
	
	//Setup the basics
	function startup(&$controller) {
		error_reporting(E_ALL);
		
		$this->api_key = Configure::read('CampaignMonitor.key');
		if(Configure::read('CampaignMonitor.client_id')) {
			$this->client_id = Configure::read('CampaignMonitor.client_id');
		}
		if(Configure::read('CampaignMonitor.list_id')) {
			$this->list_id = Configure::read('CampaignMonitor.list_id');
		}
		if(Configure::read('CampaignMonitor.synch_system_time')) {
			$this->getSystemTime();
		}
		$this->validate = new Validation();
	}
	
	function setClientId($id=null) {
		if(is_int($id) || is_string($id)) {
			$this->client_id = $id;
			return true;
		} else {
			return false;
		}
	}
	
	function addSubscriber($email, $name=null, $list_id=null, $force_add=false) {
		$data = array();
		$data['ListID'] = ($list_id) ? $list_id : $this->list_id;
		if($name) {
			if(is_string($name)) { $data['Name'] = $name; } else {
				return false;
				exit();
			}
		} else { $data['Name'] = ''; }
		
		if($this->validate->email($email)) {
			$data['Email'] = $email;
		} else {
			/* email is not an email, return false */
			return false;
			exit();
		}
		
		$action = ($force_add) ? 'Subscriber.AddAndResubscribe' : 'Subscriber.Add';
		
		if($this->__postRequest($action, $data)) {
			return $this->__evalResult(true);
		} else {
			return false;
		}
	}
	
	function removeSubscriber($email, $list_id=null) {
		$data = array();
		$data['ListID'] = ($list_id) ? $list_id : $this->list_id;
		if($this->validate->email($email)) {
			$data['Email'] = $email;
		} else {
			/* email is not an email, return false */
			$this->error = 'Please provide a valid email address.';
			return false;
			exit();
		}
		if($this->__postRequest('Subscriber.Unsubscribe', $data)) {
			return $this->__evalResult(true);
		} else {
			return false;
		}
	}
	
	function getSingleSubscriber($email, $list_id=null) {
		$data = array();
		$data['ListID'] = ($list_id) ? $list_id : $this->list_id;
		if($this->validate->email($email)) {
			$data['EmailAddress'] = $email;
		} else {
			/* email is not an email, return false */
			$this->error = 'Please provide a valid email address.';
			return false;
			exit();
		}
		if($this->__postRequest('Subscribers.GetSingleSubscriber', $data)) {
			$eval = $this->__evalResult();
			return $eval['anyType'];
		} else {
			return false;
		}
	}
	
	function checkSubscription($email, $list_id=null) {
		$data = array();
		$data['ListID'] = ($list_id) ? $list_id : $this->list_id;
		if($this->validate->email($email)) {
			$data['Email'] = $email;
		} else {
			/* email is not an email, return false */
			$this->error = 'Please provide a valid email address.';
			return false;
			exit();
		}
		if($this->__postRequest('Subscribers.GetIsSubscribed', $data)) {
			$eval = $this->__evalResult(true);
			return $eval;
		} else {
			return false;
		}
	}
	
	function getTypeFromList($date=null, $list_id=null, $type='active') {
		switch($type) {
			case 'unsubscribed':
				$action = 'Unsubscribed';
				break;
			case 'bounced':
				$action = 'Bounced';
				break;
			default:
				$action = 'Active';
		}
		$data = array();
		if(!$date) {
			$data['date'] = date('Y-m-d H:i:s', strtotime('-10 years'));
		} else {
			// should check here with pattern matching that date is correct
			if(preg_match('/([1-2][0-9])\d{2}-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3[0-1]))(T|\s)(([0-1][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])/', $date)) {
				$data['date'] = $date;
			} else {
				$this->error = 'The date entered must be formatted in "Y-m-d H:i:s" format';
				return false;
			}
		}
		$data['ListID'] = ($list_id) ? $list_id : $this->list_id;
		if($this->__postRequest('Subscribers.Get'.$action, $data)) {
			$eval = $this->__evalResult();
			return $eval['anyType'];
		} else {
			return false;
		}
	}
	
	/*
	 * Campaign monitor's getLists & getSegments in a single method
	 * set second arg to true for segments
	 * GetLists: http://www.campaignmonitor.com/api/Client.GetLists.aspx
	 * GetSegments: http://www.campaignmonitor.com/api/Client.GetSegments.aspx
	 */
	function getLists($client_id=null, $segments=false) {
		if($client_id!=null)
			$this->setClientId($client_id);
		
		if(!empty($this->client_id)) {
			$data = array('ClientID'=>$this->client_id);
			$action = ($segments) ? 'Segments' : 'Lists';
			if($this->__postRequest('Client.Get'.$action, $data)) {
				$eval = $this->__evalResult();
				if($eval==null) {
					return array();
				} else {
					return $eval['anyType'];
				}
			} else {
				return false;
			}
		} else {
			$this->error = 'A client ID must be provided before lists can be gathered.';
			return false;
		}
	}
	
	/*
	 * GetCampaigns: http://www.campaignmonitor.com/api/Client.GetCampaigns.aspx
	 */
	function getCampaigns($client_id=null) {
		if($client_id!=null)
			$this->setClientId($client_id);
		
		if(!empty($this->client_id)) {
			$data = array('ClientID'=>$this->client_id);
			if($this->__postRequest('Client.GetCampaigns', $data)) {
				$eval = $this->__evalResult();
				return $eval['anyType'];
			} else {
				return false;
			}
		} else {
			$this->error = 'A client ID must be provided before campaigns can be gathered.';
			return false;
		}
	}
	
	function sendCampaign($campaign_id, $delivery_date=null, $conf_email_address=null) {
		$data = array();
		
		if(is_string($campaign_id) || is_int($campaign_id)) {
			$data['CampaignID'] = $campaign_id;
			
			$data['SendDate'] = ($delivery_date) ? $delivery_date : date('Y-m-d H:i:s');
			
			if($conf_email_address) {
				$data['ConfirmationEmail'] = $conf_email_address;
			} else if(Configure::read('CampaignMonitor.confirmation_email_address')) {
				$data['ConfirmationEmail'] = Configure::read('CampaignMonitor.confirmation_email_address');
			} else {
				$data['ConfirmationEmail'] = 'webmaster@'.$SERVER['HTTP_HOST'];
			}
			
			if(!empty($this->client_id)) {
				$data = array('ClientID'=>$this->client_id);
				if($this->__postRequest('Campaign.Send', $data)) {
					$eval = $this->__evalResult(true);
					return $eval;
				} else {
					return false;
				}
			} else {
				$this->error = 'A client ID must be provided before campaigns can be gathered.';
				return false;
			}
		} else {
			/* email is not an email, return false */
			$this->error = 'Please provide a valid campaign ID.';
			return false;
			exit();
		}
	}
	
	/*
	 * Campaign.GetSummary: http://www.campaignmonitor.com/api/Campaign.GetSummary.aspx
	 * Campaign.GetOpens: http://www.campaignmonitor.com/api/Campaign.GetOpens.aspx
	 */
	function getStats($campaign_id, $type="summary") {
		$data = array();
		
		switch($type) {
			case 'open':
				$action = 'Opens';
				break;
			case 'bounces':
				$action = 'Bounces';
				break;
			case 'clicks':
				$action = 'Clicks';
				break;
			case 'unsubscribes':
				$action = 'Unsubscribes';
				break;
			default:
				$action = 'Summary';
		}
		
		if(is_string($campaign_id) || is_int($campaign_id)) {
			$data['CampaignID'] = $campaign_id;
			if($this->__postRequest('Campaign.Get'.$action, $data)) {
				$eval = $this->__evalResult(true);
				return $eval;
			} else {
				return false;
			}
		} else {
			/* email is not an email, return false */
			$this->error = 'Please provide a valid campaign ID.';
			return false;
		}
	}
	
	function getClients() {
		if($this->__postRequest('User.GetClients')) {
			$eval = $this->__evalResult();
			return $eval['anyType'];
		} else {
			return false;
		}
	}
	
	function getSystemTime() {
		if($this->__postRequest('User.GetSystemDate')) {
			$eval = $this->__evalResult();
			$this->system_time = $eval['anyType'];
			return true;
		} else {
			return false;
		}
	}
	
	function __postRequest($action=null, $data=array()) {
		$post_string = array('ApiKey='.$this->api_key);
		foreach($data as $key => $value) {
			$post_string[] = $key.'='.urlencode($value);
		}
		$post_string = implode('&', $post_string);
		
		$socket = new HttpSocket();
		$action = ($action) ? '/'.$action : '';
		// pr($this->url.$action);
		// pr($post_string);
		$this->result  = $socket->post($this->url.$action, $post_string);
		return true;
	}
	
	function __evalResult($boolean=false) {
		$xml = new XML();
		$xml->load($this->result);
		
		$this->nodes = $this->__iterate($xml->children);
		if($boolean) {
			// return true or false depending on status message
			if((isset($this->nodes['Result']['Code']) && $this->nodes['Result']['Code']=='0') || (isset($this->nodes['anyType'][0]) && $this->nodes['anyType'][0]=='True')) {
				$this->error = false;
				return true;
			} else {
				if(isset($this->nodes['Result']['Message'])) {
					$this->error = $this->nodes['Result']['Message'];
				} else if(isset($this->nodes['anyType']['Message'])) {
					$this->error = $this->nodes['anyType']['Message'];
				}
				return false;
			}
		} else {
			// return an array containing the parsed out data
			return $this->nodes;
		}
	}
	
	function __iterate($children) {
		$nodes = array();
		$count = count($children);
		for($i = 0; $i<$count; $i++) {
			$tmp_name = explode(':', $children[$i]->name);
			if(isset($children[$i]->children) && count($children[$i]->children)>0) {
				if($count>1)
				$nodes[$tmp_name[2]][$i] = $this->__iterate($children[$i]->children);
				else
				$nodes[$tmp_name[2]] = $this->__iterate($children[$i]->children);
			} else {
				if($count>1)
				$nodes[$tmp_name[2]] = $children[$i]->value;
				else
				$nodes[$tmp_name[2]][$i] = $children[$i]->value;
			}
		}
		return $nodes;
	}
}

?>