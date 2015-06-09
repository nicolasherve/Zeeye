<?php
namespace Zeeye\Util\String;

/**
 * Provide operations for JSON strings manipulation
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Json {

	public static function encode($value)	{
		return json_encode($value);
	}
	
	public static function decode($string) {
		return json_decode($string);
	}
}