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
class AppConf extends Conf {

    const DEFAULT_FILENAME = 'app.conf.php';

    /**
     * List of the application configuration settings
     * 
     * @var array
     */
    private $_settings;

    public function __construct($path) {
        parent::__construct($path);
        $this->_settings = array();
    }

    public function getDefaultFileName() {
        return self::DEFAULT_FILENAME;
    }

    /**
     * Gets the web root location
     * 
     * @return string
     */
    public function getWebroot() {
        if (!isset($this->_settings['web-root'])) {
            return null;
        }
        return (string) $this->_settings['web-root'];
    }

    /**
     * Gets the error handler class name
     * 
     * @return string the error handler class name
     */
    public function getErrorHandler() {
        if (!isset($this->_settings['error-handler'])) {
            return null;
        }
        return (string) $this->_settings['error-handler'];
    }

    /**
     * Gets the exception handler class name
     * 
     * @return string the exception handler class name
     */
    public function getExceptionHandler() {
        if (!isset($this->_settings['exception-handler'])) {
            return null;
        }
        return (string) $this->_settings['exception-handler'];
    }

    /**
     * Indicates whether the cache is used or not
     * 
     * @return boolean
     */
    public function useCache() {
        if (!isset($this->_settings['cache']) || !isset($this->_settings['cache']['enabled'])) {
            return false;
        }
        return (boolean) $this->_settings['cache']['enabled'];
    }

