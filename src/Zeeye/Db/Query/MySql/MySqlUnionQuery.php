<?php
namespace Zeeye\Db\Query\MySql;

use Zeeye\Db\Query\UnionQuery;
/**
 * Class used to manage UNION SQL queries for MYSQL databases
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqlUnionQuery extends UnionQuery {	
	/**
	 * Generates and returns the SQL query string
	 *
	 * @return string
	 */
	public function toString() {
		
		// Get the SELECT queries
		$selects = $this->getSelects();
			
		// If there is not SELECT query
		if (empty($selects)) {
			return '';
		}
		
		$isFirst = true;
		$query = '(';
		foreach ($selects as $select) {
			if ($isFirst) {
				$isFirst = false;
			}
			else {
				$query .= ' UNION ';
			}
			$query .= '('.$select->toString().')';
		}
		$query .= ')';
		
		return $query;
	}
}