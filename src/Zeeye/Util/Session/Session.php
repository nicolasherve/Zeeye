<?php

namespace Zeeye\Util\Session;

use Zeeye\App\App;
use Zeeye\Util\Exception\InvalidTypeException;
use StimLog\Logger\Logger;

/**
 * Class used to manage the session
 * 
 * Note that the class is meant to manage one session per user
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Session {

    /**
     * The logger
     *
     * @var Logger
     */
    private static $_logger = null;

    /**
     * The current adapter used for session management
     * 
     * @var SessionAdapter
     */
    private static $_adapter = null;

    /**
     * Indicates whether the current session is started or not
     * 
     * @var boolean
     */
    private static $_isStarted = false;

    private static function _initializeLogger() {
        if (!isset(self::$_logger)) {
            self::$_logger = Logger::create(__CLASS__);
        }
    }

    /**
     * Defines the class that will be used as the session adapter
     */
    private static function _defineAdapter() {
        // If the adapter is already defined, return
        if (isset(self::$_adapter)) {
            return;
        }

        // Get the session adapter class name from configuration
        $adapterClassName = App::getInstance()->getConf()->getSessionAdapter();
        if (!isset($adapterClassName)) {
            return;
        }

        // Instantiate the adapter
        $adapter = new $adapterClassName();
        if (!$adapter instanceof SessionAdapter) {
            throw new InvalidTypeException('The specified adapter is not valid: SessionAdapter expected, [%s] given instead', $adapter);
        }

        // Register the adapter as the new session handler
        session_set_save_handler($adapter, true);

        // Register the adapter for the current component
        self::$_adapter = $adapter;
        self::$_adapter->config();
        self::$_adapter->setup();
    }

    /**
     * Indicates whether the current session is started or not
     * 
     * @return boolean
     */
    public static function isStarted() {
        return self::$_isStarted;
    }

    /**
     * Starts a new session
     * 
     * Runs only if the session is not already started (or has been closed).
     * If no session adapter is configured, does nothing.
     */
    public static function start() {
        self::_initializeLogger();
        if (self::$_logger->isDebugEnabled()) {
            self::$_logger->debug('Session::start()');
        }
        if (!isset(self::$_adapter)) {
            self::_defineAdapter();
        }
        if (!isset(self::$_adapter)) {
            return;
        }
        if (!self::isStarted()) {
            session_start();
            self::$_isStarted = true;
        }
    }

    /**
     * Indicates whether the given variable named exists in session
     * 
     * If no session adapter is configured, throws an exception.
     * 
     * @param string $key the key of the value
     * @return boolean
     */
    public static function has($key) {
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::has() with no session adapter defined');
        }
        return isset($_SESSION[$key]);
    }

    /**
     * Retrieve the value stored in session with the given key
     * 
     * If no session adapter is configured, throws an exception.
     * 
     * @param string $key the key of the value
     * @return mixed
     */
    public static function get($key) {
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::get() with no session adapter defined');
        }
        if (!self::has($key)) {
            return null;
        }
        return $_SESSION[$key];
    }

    /**
     * Sets the given value in session with the given key
     * 
     * If no session adapter is configured, throws an exception.
     * 
     * @param string $key the key of the value
     * @param mixed $value the value that will be stored
     */
    public static function set($key, $value) {
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::set() with no session adapter defined');
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Deletes the value stored in session with the given key
     * 
     * If no session adapter is configured, throws an exception.
     * 
     * @param string $key the key of the stored value
     */
    public static function delete($key) {
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::delete() with no session adapter defined');
        }
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Resets the data stored in session
     * 
     * If no session adapter is configured, throws an exception.
     */
    public static function reset() {
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::reset() with no session adapter defined');
        }
        $_SESSION = array();
    }

    /**
     * Destroys the current session
     * 
     * If no session adapter is configured, throws an exception.
     */
    public static function destroy() {
        self::_initializeLogger();
        if (self::$_logger->isDebugEnabled()) {
            self::$_logger->debug('Session::destroy()');
        }
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::destroy() with no session adapter defined');
        }
        if (self::isStarted()) {
            session_destroy();
            self::$_isStarted = false;
        }
    }

    /**
     * Regenerates the current session's ID
     * 
     * If no session adapter is configured, throws an exception.
     */
    public static function regenerateId() {
        self::_initializeLogger();
        if (self::$_logger->isDebugEnabled()) {
            self::$_logger->debug('Session::regenerateId()');
        }
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::regenerateId() with no session adapter defined');
        }

        self::$_adapter->regenerateId();
    }

    /**
     * Closes the current session
     * 
     * If no session adapter is configured, throws an exception.
     * If the session is not started, does nothing.
     */
    public static function close() {
        self::_initializeLogger();
        if (self::$_logger->isDebugEnabled()) {
            self::$_logger->debug('Session::close()');
        }
        if (!isset(self::$_adapter)) {
            throw new SessionException('Incorrect call to Session::close() with no session adapter defined');
        }
        if (self::isStarted()) {
            session_write_close();
            self::$_isStarted = false;
        }
    }

}
