<?php
require_once 'cls.course_stat.php';

class ScormTrackStat extends TrackStat {
	
	var $tbl_lp;
	var $tbl_lp_item;
	var $tbl_lp_item_view;
	var $tbl_lp_view;
	var $tbl_outline;
	var $tbl_courseware;
	var $tbl_track_cw;
	
	var $tbl_course_quiz;
	var $tbl_stats_exercices;
	var $tbl_stats_exercices_attempts;

	function __construct() {
		parent::TrackStat ();
		$this->ScormTrackStat ();
	}

	function ScormTrackStat() {
		$this->tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
		$this->tbl_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
		$this->tbl_lp_item_view = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
		$this->tbl_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
		//$this->tbl_outline = Database::get_course_table ( TABLE_COURSE_OUTLINKE );
		$this->tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
		
		$this->tbl_course_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
		$this->tbl_stats_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$this->tbl_stats_exercices_attempts = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
		$this->tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
	}

	/**
	 * 课程学习总时间
	 * @param $user_id
	 * @param $course_code
	 */
	function get_total_learning_time($user_id, $course_code) {
		//$sql = "SELECT FROM_UNIXTIME(start_time) FROM " . $this->tbl_lp_item_view . " WHERE lp_view_id =(SELECT id FROM " . $this->tbl_lp_view . " WHERE user_id='" . escape ( $user_id ) . "' AND cc='" . escape ( $course_code ) . "' ORDER BY id DESC)";
		$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "'";
		$sql = "SELECT SUM(total_time) FROM " . $this->tbl_track_cw . " WHERE " . $sqlwhere;
		//echo $sql.'<br/>';
		return Database::get_scalar_value ( $sql );
	}

