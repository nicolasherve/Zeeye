<?php
namespace Zeeye\Db\Query;

use Zeeye\Db\Connection\DbConnection;
/**
 * Abstract class containing all common operations for DELETE SQL queries
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class DeleteQuery extends SqlQuery {
	/**
	 * The name of the target table
	 * 
	 * @var string
	 */
	private $_tableName;
	
	/**
	 * Constructor
	 * 
	 * @param string $tableName name of the table in which we delete
	 */
	protected function __construct($tableName) {
		parent::__construct();
		$this->_tableName = $tableName;
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
	 * Instantiates and returns an object used for DELETE queries
	 * 
	 * @param string $tableName the name of the table concerned by the DELETE query
	 * @param DbConnection $connection the database connection used for the query
	 * @return DeleteQuery
	 */
	public static function create($tableName, DbConnection $connection=null) {
		// If there is no given connection
		if (!isset($connection)) {
			$connection = DbConnection::getInstance();
		}
		
		// We build the class name
		$className = '\Zeeye\Db\Query\\'.$connection->getType().'\\'.$connection->getType().'DeleteQuery';
		// We instantiate the class and return it
		$query = new $className($tableName);
		// Set the table name
		$query->_tableName = $tableName;
		
		return $query;
	}
}