<?php
namespace Zeeye\Util\Autoloader;

require(ZEEYE_PATH.'Util/Includer/Includer.php');
require(ZEEYE_PATH.'Util/File/File.php');
require(ZEEYE_PATH.'App/App.php');

use Zeeye\Util\Includer\Includer;
use Zeeye\Util\File\File;
use Zeeye\App\App;
/**
 * Autoloader
 * 
 * PSR-0 compliant
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Psr0Autoloader {
	/**
	 * The directories contained in apps/
	 * 
	 * @var array
	 */
	private static $_appsDirectories = array();
	
	/**
	 * The directories contained in libs/
	 * 
	 * @var array
	 */
	private static $_libsDirectories = array();
	
	/**
	 * Autoloader method provided to load the appropriate file corresponding to the given class name
	 * 
	 * @param string $className name of the class to load
	 */
	public static function autoload($className) {
		// Build the list of applications directories
		if (empty(self::$_appsDirectories)) {
			self::$_appsDirectories = File::getDirectoriesNames(ZEEYE_APPS_PATH);
		}
		
		// Build the list of libraries directories
		if (empty(self::$_libsDirectories)) {
			self::$_libsDirectories = File::getDirectoriesNames(ZEEYE_LIBS_PATH);
		}
		
		$className = ltrim($className, '\\');
		$fileName = '';
	    $fullNamespace = '';
	    $rootPackageName = '';
	    if ($lastBackSlashPosition = strrpos($className, '\\')) {
	        $firstBackSlashPosition = strpos($className, '\\');
	        $rootPackageName = substr($className, 0, $firstBackSlashPosition);
	        $fullNamespace = substr($className, 0, $lastBackSlashPosition);
	        $className = substr($className, $lastBackSlashPosition + 1);
	        
	        $fileName = str_replace('\\', '/', $fullNamespace).'/';
	    }
	    
	    
	    if (strpos($className, '_') !== false) {
	    	$className = str_replace('_', DIRECTORY_SEPARATOR, $className);
	    }
	    
	    $fileName .= $className;
	    
	    $rootPath = null;
	    foreach (self::$_appsDirectories as $directoryName) {
    	    if (strcasecmp($rootPackageName, $directoryName) === 0) {
    	    	$rootPath = ZEEYE_APPS_PATH;
    	    	break;
    	    }
	    }
	    if (!isset($rootPath)) {
    	    foreach (self::$_libsDirectories as $directoryName) {
        	    if (strcasecmp($rootPackageName, $directoryName) === 0) {
        	    	$rootPath = ZEEYE_LIBS_PATH;
        	    	break;
        	    }
    	    }
	    }
	    if (!isset($rootPath)) {
	    	// At this point the root path has not been found
	        return;
	    }
	    
	    Includer::get($rootPath.$fileName.'.php');
	}
	
	/**
	 * Register the current autoloader
	 */
	public static function register() {
		spl_autoload_register('Zeeye\Util\Autoloader\Psr0Autoloader::autoload');
	}
}

// Register the autoloader
Autoloader::register();