<?php

namespace Zeeye\Controller;

use Zeeye\App\AppAccessor;
use Zeeye\Response\Response;
use Zeeye\Response\ResponseGenerator;
use Zeeye\Router\Route;
use Zeeye\Router\Router;
use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\Util\Flash\Flash;
use Zeeye\Util\Request\Request;
use Zeeye\Util\Url\UrlGeneratorAccessor;
use Zeeye\View\ViewGenerator;
use Zeeye\Zone\ZoneGenerator;

/**
 * Abstract class for all controllers
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Controller {

    use ResponseGenerator,
        ViewGenerator,
        ZoneGenerator,
        AppAccessor,
        UrlGeneratorAccessor;

    /**
     * The default action name (if not provided)
     * 
     * @var string
     */
    const DEFAULT_ACTION_NAME = 'action';

    /**
     * The action name
     * 
     * This name refers to the controller's method name that will be triggered
     * 
     * @var string
     */
    private $_actionName;

    /**
     * The current request
     * 
     * @var Request
     */
    protected $_request;

    /**
     * The current route
     * 
     * @var Route
     */
    protected $_route;

    /**
     * A Flash instance
     * 
     * @var Flash
     */
    protected $_flash;

    /**
     * A list of controller instances
     * 
     * @var array
     */
    private static $_instances = array();

    /**
     * Private constructor
     */
    private function __construct() {
        $this->_flash = Flash::getInstance();
    }

    /**
     * Execute optional process right after the instanciation
     *
     * Concrete subclasses can override this method
     */
    public function setup() {
        
    }

    /**
     * Get the request
     * 
     * @return Request
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Set the request
     *
     * @param Request $request
     */
    public function setRequest($request) {
        return $this->_request = $request;
    }

    /**
     * Get the route
     * 
     * @return Route
     */
    public function getRoute() {
        return $this->_route;
    }

    /**
     * Execute optional process right before executing each action
     * 
     * Concrete subclasses can override this method
     */
    public function beforeAction() {
        
    }

    /**
     * Execute optional process right after executing each action
     * 
     * Concrete subclasses can override this method
     */
    public function afterAction(Response $response = null) {
        
    }

    private function _checkResponse(Response $response = null) {
        if (!isset($response)) {
            return null;
        }
        if (!$response instanceof Response) {
            throw new InvalidTypeException('The response is not valid: Response or null expected, [%s] given instead', $response);
        }
        return $response;
    }

    /**
     * Execute the action corresponding to the current route
     */
    public function executeAction() {
        // Creates a variable with the action method name
        $methodName = $this->_actionName;

        // We execute the main action and get the related response
        $response = $this->$methodName();

        // Return the response
        return $this->_checkResponse($response);
    }

    /**
     * Execute the beforeAction() of the controller
     * 
     * @return Response|null
     */
    public function executeBeforeAction() {
        // Execute the beforeAction() method
        $response = $this->beforeAction();

        // Return the response
        return $this->_checkResponse($response);
    }

    /**
     * Execute the afterAction() of the controller
     * 
     * @return Response|null
     */
    public function executeAfterAction(Response $response = null) {
        // Execute the afterAction() method
        $response = $this->afterAction($response);

        // Return the response
        return $this->_checkResponse($response);
    }

    /**
     * Get the controller instance corresponding to the given route
     * 
     * @param Route $route the route that will be used to invoke the controller
     * @return Controller
     */
    public static function getInstanceForRoute(Route $route) {
        // If the route is not already registered
        if (!isset(self::$_instances[$route->getName()])) {

            // We extract the controller class name from the route
            $controllerClassName = $route->getControllerName();

            // Instantiates the controller
            $controller = new $controllerClassName();

            // If the controller does not extend the Controller class, throw an exception
            if (!$controller instanceof Controller) {
                throw new InvalidTypeException('The controller specified for the route [' . $route->getName() . '] is not valid: Controller expected, [%s] given instead', $controller);
            }

            // We extract the action name from the route
            $actionName = $route->getActionName();

            // If the action name is null
            if (!isset($actionName)) {
                $actionName = self::DEFAULT_ACTION_NAME;
            }

            // If the action's method does not exist
            if (!method_exists($controller, $actionName)) {
                return self::getInstanceForRoute(Router::getInstance()->getRoute404());
            }

            // Initializes the controller
            $controller->_actionName = $actionName;
            $controller->_route = $route;

            // Setup
            $controller->setup();

            // Store the instance
            self::$_instances[$route->getName()] = $controller;
        }

        // Return the action instance
        return self::$_instances[$route->getName()];
    }

}
