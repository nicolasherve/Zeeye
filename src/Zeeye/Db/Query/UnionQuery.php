<?php
namespace Zeeye\Db\Query;

use Zeeye\Db\Connection\DbConnection;
/**
 * Class used to manage UNION SQL queries
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class UnionQuery {	
	/**
	 * A list of SELECT queries that will be linked by a UNION query
	 * 
	 * @var array
	 */
	private $_selects;
	
	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->_selects = array();
	}
	
	/**
	 * Adds the given SELECT query to the list of queries to UNION
	 * 
	 * @param SelectQuery $select the SELECT query string to add
	 * @param boolean $isDistinct indicates whether the new SELECT parameter must be added in a distinct way or not
	 */
	public function addSelect(SelectQuery $select, $isDistinct=false) {
		$this->_selects[] = $select;
	}
	
	/**
	 * Get the SELECT queries
	 * 
	 * @return array
	 */
	public function getSelects() {
		return $this->_selects;
	}
	
	/**
	 * Instantiates and returns an object used for UNION queries
	 *
	 * @param DbConnection $connection the database connection used for the query
	 * @return UnionQuery
	 */
	public static function create(DbConnection $connection) {
		// If there is no given connection
        if (!isset($connection)) {
            $connection = DbConnection::getInstance();
        }
        // We build the class name
        $className = '\Zeeye\Db\Query\\'.$connection->getType().'\\'.$connection->getType().'UnionQuery';
        // We instantiate the class and return it
        return new $className();
	}
}