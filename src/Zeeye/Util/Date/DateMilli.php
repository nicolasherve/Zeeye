<?php
namespace Zeeye\Util\Date;

use Zeeye\Util\Date\DateException;
use Zeeye\App\App;
/**
 * Class used to represent a date with milliseconds
 * 
 * A DateMilli instance should be considered immutable (cannot be modified)
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class DateMilli {
	/**
	 * Private constructor
	 */
	private function __construct() {}
	
	/**
	 * Create and return a DateMilli object from a given timestamp
	 * 
	 * @param float $timestamp a Unix timestamp, including milliseconds
	 * @return DateMilli
	 */
	private static function _createFromTimestamp($timestamp) {
		// Creates an instance of the current class
		$date = new DateMilli();
		// Creates the DateTime PHP object from the given timestamp
		$dateTime = new \DateTime();
		// Check if the given timestamp is a float value (which means it contains milliseconds)
		$intValue = (int)$timestamp;
		$date->_setMilliseconds((float)($timestamp - $intValue) * 1000);
		$timestamp = $intValue;
		if ($dateTime->setTimestamp($timestamp) === false) {
			throw new DateMilliException('Impossible to create a Date object from the given timestamp value ['.$timestamp.']');
		}
		
		// Set the PHP DateTime instance property
		$date->_dateTime = $dateTime;
		
		return $date;
	}
	
	
	/**
	 * Factory method to generate and get a date instance with milliseconds
	 * 
	 * The date is forced to be the current date
	 */
	public static function create() {
		return self::_createFromTimestamp(number_format(microtime(true), 3, '.', ''));
	}
	
	/**
	 * Set the milliseconds for the current date
	 * 
	 * @param integer $value the milliseconds
	 */
	private function _setMilliseconds($value) {
		$this->_milliseconds = (int)$value;
	}
	
	/**
	 * Returns a given string format of the current Date instance
	 * 
	 * @param string $format format to use
	 * @return string
	 */
	public function toString($format=null) {
		// If no format is defined, try to use the one specified in the configuration
		if (!isset($format)) {
			$format = App::getInstance()->getDefaultLocaleDateFormat();
		}
		// If no format is defined, use the one specified in the current class
		if (!isset($format)) {
			$format = Date::DATETIME_FORMAT;
		}
		return $this->_dateTime->format($format).'.'.self::_formatMilliseconds($this->_milliseconds);
	}
	
	/**
	 * Format the given milliseconds value (integer) as a 3-characters string
	 * 
	 * @param $milliseconds the integer value
	 * @return string the value formatted as a 3-characters string
	 */
	private static function _formatMilliseconds($milliseconds) {
		if ($milliseconds >= 100) {
			return $milliseconds;
		}
		if ($milliseconds >= 10) {
			return '0'.$milliseconds;
		}
		return '00'.$milliseconds;
	}
}