<?php

namespace Zeeye\Util\Session\Adapter;

use StimLog\Logger\Logger;
use Zeeye\Util\Date\Date;
use Zeeye\Util\Session\SessionAdapter;
use Zeeye\Util\Session\SessionCookie;

/**
 * Session adapter to manage session via a database
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class DbSession extends SessionAdapter {

    /**
     * The logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * The session Dao
     * 
     * @var ISessionDao
     */
    private static $_dao = null;

    /**
     * The session data when the session is started
     *  
     * @var string
     */
    private static $_dataAtSessionStart = '';

    /**
     * Configures the session adapter
     */
    public function config() {
        // Parent config
        parent::config();

        // Initializes the logger
        $this->_logger = Logger::create(__CLASS__);

        // Sets the lifetime
        $this->setLifeTime(1440);

        // Sets the session cookie
        $this->setSessionCookie(new SessionCookie());
    }

    /**
     * Default configuration for the session
     */
    public function setup() {
        // Parent setup
        parent::setup();

        // If there is no provided Dao
        if (!isset(self::$_dao)) {
            throw new DbSessionException("The ISessionDao property is required");
        }
    }

    public function getSessionDao() {
        return self::$_dao;
    }

    public function setSessionDao(ISessionDao $sessionDao) {
        self::$_dao = $sessionDao;
    }

    public function open($savePath, $name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('DbSession::read()');
        }

        // Retrieve the data from database
        self::$_dataAtSessionStart = self::$_dao->getDataBySessionId($sessionId);

        // If there is some data from database
        if (isset(self::$_dataAtSessionStart)) {
            // Access date
            $accessDate = Date::create();

            // Update expiration date from database
            self::$_dao->updateAccessDateBySessionId($accessDate, $sessionId);

            return (string) self::$_dataAtSessionStart;
        }

        return '';
    }

    public function write($id, $data) {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('DbSession::write()');
        }

        // If some data was changed
        if ($data != self::$_dataAtSessionStart) {

            // Access date
            $accessDate = Date::create();

            // If there was no session
            if (self::$_dataAtSessionStart === null) {
                // Insert the new session in the database
                self::$_dao->insertSession($id, $data, $accessDate);
            } else {
                // Update data and expiration date from database
                self::$_dao->updateDataAndAccessDateBySessionId($data, $accessDate, $id);
            }
        }

        return true;
    }

    public function destroy($id) {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('DbSession::destroy()');
        }

        // Parent destroy
        parent::destroy($id);

        // Update Database
        self::$_dao->deleteBySessionId($id);

        return true;
    }

    public function gc($lifetime) {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('DbSession::gc()');
        }

        // Expiration date
        $expirationDate = Date::create();
        $expirationDate->remove($lifetime, Date::SECOND_UNIT);

        // Delete from database all data with past expiration date
        self::$_dao->deleteByAccessDateBefore($expirationDate);

        return true;
    }

    public function regenerateId() {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('DbSession::regenerateId()');
        }

        // Get the current session id
        $oldSessionId = session_id();

        // Destroy the cookie because it still contains the old session id
        parent::destroySessionCookie();

        // TODO Set the new session cookie
        // Regenerate the session id
        session_regenerate_id();

        // Get the new session id
        $newSessionId = session_id();

        // Update the session id from database
        self::$_dao->updateSessionIdBySessionId($oldSessionId, $newSessionId);
    }

    public static function getDataAtSessionStart() {
        return self::$_dataAtSessionStart;
    }

}
