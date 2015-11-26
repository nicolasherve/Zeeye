<?php

namespace Zeeye\Validator;

use Zeeye\App\App;
use Zeeye\Util\Date\Date;
use Zeeye\Util\String\Utf8String;
use Zeeye\Util\Url\Url;

/**
 * Abstract class for all Validator objects
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Validator {

    /**
     * The default key name to store error messages
     * 
     * @var string
     */
    const DEFAULT_KEY_NAME = 'default';

    /**
     * A list of errors when validating the data
     *
     * @var array
     */
    private $_errors;

    /**
     * An error flag indicating if there are errors, or not
     * 
     * @var boolean
     */
    private $_errorFlag;

    /**
     * Private constructor
     */
    private function __construct() {
        $this->_errors = array();
        $this->_errorFlag = false;
    }

    /**
     * Execute optional process right after the instanciation
     *
     * Concrete subclasses can override this method
     */
    public function setup() {
        
    }

    /**
     * Indicates that the current validator should be considered with errors
     * 
     * This is an alternative way of indicating errors but without specifying messages
     */
    public function flagError() {
        $this->_errorFlag = true;
    }

    /**
     * Add the given error message to the current list of errors
     *
     * @param string $message the error message
     * @param mixed $key the key used to store the error message
     */
    public function addError($message, $key = self::DEFAULT_KEY_NAME) {
        $this->_errors[$key][] = $message;
    }

    /**
     * Remove all the registered errors
     * 
     * An optional argument can be given to indicate which key is used to clear the errors
     */
    public function clearErrors($key = self::DEFAULT_KEY_NAME) {
        if ($this->hasErrors($key)) {
            $this->_errors[$key] = array();
        }
    }

    /**
     * Get the current list of errors
     *
     * An optional argument can be given to indicate which key is used to get the required errors
     * 
     * @return array
     */
    public function getErrors($key = self::DEFAULT_KEY_NAME) {
        if ($this->hasErrors($key)) {
            return $this->_errors[$key];
        }
        return array();
    }

    /**
     * Indicates whether the current validation contains error messages
     * 
     * An optional argument can be given to check if there are some error messages for the given key
     * 
     * @param string $key the key used to store the error
     * @return boolean
     */
    public function hasErrors($key = self::DEFAULT_KEY_NAME) {
        return isset($this->_errors[$key]);
    }

    /**
     * Indicates if the current validator is valid (contains no error)
     * 
     * The current validator is considered valid only if there are no error messages AND the error flag is set to false
     * 
     * @return boolean
     */
    public function isValid() {
        return empty($this->_errors) && !$this->_errorFlag;
    }

    /**
     * Check method provided to test if the given value is not empty
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isEmpty($value) {
        return !isset($value) || $value === '';
    }

    /**
     * Check method provided to test if the given value is valid IP address
     *
     * @param string $value the value to test
     * @return boolean
     */
    public function isIp($value) {
        return Url::isValidIp($value);
    }

    /**
     * Check method provided to test if the given value is a valid email address
     *
     * @param string $value the value to test
     * @return boolean
     */
    public function isEmail($value) {
        return preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $value);
    }

    /**
     * Check method provided to test if the given text value has a length greater or equal to the given parameter
     *
     * @param string $text the value to test
     * @param integer $length the min length authorized
     * @return boolean
     */
    public function hasMinLength($text, $length) {
        if (Utf8String::isUtf8($text)) {
            return Utf8String::length($text) >= $length;
        }
        return strlen($text) >= $length;
    }

    /**
     * Check method provided to test if the given text value has a length lower or equal to the given parameter
     *
     * @param string $text the value to test
     * @param integer $length the max length authorized
     * @return boolean
     */
    public function hasMaxLength($text, $length) {
        if (Utf8String::isUtf8($text)) {
            return Utf8String::length($text) <= $length;
        }
        return strlen($value) <= $length;
    }

    /**
     * Check method provided to test if the given value is numeric
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isNumber($value) {
        return is_numeric($value);
    }

    /**
     * Check method provided to test if the given value is a valid URL
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isUrl($value) {
        return Url::isValid($value);
    }

    /**
     * Check method provided to test if the given value is a valid phone number
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isPhoneNumber($value) {
        return preg_match('/^0[0-9][-. ]?[0-9]{2}[-. ]?[0-9]{2}[-. ]?[0-9]{2}[-. ]?[0-9]{2}$/', $value);
    }

    /**
     * Check method provided to test if the given value is included in the given list of values
     *
     * @param mixed $value the value to test
     * @param array $authorizedValues the list of authorized values that will be compared to the given value
     * @return boolean
     */
    public function isIn($value, array $authorizedValues) {
        return in_array($value, $authorizedValues);
    }

    /**
     * Check method provided to test if the given value is a valid date
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isDate($value) {
        return Date::isValidString($value);
    }

    /**
     * Check method provided to test if the given value is a valid creadit card number
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isCardNumber($value) {
        return preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/', $value);
    }

    /**
     * Check method provided to test if the given value is a valid float
     *
     * @param mixed $value the value to test
     * @return boolean
     */
    public function isFloat($value) {
        if (strpos($value, '.')) {
            return is_float($value);
        } elseif (strpos($value, ',')) {
            return is_float(str_replace(',', '.', $value));
        }
        return false;
    }

    /**
     * Instantiates and returns the Validator instance corresponding to the given name
     *
     * @param string $name the name refering to the validator
     * @return Validator the validator instance
     */
    public static function create($name) {
        // Get the requested validator class name
        $className = App::getInstance()->getConf()->getValidator($name);

        // Instantiates the corresponding validator
        $validator = new $className();

        // If the validator does not extend the Validator class, throw an exception
        if (!$validator instanceof Validator) {
            throw new ValidatorException('The class [' . $className . '] specified for the validator [' . $name . '] is not a valid Validator class');
        }

        // Setup
        $validator->setup();

        return $validator;
    }

}
