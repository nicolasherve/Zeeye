<?php

namespace Zeeye\Util\Session\Adapter;

/**
 * TODO A TESTER
 * 
 * Session adapter to manage session via APC
 * 
 * Relies on an underlying database to store session data
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class ApcSession extends DbSession {

    /**
     * Default configuration for the session
     */
    public function setup() {
        // If there is no provided APC
        if (!function_exists('apc_exists')) {
            throw new ApcSessionException("The [apc] PHP extension is required");
        }

        parent::setup();
    }

    public function read($id) {
        // If APC cache contains the data for the given id
        if (apc_exists($id)) {
            return (string) apc_fetch($id);
        }

        // Relies on parent read
        return parent::read($id);
    }

    public function write($id, $data) {
        // If some data was changed
        if ($data != self::getDataAtSessionStart()) {

            // Update APC cache (and expiration date)
            apc_store($id, $data, self::getLifeTime());

            // Relies on parent write
            parent::write($id, $data);
        }

        return true;
    }

    public function destroy($id) {
        // Update APC cache
        apc_delete($id);

        // Relies on parent write
        return parent::destroy($id);
    }

}
