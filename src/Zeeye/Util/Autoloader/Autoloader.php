<?php

namespace Zeeye\Util\Autoloader;

require(ZEEYE_PATH . 'Util/Includer/Includer.php');
require(ZEEYE_PATH . 'Util/File/File.php');
require(ZEEYE_PATH . 'App/App.php');

use Zeeye\Util\Includer\Includer;
use Zeeye\Util\File\File;
use Zeeye\App\App;

/**
 * Zeeye's default autoloader
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Autoloader {

    /**
     * The path to the cache file
     * 
     * @var string
     */
    const CACHE_FILE_PATH = 'autoloader/paths.php';

    /**
     * The paths to root namespaces directories
     *
     * @var array
     */
    private static $_paths = array();

    /**
     * Indicates if the cache file exists
     * 
     * Default value is null (undefined)
     * 
     * @var boolean|null
     */
    private static $_hasCacheFile = null;

    /**
     * Indicates if the cache file exists
     * 
     * @return boolean
     */
    private static function hasCacheFile() {
        if (!isset(self::$_hasCacheFile)) {
            self::$_hasCacheFile = File::exists(ZEEYE_TMP_PATH . self::CACHE_FILE_PATH);
        }
        return self::$_hasCacheFile;
    }

    /**
     * Read the cache file
     * 
     * If the file does not exist, create it
     */
    private static function _readCacheFile() {
        if (!self::$_hasCacheFile) {
            self::_createCacheFile();
        }

        $paths = array();
        require(ZEEYE_TMP_PATH . self::CACHE_FILE_PATH);
        self::$_paths = $paths;
    }

    /**
     * Create the cache file
     */
    private static function _createCacheFile() {
        $paths = array();
        $paths['apps'] = File::getDirectoriesNames(ZEEYE_APPS_PATH);
        $paths['libs'] = File::getDirectoriesNames(ZEEYE_LIBS_PATH);
        File::write(ZEEYE_TMP_PATH . self::CACHE_FILE_PATH, '<?php ' . PHP_EOL . '$paths = ' . var_export($paths, true) . ';');
        self::$_hasCacheFile = true;
    }

    /**
     * Find the root path corresponding to the given root namespace
     * 
     * @param string $namespace the root namespace name
     * @return string|null
     */
    private static function _findRootPathForNamespace($namespace) {
        foreach (self::$_paths['apps'] as $directoryName) {
            if (strcasecmp($namespace, $directoryName) === 0) {
                return ZEEYE_APPS_PATH;
            }
        }
        foreach (self::$_paths['libs'] as $directoryName) {
            if (strcasecmp($namespace, $directoryName) === 0) {
                return ZEEYE_LIBS_PATH;
            }
        }
        return null;
    }

    /**
     * Autoloader method provided to load the appropriate file corresponding to the given class name
     * 
     * @param string $className name of the class to load
     */
    public static function autoload($className) {
        // Build the list of root namespaces directories
        if (empty(self::$_paths)) {
            self::_readCacheFile();
        }

        $className = ltrim($className, '\\');
        $filePath = '';
        $fullNamespace = '';
        $rootNamespace = '';
        if ($lastBackSlashPosition = strrpos($className, '\\')) {
            $firstBackSlashPosition = strpos($className, '\\');
            $rootNamespace = substr($className, 0, $firstBackSlashPosition);
            $fullNamespace = substr($className, 0, $lastBackSlashPosition);
            $className = substr($className, $lastBackSlashPosition + 1);

            $filePath = str_replace('\\', '/', $fullNamespace) . '/';
        }

        $filePath .= $className;

        // Find the root path
        $rootPath = self::_findRootPathForNamespace($rootNamespace);

        // If no root path was found, give up
        if (!isset($rootPath)) {
            return;
        }

        $completeFilePath = $rootPath . $filePath . '.php';

        // Si le chemin n'existe pas, renvoyer vide (ne doit pas generer d'exception si la classe n'est pas trouvee)
        // TODO Improve since this is redundant with Includer::get
        if (!File::exists($completeFilePath)) {
            return false;
        }

        Includer::get($rootPath . $filePath . '.php', false);

        return true;
    }

    /**
     * Register the current autoloader
     */
    public static function register() {
        spl_autoload_register('Zeeye\Util\Autoloader\Autoloader::autoload');
    }

}

// Register the autoloader
Autoloader::register();
