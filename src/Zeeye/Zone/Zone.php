<?php

namespace Zeeye\Zone;

use Zeeye\App\App;
use Zeeye\App\AppAccessor;
use Zeeye\Cache\CachedContent;
use Zeeye\Dispatcher\Dispatcher;
use Zeeye\Router\Route;
use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\Util\Request\Request;
use Zeeye\Util\Url\UrlGeneratorAccessor;
use Zeeye\View\View;
use Zeeye\View\ViewGenerator;

/**
 * Class used to managed zones, "mini controllers" that generate content to be displayed
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Zone {

    use CachedContent,
        ViewGenerator,
        ZoneGenerator,
        AppAccessor,
        UrlGeneratorAccessor;

    /**
     * The list of parameters used to populate the zone
     *
     * @var array
     */
    private $_parameters;

    /**
     * The current request
     *
     * @var Request
     */
    protected $_request;

    /**
     * The currently processed route
     *
     * @var Route
     */
    protected $_route;

    /**
     * Default constructor
     */
    private function __construct() {
        $this->_parameters = array();
        $this->_request = Request::getCurrent();
        $this->_route = Dispatcher::getInstance()->getRoute();
    }

    /**
     * Execute optional process right after the instanciation
     *
     * Concrete subclasses can override this method
     */
    public function setup() {
        
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
        $class = __CLASS__;
        // Transform the list of identifiers into a readable format
        $identifiersAsAString = md5(serialize($identifiers));

        // Generate the cache key
        return 'zone-' . $class . '-' . $identifiersAsAString;
    }

    /**
     * Define processes that will be used before the Zone is indeed executed
     * 
     * The processes will be executed even with the use of cache
     */
    public function beforeExecute() {
        
    }

    /**
     * Define the main processes to perform
     * 
     * @return View|string
     */
    abstract protected function _execute();

    /**
     * Sets a parameter for the Zone
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
        if (isset($this->_parameters[$name])) {
            return $this->_parameters[$name];
        }
        return null;
    }

    /**
     * Execute the zone, build the content and returns it
     * 
     * @return View|string
     */
    public function execute() {
        // Process the beforeExecute() method
        $this->beforeExecute();

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

        // Generate the content
        $content = $this->_execute();

        // If the content is not a View instance nor a string
        if (!$content instanceof View && !is_string($content)) {
            throw new InvalidTypeException('The return value is not valid: string or View expected, [%s] given instead', $content);
        }

        // If the cache is enabled
        if ($this->isCacheEnabled()) {
            die('cache is enabled');
// Create the corresponding cache
            $this->cacheContent($cacheKey, $content);
        }

        return $content;
    }

    /**
     * Instantiates and returns the Zone instance corresponding to the given name
     * 
     * @param string $name the name refering to the zone
     * @return Zone the zone instance
     */
    public static function create($name) {
        // Get the requested zone class name
        $className = App::getInstance()->getAppConfiguration()->getZone($name);

        // Instantiates the corresponding zone
        $zone = new $className();

        // If the zone does not extend the Zone class, throw an exception
        if (!$zone instanceof Zone) {
            throw new ZoneException('The class [' . $className . '] specified for the zone [' . $name . '] is not a valid Zone class');
        }

        // Setup
        $zone->setup();

        return $zone;
    }

}
