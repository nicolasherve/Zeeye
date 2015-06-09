<?php

namespace Zeeye\App;

/**
 * Trait used to provide convenient access to the application instance
 */
trait AppAccessor {

    private $_app;

    /**
     * Get the application
     *
     * @return App
     */
    public function getApp($name = null) {
        if (!isset($this->_app)) {
            $this->_app = App::getInstance($name);
        }

        return $this->_app;
    }

}
