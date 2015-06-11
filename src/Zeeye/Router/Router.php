<?php

namespace Zeeye\Router;

use Zeeye\App\App;
use Zeeye\Util\Url\Url;

class Router {

    const ERROR_404_ROUTE = 'error404';

    private $_app;
    private $_routes;
    private $_webrootUsername;
    private $_webrootPassword;
    private $_webrootHost;
    private $_webrootPort;
    private $_webrootPath;
    private static $_instance = null;

    private function __construct() {
        
    }

    private function _setupForApp(App $app) {

        $this->_webrootUsername = Url::extractUserName($app->getAppConfiguration()->getWebroot());
        $this->_webrootPassword = Url::extractPassword($app->getAppConfiguration()->getWebroot());
        $this->_webrootHost = Url::extractHost($app->getAppConfiguration()->getWebroot());
        $this->_webrootPort = Url::extractPort($app->getAppConfiguration()->getWebroot());
        $this->_webrootPath = Url::extractPath($app->getAppConfiguration()->getWebroot());
        $this->_app = $app;

        $routes = $app->getRoutesConfiguration()->getRoutes();
        foreach ($routes as $routeName => $routeInfo) {
            $this->connect($routeName, Route::create($routeInfo[0], $routeInfo[1]));
        }
    }

    public function connect($name, Route $route) {
        $route->setName($name);
        $this->_routes[$name] = $route;
    }

    public function getRoutes() {
        return $this->_routes;
    }

    public function getWebrootUsername() {
        return $this->_webrootUsername;
    }

    public function getWebrootPassword() {
        return $this->_webrootPassword;
    }

    public function getWebrootHost() {
        return $this->_webrootHost;
    }

    public function getWebrootPort() {
        return $this->_webrootPort;
    }

    public function getWebrootPath() {
        return $this->_webrootPath;
    }

    /**
     * Return the route corresponding to the given path
     * 
     * @param string $urlPath
     * @return Route
     */
    public function findRouteForPath($urlPath) {

        // Remove part of the given path defined as webroot

        if (strlen($this->_webrootPath) > 0) {
            $urlPath = substr($urlPath, strlen($this->_webrootPath));
        }

        // Foreach configured route
        foreach ($this->_routes as $route) {

            // If the route matches the given path
            if ($route->matches($urlPath)) {
                return $route;
            }
        }

        return $this->getRoute404();
    }

    /**
     * Get the route used for error 404
     * 
     * @return Route
     */
    public function getRoute404() {
        return $this->getRouteByName(self::ERROR_404_ROUTE);
    }

    /**
     * Get the route corresponding to the given name
     *
     * @param string $name
     * @return Route
     */
    public function getRouteByName($name) {
        if (isset($this->_routes[$name])) {
            return $this->_routes[$name];
        }
        throw new RouterException("The route [$name] does not exist");
    }

    /**
     * Generate and return the Url corresponding to the given parameters
     * 
     * @param string $name
     * @param array $parameters
     * @return Url
     */
    public function generateUrlForRoute($name, array $parameters = array()) {

        // Get (a copy of) the route corresponding to the given name
        $route = clone($this->getRouteByName($name));
        // Generate the route path
        $path = $route->generatePath($parameters);
        // Prepend the (optional) webroot path to the route path
        if (strlen($this->_webrootPath) > 0) {
            $path = $this->_webrootPath . $path;
        }

        $url = Url::create();
        $url->setUserName($this->_webrootUsername);
        $url->setPassword($this->_webrootPassword);
        $url->setHost($this->_webrootHost);
        $url->setPort($this->_webrootPort);
        $url->setPath($path);
        $url->clearParameters();
        $url->clearFragment();

        if (isset($parameters['#'])) {
            $url->setFragment($parameters['#']);
            unset($parameters['#']);
        }

        $leftParameters = array_diff_key($parameters, $route->getParameters());
        $url->setParameters($leftParameters);


        return $url;
    }

    /**
     * Return the router instance
     * 
     * @return Router
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            $router = new Router();
            $router->_setupForApp(App::getInstance());

            self::$_instance = $router;
        }
        return self::$_instance;
    }

}
