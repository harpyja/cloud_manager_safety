<?php
/*
 ===============================================================================

 ===============================================================================
 */
/**
 ==============================================================================
 * This class allows to manage the session. Session is stored in
 * the database
 *
 * @package zllms.library
 ==============================================================================
 */
require_once ('db.class.php');

class session_handler {
	var $lifetime;
	var $sessionName;
	var $_db = NULL;
	var $_db_link = NULL;
	
	var $table_php_session = "php_session";
	var $gc_probability = 5;

	function __construct() {
		$this->session_handler ();
	}

	function __destruct() {
		//$this->_sqlClose();
	}

	function session_handler() {
		global $_configuration;
		$this->lifetime = $_configuration ["session_lifetime"]; // 60 minutes
		$this->_sqlConnect ();
	}

	function open($path, $name) {
		$this->sessionName = $name;
		return true;
	}

	function close() {
		return $this->garbage ( $this->lifetime ) ? true : false;
	}

	function read($sess_id) {
		if ($this->_db && is_object ( $this->_db )) {
			$sql = "SELECT user_data FROM " . $this->table_php_session . " WHERE session_id='$sess_id' ORDER BY last_updated_date DESC LIMIT 1";
			return $this->_db->fetch_scalar ( $sql );
		}
		return '';
	}

	function write($sess_id, $sess_value) {
		$time = time ();
		if ($this->_db && is_object ( $this->_db )) {
			$user_data = $this->read ( $sess_id );
			if (empty ( $user_data )) {
				$user_agent = (! isset ( $_SERVER ['HTTP_USER_AGENT'] )) ? "" : $_SERVER ['HTTP_USER_AGENT'];
				$sql = "INSERT IGNORE INTO " . $this->table_php_session . "(session_id,session_name,last_activity,session_start,user_data,ip_address,user_agent) VALUES";
				$sql .= "('$sess_id','" . $this->sessionName . "','$time','$time'," . $this->_db->escape ( $sess_value ) . ",'" . real_ip () . "'," . $this->_db->escape ( $user_agent ) . ")";
				$result = $this->_db->query ( $sql );
			} else {
				$sql = "UPDATE " . $this->table_php_session . " SET session_name='" . $this->sessionName . "',last_activity='$time',user_data=" . $this->_db->escape ( $sess_value ) . " WHERE session_id='$sess_id'";
				$this->_db->query ( $sql );
			}
			return true;
		}
		
		return false;
	}

	function destroy($sess_id) {
		if ($this->_db && is_object ( $this->_db )) {
			$sql = "DELETE FROM " . $this->table_php_session . " WHERE session_id='$sess_id'";
			$this->_db->query ( $sql );
			return true;
		}
		
		return false;
	}

	function garbage0($lifetime) {
		if ($this->_db && is_object ( $this->_db )) {
			$sql = "SELECT COUNT(session_id) FROM " . $this->table_php_session;
			$nbr_results = $this->_db->fetch_scalar ( $sql );
			
			if ($nbr_results > 100) {
				//$sql="DELETE FROM ".$this->table_php_session." WHERE last_activity<'".strtotime('-'.$this->lifetime.' seconds')."'";
				//以上即为:
				$sql = "DELETE FROM " . $this->table_php_session . " WHERE UNIX_TIMESTAMP(NOW())-last_activity>'" . $this->lifetime . "'";
				//$sql="DELETE FROM ".$this->table_php_session." WHERE last_activity-session_start >= '".$this->lifetime."'";
				$this->_db->query ( $sql );
			}
			return true;
		}
		
		return false;
	}

	function garbage($lifetime) {
		srand ( time () );
		if ((rand () % 100) < $this->gc_probability) {
			$expire = time () - $lifetime;
			$sql = "DELETE FROM " . $this->table_php_session . " WHERE last_activity <" . $expire;
			$this->_db->query ( $sql );
			
			if (DEBUG_MODE) api_error_log ( 'Session garbage collection performed.' . time (), __FILE__, __LINE__, "session.log" );
			return true;
		}
		return false;
	}

	function _sqlConnect() {
		global $_configuration;
		if (! $this->db_link) {
			$this->_db = new db ();
			$this->_db->connect ( $_configuration ['db_host'], $_configuration ['db_user'], $_configuration ['db_password'], $_configuration ['main_database'], "utf8", 0, "", time () );
			$this->_db_link = $this->_db->link;
		}
		return $this->_db_link ? TRUE : FALSE;
	}

	function _sqlClose() {
		if ($this->_db_link && is_object ( $this->_db )) {
			$this->_db->close ();
			$this->_db_link = NULL;
			return true;
		}
		return false;
	}
}
