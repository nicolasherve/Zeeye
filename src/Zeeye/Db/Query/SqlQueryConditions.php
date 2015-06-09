<?php
namespace Zeeye\Db\Query;
/**
 * Class used to manage conditions in SQL queries
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class SqlQueryConditions {	
	/**
	 * List of the conditions
	 * 
	 * @var array
	 */
	private $_conditions;
	
	/**
	 * The logic used for the current conditions
	 * 
	 * @var string
	 */
	private $_logic;
	
	/**
	 * The query string corresponding to the generated conditions
	 * 
	 * @var string
	 */
	private $_queryString;
	
	/**
	 * Constructor
	 * 
	 * @param string $logic logic used for the current conditions
	 */
	function __construct($logic) {
		$this->_logic = $logic;
		$this->_conditions = array();
		$this->_queryString = '';
	}
	
	/**
	 * Adds the given condition to the current conditions
	 * 
	 * @param string $condition query string representing the condition to add
	 */
	public function addCondition($condition) {
		$this->_conditions[] = $condition;
	}
	
	/**
	 * Generates and returns the query string corresponding to the list of conditions
	 * 
	 * @param boolean $hasParenthesis indicates whether the current conditions have to be specified between brackets
	 * @return string
	 */
	public function toString($hasParenthesis=false) {
		$nbConditions = count($this->_conditions);
		
		if ($hasParenthesis) {
			$this->_queryString = '(';
		}
		for ($i=0; $i < $nbConditions; $i++) {
			$this->_queryString .= $this->_conditions[$i];
			if ($i < $nbConditions - 1) {
				switch ($this->_logic) {
					case 'AND':
						$this->_queryString .= ' AND ';
						break;
					case 'OR':
						$this->_queryString .= ' OR ';
						break;
				}
			}
		}
		if ($hasParenthesis) {
			$this->_queryString .=')';
		}
		return $this->_queryString;
	}
}