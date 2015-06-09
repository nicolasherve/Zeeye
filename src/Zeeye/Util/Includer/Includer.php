<?php

namespace Zeeye\Util\Includer;

use Zeeye\Util\Includer\IncluderException;
use Zeeye\Util\File\File;

/**
 * Class used to manage other files inclusions
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Includer {

    /**
     * Array containing the files included until now in the script execution
     *
     * @var array
     */
    private static $_includedFiles = array();

    /**
     * Includes a given file path
     *
     * @param string $filePath the file path to include
     */
    public static function get($filePath) {
        if (!isset(self::$_includedFiles[$filePath])) {
            if (!File::exists($filePath)) {
                throw new IncluderException('The given file path [' . $filePath . '] does not exist');
            }
            self::$_includedFiles[$filePath] = true;
            require($filePath);
        }
    }

}
