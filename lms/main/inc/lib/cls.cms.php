<?php
$tbl_cms = Database::get_main_table ( TABLE_MAIN_SYSTEM_CMS );
$tbl_announce = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );
$tbl_announce_to = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS_TO );
class SystemAnnouncementManager {


	function get_announcement($id) {
		global $tbl_announce;
		$sql = "SELECT * FROM " . $tbl_announce . " WHERE id='" . Database::escape_string ( $id ) . "'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$announcement = Database::fetch_row ( $rs );
		return $announcement;
	}


	function add_announcement($title, $content, $date_start, $date_end, $category = 1, $visible = 1, $other_info = array()) {
		global $tbl_announce, $tbl_announce_to;
		$start = $date_start . ":00";
		$end = $date_end . ":00";
		$sql_data = array ("title" => $title, 'content' => $content, 'date_start' => $start, 'date_end' => $end, 'visible' => $visible, 'category' => $category, 'visible_to' => $other_info ['visible_to'], 'created_user' => $other_info ['created_user'] );
		$sql = Database::sql_insert ( $tbl_announce, $sql_data );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		$new_id = Database::get_last_insert_id ();
		
		if ($other_info ['visible_to'] == 0) {
			$to_orgs = $other_info ['orgs'];
			foreach ( $to_orgs as $org ) {
				if ($org) {
					$sql_data = array ('type' => 'org', 'announce_id' => $new_id, 'ref' => $org );
					$sql = Database::sql_insert ( $tbl_announce_to, $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
			$to_classes = $other_info ['classes'];
			foreach ( $to_classes as $class ) {
				if ($class) {
					$sql_data = array ('type' => 'class', 'announce_id' => $new_id, 'ref' => $class );
					$sql = Database::sql_insert ( $tbl_announce_to, $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
		}
		return $new_id;
	}


	function update_announcement($id, $title, $content, $date_start, $date_end, $category = 1, $visible = 1, $other_info = array()) {
		global $tbl_announce, $tbl_announce_to;
		$start = $date_start . ":00";
		$end = $date_end . ":00";
		$sql_data = array ("title" => $title, 'content' => $content, 'date_start' => $start, 'date_end' => $end, 'visible' => $visible, 'category' => $category, 'visible_to' => $other_info ['visible_to'] );
		$sql = Database::sql_update ( $tbl_announce, $sql_data, "id='" . escape ( $id ) . "'" );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE FROM " . $tbl_announce_to . " WHERE announce_id=" . Database::escape ( $id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if ($other_info ['visible_to'] == 0) {
			$to_orgs = $other_info ['orgs'];
			foreach ( $to_orgs as $org ) {
				if ($org) {
					$sql_data = array ('type' => 'org', 'announce_id' => $id, 'ref' => $org );
					$sql = Database::sql_insert ( $tbl_announce_to, $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
			$to_classes = $other_info ['classes'];
			foreach ( $to_classes as $class ) {
				if ($class) {
					$sql_data = array ('type' => 'class', 'announce_id' => $id, 'ref' => $class );
					$sql = Database::sql_insert ( $tbl_announce_to, $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
		}
	}


	function delete_announcement($id) {
		global $tbl_announce, $tbl_announce_to;
		$sql = "DELETE FROM " . $tbl_announce_to . " WHERE announce_id=" . Database::escape ( $id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE FROM " . $tbl_announce . " WHERE id=" . Database::escape ( $id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}


	function get_all_announcements($sqlwhere = '') {
		global $tbl_announce, $tbl_announce_to;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		
		$sql = "SELECT t1.*,IF( NOW() BETWEEN date_start AND date_end, '1', '0') AS enabled,t2.name
				FROM " . $tbl_announce . " AS t1  LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id
				AND t2.module='sys_announce'";
		if ($sqlwhere) $sql .= $sqlwhere;
		$sql .= " ORDER BY date_start DESC";
		//echo $sql;
		$announcements = api_sql_query ( $sql, __FILE__, __LINE__ );
		$all_announcements = api_store_result ( $announcements );
		return $all_announcements;
	}


	function get_latest_announcements($user_id = NULL, $limit = null) {
		global $tbl_announce;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		
		$sql = "SELECT t1.*, DATE_FORMAT(date_start,'%Y-%m-%d') AS display_date FROM " . $tbl_announce . " AS t1
		LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id WHERE t2.module='sys_announce'
		AND ((NOW() BETWEEN date_start AND date_end) OR date_end='0000-00-00 00:00:00') AND visible = 1 ";
		
		$sql .= " ORDER BY date_start DESC";
		if (! is_null ( $limit )) $sql .= " LIMIT " . $limit;
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result ( $rs );
	}


	function get_my_latest_announcements($user_id = NULL, $limit = null) {
		require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
		global $tbl_announce, $tbl_announce_to;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		
		
		$sql = "SELECT DISTINCT(announce_id) FROM $tbl_announce_to WHERE 1";
		$announce_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
		//echo $sql;
		
		$sql = "SELECT t1.*, DATE_FORMAT(date_start,'%Y-%m-%d') AS display_date FROM " . $tbl_announce . " AS t1	LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id WHERE t2.module='sys_announce'
		AND ((NOW() BETWEEN date_start AND date_end) OR date_end='0000-00-00 00:00:00') AND visible = 1 AND (visible_to=1 ";
		if ($announce_ids) $sql .= " OR (visible_to=0 AND " . Database::create_in ( $announce_ids, 't1.id' ) . ") ";
		$sql .= ")";
		$sql .= " ORDER BY date_start DESC";
		if (! is_null ( $limit )) $sql .= " LIMIT " . $limit;
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result ( $rs );
	
	}


	function get_my_latest_announcements_pagelist($user_id, $sql_where = "", $page_size = NULL, $offset = 0) {
		require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
		global $tbl_announce, $tbl_announce_to;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		
		
		$sql = "SELECT DISTINCT(announce_id) FROM $tbl_announce_to WHERE 1";
		$announce_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
		//echo $sql;
		
		$sql = "SELECT COUNT(*)  FROM " . $tbl_announce . " AS t1	LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id WHERE t2.module='sys_announce'
		AND ((NOW() BETWEEN date_start AND date_end) OR date_end='0000-00-00 00:00:00') AND visible = 1 AND (visible_to=1 ";
		if ($announce_ids) $sql .= " OR (visible_to=0 AND " . Database::create_in ( $announce_ids, 't1.id' ) . ") ";
		$sql .= ") ";
		if ($sql_where) $sql .= $sql_where;
		$total_rows = Database::get_scalar_value ( $sql );

		$sql = "SELECT t1.*, DATE_FORMAT(date_start,'%Y-%m-%d') AS display_date FROM " . $tbl_announce . " AS t1	LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id WHERE t2.module='sys_announce'
		AND ((NOW() BETWEEN date_start AND date_end) OR date_end='0000-00-00 00:00:00') AND visible = 1 AND (visible_to=1 ";
		if ($announce_ids) $sql .= " OR (visible_to=0 AND " . Database::create_in ( $announce_ids, 't1.id' ) . ") ";
		$sql .= ") ";
		if ($sql_where) $sql .= $sql_where;
		$sql .= " ORDER BY date_start DESC";
		if (empty ( $offset )) $offset = 0;
		if (isset ( $page_size )) $sql .= " LIMIT " . $offset . "," . $page_size;
		//echo $sql;
		$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
		return array ("data_list" => $data_list, "total_rows" => $total_rows );
	}


	function set_visibility($announcement_id, $visible) {
		global $tbl_announce;
		$sql = "UPDATE " . $tbl_announce . " SET visible = '" . $visible . "' WHERE id=" . Database::escape ( $announcement_id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

class CMSManager {


	function get($id) {
		global $tbl_cms;
		$sql = "SELECT * FROM " . $tbl_cms . " WHERE id='" . Database::escape_string ( $id ) . "'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::fetch_array ( $rs);
	}


	function add($title, $content, $category = 1, $visible = 1, $other_info = array()) {
		global $tbl_cms;
		$sql_data = array ("title" => $title, 'content' => $content, 'visible' => $visible, 'category' => $category );
		$sql_data = array_merge ( $sql_data, $other_info );
		$sql = Database::sql_insert ( $tbl_cms, $sql_data );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		$new_id = Database::get_last_insert_id ();
		return $new_id;
	}


	function update($id, $title, $content, $category = 1, $visible = 1, $other_info = array()) {
		global $tbl_cms;
		$sql_data = array ("title" => $title, 'content' => $content, 'visible' => $visible, 'category' => $category );
		$sql_data = array_merge ( $sql_data, $other_info );
		$sql = Database::sql_update ( $tbl_cms, $sql_data, "id='" . escape ( $id ) . "'" );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}


	function delete($id) {
		global $tbl_cms;
		$sql = "DELETE FROM " . $tbl_cms . " WHERE id=" . Database::escape ( $id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}


	function get_list($sqlwhere = '', $limit = null) {
		global $tbl_cms;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		
		$sql = "SELECT t1.*,t2.name,t2.code FROM " . $tbl_cms . " AS t1  LEFT JOIN " . $tbl_category . " AS t2 ON t1.category=t2.id  AND t2.module='sys_cms'";
		if (! $sqlwhere) $sql .= $sqlwhere;
		$sql .= " ORDER BY last_updated_date DESC";
		if (! is_null ( $limit )) $sql .= " LIMIT " . $limit;
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result ( $rs );
	}


	function set_visibility($id, $visible) {
		global $tbl_cms;
		$sql = "UPDATE " . $tbl_cms . " SET visible = '" . $visible . "' WHERE id=" . Database::escape ( $id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}