<?php

namespace Zeeye\Cache;

use Zeeye\App\App;
use Zeeye\Cache\Adapter\DummyCache;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Abtract class to manage different cache adapters
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class CacheManager {

    private static $_instances = array();

    public static function getInstance($name) {

        // If the requested Cache is already registered, returns the existing instance
        if (isset(self::$_instances[$name])) {
            return self::$_instances[$name];
        }

        $cache = null;

        if (!self::isCacheEnabled()) {
            $cache = new DummyCache();
        } else {
            // Get the requested cache class configuration settings
            $cacheConfig = App::getInstance()->getAppConfiguration()->getCache($name);

            // Retrieve the class name from the settings
            $cacheClassName = $cacheConfig['class'];

            // Instantiates the given cache with the given settings
            $cache = new $cacheClassName();
            $cache->setup($cacheConfig);
        }

        // If the cache does not extend the CacheAdapter class, throw an exception
        if (!$cache instanceof CacheAdapter) {
            throw new InvalidTypeException('The cache named [' . $name . '] is not valid: CacheAdapter expected, [%s] given instead', $cache);
        }

        // Store the Cache in the list of instances
        self::$_instances[$name] = $cache;

        // Returns the Cache
        return $cache;
    }

    public static function isCacheEnabled() {
        return App::getInstance()->getAppConfiguration()->useCache();
    }

}
