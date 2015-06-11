<?php

namespace Zeeye\Dispatcher;

use Zeeye\Controller\Controller;
use Zeeye\Filter\Filter;
use Zeeye\Response\Response;
use Zeeye\Router\Route;
use Zeeye\Router\Router;
use Zeeye\Util\Request\Request;
use Zeeye\Util\Session\Session;

/**
 * Frontal controller of the application
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Dispatcher {

    /**
     * List of instances
     * 
     * @var array
     */
    private static $_instances = array();

    /**
     * List of the registered filters
     * 
     * @var array
     */
    private $_filters;

    /**
     * The current request
     * 
     * @var Request
     */
    private $_request;

    /**
     * The current route in use
     * 
     * @var Route
     */
    private $_route;

    /**
     * The list of processed routes
     * 
     * @var array
     */
    private $_routes;

    /**
     * Constructor
     */
    private function __construct() {
        $this->_routes = array();
    }

    /**
     * Get the current request
     * 
     * @return Request
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Set the request to be processed
     * 
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->_request = $request;

        // Reset the current used route
        $this->_route = null;
        // Reset the current list of processed routes
        $this->_routes = array();
    }

    /**
     * Get the current processed route
     * 
     * @return Route
     */
    public function getRoute() {
        return $this->_route;
    }

    /**
     * Get the processed routes
     *
     * @return array
     */
    public function getRoutes() {
        return $this->_routes;
    }

    /**
     * Set the route to be processed
     *
     * @param Route $route
     */
    public function setRoute(Route $route) {
        $this->_route = $route;

        // Store the route
        $this->_routes[] = $route;
    }

    /**
     * Launch the framework and executes the global process
     */
    public function process() {
        // Execute the action and get the response
        $response = $this->action();

        // If the obtained response is not null
        if (isset($response)) {
            // We output the response
            $this->outputResponse($response);
        }
    }

    /**
     * Execute the action and returns the corresponding response, if any
     */
    public function action() {
        // If no route is provided
        if (!isset($this->_route)) {

            // If no request is provided
            if (!isset($this->_request)) {
                // Get the current request
                $this->setRequest(Request::getCurrent());
            }

            // Get the router
            $router = Router::getInstance();

            // Find the route for the given request
            $route = $router->findRouteForPath($this->_request->getPath());

            // Set the route to process
            $this->setRoute($route);
        }

        // Initialize and get filters
        $this->_filters = Filter::getAll();

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filterResponse = $filter->beforeSession();
            if (isset($filterResponse) && $filterResponse instanceof Response) {
                return $this->outputResponse($filterResponse);
            }
        }

        // Start the session
        Session::start();

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filterResponse = $filter->beforeAction();
            if (isset($filterResponse) && $filterResponse instanceof Response) {
                return $this->outputResponse($filterResponse);
            }
        }

        // Instantiate the controller
        $controller = Controller::getInstanceForRoute($this->_route);
        $controller->setRequest($this->_request);

        // We execute the beforeAction() method of the controller
        $beforeActionResponse = $controller->executeBeforeAction();
        if (isset($beforeActionResponse)) {
            return $this->outputResponse($beforeActionResponse);
        }

        // We execute the action and get the related response
        $response = $controller->executeAction();

        // We execute the afterAction() method of the controller
        $afterActionResponse = $controller->executeAfterAction($response);
        if (isset($afterActionResponse)) {
            return $this->outputResponse($afterActionResponse);
        }

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filterResponse = $filter->afterAction($response);
            if (isset($filterResponse) && $filterResponse instanceof Response) {
                return $this->outputResponse($filterResponse);
            }
        }

        return $response;
    }

    /**
     * Returns the response of the requested action
     * 
     * @param Response $response the response to output
     */
    public function outputResponse(Response $response) {
        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->beforeOutput($response);
        }

        // We output the response
        $response->output();

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->afterOutput($response);
        }
    }

    /**
     * Returns an instance of the class
     * 
     * An optional parameter can be specified as the name of the instance
     * 
     * @param string $id unique identifier of the instance
     * @return Dispatcher
     */
    public static function getInstance($id = 'default') {
        if (!isset(self::$_instances[$id])) {
            self::$_instances[$id] = new Dispatcher();
        }
        return self::$_instances[$id];
    }

}
