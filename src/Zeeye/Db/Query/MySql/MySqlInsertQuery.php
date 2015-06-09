<?php
namespace Zeeye\Db\Query\MySql;

use Zeeye\Db\Query\InsertQuery;
/**
 * Class used to manage INSERT SQL queries for MYSQL databases
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqlInsertQuery extends InsertQuery {
	/**
	 * Generates and returns the SQL query string
	 * 
	 * @return string
	 */
	public function toString() {
		// Gets the fields names and values
		$fieldNames = array_keys($this->getFields());
		$fieldValues = array_values($this->getFields());
		
		// Builds the query string
		$queryString = 'INSERT INTO '.$this->getTableName().' (';
		$queryString .= implode(', ', $fieldNames);
		$queryString .= ') VALUES (';
		$queryString .= implode(', ', $fieldValues);
		$queryString .= ')';
		
		return $queryString;
	}
}