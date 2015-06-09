<?php

namespace Zeeye\Cache\Adapter;

use Zeeye\App\App;
use Zeeye\Cache\CacheAdapter;
use Zeeye\Util\File\File;

class FileCache extends CacheAdapter {

    public function setup(array $parameters) {
        parent::setup($parameters);
    }

    private function _generateCacheDirectory() {
        return ZEEYE_TMP_PATH . App::getInstance()->getName() . '/cache';
    }

    private function _generateFilePathFromKey($key) {
        return $this->_generateCacheDirectory() . '/' . $key;
    }

    public function clear() {

        $directoryPath = $this->_generateCacheDirectory();

        if (!File::exists($directoryPath)) {
            return false;
        }

        File::deleteDirectory($directoryPath);

        return true;
    }

    public function delete($key) {

        $filePath = $this->_generateFilePathFromKey($key);

        if (!File::exists($filePath)) {
            return false;
        }

        File::deleteFile($filePath);

        return true;
    }

    public function get($key) {

        $filePath = $this->_generateFilePathFromKey($key);

        if (!File::exists($filePath)) {
            return null;
        }

        return File::read($filePath);
    }

    public function set($key, $value) {

        $filePath = $this->_generateFilePathFromKey($key);

        File::write($filePath, $value);
    }

}