	/**
	 * 上次学习时间
	 * @param $user_id
	 * @param $course_code
	 */
	function get_last_learning_time($user_id, $course_code) {
		$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "'";
		$sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(last_access_time),'%Y-%m-%d %H:%i') FROM " . $this->tbl_track_cw . " WHERE " . $sqlwhere . " ORDER BY last_access_time DESC ";
		//echo $sql.'<br/>';
		return Database::get_scalar_value ( $sql );
	}

	/**
	 * 某个课件学习总时间
	 * @param unknown_type $user_id
	 * @param unknown_type $course_code
	 * @param unknown_type $lp_id
	 */
	function get_scorm_learning_time($user_id, $course_code, $lp_id) {
		$sql = "SELECT SUM(total_time) FROM " . $this->tbl_lp_item_view . " WHERE lp_view_id IN (SELECT id FROM " . $this->tbl_lp_view . " WHERE user_id='" . escape ( $user_id ) . "' AND cc='" . escape ( $course_code ) . "' AND lp_id='" . escape ( $lp_id ) . "')";
		return Database::get_scalar_value ( $sql );
	}

	/**
	 * 某个课件上次学习时间
	 * @param unknown_type $user_id
	 * @param unknown_type $course_code
	 * @param unknown_type $lp_id
	 */
	function get_scorm_last_learning_time($user_id, $course_code, $lp_id) {
		$tbl_crs_scorm_scoes_track = Database::get_course_table ( TABLE_SCORM_SCOES_TRACK );
		$sqlwhere = "userid='" . escape ( $user_id ) . "' AND scormid='" . escape ( $lp_id ) . "' AND cc='" . escape ( $course_code ) . "' AND element='cmi.core.total_time'";
		$sql = "SELECT value FROM $tbl_crs_scorm_scoes_track WHERE " . $sqlwhere;
		$values = Database::get_into_array ( $sql );
		$total_time = 0;
		if ($values && is_array ( $values )) {
			foreach ( $values as $val ) {
				$total_time += api_hms_to_time ( $val );
			}
		}
		return $total_time;
	}

	function get_scorm_last_learning_status($user_id, $course_code, $lp_id) {
		$sql = "SELECT status FROM " . $this->tbl_lp_item_view . " WHERE lp_view_id =(SELECT id FROM " . $this->tbl_lp_view . " WHERE user_id='" . escape ( $user_id ) . "' AND cc='" . escape ( $course_code ) . "' AND lp_id=" . Database::escape ( $lp_id ) .
				 " ORDER BY id DESC LIMIT 1) ORDER BY start_time DESC LIMIT 1";
				return Database::get_scalar_value ( $sql );
			}

			function get_courseware_progress($user_id, $course_code, $cw_id) {
				$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
				$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND cw_id=" . Database::escape ( $cw_id );
				$sql = "SELECT progress FROM $tbl_track_cw WHERE " . $sqlwhere;
				$progress = Database::get_scalar_value ( $sql );
				if (empty ( $progress )) $progress = 0;
				return $progress > 100 ? 100 : $progress;
			}

			/**
			 * 检查课程是否已完成
			 * @param $user_id
			 * @param $course_code
			 * @param $check_quiz
			 */
			function is_course_finish($user_id, $course_code, $check_quiz = FALSE) {
				$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
				$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
				
				//所有学习内容(课件)
				$is_cw_completed = TRUE;
				$sql = "SELECT id FROM $tbl_courseware WHERE cc='" . escape ( $course_code ) . "' AND visibility=1";
				$cw_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
				if ($cw_ids && is_array ( $cw_ids ) && count ( $cw_ids ) > 0) {
					foreach ( $cw_ids as $cw_id ) {
						$progress = $this->get_courseware_progress ( $user_id, $course_code, $cw_id );
						if ($progress != 100) {
							$is_cw_completed = FALSE;
							break;
						}
					}
				}
				
				//检查测验通过情况
				$is_quiz_pass = TRUE;
				
				return ($is_cw_completed && $is_quiz_pass);
			
			}

			/**
			 * 课程的总体进度
			 * @param $course_code
			 * @param $user_id
			 */
			function get_course_progress($course_code, $user_id) {
				$caculate_method = 'avg'; //avg  completed_total
				$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
				$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
				$sql = "SELECT id FROM $tbl_courseware WHERE cc='" . escape ( $course_code ) . "' AND visibility=1";
				$cw_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
				if (empty ( $cw_ids ) or count ( $cw_ids ) == 0) return 0;
				
				//学习状态为完成或通过,课程进度显示100
				/*$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
		$sql = "SELECT is_pass FROM " . $view_course_user . " WHERE user_id='" . escape ( $user_id ) . "' AND course_code=" . Database::escape ( $course_code );
		$learning_status = Database::get_scalar_value ( $sql );
		if (in_array ( $learning_status, array (LEARNING_STATE_COMPLETED, LEARNING_STATE_PASSED ) )) {
			return 100;
		}*/
				
				if (is_array ( $cw_ids ) && count ( $cw_ids ) == 1) { //只有一节时, 这节的进度作为课程进度
					$progress = $this->get_courseware_progress ( $user_id, $course_code, $cw_ids [0] );
					return $progress;
				} else {
					$total = count ( $cw_ids );
					$sqlwhere = " AND user_id=" . Database::escape ( $user_id ) . " AND cc=" . Database::escape ( $course_code );
					if ($caculate_method == 'completed_total') {
						$sql = "SELECT COUNT(*) FROM $tbl_track_cw WHERE progress=100 " . $sqlwhere . " AND " . Database::create_in ( $cw_ids, 'cw_id' );
						$completed = Database::get_scalar_value ( $sql );
						return ($total != 0) ? round ( $completed / $total * 100 ) : 0;
					}
					if ($caculate_method == 'avg') {
						$sql = "SELECT SUM(progress) FROM $tbl_track_cw WHERE 1 " . $sqlwhere . " AND " . Database::create_in ( $cw_ids, 'cw_id' ); //平均
						$completed = Database::get_scalar_value ( $sql );
						return ($total > 0 ? round ( $completed / $total, 1 ) : 0);
					}
				}
				return 0;
			}

			function get_learning_progress($user_id, $course_code, $lp_id, $lp_maker = '') {
				if (empty ( $lp_maker )) {
					$sql = "SELECT content_maker FROM " . $this->tbl_lp . " WHERE id=" . Database::escape ( $lp_id );
					$lp_maker = Database::get_scalar_value ( $sql );
				}
				if ($lp_maker == "articulate") {
					$progress = $this->get_articulate_progress ( $user_id, $course_code, $lp_id );
				} elseif ($lp_maker == "single_sco") {
					$progress = $this->get_scorm_learning_progress ( $user_id, $course_code, $lp_id );
				}
				return $progress;
			}

			/**
			 * SCORM课件的播放进度
			 * @param $user_id
			 * @param $course_code
			 * @param $lp_id
			 */
			function get_scorm_learning_progress($user_id, $course_code, $lp_id) {
				$sql = "SELECT (learning_time*60) FROM " . $this->tbl_lp . " WHERE id='" . escape ( $lp_id ) . "' AND cc='" . escape ( $course_code ) . "'";
				$learning_time = Database::get_scalar_value ( $sql );
				
				if (! empty ( $learning_time ) && $learning_time > 0) {
					$sql = "SELECT ROUND(total_time/" . $learning_time . "*100) AS progress,status FROM " . $this->tbl_lp_item_view . " WHERE lp_view_id =(SELECT id FROM " . $this->tbl_lp_view . " WHERE user_id='" . escape ( $user_id ) . "' AND cc='" . escape ( $course_code ) . "' AND lp_id='" .
							 escape ( $lp_id ) . "' ORDER BY id DESC LIMIT 1) ORDER BY start_time DESC";
							$row = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
							if ($row && in_array ( $row ["status"], array ('completed', 'passed', 'succeeded' ) )) {
								return 100;
							} else {
								return $row ["progress"];
							}
						
						}
						return 0;
					}

					function get_articulate_progress($user_id, $course_code, $lp_id) {
						$sql = "SELECT suspend_data,status FROM " . $this->tbl_lp_item_view . " WHERE lp_view_id =(SELECT id FROM " . $this->tbl_lp_view . " WHERE user_id='" . escape ( $user_id ) . "' AND cc='" . escape ( $course_code ) . "' AND lp_id='" . escape ( $lp_id ) .
								 "' ORDER BY id DESC LIMIT 1) ORDER BY start_time DESC";
								$row = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
								if ($row && in_array ( $row ["status"], array ('completed', 'passed', 'succeeded', 'failed' ) )) {
									return 100;
								} else {
									$total_slide = $this->_get_articulate_total ( $course_code, $lp_id );
									if ($row ["suspend_data"]) {
										$suspend_node = explode ( "|", $row ["suspend_data"] );
										$viewed_arr = explode ( "=", $suspend_node [0] );
										$viewed_str = $viewed_arr [1];
										$viewed_nodes = explode ( ",", $viewed_str );
										$view_count = count ( $viewed_nodes );
										if ($total_slide > 0) {
											$progress = round ( $view_count / $total_slide, 2 ) * 100;
											return $progress;
										}
									}
								}
								return 0;
							}

							function _get_articulate_total($course_code, $lp_id, $type = "ppt_slide") {
								if ($type == "ppt_slide" && $lp_id) {
									$sql = "SELECT path FROM " . $this->tbl_lp . " WHERE id='" . escape ( $lp_id ) . "'";
									$scorm_path = Database::get_scalar_value ( $sql );
									$course_path = api_get_course_path ( $course_code );
									$path = api_get_path ( SYS_COURSE_PATH ) . $course_path . "/scorm/" . substr ( $scorm_path, 0, - 1 );
									$xml_file = $path . "data/presentation.xml";
									
									$doc = new DOMDocument ();
									if (file_exists ( $xml_file )) {
										$res = $doc->load ( $xml_file );
										if ($res === false) {
											api_error_log ( 'ERROR - In parse Articulate PPT XML File - Exception thrown when loading ' . $file . ' in DOMDocument', __FILE__, __LINE__ );
											return 0;
										}
									} else {
									
									}
									
									$nodes = $doc->getElementsByTagName ( "LmsProperties" );
									foreach ( $nodes as $node ) {
										$completion_node = $node->getElementsByTagName ( "completion" );
										foreach ( $completion_node as $node1 ) {
											$threshold_node = $node1->getElementsByTagName ( "threshold" );
											$val = $threshold_node->item ( 0 )->nodeValue;
										}
									}
									return $val;
								}
								return 0;
							}

							function get_last_learning_item_url($course_code, $user_id) {
								$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id );
								$sql = "SLECT t1.*,t2.* FROM " . $this->tbl_track_cw . " AS t1 LEFT JOIN " . $this->tbl_courseware . " AS t2 ON t1.cw_id=t2.id WHERE " . $sqlwhere . " ORDER BY last_access_time DESC LIMIT 1";
								$result = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
								if ($result) { // 存在上次学习课件
									switch ($result ['cw_type']) {
										case 'scorm' :
											
											break;
										case 'html' :
											
											break;
										
										case 'link' :
											
											break;
										case 'media' :
											
											break;
									}
								} else {
								
								}
							}

							/**
							 * 上次学习到的章节信息
							 * @param unknown_type $course_code
							 * @param unknown_type $user_id
							 */
							function get_last_scorm_lp_item($course_code, $user_id) {
								
								//最后一次学习的LP
								$sql = "SELECT lp_id,id FROM " . $this->tbl_lp_view . " WHERE cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' ORDER BY id DESC LIMIT 1";
								$result = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
								
								if ($result ["lp_id"] && $result ["id"]) {
									//最后学习的SCO
									$sql = "SELECT FROM_UNIXTIME(start_time) AS start_time, total_time,lp_item_id AS item_id,status FROM " . $this->tbl_lp_item_view . " WHERE cc='" . escape ( $course_code ) . "' AND lp_view_id='" . $result ["id"] .
											 "'  ORDER BY start_time DESC";
											//echo $sql;
											$row1 = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
											$row2 ["lp_id"] = $result ["lp_id"];
											//var_dump(array_merge($row1,$row2));
											return array_merge ( $row1, $row2 );
										}
										return false;
									}

									/**
									 * 获取单SCO的SCORM课程学习进度
									 * @param unknown_type $course_code
									 * @param unknown_type $user_id
									 */
									function get_course_progress_single_sco($course_code, $user_id) {
										//总共节数
										$sql = "SELECT id FROM " . $this->tbl_courseware . " WHERE cc='" . escape ( $course_code ) . "' AND visibility=1";
										$cw_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
										$total = count ( $cw_ids );
										
										$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
										$sqlwhere = " AND user_id=" . Database::escape ( $user_id ) . " AND cc=" . Database::escape ( $course_code );
										$sql = "SELECT COUNT(*) FROM $tbl_track_cw WHERE progress=100 " . $sqlwhere . " AND " . Database::create_in ( $cw_ids, 'cw_id' );
										//echo $sql;
										$completed = Database::get_scalar_value ( $sql );
										
										$sql = "SELECT ROUND(" . $completed . "/" . $total . "*100,1)";
										$rtn = Database::get_scalar_value ( $sql );
										return $rtn;
									}

									//-------------------------------------- 测验统计
									

									function get_course_exam_info($course_code) {
										$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
										$sql = "SELECT * FROM $tbl_quiz AS ce WHERE active=1 AND type=2 AND ce.cc='" . $course_code . "'";
										$sql .= " ORDER BY created_date DESC";
										$exam_info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
										return $exam_info;
									}

									function get_course_exam_score($user_id, $course_code) {
										$exam_info = $this->get_course_exam_info ( $course_code );
										if ($exam_info && $user_id && $course_code) {
											$exerciseId = $exam_info ['id'];
											$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
											$sqlwhere = " exam_id=" . Database::escape ( $exerciseId ) . " AND user_id='" . $user_id . "'";
											$sql = "SELECT score FROM $tbl_exam_rel_user WHERE " . $sqlwhere;
											$rtn = Database::get_scalar_value ( $sql );
											return $rtn;
										}
									}

									/**
									 * 测验总次数
									 * @param $user_id
									 * @param $course_code
									 * @param $quiz_id
									 */
									function get_quiz_attemps($user_id, $course_code, $quiz_id) {
										$sql = "SELECT COUNT(ex.exe_id) as essais FROM " . $this->tbl_stats_exercices . " AS ex
					WHERE  ex.exe_cours_id = '" . escape ( $course_code ) . "'
					AND exe_user_id='" . escape ( $user_id ) . "'
					AND ex.exe_exo_id = '" . escape ( $quiz_id ) . "'";
										return Database::get_scalar_value ( $sql );
									}

									/**
									 * 测验最高分,最低分,平均分
									 * @param unknown_type $user_id
									 * @param unknown_type $course_code
									 * @param unknown_type $quiz_id
									 */
									function get_max_min_avg_quiz_score($user_id, $course_code, $quiz_id) {
										$sql = "SELECT MAX(exe_result),MIN(exe_result),AVG(exe_result) FROM " . $this->tbl_stats_exercices . " AS ex
						WHERE  ex.exe_cours_id = '" . escape ( $course_code ) . "'
						AND ex.exe_exo_id = " . escape ( $quiz_id ) . "	AND exe_user_id='" . escape ( $user_id ) . "'";
										$res = api_sql_query ( $sql, __FILE__, __LINE__ );
										list ( $max_score, $min_score, $avg_score ) = Database::fetch_row ( $res );
										return array ("max" => $max_score, "min" => $min_score, "avg" => $avg_score );
									}

									/**
									 * 获取测验成绩(以最高分为准)
									 * @param $user_id
									 * @param $course_code
									 * @param $quiz_id
									 */
									function get_quiz_score($user_id, $course_code, $quiz_id) {
										$sql = "SELECT MAX(exe_result),ROUND(MAX(exe_result*100/exe_weighting),1) FROM " . $this->tbl_stats_exercices . " AS ex
						WHERE  ex.exe_cours_id = '" . escape ( $course_code ) . "'
						AND ex.exe_exo_id = " . escape ( $quiz_id ) . "	AND exe_user_id='" . escape ( $user_id ) . "'";
										$res = api_sql_query ( $sql, __FILE__, __LINE__ );
										list ( $raw_score, $percent_score ) = Database::fetch_row ( $res );
										return array ("raw_score" => $raw_score, "percent_score" => $percent_score );
									}

									/**
									 * 某用户最后一次参加测验的情况
									 * @param $user_id
									 * @param $course_code
									 */
									function get_last_quiz_info($user_id, $course_code, $quiz_id) {
										$sql = "SELECT exe_id, exe_result,exe_weighting,exe_date FROM " . $this->tbl_stats_exercices . "
				WHERE exe_user_id = '" . escape ( $user_id ) . "' AND exe_cours_id = '" . escape ( $course_code ) . "'
				AND exe_exo_id = '" . escape ( $quiz_id ) . "' ORDER BY exe_date DESC LIMIT 1";
										$res = api_sql_query ( $sql, __FILE__, __LINE__ );
										list ( $exe_id, $exe_score, $paper_score, $exe_date ) = Database::fetch_row ( $res );
										return array ("exe_id" => $exe_id, "exe_score" => $exe_score, "paper_score" => $paper_score, "exe_date" => $exe_date );
									}
								
								}

										