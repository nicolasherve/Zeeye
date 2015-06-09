<?php

namespace Zeeye\Cache\Adapter;

use Zeeye\Cache\CacheAdapter;

class DummyCache extends CacheAdapter {

    public function clear() {
        return;
    }

    public function delete($key) {
        return;
    }

    public function get($key) {
        return;
    }

    public function set($key, $value) {
        return;
    }

}
