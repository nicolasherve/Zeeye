<?php

namespace Zeeye\Db\Connection;

use Zeeye\App\App;
use Zeeye\Db\Connection\MySqli\MySqliDbConnection;
use Zeeye\Db\Query\SelectQuery;
use Zeeye\Db\Query\SqlQuery;

/**
 * Abstract class used for all common operations about database connections
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class DbConnection {

    /**
     * List of the database profiles specified in the configuration file
     * 
     * @var array
     */
    private static $_dbProfiles = null;

    /**
     * List of the current instanced database connections
     * 
     * @var array
     */
    private static $_connections = array();

    /**
     * Indicates if the connection is performed
     *
     * @var boolean
     */
    protected $_isConnected;

    /**
     * The host of the database server
     * 
     * @var string
     */
    private $_host;

    /**
     * The name of the connection
     * 
     * @var string
     */
    private $_name;

    /**
     * The name of the database
     * 
     * @var string
     */
    private $_dbName;

    /**
     * The user name used to connect to the database server
     * 
     * @var string
     */
    private $_user;

    /**
     * The password used to connect to the database server
     * 
     * @var string
     */
    private $_password;

    /**
     * Indicates the charset to use for the connection
     * 
     * @var string
     */
    private $_charset;

    /**
     * Constructor
     * 
     * @param string $name the name of the connection
     * @param array $profile the connection profile, used to connect to the database
     */
    protected function __construct($name, array $profile) {
        // The name of the connection
        $this->_name = $name;

        // Required properties
        if (!isset($profile['host']) || empty($profile['host'])) {
            throw new DbException('The host must be defined for the [' . $profile['name'] . '] database profile');
        }
        if (!isset($profile['user']) || empty($profile['user'])) {
            throw new DbException('The user must be defined for the [' . $profile['name'] . '] database profile');
        }
        if (!isset($profile['password'])) {
            throw new DbException('The password must be defined for the [' . $profile['name'] . '] database profile');
        }
        if (!isset($profile['charset'])) {
            throw new DbException('The charset must be defined for the [' . $profile['name'] . '] database profile');
        }
        $this->_host = $profile['host'];
        $this->_dbName = isset($profile['name']) ? $profile['name'] : null;
        $this->_user = $profile['user'];
        $this->_password = $profile['password'];
        $this->_charset = $profile['charset'];

        // The connection is not done
        $this->_isConnected = false;
    }

    /**
     * Indicates if the connection is done
     * 
     * @return boolean
     */
    public function isConnected() {
        return $this->_isConnected;
    }

    /**
     * Gets the name of the connection
     * 
     * @return string
     */
    final public function getName() {
        return $this->_name;
    }

    /**
     * Gets the name of the host of the database server
     * 
     * @return string
     */
    final public function getHost() {
        return $this->_host;
    }

    /**
     * Gets the name of the database
     * 
     * @return string
     */
    final public function getDbName() {
        return $this->_dbName;
    }

    /**
     * Indicates if the name of the database is specified for the connection
     *
     * @return boolean
     */
    final public function hasDbName() {
        return isset($this->_dbName);
    }

    /**
     * Gets the name of the database user
     * 
     * @return string
     */
    final public function getUser() {
        return $this->_user;
    }

    /**
     * Gets the password of the database user
     * 
     * @return string
     */
    final public function getPassword() {
        return $this->_password;
    }

    /**
     * Gets the charset used for the connection
     * 
     * @return string
     */
    final public function getCharset() {
        return $this->_charset;
    }

    /**
     * Connect to the associated database
     */
    abstract public function connect();

    /**
     * Executes the given batch query string on the database
     *
     * @param string $queryString the query string to execute
     */
    abstract public function executeBatchQueryString($queryString);

    /**
     * Executes the given batch file on the database
     *
     * @param string $filePath the file to execute
     */
    abstract public function executeBatchFile($filePath);

    /**
     * Executes the given query of type SELECT and returns a list of results
     * 
     * @param SelectQuery|string $query the query to execute
     * @return array list of results
     */
    abstract public function selectQuery($query);

    /**
     * Executes the given query of type INSERT, UPDATE or DELETE and returns the number of affected rows
     * 
     * @param SqlQuery|string $query the query to execute
     * @return int number of affected rows
     */
    abstract public function executeQuery($query);

    /**
     * Get the list of tables in the underlying database
     *
     * @return array
     */
    abstract public function listTables();

    /**
     * Returns the last id value inserted in the database
     * 
     * @param string $sequenceName the name of the sequence to use
     * @return integer
     */
    abstract public function getLastInsertId($sequenceName = null);

    /**
     * Validates a current transaction for the connection
     */
    abstract public function commit();

    /**
     * Cancels a current transaction for the connection
     */
    abstract public function rollback();

    /**
     * Starts a new transaction for the connection
     */
    abstract public function beginTransaction();

    /**
     * Indicates whether the driver is available or not
     * 
     * @return boolean
     */
    abstract public function isDriverAvailable();

    /**
     * Returns the type name of the current connection
     * 
     * @return string
     */
    abstract public function getType();

    /**
     * Returns the database connection corresponding to the given database profile name
     * 
     * @param string $profileName name of the profile
     * @return DbConnection
     */
    public static function getInstance($profileName = null) {
        // We make sure the database profiles from configuration are loaded
        if (!isset(self::$_dbProfiles)) {
            self::$_dbProfiles = App::getInstance()->getDbConf()->getDbProfiles();
        }

        // If the connection corresponding to the given profile name does not exist
        if (!isset(self::$_connections[$profileName])) {

            // If the profile name is not set
            if (!isset($profileName)) {
                $profileName = App::getInstance()->getDbConf()->getDefaultProfileName();
            }

            // A name is given, try to retrieve the profile of the name
            $profile = self::getProfile($profileName);

            // Depending on the profile driver, try to instantiate a DbConnection object
            switch ($profile['driver']) {
                case 'mysqli':
                    self::$_connections[$profileName] = new MySqliDbConnection($profileName, $profile);
                    break;
                default:
                    throw new DbConnectionException('The specified driver [' . $profile['driver'] . '] is not correct for the db profile [' . $profileName . ']');
            }
        }
        // Returns the stored DbConnection
        return self::$_connections[$profileName];
    }

    /**
     * Returns the profile information corresponding to the given profile name
     * 
     * @param string $name name of the profile
     * @return array
     */
    public static function getProfile($name) {
        if (!isset(self::$_dbProfiles[$name])) {
            throw new DbConnectionException('The database profile named [' . $name . '] cannot be found');
        }
        return self::$_dbProfiles[$name];
    }

}
