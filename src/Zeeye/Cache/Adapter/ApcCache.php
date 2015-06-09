<?php

namespace Zeeye\Cache\Adapter;

use Zeeye\App\App;
use Zeeye\Cache\CacheAdapter;

class ApcCache extends CacheAdapter {

    public function setup(array $parameters) {
        parent::setup($parameters);

        // If there is no provided APC
        if (!function_exists('apc_exists')) {
            throw new ApcCacheException("The [apc] PHP extension is required");
        }
    }

    public function clear() {

        apc_clear_cache();
    }

    public function delete($key) {
        // Delete APC cache
        apc_delete($key);
    }

    public function get($key) {

        // If APC cache contains the data for the given id
        if (apc_exists($key)) {
            return (string) apc_fetch($key);
        }

        return null;
    }

    public function set($key, $value) {
        // Update APC cache (and expiration date)
        apc_store($key, $value, $this->_lifeTime);
    }

}
