<?php

namespace Zeeye\Locale;

use Zeeye\Locale\LocaleAdapter;
use Zeeye\Locale\LocaleException;
use Zeeye\App\App;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Class used to manage the translations
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Locale {

    /**
     * The current adapter used for translations
     * 
     * @var LocaleAdapter
     */
    private static $_adapter = null;

    /**
     * The language code
     * 
     * @var string
     */
    private static $_language = null;

    /**
     * Returns the current language code
     * 
     * @return string
     */
    public static function getLanguage() {
        if (!isset(self::$_language)) {
            self::$_language = App::getInstance()->getConf()->getDefaultLocaleLanguage();
        }
        return self::$_language;
    }

    /**
     * Sets the language to use
     * 
     * @param string $language language code
     */
    public static function setLanguage($language) {
        self::$_language = $language;
    }

    /**
     * Defines the class that will be used as the locale adapter
     */
    private static function _defineAdapter() {
        // If the adapter is already defined
        if (isset(self::$_adapter)) {
            return;
        }

        // Get the adapter class name defined in the configuration
        $adapterClassName = App::getInstance()->getLocaleAdapter();
        if (!isset($adapterClassName)) {
            throw new LocaleException('The locale adapter has to be defined in the configuration');
        }

        // Instantiate the adapter
        $adapter = new $adapterClassName();
        if (!$adapter instanceof LocaleAdapter) {
            throw new InvalidTypeException('The locale adapter is not valid: LocaleAdapter expected, [%s] given instead', $adapter);
        }

        // Assign the adapter
        self::$_adapter = $adapter;
    }

    /**
     * Returns the translation corresponding to the given key
     * 
     * @param string $key key of the translation
     * @param array|string $args list of the parameters to include inthe translation
     * @param string $language the language that must be used for the translation
     * @return string
     */
    public static function get($key, $args = null, $language = null) {
        self::_defineAdapter();
        if (!isset($language)) {
            $language = self::getLanguage();
        }
        if (isset($args) && !is_array($args)) {
            $args = array($args);
        }
        return self::$_adapter->get($key, $language, $args);
    }

    /**
     * Returns the translation corresponding to the given key
     * 
     * This operation is similar to <code>get()</code> except that you can specify
     * the directory path if this has some importance for the adapter you use.
     * 
     * @param string $directoryPath the directory path to the resources file
     * @param string $key key of the translation
     * @param array|string $args list of the parameters to include inthe translation
     * @param string $language the language that must be used for the translation
     * @return string
     */
    public static function getFromDirPath($directoryPath, $key, $args = null, $language = null) {
        self::_defineAdapter();
        if (!isset($language)) {
            $language = self::getLanguage();
        }
        if (isset($args) && !is_array($args)) {
            $args = array($args);
        }
        return self::$_adapter->getFromDirPath($directoryPath, $key, $language, $args);
    }

}
