<?php

namespace Zeeye\Db\Connection\MySqli;

use PDO;
use PDOStatement;
use StimLog\Logger\Logger;
use Zeeye\Db\Connection\Pdo\PdoDbConnection;
use Zeeye\Db\Query\SelectQuery;
use Zeeye\Db\Query\SqlQuery;

/**
 * Class used for PDO MySQL connections
 * TODO deplacer la plupart des methodes dans la classe parent
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class PdoMySqlDbConnection extends PdoDbConnection {

    /**
     * The logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Constructor
     * 
     * @param array $profile the profile information
     */
    public function __construct(array $profile) {
        // Initializes the logger
        $this->_logger = Logger::create(__CLASS__);
        // Set up the options
        $this->addOption(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES ' . $this->getCharset());
        // Uses parent constructor
        parent::__construct($profile);
    }

    /**
     * Executes the given query and returns the result
     * 
     * @param SqlQuery $query the query to execute
     * @return PDOStatement the statement of the query
     */
    public function query(SqlQuery $query) {
        // TODO control errors
        $queryString = $query->toString();
        // TODO control errors
        $statement = $this->_pdo->prepare($queryString);

        // Try to execute the query
        if (!$statement->execute($query->getParameters())) {
            $errorInfos = $statement->errorInfo();
            throw new PdoMysqlDbConnectionException('SQL query [' . $queryString . '] failed with message [' . $errorInfos[2] . ']');
        } else {
            // Logs the query as an info
            if ($this->logger->isInfoEnabled()) {
                $this->logger->info('SQL query succeed [' . $queryString . ']');
            }
        }
        // If the query is a SELECT, returns the statement
        if ($query instanceof SelectQuery) {
            return $statement;
        }
        // Since the query is not a SELECT, returns the number of affected rows
        return $statement->rowCount();
    }

    /**
     * Executes the given query and returns a list of results
     * 
     * @param SelectQuery $query the query to execute
     * @return array list of results
     */
    public function fetchRows(SelectQuery $query) {
        // Executes the query and gets the statement
        $statement = $this->query($query);
        // Builds an array of results objects
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        // Close the cursor associated with the result
        $statement->closeCursor();
        // Returns the array
        return $results;
    }

    /**
     * Executes the given query and returns the first row as a result
     * 
     * @param SelectQuery $query the query to execute
     * @return array the first row of the results
     */
    public function fetchRow(SelectQuery $query) {
        // Executes the query and gets the statement
        $statement = $this->query($query);
        // Builds an array of results objects
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Indicates whether the PDO MySQL driver is available or not
     * 
     * @return boolean
     */
    public function isDriverAvailable() {
        if (!class_exists('\PDO')) {
            return false;
        }
        return in_array('mysql', PDO::getAvailableDrivers());
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
        $this->_pdo->commit();
    }

    /**
     * Cancels a current transaction for the connection
     */
    public function rollback() {
        $this->_pdo->rollback();
    }

    /**
     * Starts a new transaction for the connection
     */
    public function beginTransaction() {
        $this->_pdo->beginTransaction();
    }

    /**
     * Returns the last id value inserted in the database
     * 
     * @param string $sequenceName the name of the sequence to use
     * @return integer
     */
    public function getLastInsertId($sequenceName = null) {
        return $this->_pdo->lastInsertId();
    }

}