    /**
     * Gets the configuration settings of the given cache name
     * 
     * @param string $name the name of the requested cache
     * @return array
     */
    public function getCache($name) {
        if (!isset($this->_settings['cache'])) {
            throw new AppException('The $app["cache"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['cache']['adapters'])) {
            throw new AppException('The $app["cache"]["adapters"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['cache']['adapters'][$name])) {
            throw new AppException('The requested cache named [' . $name . '] is not defined in the configuration file');
        }
        return (array) $this->_settings['cache']['adapters'][$name];
    }

    /**
     * Gets the current ISO code for locale language
     * 
     * @return string
     */
    public function getDefaultLocaleLanguage() {
        if (!isset($this->_settings['locale']['default-language'])) {
            return null;
        }
        return (string) $this->_settings['locale']['default-language'];
    }

    /**
     * Gets the current TimeZone code for locale
     * 
     * @return string
     */
    public function getDefaultLocaleTimezone() {
        if (!isset($this->_settings['locale']) || !isset($this->_settings['locale']['default-timezone'])) {
            return null;
        }
        return (string) $this->_settings['locale']['default-timezone'];
    }

    /**
     * Gets the current date format for locale
     * 
     * @return string
     */
    public function getDefaultLocaleDateFormat() {
        if (!isset($this->_settings['locale']['default-date-format'])) {
            return null;
        }
        return (string) $this->_settings['locale']['default-date-format'];
    }

    /**
     * Gets the current adapter path for locale
     * 
     * @return string
     */
    public function getLocaleAdapter() {
        if (!isset($this->_settings['locale']['adapter'])) {
            return null;
        }
        return (string) $this->_settings['locale']['adapter'];
    }

    /**
     * Gets the current adapter path for session
     * 
     * @return string
     */
    public function getSessionAdapter() {
        if (!isset($this->_settings['session-adapter'])) {
            return null;
        }
        return (string) $this->_settings['session-adapter'];
    }

    /**
     * Gets the class name of the given Dao name
     * 
     * @param string $name the name of the requested Dao
     * @return array
     */
    public function getDao($name) {
        if (!isset($this->_settings['daos'])) {
            throw new AppException('The $app["daos"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['daos'][$name])) {
            throw new AppException('The requested dao named [' . $name . '] is not defined in the configuration file');
        }

        $config = array();
        if (is_string($this->_settings['daos'][$name])) {
            $config['class'] = $this->_settings['daos'][$name];
        } elseif (is_array($this->_settings['daos'][$name])) {
            // The "class" setting is required
            if (!isset($this->_settings['daos'][$name]['class'])) {
                throw new AppException('The requested dao named [' . $name . '] is defined in the configuration file as an array and thus must define a "class" key');
            }
            $config['class'] = $this->_settings['daos'][$name]['class'];

            // If the DAO contains a "cache" setting, get it
            if (isset($this->_settings['daos'][$name]['cache'])) {
                $config['cache'] = $this->_settings['daos'][$name]['cache'];
            }

            // If the DAO contains a "connection" setting, get it
            if (isset($this->_settings['daos'][$name]['connection'])) {
                $config['connection'] = $this->_settings['daos'][$name]['connection'];
            }
        }

        return (array) $config;
    }

    /**
     * Gets the class name of the given zone name
     * 
     * @param string $name the name of the requested zone
     * @return string
     */
    public function getZone($name) {
        if (!isset($this->_settings['zones'])) {
            throw new AppException('The $app["zones"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['zones'][$name])) {
            throw new AppException('The requested zone named [' . $name . '] is not defined in the configuration file');
        }
        return (string) $this->_settings['zones'][$name];
    }

    /**
     * Gets the class name of the given validator name
     *
     * @param string $name the name of the requested validator
     * @return string
     */
    public function getValidator($name) {
        if (!isset($this->_settings['validators'])) {
            throw new AppException('The $app["validators"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['validators'][$name])) {
            throw new AppException('The requested validator named [' . $name . '] is not defined in the configuration file');
        }
        return (string) $this->_settings['validators'][$name];
    }

    /**
     * Gets the class name of the given service name
     *
     * @param string $name the name of the requested service
     * @return string
     */
    public function getService($name) {
        if (!isset($this->_settings['services'])) {
            throw new AppException('The $app["services"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['services'][$name])) {
            throw new AppException('The requested service named [' . $name . '] is not defined in the configuration file');
        }
        return (string) $this->_settings['services'][$name];
    }

    /**
     * Gets the class name of the given helper name
     * 
     * @param string $name the name of the requested helper
     * @return string
     */
    public function getHelper($name) {
        if (!isset($this->_settings['helpers'])) {
            throw new AppException('The $app["helpers"] setting is not defined in the configuration file');
        }
        if (!isset($this->_settings['helpers'][$name])) {
            throw new AppException('The requested helper named [' . $name . '] is not defined in the configuration file');
        }
        return (string) $this->_settings['helpers'][$name];
    }

    /**
     * Gets the information about the current filters
     * 
     * @return array
     */
    public function getFilters() {
        if (!isset($this->_settings['filters'])) {
            return array();
        }
        return (array) $this->_settings['filters'];
    }

    /**
     * Gets the information about the current event listeners
     * 
     * @return array
     */
    public function getEventListeners() {
        if (!isset($this->_settings['event-listeners'])) {
            return array();
        }
        return (array) $this->_settings['event-listeners'];
    }

    /**
     * Gets the default charset
     * 
     * @return string
     */
    public function getDefaultCharset() {
        if (!isset($this->_settings['default-charset'])) {
            return null;
        }
        return (string) $this->_settings['default-charset'];
    }

    /**
     * Checks the required configuration settings
     * 
     * @throws AppException
     */
    private function _checkRequiredConfiguration() {
        if (!isset($this->_settings['locale']) || !isset($this->_settings['locale']['default-timezone'])) {
            throw new AppException('The $app["locale"]["timezone"] configuration value is required');
        }
    }

    /**
     * Fetch the configuration's file for the current application
     */
    public function fetch() {
        // If the setup is already done, do nothing
        if (!empty($this->_settings)) {
            return;
        }

        // Make sure the application file exists
        File::checkFilePath($this->_path . $this->_fileName);

        // Registers the application configuration
        $app = array();
        require($this->_path . $this->_fileName);
        $this->_settings = $app;

        $this->_checkRequiredConfiguration();
    }

}
