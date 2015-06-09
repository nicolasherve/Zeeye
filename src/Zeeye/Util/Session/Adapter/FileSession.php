<?php

namespace Zeeye\Util\Session\Adapter;

use Zeeye\Util\Session\SessionAdapter;

/**
 * Concrete session adapter to manage session via files
 * 
 * A cookie is used to send the session id to the server.
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class FileSession extends SessionAdapter {

    /**
     * The path where the session files are stored
     * 
     * @var string
     */
    private static $_savePath;

    /**
     * Default configuration for the session
     */
    public function setup() {
        // If the save path is not defined
        if (!isset(self::$_savePath)) {
            throw new FileSessionException("");
        }

        // Parent setup
        parent::setup();

        // The path where the session files will be stored
        ini_set('session.save_path', self::$_savePath);
    }

    public function open($savePath, $name) {
        // If the save path directory does not exist
        if (!is_dir(self::$_savePath)) {
            // Create the directory
            mkdir(self::$_savePath, 0777, true);
        }

        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $file = self::$_savePath . '/sess_' . $id;
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    public function write($id, $data) {
        return file_put_contents(self::$_savePath . '/sess_' . $id, $data) === false ? false : true;
    }

    public function destroy($id) {
        // Parent destroy
        parent::destroy($id);

        // Delete the session file
        $file = self::$_savePath . '/sess_' . $id;
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc($maxlifetime) {
        // For each session file
        foreach (glob(self::$_savePath . '/sess_*') as $file) {
            // If the session file was modified before the maxlifetime margin
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                // Delete the file
                unlink($file);
            }
        }

        return true;
    }

    public function regenerateId() {
        session_regenerate_id();
    }

    public function getSavePath() {
        return self::$_savePath;
    }

    public function setSavePath($savePath) {
        self::$_savePath = $savePath;
    }

}
