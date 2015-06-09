<?php
namespace Zeeye;
/**
 * Requires the classes of the framework and defines some useful paths as constants
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */

// Definition of the current path
define('ZEEYE_PATH', dirname(__FILE__).'/');

// Definition of the root level directories
define('ZEEYE_APPS_PATH',	realpath(ZEEYE_PATH.'../../apps/').'/');
define('ZEEYE_LIBS_PATH',	realpath(ZEEYE_PATH.'../../libs/').'/');
define('ZEEYE_TMP_PATH',	realpath(ZEEYE_PATH.'../../tmp/').'/');