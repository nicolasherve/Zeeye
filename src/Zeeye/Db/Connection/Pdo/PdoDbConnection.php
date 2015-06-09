<?php
namespace Zeeye\Db\Connection\Pdo;

use Zeeye\Db\Connection\DbConnection;
use Zeeye\Db\Connection\Pdo\PdoDbConnectionException;
/**
 * Class used for all PDO connections
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class PdoDbConnection extends DbConnection {
	/**
	 * The PDO object
	 * 
	 * @var PDO
	 */
    protected $_pdo;
    
    /**
     * A list of options for the driver
     * 
     * @var array
     */
    private $_options;
    
    /**
     * Constructor
     * 
     * @param string $name the name of the connection
     * @param array $profile the profile information
     */
    protected function __construct($name, array $profile) {
    	// Uses parent constructor
        parent::__construct($name, $profile);
        
        // Try to connect to the database
        try {
	        // Instantiate the PDO object (try to connect to the server)
	        $this->_pdo = new \PDO($this->getDriver().':dbname='.$this->getDbName().';host='.$this->getHost(), $this->getUser(), $this->getPassword(), $this->getOptions());
        }
        catch (PDOException $e) {
        	throw new PdoDbConnectionException($e->getStackTrace());
        }
    }
    
	public function getOptions() {
    	return $this->_options;
    }
    
    protected function addOption($key, $value) {
    	$this->_options[$key] = $value;
    }
}