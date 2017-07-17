<?php
/*
 ==============================================================================
 系统公告
 ==============================================================================
 */
define ( 'VISIBLE_GUEST', 1 );
define ( 'VISIBLE_STUDENT', 2 );
define ( 'VISIBLE_TEACHER', 3 );
define ( 'NUM_PAGE', 20 );

class SystemAnnouncementManager {

	function get_latest_announcements($visible, $user_id = NULL, $limit = NUMBER_PAGE, $restrict_org_id) {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		
		$sql = "SELECT t1.*, DATE_FORMAT(date_start,'%Y-%m-%d') AS display_date FROM " . $db_table . " AS t1
		LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id WHERE t2.module='sys_announce' AND visible=1";
		
		if ($restrict_org_id && ! api_is_platform_admin ()) {
			$sql .= " AND (org_id=-1 OR org_id=" . Database::escape ( $restrict_org_id ) . ")";
		}
		
		$sql .= " ORDER BY date_start DESC LIMIT " . $limit;
		
		//echo $sql2;
		$announcements = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result_array ( $announcements );
	
	}

	function count_nb_announcement($user_id = '') {
		$visibility = api_is_allowed_to_create_course () ? VISIBLE_TEACHER : VISIBLE_STUDENT;
		$user_selected_language = api_get_interface_language ();
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = 'SELECT count(id) FROM ' . $db_table . '	WHERE visible=1';
		$announcements = api_sql_query ( $sql, __FILE__, __LINE__ );
		$result = mysql_fetch_array ( $announcements );
		$i = isset ( $result ) ? $result [0] : 0;
		return $i;
	}

	/**
	 * 计算自从有效的的通知公告数
	 * @param $user_id
	 * @return unknown_type
	 * @since V1.2.0
	 */
	function get_valid_announcement_count($user_id = '') {
		global $_user;
		$visibility = api_is_allowed_to_create_course () ? VISIBLE_TEACHER : VISIBLE_STUDENT;
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = "SELECT count(id) FROM $db_table WHERE  visible=1";
		//echo $sql;
		$i = Database::get_scalar_value ( $sql );
		return $i;
	}

	/**
	 * 计算自从上次登录以来发布的通知公告数
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function count_announcement_since_last_login($user_id = '') {
		global $_user;
		$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		$sql = "select login_date from " . $tbl_track_login . " WHERE login_user_id='" . Database::escape_string ( $user_id ) . "' ORDER BY login_id DESC LIMIT 2";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$first_time_login = false;
		if (Database::num_rows ( $result ) == 1)
			$first_time_login = true;
		else {
			$last_login_date = mysql_result ( $result, 1 );
		}
		//echo $last_login_date;
		

		$visibility = api_is_allowed_to_create_course () ? VISIBLE_TEACHER : VISIBLE_STUDENT;
		$user_selected_language = api_get_interface_language ();
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = "SELECT count(id) FROM $db_table	WHERE  visible=1";
		if (! $first_time_login) {
			$sql .= " AND date_start>='" . $last_login_date . "'";
		}
		//echo $sql;
		$i = Database::get_scalar_value ( $sql );
		return $i;
	}

	/**
	 * Get all announcements
	 * @return array An array with all available system announcements (as php
	 * objects)
	 */
	function get_all_announcements($restrict_org_id = NULL, $sqlwhere = "") {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
		
		$sql = "SELECT t1.*,t2.name,t3.username,t3.firstname FROM " . $db_table . " AS t1
				LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id
				AND t2.module='sys_announce' LEFT JOIN $tbl_user AS t3 ON t3.user_id=t1.created_user WHERE 1 ";
		
		if ($restrict_org_id && ! api_is_platform_admin ()) {
			$sql .= " AND ( t1.org_id=" . Database::escape ( $restrict_org_id ) . ")";
		}
		if ($sqlwhere) $sql .= $sqlwhere;
		$sql .= " ORDER BY date_start DESC";
		
		//echo $sql;
		$announcements = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$all_announcements = array ();
		while ( $announcement = Database::fetch_object ( $announcements ) ) {
			$all_announcements [] = $announcement;
		}
		return $all_announcements;
	}

	function add_announcement($title, $content, $date_start, $visible = 10, $category = 1, $created_user = 1) {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$start = $date_start . ":00";
		$sql_data = array ("title" => $title, 'content' => $content, 'date_start' => $start, 'visible' => $visible, 'category' => $category, 'created_user' => $created_user );
		$sql = Database::sql_insert ( $db_table, $sql_data );
		//echo $sql;
		api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::get_last_insert_id ();
	}

	function update_announcement($id, $title, $content, $date_start, $visible = 1, $category = 1, $org_id = -1) {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$start = $date_start . ":00";
		$sql_data = array ("title" => $title, 'content' => $content, 'date_start' => $start, 'visible' => $visible, 'category' => $category, 'org_id' => $org_id );
		$sql = Database::sql_update ( $db_table, $sql_data, "id='" . escape ( $id ) . "'" );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function delete_announcement($id) {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = "DELETE FROM " . $db_table . " WHERE id=" . Database::escape ( $id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function get_announcement($id) {
		$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = "SELECT t1.*,t3.username,t3.firstname FROM " . $db_table . " AS t1 LEFT JOIN $tbl_user AS t3 ON t3.user_id=t1.created_user WHERE id='" . Database::escape_string ( $id ) . "'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$announcement = Database::fetch_object ( $rs );
		return $announcement;
	}

	function set_visibility($announcement_id, $visible) {
		$db_table = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
		$sql = "UPDATE " . $db_table . " SET visible = '" . $visible . "'   WHERE id=" . Database::escape ( $announcement_id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}
