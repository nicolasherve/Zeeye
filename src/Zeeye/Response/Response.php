<?php

namespace Zeeye\Response;

/**
 * Abstract class for all responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Response {

    /**
     * The liste of headers associated to the response
     * 
     * @var array
     */
    private $_headers;

    /**
     * The HTTP status code
     * 
     * @var integer
     */
    private $_statusCode;

    /**
     * The default HTTP status messages
     * 
     * @var array
     */
    private static $_defaultStatusMessages = array(
        200 => '200 Ok',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        307 => '307 Temporary Redirect',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        500 => '500 Internal Server Error',
        503 => '503 Service Unavailable'
    );

    /**
     * Constructor
     */
    protected function __construct() {
        $this->_headers = array();
        $this->_statusCode = 200;
    }

    /**
     * Adds the given header for the response
     * 
     * @param string $name header's name
     * @param string $value header's value
     */
    final public function setHeader($name, $value) {
        $this->_headers[strtolower($name)] = $value;
    }

    /**
     * Sets the response's HTTP status
     * 
     * @param integer $code the response's HTTP status code
     */
    final public function setStatus($code) {
        $this->_statusCode = $code;
    }

    /**
     * Return the response's HTTP status code
     * 
     * @return integer the response's HTTP status code
     */
    final public function getStatusCode() {
        return $this->_statusCode;
    }

    /**
     * Send the headers for the response
     */
    final protected function _sendHeaders() {
        header('HTTP/1.1 ' . self::$_defaultStatusMessages[$this->_statusCode]);
        foreach ($this->_headers as $type => $content) {
            header($type . ':' . $content);
        }
    }

    /**
     * Executes the response
     * 
     * The subclasses must redefine this method
     * 
     * @return void
     */
    abstract public function output();
}
