<?php

class SurveyManager {

	private function __construct() {

	}

	function get_survey($survey_id) {
		//	global $_course;
		//	if (isset ( $_GET ['course'] )) {
		//		$my_course_id = Security::remove_XSS ( $_GET ['course'] );
		//	} else {
		//		$my_course_id = api_get_course_id ();
		//	}
		

		global $tbl_survey;
		$sql = "SELECT * FROM $tbl_survey WHERE id='" . Database::escape_string ( $survey_id ) . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$return = array ();
		
		if (Database::num_rows ( $result ) > 0) {
			$return = Database::fetch_array ( $result, 'ASSOC' );
		}
		return $return;
	}

	function get_info($survey_id) {
		global $tbl_survey, $tbl_survey_user;
		//		if(empty($user_id)) $user_id=api_get_user_id();
		$sql = "SELECT t1.*,t2.* FROM $tbl_survey_user AS t1," . $tbl_survey . " AS t2 WHERE t1.survey_id=" . Database::escape ( $survey_id ) . " AND t1.survey_id=t2.id";
		$sql .= " ORDER BY t1.start_date DESC LIMIT 1";
		return Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
	}

	function store_survey($values) {
		global $tbl_survey;
		$values ['display_type'] = ALL_ON_ONE_PAGE;
		if (empty ( $values ['id'] ) or ! is_numeric ( $values ['id'] )) { //新增
			$sql = "SELECT id FROM " . $tbl_survey . " WHERE code='" . escape ( $values ['code'] ) . "'";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) {
				$return ['message'] = 'ThisSurveyCodeSoonExists';
				$return ['type'] = 'error';
				$return ['id'] = isset ( $values ['id'] ) ? $values ['id'] : 0;
				return $return;
			}
			
			//$title=Security::remove_XSS ( stripslashes ( api_html_entity_decode ( $values ['title'] ) ) );
			//$intro=Security::remove_XSS ( stripslashes ( api_html_entity_decode ( $values ['intro'] ) ) );
			$sql_data = array ('code' => $values ['code'], 
					'title' => trim ( $values ['title'] ), 
					'author' => api_get_user_name (), 
					'avail_from' => $values ['start_date'], 
					'avail_till' => $values ['end_date'], 
					'max_attempt' => $values ['max_attempt'], 
					'display_type' => $values ['display_type'], 
					'option_display_type' => $values ['option_display_type'], 
					'results_disabled' => $values ['results_disabled'], 
					'intro' => trim ( $values ['intro'] ), 
					'surveythanks' => trim ( $values ['surveythanks'] ), 
					'creation_date' => date ( 'Y-m-d H:i:s' ), 
					'created_user' => api_get_user_id () );
			$sql = Database::sql_insert ( $tbl_survey, $sql_data );
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			$survey_id = Database::get_last_insert_id ();
			$return ['message'] = 'SurveyCreatedSuccesfully';
			$return ['type'] = 'confirmation';
			$return ['id'] = $survey_id;
		} else {
			$sql = 'SELECT id FROM ' . $tbl_survey . ' WHERE code="' . Database::escape_string ( $values ['code'] ) . '" AND id!=' . intval ( $values ['id'] );
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) {
				$return ['message'] = 'ThisSurveyCodeSoonExistsInThisLanguage';
				$return ['type'] = 'error';
				$return ['id'] = isset ( $values ['id'] ) ? $values ['id'] : 0;
				return $return;
			}
			
