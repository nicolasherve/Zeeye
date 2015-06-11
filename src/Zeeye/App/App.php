<?php

namespace Zeeye\App;

use Zeeye\Util\Autoloader\Autoloader;
use Zeeye\Util\Date\Date;

/**
 * Class used to manipulate an application
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class App {

    /**
     * The path to the application's directory
     * 
     * @var string
     */
    private $_path;

    /**
     * The path to the application's configuration's directory
     * 
     * @var string
     */
    private $_configurationPath;

    /**
     * Application configuration
     * 
     * @var AppConf
     */
    private $_appConfiguration;

    /**
     * Routes configuration
     *
     * @var RoutesConf
     */
    private $_routesConfiguration;

    /**
     * Databases configuration
     *
     * @var DbConf
     */
    private $_dbConfiguration;

    /**
     * Loggers configuration
     *
     * @var LoggersConf
     */
    private $_loggersConfiguration;

    /**
     * The App instance
     * 
     * @var App
     */
    private static $_instance = null;

    /**
     * Private constructor
     */
    private function __construct($path, $configurationPath = '') {

        // Registers the application's path
        $this->_path = realpath($path) . '/';

        // Registers the application's configuration path
        $this->_configurationPath = $configurationPath;

        // Creates the configurations instances
        $this->_appConfiguration = new AppConf($this->_path . $this->_configurationPath);
        $this->_routesConfiguration = new RoutesConf($this->_path . $this->_configurationPath);
        $this->_dbConfiguration = new DbConf($this->_path . $this->_configurationPath);
        $this->_loggersConfiguration = new LoggersConf($this->_path . $this->_configurationPath);
    }

    /**
     * Return the application's path
     * 
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }

    /**
     * Return the path to the application's configuration's file
     * 
     * @return string
     */
    public function getConfigurationPath() {
        return $this->_configurationPath;
    }

    public function getAppConfiguration() {
        return $this->_appConfiguration;
    }

    public function getRoutesConfiguration() {
        return $this->_routesConfiguration;
    }

    public function getLoggersConfiguration() {
        return $this->_loggersConfiguration;
    }

    public function getDbConfiguration() {
        return $this->_dbConfiguration;
    }

    /**
     * Sets the configured error handler
     */
    private function _setupErrorHandler() {
        // Get the error handler class name
        $errorHandlerClassName = $this->_appConfiguration->getErrorHandler();

        // If there is no error handler, stops
        if (!empty($errorHandlerClassName)) {
            return;
        }

        // Separate the class name from the method
        $errorHandlerInfo = explode('::', $errorHandlerClassName);

        // If the error handler class and method is not correctly defined
        if (!isset($errorHandlerInfo[1])) {
            throw new AppException('The error handler you specified with the $app["error-handler"] setting must define a class name and a method, separated by ::');
        }

        // Make sure the error handler is included
        Autoloader::autoload($errorHandlerInfo[0]);

        // Set the error handler
        set_error_handler($errorHandlerClassName);
    }

    /**
     * Sets the configured exception handler
     */
    private function _setupExceptionHandler() {
        // Get the exception handler class name
        $exceptionHandlerClassName = $this->_appConfiguration->getExceptionHandler();

        // If there is no exception handler, stops
        if (empty($exceptionHandlerClassName)) {
            return;
        }

        // Separate the class name from the method
        $exceptionHandlerInfo = explode('::', $exceptionHandlerClassName);

        // If the error handler class and method is not correctly defined
        if (!isset($exceptionHandlerInfo[1])) {
            throw new AppException('The exception handler you specified with the $app["exception-handler"] setting must define a class name and a method, separated by ::');
        }

        // Make sure the error handler is included
        Autoloader::autoload($exceptionHandlerInfo[0]);

        // Set the error handler
        set_exception_handler($exceptionHandlerClassName);
    }

    private function _fetchConfigurations() {
        // Fetch the application configuration
        $this->_appConfiguration->fetch();

        // Fetch the required routes settings
        $this->_routesConfiguration->fetch();

        // Fetch the optional loggers
        $this->_loggersConfiguration->fetch();

        // Fetch the optional database profiles
        $this->_dbConfiguration->fetch();
    }

    private function _activateConfiguration() {
        // Setup the loggers
        $this->_loggersConfiguration->setup();

        // Setup the errors handler
        $this->_setupErrorHandler();

        // Setup the exceptions handler
        $this->_setupExceptionHandler();

        // Sets the default timezone for the application
        Date::setDefaultTimeZone($this->_appConfiguration->getDefaultLocaleTimezone());
    }

    /**
     * Setup the application
     * 
     * @param string $name name of the application
     * @param string $configurationPath the configuration's directory path
     */
    public static function setup($name, $configurationPath = '') {

        // If a previous setup was called
        if (isset(self::$_instance)) {
            throw new AppException('The App::setup() operation must be called only once');
        }

        // Create the App object
        $app = new App($name, $configurationPath);

        // Fetch the related configurations
        $app->_fetchConfigurations();

        // Activate the app
        $app->_activateConfiguration();

        // Register it in the instances list
        self::$_instance = $app;
    }

    /**
     * Get the App for the current application
     * 
     * @return App the requested application
     */
    public static function getInstance() {
        return self::$_instance;
    }

}
