<?php
namespace Zeeye\Locale\Adapter;

use Zeeye\App\App;
use Zeeye\Locale\LocaleAdapter;
use Zeeye\Locale\Adapter\PhpFileException;
/**
 * This adapter offers a default method to use translations through resources files
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class PhpFile implements LocaleAdapter {
	/**
	 * The name of the directory in which are stored the resources files
	 * 
	 * @var string
	 */
	const DIRECTORY_NAME = 'resources';
	
	/**
	 * The file pattern of the resources files
	 * 
	 * @var string
	 */
	const FILE_PATTERN = 'locale_%s.inc.php';
	
	/**
	 * The list of already translated keys
	 *
	 * @var array
	 */
	private static $_translated = array();
		
	/**
	 * Retrieve the data from the resources file indicated by the given directory path and language
	 * 
	 * The data is added to the current translations
	 * 
	 * @param string $dirPath the directory path to the resources file
	 * @param string $language the required language
	 */
	private static function _retrieveDataFromFile($dirPath, $language) {
		// Generate the complete resources file path
		$resourcesFilePath = $dirPath.self::DIRECTORY_NAME.'/'.sprintf(self::FILE_PATTERN, $language);
		
		// If the requested file does not exist, stop the process
		if (!file_exists($resourcesFilePath)) {
			return;
		}
		
		// Retrieve the locale defined in the expected contextual file
		$locale = array();
		require($resourcesFilePath);
		
		// Put the data in a temporary array
		$data = array();
		$data[$language] = $locale;
		
		// Merge the retrieved data with the existing translated data
		self::$_translated = array_merge(self::$_translated, $data);
	}
	
	/**
	 * Returns the translated string corresponding to the given key and language
	 * 
	 * @param string $key the key indicating the translated string to use
	 * @param string $language the required language
	 * @param array $args an optional list of arguments that will be used to generated the output string
	 * @return string the output translated string
	 */
	private static function _generateFinalString($key, $language, array $args=null) {
		// If there is no translated data, thow an exception
		if (!isset(self::$_translated[$language][$key])) {
			throw new PhpFileException('No translation found for the key ['.$key.'] in language ['.$language.']');
		}
		// If there are some parameters, format the string to include them
		if (!empty($args)){	
			return call_user_func_array('sprintf', array_merge(array(self::$_translated[$language][$key]), $args));
		}
		
		// Returns the translated string
		return self::$_translated[$language][$key];
	}
	
	/**
	 * Returns the translation related to the given key and language
	 * 
	 * The translations are stored in a contextual place
	 * 
	 * @param string $dirPath the directory path to the resources file
	 * @param string $key key of the translation
	 * @param string $language the language that must be used for the translation
	 * @param array $args list of the parameters to include in the translation
	 * @return string
	 */
	public function getFromDirPath($dirPath, $key, $language, array $args=null) {
		// If the requested key does not exist for the requested language, try to retrieve it
		if (!isset(self::$_translated[$language][$key])) {
			self::_retrieveDataFromFile($dirPath, $language);
		}
		// If no key was found from the context path, use the general operation
		if (!isset(self::$_translated[$language][$key])) {
			return self::translate($key, $language, $args);
		}
		// Try to generate the final content string
		return self::_generateFinalString($key, $language, $args);
	}
	
	/**
	 * Returns the translation related to the given key and language
	 *
	 * The translations are stored in a centralized place
	 * 
	 * @param string $key the key refering to the translation we want
	 * @param string $language the target language of the translation
	 * @param array $args list of parameters to include in the translation
	 * @return string
	 */
	public function get($key, $language, array $args=null) {
		// If the requested key does not exist for the requested language, try to retrieve it
		if (!isset(self::$_translated[$language][$key])) {
			self::_retrieveDataFromFile(App::getInstance()->getPath(), $language);
		}
		// Try to generate the final content string
		return self::_generateFinalString($key, $language, $args);
	}
}