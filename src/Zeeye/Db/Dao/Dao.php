<?php

namespace Zeeye\Db\Dao;

use Zeeye\App\App;
use Zeeye\Db\Connection\DbConnection;
use Zeeye\Db\Connection\DbConnectionException;
use Zeeye\Db\Dao\CachedDao;
use Zeeye\Db\Query\DeleteQuery;
use Zeeye\Db\Query\InsertQuery;
use Zeeye\Db\Query\SelectQuery;
use Zeeye\Db\Query\SqlQuery;
use Zeeye\Db\Query\UnionQuery;
use Zeeye\Db\Query\UpdateQuery;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Abstract class representing a mapping process to access data from databases for one table
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Dao {

    /**
     * The default name of the primary key
     * 
     * @var string
     */
    const DEFAULT_PK_NAME = 'id';

    /**
     * Connection to the database containing the table represented by the DAO
     * 
     * @var DbConnection
     */
    protected $_connection;

    /**
     * Name of the table represented by the DAO
     * 
     * @var string
     */
    protected $_tableName;

    /**
     * Name of the primary key of the table
     * 
     * @var string
     */
    protected $_primaryKeyName;

    /**
     * List of the methods that should be cached
     * 
     * @var array|null
     */
    protected $_cachedMethods = null;

    /**
     * A list of unique instances from the requested Daos
     * 
     * @var array
     */
    private static $_instances = array();

    /**
     * Returns the underlying database connection
     * 
     * @return DbConnection the underlying database connection
     */
    public function getConnection() {
        return $this->_connection;
    }

    /**
     * Set the underlying database connection
     *  
     * @param DbConnection $connection the database connection
     */
    public function setConnection(DbConnection $connection) {
        return $this->_connection = $connection;
    }

    /**
     * Get the name of the table used for the Dao
     * 
     * @return string
     */
    public function getTableName() {
        return $this->_tableName;
    }

    /**
     * Get the name of the primary key
     * 
     * @return string
     */
    public function getPrimaryKeyName() {
        return $this->_primaryKeyName;
    }

    /**
     * Instantiates and returns a new SELECT query object
     * 
     * @return SelectQuery
     */
    protected function _createSelectQuery() {
        return SelectQuery::create($this->_connection);
    }

    /**
     * Instantiates and returns a new UNION query object
     *
     * @return UnionQuery
     */
    protected function _createUnionQuery() {
        return UnionQuery::create($this->_connection);
    }

    /**
     * Instantiates and returns a new INSERT query object
     * 
     * @return InsertQuery
     */
    protected function _createInsertQuery() {
        return InsertQuery::create($this->_tableName, $this->_connection);
    }

    /**
     * Instantiates and returns a new UPDATE query object
     * 
     * @return UpdateQuery
     */
    protected function _createUpdateQuery() {
        return UpdateQuery::create($this->_tableName, $this->_connection);
    }

    /**
     * Instantiates and returns a new DELETE query object
     * 
     * @return DeleteQuery
     */
    protected function _createDeleteQuery() {
        return DeleteQuery::create($this->_tableName, $this->_connection);
    }

    protected function _getCachedMethods() {
        return array();
    }

    protected function _hasCachedMethods() {
        if (!isset($this->_cachedMethods)) {
            $this->_cachedMethods = array_fill_keys($this->_getCachedMethods(), $this->_getCachedMethods());
        }
        return !empty($this->_cachedMethods);
    }

    protected function _hasCachedMethod($methodName) {
        return isset($this->_cachedMethods[$methodName]);
    }

    private function __construct() {
        
    }

    abstract public function setup();

    /**
     * Retrieves the row corresponding to the given id in the table of the DAO
     * 
     * @param integer $id id of the row to get
     * @return array|null the requested row
     */
    public function get($id) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('*');
        $select->from($this->_tableName);
        $select->addConditionEquals($this->_primaryKeyName, $id);

        // Executes the query and retrieve the results
        return $this->_selectFirst($select);
    }

    /**
     * Retrieves the number of rows in the table of the DAO
     *
     * @return int the number of rows
     */
    public function count() {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('COUNT(*)', null, 'total');
        $select->from($this->_tableName);

        // Executes the query and retrieve the results
        $result = $this->_selectFirst($select);

        // If there is a result with the expected column, return it
        if (isset($result['total'])) {
            return $result['total'];
        }

        return 0;
    }

    /**
     * Retrieves the minimum value of the given field for the current table
     *
     * If the table contains no data, returns null.
     *
     * @return null|int the retrieved value
     */
    public function min($fieldName) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('MIN(' . $fieldName . ')', null, 'min');
        $select->from($this->_tableName);

        // Executes the query and retrieve the results
        $result = $this->_selectFirst($select);

        // If there is a result with the expected column, return it
        if (isset($result['min'])) {
            return $result['min'];
        }

        return null;
    }

    /**
     * Retrieves the maximum value of the given field for the current table
     *
     * If the table contains no data, returns null.
     *
     * @return null|int the retrieved value
     */
    public function max($fieldName) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('MAX(' . $fieldName . ')', null, 'max');
        $select->from($this->_tableName);

        // Executes the query and retrieve the results
        $result = $this->_selectFirst($select);

        // If there is a result with the expected column, return it
        if (isset($result['max'])) {
            return $result['max'];
        }

        return null;
    }

    /**
     * Retrieves the sum value of the given field for the current table
     *
     * If the table contains no data, returns null.
     *
     * @return null|int the retrieved value
     */
    public function sum($fieldName) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('SUM(' . $fieldName . ')', null, 'sum');
        $select->from($this->_tableName);

        // Executes the query and retrieve the results
        $result = $this->_selectFirst($select);

        // If there is a result with the expected column, return it
        if (isset($result['sum'])) {
            return $result['sum'];
        }

        return null;
    }

    /**
     * Retrieves the average value of the given field for the current table
     *
     * If the table contains no data, returns null.
     *
     * @return null|int the retrieved value
     */
    public function avg($fieldName) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('AVG(' . $fieldName . ')', null, 'avg');
        $select->from($this->_tableName);

        // Executes the query and retrieve the results
        $result = $this->_selectFirst($select);

        // If there is a result with the expected column, return it
        if (isset($result['avg'])) {
            return $result['avg'];
        }

        return null;
    }

    /**
     * Retrieves the rows of the table
     *
     * @param integer $limit limit to use when retrieving the data
     * @param integer $offset offset to use when retrieving the data
     * @return array the requested rows
     */
    public function find($limit = null, $offset = null) {
        // Builds the query
        $select = $this->_createSelectQuery();
        $select->field('*');
        $select->from($this->_tableName);
        $select->order($this->_primaryKeyName);
        if (isset($offset)) {
            $select->offset($offset);
        }
        if (isset($limit)) {
            $select->limit($limit);
        }

        // Executes the query and retrieve the results
        return $this->_selectAll($select);
    }

    /**
     * Defines processes that take place before the insert query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param InsertQuery $query the query
     */
    protected function _beforeInsert(InsertQuery $query) {
        
    }

    /**
     * Defines processes that take place after the insert query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param InsertQuery $query the query
     * @param integer $nbAffectedRows the number of affected rows
     */
    protected function _afterInsert(InsertQuery $query, $nbAffectedRows) {
        
    }

    /**
     * Inserts the given fields in the table of the DAO
     * 
     * @param array $row list of the fields to insert with their values
     * @return integer number of affected rows
     */
    public function insert(array &$row) {
        // Builds the query
        $insert = $this->_createInsertQuery();
        // For each field of the row to insert
        foreach ($row as $fieldName => $value) {
            // The primary key value cannot be inserted
            if ($fieldName == $this->_primaryKeyName && !isset($row[$fieldName])) {
                continue;
            }

            // Add the field and its value to the query
            $insert->field($fieldName, $value);
        }

        try {
            // Executes the query
            $nbAffectedRows = $this->_execute($insert);
            // Adds (or replace) the value of the primary key to the given row
            $row[$this->_primaryKeyName] = $this->_getLastInsertId();
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::insert() operation failed due to a DbConnection exception', 0, $e);
        }

        return $nbAffectedRows;
    }

    /**
     * Defines processes that take place before the update query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param UpdateQuery $query the query
     */
    protected function _beforeUpdate(UpdateQuery $query) {
        
    }

    /**
     * Defines processes that take place after the update query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param UpdateQuery $query the query
     * @param integer $nbAffectedRows the number of affected rows
     */
    protected function _afterUpdate(UpdateQuery $query, $nbAffectedRows) {
        
    }

    /**
     * Updates the given fields in the table of the DAO
     * 
     * @param array $row list of the fields to update with their values
     * @return integer number of affected rows
     */
    public function update(array $row) {
        // The id field is required for an update
        if (!isset($row[$this->_primaryKeyName])) {
            throw new DaoException('The primary key [' . $this->_primaryKeyName . '] field is missing');
        }

        // Builds the query
        $update = $this->_createUpdateQuery();
        // For each field of the row to update
        foreach ($row as $fieldName => $value) {

            // The primary key value cannot be updated
            if ($fieldName == $this->_primaryKeyName) {
                continue;
            }
            $update->field($fieldName, $value);
        }
        $update->addConditionEquals($this->_primaryKeyName, $row[$this->_primaryKeyName]);

        try {
            // Executes the query
            return $this->_execute($update);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::update() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Defines processes that take place before the delete query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param DeleteQuery $query the query
     */
    protected function _beforeDelete(DeleteQuery $query) {
        
    }

    /**
     * Defines processes that take place after the delete query is processed
     * 
     * This method can be redefined in the sub classes.
     * 
     * @param DeleteQuery $query the query
     * @param integer $nbAffectedRows the number of affected rows
     */
    protected function _afterDelete(DeleteQuery $query, $nbAffectedRows) {
        
    }

    /**
     * Deletes the row corresponding to the given id in the table of the DAO
     * 
     * @param integer $id id of the row to delete
     * @return integer number of affected rows
     */
    public function delete($id) {
        // Builds the query
        $delete = $this->_createDeleteQuery();
        $delete->addConditionEquals($this->_primaryKeyName, $id);

        try {
            // Executes the query
            return $this->_execute($delete);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::delete() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Describe the table by returning information about its columns
     * 
     * @return array
     */
    public function describe() {
        try {
            return $this->_selectAll('DESCRIBE ' . $this->_tableName);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::describe() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Empty the table
     */
    public function purge() {
        try {
            // Builds the query
            $delete = $this->_createDeleteQuery();

            // Executes the query
            $this->_execute($delete);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::purge() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Executes the given query of type SELECT and returns a list of results
     * 
     * @param SelectQuery|string $query the query to execute
     * @return array list of rows
     */
    protected function _selectAll($query) {
        try {
            // Executes the SELECT query
            return $this->_connection->selectQuery($query);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_selectAll() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Executes the given query of type SELECT and returns a result row
     *
     * @param SelectQuery|string $query the query to execute
     * @return null|array the row
     */
    protected function _selectFirst($query) {
        try {
            // Executes the SELECT query
            $results = $this->_connection->selectQuery($query);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_selectFirst() operation failed due to a DbConnection exception', 0, $e);
        }

        if (!isset($results[0])) {
            return null;
        }

        return $results[0];
    }

    /**
     * Executes the given query of type INSERT, UPDATE or DELETE and returns number of affected rows
     * 
     * @param SqlQuery|string $query the query to execute
     * @return int number of affected rows
     */
    protected function _execute($query) {
        // Callback before runnning the query
        if ($query instanceof InsertQuery) {
            $this->_beforeInsert($query);
        } elseif ($query instanceof UpdateQuery) {
            $this->_beforeUpdate($query);
        } elseif ($query instanceof DeleteQuery) {
            $this->_beforeDelete($query);
        }

        try {
            // Executes the query
            $nbAffectedRows = $this->_connection->executeQuery($query);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_execute() operation failed due to a DbConnection exception', 0, $e);
        }

        // Callback after runnning the query
        if ($query instanceof InsertQuery) {
            $this->_afterInsert($query, $nbAffectedRows);
        } elseif ($query instanceof UpdateQuery) {
            $this->_afterUpdate($query, $nbAffectedRows);
        } elseif ($query instanceof DeleteQuery) {
            $this->_afterDelete($query, $nbAffectedRows);
        }

        return $nbAffectedRows;
    }

    /**
     * Returns the last id value inserted in the database
     * 
     * @param string $sequenceName the name of the sequence to use
     * @return integer
     */
    protected function _getLastInsertId($sequenceName = null) {
        try {
            return $this->_connection->getLastInsertId($sequenceName);
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_getLastInsertId() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Validates a current transaction for the connection
     */
    protected function _commit() {
        try {
            $this->_connection->commit();
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_commit() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Cancels a current transaction for the connection
     */
    protected function _rollback() {
        try {
            $this->_connection->rollback();
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_rollback() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Starts a new transaction for the connection
     */
    protected function _beginTransaction() {
        try {
            $this->_connection->beginTransaction();
        } catch (DbConnectionException $e) {
            // Throws an exception
            throw new DaoException('The Dao::_beginTransaction() operation failed due to a DbConnection exception', 0, $e);
        }
    }

    /**
     * Returns an instance of the requested Dao
     * 
     * If an instance of the requested Dao already exists, the method returns it (singleton).
     * 
     * @param string $name name of the requested Dao, as defined in the configuration file
     * @return Dao an instance of the requested Dao
     */
    public static function getInstance($name) {

        // If the requested Dao is already registered, returns the existing instance
        if (isset(self::$_instances[$name])) {
            return self::$_instances[$name];
        }

        // Get the requested dao config
        $daoConfig = App::getInstance()->getAppConfiguration()->getDao($name);

        // Get the dao class name
        $daoClassName = $daoConfig['class'];

        // Instantiates the given dao
        $dao = new $daoClassName();

        // If the dao does not extend the Dao class, throw an exception
        if (!$dao instanceof Dao) {
            throw new InvalidTypeException('The dao named [' . $name . '] is not valid: Dao expected, [%s] given instead', $dao);
        }

        // Setup
        $dao->setup();

        // If the dao has no corresponding table name, throw an exception
        if (empty($dao->_tableName)) {
            throw new DaoException('The dao class [' . $daoClassName . '] has been created without a table name.');
        }

        // If the dao has no corresponding primary key name, sets the default one
        if (empty($dao->_primaryKeyName)) {
            $dao->_primaryKeyName = self::DEFAULT_PK_NAME;
        }

        // Get the dao connection name (if any)
        $daoConnectionName = isset($daoConfig['connection']) ? $daoConfig['connection'] : null;

        // Sets the appropriate connection
        $dao->setConnection(DbConnection::getInstance($daoConnectionName));

        // Gestion du cache
        if (isset($daoConfig['cache']) && $dao->_hasCachedMethods()) {
            $dao = CachedDao::create($dao, $daoConfig['cache']);
        }

        // Store the Dao in the list of instances
        self::$_instances[$name] = $dao;

        // Returns the Dao
        return $dao;
    }

}
