<?php

namespace Zeeye\View;

use Zeeye\App\AppAccessor;
use Zeeye\Cache\CachedContent;
use Zeeye\Locale\Locale;
use Zeeye\Util\File\File;
use Zeeye\View\Helper\Helper;
use Zeeye\View\Helper\HtmlHelper;
use Zeeye\Zone\Zone;

/**
 * Class used to manage views
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class View {

    use CachedContent,
        AppAccessor;

    /**
     * The default group name (used for CSS and JS links)
     *
     * @var string
     */
    const DEFAULT_GROUP_NAME = 'default';

    /**
     * The list of Javascript links that are included in the page
     *
     * @var array
     */
    private $_jsLinks;

    /**
     * The list of Javascript links that are included in the sub views of the current one
     *
     * @var array
     */
    private $_extraJsLinks;

    /**
     * The list of CSS links that are included in the page
     *
     * @var array
     */
    private $_cssLinks;

    /**
     * The list of CSS links that are included in the sub views of the current one
     *
     * @var array
     */
    private $_extraCssLinks;

    /**
     * The path of the view file directory
     *
     * @var string
     */
    private $_dirPath;

    /**
     * The name of the view file
     *
     * @var string
     */
    private $_fileName;

    /**
     * The list of parameters used to populate the view
     *
     * @var array
     */
    private $_parameters;

    /**
     * Constructor
     *
     * @param string $filePath the path of the view file
     */
    protected function __construct($filePath = null) {
        if (isset($filePath)) {
            $this->_dirPath = dirname($this->getApp()->getPath() . $filePath) . '/';
            $this->_fileName = basename($this->getApp()->getPath() . $filePath);
        } else {
            $this->_dirPath = null;
            $this->_fileName = null;
        }
        $this->_parameters = array();
        $this->_cssLinks = array();
        $this->_extraCssLinks = array();
        $this->_jsLinks = array();
        $this->_extraJsLinks = array();
    }

    /**
     * Factory method to get a view instance
     *
     * @param string $filePath the path of the view file
     * @return View a view instance
     */
    public static function create($filePath = null) {
        return new View($filePath);
    }

    /**
     * Sets a parameter in the view
     *
     * @param string $name the name of the parameter
     * @param mixed $value the value of the parameter
     */
    public function set($name, $value) {
        $this->_parameters[$name] = $value;
    }

    /**
     * Indicates if there is a parameter for the given name
     *
     * @param string $name name of the parameter
     * @return boolean
     */
    public function has($name) {
        return isset($this->_parameters[$name]);
    }

    /**
     * Gets the value of the given parameter
     *
     * @param string $name name of the parameter
     * @return mixed
     */
    public function get($name) {
        return $this->_parameters[$name];
    }

    /**
     * Return the view file path
     * 
     * @return string
     */
    public function getFilePath() {
        return $this->_dirPath . $this->_fileName;
    }

    /**
     * Set the view file path
     * 
     * @param string $filePath
     */
    public function setFilePath($filePath) {
        $this->_dirPath = dirname($this->getApp()->getPath() . $filePath) . '/';
        $this->_fileName = basename($this->getApp()->getPath() . $filePath);
    }

    /**
     * Return the file cache name for the current Zone
     *
     * @param array $identifiers an array containing a unique identification of the file cache name
     * @return string
     */
    public function generateCacheKey(array $identifiers = array()) {
        // The parameters are sorted so that any order can be used by the user to produce the same file cache name
        $identifiers = $this->_parameters;
        ksort($identifiers);
        // Automatically add some specific values to the list of cache identifiers
        $filePath = $this->_dirPath . $this->_fileName;
        // Transform the list of identifiers into a readable format
        $identifiersAsAString = md5(serialize($identifiers));

        // Generate the cache key
        return 'zone-' . $filePath . '-' . $identifiersAsAString;
    }

    /**
     * Parse the parameters and execute each Zone instance in them
     */
    public function executeZones() {
        foreach ($this->_parameters as $name => $value) {
            if ($value instanceof Zone) {
                $this->_parameters[$name] = $value->execute();
            }
        }
    }

    /**
     * Generates the content of the view and returns it
     *
     * @return string
     */
    public function render() {
        // The cache key for the current Zone
        $cacheKey = $this->generateCacheKey();

        // If the cache is enabled
        if ($this->isCacheEnabled()) {
            // Get the eventual cached content
            $content = $this->getCachedContent($cacheKey);
            // If the cached content is not null
            if (isset($content)) {
                return $content;
            }
        }

        // Check that the file path is given
        if (!isset($this->_dirPath) || !isset($this->_fileName)) {
            throw new ViewException("A render() operation was called on a View instance with no defined file path");
        }

        // Check that the view file exists
        File::checkFilePath($this->_dirPath . $this->_fileName);

        // We extract parameters to be able to use them as variables in the view
        if (!empty($this->_parameters)) {
            // Replace the Zone parameters by their rendered value for the view
            $this->executeZones();
            // For each parameter
            foreach ($this->_parameters as $key => $value) {
                // If the current parameter is an instance of View
                if ($value instanceof View) {
                    // Replace the object instance by its rendered value for the view
                    $this->_parameters[$key] = $value->render();
                    // Adds the eventual CSS links from the given view to the current one
                    $this->_extraCssLinks = \array_merge_recursive($this->_extraCssLinks, $value->_cssLinks, $value->_extraCssLinks);
                    // Adds the eventual JS links from the given view to the current one
                    $this->_extraJsLinks = \array_merge_recursive($this->_extraJsLinks, $value->_jsLinks, $value->_extraJsLinks);
                }
            }
            // Extract the parameters as actual variables for the current view
            extract($this->_parameters);
        }

        // Create the HtmlHelper instance that will be available in the view file
        $html = $this->_createHelper(new HtmlHelper());

        // We start alternative output
        ob_start();
        // Requires the view file
        require($this->_dirPath . $this->_fileName);
        // We register the content
        $content = ob_get_contents();
        // We stop the alternative output
        ob_end_clean();

        // If the cache is enabled
        if ($this->isCacheEnabled()) {
            // Create the corresponding cache
            $this->cacheContent($cacheKey, $content);
        }

        // The generated content is returned
        return $content;
    }

    /**
     * Imports the given file path into the current view
     *
     * @param string $filePath path of the view file to include
     * @param array $parameters a list of variables to use in the given path file
     */
    public function import($filePath, array $parameters = array()) {
        // Create a View instance for the given file
        $view = View::create($filePath);

        // Set the parameters from the current view file
        if (!empty($this->_parameters)) {
            foreach ($this->_parameters as $name => $value) {
                $view->set($name, $value);
            }
        }

        // Set eventual extra parameters
        if (!empty($parameters)) {
            foreach ($parameters as $name => $value) {
                $view->set($name, $value);
            }
        }

        // Display the rendered view
        echo $view->render();

        // Adds the eventual CSS links from the given view to the current one
        $this->_extraCssLinks = \array_merge_recursive($this->_extraCssLinks, $view->_cssLinks, $view->_extraCssLinks);
        // Adds the eventual JS links from the given view to the current one
        $this->_extraJsLinks = \array_merge_recursive($this->_extraJsLinks, $view->_jsLinks, $view->_extraJsLinks);
    }

    /**
     * Get the CSS links of the view
     *
     * @param string $groupName the group name associated to the files
     * @return array
     */
    public function getCssLinks($groupName = null) {
        $finalGroupName = self::_getGroupName($groupName);
        if (!isset($this->_cssLinks[$finalGroupName])) {
            return array();
        }
        return $this->_cssLinks[$finalGroupName];
    }

    /**
     * Get the JS links of the view
     *
     * @param string $groupName the group name associated to the files
     * @return array
     */
    public function getJsLinks($groupName = null) {
        $finalGroupName = self::_getGroupName($groupName);
        if (!isset($this->_jsLinks[$finalGroupName])) {
            return array();
        }
        return $this->_jsLinks[$finalGroupName];
    }

    /**
     * Get the CSS links of the sub views
     *
     * @param string $groupName the group name associated to the files
     * @return array
     */
    public function getExtraCssLinks($groupName = null) {
        $finalGroupName = self::_getGroupName($groupName);
        if (!isset($this->_extraCssLinks[$finalGroupName])) {
            return array();
        }
        return $this->_extraCssLinks[$finalGroupName];
    }

    /**
     * Get the JS links of the sub views
     *
     * @param string $groupName the group name associated to the files
     * @return array
     */
    public function getExtraJsLinks($groupName = null) {
        $finalGroupName = self::_getGroupName($groupName);
        if (!isset($this->_extraJsLinks[$finalGroupName])) {
            return array();
        }
        return $this->_extraJsLinks[$finalGroupName];
    }

    /**
     * Return the group name to use depending on the given group name
     *
     * @param string $groupName the group name to check
     * @return string the group name to use
     */
    private static function _getGroupName($groupName) {
        if (!isset($groupName)) {
            return self::DEFAULT_GROUP_NAME;
        }
        return $groupName;
    }

    /**
     * Adds the given CSS link to the current page
     *
     * @param array $link link to the CSS file
     * @param string $groupName the group name associated to the file
     */
    public function addCssLink($link, $groupName = null) {
        $this->_cssLinks[self::_getGroupName($groupName)][$link['url']] = $link;
    }

    /**
     * Adds the given JS link to the current page
     *
     * @param array $link link to the JS file
     * @param string $groupName the group name associated to the file
     */
    public function addJsLink($link, $groupName = null) {
        $this->_jsLinks[self::_getGroupName($groupName)][$link['url']] = $link;
    }

    /**
     * Remove all the existing CSS links of the current page
     */
    public function clearCssLinks($groupName = null) {
        $this->_cssLinks[self::_getGroupName($groupName)] = array();
    }

    /**
     * Remove all the existing JS links of the current page
     */
    public function clearJsLinks($groupName = null) {
        $this->_jsLinks[self::_getGroupName($groupName)] = array();
    }

    /**
     * Returns the translation corresponding to the given key
     *
     * @param string $key key of the translation
     * @param mixed $args parameters to include in the translation
     * @param string $language the language that must be used for the translation
     * @return string
     */
    public function locale($key, $args = null, $language = null) {
        return Locale::getFromDirPath($this->_dirPath, $key, $args, $language);
    }

    /**
     * Returns an Helper instance corresponding to the given name
     *
     * @param string $helperName the Helper name as defined in the configuration
     * @return Helper
     */
    public function createHelper($helperName) {
        return Helper::create($helperName, $this);
    }

    private function _createHelper(Helper $helper) {
        $helper->setView($this);
        $helper->setup();

        return $helper;
    }

}
