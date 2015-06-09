<?php
namespace Zeeye\Event;

/**
 * Class used to represent an event
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Event {
	private $_name;
	private $_parameters;
	
	private function __construct($name, array $parameters=array()) {
		$this->_name = $name;
		$this->_parameters = $parameters;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function hasParameter($key) {
		return isset($this->_parameters[$key]);
	}
	
	public function getParameter($key) {
		if (isset($this->_parameters[$key])) {
			return $this->_parameters[$key];
		}
		return null;
	}
	
	public function getParameters() {
		return $this->_parameters;
	}
	
	public static function create($name, array $parameters=array()) {
		return new Event($name, $parameters);
	}
}