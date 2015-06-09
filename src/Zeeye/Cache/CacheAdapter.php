<?php

namespace Zeeye\Cache;

abstract class CacheAdapter {

    protected $_parameters;
    protected $_lifeTime;

    public function setup(array $parameters) {
        if (!isset($parameters['lifeTime'])) {
            throw new CacheAdapterException('The parameter [lifeTime] is required');
        }
        $this->_parameters = $parameters;
        $this->_lifeTime = $parameters['lifeTime'];
    }

    public abstract function clear();

    public abstract function delete($key);

    public abstract function set($key, $value);

    public abstract function get($key);

    public function isEnabled() {
        if (isset($this->_parameters['enabled']) && $this->_parameters['enabled'] === false) {
            return false;
        }
        return true;
    }

    public function getLifeTime() {
        return $this->_lifeTime;
    }

}
