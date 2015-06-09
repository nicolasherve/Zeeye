<?php
namespace Zeeye\Util\Session;

use Zeeye\Util\Session\SessionAdapter;
use Zeeye\Util\Cookie\Cookie;
/**
 * Represents a session cookie
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class SessionCookie extends Cookie {
	
	/**
	 * The default session's cookie name
	 *
	 * @var string
	 */
	const DEFAULT_NAME = 'zeeye';
	
	/**
	 * The default session's cookie lifetime
	 *
	 * @var string
	 */
	const DEFAULT_LIFETIME = '0';
	
	const DEFAULT_PATH = '/';
	
	const DEFAULT_DOMAIN = '';
	
	const DEFAULT_SECURE = '0';
	
	const DEFAULT_HTTP_ONLY = '1';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->setName(self::DEFAULT_NAME);
		$this->setLifeTime(self::DEFAULT_LIFETIME);
		$this->setPath(self::DEFAULT_PATH);
		$this->setDomain(self::DEFAULT_DOMAIN);
		$this->setIsSecure(self::DEFAULT_SECURE);
		$this->setIsHttpOnly(self::DEFAULT_HTTP_ONLY);
	}
	
	public static function useSessionCookie(SessionCookie $cookie) {
		ini_set('session.name', $cookie->getName());
		ini_set('session.cookie_lifetime', $cookie->getLifeTime());
		ini_set('session.cookie_path', $cookie->getPath());
		ini_set('session.cookie_domain', $cookie->getDomain());
		ini_set('session.cookie_secure', $cookie->getIsSecure());
		ini_set('session.cookie_httponly', $cookie->getIsHttpOnly());
	}
    
}