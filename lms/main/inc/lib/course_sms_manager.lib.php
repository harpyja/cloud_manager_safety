<?php
define ( 'SMS_TYPE_COURSE_ALL', 'course_all' );
define ( 'SMS_TYPE_COURSE_PRIVATE', 'course_private_sms' );
define ( 'SMS_CATEGORY_ASSIGNMENT_PUB', 'ASSIGNMENT_PUB' );
define ( 'SMS_CATEGORY_ASSIGNMENT_SUB', 'ASSIGNMENT_SUB' );

//require_once("database.lib.php");
class CourseSMSManager {

	static function _table_course_sms($db_name = '') {
		return Database::get_course_table ( TABLE_TOOL_SMS, $db_name );
	}

	static function _table_course_sms_received($db_name = '') {
		return Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED, $db_name );
	}

	static function _table_user() {
		return Database::get_main_table ( TABLE_MAIN_USER );
	}

	/**
	 * 新增SMS
	 *
	 * @param unknown_type $sender
	 * @param unknown_type $content
	 * @param unknown_type $send_time
	 * @param unknown_type $receivers
	 * @param unknown_type $is_to_all
	 * @param unknown_type $type
	 * @return unknown
	 */
	function create_sms($sender, $content, $send_time, $receivers = array(), $is_to_all = false, $type = SMS_TYPE_COURSE_ALL, $category = '', $ref_id = '', $db_name = '', $course_code = '') {
		
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS, $db_name );
		$table_course_sms_received = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED, $db_name );
		
		$sms_receivers = $receivers;
		
		/*$sql="INSERT INTO ".self::_table_course_sms($db_name)
		 ." SET sender='".Database::escape_string($sender)."',"
		 ."content='".Database::escape_string($content)."',"
		 ."send_time='".$send_time."',"
		 ."sms_type='".$type."',is_to_all='"
		 .($is_to_all?"1":"0")."',creation_time=now(),category='"
		 .$category."',ref_id='".Database::escape_string($ref_id)."'"	;*/
		
		$sql_data = array ('sender' => $sender, 'content' => $content, 'send_time' => $send_time, 'sms_type' => $type, 'is_to_all' => ($is_to_all ? "1" : "0"), 'creation_time' => date ( 'Y-m-d H:i:s' ), 'category' => $category, 'ref_id' => $ref_id );
		
		$sql_data ['cc'] = api_get_course_code ();
		$sql = Database::sql_insert ( self::_table_course_sms ( $db_name ), $sql_data );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$last_id = ($result ? mysql_insert_id () : false);
		
		if ($is_to_all && is_not_blank ( $course_code )) {
			unset ( $sms_receivers );
			$condition = "AND t1.course_code='" . $course_code . "' AND t2.active=1 ORDER BY lastname,firstname";
			$user_list = self::get_user_list ( $condition );
			if ($list_id && is_array ( $user_list )) {
				$idx = 0;
				foreach ( $receiver as $key => $val ) {
					$sms_receivers [$idx] = $key;
					$idx ++;
				}
			}
		}
		if ($last_id && is_array ( $sms_receivers )) {
			foreach ( $receivers as $key ) {
				if ($key) {
					/*$sql="INSERT INTO ".self::_table_course_sms_received($db_name)." SET user_id='".$key."',"
					 ."sms_id='".$last_id."',is_read=0";*/
					$sql_data = array ('user_id' => $key, 'sms_id' => $last_id, 'is_read' => 0 );
					
					$sql_data ['cc'] = api_get_course_code ();
					$sql = Database::sql_insert ( self::_table_course_sms_received ( $db_name ), $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
		}
		return ($result ? mysql_insert_id () : false);
	
	}

	/**
	 * 删除SMS
	 *
	 * @param unknown_type $sms_id
	 * @param unknown_type $user_id
	 * @param unknown_type $is_to_all
	 * @return unknown
	 */
	function del_sms($user_id, $sms_id, $db_name = '') {
		$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS, Database::escape_string ( $db_name ) );
		$table_course_sms_received = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED, Database::escape_string ( $db_name ) );
		
		$sql = "UPDATE " . $table_course_sms_received . " SET is_deleted=1,is_read=1 WHERE user_id='" . Database::escape_string ( $user_id ) . "' AND
			sms_id='" . Database::escape_string ( $sms_id ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		return true;
	}

	function get_my_sms_list($user_id, $condition = "") {
		
		$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS );
		$table_course_sms_received = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED );
		
		$return_array [] = array ();
		
		$sql = "SELECT * FROM " . $table_course_sms . " as t1," . $table_course_sms_received . " as t2 WHERE t2.user_id='" . Database::escape_string ( $id ) . "' AND t1.id=t2.sms_id " . $condition;
		$sql .= " AND t1.cc='" . api_get_course_code () . "' ";
		$result = api_sql_query ( $sql );
		while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
			$return_array [] = $row;
		}
		return $return_array;
	}

	function get_sms_list($condition = "") {
		$table_course_sms = Database::get_main_table ( TABLE_MAIN_SYS_SMS );
		$sql = "SELECT * FROM " . $table_course_sms . " as t1 ";
		$sql .= " WHERE cc='" . api_get_course_code () . "' ";
		if ($condition) $sql .= $condition;
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
			$return_array [] = $row;
		}
		return $return_array;
	}

	/**
	 * 当前课程内最后的一条末读信息, 是否有未读短消息
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function has_not_read_course_sms($user_id = NULL) {
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
		if (! $my_courses) return false;
		
		foreach ( $my_courses as $key => $value ) {
			$course_codes [] = $value ['code'];
		}
		$sql_in = Database::create_in ( $course_codes, "cc" );
		$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS );
		$table_course_sms_received = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED );
		$sql = "SELECT t1.id,t1.sender,t1.send_time,t1.content FROM " . $table_course_sms . " as t1," . $table_course_sms_received . " as t2 where t2.is_read=0 AND
				t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='" . $user_id . "'";
		if ($sql_in) $sql .= " AND " . $sql_in;
		
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($rs) {
			return api_store_result ( $rs );
		} else {
			return false;
		}
	}

	function get_all_my_courses($user_id = NULL) {
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$sql = "SELECT t2.code,t2.db_name,t2.title FROM " . $table_course_user . " AS t1 LEFT JOIN " . $table_course . " AS t2 ON t1.course_code=t2.code WHERE t1.user_id=" . Database::escape ( $user_id ) . " ORDER BY t1.creation_time DESC";
		//echo $sql;
		return api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	}

	function get_user_course_str($user_id = NULL) {
		$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
		if ($my_courses && is_array ( $my_courses )) {
			foreach ( $my_courses as $key => $value ) {
				$course_codes [] = $value ['code'];
			}
			if ($course_codes && is_array ( $course_codes )) {return implode ( ",", $course_codes );}
		}
		return "";
	
	}

	//****************************************末读取信息
	

	/**
	 * 得到末读取信息的总数
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function get_not_read_sms_count($user_id = NULL) {
		//$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$count = 0;
		if (DB_MODE == 'single') {
			$my_courses_str = self::get_user_course_str ( $user_id );
			if (empty ( $my_courses_str )) return 0;
			$sql = "SELECT count(t1.id) FROM " . $table_course_sms . " as t1," . $table_course_sms_received . " as t2 where t1.send_time<=now() AND t2.is_read=0 AND
				t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='" . $user_id . "'";
			$sql .= " AND t1.cc='" . api_get_course_code () . "' t2.cc='" . api_get_course_code () . "'";
			$sql .= " AND t1.cc IN (" . $my_courses_str . ")";
			//echo $sql;
			$count = Database::get_scalar_value ( $sql );
		} else {
			$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
			if (! $my_courses) return 0;
			if (is_array ( $my_courses ) && count ( $my_courses ) > 0) {
				foreach ( $my_courses as $key => $value ) {
					$table_course_sms = self::_table_course_sms ( $value ['db_name'] );
					$table_course_sms_received = self::_table_course_sms_received ( $value ['db_name'] );
					
					$sql = "SELECT count(t1.id) FROM " . $table_course_sms . " as t1," . $table_course_sms_received . " as t2 where t1.send_time<=now() AND t2.is_read=0 AND
						t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='" . $user_id . "'";
					//echo $sql;
					

					$count += Database::get_scalar_value ( $sql );
				}
			}
		}
		
		return $count;
	
	}

	function _get_all_coursesms_notread_sql($user_id = NULL) {
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		
		$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
		
		if (is_array ( $my_courses ) && count ( $my_courses ) > 0) {
			
			if (DB_MODE == 'single') {
				$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS );
				$table_course_sms_received = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED );
				$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
				foreach ( $my_courses as $key => $value ) {
					$all_cc [] = $value ['code'];
				}
				$sql_code_in = Database::create_in ( $all_cc, "cc" );
				
				$sql = "SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content,
						CONCAT(t3.title,'-',t3.code) as memo,t1.cc as course_code,
						t3.title as course_title,t3.db_name as course_dbname FROM " . $table_course_sms . " AS t1 ," . $table_course_sms_received . " AS t2," . $table_course . " AS t3 where t1.cc=t3.code AND t2.is_read=0 AND
						t2.is_deleted=0 AND t1.send_time<=now() AND t1.id =t2.sms_id AND t2.user_id='" . $user_id . "'";
				$sql .= " AND t1." . $sql_code_in;
			
		//echo $sql;
			

			} else {
				foreach ( $my_courses as $key => $value ) {
					$table_course_sms = self::_table_course_sms ( $value ['db_name'] );
					$table_course_sms_received = self::_table_course_sms_received ( $value ['db_name'] );
					
					$sql .= " UNION ALL SELECT " . $table_course_sms . ".id," . $table_course_sms . ".is_to_all," . $table_course_sms . ".sender," . $table_course_sms . ".send_time," . $table_course_sms .
							 ".content
			,('" .
							 $value ['title'] . "-" . $value ['code'] . "') as memo,('" . $value ['code'] . "') as course_code,('" . $value ['title'] . "') as course_title,('" . $value ['db_name'] . "') as course_dbname FROM " . $table_course_sms . " ," . $table_course_sms_received . "  where  " .
							 $table_course_sms_received . ".is_read=0 AND
			" . $table_course_sms_received . ".is_deleted=0 AND " .
							 $table_course_sms . ".send_time<=now() AND " . $table_course_sms . ".id =" . $table_course_sms_received . ".sms_id AND " . $table_course_sms_received . ".user_id='" . $user_id . "'";
				
				}
				if (strpos ( $sql, ' UNION ALL' ) == 0) $sql = substr ( $sql, 11 );
			}
			return $sql;
		}
		return "";
	}

	function _get_coursesms_notread_sql($user_id = NULL, $db_name = '') {
		$sql = "SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM " . self::_table_course_sms_received ( $db_name ) . " as t2," . self::_table_course_sms ( $db_name ) . " AS t1 where t2.is_read=0 AND t2.is_deleted=0 AND t2.sms_id=t1.id AND  t1.send_time<=now() AND t2.user_id='" .
				 $user_id . "'";
				if (DB_MODE == 'single') $sql .= " AND t2.cc='" . api_get_course_code () . "' ";
				//echo $sql;
				return $sql;
			}

			/**
			 * 未读信息列表
			 *
			 * @param unknown_type $user_id
			 * @param unknown_type $start
			 * @param unknown_type $is_get_all true:获取用户所有课程消息
			 */
			function display_sms_list_notread($user_id = NULL, $parameters = array(), $is_get_all = true, $db_name = '') {
				$table_user = Database::get_main_table ( TABLE_MAIN_USER );
				
				if ($is_get_all) {
					$sql = self::_get_all_coursesms_notread_sql ( $user_id );
				} else {
					$sql = CourseSMSManager::_get_coursesms_notread_sql ( $user_id, $db_name );
				}
				//echo $sql;
				if (is_not_blank ( $sql )) {
					$sql = "SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (" . $sql . ") t left join " . $table_user . " as t3 ON t.sender=t3.user_id WHERE 1=1 ";
					if (is_array ( $parameters ) && array_key_exists ( 'keyword_content', $parameters ) && ! empty ( $parameters ['keyword_content'] )) {
						$sql .= " AND t.content LIKE '%" . Database::escape_string ( $parameters ['keyword_content'] ) . "%' ";
					}
					
					/*if(is_array($parameters) && array_key_exists('keyword_start',$parameters)  && is_array($parameters['keyword_start'])
			 && array_key_exists('keyword_end',$parameters)  && is_array($parameters['keyword_end'])){*/
					if (is_not_blank ( $_GET ['keyword_start'] )) {
						$start_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_start'] );
						$sql .= " AND t.send_time >='" . $start_date . "' ";
					}
					if (is_not_blank ( $_GET ['keyword_end'] )) {
						$end_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_end'] );
						$sql .= " AND t.send_time <='" . $end_date . "' ";
					}
					//			echo $sql;
					

					$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
					$table_header [0] = array ('', false );
					$table_header [] = array (get_lang ( 'Sender' ), true );
					$table_header [] = array (get_lang ( 'SendTime' ), true );
					$table_header [] = array (get_lang ( 'Content' ), true, 'width="50%"' );
					if ($is_get_all) {
						$table_header [] = array (get_lang ( 'Courses' ), true );
					}
					$table_header [] = array (get_lang ( 'Actions' ), false );
					$table_data = array ();
					
					while ( $row_data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
						$row = array ();
						$row [] = $row_data ['course_dbname'] . "#" . $row_data ['id'];
						$row [] = $row_data ['firstname'];
						$row [] = $row_data ['send_time'];
						$row [] = $row_data ['content'];
						if ($is_get_all) {
							$row [] = "<a href='" . api_get_path ( WEB_CODE_PATH ) . "course_sms/index.php?cidReq=" . $row_data ['course_code'] . "'>" . $row_data ['memo'] . "</a>";
						}
						$row [] = "<a href='" . api_get_path ( REL_CODE_PATH ) . "course_sms/" . ($is_get_all ? "desktop_sms" : "index") . ".php?action=markread&sms_id=" . $row_data ['id'] . "&course_code=" . $row_data ['course_dbname'] . "'>" .
								 Display::return_icon ( 'setting_true.gif', get_lang ( "MarkRead" ) ) . "</a>";
								
								$table_data [] = $row;
							}
							
							$query_vars ['action'] = 'notread';
							$query_vars = @array_merge ( $query_vars, $parameters );
							
							$sorting_options = array ();
							$sorting_options ['column'] = 2;
							$sorting_options ['default_order_direction'] = 'DESC';
							//$paging_options['per_page']=2;
							$form_actions = array ('markreadAll' => get_lang ( 'MarkRead' ) );
							Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, $form_actions );
						} else {
							echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
						}
					
					}

					function get_latest_sms_list_notread($user_id = NULL, $limit = NUMBER_PAGE, $is_get_all = true, $db_name = '') {
						$table_user = Database::get_main_table ( TABLE_MAIN_USER );
						
						if ($is_get_all) {
							$sql = self::_get_all_coursesms_notread_sql ( $user_id );
						} else {
							$sql = self::_get_coursesms_notread_sql ( $user_id, $db_name );
						}
						
						if (is_not_blank ( $sql )) {
							$sql = "SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (" . $sql . ") t left join " . $table_user . " as t3 ON t.sender=t3.user_id ORDER BY t.send_time DESC";
							$sql .= " LIMIT " . $limit;
							//echo $sql;
							$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
							return api_store_result_array ( $rs );
						}
					}

					//******************************************已接收信息
					function _get_all_coursesms_received_sql($user_id = NULL) {
						$table_user = Database::get_main_table ( TABLE_MAIN_USER );
						
						$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
						
						foreach ( $my_courses as $key => $value ) {
							$table_course_sms = self::_table_course_sms ( $value ['db_name'] );
							$table_course_sms_received = self::_table_course_sms_received ( $value ['db_name'] );
							
							$sql .= " UNION ALL SELECT $table_course_sms.id,$table_course_sms.is_to_all,$table_course_sms.sender,$table_course_sms.send_time,$table_course_sms.content
			,('" . $value ['title'] . "-" .
									 $value ['code'] . "') as memo,('" . $value ['code'] . "') as course_code,('" . $value ['db_name'] . "') as course_dbname FROM " . $table_course_sms . " ," . $table_course_sms_received . "  where $table_course_sms.send_time<=NOW() AND
			$table_course_sms_received.is_deleted=0 AND $table_course_sms.id =$table_course_sms_received.sms_id AND $table_course_sms_received.user_id='" . api_get_user_id () . "'";
								}
								if (strpos ( $sql, ' UNION ALL' ) == 0) $sql = substr ( $sql, 11 );
								return $sql;
							}

							function _get_coursesms_received_sql($user_id = NULL, $db_name = '') {
								$table_course_sms = self::_table_course_sms ( $db_name );
								$table_course_sms_received = self::_table_course_sms_received ( $db_name );
								$sql = "SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM " . $table_course_sms . " as t1," . $table_course_sms_received . " as t2 where t1.send_time<=NOW() AND
			t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='" . $user_id . "'";
								if (DB_MODE == 'single') $sql .= " AND t1.cc='" . api_get_course_code () . "' ";
								//echo $sql;
								return $sql;
							}

							/**
							 * 已接收信息列表
							 *
							 * @param unknown_type $user_id
							 * @param unknown_type $start
							 */
							function display_sms_list_received($user_id = NULL, $parameters = array(), $is_get_all = true, $db_name = '') {
								$table_user = Database::get_main_table ( TABLE_MAIN_USER );
								
								if ($is_get_all) {
									$sql = CourseSMSManager::_get_all_coursesms_received_sql ( $user_id );
								} else {
									$sql = CourseSMSManager::_get_coursesms_received_sql ( $user_id, $db_name );
								}
								
								if (is_not_blank ( $sql )) {
									$sql = "SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (" . $sql . ") t left join " . $table_user . " as t3 ON t.sender=t3.user_id WHERE 1=1 ";
									if (is_array ( $parameters ) && array_key_exists ( 'keyword_content', $parameters ) && ! empty ( $parameters ['keyword_content'] )) {
										$sql .= " AND t.content LIKE '%" . Database::escape_string ( $parameters ['keyword_content'] ) . "%' ";
									}
									
									if (is_not_blank ( $_GET ['keyword_start'] )) {
										$start_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_start'] );
										$sql .= " AND t.send_time >='" . $start_date . "' ";
									}
									if (is_not_blank ( $_GET ['keyword_end'] )) {
										$end_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_end'] );
										$sql .= " AND t.send_time <='" . $end_date . "' ";
									}
									//echo $sql;
									

									$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
									
									$table_header [] = array ('', false );
									$table_header [] = array (get_lang ( 'Sender' ), true, null, array ('width' => '70' ) );
									$table_header [] = array (get_lang ( 'SendTime' ), true, null, array ('width' => '150' ) );
									$table_header [] = array (get_lang ( 'Content' ), false );
									if ($is_get_all) {
										$table_header [] = array (get_lang ( 'Remark' ), true, null, array ('width' => '250' ) );
									}
									$table_header [] = array (get_lang ( 'Actions' ), false );
									$table_data = array ();
									
									while ( $row_data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
										$row = array ();
										$row [] = $row_data ['course_dbname'] . "#" . $row_data ['id'];
										$row [] = $row_data ['firstname'];
										$row [] = $row_data ['send_time'];
										$row [] = $row_data ['content'];
										if ($is_get_all) {
											$row [] = "<a href='" . api_get_path ( WEB_CODE_PATH ) . "course_sms/index.php?cidReq=" . $row_data ['course_code'] . "'>" . $row_data ['memo'] . "</a>";
										}
										if ($row_data ['is_to_all'] == 0) {
											$row [] = "<a class='thickbox' href='send.php?send_to_user=" . $row_data ['sender'] . "&course_code=" . $row_data ['course_dbname'] . "height=320&width=700&TB_iframe=true&KeepThis=true&modal=true'>" .
													 Display::return_icon ( 'forum.gif', get_lang ( 'Feedback' ) ) .
													 "</a>
					&nbsp;<a href='" .
													 ($is_get_all ? 'desktop_sms' : 'index') . ".php?action=delete&course_code=" . $row_data ['course_dbname'] . "&sms_id=" . $row_data ['id'] . '\' onClick="{if(confirm(\'' . get_lang ( "DeleteConfirm" ) . '\')){return true;}return false;}">' .
													 Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ) ) . "</a>";
										} else {
											$row [] = "";
										}
										$table_data [] = $row;
									}
									
									$query_vars ['action'] = 'received';
									$query_vars = @array_merge ( $query_vars, $parameters );
									
									$sorting_options = array ();
									$sorting_options ['column'] = 2;
									$sorting_options ['default_order_direction'] = 'DESC';
									//$paging_options['per_page']=2;
									$form_actions = array ('delete' => get_lang ( 'DeleteAll' ) );
									Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, $form_actions );
								
								} else {
									echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
								}
							
							}

							//************************************已发送SMS
							function _get_all_coursesms_send_sql($user_id = NULL) {
								$table_user = Database::get_main_table ( TABLE_MAIN_USER );
								
								$my_courses = CourseSMSManager::get_all_my_courses ( $user_id );
								
								foreach ( $my_courses as $key => $value ) {
									$table_course_sms = self::_table_course_sms ( $value ['db_name'] );
									$table_course_sms_received = self::_table_course_sms_received ( $value ['db_name'] );
									$view_sms_receivers_list = Database::get_course_table ( VIEW_TOOL_SMS_RECEIVERS_LIST, $value ['db_name'] );
									
									$sql .= " UNION ALL SELECT $table_course_sms.id,$table_course_sms.is_to_all,$table_course_sms.sender,
			$table_course_sms.send_time,$table_course_sms.content
			,('" . $value ['title'] . "-" . $value ['code'] . "') as memo,('" . $value ['code'] . "') as course_code,('" . $value ['db_name'] . "') as course_dbname
			,$view_sms_receivers_list.receivers_name FROM " . $table_course_sms . "," . $view_sms_receivers_list .
											 "  where $table_course_sms.sender='" . $user_id . "' AND $view_sms_receivers_list.sms_id=$table_course_sms.id";
										}
										if (strpos ( $sql, ' UNION ALL' ) == 0) $sql = substr ( $sql, 11 );
										return $sql;
									}

									function _get_coursesms_send_sql($user_id = NULL, $db_name = '') {
										$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS, $db_name );
										$view_sms_receivers_list = Database::get_course_table ( VIEW_TOOL_SMS_RECEIVERS_LIST, $db_name );
										$sql = "SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content,t2.receivers_name FROM " . $table_course_sms . " as t1," . $view_sms_receivers_list . " as t2 where t1.id=t2.sms_id AND t1.sender='" . Database::escape_string ( $user_id ) . "'";
										if (DB_MODE == 'single') $sql .= " AND t1.cc='" . api_get_course_code () . "' ";
										return $sql;
									}

									/**
									 * 显示已发送SMS列表
									 *
									 * @param unknown_type $user_id
									 * @param unknown_type $start
									 */
									function display_sms_list_send($user_id = NULL, $parameters = array(), $is_get_all = true, $db_name = '') {
										$table_user = Database::get_main_table ( TABLE_MAIN_USER );
										
										if ($is_get_all) {
											$sql = CourseSMSManager::_get_all_coursesms_send_sql ( $user_id );
										} else {
											$sql = CourseSMSManager::_get_coursesms_send_sql ( $user_id, $db_name );
										}
										
										if (is_not_blank ( $sql )) {
											$sql = "SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (" . $sql . ") t left join " . $table_user . " as t3 ON t.sender=t3.user_id WHERE 1=1 ";
											if (is_array ( $parameters ) && array_key_exists ( 'keyword_content', $parameters ) && ! empty ( $parameters ['keyword_content'] )) {
												$sql .= " AND t.content LIKE '%" . Database::escape_string ( $parameters ['keyword_content'] ) . "%' ";
											}
											
											if (is_not_blank ( $_GET ['keyword_start'] )) {
												$start_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_start'] );
												$sql .= " AND t.send_time >='" . $start_date . "' ";
											}
											if (is_not_blank ( $_GET ['keyword_end'] )) {
												$end_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_end'] );
												$sql .= " AND t.send_time <='" . $end_date . "' ";
											}
											
											//echo $sql;
											$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
											
											//$table_header[0] = array('',false);
											$table_header [] = array (get_lang ( 'Receivers' ), true );
											$table_header [] = array (get_lang ( 'SendTime' ), true, null, array ('width' => '150' ) );
											$table_header [] = array (get_lang ( 'Content' ), false );
											if ($is_get_all) {
												$table_header [] = array (get_lang ( 'Remark' ), true, null, array ('width' => '250' ) );
											}
											//$table_header[] = array(get_lang('Actions'),false);
											$table_data = array ();
											
											while ( $row_data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
												$row = array ();
												$receivers_str = $row_data ['receivers_name'];
												//$row[]=$row_data['id'];
												$row [] = $receivers_str;
												$row [] = $row_data ['send_time'];
												$row [] = $row_data ['content'];
												if ($is_get_all) {
													//$row[]=$row_data['memo'];
													$row [] = "<a href='" . api_get_path ( WEB_CODE_PATH ) . "course_sms/index.php?cidReq=" . $row_data ['course_code'] . "'>" . $row_data ['memo'] . "</a>";
												}
												
												$table_data [] = $row;
											}
											
											$query_vars ['action'] = 'sent';
											$query_vars = @array_merge ( $query_vars, $parameters );
											
											$sorting_options = array ();
											$sorting_options ['column'] = 1;
											$sorting_options ['default_order_direction'] = 'DESC';
											//$paging_options['per_page']=2;
											$paging_options ['tablename'] = 'sent_crs_sms';
											//$form_actions=array ('delete' => get_lang('DeleteAll'));
											Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, $form_actions );
										
										} else {
											echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
										}
									
									}

									function _get_req_date($name) {
										return Database::escape_string ( $name );
									}

									/**
									 * 查询短消息
									 *
									 * @param unknown_type $user_id
									 * @param unknown_type $parameters
									 * @param unknown_type $is_get_all
									 * @param unknown_type $db_name
									 */
									function display_sms_query_result($user_id = NULL, $parameters = array(), $is_get_all = true, $db_name = '') {
										if ($is_get_all) { //所有课程
											$query_sms_type = $parameters ['query_sms_type'];
											if (is_equal ( $query_sms_type, 'notread' )) {
												CourseSMSManager::display_sms_list_notread ( api_get_user_id (), $parameters, true );
											} elseif (is_equal ( $query_sms_type, 'received' )) {
												CourseSMSManager::display_sms_list_received ( api_get_user_id (), $parameters, true );
											} elseif (is_equal ( $query_sms_type, 'sent' )) {
												CourseSMSManager::display_sms_list_send ( api_get_user_id (), $parameters, true );
											}
										} else { //当前课程
											

											$table_user = Database::get_main_table ( TABLE_MAIN_USER );
											$table_course_sms = self::_table_course_sms ( $db_name );
											$table_course_sms_received = self::_table_course_sms_received ( $db_name );
											
											//$keyword_user=$parameters['keyword_user'];
											$keyword_user = $parameters ['keyword_user'] ['TO_ID'];
											
											if (is_equal ( $parameters ['keyword_sender'], "1" )) { //发信息人
												$sql = "SELECT t1.id,	t1.sender,	t1.content,	t1.send_time
					FROM " . $table_course_sms . " as t1 ," . $table_course_sms_received . " as t2 WHERE   t1.id =t2.sms_id 
					AND t2.user_id='" . Database::escape_string ( $user_id ) . "' ";
												if (DB_MODE == 'single') $sql .= " AND t1.cc='" . api_get_course_code () . "' ";
												if (! empty ( $keyword_user ) && $keyword_user != '%') {
													$sql .= " AND t1.sender='" . $keyword_user . "' ";
												}
												
												if (is_not_blank ( $sql )) {
													$sql = "SELECT tbl.*,t3.firstname FROM (" . $sql . ") tbl left join " . $table_user . " as t3 ON tbl.sender=t3.user_id WHERE 1=1 ";
													
													if (is_array ( $parameters ) && array_key_exists ( 'keyword_content', $parameters ) && ! empty ( $parameters ['keyword_content'] )) {
														$sql .= " AND tbl.content LIKE '%" . Database::escape_string ( $parameters ['keyword_content'] ) . "%' ";
													}
													
													if (is_not_blank ( $parameters ['keyword_start'] )) {
														$start_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_start'] );
														$sql .= " AND tbl.send_time >='" . $start_date . "' ";
													}
													if (is_not_blank ( $parameters ['keyword_end'] )) {
														$end_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_end'] );
														$sql .= " AND tbl.send_time <='" . $end_date . "' ";
													}
													
													//echo $sql;
													$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
													$table_header [] = array (get_lang ( 'Receivers' ), true );
													$table_header [] = array (get_lang ( 'SendTime' ), true );
													$table_header [] = array (get_lang ( 'Content' ), true, 'width="55%"' );
													$table_header [] = array (get_lang ( 'Remark' ), true );
													//$table_header[] = array(get_lang('Actions'),false);
													$table_data = array ();
													
													while ( $row_data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
														$row = array ();
														$receivers_arr = CourseSMSManager::_get_sms_receivers ( $row_data ['id'], $db_name );
														
														if (is_array ( $receivers_arr )) {
															foreach ( $receivers_arr as $key => $value ) {
																$recevers_array [] = $value ['firstname'] . "" . $value ['lastname'];
															}
															$receivers_str = implode ( ',', $recevers_array );
															unset ( $recevers_array );
														} else {
															$receivers_str = $receivers_arr;
														}
														
														$row [] = $receivers_str;
														$row [] = $row_data ['send_time'];
														$row [] = $row_data ['content'];
														$row [] = $row_data ['memo'];
														$table_data [] = $row;
													}
												}
											} else { //收信人
												$sql = "SELECT t1.id,	t1.sender,	t1.content,t1.send_time  FROM " . $table_course_sms . " as t1 ," . $table_course_sms_received . " as t2 WHERE t1.id =t2.sms_id	AND t1.sender='" . Database::escape_string ( $user_id ) . "' ";
												if (DB_MODE == 'single') $sql .= " AND t1.cc='" . api_get_course_code () . "' ";
												if (! empty ( $keyword_user ) && $keyword_user != '%') {
													$sql .= " AND t2.user_id='" . $keyword_user . "' ";
												}
												
												if (is_not_blank ( $sql )) {
													$sql = "SELECT tbl.*,t3.firstname FROM (" . $sql . ") tbl left join " . $table_user . " as t3 ON tbl.sender=t3.user_id WHERE 1=1 ";
													
													if (is_array ( $parameters ) && array_key_exists ( 'keyword_content', $parameters ) && ! empty ( $parameters ['keyword_content'] )) {
														$sql .= " AND tbl.content LIKE '%" . Database::escape_string ( $parameters ['keyword_content'] ) . "%' ";
													}
													
													if (is_not_blank ( $parameters ['keyword_start'] )) {
														$start_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_start'] );
														$sql .= " AND tbl.send_time >='" . $start_date . "' ";
													}
													if (is_not_blank ( $parameters ['keyword_end'] )) {
														$end_date = CourseSMSManager::_get_req_date ( $_GET ['keyword_end'] );
														$sql .= " AND tbl.send_time <='" . $end_date . "' ";
													}
													
													$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
													$table_header [] = array (get_lang ( 'Sender' ), true );
													$table_header [] = array (get_lang ( 'SendTime' ), true );
													$table_header [] = array (get_lang ( 'Content' ), true );
													$table_header [] = array (get_lang ( 'Remark' ), true );
													//$table_header[] = array(get_lang('Actions'),false);
													$table_data = array ();
													
													while ( $row_data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
														$row = array ();
														$row [] = $row_data ['firstname'];
														$row [] = $row_data ['send_time'];
														$row [] = $row_data ['content'];
														$row [] = $row_data ['memo'];
														$table_data [] = $row;
													}
												}
											}
											
											if ($table_header && $table_data) {
												$query_vars ['action'] = 'do_query';
												$query_vars = @array_merge ( $query_vars, $parameters );
												$sorting_options = array ();
												$sorting_options ['column'] = 1;
												$sorting_options ['default_order_direction'] = 'DESC';
												$paging_options ['tablename'] = 'sent_crs_sms';
												Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, $form_actions );
											} else {
												echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
											}
										}
									
									}

									function _get_sms_receivers($sms_id, $course_dbname) {
										$view_sms_receivers = Database::get_course_table ( VIEW_TOOL_SMS_RECEIVERS, $value ['db_name'] );
										
										$sql = "SELECT t2.firstname,t2.lastname,t2.username,t2.user_id FROM " . $view_sms_receivers . " as t2 WHERE t2.sms_id='" . Database::escape_string ( $sms_id ) . "' AND t2.is_deleted=0";
										//echo $sql.'<p>';
										return api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
									
									}

									function get_sms_info($sms_id, $db_name = '') {
										$table_user = Database::get_main_table ( TABLE_MAIN_USER );
										
										$sql = "SELECT t1.*,t2.username,t2.firstname,t2.status FROM " . self::_table_course_sms ( $db_name ) . " as t1 left join " . $table_user . " as t2 ON t1.sender=t2.user_id WHERE t1.id='" . Database::escape_string ( $sms_id ) . "'";
										//echo $sql;
										$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
										return $rs ? Database::fetch_array ( $rs, 'ASSOC' ) : false;
									}

									function is_sms_read($user_id, $sms_id, $db_name = '') {
										$table_course_sms_received = self::_table_course_sms_received ( $db_name );
										
										$sql = "SELECT is_read from " . $table_course_sms_received . "  WHERE sms_id='" . Database::escape_string ( $sms_id ) . "' AND user_id='" . Database::escape_string ( $user_id ) . "'";
										return (Database::get_scalar_value ( $sql ) == 1 ? true : false);
									
									}

									/**
									 * 点击”我知道了“后处理动作，即”不再提醒“
									 *
									 * @param unknown_type $user_id
									 * @param unknown_type $sms_id
									 * @param unknown_type $is_to_all
									 * @return unknown
									 */
									function read_sms($user_id, $sms_id, $db_name = '') {
										$table_course_sms_received = self::_table_course_sms_received ( $db_name );
										
										$sms_info = CourseSMSManager::get_sms_info ( $sms_id, $db_name );
										$sql = "UPDATE " . $table_course_sms_received . " SET is_read=1,read_time=NOW() WHERE sms_id='" . Database::escape_string ( $sms_id ) . "' AND user_id='" . Database::escape_string ( $user_id ) . "'";
										$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
										return true;
									}

									/**
									 * 根据条件获取课程相关学生列表
									 *
									 * @param unknown_type $condition
									 * @return unknown
									 */
									function get_user_list($condition = '', $all_classes_arr = array()) {
										//$table_user = Database :: get_main_table(TABLE_MAIN_USER);
										$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
										$sql = "SELECT t2.user_id,t2.lastname,t2.firstname,t2.username,t1.class_id,t2.status FROM " . $table_course_user . " AS t1 LEFT JOIN " . self::_table_user () . " AS t2 ON t1.user_id=t2.user_id WHERE 1=1 " . $condition;
										$res = api_sql_query ( $sql, __FILE__, __LINE__ );
										$users = array ();
										while ( $obj = mysql_fetch_object ( $res ) ) {
											$value = $obj->firstname . ' ' . $obj->lastname . '(' . $obj->username;
											if ($obj->status == STUDENT) {
												if ($all_classes_arr && is_array ( $all_classes_arr )) {
													$value .= "," . $all_classes_arr [$obj->class_id];
												}
											}
											$value .= ")";
											$users [$obj->user_id] = $value;
										}
										return $users;
									}
								}
								?>