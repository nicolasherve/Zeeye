<?php

namespace Zeeye\App;

use Zeeye\Util\File\File;

/**
 * Class used to manage an application's configuration
 * 
 * The application's configuration is composed of sub parts:
 * - application settings (required)
 * - routes settings (required)
 * - database settings (optional)
 * - loggers settings (optional)
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class RoutesConf extends Conf {

    const DEFAULT_FILENAME = 'routes.conf.php';

    private $_routes;

    public function __construct($path) {
        parent::__construct($path);
        $this->_routes = array();
    }

    public function getDefaultFileName() {
        return self::DEFAULT_FILENAME;
    }

    /**
     * Gets the routes
     * 
     * @return array
     */
    public function getRoutes() {
        return (array) $this->_routes;
    }

    /**
     * Checks the required routes settings
     * 
     * @throws AppException
     */
    private function _checkRequiredRoutes() {
        // TODO error404 + home
    }

    public function fetch() {
        // Make sure the application file exists
        File::checkFilePath($this->_path . $this->_fileName);

        // Registers the application routes
        $routes = array();
        require($this->_path . $this->_fileName);
        $this->_routes = $routes;

        $this->_checkRequiredRoutes();
    }

}
