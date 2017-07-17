<?php

/*
 ==============================================================================

 ==============================================================================
 */

class Tracking {

	/* ==============================================================================
	 为提升性能，V2.0开发的新功能
	 ==============================================================================
	 */
	
	function get_average_student_score($course_code, $nb_students_in_course) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$tbl_track_exercise = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$sql = "SELECT  SUM(exe_result)/SUM(exe_weighting)	FROM " . $tbl_track_exercise . " AS t1,
		$tbl_course_user AS t2	WHERE t1.exe_user_id=t2.user_id AND t2.status=" . STUDENT . " AND t1.exe_cours_id = '" . escape ( $course_code ) . "' AND t2.course_code='" .
				 escape ( $course_code ) . "'";
				//echo $sql."<br/>";
				$pourcentageScore = Database::get_scalar_value ( $sql );
				if ($nb_students_in_course > 0) {
					$avg_score_in_course = round ( ($pourcentageScore * 100) / $nb_students_in_course, 1 ) . ' %';
				}
				return $avg_score_in_course;
			}

			/* ==============================================================================
	 END: 为提升性能，V2.0开发的新功能
	 ==============================================================================
	 */
			
			//liyu: 统计在平台的总时间，用MYSQL函数
			function get_time_spent_on_the_platform($user_id) {
				
				$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
				
				$sql = 'SELECT ABS(SUM(UNIX_TIMESTAMP(logout_date)-UNIX_TIMESTAMP(login_date))) FROM ' . $tbl_track_login . '
				WHERE login_user_id = ' . intval ( $user_id );
				//echo $sql;
				return Database::get_scalar_value ( $sql );
			}

			function get_last_connection_date($student_id, $warning_message = false) {
				$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
				$sql = 'SELECT login_date FROM ' . $tbl_track_login . '
				WHERE login_user_id = ' . intval ( $student_id ) . '
				ORDER BY login_date DESC LIMIT 0,1';
				
				$last_login_date = Database::get_scalar_value ( $sql, __FILE__, __LINE__ );
				if ($last_login_date) {
					if (! $warning_message) {
						//liyu
						//return format_locale_date(get_lang('DateFormatLongWithoutDay'),strtotime($last_login_date));
						return $last_login_date;
					} else {
						$timestamp = strtotime ( $last_login_date );
						$currentTimestamp = mktime ();
						
						//If the last connection is > than 7 days, the text is red
						//345600 = 7 days in seconds
						if ($currentTimestamp - $timestamp > 345600) {
							//liyu
							//return '<span style="color: #F00;">'.format_locale_date(get_lang('DateFormatLongWithoutDay'),strtotime($last_login_date)).'</span>';
							return '<span style="color: #F00;">' . $last_login_date . '</span>';
						} else {
							//liyu
							//return format_locale_date(get_lang('DateFormatLongWithoutDay'),strtotime($last_login_date));
							return $last_login_date;
						}
					}
				} else {
					return false;
				}
			}

			function count_course_per_student($user_id) {
				
				$user_id = intval ( $user_id );
				$tbl_course_rel_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
				
				$sql = 'SELECT DISTINCT course_code
				FROM ' . $tbl_course_rel_user . '
				WHERE user_id = ' . $user_id;
				$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
				$nb_courses = mysql_num_rows ( $rs );
				
				return $nb_courses;
			}

			/**
			 * 每个给定用户的平均进度: 某一用户总进度和/LP数
			 * @param $student_id
			 * @param $course_code
			 */
			function get_avg_student_progress($student_id, $course_code) {
				require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
				
				// protect datas
				$student_id = intval ( $student_id );
				$course_code = Database::escape_string ( $course_code );
				$avg_progress = 0;
				
				$tbl_course_lp = Database::get_course_table ( TABLE_LP_MAIN );
				$tbl_course_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
				$sql = 'SELECT id FROM ' . $tbl_course_lp . " WHERE cc='" . $course_code . "'";
				$lp_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
				
				$sqlProgress = "SELECT AVG(progress) FROM " . $tbl_course_lp_view . " AS lp_view
								LEFT JOIN $tbl_course_lp AS t2 ON lp_view.lp_id=t2.id
			WHERE lp_view.user_id = " . $student_id . " AND " . Database::create_in ( $lp_ids, "lp_view.lp_id" );
				//echo $sqlProgress."<br/>";
				$avg_progress = Database::get_scalar_value ( $sqlProgress );
				
				return $avg_progress;
			}

			function get_avg_student_score($student_id, $course_code) {
				$student_id = intval ( $student_id );
				$course_code = addslashes ( $course_code );
				
				$sqlScore = "SELECT  exe_result,exe_weighting
		 				FROM " . Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES ) . "
						WHERE exe_user_id = " . $student_id . "
		 				AND exe_cours_id = '" . $course_code . "'";
				$resultScore = api_sql_query ( $sqlScore );
				$i = 0;
				$score = 0;
				while ( $a_score = mysql_fetch_array ( $resultScore ) ) {
					$score = $score + $a_score ['exe_result'];
					$weighting = $weighting + $a_score ['exe_weighting'];
					$i ++;
				}
				
				if ($weighting > 0) {
					$pourcentageScore = round ( ($score * 100) / $weighting );
				}
				
				return $pourcentageScore;
			}

			function count_student_assignments($student_id, $course_code) {
				require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
				
				// protect datas
				$student_id = intval ( $student_id );
				$course_code = addslashes ( $course_code );
				
				// get the informations of the course
				$a_course = CourseManager::get_course_information ( $course_code );
				
				// table definition
				$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY, $a_course ['db_name'] );
				$sql = "SELECT COUNT(*) FROM " . $tbl_item_property . "	WHERE insert_user_id='" . $student_id . "' AND tool='" . TOOL_ASSIGNMENT . "'";
				//echo $sql."<br/>";
				$total = Database::get_scalar_value ( $sql );
				return $total;
			}

			function count_student_visited_links($student_id, $course_code) {
				// protect datas
				$student_id = intval ( $student_id );
				$course_code = addslashes ( $course_code );
				
				// table definition
				$tbl_stats_links = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LINKS );
				
				$sql = 'SELECT COUNT(*)
				FROM ' . $tbl_stats_links . '
				WHERE links_user_id=' . $student_id . '
				AND links_cours_id="' . $course_code . '"';
				
				return Database::get_scalar_value ( $sql );
			}

			function count_student_downloaded_documents($student_id, $course_code) {
				// protect datas
				$student_id = intval ( $student_id );
				$course_code = addslashes ( $course_code );
				
				// table definition
				$tbl_stats_documents = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_DOWNLOADS );
				
				$sql = 'SELECT 1
				FROM ' . $tbl_stats_documents . '
				WHERE down_user_id=' . $student_id . '
				AND down_cours_id="' . $course_code . '"';
				
				$rs = api_sql_query ( $sql, __LINE__, __FILE__ );
				return mysql_num_rows ( $rs );
			}
		
		}
		
		?>