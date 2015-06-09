<?php

namespace Zeeye\App;

use StimLog\Manager\LoggerManager;
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
class LoggersConf extends Conf {

    const DEFAULT_FILENAME = 'stimlog.conf.php';

    private $_loggers;

    public function __construct($path) {
        parent::__construct($path);
        $this->_loggers = array();
    }

    public function getDefaultFileName() {
        return self::DEFAULT_FILENAME;
    }

    /**
     * Gets the loggers
     * 
     * @return array
     */
    public function getLoggers() {
        return (array) $this->_loggers;
    }

    public function fetch() {
        if (!File::exists($this->_path . $this->_fileName)) {
            return;
        }

        // Registers the application loggers
        $loggers = array();
        require($this->_path . $this->_fileName);
        $this->_loggers = $loggers;
    }

    public function setup() {
        LoggerManager::setupFromArray($this->_loggers);
    }

}
