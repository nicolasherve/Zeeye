<?php
namespace Zeeye\Util\Session\Adapter;

use Zeeye\Util\Date\Date;
/**
 * List all operations that must be provided by the concrete session Dao
 * 
 * Must be implemented by a concrete session Dao
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
interface ISessionDao {
	/**
	 * Insert the new session data in database
	 * 
	 * @param string $sessionId
	 * @param string $data
	 * @param Date $accessDate
	 */
	public function insertSession($sessionId, $data, Date $accessDate);
	
	/**
	 * Return the session data corresponding to the given session id
	 * 
	 * @param string $sessionId
	 */
	public function getDataBySessionId($sessionId);
	
	/**
	 * Update the access date of the session
	 * 
	 * @param Date $accessDate
	 * @param string $sessionId
	 */
	public function updateAccessDateBySessionId(Date $accessDate, $sessionId);
	
	/**
	 * Update the data and access date of the session corresponding to the given session id
	 * 
	 * @param string $data
	 * @param Date $accessDate
	 * @param string $sessionId
	 */
	public function updateDataAndAccessDateBySessionId($data, Date $accessDate, $sessionId);
	
	/**
	 * Update the session id
	 * 
	 * @param string $oldSessionId
	 * @param string $newSessionId
	 */
	public function updateSessionIdBySessionId($oldSessionId, $newSessionId);
	
	/**
	 * Delete the sessions whith access date before the given date
	 * 
	 * @param Date $date
	 */
	public function deleteByAccessDateBefore(Date $date);
	
	/**
	 * Delete the session corresponding to the given session id
	 * 
	 * @param string $sessionId
	 */
	public function deleteBySessionId($sessionId);
}