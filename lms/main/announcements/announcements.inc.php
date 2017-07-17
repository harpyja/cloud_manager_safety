<?php
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

/*
 ==============================================================================
 课程公告类，原来文件名为announcements.inc.php, 现将其改成类
 ==============================================================================
 */

class CourseAnnouncementManager {

	/**
	 * liyu: 获取某条公告记录
	 *
	 * @param unknown_type $announcement_id
	 * @return unknown
	 */
	function get_announcement($announcement_id, $current_user_id, $db_name = '') {
		global $_user;
		$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT, $db_name );
		$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY, $db_name );
		
		if ($current_user_id) {
			$sql_query = "SELECT announcement.*, toolitemproperties.*
			FROM $tbl_announcement announcement, $tbl_item_property toolitemproperties
			WHERE announcement.id = toolitemproperties.ref
			AND toolitemproperties.tool='" . TOOL_ANNOUNCEMENT . "'
			AND announcement.id = '$announcement_id'";
			if (! api_is_allowed_to_edit ()) {
				$sql_query .= " AND (toolitemproperties.to_user_id='" . $current_user_id . "' OR toolitemproperties.to_user_id IS NULL)";
			}
			$sql_query .= " AND toolitemproperties.visibility='1'	ORDER BY display_order DESC";
		} else {
			$sql_query = "	SELECT announcement.*, toolitemproperties.*
			FROM $tbl_announcement announcement, $tbl_item_property toolitemproperties
			WHERE announcement.id = toolitemproperties.ref
			AND announcement.id = '$announcement_id'
			AND toolitemproperties.tool='" . TOOL_ANNOUNCEMENT . "'
			AND toolitemproperties.visibility='1'";
		}
		//echo $sql_query;
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		$result = Database::fetch_array ( $sql_result );
		return $result;
	}

	function del_announcement($announcement_id) {
		if ($announcement_id) {
			//$db_name=api_get_course_dbName();
			$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
			$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
			$file_path_prefix = api_get_path ( SYS_COURSE_PATH ) . api_get_course_path () . "/";
			AttachmentManager::del_all_attachment ( 'COURSE_ANNOUNCEMENT', $announcement_id, $file_path_prefix );
			
			$sql = "DELETE FROM $tbl_item_property WHERE tool='" . TOOL_ANNOUNCEMENT . "' and ref='" . $announcement_id . "' AND cc='" . api_get_course_code () . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM " . $tbl_announcement . " WHERE id='" . $announcement_id . "' AND cc='" . api_get_course_code () . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			return true;
		}
		return false;
	}

	function display_announcement($announcement_id) {
		$result = get_announcement ( $announcement_id, $_user ['user_id'] );
		
		$title = $result ['title'];
		$content = $result ['content'];
		$content = make_clickable ( $content );
		$content = text_filter ( $content );
		$last_post_datetime = $myrow ['temps']; // post time format  datetime de mysql
		list ( $last_post_date, $last_post_time ) = split ( " ", $last_post_datetime );
		
		echo "<table height=\"100\" width=\"100%\" border=\"1\" cellpadding=\"5\" cellspacing=\"0\" id=\"agenda_list\">\n";
		echo "<tr class=\"data\"><td>" . $title . "</td></tr>\n";
		echo "<tr><td class=\"announcements_datum\">" . get_lang ( 'AnnouncementPublishedOn' ) . " : " . ucfirst ( format_locale_date ( get_lang ( 'DateFormatLong' ), strtotime ( $last_post_date ) ) ) . "</td></tr>\n";
		echo "<tr class=\"text\"><td>$content</td></tr>\n";
		echo "</table>";
	}

	/**
	 * this function gets all the users of the course,
	 * including users from linked courses
	 */
	function get_course_users() {
		$user_list = CourseManager::get_user_list_from_course_code ( api_get_course_id () );
		return $user_list;
	}

	/*======================================
	 SENT_TO()
	 ======================================*/
	/**
	 * returns all the users and all the groups a specific announcement item
	 * has been sent to
	 */
	function sent_to($tool, $id) {
		global $_course;
		global $tbl_item_property;
		
		$sql = "SELECT * FROM $tbl_item_property WHERE tool='$tool' AND ref='" . $id . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		while ( $row = mysql_fetch_array ( $result ) ) {
			
			// if to_user_id <> 0 then it is sent to a specific user
			if ($row ['to_user_id'] != 0) {
				$sent_to_user [] = $row ['to_user_id'];
			}
		}
		
		if (isset ( $sent_to_user )) {
			$sent_to ['users'] = $sent_to_user;
		}
		return $sent_to;
	}

	/*===================================================
	 CHANGE_VISIBILITY($tool,$id)
	 =================================================*/
	/**
	 * This functions swithes the visibility a course resource
	 * using the visibility field in 'item_property'
	 * values: 0 = invisibility for
	 */
	function change_visibility($tool, $id) {
		global $_course;
		global $tbl_item_property;
		
		$sql = "SELECT * FROM $tbl_item_property WHERE tool='$tool' AND ref='$id'";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ ) or die ( mysql_error () );
		$row = mysql_fetch_array ( $result );
		
		if ($row ['visibility'] == '1') {
			$sql_visibility = "UPDATE $tbl_item_property SET visibility='0' WHERE tool='$tool' AND ref='$id'";
		} else {
			$sql_visibility = "UPDATE $tbl_item_property SET visibility='1' WHERE tool='$tool' AND ref='$id'";
		}
		
		$result = api_sql_query ( $sql_visibility, __FILE__, __LINE__ ) or die ( mysql_error () );
	}

	/*====================================================
	 STORE_ADVALVAS_ITEM
	 ====================================================*/
	/**
	 * 新增公告,更新item_property表
	 *
	 * @param unknown_type $emailTitle
	 * @param unknown_type $newContent
	 * @param unknown_type $order
	 * @param unknown_type $to
	 * @return unknown
	 */
	function store_advalvas_item($emailTitle, $newContent, $order, $to) {
		
		global $_course;
		global $nameTools;
		global $_user;
		
		global $tbl_announcement;
		global $tbl_item_property;
		
		// store in the table announcement
		$sql = "INSERT INTO $tbl_announcement SET content = '$newContent', title = '$emailTitle', end_date = NOW(), display_order ='$order'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ ) or die ( mysql_error () );
		$last_id = mysql_insert_id ();
		
		if (! is_null ( $to )) {
			if (is_array ( $send_to ['users'] )) {
				foreach ( $send_to ['users'] as $user ) {
					api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $last_id, "AnnouncementAdded", $_user ['user_id'], '', $user );
				}
			}
		} else {
			api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $last_id, "AnnouncementAdded", $_user ['user_id'], '0' );
		}
		
		return $last_id;
	
	}

	/**
	 * 更新公告
	 * This function stores the announcement Item in the table announcement
	 * and updates the item_property also
	 */
	function edit_advalvas_item($id, $emailTitle, $newContent, $to) {
		global $_course;
		global $nameTools;
		global $_user;
		
		global $tbl_announcement;
		global $tbl_item_property;
		
		// store the modifications in the table announcement
		$sql = "UPDATE $tbl_announcement SET content='$newContent', title = '$emailTitle' WHERE id='$id'";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ ) or die ( mysql_error () );
		
		// we remove everything from item_property for this
		$sql_delete = "DELETE FROM $tbl_item_property WHERE ref='$id' AND tool='announcement'";
		$result = api_sql_query ( $sql_delete, __FILE__, __LINE__ ) or die ( mysql_error () );
		
		// store in item_property (first the groups, then the users
		if (! is_null ( $to )) // !is_null($to): when no user is selected we send it to everyone
{
			$send_to = self::separate_users_groups ( $to );
			
			// storing the selected users
			if (is_array ( $send_to ['users'] )) {
				foreach ( $send_to ['users'] as $user ) {
					api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $id, "AnnouncementUpdated", $_user ['user_id'], '', $user );
				}
			}
		} else // the message is sent to everyone, so we set the group to 0
{
			api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $id, "AnnouncementUpdated", $_user ['user_id'], '0' );
		}
	}

	/*
	 ==============================================================================
	 MAIL FUNCTIONS
	 ==============================================================================
	 */
	
	/**
	 * Sends an announcement by email to a list of users.
	 * Emails are sent one by one to try to avoid antispam.
	 */
	function send_announcement_email($user_list, $course_code, $_course, $mail_title, $mail_content) {
		foreach ( $user_list as $this_user ) {
			$mail_subject = get_lang ( 'professorMessage' ) . ' - ' . $_course ['official_code'] . ' - ' . $mail_title;
			
			$mail_body = '[' . $_course ['official_code'] . '] - [' . $_course ['name'] . "]\n";
			$mail_body .= $this_user ['lastname'] . ' ' . $this_user ['firstname'] . ' <' . $this_user ["email"] . "> \n\n" . stripslashes ( $mail_title ) . "\n\n" . trim ( 
					stripslashes ( html_entity_decode ( strip_tags ( str_replace ( array ('<p>', '</p>', '<br />' ), array ('', "\n", "\n" ), $mail_content ) ) ) ) ) . " \n\n-- \n";
			$mail_body .= $_user ['firstName'] . ' ' . $_user ['lastName'] . ' ';
			$mail_body .= '<' . $_user ['mail'] . ">\n";
			$mail_body .= $_course ['official_code'] . ' ' . $_course ['name'];
			
			//set the charset and use it for the encoding of the email - small fix, not really clean (should check the content encoding origin first)
			//here we use the encoding used for the webpage where the text is encoded (ISO-8859-1 in this case)
			// hgz 20070612
			//if(empty($charset)){$charset='ISO-8859-1';}
			$encoding = 'Content-Type: text/plain; charset=' . SYSTEM_CHARSET;
			$newmail = api_mail ( $this_user ['lastname'] . ' ' . $this_user ['firstname'], $this_user ['email'], $mail_subject, $mail_body, $_SESSION ['_user'] ['lastName'] . ' ' . $_SESSION ['_user'] ['firstName'], $_SESSION ['_user'] ['mail'], $encoding );
		}
	}

	function update_mail_sent($insert_id) {
		global $_course;
		global $tbl_announcement;
		$sql = "UPDATE $tbl_announcement SET email_sent='1' WHERE id='$insert_id'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	/**
	 *左部列表SQL语句
	 *
	 * @return unknown
	 */
	function get_announcemet_list_sql($user_id, $course_code, $last_login_date = '0000-00-00 00:00:00') {
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
		$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
		$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		
		// 教师或课程管理员,返回所有(包括隐藏,非隐藏的)
		if (UserManager::is_course_admin ( $user_id, $course_code )) {
			$sql = "SELECT t1.*, t2.*,t1.cc AS course_code,t1.id AS anno_id
				FROM $tbl_announcement AS t1, $tbl_item_property AS t2
				WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'
				AND t2.visibility<>'2'";
			if ($last_login_date != '0000-00-00 00:00:00') $sql .= " AND t2.lastedit_date>='" . $last_login_date . "'";
			$sql .= " AND t1.cc='" . $course_code . "' AND t2.cc='" . $course_code . "'";
			$sql .= " GROUP BY t2.ref	";
		} else {
			$sql = "SELECT	t1.*, t2.*,t1.cc AS course_code,t1.id AS anno_id
						FROM $tbl_announcement AS t1, $tbl_item_property AS t2
						WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'
						AND t2.visibility='1'";
			$sql .= " AND (t2.to_user_id=" . Database::escape ( $user_id ) . " OR t2.to_user_id IS NULL)";
			if ($last_login_date != '0000-00-00 00:00:00') $sql .= " AND t2.lastedit_date>='" . $last_login_date . "'";
			$sql .= " AND t1.cc='" . api_get_course_code () . "' AND t2.cc='" . $course_code . "'";
			$sql .= " GROUP BY t2.ref	";
		}
		return $sql;
	}

	/**
	 *
	 * @param $user_id
	 * @param $type
	 * @return unknown_type
	 * @since V1.4.0
	 */
	function disp_all_announcements($user_id = NULL, $type = 'days1') {
		$my_courses = UserManager::get_all_courses_of_one_user_subscribed ( $user_id );
		if (! $my_courses) {
			Display::display_normal_message ( get_lang ( 'NoAnnouncements' ) );
		} else {
			$table_header [] = array (get_lang ( 'Title' ) );
			$table_header [] = array (get_lang ( 'Publisher' ), true, null, array ('width' => '80' ) );
			$table_header [] = array (get_lang ( 'AnnouncementPublishedOn' ), true, null, array ('width' => '90' ) );
			$table_header [] = array (get_lang ( 'Courses' ), true, null, array ('width' => '25%' ) );
			
			$row_index = 0;
			$table_data = array ();
			
			$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
			$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
			foreach ( $my_courses as $key => $value ) {
				$all_cc [] = $value ['code'];
			}
			$sql_code_in = Database::create_in ( $all_cc, "t1.cc" );
			
			$sql = "SELECT	t1.*, t2.*,t1.cc AS course_code,t1.id AS anno_id
				FROM $tbl_announcement AS t1, $tbl_item_property AS t2
				WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'";
			$sql .= " AND " . $sql_code_in;
			switch ($type) {
				case 'days1' :
					$sql .= " AND (TO_DAYS(NOW())-TO_DAYS(lastedit_date))=0";
					break;
				case 'days7' :
					$sql .= " AND (TO_DAYS(NOW())-TO_DAYS(lastedit_date))<=7";
					break;
				case 'days30' :
					$sql .= " AND (TO_DAYS(NOW())-TO_DAYS(lastedit_date))<=30";
					break;
				case 'daysall' :
				default :
					break;
			}
			$sql .= " GROUP BY t2.ref ";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$course_code = $row ['course_code'];
				if (UserManager::is_course_admin ( $user_id, $course_code )) {
					$all_announcement [] = $row;
				} else {
					if ($row ['visibility'] == 1) {
						if ($user_id && $user_id == $row ['to_user_id']) {
							$all_announcement [] = $row;
						}
						
						if (empty ( $row ['to_user_id'] )) {
							$all_announcement [] = $row;
						}
					}
				
				}
			}
			
			if (is_array ( $all_announcement ) && $all_announcement) {
				foreach ( $all_announcement as $info ) {
					$row = array ();
					
					$course_info = CourseManager::get_course_information ( $info ['course_code'] );
					$user_info = UserManager::get_user_info_by_id ( $info ['insert_user_id'] );
					
					//$row[]=Display::return_icon('valves.gif');
					if ($info ['visibility'] == 0) {
						$row [] = '<span class="invisible">' . $info ['title'] . '</span>';
					} else {
						$row [] = "<a  class=\"thickbox\" href='?todo=view&id=" . $info ['anno_id'] . "&course_code=" . $course_info ['code'] . "&KeepThis=true&TB_iframe=true&height=390&width=770&modal='>{$info['title']}</a>";
					}
					
					$row [] = $user_info ['firstname'];
					
					$row [] = $info ['display_date'];
					
					$row [] = "<a href='" . api_get_path ( WEB_CODE_PATH ) . "announcements/index.php?cidReq=" . $course_info ['code'] . "'>" . $course_info ['title'] . "-" . $course_info ['code'] . "</a>";
					
					$table_data [] = $row;
				}
			}
			$query_vars ['action'] = $type;
			$sorting_options = array ();
			Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars );
		}
	}

	function get_latest_announcements($user_id = NULL, $limit = NUMBER_PAGE) {
		$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
		$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$sql2 = "SELECT	t1.*, t2.*,t1.cc AS course_code,t1.id AS anno_id FROM $tbl_announcement AS t1, $tbl_item_property AS t2
				WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'";
		$sql2 .= " AND t1.cc IN ( SELECT t4.code FROM " . $table_course_user . " AS t3 WHERE t3.user_id=" . Database::escape ( $user_id ) . ")";
		$sql2 .= " GROUP BY t2.ref  LIMIT " . $limit;
		$res = api_sql_query ( $sql2, __FILE__, __LINE__ );
		while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$course_code = $row ['course_code'];
			if (UserManager::is_course_admin ( $user_id, $course_code )) {
				$all_announcement [] = $row;
			} else {
				if ($row ['visibility'] == 1) {
					if ($user_id && $user_id == $row ['to_user_id']) {
						$all_announcement [] = $row;
					}
					
					if (empty ( $row ['to_user_id'] )) {
						$all_announcement [] = $row;
					}
				}
			}
		}
		//echo $sql2;
		return $all_announcement;
	
	}

	function count_course_announcement_since_last_login($user_id = '') {
		$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		$sql = "select login_date from " . $tbl_track_login . " WHERE login_user_id='" . Database::escape_string ( $user_id ) . "' ORDER BY login_id DESC LIMIT 2";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$first_time_login = false;
		if (Database::num_rows ( $result ) == 1) $first_time_login = true;
		else {
			$last_login_date = mysql_result ( $result, 1 );
		}
		//echo $last_login_date;
		

		$my_courses = UserManager::get_all_courses_of_one_user_subscribed ( $user_id );
		unset ( $sql );
		if (is_array ( $my_courses ) && count ( $my_courses ) > 0) {
			foreach ( $my_courses as $key => $value ) {
				$tmp_sql = self::get_announcemet_list_sql ( api_get_user_id (), $value ['db_name'] );
				//echo $tmp_sql;
				$sql .= " UNION (" . $tmp_sql . ") ";
			}
			if (strpos ( $sql, ' UNION' ) == 0) $sql = substr ( $sql, 7 );
			
			$sql = "SELECT count(*) FROM (" . $sql . ") AS t WHERE t.lastedit_date>='" . $last_login_date . "'";
			
			return Database::get_scalar_value ( $sql );
		} else {
			return 0;
		}
	}

	function count_course_announcement_since_last_login2($user_id = '') {
		if ($user_id) {
			$last_login_date = api_get_last_login_time ( $user_id );
			$my_courses = api_get_user_courses ( $user_id );
			//$courses=array_keys($my_courses);
			$total = 0;
			if (is_array ( $my_courses ) && count ( $my_courses ) > 0) {
				foreach ( $my_courses as $course_code => $course ) {
					$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY, $course ['db'] );
					$tmp_sql = self::get_announcemet_list_sql ( api_get_user_id (), $course ['db'], $last_login_date );
					$res = api_sql_query ( $tmp_sql, __FILE__, __LINE__ );
					$total += Database::num_rows ( $res );
				}
			}
			return $total;
		}
		return 0;
	}

}