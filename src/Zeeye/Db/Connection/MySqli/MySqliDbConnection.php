<?php

namespace Zeeye\Db\Connection\MySqli;

use Zeeye\Db\Connection\DbConnection;
use Zeeye\Db\Query\SqlQuery;
use Zeeye\Db\Query\SelectQuery;
use Zeeye\Db\Query\UnionQuery;
use StimLog\Logger\Logger;
use Zeeye\Util\String\StringGenerator;
use Zeeye\Util\File\File;

/**
 * Class used for all MySQL connections (using the MySQLI API)
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqliDbConnection extends DbConnection {

    /**
     * The logger
     * 
     * @var Logger
     */
    private $_logger;

    /**
     * The current connection
     * 
     * @var \mysqli
     */
    private $_mysqli;

    /**
     * The transaction's level
     * 
     * @var integer
     */
    private $_transactionLevel;

    /**
     * The transaction level name
     * 
     * @var string
     */
    const TRANSACTION_LEVEL_NAME = 'LEVEL_';

    /**
     * Constructor
     * 
     * @param string $name the name of the connection
     * @param array $profile the profile information
     */
    protected function __construct($name, array $profile) {
        // Initializes the logger
        $this->_logger = Logger::create(__CLASS__);
        // Uses parent constructor
        parent::__construct($name, $profile);
        // The transaction's level is defined to zero
        $this->_transactionLevel = 0;
        // The mysqli object is null
        $this->_mysqli = null;
    }

    /**
     * Connect to the associated database
     */
    public function connect() {
        // If the connection is already established
        if ($this->isConnected()) {
            return;
        }

        // If the database name is specified for the connection
        if ($this->hasDbName()) {
            // Instantiate the mysqli object (try to connect to the server)
            $this->_mysqli = new \mysqli($this->getHost(), $this->getUser(), $this->getPassword(), $this->getDbName());
        } else {
            // Instantiate the mysqli object (try to connect to the server)
            $this->_mysqli = new \mysqli($this->getHost(), $this->getUser(), $this->getPassword());
        }
        // If the connection failed
        if (!empty($this->_mysqli->connect_error)) {
            throw new MySqliDbConnectionException('Connection to database failed : [mysqli_connect(' . $this->getHost() . ',' . $this->getUser() . ',' . $this->getPassword() . ',' . $this->getDbName() . ')] with error message : [' . $this->_mysqli->connect_error . ']');
        }
        // Try to assign the defined charset
        if (!$this->_mysqli->set_charset($this->getCharset())) {
            throw new MySqliDbConnectionException('An error occured while setting the connection charset to [' . $charset . ']');
        }

        // The database is now connected
        $this->_isConnected = true;
    }

    /**
     * Rename the parameters of the given SELECT query
     *
     * @param SelectQuery $select the SELECT query to modify
     */
    private static function _renameParameters(SelectQuery $select) {
        // The new parameters
        $newParameters = array();

        // The replacements to perform in the query string
        $replacements = array();

        // Generate a random base name for the new parameters names
        $randomKey = StringGenerator::generateRandomAlpha(6);

        // For each parameter
        foreach ($select->getParameters() as $name => $value) {
            // Define the new parameter name base to use
            $newParameterName = str_replace(SqlQuery::PARAMETER_KEY_NAME, $randomKey, $name);

            // Register the new parameter with the new name and current value
            $newParameters[$newParameterName] = $value;

            // Register the replacement to perform for the parameter name
            $replacements[$name] = $newParameterName;
        }

        // Update the parameters with the new ones
        $select->setParameters($newParameters);

        // Get the current conditions string
        $conditionsString = $select->getConditionsString();
        // Update the conditions string with the new one
        $select->setConditionsString(str_replace(array_keys($replacements), array_values($replacements), $conditionsString));
    }

    /**
     * Generate the parameters for the given query
     * 
     * @param SqlQuery $query the query to generate the parameters
     * @return array
     */
    private function _generateParametersFromQuery(SqlQuery $query) {
        // Get the parameters of the main query
        $parameters = $query->getParameters();

        // If the query is a SELECT query
        if ($query instanceof SelectQuery) {
            $from = $query->getFrom();
            // If the FROM part is a SELECT query
            if ($from instanceof SelectQuery) {
                self::_renameParameters($from);
                $parameters = array_merge($parameters, $from->getParameters());
            }
            // If the FROM part is a UNION query
            elseif ($from instanceof UnionQuery) {
                foreach ($from->getSelects() as $select) {
                    self::_renameParameters($select);
                    $parameters = array_merge($parameters, $select->getParameters());
                }
            }
        }

        return $parameters;
    }

    /**
     * Binds the parameters of the given query with their actual values
     * 
     * @param SqlQuery $query the query we want to bind the parameters
     * @return string the query string
     */
    private function _generateQueryString(SqlQuery $query) {
        // Get the list of parameters
        $parameters = $this->_generateParametersFromQuery($query);

        // Get the query string
        $queryString = $query->toString();

        // For each parameter
        foreach ($parameters as $key => $value) {
            if (!isset($value)) {
                $queryString = str_replace($key, 'NULL', $queryString);
            } else {
                $queryString = str_replace($key, "'" . $this->_mysqli->real_escape_string($value) . "'", $queryString);
            }
        }

        return $queryString;
    }

    /**
     * Run the given query string on the database
     * 
     * @param string $queryString the SQL query to execute
     * @return mysqli_result the MySQLi result of the query
     */
    private function _runQueryString($queryString) {
        // Make sure the database is connected
        $this->connect();

        // Execute the query
        $result = $this->_mysqli->query($queryString);

        // If the query failed
        if ($result === false) {
            throw new MySqliDbConnectionException('SQL query [' . $queryString . '] failed with message [' . $this->_mysqli->error . ']');
        }

        // Logs the query as an info
        if ($this->_logger->isInfoEnabled()) {
            $this->_logger->info('SQL query succeed [' . $queryString . ']');
        }

        return $result;
    }

    /**
     * Executes the given query of type INSERT, UPDATE or DELETE and returns number of affected rows
     * 
     * @param SqlQuery|string $query the query to execute
     * @return int number of affected rows
     */
    public function executeQuery($query) {
        // Make sure the database is connected
        $this->connect();

        // The query string is considered given
        $queryString = $query;

        // If the given query is of type SqlQuery
        if ($query instanceof SqlQuery) {
            // Gets the actual query string from the query object
            $queryString = $this->_generateQueryString($query);
        }

        // Execute the query string
        $this->_runQueryString($queryString);

        // Return the number of affected rows
        return $this->_mysqli->affected_rows;
    }

    /**
     * Executes the given batch query string on the database
     *
     * @param string $queryString the query string to execute
     */
    public function executeBatchQueryString($queryString) {
        // Make sure the database is connected
        $this->connect();

        // Execute the query
        $this->_mysqli->multi_query($queryString);

        //The returned results sets
        $results = array();

        // Loop through the results
        while ($this->_mysqli->more_results()) {
            $isSuccess = $this->_mysqli->next_result();
            // If the query failed
            if (!$isSuccess) {
                throw new MySqliDbConnectionException('Batch SQL query [' . $queryString . '] failed with message [' . $this->_mysqli->error . ']');
            }
        }

        // Logs the query as an info
        if ($this->_logger->isInfoEnabled()) {
            $this->_logger->info('Batch SQL query succeed [' . $queryString . ']');
        }
    }

    /**
     * Executes the given batch file on the database
     *
     * TODO faire du ligne par ligne avec fopen ?
     * @param string $filePath the file to execute
     */
    public function executeBatchFile($filePath) {
        // Get the content of the given file
        $string = File::read($filePath);
        // Remove all comments made with --
        $string = preg_replace('#([^-]*)--.*#', '$1', $string);
        // Remove all comments made with /* and */
        $string = preg_replace('#(.*)(/\*.*\*/)(.*)#', '$1$3', $string);

        $this->executeBatchQueryString($string);
    }

    /**
     * Executes the given query of type SELECT and returns a list of results
     * 
     * @param SelectQuery|string $query the query to execute
     * @return array list of results
     */
    public function selectQuery($query) {
        // Make sure the database is connected
        $this->connect();

        // The query string is considered given
        $queryString = $query;

        // If the given query is of type SelectQuery
        if ($query instanceof SelectQuery) {
            // Gets the actual query string from the query object
            $queryString = $this->_generateQueryString($query);
        }

        // Execute the query string
        $result = $this->_runQueryString($queryString);

        // Build a list of rows and return it
        return $this->_fetchRows($result);
    }

    /**
     * Get the list of tables in the underlying database
     *
     * @return array
     */
    public function listTables() {
        $tables = $this->selectQuery('SHOW TABLES IN ' . $this->getDbName());
        foreach ($tables as $key => $value) {
            $tables[$key] = array_values($value);
        }
        return $tables;
    }

    /**
     * Build a list of rows from the given MySQLi result
     * 
     * @param mysqli_result $result the result of the executed query
     * @return array list of results
     */
    private function _fetchRows($result) {
        // Builds an array of results objects
        $results = array();
        while ($row = $result->fetch_assoc()) {
            if (isset($row)) {
                $results[] = $row;
            }
        }

        // Free the memory associated with the result
        $result->free();

        // Returns the array
        return $results;
    }

    /**
     * Indicates whether the MySQLi driver is available or not
     * 
     * @return boolean
     */
    public function isDriverAvailable() {
        return function_exists('\mysqli_connect');
    }

    /**
     * Returns the type name of the current connection
     * 
     * @return string
     */
    public function getType() {
        return 'MySql';
    }

    /**
     * Validates a current transaction for the connection
     */
    public function commit() {
        // Decrease the transaction's level
        $this->_transactionLevel--;

        // If the transaction's level is equal to zero
        if ($this->_transactionLevel == 0) {
            // Use transaction
            $this->_runQueryString('COMMIT');
            $this->_runQueryString('SET AUTOCOMMIT = 1');
        } else {
            // Use savepoint
            $savePointName = self::TRANSACTION_LEVEL_NAME . $this->_transactionLevel;
            $this->_runQueryString('RELEASE SAVEPOINT ' . $savePointName);
        }
    }

    /**
     * Cancels a current transaction for the connection
     */
    public function rollback() {
        // Decrease the transaction's level
        $this->_transactionLevel--;

        // If the transaction's level is equal to zero
        if ($this->_transactionLevel == 0) {
            // Use transaction
            $this->_runQueryString('ROLLBACK');
            $this->_runQueryString('SET AUTOCOMMIT = 1');
        } else {
            // Use savepoint
            $savePointName = self::TRANSACTION_LEVEL_NAME . $this->_transactionLevel;
            $this->_runQueryString('ROLLBACK TO SAVEPOINT ' . $savePointName);
        }
    }

    /**
     * Starts a new transaction for the connection
     */
    public function beginTransaction() {
        // If the transaction's level is equal to zero
        if ($this->_transactionLevel == 0) {
            // Use transaction
            $this->_runQueryString('SET AUTOCOMMIT = 0');
            $this->_runQueryString('START TRANSACTION');
        } else {
            // Use savepoint
            $savePointName = self::TRANSACTION_LEVEL_NAME . $this->_transactionLevel;
            $this->_runQueryString('SAVEPOINT ' . $savePointName);
        }

        // Increase the transaction's level
        $this->_transactionLevel++;
    }

    /**
     * Returns the last id value inserted in the database
     * 
     * @param string $sequenceName the name of the sequence to use
     * @return integer
     */
    public function getLastInsertId($sequenceName = null) {
        // Make sure the database is connected
        $this->connect();

        return $this->_mysqli->insert_id;
    }

}
