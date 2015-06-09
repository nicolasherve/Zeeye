<?php
namespace Zeeye\Db\Query\MySql;

use Zeeye\Db\Query\UpdateQuery;
/**
 * Class used to manage UPDATE SQL queries for MYSQL databases
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqlUpdateQuery extends UpdateQuery {
	/**
	 * Generates and returns the SQL query string
	 * 
	 * @return string
	 */
	public function toString() {
		$queryString = 'UPDATE '.$this->getTableName().' SET ';
		$i = 0;
		foreach ($this->getFields() as $fieldName => $value) {
			if ($i > 0) {
				$queryString .= ', ';
			}
			$queryString .= $fieldName.' = '.$value;
			$i++;
		}
		// Adds the conditions
		$conditions = $this->getConditionsString();
		if (!empty($conditions)) {
			$queryString .= ' WHERE '.$conditions;
		}
		return $queryString;
	}
}