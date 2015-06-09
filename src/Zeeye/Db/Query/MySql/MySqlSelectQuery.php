<?php
namespace Zeeye\Db\Query\MySql;

use Zeeye\Db\Query\SqlQuery;
use Zeeye\Db\Query\SelectQuery;
use Zeeye\Db\Query\UnionQuery;
use Zeeye\Util\String\String;
/**
 * Class used to manage SELECT SQL queries for MYSQL databases
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class MySqlSelectQuery extends SelectQuery {
	
	
	/**
	 * Generates and returns the SQL query string
	 * 
	 * @return string
	 */
	public function toString() {
		// Fields
		$fields = implode(', ', $this->getFields());
		
		// From
		$from = $this->getFrom();
		$fromString = $from;
		// If the given identifier is a SelectQuery instance or a UnionQuery instance
		if ($from instanceof SelectQuery || $from instanceof UnionQuery) {
			$fromString = $from->toString();
		}
		// If the given identifier is an array
		elseif (is_array($from)) {
			$fromString = implode(', ', $from);
		}
		// If an alias is given
		$alias = $this->getFromAlias();
		if (!empty($alias)) {
			$fromString .= ' AS '.$alias;
		}
		
		// Starts the query string
		$queryString = 'SELECT '.$fields.' FROM '.$fromString;
		
		// Adds the conditions
		$conditions = $this->getConditionsString();
		if (!empty($conditions)) {
			$queryString .= ' WHERE '.$conditions;
		}
		
		// Adds the GROUP part
		$group = $this->getGroup();
		if (!empty($group)) {
			$queryString .= ' '.$group;
		}
		
		// Adds the ORDER part
		$order = $this->getOrder();
		if (!empty($order)) {
			$queryString .= ' '.$order;
		}
		
		// Adds the LIMIT part
		$limit = $this->getLimit();
		if (!empty($limit)) {
			if ($offset = $this->getOffset()) {
				$queryString .= ' LIMIT '.$offset.','.$limit;
			}
			else {
				$queryString .= ' LIMIT '.$limit;
			}
		}
		
		return $queryString;
	}
}