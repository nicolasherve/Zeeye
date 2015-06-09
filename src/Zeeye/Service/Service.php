<?php
namespace Zeeye\Service;

use Zeeye\App\App;
/**
 * Abstract class used as a ServiceLocator
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Service {
    /**
     * The list of instanciated services objects
     * 
     * @var array
     */
	private static $_instances = array();
	
    /**
     * Instantiates and returns the service instance corresponding to the given name
     *
     * @param string $name the name refering to the service
     * @return object the service instance
     */
    public static function create($name) {
    	// Get the requested service class name
    	$className = App::getInstance()->getService($name);
    	
    	// Instantiates the corresponding service
    	return new $className();
    }
    
    /**
     * Returns the service instance corresponding to the given name
     * 
     * If the instance was already instantiated, return the same instance
     *
     * @param string $name the name refering to the service
     * @return object the service instance
     */
    public static function getInstance($name) {
    	// If an instance associated to the given name is already registered, return it
    	if (isset(self::$_instances[$name])) {
    		return self::$_instances[$name];
    	}
    	
    	// Create the instance and register it
    	self::$_instances[$name] = self::create($name);
    	
    	// Return the instance
    	return self::$_instances[$name];
    }
}