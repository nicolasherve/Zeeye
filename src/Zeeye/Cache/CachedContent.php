<?php

namespace Zeeye\Cache;

trait CachedContent {

    /**
     * @var CacheAdapter
     */
    protected $_cache;

    public function isCacheEnabled() {
        return isset($this->_cache) && $this->_cache->isEnabled();
    }

    /**
     * @param array $identifiers  an array containing a unique identification of the file cache name
     */
    public abstract function generateCacheKey(array $identifiers = array());

    public function getCachedContent($cacheKey) {
        // If the cache is not enabled
        if (!$this->isCacheEnabled()) {
            return null;
        }

        // Get the cached content
        $content = $this->_cache->get($cacheKey);
        // If the cached content is defined
        if (isset($content)) {
            return $content;
        }

        return null;
    }

    public function cacheContent($cacheKey, $content) {
        // If the cache is enabled
        if ($this->isCacheEnabled()) {
            // Create the corresponding cache
            $this->_cache->set($cacheKey, $content);
        }
    }

}
