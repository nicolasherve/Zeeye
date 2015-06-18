<?php

namespace Zeeye\Filter;

use Zeeye\App\App;
use Zeeye\App\AppAccessor;
use Zeeye\Dispatcher\Dispatcher;
use Zeeye\Response\DisplayableResponse;
use Zeeye\Response\Response;
use Zeeye\Response\ResponseGenerator;
use Zeeye\Router\Route;
use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\Util\Request\Request;
use Zeeye\Util\Url\UrlGeneratorAccessor;
use Zeeye\View\ViewGenerator;
use Zeeye\Zone\ZoneGenerator;

/**
 * A filter is a component providing callbacks that will be called durig the request's flow
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Filter {

    use ResponseGenerator,
        ViewGenerator,
        ZoneGenerator,
        AppAccessor,
        UrlGeneratorAccessor;

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
     * The list of the filters used in the application
     *
     * @var array
     */
    private static $_instances = null;

    /**
     * Private constructor
     */
    private function __construct() {
        $this->_request = Request::getCurrent();
        $this->_route = Dispatcher::getInstance()->getRoute();
    }

    /**
     * Gets the list of the filters used in the application
     *
     * @var array
     */
    final public static function getAll() {
        if (!isset(self::$_instances)) {
            // Initializes the instances
            self::$_instances = array();

            // For each filter specified in the configuration
            foreach (App::getInstance()->getConf()->getFilters() as $filterClassName) {
                // Instantiate the filter
                $filter = new $filterClassName();

                // If the filter does not extend the Filter class, throw an exception
                if (!$filter instanceof Filter) {
                    throw new InvalidTypeException('The filter is not valid: Filter expected, [%s] given instead', $filter);
                }

                // Setup
                $filter->setup();

                // Store the instance
                self::$_instances[] = $filter;
            }
        }

        return self::$_instances;
    }

    /**
     * Execute optional process right after the instanciation
     *
     * Concrete subclasses can override this method
     */
    public function setup() {
        
    }

    /**
     * Defines processes that take place before the PHP session is started
     */
    public function beforeSession() {
        
    }

    /**
     * Defines processes that take place before the controller's action is processed
     */
    public function beforeAction() {
        
    }

    /**
     * Defines processes that take place after the controller's action is processed
     *
     * @param Response $response the response sent by the action
     */
    public function afterAction(Response $response = null) {
        
    }

    /**
     * Defines processes that take place before the controller's response is processed
     *
     * @param Response $response the response that will be processed
     */
    public function beforeOutput(Response $response) {
        
    }

    /**
     * Defines processes that take place before the response'zones are executed
     *
     * @param DisplayableResponse $response the response that will be output
     */
    public function beforeExecuteZones(DisplayableResponse $response) {
        
    }

    /**
     * Defines processes that take place before the content of the response is generated
     *
     * @param DisplayableResponse $response the response whose content will be generated
     */
    public function beforeGenerateContent(DisplayableResponse $response) {
        
    }

    /**
     * Defines processes that take place before the controller's response content is displayed
     *
     * @param DisplayableResponse $response the response that will be displayed
     */
    public function beforeDisplay(DisplayableResponse $response) {
        
    }

    /**
     * Defines processes that take place after the controller's response content is displayed
     *
     * @param DisplayableResponse $response the response that was displayed
     */
    public function afterDisplay(DisplayableResponse $response) {
        
    }

    /**
     * Defines processes that take place after the controller's response is processed
     *
     * @param Response $response the response that was processed
     */
    public function afterOutput(Response $response) {
        
    }

}
