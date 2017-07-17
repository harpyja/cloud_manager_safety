<?php

/*
 ==============================================================================

 ==============================================================================
 */

function event_login() {
	global $_configuration;
	global $_user;
	if ($_configuration ['tracking_enabled']) {
		$TABLETRACK_LOGIN = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$now = date ( 'Y-m-d H:i:s' );
		$sql = "SELECT * FROM " . $TABLETRACK_LOGIN . " WHERE login_user_id='" . $_user ['user_id'] . "' AND login_ip='" . get_onlineip () . "' AND login_date=FROM_UNIXTIME(" . $reallyNow . ")";
		if (! Database::if_row_exists ( $sql )) {
			$user_agent = $_SERVER ['HTTP_USER_AGENT'];
			$_browser = api_get_navigator ();
			$browser = $_browser ['name'] . '/' . $_browser ['version'];
			$sql_data = array ("login_user_id" => $_user ['user_id'], "login_ip" => get_onlineip (), 'login_date' => $now, 'session_id' => session_id (), 'user_agent' => $user_agent, 'browser' => $browser );
			$sql = Database::sql_insert ( $TABLETRACK_LOGIN, $sql_data );
			//$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		$sql = "UPDATE " . $table_user . " SET last_login_date='" . $now . "',last_login_ip='" . real_ip () . "',visit_count=visit_count+1 WHERE user_id='" . $_user ['user_id'] . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

function event_download($doc_url) {
	global $_configuration;
	global $_user;
	global $_cid;
	if ($_configuration ['tracking_enabled']) {
		$TABLETRACK_DOWNLOADS = $_configuration ['statistics_database'] . "`.`track_e_downloads";
		$reallyNow = time ();
		$user_id = api_get_user_id ();
		
		$sql = "INSERT INTO `" . $TABLETRACK_DOWNLOADS . "`	(
				 `down_user_id`, `down_cours_id`, `down_doc_path`, `down_date` ) VALUES	(
				 " . $user_id . ",  '" . $_cid . "', '" . htmlspecialchars ( $doc_url, ENT_QUOTES ) . "',
				 FROM_UNIXTIME(" . $reallyNow . "))";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return 1;
	}
}

/**
 * @param link_id (id in coursDb liens table)
 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @desc Record information for link event (when an user click on an added link)
 * it will be used in a redirection page
 */
function event_link($link_id) {
	global $_configuration, $_user, $_cid;
	$TABLETRACK_LINKS = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LINKS );
	if ($_configuration ['tracking_enabled']) {
		$user_id = ($_user ['user_id'] ? $_user ['user_id'] : null);
		$sql_data = array ('links_user_id' => $user_id, 'links_cours_id' => $_cid, 'links_link_id' => $link_id, 'links_date' => date ( 'Y-m-d H:i:s' ) );
		$sql = Database::sql_insert ( $TABLETRACK_LINKS, $sql_data );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		evnet_courseware ( $_cid, $user_id, $link_id, 0 );
		return 1;
	}
	return 0;
}

/**
 * 更新最后访问时间
 *
 * @param unknown_type $user_id
 */
function event_update_logout_date($user_id) {
	if ($user_id) {
		$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		
		//最后登录时间及对应的id
		$sql = "SELECT login_id, login_date FROM $tbl_track_login WHERE login_user_id='" . Database::escape_string ( $user_id ) . "' ORDER BY login_date DESC LIMIT 0,1";
		list ( $login_id, $login_date ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
		if ($login_id) {
			$s_sql_update_logout_date = "UPDATE $tbl_track_login SET logout_date=NOW() WHERE login_id='$i_id_last_connection'";
			api_sql_query ( $s_sql_update_logout_date, __FILE__, __LINE__ );
		}
	}
}

/**
 * 更新课程跟踪信息:总时间及最后学习时间
 * @param string $course_code
 * @param int $user_id
 * @param int $cw_id
 * @param int $learn_time
 * @param string $method: 更新时间方法: add在原有基础上增加, update替换值
 */
function evnet_courseware($course_code, $user_id, $cw_id, $learn_time = 0, $method = 'add') {
	$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
	$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND cw_id=" . Database::escape ( $cw_id );
	$sql = "SELECT * FROM $tbl_track_cw WHERE " . $sqlwhere;
	if (empty ( $learn_time )) $learn_time = 0;
	if (Database::if_row_exists ( $sql, __FILE__, __LINE__ )) {
		if (strtolower ( $method ) == 'add') {
			$sql = "UPDATE $tbl_track_cw SET total_time=total_time+" . $learn_time . ",last_access_time=UNIX_TIMESTAMP() WHERE " . $sqlwhere;
		}
		if (strtolower ( $method ) == 'update') {
			$sql = "UPDATE $tbl_track_cw SET total_time='" . escape ( $learn_time ) . "',last_access_time=UNIX_TIMESTAMP() WHERE " . $sqlwhere;
		}
	
		//$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
	} else {
		$now = time ();
		$sql_data = array ('last_access_time' => $now, 'total_time' => $learn_time, 'cc' => $course_code, 'user_id' => $user_id, 'cw_id' => $cw_id );
		$sql = Database::sql_insert ( $tbl_track_cw, $sql_data );
	}
	$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
}

function event_cw_access_times($course_code, $user_id, $cw_id, $access_times = 1, $method = 'add') {
	$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
	$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND cw_id=" . Database::escape ( $cw_id );
	if (strtolower ( $method ) == 'add') {
		$sql = "UPDATE $tbl_track_cw SET hits=hits+1 WHERE " . $sqlwhere;
	} elseif (strtolower ( $method ) == 'update') {
		$sql = "UPDATE $tbl_track_cw SET hits='" . escape ( $access_times ) . "' WHERE " . $sqlwhere;
	}
	$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
}

function event_cw_progress($course_code, $user_id, $cw_id, $progress = 0) {
	$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
	$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND cw_id=" . Database::escape ( $cw_id );
	$sql = "UPDATE $tbl_track_cw SET progress='" . escape ( $progress ) . "' WHERE " . $sqlwhere;
	$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
}
