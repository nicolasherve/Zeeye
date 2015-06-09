<?php
namespace Zeeye\Db\Query;

use Zeeye\Db\Connection\DbConnection;
/**
 * Abstract class containing all common operations for INSERT SQL queries
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class InsertQuery extends SqlQuery {
	/**
	 * List of the fields to insert
	 * 
	 * @var array
	 */
	private $_fields;
	
	/**
	 * The name of the target table
	 * 
	 * @var string
	 */
	private $_tableName;
	
	/**
	 * Constructor
	 * 
	 * @param string $tableName name of the table in which we insert
	 */
	protected function __construct($tableName) {
		parent::__construct();
		$this->_tableName = $tableName;
		$this->_fields = array();
	}
	
	/**
	 * Assign the given value to the given field name
	 * 
	 * @param string $name name of the field
	 * @param string $value value to assign
	 */
	public function field($name, $value) {
		$this->_fields[$name] = $this->_getNewParameterName();
		$this->_parameters[$this->_getNewParameterKey()] = $value;
	}
	
	/**
	 * Assign the given expression to the given field name
	 *
	 * @param string $name name of the field
	 * @param string $expression expression to assign
	 */
	public function expression($name, $expression) {
		$this->_fields[$name] = $this->_getNewParameterName();
		$this->_expressions[$this->_getNewParameterKey()] = $expression;
	}
	
	/**
	 * Returns the name of the target table
	 * 
	 * @return string
	 */
	public function getTableName() {
	    return $this->_tableName;
	}
	
	/**
	 * Indicates whether the current query has a field specified with the given name
	 * 
	 * @param $name name of the field
	 * @return boolean
	 */
	public function hasField($name) {
		return isset($this->_fields[$name]);
	}
	
	/**
	 * Returns the value of the specified field in the current query
	 * 
	 * @param string $name name of the field
	 * @return null|string value of the string
	 */
	public function getFieldValue($name) {
		if (!$this->hasField($name)) {
			return null;
		}
		return $this->_fields[$name];
	}
	
	/**
	 * Gets the list of fields that will be updated
	 * 
	 * @return array
	 */
	public function getFields() {
		return $this->_fields;
	}
	
	/**
	 * Instantiates and returns an object used for INSERT queries
	 * 
	 * @param string $tableName the name of the table concerned by the INSERT query
	 * @param DbConnection $connection the database connection used for the query
	 * @return InsertQuery
	 */
	public static function create($tableName, DbConnection $connection=null) {
		// If there is no given connection
		if (!isset($connection)) {
			$connection = DbConnection::getInstance();
		}
		
		// We build the class name
		$className = '\Zeeye\Db\Query\\'.$connection->getType().'\\'.$connection->getType().'InsertQuery';
		// We instantiate the class and return it
		$query = new $className($tableName);
		// Set the table name
		$query->_tableName = $tableName;
		
		return $query;
	}
}