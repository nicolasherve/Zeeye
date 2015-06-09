<?php

namespace Zeeye\App;

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
abstract class Conf {

    protected $_path;
    protected $_fileName;

    public function __construct($path) {
        $this->_path = $path;
        $this->_fileName = $this->getDefaultFileName();
    }

    public function getFileName() {
        return $this->_fileName;
    }

    public function setFileName($fileName) {
        $this->_fileName = $fileName;
    }

    public abstract function getDefaultFileName();

    public abstract function fetch();
}
