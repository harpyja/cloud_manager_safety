<?php

class QuestionPool {
	var $tbl_exam_question_pool;
	var $tbl_exam_question;
	var $tbl_exam_answer;
	
	var $id;
	var $pid = 0;
	var $pool_name;
	var $display_order = 1;
	
	var $all_pool_tree;
	var $sub_pool_tree;
	var $sub_pool_ids;
	var $pool_path;

	function __construct() {
		$this->QuestionPool ();
	}

	function QuestionPool() {
		$this->pid = 0;
		$this->display_order = 1;
		$this->tbl_exam_question_pool = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION_POOL );
		$this->tbl_exam_question = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION );
		$this->tbl_exam_answer = Database::get_main_table ( TABLE_MAIN_EXAM_ANSWER );
		$this->all_pool_tree = array ();
		$this->sub_pool_tree = array ();
		$this->sub_pool_ids = array ();
		$this->pool_path = "";
	}

	function add($sql_data = array()) {
		
		$sql = Database::sql_insert ( $this->tbl_exam_question_pool, $sql_data );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function edit($sql_data = array(), $sqlwhere = "") {
		
		$sql = Database::sql_update ( $this->tbl_exam_question_pool, $sql_data, $sqlwhere );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function get_info($id) {
		if ($id) {
			$sql = "SELECT * FROM " . $this->tbl_exam_question_pool . " WHERE id='" . escape ( $id ) . "'";
			$data = Database::fetch_one_row ( $sql, __FILE__, __LINE__ );
			return $data;
		}
		return false;
	}

	function get_next_display_order() {
		$sql = "SELECT MAX(display_order) FROM " . $this->tbl_exam_question_pool;
		$disp_order = Database::get_scalar_value ( $sql ) + 1;
		return $disp_order;
	}

	function get_list() {
		$sql = "SELECT * FROM " . $this->tbl_exam_question_pool . "  ORDER BY display_order ASC";
		$rtn = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
		//var_dump($rtn);
		return $rtn;
	}

	function del_info($id = 0) {
		$sql = "SELECT COUNT(*) FROM " . $this->tbl_exam_question . " WHERE pool_id='" . escape ( $id ) . "'";
		$question_cnt = Database::get_scalar_value ( $sql );
		if ($question_cnt > 0) return 101;
		$sql = "DELETE FROM " . $this->tbl_exam_question_pool . " WHERE id='" . escape ( $id ) . "'";
		$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
		return SUCCESS;
	}

	function get_all_pool_tree() {
		$list2 = $this->get_list ();
		foreach ( $list2 as $pool ) {
			$this->all_pool_tree [] = $pool;
			unset ( $pool );
		}
		return $this->all_pool_tree;
	
	}

	function get_pool_count() {
		$sql = "SELECT COUNT(*) FROM " . $this->tbl_exam_question_pool;
		return Database::get_scalar_value ( $sql );
	}

	function get_pool_question_count($pool_id = 0, $question_type = 0) {
		$sql = "SELECT COUNT(*) FROM " . $this->tbl_exam_question . " WHERE pid=0";
		if ($question_type) $sql .= " AND type=" . Database::escape ( $question_type );
		if ($pool_id) $sql .= " AND pool_id='" . escape ( $pool_id ) . "'";
		//echo $sql,'<br/>';
		return Database::get_scalar_value ( $sql );
	}

}