<?php
namespace Zeeye\Db\Query;

use Zeeye\Db\Connection\DbConnection;
use Zeeye\Util\Exception\InvalidTypeException;
/**
 * Abstract class containing all common operations for SELECT SQL queries
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class SelectQuery extends SqlQuery {
    /**
     * The string containing the FROM part
     *
     * @var string
     */
    private $_from;
    
    /**
     * The alias for the FROM part
     *
     * @var string
     */
    private $_fromAlias;

    /**
     * The list of field names to select
     *
     * @var array
     */
    private $_fields;

    /**
     * The LIMIT value
     *
     * @var string
     */
    private $_limit;

    /**
     * The ORDER BY value
     *
     * @var string
     */
    private $_order;

    /**
     * The GROUP BY value
     *
     * @var string
     */
    private $_group;

    /**
     * The OFFSET value
     *
     * @var integer
     */
    private $_offset;

    /**
     * Constructor
     */
    protected function __construct() {
        parent::__construct();
        $this->_from = null;
        $this->_fromAlias = null;
        $this->_fields = array();
        $this->_limit = null;
        $this->_order = null;
        $this->_group = null;
        $this->_offset = null;
    }

    /**
     * Sets the FROM part of the query
     *
     * @param string|array|SelectQuery|UnionQuery $from identifier for the FROM part part the query
     * @param string $alias alias for the given table
     */
    public function from($from, $alias=null) {
        
        // If the given identifier is a SelectQuery instance or a UnionQuery instance
    	if ($from instanceof SelectQuery || $from instanceof UnionQuery) {
        	// If there is no given alias
        	if (empty($alias)) {
        		throw new SqlQueryException('It is required to set an alias when passing an instance of SelectQuery or UnionQuery to the from() operation');
        	}
        }
        // If the given identifier is an array
        elseif (!is_array($from) && !is_string($from)) {
        	throw new InvalidTypeException('The given $from parameter is not valid: string, array, SelectQuery or UnionQuery expected, [%s] given instead', $from);
        }
        
        // If an alias is given
        if (!empty($alias)) {
	        $this->_fromAlias = $alias;
	    }
	    
        $this->_from = $from;
    }

    /**
     * Adds the given field name to the SELECT part of the query
     *
     * @param string $fieldName name of the field to add
     * @param string $tableName name of the table containing the field
     * @param string $alias alias of the field name
     */
    public function field($fieldName, $tableName=null, $alias=null) {
        $completeFieldName = $fieldName;
        if (!empty($alias)) {
            $completeFieldName .= ' AS '.$alias;
        }
        if (!empty($tableName)) {
            $completeFieldName = $tableName.'.'.$completeFieldName;
        }
        $this->_fields[] = $completeFieldName;
    }

    /**
     * Gets the selected fields
     *
     * @return array
     */
    public function getFields() {
        return $this->_fields;
    }

    /**
     * Gets the FROM part of the query
     *
     * @return string
     */
    public function getFrom() {
        return $this->_from;
    }

    /**
     * Gets the FROM alias part of the query
     *
     * @return string
     */
    public function getFromAlias() {
        return $this->_fromAlias;
    }
    
    
    /**
     * Adds a LEFT JOIN part to the current query
     *
     * @param string $tableName name of the table
     * @param array|string $joinConditions conditions on the join
     * @param string $alias alias of the table
     */
    public function leftJoin($tableName, $joinConditions, $alias=null) {
        $completeTableName = $tableName;
        if (!empty($alias)) {
            $completeTableName .= ' '.$alias;
        }
        $this->_from .= ' LEFT JOIN '.$completeTableName.' ON ';
        if (is_array($joinConditions)) {
            $this->_from .= '('.implode(' AND ', $joinConditions).')';
        }
        else {
            $this->_from .= '('.$joinConditions.')';
        }
    }

    /**
     * Adds a INNER JOIN part to the current query
     *
     * @param string $tableName name of the table
     * @param array|string $joinConditions conditions on the join
     * @param string $alias alias of the tabled
     */
    public function innerJoin($tableName, $joinConditions, $alias=null) {
        $completeTableName = $tableName;
        if (!empty($alias)) {
            $completeTableName .= ' '.$alias;
        }
        $this->_from .= ' INNER JOIN '.$completeTableName.' ON ';
        if (is_array($joinConditions)) {
            $this->_from .= '('.implode(' AND ', $joinConditions).')';
        }
        else {
            $this->_from .= '('.$joinConditions.')';
        }
    }

    /**
     * Sets the LIMIT part for the current query
     *
     * @param string $limit the LIMIT expression
     */
    public function limit($limit) {
        if (!empty($limit)) {
            $this->_limit = $limit;
        }
    }

    /**
     * Gets the LIMIT part of the current query
     *
     * @return string
     */
    public function getLimit() {
        return $this->_limit;
    }

    /**
     * Sets the ORDER BY part for the current query
     *
     * @param string|array $order the ORDER BY expression
     */
    public function order($order) {
        if (!empty($order)) {
            if (is_array($order)) {
            	$order = implode(', ', $order);
            }
        	$this->_order = 'ORDER BY '.$order;
        }
    }

    /**
     * Gets the ORDER BY part of the current query
     *
     * @return string
     */
    public function getOrder() {
        return $this->_order;
    }

    /**
     * Sets the OFFSET part for the current query
     *
     * @param string $offset the OFFSET expression
     */
    public function offset($offset) {
        if (!empty($offset)) {
            $this->_offset = $offset;
        }
    }

    /**
     * Gets the OFFSET part of the current query
     *
     * @return string
     */
    public function getOffset() {
        return $this->_offset;
    }

    /**
     * Sets the GROUP BY part for the current query
     *
     * @param string $group the GROUP BY expression
     */
    function group($group) {
        if (!empty($group)) {
            $this->_group = 'GROUP BY '.$group;
        }
    }

    /**
     * Gets the GROUP BY part of the current query
     *
     * @return string
     */
    public function getGroup() {
        return $this->_group;
    }

    /**
     * Instantiates and returns an object used for SELECT queries
     *
     * @param DbConnection $connection the database connection used for the query
     * @return SelectQuery
     */
    public static function create(DbConnection $connection=null) {
        // If there is no given connection
        if (!isset($connection)) {
            $connection = DbConnection::getInstance();
        }
        // We build the class name
        $className = '\Zeeye\Db\Query\\'.$connection->getType().'\\'.$connection->getType().'SelectQuery';
        // We instantiate the class and return it
        return new $className();
    }
}