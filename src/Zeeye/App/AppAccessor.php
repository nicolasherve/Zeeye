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
    public function getApp() {
        if (!isset($this->_app)) {
            $this->_app = App::getInstance();
        }

        return $this->_app;
    }

}
