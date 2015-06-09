<?php
namespace Zeeye\Util\String;

use Zeeye\Util\Server\Server;
use Zeeye\App\App;
/**
 * Class providing several operations on UTF-8 strings manipulation
 * 
 * The class requires one or several specific extensions to be enabled.
 * The use of mb_string extension is the most required.
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Utf8String {
	/**
	 * Returns true if the given string equals "UTF-8" (case-insensitive)
	 * 
	 * @param string $string the string to test
	 * @return boolean
	 */
	public static function isUtf8Code($string) {
		return (boolean)strcasecmp($string, 'UTF-8') == 0;
	}
	
	/**
	 * Returns true if the PHP mbstring extension is active
	 * 
	 * @return boolean
	 */
	public static function isMbStringEnabled() {
		return function_exists('mb_strlen');
	}
	
	/**
	 * Returns true if the PHP iconv extension is active
	 *
	 * @return boolean
	 */
	public static function isIconvEnabled() {
		return function_exists('iconv_strlen');
	}
	
	/**
	 * Returns true if the PHP PCRE module is UTF-8 enabled
	 *
	 * @return boolean
	 */
	public static function isPcreUtf8Enabled() {
		return preg_match('/a/u', 'a') !== false;
	}
	
	/**
	 * Returns true if the given string contains UTF-8 characters
	 *
	 * @return boolean
	 */
	public static function isUtf8($string) {
		if (self::isMbStringEnabled()) {
			return mb_strlen($string) == strlen($string);
		}
		if (self::isIconvEnabled()) {
			return iconv_strlen($string) == strlen($string);
		}
		if (self::isPcreUtf8Enabled()) {
			return preg_match('/\S/u', $string);
		}
		
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string, iconv and PCRE UTF-8 extensions are disabled');
	}
	
	/**
	 * Returns the uppercase form of the given string
	 * 
	 * @param string $string the string to transform into its uppercase form
	 * @return string
	 */
	public static function upper($string) {
		if (self::isMbStringEnabled()) {
			return mb_strtoupper($string);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because mb_string extension is disabled');
	}
	
	/**
	 * Returns the lowercase form of the given string
	 *
	 * @param string $string the string to transform into its lowercase form
	 * @return string
	 */
	public static function lower($string) {
		if (self::isMbStringEnabled()) {
			return mb_strtolower($string);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because mb_string extension is disabled');
	}
	
	/**
	 * Returns the length (in characters) of the given string
	 *
	 * @param string $string the string we want to get the length from
	 * @return integer
	 */
	public static function length($string) {
		if (self::isMbStringEnabled()) {
			return mb_strlen($string);
		}
		if (self::isIconvEnabled()) {
			return iconv_strlen($string);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string and iconv extensions are disabled');
	}
	
	/**
	 * Returns the position of the given searched string into the given subject
	 *
	 * Returns false if the searched string is not found
	 *
	 * @param string $search the string that is searched
	 * @param string $subject the string in which the search happens
	 * @return integer|false
	 */
	public static function pos($search, $subject) {
		if (self::isMbStringEnabled()) {
			return mb_strpos($subject, $search);
		}
		if (self::isIconvEnabled()) {
			return iconv_strpos($subject, $search);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string and iconv extensions are disabled');
	}
	
	/**
	 * Returns part of the given string
	 * 
	 * @param string $string the string from which the part will be extracted
	 * @param integer $start the first position from which the part will be extracted
	 * @param integer $length the length of the extracted part
	 * @return string
	 */
	public static function substr($string, $start, $length=null) {
		if (self::isMbStringEnabled()) {
			return mb_substr($string, $start, $length);
		}
		if (self::isIconvEnabled()) {
			return iconv_substr($string, $start, $length);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string and iconv extensions are disabled');
	}
	
	/**
	 * Returns true if the given regular expression matches the given string
	 * 
	 * You must not use delimiter chars in the given pattern.
	 * 
	 * @param string $pattern the regular expression pattern to find
	 * @param string $string the string in which the search happens
	 * @return boolean
	 */
	public static function match($pattern, $string) {
		if (self::isPcreUtf8Enabled()) {
			$delimiterChar = self::_defineDelimiterCharFromPattern($pattern);
			return (boolean) preg_match($delimiterChar.$pattern.$delimiterChar.'u', $string);
		}
		if (self::isMbStringEnabled()) {
			return mb_ereg_match($pattern, $string);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string and PCRE UTF-8 extensions are disabled');
	}
	
	/**
	 * Performs a regular expression search and replace on the given string
	 * 
	 * You must not use delimiter chars in the given pattern.
	 * 
	 * @param string $pattern the regular expression pattern to find
	 * @param string $replacement the replacement for the matched string
	 * @param string $string the string in which the search and replace happens
	 * @return string
	 */
	public static function replace($pattern, $replacement, $string) {
		if (self::isPcreUtf8Enabled()) {
			$delimiterChar = self::_defineDelimiterCharFromPattern($pattern);
			return preg_replace($delimiterChar.$pattern.$delimiterChar.'u', $replacement, $string);
		}
		if (self::isMbStringEnabled()) {
			return mb_ereg_replace($pattern, $replacement, $string);
		}
	
		throw new Utf8StringException('Impossible to perform the required operation because both mb_string and PCRE UTF-8 extensions are disabled');
	}
	
	/**
	 * Convert the encoding of the given string to UTF-8
	 * 
	 * @param string $string the string whose encoding will be converted
	 * @param string $sourceEncoding the initial encoding of the string
	 * @return string
	 */
	public static function convertToUtf8($string, $sourceEncoding) {
		if (self::isMbStringEnabled()) {
			return mb_convert_encoding($string, 'UTF-8', $sourceEncoding);
		}
		if (self::isIconvEnabled()) {
			return iconv($sourceEncoding, 'UTF-8', $string);
		}
		
		throw new Utf8StringException('Impossible to perform the required operation because iconv extension is disabled');
	}
	
	/**
	 * Returns a delimiter character for the given pattern
	 * 
	 * @param string $pattern the regular expression pattern
	 * @return string
	 */
	private static function _defineDelimiterCharFromPattern($pattern) {
		if (self::pos('/', $pattern) === false) {
			return '/';
		}
		if (self::pos('#', $pattern) === false) {
			return '#';
		}
		if (self::pos('|', $pattern) === false) {
			return '|';
		}
		
		throw new Utf8StringException('The given pattern ['.$pattern.'] uses too many special characters to define a delimiter character');
	}
}