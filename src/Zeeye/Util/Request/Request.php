<?php

namespace Zeeye\Util\Request;

use Zeeye\Util\Url\Url;

/**
 * Class used to get the values sent to the server
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Request extends Url {

    /**
     * The current HTTP request's method
     * 
     * @var string
     */
    private $_method;

    /**
     * The current HTTP request's POST data
     * 
     * @var array
     */
    private $_data;

    /**
     * The current HTTP request's FILES data
     *
     * @var array
     */
    private $_files;

    /**
     * The request's client
     * 
     * @var Client
     */
    private $_client;

    /**
     * The referrer
     * 
     * @var string
     */
    private $_referrer;

    /**
     * The current request
     *
     * @var Request
     */
    private static $_current = null;

    public function __construct() {
        parent::__construct();

        $this->_client = new Client();
        $this->_data = array();
        $this->_files = array();
    }

    /**
     * Returns the current request
     *
     * @return Request the static instance
     */
    public static function getCurrent() {
        if (!isset(self::$_current)) {
            $request = new Request();

            // Common Url parts
            $request->setScheme(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
            $request->setUserName(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null);
            $request->setPassword(isset($_SERVER['PHP_AUTH_PWD']) ? $_SERVER['PHP_AUTH_PWD'] : null);
            $request->setHost($_SERVER['HTTP_HOST']);
            $request->setPort($_SERVER['SERVER_PORT']);
            $questionMarkPosition = strpos($_SERVER['REQUEST_URI'], '?');
            if ($questionMarkPosition === false) {
                $request->setPath($_SERVER['REQUEST_URI']);
            } else {
                $request->setPath(substr($_SERVER['REQUEST_URI'], 0, $questionMarkPosition));
            }
            $request->setParameters($_GET);
            // TODO extract fragment
            //$request->setFragment();
            // Specific request parts
            $request->setData($_POST);
            $request->setMethod(strtoupper($_SERVER['REQUEST_METHOD']));
            $request->setReferrer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);

            // Specific request client parts
            $request->getClient()->setAddress(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
            $request->getClient()->setPort($_SERVER['REMOTE_PORT']);
            $request->getClient()->setAgent($_SERVER['HTTP_USER_AGENT']);

            self::$_current = $request;
        }

        return self::$_current;
    }

    public function getClient() {
        return $this->_client;
    }

    public function setClient(Client $client) {
        $this->_client = $client;
    }

    public function getMethod() {
        return $this->_method;
    }

    public function setMethod($method) {
        $this->_method = $method;
    }

    public function getData() {
        return $this->_data;
    }

    public function setData(array $data) {
        $this->_data = $data;
    }

    public function addData($name, $value) {
        $this->_data[$name] = $value;
    }

    public function getReferrer() {
        return $this->_referrer;
    }

    public function setReferrer($referrer) {
        $this->_referrer = $referrer;
    }

    /**
     * Indicates if the current request is a GET request
     * 
     * @return boolean
     */
    public function isGet() {
        return $this->_method == 'GET';
    }

    /**
     * Indicates if the current request is a POST request
     * 
     * @return boolean
     */
    public function isPost() {
        return $this->_method == 'POST';
    }

    /**
     * Indicates if the current request is a PUT request
     * 
     * @return boolean
     */
    public function isPut() {
        return $this->_method == 'PUT';
    }

    /**
     * Indicates if the current request is a DELETE request
     * 
     * @return boolean
     */
    public function isDelete() {
        return $this->_method == 'DELETE';
    }

    /**
     * Indicates if the current request is a HEAD request
     * 
     * @return boolean
     */
    public function isHead() {
        return $this->_method == 'HEAD';
    }

    /**
     * Indicates if the current request is a OPTIONS request
     * 
     * @return boolean
     */
    public function isOptions() {
        return $this->_method == 'OPTIONS';
    }

    /**
     * Indicates if the current request is a TRACE request
     * 
     * @return boolean
     */
    public function isTrace() {
        return $this->_method == 'TRACE';
    }

    /**
     * Indicates if the current request is a CONNECT request
     * 
     * @return boolean
     */
    public function isConnect() {
        return $this->_method == 'CONNECT';
    }

    /**
     * Retrieve the GET parameter sent with the given name
     * 
     * @param string $name name of the parameter
     * @return mixed
     */
    public function getGet($name) {
        if (!$this->hasParameter($name)) {
            return null;
        }
        return $this->getParameter($name);
    }

    /**
     * Retrieve the POST parameter sent with the given name
     * 
     * @param string $name name of the parameter
     * @return mixed
     */
    public function getPost($name) {
        if (empty($this->_data)) {
            return null;
        }
        if (!isset($this->_data[$name])) {
            return null;
        }
        return $this->_data[$name];
    }

    /**
     * Retrieve the parameter corresponding to the given name
     *
     * @param string $name name of the parameter
     * @return mixed
     */
    public function get($name) {
        if ($this->hasGet($name)) {
            return $this->getGet($name);
        }
        if ($this->hasPost($name)) {
            return $this->getPost($name);
        }
        return null;
    }

    /**
     * Indicates whether a GET parameter with a given name exists
     *
     * @param string $name name of GET parameter
     * @return boolean
     */
    public function hasGet($name) {
        return $this->hasParameter($name);
    }

    /**
     * Indicates whether a POST parameter with a given name exists
     *
     * @param string $name name of the POST parameter
     * @return boolean
     */
    public function hasPost($name) {
        return isset($this->_data[$name]);
    }

    /**
     * Indicates whether a parameter with a given name exists
     *
     * @param string $name name of the parameter
     * @return boolean
     */
    public function has($name) {
        if ($this->hasGet($name)) {
            return true;
        }
        if ($this->hasPost($name)) {
            return true;
        }
        return false;
    }

    /**
     * Indicates if the request has uploaded files
     *
     * @return boolean
     */
    public function hasUploadedFiles() {
        return !empty($this->_files);
    }

    /**
     * Get a list of the uploaded files
     * 
     * @return array
     */
    public function getUploadedFiles() {
        return $this->_files;
    }

    /**
     * Get a the information about the given uploaded file name
     *
     * @return array
     */
    public function getUploadedFile($name) {
        if (!isset($this->_files[$name])) {
            return array();
        }
        return $this->_files[$name];
    }

}
