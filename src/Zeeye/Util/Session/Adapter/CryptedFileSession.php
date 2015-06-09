<?php
namespace Zeeye\Util\Session\Adapter;

use Zeeye\Util\String\StringGenerator;
use Zeeye\Util\Cookie\Cookie;
/**
 * TODO A TESTER
 * 
 * Concrete session adapter to manage session via files
 * 
 * A cookie is used to send the session id to the server.
 * The files'content is encrypted.
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class CryptedFileSession extends FileSession {
	
	const CIPHER = MCRYPT_RIJNDAEL_256;
	const CIPHER_MODE = MCRYPT_MODE_CBC;
	private static $_ivSize = 0;
	private static $_cookieName = null;
	private static $_key;
	
	private static function _generateRandomKey($length=32) {
		if (function_exists('openssl_random_pseudo_bytes')) {
			return openssl_random_pseudo_bytes($length, true);
		}
		return StringGenerator::generateRandomAlphaAndNumbersAndSpecial($length);
	}
		
	public function open($savePath, $name) {
		
		if (!isset(self::$_cookie)) {
			// TODO Error
		}
		
		self::$_ivSize= mcrypt_get_iv_size(self::CIPHER, self::CIPHER_MODE);
		self::$_cookieName= 'key_'.$session_name;
		
		if (Cookie::has(self::$_cookieName)) {
			$keyLength= mcrypt_get_key_size(self::CIPHER, self::CIPHER_MODE);
			self::$_key = self::_generateRandomKey($keyLength);
			
			$sessionCookieParams = session_get_cookie_params();
			
			Cookie::put(
				self::$_cookieName, 
				base64_encode(self::$_key), 
				$sessionCookieParams['lifetime'],
				$sessionCookieParams['path'],
				$sessionCookieParams['domain'], 
				$sessionCookieParams['secure'],
				$sessionCookieParams['httponly']
			);
			
		} 
		else {
			self::$_key= base64_decode(Cookie::get(self::$_cookieName));
		}
		
		return true;
	}
	
	public function read($id) {
		
		$file = $this->_savePath.'/sess_'.$id;
		if (!file_exists($file)) {
			return '';
		}
		$data = file_get_contents($file);
		
		$iv= substr($data, 0, self::$_ivSize);
		$encrypted= substr($data, self::$_ivSize);
		$decrypted = mcrypt_decrypt(
			self::CIPHER,
			self::$_key,
			$encrypted,
			self::CIPHER_MODE,
			$iv
		);
		
		return $decrypted;
	}
	
	public function write($id, $data) {
		
		$iv = mcrypt_create_iv(self::$_ivSize, MCRYPT_RAND);
		
		$encrypted = mcrypt_encrypt(
			self::CIPHER,
			self::$_key,
			$data,
			self::CIPHER_MODE,
			$iv
		);
		
		return file_put_contents($this->_savePath.'/sess_'.$id, $iv.$encrypted) === false ? false : true;
	}
}