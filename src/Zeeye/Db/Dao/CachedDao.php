<?php

namespace Zeeye\Db\Dao;

use ReflectionClass;
use Zeeye\Cache\Cache;
use Zeeye\Cache\CacheManager;

/**
 * Class representing a cached Dao
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class CachedDao extends Dao {

    /**
     * The cache used by the cached Dao
     * 
     * @var Cache
     */
    private $_cache = null;

    /**
     * The underlying Dao used by the cached Dao
     * 
     * @var Dao
     */
    private $_dao = null;

    /**
     * Constructor
     * 
     * @param Dao $dao
     * @param Cache $cache
     */
    public function __construct(Dao $dao, Cache $cache) {
        $this->_dao = $dao;
        $this->_cache = $cache;
    }

    public function setup() {
        $this->_dao->setup();
    }

    private static function _generateCacheKey($methodName, $arguments) {
        // Build an array containing a unique identification of the file cache name
        $cacheIdentifiers = $arguments;
        // The parameters are sorted so that any order can be used by the user to produce the same file cache name
        ksort($cacheIdentifiers);
        // Automatically prepend some specific values to the list of cache identifiers
        $class = new ReflectionClass($this->_dao);
        $fileName = $class->getFileName();
        array_unshift($cacheIdentifiers, $fileName, $methodName);

        // Generate the cache key
        return 'dao-' . md5(serialize($cacheIdentifiers));
    }

    public function __call($methodName, $arguments) {

        $cacheKey = self::_generateCacheKey($methodName, $arguments);

        if ($this->_dao->hasCachedMethod($methodName)) {
            $result = $this->_cache->get($cacheKey);
            if (isset($result)) {
                return $result;
            }
        }

        // executes the original function and gets the result
        $result = call_user_func_array(array($this->_dao, $methodName), $arguments);

        if ($this->_dao->hasCachedMethod($methodName)) {
            $this->_cache->set($cacheKey, $result);
        }

        return $result;
    }

    public static function create(Dao $dao, $cacheName) {
        $cache = CacheManager::getInstance($cacheName);
        return new CachedDao($dao, $cache);
    }

}
