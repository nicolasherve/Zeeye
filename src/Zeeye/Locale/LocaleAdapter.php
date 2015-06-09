<?php
namespace Zeeye\Locale;
/**
 * Interface used for all concrete adapters
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
interface LocaleAdapter {	
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
	public function get($key, $language, array $args=null);
	
	/**
	 * Returns the translation related to the given key and language
	 * 
	 * The translations are stored in a contextual place
	 * 
	 * @param string $dirPath the directory path to the resources file
	 * @param string $key key of the translation
	 * @param string $language the target language of the translation
	 * @param array $args list of the parameters to include in the translation
	 * @return string
	 */
	public function getFromDirPath($dirPath, $key, $language, array $args=null);
}