<?php
namespace Zeeye\Util\Cookie;

use Zeeye\App\App;
/**
 * Class used to manage the cookies
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class Cookie {	
	private $_name;
	private $_value;
	private $_lifeTime;
	private $_path;
	private $_domain;
	private $_isSecure;
	private $_isHttpOnly;
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function setValue($value) {
		$this->_value = $value;
	}
	
	public function getValue() {
		return $this->_value;
	}
	
	public function setLifeTime($lifeTime) {
		$this->_lifeTime = $lifeTime;
	}
	
	public function getLifeTime() {
		return $this->_lifeTime;
	}
	
	public function setPath($path) {
		$this->_path = $path;
	}
	
	public function getPath(){
		return $this->_path;
	}
	
	public function setDomain($domain) {
		$this->_domain = $domain;
	}
	
	public function getDomain() {
		return $this->_domain;
	}
	
	public function setIsSecure($isSecure) {
		$this->_isSecure = $isSecure;
	}
	
	public function getIsSecure() {
		return $this->_isSecure;
	}
	
	public function setIsHttpOnly($isHttpOnly) {
		$this->_isHttpOnly = $isHttpOnly;
	}
	
	public function getIsHttpOnly() {
		return $this->_isHttpOnly;
	}
	
	/**
	 * Create a cookie with the given parameters
	 *
	 * @param string $name name of the cookie
	 * @param string $value the value contained in the cookie
	 * @param integer $expire the timestamp expiration date of the cookie, in seconds
	 * @param string $path the path concerned by the cookie
	 * @param string $domain the domain concerned by the cookie
	 * @param boolean $isSecure indicates if the cookie is accessible only through a secure connection
	 * @param boolean $isHttpOnly indicates if the cookie is accessible only through the HTTP request
	 */
	public static function create($name, $value, $expire=0, $path='/', $domain='', $isSecure=false, $isHttpOnly=false) {
		// If the expiration is not 0 (which means session cookie)
		if ($expire > 0) {
			// We consider the given expiration is a number of seconds after now
			$expire = time() + $expire;
		}
		
		// Create the new cookie
		$cookie = new Cookie();
		$cookie->setName($name);
		$cookie->setValue($value);
		$cookie->setLifeTime($expire);
		$cookie->setPath($path);
		$cookie->setDomain($domain);
		$cookie->setIsSecure($isSecure);
		$cookie->setIsHttpOnly($isHttpOnly);
		
		return $cookie;
	}
	
	/**
	 * Create a cookie with the given parameters
	 * 
	 * @param string $name name of the cookie
	 * @param string $value the value contained in the cookie
	 * @param integer $expire the timestamp expiration date of the cookie, in seconds
	 * @param string $path the path concerned by the cookie
	 * @param string $domain the domain concerned by the cookie
	 * @param boolean $isSecure indicates if the cookie is accessible only through a secure connection
	 * @param boolean $isHttpOnly indicates if the cookie is accessible only through the HTTP request
	 */
	public static function put($name, $value, $expire=0, $path='/', $domain='', $isSecure=false, $isHttpOnly=false) {
		// If the expiration is not 0 (which means session cookie)
	    if ($expire > 0) {
		    // We consider the given expiration is a number of seconds after now
	        $expire = time() + $expire;
		}
		
		// Set the cookie to the client
	    setcookie($name, $value, $expire, $path, $domain, $isSecure, $isHttpOnly);
	}
	
	/**
	 * Delete the cookie with the given name
	 * 
	 * @param string $name name of the cookie
	 * @param string $path the path concerned by the cookie
	 * @param string $domain the domain concerned by the cookie
	 */
	public static function delete($name, $path='/', $domain='', $isSecure=false, $isHttpOnly=false) {
	    setcookie($name, '', time() - (3600*25), $path, $domain, $isSecure, $isHttpOnly);
	}
	
	/**
	 * Indicates whether a cookie with the given name exists or not
	 * 
	 * @param string $name name of the cookie
	 * @return boolean
	 */
	public static function has($name) {
		return isset($_COOKIE[$name]);
	}
	
	/**
	 * Get the value of the cookie with the given name
	 * 
	 * @param string $name name of the cookie
	 * @return string
	 */
	public static function get($name) {
		if (self::has($name)) {
			return $_COOKIE[$name];
		}
		return null;
	}
	
	/**
	 * Returns an array containing all the available cookies
	 * 
	 * @return array list of the cookies
	 */
	public static function getAll() {
	    if (!empty($_COOKIE)) {
	        return $_COOKIE;
	    }
	    return array();
	}
}