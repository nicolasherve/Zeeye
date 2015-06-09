<?php

namespace Zeeye\Util\Url;

use Zeeye\App\App;

/**
 * Class used to manage URLs
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Url {

    /**
     * The URL's scheme
     *
     * @var string
     */
    private $_scheme;

    /**
     * The user name to access the resource
     * 
     * @var string 
     */
    private $_userName;

    /**
     * The password to access the resource
     * 
     * @var string
     */
    private $_password;

    /**
     * The URL's host
     *
     * @var string
     */
    private $_host;

    /**
     * The port to access the resource
     * 
     * @var integer 
     */
    private $_port;

    /**
     * The URL's path
     *
     * @var string
     */
    private $_path;

    /**
     * The query string
     * 
     * @var string
     */
    private $_queryString;

    /**
     * The URL's parameters
     *
     * @var array
     */
    private $_parameters;

    /**
     * Indicates whether the parameters changed or not
     * 
     * @var boolean
     */
    private $_haveParametersChanged;

    /**
     * The fragment part of the URL
     *
     * @var string
     */
    private $_fragment;

    /**
     * Constructor
     */
    protected function __construct() {
        $this->_scheme = 'http';
    	$this->_parameters = array();
        $this->_queryString = '';
        $this->_haveParametersChanged = false;
    }

    /**
     * Returns a new instance
     *
     * The given parameter can be string containing the new URL
     *
     * @param Url|string $url the new URL
     * @return Url
     */
    public static function create($url = null) {
        if (!isset($url)) {
            return new Url();
        }
        if ($url instanceof Url) {
            return clone($url);
        }
        if (!is_string($url)) {
            throw new UrlException('The given parameter is not correct to create a new URL object');
        }
        return self::_createFromString($url);
    }

    /**
     * Creates a new instance from the given string
     *
     * @param string $string string containing the information to build the new URL
     * @return a new instance
     */
    private static function _createFromString($string) {
        // If the given URL is not considered valid
        if (!self::isValid($string)) {
            throw new UrlException('The given parameter [' . $string . '] is not a valid URL string');
        }

        // Parses the given URL and extract the different parts
        $url = parse_url($string);

        // If the given URL is not considered well-formed
        if ($url === false) {
            throw new UrlException('The given parameter [' . $string . '] is not a well-formed URL string');
        }

        // Create a new Url instance
        $instance = new Url();

        if (isset($url['scheme'])) {
            $instance->setScheme($url['scheme']);
        }
        if (isset($url['user'])) {
            $instance->setUserName($url['user']);
        }
        if (isset($url['pass'])) {
            $instance->setPassword($url['pass']);
        }
        if (isset($url['host'])) {
            $instance->setHost($url['host']);
        }
        if (isset($url['port'])) {
            $instance->setPort($url['port']);
        }
        if (isset($url['path'])) {
            $instance->setPath($url['path']);
        }
        if (isset($url['query'])) {
            $instance->setQueryString($url['query']);
        }
        if (isset($url['fragment'])) {
            $instance->setFragment($url['fragment']);
        }

        return $instance;
    }

    /**
     * Gets the scheme from the current URL
     *
     * @return string
     */
    public function getScheme() {
        return $this->_scheme;
    }

    /**
     * Sets the scheme for the URL
     *
     * @param string $scheme the scheme
     */
    public function setScheme($scheme) {
        $this->_scheme = $scheme;
    }

    /**
     * Gets the user name from the current URL
     *
     * @return string
     */
    public function getUserName() {
        return $this->_userName;
    }

    /**
     * Indicates whether the URL has a user name or not
     *
     * @return boolean
     */
    public function hasUserName() {
        return !empty($this->_userName);
    }

    /**
     * Sets the user name for the URL
     *
     * @param string $userName the user name
     */
    public function setUserName($userName) {
        $this->_userName = $userName;
    }

    /**
     * Gets the password from the current URL
     *
     * @return string
     */
    public function getPassword() {
        return $this->_password;
    }

    /**
     * Sets the password for the URL
     *
     * @param string $password the password
     */
    public function setPassword($password) {
        $this->_password = $password;
    }

    /**
     * Indicates whether the URL has a password or not
     * 
     * @return boolean
     */
    public function hasPassword() {
        return !empty($this->_password);
    }

    /**
     * Gets the host from the current URL
     *
     * @return string
     */
    public function getHost() {
        return $this->_host;
    }

    /**
     * Sets the host for the URL
     *
     * @param string $host the host
     */
    public function setHost($host) {
        $this->_host = $host;
    }

    /**
     * Gets the port from the current URL
     *
     * @return string
     */
    public function getPort() {
        return $this->_port;
    }

    /**
     * Indicates whether the URL has a port or not
     *
     * @return boolean
     */
    public function hasPort() {
        return !empty($this->_port);
    }

    /**
     * Sets the port for the URL
     *
     * @param string $port the port
     */
    public function setPort($port) {
        $this->_port = $port;
    }

    /**
     * Gets the path from the current URL
     *
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }

    /**
     * Sets the path for the URL
     *
     * @param string $path the path
     */
    public function setPath($path) {
        $this->_path = $path;
    }

    /**
     * Gets the query string from the current URL
     *
     * @return string
     */
    public function getQueryString() {
        if ($this->_haveParametersChanged) {
            $this->_setQueryString($this->_generateQueryStringFromParameters());
            $this->_haveParametersChanged = false;
        }
        return $this->_queryString;
    }

    /**
     * Sets the query string for the URL
     *
     * @param string $queryString the query string
     */
    private function _setQueryString($querystring) {
        $this->_queryString = $querystring;
    }
    
    /**
     * Clear the parameters of the current Url
     */
    public function clearParameters() {
    	$this->_parameters = array();
    	$this->_haveParametersChanged = true;
    }

    /**
     * Add a parameter to the URL
     *
     * An existing parameter with the same given name will be overwritten.
     *
     * @param string $name the name of the parameter
     * @param mixed $value the value of the parameter
     */
    public function addParameter($name, $value) {
        $this->_parameters[$name] = $value;
        $this->_haveParametersChanged = true;
    }

    /**
     * Indicates whether the URL has a parameter with the given name or not
     *
     * @param string $name the name of the parameter
     * @return boolean
     */
    public function hasParameter($name) {
        return isset($this->_parameters[$name]);
    }

    /**
     * Removes the given parameter from the URL parameters'list
     *
     * @param string $name the name of the parameter
     */
    public function removeParameter($name) {
        if (isset($this->_parameters[$name])) {
            unset($this->_parameters[$name]);
            $this->_haveParametersChanged = true;
        }
    }

    /**
     * Gets the value of the given parameter
     *
     * @param string $name the name of the parameter
     * @param boolean $isDecoded indicates whether the retrieved parameter must be URL decoded or not
     * @return mixed
     */
    public function getParameter($name, $isDecoded = false) {
        if (isset($this->_parameters[$name])) {
            if ($isDecoded) {
                return self::decode($this->_parameters[$name]);
            }
            return $this->_parameters[$name];
        }
        return false;
    }

    /**
     * Indicates whether the URL has some parameters or not
     *
     * @return boolean
     */
    public function hasParameters() {
        return !empty($this->_parameters);
    }

    /**
     * Gets the parameters from the current URL
     *
     * @return array
     */
    public function getParameters() {
        return $this->_parameters;
    }

    /**
     * Sets the parameters for the URL
     *
     * @param array $parameters parameters of the URL
     */
    public function setParameters(array $parameters) {
        $this->_parameters = $parameters;
        $this->_haveParametersChanged = true;
    }

    /**
     * Indicates whether the URL has some fragment or not
     *
     * @return boolean
     */
    public function hasFragment() {
        return !empty($this->_fragment);
    }

    /**
     * Clear the fragment of the current Url
     */
    public function clearFragment() {
    	$this->_fragment = '';
    }
    
    /**
     * Get the fragment of the URL
     * 
     * @return string
     */
    public function getFragment() {
        return $this->_fragment;
    }

    /**
     * Set the fragment for the URL
     * 
     * @param string $string the fragment value
     */
    public function setFragment($string) {
        $this->_fragment = $string;
    }

    /**
     * Generates the query string for the current URL
     *
     * @param boolean $isEncoded indicates whether the retrieved parameters must be URL-encoded or not
     * @return string
     */
    private function _generateQueryStringFromParameters($isEncoded = false) {
        return self::generateQueryStringFromParameters($this->_parameters, $isEncoded);
    }

    /**
     * Returns the URL in a string format
     *
     * @param boolean $isEncoded indicates whether the retrieved parameters must be URL-encoded or not
     * @return string
     */
    public function toString($isEncoded = false) {

        // Build the URL
        $string = $this->getScheme() . '://';

        // If a user name is specified
        if ($this->hasUserName()) {
            $string .= $this->getUserName();
            // If a password is specified
            if ($this->hasPassword()) {
                $string .= ':' . $this->getPassword();
            }
            $string .= '@';
        }

        // Append the host part
        $string .= $this->getHost();

        // If a port is specified
        if ($this->hasPort()) {
            $string .= ':' . $this->getPort();
        }

        // Append the path to the URL
        $string .= $this->getPath();

        // If a querystring exists
        if ($this->hasParameters()) {
            $string .= '?' . $this->getQueryString($isEncoded);
        }

        // If a fragment exists
        if ($this->hasFragment()) {
            $string .= '#' . $this->_getFragment();
        }

        return $string;
    }

    /**
     * Indicates whether the given URL string is valid or not
     *
     * Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
     *
     * @param string $url URL in a string format
     * @return boolean
     */
    public static function isValid($url) {
        if (!preg_match(
                        '~^
	 
	        # scheme
	        [-a-z0-9+.]++://
	 
	        # username:password (optional)
	        (?:
	                [-a-z0-9$_.+!*\'(),;?&=%]++   # username
	            (?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
	            @
	        )?
	 
	        (?:
	            # ip address
	            \d{1,3}+(?:\.\d{1,3}+){3}+
	 
	            | # or
	 
	            # hostname (captured)
	            (
	                     (?!-)[-a-z0-9]{1,63}+(?<!-)
	                (?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
	            )
	        )
	 
	        # port (optional)
	        (?::\d{1,5}+)?
	 
	        # path (optional)
	        (?:/.*)?
	 
	        $~iDx', $url, $matches)) {
            return false;
        }

        // We matched an IP address
        if (!isset($matches[1])) {
            return true;
        }

        // Check maximum length of the whole hostname
        // http://en.wikipedia.org/wiki/Domain_name#cite_note-0
        if (strlen($matches[1]) > 253) {
            return false;
        }

        // An extra check for the top level domain
        // It must start with a letter
        $tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
        return ctype_alpha($tld[0]);
    }

    /**
     * Extracts the scheme from the given URL
     *
     * @param string $url the URL from which we extract the scheme
     * @return string
     */
    public static function extractScheme($url) {
        $scheme = parse_url($url, \PHP_URL_SCHEME);
        if (empty($scheme)) {
        	return '';
        }
        return $scheme;
    }

    /**
     * Extracts the username from the given URL
     *
     * @param string $url the URL from which we extract the username
     * @return string
     */
    public static function extractUserName($url) {
        $userName = parse_url($url, \PHP_URL_USER);
        if (empty($userName)) {
        	return '';
        }
        return $userName;
    }

    /**
     * Extracts the password from the given URL
     *
     * @param string $url the URL from which we extract the password
     * @return string
     */
    public static function extractPassword($url) {
        $password = parse_url($url, \PHP_URL_PASS);
        if (empty($password)) {
        	return '';
        }
        return $password;
    }

    /**
     * Extracts the host from the given URL
     *
     * @param string $url the URL from which we extract the host
     * @return string
     */
    public static function extractHost($url) {
        $host = parse_url($url, \PHP_URL_HOST);
        if (empty($host)) {
        	return '';
        }
        return $host;
    }

    /**
     * Extracts the port from the given URL
     *
     * @param string $url the URL from which we extract the port
     * @return string
     */
    public static function extractPort($url) {
        $port = parse_url($url, \PHP_URL_PORT);
        if (empty($port)) {
        	return '';
        }
        return $port;
    }

    /**
     * Extracts the path from the given URL
     *
     * @param string $url the URL from which we extract the path
     * @return string
     */
    public static function extractPath($url) {
        $path = parse_url($url, \PHP_URL_PATH);
        if (empty($path)) {
        	return '';
        }
        return $path;
    }

    /**
     * Extract the query string from the given URL
     *
     * @param string $url the URL we want to extract the query string from
     * @return string
     */
    public static function extractQueryString($url) {
        $queryString = parse_url($url, \PHP_URL_QUERY);
        if (empty($queryString)) {
        	return '';
        }
        return $queryString;
    }

    /**
     * Extracts the parameters from the given URL
     *
     * @param string $url the URL from which we extract the parameters list
     * @param boolean $isEncoded indicates whether the retrieved parameters must be URL encoded or not
     * @return array
     */
    public static function extractParameters($url, $isEncoded = false) {
        $queryString = parse_url($url, \PHP_URL_QUERY);
        $parameters = array();
        if (empty($queryString)) {
            return $parameters;
        }
        foreach (explode('&', $queryString) as $paramString) {
            if (!(preg_match_all('/(.*)=([^#]*)/', $paramString, $matches))) {
                break;
            }
            if ($isEncoded) {
                $parameters[$matches[1][0]] = self::encode($matches[2][0]);
            } else {
                $parameters[$matches[1][0]] = $matches[2][0];
            }
        }
        return $parameters;
    }

    /**
     * Extract the fragment from the given URL
     *
     * @param string $url the URL we want to extract the fragment from
     * @return string
     */
    public static function extractFragment($url) {
        $fragment = parse_url($url, \PHP_URL_FRAGMENT);
        if (empty($fragment)) {
        	return '';
        }
        return $fragment;
    }

    /**
     * Generate the query string corresponding to the given list of parameters
     *
     * @param boolean $isEncoded indicates whether the generated parameters must be URL-encoded or not
     * @param array $parameters the parameters to parse
     * @return string
     */
    public static function generateQueryStringFromParameters(array $parameters, $isEncoded = false) {
        $builtParameters = array();
        foreach ($parameters as $name => $value) {
            if ($isEncoded) {
                $builtParameters[] = $name . '=' . self::encode($value);
            } else {
                $builtParameters[] = $name . '=' . $value;
            }
        }
        return implode('&', $builtParameters);
    }

    /**
     * Encodes a string a a URL-safe format
     *
     * All non alphanum characters (except -_.~) will be replaced by their %xx equivalent
     *
     * @param string $string string to encode
     * @return string
     */
    public static function encode($string) {
        return rawurlencode($string);
    }

    /**
     * Decodes a string from an URL encoded format
     *
     * @param string $string URL encoded string
     * @return string
     */
    public static function decode($string) {
        return rawurldecode($string);
    }

    /**
     * Indicates whether a given IP address is valid or not
     *
     * @param string $ip IP address
     * @return boolean
     */
    public static function isValidIp($ip) {
        return preg_match('/^(?\d\d?|2[0-4]\d|25[0-5]).(?\d\d?|2[0-4]\d|25[0-5]).(?\d\d?|2[0-4]\d|25[0-5]).(?\d\d?|2[0-4]\d|25[0-5])$/', $ip);
    }

    /**
     * Generate the slug corresponding to the given string
     * 
     * TODO Use String operations for html encoding
     * @param string $string the string to "sluggify"
     * @param string $charset the charset of the string
     * @return string
     */
    public static function generateSlug($string, $charset = null) {
        if (!isset($charset)) {
            $charset = App::getInstance()->getDefaultCharset();
        }
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, $charset)), ENT_QUOTES, $charset)), '-'));
    }

    /**
     * Normalize the given URL string and returns it
     * 
     * See http://en.wikipedia.org/wiki/URL_normalization
     * 
     * TODO Use String operations for regex
     * @param string $url
     * @return string
     */
    public static function normalize($url) {
        // Create a Url instance
        $url = self::create($url);

        // 1) Make sure the scheme and host are lowercased
        $url->setScheme(strtolower($url->getScheme()));
        $url->setHost(strtolower($url->getHost()));

        // 2) Make sure letters in escape sequences are uppercased
        if ($url->hasParameters()) {
            $parameters = $url->getParameters();
            foreach ($parameters as $name => $value) {
                $parameters[$name] = preg_replace_callback('/%([\dABCDEF]{2})/i', 'strtolower($1)', $value);
            }
            $url->setParameters($parameters);
        }

        // 3) Decode eventual encoded octets of unreserved characters
        $string = $url->toString();
        if (preg_match('/[^\?]%[\dABCDEF]{2}/i', $url)) {
            $withoutQueryString = substr($string, 0, strpos($string, '?'));
            $withoutQueryString = self::decode($withoutQueryString);
            if ($url->hasParameters()) {
                $string .= '?' . $url->getQueryString();
            }
        }

        return $string;
    }

}
