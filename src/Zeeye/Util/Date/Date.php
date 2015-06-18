<?php

namespace Zeeye\Util\Date;

use Zeeye\Util\Date\DateException;
use Zeeye\App\App;

/**
 * Class used to manage operations on dates
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Date {

    /**
     * The date format
     * 
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * The date time format
     * 
     * @var string
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * The second unit for intervals
     * 
     * @var string
     */
    const INTERVAL_SECOND_UNIT = 's';

    /**
     * The minute unit for intervals
     * 
     * @var string
     */
    const INTERVAL_MINUTE_UNIT = 'i';

    /**
     * The hour unit for intervals
     * 
     * @var string
     */
    const INTERVAL_HOUR_UNIT = 'h';

    /**
     * The day unit for intervals
     * 
     * @var string
     */
    const INTERVAL_DAY_UNIT = 'd';

    /**
     * The month unit for intervals
     * 
     * @var string
     */
    const INTERVAL_MONTH_UNIT = 'm';

    /**
     * The year unit for intervals
     * 
     * @var string
     */
    const INTERVAL_YEAR_UNIT = 'y';

    /**
     * The second unit
     * 
     * @var string
     */
    const SECOND_UNIT = 'second';

    /**
     * The minute unit
     * 
     * @var string
     */
    const MINUTE_UNIT = 'minute';

    /**
     * The hour unit
     * 
     * @var string
     */
    const HOUR_UNIT = 'hour';

    /**
     * The day unit
     * 
     * @var string
     */
    const DAY_UNIT = 'day';

    /**
     * The month unit
     * 
     * @var string
     */
    const MONTH_UNIT = 'month';

    /**
     * The year unit
     * 
     * @var string
     */
    const YEAR_UNIT = 'year';

    /**
     * The second part of a date
     * 
     * @var string
     */
    const SECOND_PART = 's';

    /**
     * The minute part of a date
     * 
     * @var string
     */
    const MINUTE_PART = 'i';

    /**
     * The hour part of a date
     * 
     * @var string
     */
    const HOUR_PART = 'H';

    /**
     * The day part of a date
     * 
     * @var string
     */
    const DAY_PART = 'd';

    /**
     * The month part of a date
     * 
     * @var string
     */
    const MONTH_PART = 'm';

    /**
     * The year part of a date
     * 
     * @var string
     */
    const YEAR_PART = 'Y';

    /**
     * The "date" (year, month and day) part of a date
     * 
     * @var string
     */
    const DATE_PART = 'Y-m-d';

    /**
     * The "time" (hour, minute and second) part of a date
     * 
     * @var string
     */
    const TIME_PART = 'H:i:s';

    /**
     * The "datetime" (year, month, day and hour, minute, second) part of a date
     * 
     * @var string
     */
    const DATETIME_PART = 'Y-m-d H:i:s';

    /**
     * The available date units
     * 
     * @var array
     */
    private static $_units = array(
        self::YEAR_UNIT => 1,
        self::MONTH_UNIT => 1,
        self::DAY_UNIT => 1,
        self::HOUR_UNIT => 1,
        self::MINUTE_UNIT => 1,
        self::SECOND_UNIT => 1
    );

    /**
     * The available date parts
     * 
     * @var array
     */
    private static $_parts = array(
        self::YEAR_PART => 1,
        self::MONTH_PART => 1,
        self::DAY_PART => 1,
        self::HOUR_PART => 1,
        self::MINUTE_PART => 1,
        self::SECOND_PART => 1,
        self::DATE_PART => 1,
        self::TIME_PART => 1,
        self::DATETIME_PART => 1
    );

    /**
     * The available formats for creating an instance
     * 
     * @var string
     */
    private static $_formats = array(
        self::DATETIME_FORMAT => 1,
        self::DATE_FORMAT => 1,
        \DateTime::ATOM => 1,
        \DateTime::COOKIE => 1,
        \DateTime::ISO8601 => 1,
        \DateTime::RFC822 => 1,
        \DateTime::RFC850 => 1,
        \DateTime::RFC1036 => 1,
        \DateTime::RFC1123 => 1,
        \DateTime::RFC2822 => 1,
        \DateTime::RFC3339 => 1,
        \DateTime::RSS => 1,
        \DateTime::W3C => 1
    );

    /**
     * The current Timezone used in the application
     * 
     * @var string
     */
    private static $_timeZone = null;

    /**
     * PHP Built-in DateTime object
     * 
     * @var DateTime
     */
    private $_dateTime;

    /**
     * Private constructor
     */
    private function __construct() {
        
    }

    /**
     * Create and return a Date object from a given timestamp
     * 
     * @param integer $timestamp a Unix timestamp
     * @return Date
     */
    private static function _createFromTimestamp($timestamp) {
        // Creates an instance of the current class
        $date = new Date();
        // Creates the DateTime PHP object from the given timestamp
        $dateTime = new \DateTime();
        if ($dateTime->setTimestamp($timestamp) === false) {
            throw new DateException('Impossible to create a Date object from the given timestamp value [' . $timestamp . ']');
        }

        // Set the PHP DateTime instance property
        $date->_dateTime = $dateTime;

        return $date;
    }

    /**
     * Create and return a Date object from the given string and format
     * 
     * If no string is specified, the current date is used.
     * If no format is specified, the ISO8601 representation of a date is used (T optional).
     * 
     * @param string $string the string representation of the date
     * @param string $format the string format of the date
     * @return Date
     */
    private static function _createFromString($string = null, $format = self::DATETIME_FORMAT) {
        $dateTime = null;
        // Creates the DateTime PHP object from the given string
        if (!isset($string)) {
            // The DateTime object is created with the default constructor
            $dateTime = new \DateTime();
        } else {
            self::_checkDateFormat($format);
            $dateTime = \DateTime::createFromFormat($format, $string);
            if ($dateTime === false) {
                throw new DateException('Impossible to create a Date object from the given string [' . $string . '] and format [' . $format . '] values');
            }
        }

        // Creates an instance of the current class
        $date = new Date();
        $date->_dateTime = $dateTime;

        return $date;
    }

    /**
     * Factory method to generate and get a date instance
     * 
     * The parameters can be used to create a specific date from a specific format.
     * If no parameter is provided, create an instance corresponding to the current date.
     * 
     * @param mixed $mixed a representation of the date to create, as a timestamp or a string
     * @param string $format, in the case of a string representation of a date, this is the format that must be used to analyse the string
     * @return Date
     */
    public static function create($mixed = null, $format = self::DATETIME_FORMAT) {
        if (!isset(self::$_timeZone)) {
            self::setDefaultTimeZone();
        }
        if (is_int($mixed)) {
            return self::_createFromTimestamp($mixed);
        }
        return self::_createFromString($mixed, $format);
    }

    /**
     * Returns a given string format of the current Date instance
     * 
     * @param string $format format to use
     * @return string
     */
    public function toString($format = null) {
        // If no format is defined, try to use the one specified in the configuration
        if (!isset($format)) {
            $format = App::getInstance()->getConf()->getDefaultLocaleDateFormat();
        }
        // If no format is defined, use the one specified in the current class
        if (!isset($format)) {
            $format = self::DATETIME_FORMAT;
        }
        return $this->_dateTime->format($format);
    }

    /**
     * Returns the Unix timestamp value corresponding to the current date
     * 
     * @return integer
     */
    public function getTimestamp() {
        return $this->_dateTime->getTimestamp();
    }

    /**
     * Sets the default time zone for the application
     * 
     * @param string $timeZone the new time zone that will be used
     */
    public static function setDefaultTimeZone($timeZone = null) {
        if (!isset($timeZone)) {
            $timeZone = App::getInstance()->getConf()->getDefaultLocaleTimezone();
        }
        if (!isset($timeZone)) {
            $timeZone = ini_get('date.timezone');
        }
        if (empty($timeZone)) {
            throw DateException('There is no default time zone defined. Please define one.');
        }
        self::$_timeZone = $timeZone;
        date_default_timezone_set(self::$_timeZone);
    }

    /**
     * Sets the time zone to use for the current date
     * 
     * @param string $timeZone the new time zone that will be used
     */
    public function setTimeZone($timeZone) {
        $this->_dateTime->setTimezone($timeZone);
    }

    /**
     * Indicates if the given date equals the current instance
     * 
     * @param Date $date date to test
     * @param string $part part of the date that will be tested
     * @return boolean
     */
    public function isEqual(Date $date, $part = null) {
        if (!isset($part)) {
            return $this->_dateTime == $date;
        }
        self::_checkDatePart($part);
        return $this->_dateTime->format($part) == $date->_dateTime->format($part);
    }

    /**
     * Indicates if the given date is earlier than the current instance
     * 
     * @param Date $date date to test
     * @param string $part part of the date that will be tested
     * @return boolean
     */
    public function isEarlier(Date $date, $part = null) {
        if (!isset($part)) {
            return $this->_dateTime < $date;
        }

        return $this->_dateTime->format($part) < $date->_dateTime->format($part);
    }

    /**
     * Returns the difference between the current date and the given one, in the given unit
     * 
     * @param Date $date the date we want to compare to the current one
     * @param string $unit unit of the value we want to retrieve
     * @return integer
     */
    public function getDiffFromDate(Date $date, $unit = null) {
        if (!isset($unit)) {
            return $this->_dateTime->diff($date->_dateTime);
        }
        self::_checkDateUnit($unit);

        // Limit the units that can be used
        if ($unit == self::MONTH_UNIT || $unit == self::YEAR_UNIT) {
            throw new DateException("The getDiffFromDate() operation cannot be used with MONTH or YEAR unit");
        }

        // Get the number of seconds between the two dates
        $diffInSeconds = abs($this->getTimestamp() - $date->getTimestamp());

        // Return the result value into the expected unit
        return $this->_convertSecondsIntoGivenUnit($diffInSeconds, $unit);
    }

    private function _convertSecondsIntoGivenUnit($nbSeconds, $unit) {
        if ($unit == self::DAY_UNIT) {
            return floor($nbSeconds / (60 * 60 * 24));
        }
        if ($unit == self::HOUR_UNIT) {
            return floor($nbSeconds / (60 * 60));
        }
        if ($unit == self::MINUTE_UNIT) {
            return floor($nbSeconds / 60);
        }
        if ($unit == self::SECOND_UNIT) {
            return $nbSeconds;
        }
    }

    /**
     * Indicates whether the given year is a leap year or not
     * 
     * @param integer $year year
     * @return boolean;
     */
    public static function isLeapYear($year) {
        if ($year % 400 == 0) {
            return true;
        }
        if ($year % 100 == 0) {
            return false;
        }
        if ($year % 4 == 0) {
            return true;
        }
        return false;
    }

    /**
     * Adds the given value of given unit to the current instance
     * 
     * @param integer $value value to add to the current instance
     * @param string $unit unit of the value to add to the current instance
     */
    public function add($value, $unit) {
        self::_checkDateUnit($unit);
        $this->_dateTime->modify('+' . intval($value) . ' ' . $unit);
    }

    /**
     * Removes the given value of given unit from the current instance
     * 
     * @param integer $value value to remove from the current instance
     * @param string $unit unit of the value to remove from the current instance
     */
    public function remove($value, $unit) {
        self::_checkDateUnit($unit);
        $this->_dateTime->modify('-' . intval($value) . ' ' . $unit);
    }

    /**
     * Check if the given date unit is valid
     * 
     * @param string $unit the date unit to check
     */
    private static function _checkDateUnit($unit) {
        if (!isset(self::$_units[$unit])) {
            throw new DateException('The given unit [' . $unit . '] is not a valid date unit');
        }
    }

    /**
     * Check if the given date part is valid
     *
     * @param string $part the date part to check
     */
    private static function _checkDatePart($part) {
        if (!isset(self::$_parts[$part])) {
            throw new DateException('The given part [' . $part . '] is not a valid date part');
        }
    }

    /**
     * Check if the given date format is valid
     *
     * @param string $format the date format to check
     */
    private static function _checkDateFormat($format) {
        if (!isset(self::$_formats[$format])) {
            throw new DateException('The given format [' . $format . '] is invalid');
        }
    }

}
