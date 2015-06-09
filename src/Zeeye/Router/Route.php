<?php

namespace Zeeye\Router;

class Route {

    private $_name;
    private $_routePattern;
    private $_parameters;
    private $_controllerName;
    private $_actionName;
    private $_regexpPattern;

    private function __construct($routePattern, $controllerPart) {
        $this->_routePattern = $routePattern;
        $this->_extractControllerAndActionNames($controllerPart);
        $this->_generateRegexpPattern();
    }

    private function _extractControllerAndActionNames($controllerPart) {
        $doubleColonPosition = strpos($controllerPart, '::');
        if ($doubleColonPosition !== false) {
            $this->_controllerName = substr($controllerPart, 0, $doubleColonPosition);
            $this->_actionName = substr($controllerPart, $doubleColonPosition + 2);
        } else {
            $this->_controllerName = $controllerPart;
        }
    }

    public static function create($routePattern, $controllerPart) {
        return new Route($routePattern, $controllerPart);
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function getRoutePattern() {
        return $this->_routePattern;
    }

    public function getParameters() {
        return $this->_parameters;
    }

    public function getControllerName() {
        return $this->_controllerName;
    }

    public function getActionName() {
        return $this->_actionName;
    }

    public function hasParameters() {
        return !empty($this->_parameters);
    }

    public function has($name) {
        return isset($this->_parameters[$name]) && $this->_parameters[$name]->hasValue();
    }

    public function get($name) {
        if (isset($this->_parameters[$name])) {
            return $this->_parameters[$name]->getvalue();
        }
        return null;
    }

    private function _setupParameters() {

        if (isset($this->_parameters)) {
            return;
        }

        $this->_parameters = array();

        $matches = array();

        if (preg_match_all('#\{([a-z0-9-_]+)(:([^/]+))?\}#i', $this->_routePattern, $matches)) {
            $i = 0;
            foreach ($matches[1] as $name) {

                $parameter = new RouteParameter($name);

                if (isset($matches[3][$i]) && !empty($matches[3][$i])) {
                    $parameter->setRegexp($matches[3][$i]);
                }

                $this->_parameters[$name] = $parameter;

                $i++;
            }
        }
    }

    private function _generateRegexpPattern() {

        $this->_setupParameters();

        $from = array();
        $to = array();

        foreach ($this->_parameters as $parameter) {
            // Translating short-form route pattern into regexp pattern
            $from[] = '{' . $parameter->getName() . '}';
            $to[] = '(?<' . $parameter->getName() . '>' . $parameter->getRegexp() . ')';

            // Translating explicit-form route pattern into regexp pattern
            $from[] = '{' . $parameter->getName() . ':' . $parameter->getRegexp() . '}';
            $to[] = '(?<' . $parameter->getName() . '>' . $parameter->getRegexp() . ')';
        }

        $this->_regexpPattern = str_replace($from, $to, $this->_routePattern);
    }

    /**
     * Indicates if the route matches the given path
     * 
     * @param string $urlPath
     * @return boolean
     */
    public function matches($urlPath) {
        if ($this->getRegexpPattern() == '') {
            return false;
        }

        if (preg_match('#^' . $this->getRegexpPattern() . '$#', $urlPath, $matches)) {
            unset($matches[0]);
            $this->_assignMatchedValuesToParameters($matches);

            return true;
        }

        return false;
    }

    private function _assignMatchedValuesToParameters(array $matches) {
        foreach ($this->_parameters as $parameterName => $parameter) {

            // If the parameter has been matched through the regexp
            if (isset($matches[$parameterName])) {
                $this->addParameter($parameterName, $matches[$parameterName]);
            }
        }
    }

    /**
     * Generate the route's path with the given parameters
     * 
     * @param array $parameters
     * @return string
     */
    public function generatePath(array $parameters = array()) {
        $this->_setupParameters();

        // If the route has no parameter
        if (!$this->hasParameters()) {
            return $this->_routePattern;
        }

        $from = array();
        $to = array();

        // For each route parameter
        foreach ($this->getParameters() as $parameterName => $parameter) {
            // If a route's parameter was not provided
            if (!isset($parameters[$parameterName])) {
                throw new RouteException("The route [" . $this->getName() . "] expects a parameter named [$parameterName] which is missing");
            }

            // Translating short-form route pattern into regexp pattern
            $from[] = '{' . $parameterName . '}';
            $to[] = $parameters[$parameterName];

            // Translating explicit-form route pattern into regexp pattern
            $from[] = '{' . $parameterName . ':' . $parameter->getRegexp() . '}';
            $to[] = $parameters[$parameterName];
        }

        return str_replace($from, $to, $this->_routePattern);
    }

    public function getRegexpPattern() {
        return $this->_regexpPattern;
    }

    public function addParameter($name, $value) {
        if (!isset($this->_parameters[$name])) {
            throw new RouteException("Trying to set an undefined parameter [$name] for the route [" . $this->getName() . "]");
        }

        $this->_parameters[$name]->setvalue($value);

        // If the parameter is the action
        if ($name == 'action') {
            $this->_actionName = $value;
        }
    }

}
