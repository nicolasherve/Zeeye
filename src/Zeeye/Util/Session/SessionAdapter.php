<?php

namespace Zeeye\Util\Session;

use Zeeye\Util\Cookie\Cookie;
use StimLog\Logger\Logger;

/**
 * Abstract class used for session adapters
 * 
 * The class relies on a session cookie
 * 
 * See http://www.php.net/manual/en/function.session-set-save-handler.php
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class SessionAdapter implements \SessionHandlerInterface, WithSessionCookie {

    /**
     * The logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * The maximum life time (in seconds) an unused PHP session will be kept alive
     * 
     * @var int
     */
    private static $_lifeTime;

    /**
     * The session cookie
     *
     * @var SessionCookie
     */
    private static $_cookie;

    /**
     * Configures the session adapter
     */
    public function config() {
        // Initializes the logger
        $this->_logger = Logger::create(__CLASS__);

        // Sets the lifetime
        $this->setLifeTime(1440);

        // Sets the session cookie
        $this->setSessionCookie(new SessionCookie());
    }

    /**
     * Setups the session
     */
    public function setup() {
        // If there is no provided life time
        if (!isset(self::$_lifeTime)) {
            throw new SessionAdapterException("The lifeTime property has to be set");
        }

        // If there is no provided session cookie
        if (!isset(self::$_cookie)) {
            throw new SessionAdapterException("The cookie property has to be set");
        }

        // The session id will be stored only in a cookie (not transmitted in URL)
        ini_set('session.use_only_cookies', '1');

        // The maximum lifetime (in seconds) an unused PHP session will be kept alive (timeout, server side)
        ini_set('session.gc_maxlifetime', self::$_lifeTime);

        // Activates the session cookie parameters
        SessionCookie::useSessionCookie(self::$_cookie);
    }

    public function destroy($id) {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('SessionAdapter::destroy()');
        }

        // Delete the session cookie
        $this->destroySessionCookie();

        return true;
    }

    /**
     * Regenerates the current session's ID
     */
    abstract public function regenerateId();

    public function getLifeTime() {
        return self::$_lifeTime;
    }

    public function setLifeTime($lifeTime) {
        self::$_lifeTime = $lifeTime;
    }

    public function getSessionCookie() {
        return self::$_cookie;
    }

    public function setSessionCookie(SessionCookie $cookie) {
        self::$_cookie = $cookie;
    }

    public function destroySessionCookie() {

        $cookieParams = session_get_cookie_params();

        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('SessionAdapter::destroySessionCookie()');
            $this->_logger->debug(session_name() . ', ' . $cookieParams['path'] . ', ' . $cookieParams['domain'] . ', ' . $cookieParams['secure'] . ', ' . $cookieParams['httponly']);
        }

        Cookie::delete(session_name(), $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    }

}
