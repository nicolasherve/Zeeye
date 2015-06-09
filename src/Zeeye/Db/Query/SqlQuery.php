<?php
namespace Zeeye\Db\Query;

/**
 * Abstract class containing all common operations for SQL queries
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class SqlQuery {
	/**
	 * The parameter key name
	 * 
	 * @var string
	 */
	const PARAMETER_KEY_NAME = 'var';
	
	/**
	 * List of conditions groups
	 * 
	 * @var array
	 */
	private $_conditionsGroups;
	
	/**
	 * The string of the query containing the WHERE part
	 * 
	 * @var string
	 */
	private $_conditionsString;
	
	/**
	 * The list of parameters of the query
	 * 
	 * @var array
	 */
    protected $_parameters;
    
    /**
     * The list of expressions of the query
     *
     * @var array
     */
    protected $_expressions;
	
	/**
	 * The current number of parameters in the query
	 * 
	 * @var integer
	 */
	private $_nbParams;
	
	/**
	 * The current number of parameters keys in the query
	 * 
	 * @var integer
	 */
	private $_nbKeyParams;
	
	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->_conditionsGroups = array();
		$this->_conditionsString = '';
		$this->_parameters = array();
		$this->_expressions = array();
		$this->_nbParams = 0;
		$this->_nbKeyParams = 0;
	}
	
	/**
	 * Gets the new query parameter name that can be used
	 * 
	 * @return string
	 */
	protected function _getNewParameterName() {
		$this->_nbParams++;
		return ':'.self::PARAMETER_KEY_NAME.$this->_nbParams.':';
	}
	
	/**
	 * Gets the new query parameter key that can be used
	 * 
	 * @return string
	 */
	protected function _getNewParameterKey() {
		$this->_nbKeyParams++;
		return ':'.self::PARAMETER_KEY_NAME.$this->_nbKeyParams.':';
	}
	
	/**
	 * Gets the conditions string from the query
	 * 
	 * @return string
	 */
	public function getConditionsString() {
		// If some conditions groups remain unclosed
		if (!empty($this->_conditionsGroups)) {
			// If there is more than one conditions group to close
			if (count($this->_conditionsGroups) > 1) {
				throw new SqlQueryException('Some conditions groups must be closed');
			}
			// Close the last conditions group
			$this->endConditionsGroup();
		}
		
		return $this->_conditionsString;
	}
	
	/**
	 * Sets the conditions' string
	 * 
	 * @param string $conditionsString the new conditions' string to assign
	 */
	public function setConditionsString($conditionsString) {
		$this->_conditionsString = $conditionsString;
	}
	
	/**
	 * Starts a new conditions group
	 * 
	 * @param string $logic logic used between the conditions of this group
	 */
	public function startConditionsGroup($logic='AND') {
		$this->_conditionsGroups[] = new SqlQueryConditions($logic);
	}
	
	/**
	 * Generic method used to add every condition
	 *
	 * @param string $condition condition expressed in SQL
	 * @param mixed $parameters single parameter or list of parameters used for the given condition
	 */
	private function _addCondition($condition, $parameters=array()) {
		if (is_array($parameters)) {
			foreach ($parameters as $parameter) {
				$this->_parameters[$this->_getNewParameterKey()] = $parameter;
			}
		}
		else {
			$this->_parameters[$this->_getNewParameterKey()] = $parameters;
		}
		$this->_getLastConditionsGroup()->addCondition($condition);
	}
	
	/**
	 * Generic method used to add every expression condition
	 *
	 * @param string $condition condition expressed in SQL
	 * @param mixed $parameters single parameter or list of parameters used for the given condition
	 */
	private function _addExpressionCondition($condition, $parameters=array()) {
		if (is_array($parameters)) {
			foreach ($parameters as $parameter) {
				$this->_expressions[$this->_getNewParameterKey()] = $parameter;
			}
		}
		else {
			$this->_expressions[$this->_getNewParameterKey()] = $parameters;
		}
		$this->_getLastConditionsGroup()->addCondition($condition);
	}
	
	/**
	 * Adds the given condition to the current query
	 * 
	 * @param string $condition condition expressed in SQL
	 * @param array $params list of parameters used for the given condition
	 */
	public function addCondition($condition, array $params=array()) {
		$this->_addCondition($condition, $params);
	}
	
	/**
	 * Adds the given expression condition to the current query
	 *
	 * @param string $condition condition expressed in SQL
	 * @param array $params list of parameters used for the given condition
	 */
	public function addExpression($condition, array $params=array()) {
		$this->_addExpressionCondition($condition, $params);
	}
	
	/**
	 * Gets the current parameters of the query
	 * 
	 * @return array
	 */
	public function getParameters() {
		return $this->_parameters;
	}
	
	/**
	 * Sets the parameters
	 * 
	 * @param array $parameters the new parameters to assign
	 */
	public function setParameters(array $parameters) {
		$this->_parameters = $parameters;
	}
	
	/**
	 * Gets the current expressions of the query
	 *
	 * @return array
	 */
	public function getExpressions() {
		return $this->_expressions;
	}
	
	/**
	 * Sets the expressions
	 *
	 * @param array $expressions the new expressions to assign
	 */
	public function setExpressions(array $expressions) {
		$this->_expressions = $expressions;
	}
	
	/**
	 * Adds a condition of type "equals" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param mixed $fieldValue value to test
	 * @param boolean $isEqual indicates whether we test the equality or not
	 */
	public function addConditionEquals($fieldName, $fieldValue, $isEqual=true) {
		$condition = $fieldName.' ';
		if (!$isEqual) {
			$condition .= '!';
		}
		if (empty($fieldValue) && is_string($fieldValue)) {
			$fieldValue = "''";
		}
		$condition .= '= '.$this->_getNewParameterName();
		$this->_addCondition($condition, $fieldValue);
	}
	
	/**
	 * Adds a condition of type "between" to the current query
	 *
	 * @param string $fieldName name of the field to test
	 * @param integer $minValue the low value of the between
	 * @param integer $maxValue the high value of the between
	 * @param boolean $isBetween indicates whether we test the in between or not
	 */
	public function addConditionIsBetween($fieldName, $minValue, $maxValue, $isBetween=true) {
		$condition = $fieldName;
		if (!$isBetween) {
			$condition .= ' NOT';
		}
		$condition .= ' BETWEEN '.$this->_getNewParameterName().' AND '.$this->_getNewParameterName();
		$this->_addCondition($condition, array($minValue, $maxValue));
	}
    
    /**
	 * Adds a condition of type "greater than" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param mixed $fieldValue value to test
	 * @param boolean $isEqualAllowed indicates whether the given value is considered valid or not
	 */
    public function addConditionGreaterThan($fieldName, $fieldValue, $isEqualAllowed=false) {
        $condition = $fieldName.' >';
		if ($isEqualAllowed) {
			$condition .= '=';
		}
		$condition .= ' '.$this->_getNewParameterName();
		$this->_addCondition($condition, $fieldValue);
    }
    
    /**
	 * Adds a condition of type "less than" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param mixed $fieldValue value to test
	 * @param boolean $isEqualAllowed indicates whether the given value is considered valid or not
	 */
    public function addConditionLessThan($fieldName, $fieldValue, $isEqualAllowed=false) {
        $condition = $fieldName.' <';
		if ($isEqualAllowed) {
			$condition .= '=';
		}
		$condition .= ' '.$this->_getNewParameterName();
		$this->_addCondition($condition, $fieldValue);
    }
	
	/**
	 * Adds a condition of type "like" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param mixed $fieldValue value to test
	 * @param boolean $isEqual indicates whether we test the equality or not
	 */
	public function addConditionLike($fieldName, $fieldValue, $isEqual=true) {
		$condition = $fieldName.' ';
		if (!$isEqual) {
			$condition .= 'NOT ';
		}
		if (empty($fieldValue) && is_string($fieldValue)) {
			$fieldValue = "''";
		}
		$condition .= 'LIKE '.$this->_getNewParameterName();
		$this->_addCondition($condition, $fieldValue);
	}
	
	/**
	 * Adds a condition of type "is null" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param boolean $isNull indicates whether we test the null equality or not
	 */
	public function addConditionIsNull($fieldName, $isNull=true) {
		$condition = $fieldName.' IS ';
		if (!$isNull) {
			$condition .= 'NOT ';
		}
		$condition .= 'NULL';
		$this->_addCondition($condition);
	}
	
	/**
	 * Adds a condition of type "in" to the current query
	 * 
	 * @param string $fieldName name of the field to test
	 * @param string|array $condition string representing the condition, or an array of values
	 * @param boolean $isEqual indicates whether we test the equality or not
	 * @param boolean $hasOnlyNumericValues inidcates whether the values of the condition array are numeric or not
	 * @return false|void
	 */
	public function addConditionIn($fieldName, $condition, $isEqual=true, $hasOnlyNumericValues=true) {
		if (empty($condition)) {
			return false;
		}
		if (is_array($condition)) {
			if (!$hasOnlyNumericValues) {
				foreach ($condition as $condition_key => $condition_row) {
					$condition[$condition_key] = "'".str_replace("'", "\'", $condition[$condition_key])."'";
				}
			}
			$condition = implode(',', $condition);
		}
		$clause = '';
		if (!$isEqual) {
			$clause .= 'NOT ';
		}
		$clause .= 'IN';
		$this->_addCondition($fieldName.' '.$clause.' ('.$condition.')');
	}
	
	/**
	 * Closes the current group of conditions and refreshes the conditions query string consequently
	 */
	public function endConditionsGroup() {
		if (empty($this->_conditionsGroups)) {
			throw new SqlQueryException('There is no conditions groups to close');
		}
		$indexLastConditionsGroup = count($this->_conditionsGroups) - 1;
		if ($indexLastConditionsGroup > 0) {
			$previousConditionsGroup = $this->_conditionsGroups[$indexLastConditionsGroup - 1];
			$previousConditionsGroup->addCondition($this->_conditionsGroups[$indexLastConditionsGroup]->toString(true));
			unset($this->_conditionsGroups[$indexLastConditionsGroup]);
			$this->_conditionsGroups = array_values($this->_conditionsGroups);
		}
		else {
			$this->_conditionsString .= $this->_conditionsGroups[$indexLastConditionsGroup]->toString();
			unset($this->_conditionsGroups[$indexLastConditionsGroup]);
			$this->_conditionsGroups = array();
		}
	}
	
	/**
	 * Gets the last group of conditions used
	 * 
	 * @return ConditionsQuery
	 */
	private function _getLastConditionsGroup() {
		if (empty($this->_conditionsGroups)) {
			$this->startConditionsGroup();
		}
		$indexLastConditionsGroup = \count($this->_conditionsGroups) - 1;
		return $this->_conditionsGroups[$indexLastConditionsGroup];
	}
	

	/**
	 * Generate and return the SQL query string
	 *
	 * @return string
	 */
	abstract function toString();
}