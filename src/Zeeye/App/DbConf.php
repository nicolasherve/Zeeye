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
class DbConf extends Conf {

    const DEFAULT_FILENAME = 'db.conf.php';
    const DEFAULT_PROFILE_NAME = 'default';

    private $_dbProfiles;

    public function __construct($path) {
        parent::__construct($path);
        $this->_dbProfiles = array();
    }

    public function getDefaultFileName() {
        return self::DEFAULT_FILENAME;
    }

    public function getDefaultProfileName() {
        return self::DEFAULT_PROFILE_NAME;
    }

    public function getDbProfiles() {
        return $this->_dbProfiles;
    }

    public function fetch() {
        if (!File::exists($this->_path . $this->_fileName)) {
            return;
        }

        // Registers the application database profiles
        $db = array();
        require($this->_path . $this->_fileName);
        $this->_dbProfiles = $db;

        // If there is only one defined configuration and the default one is not defined
        if (count($db) == 1 && !isset($db[self::DEFAULT_PROFILE_NAME])) {
            // We consider the defined configuration as the default one
            $this->_dbProfiles[self::DEFAULT_PROFILE_NAME] = current($db);
        }
    }

}
