<?php

namespace Zeeye\Util\Flash;

use Zeeye\Util\Session\Session;

class Flash {

    private static $_instances = array();
    private $_name;
    private $_type;
    private $_messages;

    private function __construct($name) {
        $this->_name = $name;
        $this->_messages = array();
        $this->_synchronizeFromSession();
    }

    public function getName() {
        return $this->_name;
    }
    
    public function getType() {
    	return $this->_type;
    }
    
    public function getMessages() {
    	return $this->_messages;
    }

    private function _addMessage($message, $type) {
        if (isset($this->_type)) {
        	if ($this->_type != $type) {
        		throw new FlashException("You cannot add a message with type [".$type."] because some messages with type [".$this->_type."] already exist");
        	}
        }
    	$this->_type = $type;
    	$this->_messages[] = $message;
        $this->_synchronizeToSession();
    }

    public function addSuccess($message) {
        $this->_addMessage($message, 'success');
    }

    public function addSuccessList(array $messages) {
        foreach ($messages as $message) {
            $this->addSuccess($message);
        }
    }

    public function addWarning($message) {
        $this->_addMessage($message, 'warning');
    }

    public function addWarningList(array $messages) {
        foreach ($messages as $message) {
            $this->addWarning($message);
        }
    }

    public function addError($message) {
        $this->_addMessage($message, 'error');
    }

    public function addErrorList(array $messages) {
        foreach ($messages as $message) {
            $this->addError($message);
        }
    }

    private function _reset() {
    	$this->_messages = array();
    	$this->_type = null;
    }
    
    public function remove() {
        $flash = clone($this);
        
        $this->_reset();

        $this->_synchronizeToSession();

        return $flash;
    }

    public function hasMessages() {
        return !empty($this->_messages);
    }

    private function _synchronizeFromSession() {
        if (!Session::isStarted()) {
        	return;
        }
    	if (Session::has($this->_name)) {
            $this->_messages = (array) Session::get($this->_name.'.messages');
            $this->_type = (string) Session::get($this->_name.'.type');
        }
    }

    private function _synchronizeToSession() {
    	if (!Session::isStarted()) {
    		return;
    	}
    	Session::set($this->_name.'.messages', $this->_messages);
    	Session::set($this->_name.'.type', $this->_type);
    }

    /**
     * 
     * @param type $name
     * @return Flash
     */
    public static function getInstance($name = 'flash') {
        if (!isset(self::$_instances[$name])) {
            self::$_instances[$name] = new Flash($name);
        }
        return self::$_instances[$name];
    }

}