			$sql_data = array ('title' => trim ( $values ['title'] ), 
					'avail_from' => $values ['start_date'], 
					'avail_till' => $values ['end_date'], 
					'max_attempt' => $values ['max_attempt'], 
					'display_type' => $values ['display_type'], 
					'option_display_type' => $values ['option_display_type'], 
					'results_disabled' => $values ['results_disabled'], 
					'intro' => trim ( $values ['intro'] ), 
					'surveythanks' => trim ( $values ['surveythanks'] ) );
			$sql_where = " id = '" . Database::escape_string ( $values ['id'] ) . "'";
			$sql = Database::sql_update ( $tbl_survey, $sql_data, $sql_where );
			//echo $sql;
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$return ['message'] = 'SurveyUpdatedSuccesfully';
			$return ['type'] = 'confirmation';
			$return ['id'] = $values ['id'];
		}
		
		return $return;
	}

	function delete_survey($survey_id) {
		global $tbl_survey, $tbl_survey_question_group;
		
		$sql = "DELETE from $tbl_survey WHERE id='" . Database::escape_string ( $survey_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE from $tbl_survey_question_group WHERE survey_id='" . Database::escape_string ( $survey_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		// deleting the questions of the survey
		self::delete_all_survey_questions ( $survey_id );
		
		return true;
	}

	function delete_all_survey_questions($survey_id) {
		global $tbl_survey_question;
		$sql = "DELETE from $tbl_survey_question WHERE survey_id='" . Database::escape_string ( $survey_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		self::delete_all_survey_questions_options ( $survey_id );
		
		self::delete_all_survey_answers ( $survey_id );
	}

	function delete_all_survey_questions_options($survey_id) {
		global $tbl_survey_question_option;
		
		$sql = "DELETE from $tbl_survey_question_option WHERE survey_id='" . Database::escape_string ( $survey_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return true;
	}

	function delete_all_survey_answers($survey_id) {
		global $tbl_survey_answer;
		api_sql_query ( 'DELETE FROM ' . $tbl_survey_answer . ' WHERE survey_id=' . $survey_id, __FILE__, __LINE__ );
		return true;
	}

	function add_user($survey_id, $user_id, $start_date, $end_date) {
		global $tbl_survey_user;
		$sql = "SELECT * FROM " . $tbl_survey_user . " WHERE user_id=" . Database::escape ( $user_id ) . " AND survey_id=" . Database::escape ( $survey_id );
		if (! Database::if_row_exists ( $sql ) && $user_id && $survey_id) {
			$sql_data = array ('user_id' => $user_id, 'survey_id' => $survey_id, 'start_date' => $start_date, 'end_date' => $end_date );
			$sql = Database::sql_insert ( $tbl_survey_user, $sql_data );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	function update_invited($survey_id) {
		global $tbl_survey, $tbl_survey_user;
		$sql = "UPDATE $tbl_survey SET invited=(SELECT COUNT(*) FROM $tbl_survey_user WHERE survey_id=" . Database::escape ( $survey_id ) . ") WHERE id=" . Database::escape ( $survey_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function update_answered($survey_id) {
		global $tbl_survey, $tbl_survey_user;
		$sql = "UPDATE $tbl_survey SET answered=(SELECT COUNT(*) FROM $tbl_survey_user WHERE survey_id=" . Database::escape ( $survey_id );
		$sql .= " AND (last_attempt_time IS NOT NULL AND last_attempt_time<>'') AND (data_tracking IS NOT NULL OR data_tracking<>'')) WHERE id=" . Database::escape ( $survey_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function del_user($survey_id, $user_id) {
		global $tbl_survey_user, $tbl_survey_answer;
		$sql = "DELETE FROM " . $tbl_survey_answer . " WHERE user_id=" . Database::escape ( $user_id ) . " AND survey_id=" . Database::escape ( $survey_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE FROM " . $tbl_survey_user . " WHERE user_id=" . Database::escape ( $user_id ) . " AND survey_id=" . Database::escape ( $survey_id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function get_user_surveys_pagelist($user_id, $sqlwhere = "", $page_size = NULL, $offset = 0) {
		global $tbl_survey, $tbl_survey_user;
		
		$sql = "SELECT COUNT(*) FROM $tbl_survey_user AS t1," . $tbl_survey . " AS t2 WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.survey_id=t2.id";
		if ($sqlwhere) $sql .= " AND " . $sqlwhere;
		$total_rows = Database::get_scalar_value ( $sql );
		
		$sql = "SELECT t1.*,t2.* FROM $tbl_survey_user AS t1," . $tbl_survey . " AS t2 WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.survey_id=t2.id";
		if ($sqlwhere) $sql .= " AND " . $sqlwhere;
		$sql .= " ORDER BY t1.start_date DESC";
		if (empty ( $offset )) $offset = 0;
		if (isset ( $page_size )) $sql .= " LIMIT " . $offset . "," . $page_size;
		//		echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$row = api_store_result ( $rs );
		return array ("data_list" => $row, "total_rows" => $total_rows );
	}

	/**
	 * 检查用户是否有权限进入某个调查
	 * @param $exam_id
	 * @param $user_id
	 */
	function is_survey_available($survey_id, $user_id) {
		global $tbl_survey, $tbl_survey_user, $_user;
		//if ($_user ['status'] > COURSEMANAGER) return 100;
		$allwedAccess = FALSE;
		$survey_info = self::get_survey ( $survey_id );
		if (empty ( $survey_info )) { //不存在
			return 101;
		} else { //调查存在
			if ($survey_info ["status"] != STATE_PUBLISHED) { //试卷存在,但是未发布草稿状态
				return 102;
			}
			
			//调查用户表survey_user中有记录才允许参加考试
			$sql = "SELECT * FROM $tbl_survey_user WHERE survey_id=" . Database::escape ( $survey_id ) . " AND user_id=" . Database::escape ( $user_id ) . " ORDER BY start_date DESC";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) {
				$row = Database::fetch_array ( $rs, 'ASSOC' );
				// 可参加考试时间段限制
				$examTimeAllowed = false;
				if ((empty ( $survey_info ["avail_from"] ) && empty ( $survey_info ["avail_till"] )) or ($survey_info ["avail_from"] == '0000-00-00' && $survey_info ["avail_till"] == '0000-00-00')) {
					$examTimeAllowed = TRUE;
				} else {
					if ((empty ( $row ['start_date'] ) && empty ( $row ['end_date'] )) or ($row ['start_date'] == '0000-00-00' && $row ['end_date'] == '0000-00-00')) {
						// 考试的默认可进入时间
						$sql = "SELECT * FROM " . $tbl_survey . " WHERE NOW() BETWEEN avail_from AND avail_till AND id=" . Database::escape ( $survey_id );
						$examTimeAllowed = Database::if_row_exists ( $sql );
					
					} else { // 用户考试时间有特殊安排时
						$sql = "SELECT start_date,end_date FROM $tbl_survey_user WHERE NOW() BETWEEN start_date AND end_date AND survey_id=" . Database::escape ( $survey_id ) . " AND user_id=" . Database::escape ( $user_id );
						$examTimeAllowed = Database::if_row_exists ( $sql );
					}
				
				}
				if ($examTimeAllowed === FALSE) {
					return 104;
				}
				
				if ($row ["last_attempt_time"] and ! is_equal ( $row ["last_attempt_time"], '0000-00-00 00:00:00' )) {
					return 105;
				}
				
				//是否到最大允许次数
				/*if ($survey_info ["max_attempt"] > 0) {
					$attempt = $this->get_user_attempts ( $user_id, $survey_id );
					if ($attempt >= $survey_info ["max_attempt"]) { //超过最大次数限制时
						return 105;
					}
				}*/
				return SUCCESS;
			} else {
				return 103;
			}
		}
		return FAILURE;
	}

	function get_survey_group_list($survey_id) {
		global $tbl_survey_question_group;
		$sql = "SELECT * FROM " . $tbl_survey_question_group . " WHERE survey_id=" . Database::escape ( $survey_id );
		$sql .= " ORDER BY display_order";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$row = api_store_result_array ( $rs );
		return $row;
	}

	/* 试题列表
	 * @param unknown_type $paper_sid
	 * @param unknown_type $sqlwhere
	 */
	function get_question_list($survey_id, $sqlwhere = "") {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_group;
		$sql = "SELECT t1.*,t2.name AS label_name,t2.display_order as disp_order FROM " . $tbl_survey_question . " AS t1 LEFT JOIN " . $tbl_survey_question_group . " AS t2 ON t1.group_id=t2.id WHERE t1.survey_id=" . Database::escape ( $survey_id );
		if ($sqlwhere) $sql .= $sqlwhere;
		//echo $sql.'<p>';
		$rs = Database::query ( $sql, __FILE__, __LINE__ );
		$row = api_store_result ( $rs );
		return $row;
	}

	function save_survey_submit_question($survey_id, $user_id, $quesId, $answer, $score) {
		global $_configuration;
		global $tbl_survey_answer;
		if ($_configuration ['tracking_enabled'] && $quesId && $survey_id && $user_id && $answer) {
			if (empty ( $user_id )) $user_id = api_get_user_id ();
			$sql_data = array ('user_id' => $user_id, 'survey_id' => $survey_id, 'question_id' => $quesId, 'option_id' => $answer, 'score' => $score );
			$sql = Database::sql_insert ( $tbl_survey_answer, $sql_data );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	function update_event_survey($survey_id, $user_id, $score, $data_tracking) {
		global $tbl_survey, $tbl_survey_user, $tbl_survey_answer;
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if ($survey_id && $user_id) {
			$sql_data = array ('data_tracking' => $data_tracking, 'last_attempt_time' => date ( 'Y-m-d H:i:s' ), 'score' => $score );
			$sqlwhere = " survey_id=" . Database::escape ( $survey_id ) . " AND user_id=" . Database::escape ( $user_id );
			$sql = Database::sql_update ( $tbl_survey_user, $sql_data, $sqlwhere );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			self::update_answered ( $survey_id );
		}
	}

	function get_question_count($survey_id, $group_id = null) {
		global $tbl_survey_question;
		$sql = "SELECT COUNT(id) FROM $tbl_survey_question WHERE survey_id=" . Database::escape ( $survey_id );
		if ($group_id) $sql .= " AND group_id=" . Database::escape ( $group_id );
		$nbrQuestions = Database::get_scalar_value ( $sql );
		return $nbrQuestions;
	}

	function get_survey_user_count($survey_id) {
		global $tbl_survey_user;
		$sql = "SELECT COUNT(user_id) FROM $tbl_survey_user WHERE survey_id=" . Database::escape ( $survey_id );
		$nbr = Database::get_scalar_value ( $sql );
		return $nbr;
	}

	function get_paper_total_score($survey_id) {
		global $tbl_survey_question_option;
		$sql = "SELECT SUM(value) from $tbl_survey_question_option WHERE survey_id='" . Database::escape_string ( $survey_id ) . "'";
		$rtn = Database::get_scalar_value ( $sql );
		return $rtn;
	}

}