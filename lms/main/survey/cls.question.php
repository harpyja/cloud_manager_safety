<?php

abstract class Question {
	
	var $survey_id;
	var $id;
	var $type;
	var $question;
	var $group_id; //所属调查项
	var $is_active; //是否可用
	var $position;
	var $category;
	
	var $paperList; // array with the list of exercises which this question is in
	

	static $alpha = array ('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
	static $typePicture = 'new_question.png';
	static $explanationLangVar = '';
	
	static $questionTypes = array (UNIQUE_ANSWER => array ('cls.unique_answer.php', 'UniqueAnswer' ), MULTIPLE_ANSWER => array ('cls.multiple_answer.php', 'MultipleAnswer' ), FREE_ANSWER => array ('cls.free_answer.php', 'FreeAnswer' ) );

	function __construct() {
		$this->Question ();
	}

	function Question() {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$this->id = 0;
		$this->question = '';
		$this->weighting = 0;
		$this->position = 1;
		$this->answer_str = '';
		$this->exerciseList = array ();
		$this->subQuestionList = array ();
	}

	static function getInstance($type) {
		
		list ( $file_name, $class_name ) = self::$questionTypes [$type];
		include_once ($file_name);
		if (class_exists ( $class_name )) {
			return new $class_name ();
		} else {
			api_error_log ( 'Can\'t instanciate class ' . $class_name . ' of type ' . $type, __FILE__, __LINE__, "exam.log" );
			return null;
		}
	
	}

	function get_list($survey_id = 0, $question_type = 0, $keyword = null, $group_id = null) {
		global $tbl_survey_question, $tbl_survey_question_option, $tbl_survey_question_group, $_question_types;
		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		
		/*$sql = "SELECT t1.*,t2.name AS group_name,t3.name AS category_name FROM $tbl_survey_question AS t1 LEFT JOIN $tbl_survey_question_group AS t2 
				ON t1.group_id=t2.id LEFT JOIN $tbl_category AS t3 ON t1.category=t3.id  
				WHERE t1.survey_id='" . escape ( $survey_id ) . "'";*/
		$sql = "SELECT t1.*,t3.name AS category_name FROM $tbl_survey_question AS t1  LEFT JOIN $tbl_category AS t3 ON t1.category=t3.id  
				WHERE t1.survey_id='" . escape ( $survey_id ) . "'";
		$sql = "SELECT t1.* FROM $tbl_survey_question AS t1 WHERE t1.survey_id='" . escape ( $survey_id ) . "'";
		
		if (is_not_blank ( $question_type )) {
			$sql .= " AND type='" . escape ( $question_type ) . "'";
		}
		
		/*	if (is_not_blank ( $group_id )) {
			$sql .= " AND group_id='" . escape ( $group_id ) . "'";
		}*/
		
		if (is_not_blank ( $keyword )) {
			$keyword = Database::escape_str ( $keyword, TRUE );
			$sql .= " AND survey_question LIKE '%" . $keyword . "%'";
		}
		$sql .= " ORDER BY id DESC";
		//		echo $sql;
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$rtn = api_store_result ( $result );
		return $rtn;
	}

	function get_info($id) {
		global $tbl_survey_question;
		$sql = "SELECT * FROM " . $tbl_survey_question . " WHERE id='" . escape ( $id ) . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($object = Database::fetch_object ( $result )) {
			$objQuestion = Question::getInstance ( $object->type );
			$objQuestion->id = $id;
			$objQuestion->question = $object->survey_question;
			$objQuestion->position = $object->sort;
			$objQuestion->type = $object->type;
			$objQuestion->category = $object->category;
			$objQuestion->group_id = $object->group_id;
			
			return $objQuestion;
		}
		
		return false;
	}

	function save() {
		global $tbl_survey_question;
		
		//编辑
		if ($this->id) {
			$sql_data = array ('survey_question' => $this->question, 'sort' => $this->position, 'type' => $this->type, 'group_id' => $this->group_id, 'category' => $this->category );
			$sql = Database::sql_update ( $tbl_survey_question, $sql_data, "id=" . Database::escape ( $this->id ) );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} else { //新增
			$sql = "SELECT max(sort) FROM $tbl_survey_question as question ";
			$current_position = Database::get_scalar_value ( $sql );
			$this->position = empty ( $current_position ) ? 1 : $current_position + 1;
			
			$sql_data = array ('survey_question' => $this->question, 'sort' => $this->position, 'type' => $this->type, 'group_id' => $this->group_id, 'category' => $this->category );
			$sql_data ['survey_id'] = $this->survey_id;
			$sql = Database::sql_insert ( $tbl_survey_question, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$this->id = Database::get_last_insert_id ();
		}
	
	}

	/**
	 * deletes a question from the database
	 * the parameter tells if the question is removed from all exercises (value = 0),
	 * or just from one exercise (value = exercise ID)
	 *
	 * @param - integer $deleteFromEx - exercise ID if the question is only removed from one exercise
	 */
	function delete($deleteFromEx = 0) {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$id = escape ( $this->id );
		//从所有测验中删除
		if (! $deleteFromEx) {
			
			$sql = "DELETE FROM $tbl_survey_question_option WHERE question_id='$id'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_survey_question WHERE id='$id'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$this->Question ();
		} else {
		
		}
	}

	/**
	 * 题干 表单
	 * Creates the form to create / edit a question
	 * A subclass can redifine this function to add fields...
	 * @param FormValidator $form the formvalidator instance (by reference)
	 */
	function createForm(&$form) {
		
		$html = '<table align="center" width="100%" cellpadding="4" cellspacing="0">';
		//$html .= '<tr><th class="formTableTh" colspan="2">' . self::get_question_type_name ( $this->type ) . '</th></tr>';
		//$form->addElement ( 'static', null, null, $html );
		

		$required_html = '<span class="form_required">*</span>';
		$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left"><!-- BEGIN error --><span class="onError">{error}<br/></span><!-- END error -->{element}</td></tr>';
		$renderer = $form->defaultRenderer ();
		
		// 题型
		$answerType = intval ( $_REQUEST ['answerType'] );
		$form->addElement ( 'hidden', 'answerType', $_REQUEST ['answerType'] );
		
		/*global $tbl_survey_question_group;
		$sql = "SELECT id,name FROM $tbl_survey_question_group WHERE survey_id=" . Database::escape ( $this->survey_id );
		$categories = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		$form->addElement ( 'select', 'group_id', get_lang ( "InSurveyItemGroup" ), $categories );
		$renderer->setElementTemplate ( $default_template, 'group_id' );*/
		
		/*		$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
		$sql = "SELECT id,name FROM $tbl_category WHERE module='survey_question'";
		$categories = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		$form->addElement ( 'select', 'category', get_lang ( "InCategories" ), $categories, array ('style' => 'min-width:30%' ) );
		$renderer->setElementTemplate ( $default_template, 'category' );*/
		
		//题目内容
		if ($GLOBALS ['hide_question_name'] == false) {
			$form->addElement ( 'textarea', 'questionName', $required_html . get_lang ( 'Question' ), array ('id' => 'description', 'style' => 'width:100%;height:150px', 'wrap' => 'virtual', 'class' => 'inputText' ) );
			$renderer->setElementTemplate ( $default_template, 'questionName' );
			$form->addRule ( 'questionName', get_lang ( 'GiveQuestion' ), 'required' );
		}
		
		$form->addElement ( 'hidden', 'myid', $_REQUEST ['myid'] );
		
		$defaults ['questionName'] = $this->question;
		$form->setDefaults ( $defaults );
	}

	/**
	 * 问题题干的创建（名称及描述）
	 * function which process the creation of questions
	 * @param FormValidator $form the formvalidator instance
	 * @param Exercise $objExercise the Exercise instance
	 */
	function processCreation($form) {
		
		$data = $form->getSubmitValues ();
		
		//题目
		$this->question = (trim ( $data ['questionName'] ));
		
		$this->survey = (trim ( $data ['survey_id'] ));
		
		//所属调查项
		//$this->group_id = trim ( $data ["group_id"] );
		

		$this->category = trim ( $data ["category"] );
		
		$this->save ();
	
	}

	abstract function createAnswersForm($form);

	abstract function processAnswersCreation($form);

	static function get_question_list($pid) {
		global $tbl_survey_question;
		if (is_not_blank ( $pid )) {
			//position 为子题目的显示顺序
			$sql = "SELECT * FROM " . $tbl_survey_question . " WHERE group_id='" . escape ( $pid ) . "' ORDER BY position";
			$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $row1 = Database::fetch_row ( $result1 ) ) {
				$subQuestionList [$row1 ["position"]] = $row1;
			}
			return $subQuestionList;
		}
		return false;
	}

	static function stat_question_count_by_pool($pool_id = null) {
		global $tbl_survey_question;
		if (is_null ( $pool_id )) {
			$sql = "SELECT group_id,count(*) FROM $tbl_survey_question GROUP BY group_id";
			$rtn = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
			return $rtn;
		} else {
		
		}
	}

	/**
	 * 题型名称
	 * @param $type
	 * @return unknown_type
	 */
	static function get_question_type_name($type = 0) {
		$langVar = NULL;
		foreach ( self::$questionTypes as $key => $val ) {
			if ($key == $type) {
				$langVar = $val [1];
				break;
			}
		}
		return get_lang ( $langVar );
	}

}