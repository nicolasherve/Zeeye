<?php
namespace Zeeye\Util\Session;

/**
 * Interface for session ids based on cookie
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
interface WithSessionCookie {
	public function setSessionCookie(SessionCookie $cookie);
	public function getSessionCookie();
	public function destroySessionCookie();
}