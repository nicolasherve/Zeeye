<?php

namespace Zeeye\Router;

class RouteParameter {

    const DEFAULT_REGEXP = '[^/]+';

    private $_name;
    private $_value;
    private $_regexp;

    public function __construct($name) {
        $this->_name = $name;
        $this->_regexp = self::DEFAULT_REGEXP;
    }

    public function getName() {
        return $this->_name;
    }

    public function hasValue() {
        return isset($this->_value);
    }

    public function getValue() {
        return $this->_value;
    }

    public function setValue($value) {
        $this->_value = $value;
    }

    public function getRegexp() {
        return $this->_regexp;
    }

    public function setRegexp($regexp) {
        $this->_regexp = $regexp;
    }

}
