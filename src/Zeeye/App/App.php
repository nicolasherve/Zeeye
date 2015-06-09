<?php

namespace Zeeye\App;

use Zeeye\Router\Router;
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
     * The name of the application (used as the associated directory's name)
     * 
     * @var string
     */
    private $_name;

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
     * The application router
     * 
     * @var Router
     */
    private $_router;

    /**
     * The list of App instances
     * 
     * @var array
     */
    private static $_instances = array();

    /**
     * The current application instance
     * 
     * @var App
     */
    private static $_current = null;

    /**
     * Private constructor
     */
    private function __construct($name, $configurationPath = '') {
        // The name can only contain alpha-numeric characters and the underscore character
        if (preg_match('/[^a-z0-9_]/i', $name)) {
            throw new AppException("The name of your application can only contain alpha-numeric characters and the underscore character");
        }

        // Registers the application's name
        $this->_name = $name;

        // Registers the application's path
        $this->_path = realpath(ZEEYE_PATH . '../../apps/' . $name) . '/';

        // Registers the application's configuration path
        $this->_configurationPath = $configurationPath;

        // Creates the configurations instances
        $this->_appConfiguration = new AppConf($this->_path . $this->_configurationPath);
        $this->_routesConfiguration = new RoutesConf($this->_path . $this->_configurationPath);
        $this->_dbConfiguration = new DbConf($this->_path . $this->_configurationPath);
        $this->_loggersConfiguration = new LoggersConf($this->_path . $this->_configurationPath);

        $this->_router = null;
    }

    /**
     * Gets the application's name
     * 
     * @return string
     */
    public function getName() {
        return $this->_name;
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
     * Get the application's router
     *
     * @return Router
     */
    public function getRouter() {
        return $this->_router;
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
        $this->_router = Router::getInstanceForApp($this);

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
        if (isset(self::$_instances[$name])) {
            throw new AppException('The App::setup() operation must be called only once for a given application');
        }

        // Create the App object
        $app = new App($name, $configurationPath);

        // Fetch the related configurations
        $app->_fetchConfigurations();

        // Register it as the current application
        if (!isset(self::$_current)) {
            self::$_current = $app;
            self::$_current->_activateConfiguration();
        }

        // Register it in the instances list
        self::$_instances[$name] = $app;
    }

    /**
     * Get the App corresponding to the given application's name
     * 
     * If no name is given, the current application will be returned
     * 
     * @param string $name name of the requested application
     * @return App the requested application
     */
    public static function getInstance($name = null) {
        if (!isset($name)) {
            return self::$_current;
        }
        if (!isset(self::$_instances[$name])) {
            throw new AppException("You must create the instance with App::setup() before trying to get it through App::getInstance()");
        }
        return self::$_instances[$name];
    }

}
