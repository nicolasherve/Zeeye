<?php
namespace Zeeye\Db\Query\MySql;

use Zeeye\Db\Query\DeleteQuery;
/**
 * Class used to manage DELETE SQL queries for MYSQL databases
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqlDeleteQuery extends DeleteQuery {
	/**
	 * Generates and returns the SQL query string
	 * 
	 * @return string
	 */
	public function toString() {		
		$queryString = 'DELETE FROM '.$this->getTableName().' ';
		// Adds the conditions
		$conditions = $this->getConditionsString();
		if (!empty($conditions)) {
			$queryString .= ' WHERE '.$conditions;
		}
		return $queryString;
	}
}