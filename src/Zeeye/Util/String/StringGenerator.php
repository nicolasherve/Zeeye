<?php
namespace Zeeye\Util\String;

/**
 * Provide operations for random string generation
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class StringGenerator {
	
	private static function _generateRandomFromArray($array, $length) {
		$string = '';
		for ($i=0; $i < $length; $i++) {
			$index = array_rand($array);
			$string .= $array[$index];
		}
		return $string;
	}
	
	private static function _generateRandomFromArrays($arrays, $length) {
		$nbArrays = count($arrays);
	
		$string = '';
		for ($i=0; $i < $length; $i++) {
			$array = $arrays[mt_rand(0, $nbArrays - 1)];
			$string .= $array[mt_rand(0, count($array) -1)];
		}
	
		return $string;
	}
	
	public static function generateRandomAlpha($length) {
		$lettersLowerCase = range('a', 'z');
		$lettersUpperCase = range('A', 'Z');
		
		return self::_generateRandomFromArrays(array($lettersLowerCase, $lettersUpperCase), $length);
	}
	
	public static function generateRandomNumbers($length) {
		$numbers = range(0, 9);
		
		return self::_generateRandomFromArray($numbers, $length);
	}
	
	public static function generateRandomAlphaAndNumbers($length) {
		$lettersLowerCase = range('a', 'z');
		$lettersUpperCase = range('A', 'Z');
		$numbers = range(0, 9);
		
		return self::_generateRandomFromArrays(array($lettersLowerCase, $lettersUpperCase, $numbers), $length);
	}
	
	public static function generateRandomAlphaAndNumbersAndSpecial($length) {
		$lettersLowerCase = range('a', 'z');
		$lettersUpperCase = range('A', 'Z');
		$numbers = range(0, 9);
		$specialChars = array('!', '#', '%', '?', '-', '_', '&', '$');
		
		return self::_generateRandomFromArrays(array($lettersLowerCase, $lettersUpperCase, $numbers, $specialChars), $length);
	}
}