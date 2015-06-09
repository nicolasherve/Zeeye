<?php
namespace Zeeye\Util\Request;

class Client {
	
	private $_address;
	private $_port;
	private $_agent;
	
	public function getAddress() {
		return $this->_address;
	}
	
	public function setAddress($address) {
		$this->_address = $address;
	}
	
	public function getPort() {
		return $this->_port;
	}
	
	public function setPort($port) {
		$this->_port = $port;
	}
	
	public function getAgent() {
		return $this->_agent;
	}
	
	public function setAgent($agent) {
		$this->_agent = $agent;
	}
	
	
}