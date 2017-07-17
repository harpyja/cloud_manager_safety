<?php

/*
 测验类
 */
define ( 'ALL_ON_ONE_PAGE', 0 );
define ( 'ONE_PER_PAGE', 1 );
define ( 'ONE_TYPE_PER_PAGE', 2 );

class Exercise {
	var $id;
	var $exercise;
	var $description;
	var $sound;
	var $type;
	var $cc;
	var $random;
	var $display_type;
	var $active;
	var $timeLimit;
	var $attempts;
	var $feedbacktype;
	var $end_time;
	var $start_time;
	var $results_disabled;
	var $duration;
	var $questionList; // array with the list of this exercise's questions
	var $display_order;
	var $pass_score;
	var $exam_manager;
	var $created_user;

	function __construct() {
		$this->Exercise ();
	}

	function Exercise() {
		$this->id = 0;
		$this->exercise = '';
		$this->description = '';
		$this->sound = '';
		$this->type = 1;
		$this->random = 0;
		$this->attempts = 0;
		$this->active = 0;
		$this->questionList = array ();
		$this->timeLimit = 0;
		$this->end_time = '0000-00-00 00:00:00';
		$this->start_time = '0000-00-00 00:00:00';
		$this->results_disabled = 1;
		$this->feedbacktype = 0;
		$this->duration = 0;
		$this->display_order = 1;
		$this->display_type = 2;
		$this->pass_score = 60;
		$this->cc = '';
		$this->exam_manager = 1;
	}

	function read($id) {
		global $_course;
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		
		$sql = "SELECT * FROM $TBL_EXERCICES WHERE id=" . Database::escape ( $id );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$this->id = $id;
		if ($object = Database::fetch_object ( $result )) {
			$this->id = $id;
			$this->exercise = $object->title;
			$this->description = $object->description;
			$this->sound = $object->sound;
			$this->type = $object->type;
			$this->random = $object->random;
			$this->active = $object->active;
			$this->attempts = $object->max_attempt;
			$this->results_disabled = $object->results_disabled;
			$this->feedbacktype = $object->feedback_type;
			$this->duration = $object->max_duration;
			$this->start_time = $object->start_time;
			$this->end_time = $object->end_time;
			$this->display_order = $object->display_order;
			$this->pass_score = $object->pass_score;
			$this->display_type = $object->display_type;
			$this->cc = $object->cc;
			$this->exam_manager = $object->exam_manager;
			$this->created_user = $object->created_user;
			//var_dump($object);
			$this->load_question_list ( $id );
			return true;
		}
		
		return false;
	}

	function load_question_list($id) {
		global $TBL_EXERCICE_QUESTION;
		$this->questionList = array ();
		
		//查询quiz_rel_question,确定题目显示顺序
		$sql = "SELECT question_id,question_order as position,question_type FROM " . $TBL_EXERCICE_QUESTION . " WHERE exercice_id=" . Database::escape ( $id );
		$sql .= " ORDER BY question_type,question_order ASC";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $object = Database::fetch_array ( $result ) ) {
			$this->questionList [$object ['question_id']] = array ($object ['question_id'], $object ['position'], $object ['question_type'] );
		}
		return true;
	}

	/**
	 * 更新测验主信息
	 * updates the exercise in the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save() {
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		
		$id = $this->id;
		$exercise = ($this->exercise);
		$description = ($this->description);
		$sound = ($this->sound);
		$type = $this->type;
		$random = $this->random;
		$attempts = $this->attempts;
		$active = $this->active;
		if (empty ( $active )) $active = 0;
		
		// exercise already exists
		if ($id) { //更新
			$sql_data = array ('title' => $exercise, 
					'description' => $description, 
					'sound' => $sound, 
					'type' => $type, 
					'random' => $random, 
					'active' => $active, 
					'max_attempt' => $attempts, 
					'results_disabled' => $this->results_disabled, 
					'feedback_type' => $this->selectFeedbackType (), 
					'max_duration' => $this->duration, 
					"start_time" => $this->selectStartTime (), 
					"end_time" => $this->selectEndTime (), 
					'display_order' => $this->display_order, 
					'display_type' => $this->display_type, 
					'pass_score' => $this->pass_score, 
					'exam_manager' => $this->exam_manager );
			$sql = Database::sql_update ( $TBL_EXERCICES, $sql_data, "id='" . $id . "'" );
			//echo $sql;exit;
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} else { //新增
			if ($type == QUIZ_TYPE_HW) {
				$attempts = 0;
				$this->duration = 0;
				$this->updateStartTime ( '0000-00-00 00:00:00' );
				$this->updateEndTime ( '0000-00-00 00:00:00' );
			}
			$sql_data = array ('title' => $exercise, 
					'description' => $description, 
					'sound' => $sound, 
					'type' => $type, 
					'random' => $random, 
					'active' => $active, 
					'max_attempt' => $attempts, 
					'results_disabled' => $this->results_disabled, 
					'feedback_type' => $this->selectFeedbackType (), 
					'max_duration' => $this->duration, 
					"start_time" => $this->selectStartTime (), 
					"end_time" => $this->selectEndTime (), 
					'display_order' => $this->display_order, 
					'display_type' => $this->display_type, 
					'pass_score' => $this->pass_score, 
					'exam_manager' => $this->exam_manager 
                                );
			$sql_data ['cc'] = $this->cc;
			$sql_data ['created_user'] = api_get_user_id ();
			$sql = Database::sql_insert ( $TBL_EXERCICES, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			//echo $sql;exit();
			$this->id = Database::get_last_insert_id ();
		}
		
		// updates the question position
		foreach ( $this->questionList as $position => $questionId ) {
			$sql = "UPDATE $TBL_EXERCICE_QUESTION SET question_order='$position' WHERE question_id='$questionId'";
			$sql .= " AND cc='" . $this->cc . "' ";
			$sql .= " AND exercice_id=" . Database::escape ( $this->id );
			
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	/**
	 * deletes the exercise from the database
	 * Notice : leaves the question in the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function delete($setUnactive = true) {
		global $tbl_exam_rel_user, $TBL_EXERCICE_QUESTION, $TBL_TRACK_EXERCICES, $TBL_TRACK_ATTEMPT, $TBL_EXERCICES;
		if ($setUnactive) {
			$sql = "UPDATE $TBL_EXERCICES SET active='-1' WHERE id='" . $this->id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			$sql = "DELETE FROM $TBL_EXERCICE_QUESTION WHERE exercice_id='" . $this->id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "SELECT exe_id FROM $TBL_TRACK_EXERCICES WHERE exe_exo_id='" . $this->id . "'";
			$exe_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $TBL_TRACK_ATTEMPT WHERE " . Database::create_in ( $exe_ids, 'exe_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $TBL_TRACK_EXERCICES WHERE exe_exo_id='" . $this->id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_exam_rel_user WHERE exam_id='" . $this->id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $TBL_EXERCICES WHERE id='" . $this->id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	function createForm($form) {
		$course_info = api_get_course_info ();
		if (empty ( $this->id )) { //新增
			//考试性质
			//$group = array ();
			//$group [] = $form->createElement ( 'radio', 'exerciseType', null, get_lang ( 'ExamProperty1' ), 1, array ("id" => "property1" ) ); //一般考试
			//$group [] = $form->createElement ( 'radio', 'exerciseType', null, get_lang ( 'ExamProperty2' ), 2, array ("id" => "property2" ) ); //课程考试
			//$form->addGroup ( $group, null, get_lang ( 'ExamProperty' ), '&nbsp;&nbsp;', false );
			//$modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'SINGLE_COURSE', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/modal_frame.php?", 'form_name' => 'exercise_admin', 'TO_NAME' => 'TO_NAME_CRS', 'TO_ID' => 'TO_ID_CRS' );
			//$form->addElement ( 'modaldialog_select', 'ref_course', get_lang ( 'CourseRef' ), null, $modaldialog_select_options );
			$type = getgpc ( 'type' );
//			if (empty ( $this->cc )) { //非课程考试
//				$form->addElement ( 'hidden', 'exerciseType', $type );
//				if ($type == 1) $form->addElement ( 'static', '', get_lang ( 'ExamProperty' ), get_lang ( 'ExamProperty1' ) );
//				if ($type == 3) $form->addElement ( 'static', '', get_lang ( 'ExamProperty' ), get_lang ( 'ExamProperty3' ) );
//			} else {
//				$form->addElement ( 'hidden', 'exerciseType', 2 );
//				$form->addElement ( 'hidden', 'ref_course', $this->cc );
//				$dispTxt = ' (' . get_lang ( 'Course' ) . ': ' . $course_info ['name'] . '; ' . get_lang ( 'CourseCode' ) . ':' . $this->cc . ')';
//				$form->addElement ( 'static', '', get_lang ( 'ExamProperty' ), get_lang ( 'ExamProperty2' ) . $dispTxt );
//			}
                        
                        if($type==null){
                            $sql="select `id`, `name` from `exam_type`";
                            $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                             $tbl_row = array ();
                            // $tbl_row['请选择竞赛名称']='请选择竞赛名称';
                            while ($row = Database::fetch_array ( $result, 'ASSOC' )){
                               
                                $tbl_row[$row['id']]=$row['name'];
                                
                            }
                            //array_unshift($tbl_row, '请选择竞赛名称');
                            $arr=  array_keys($tbl_row);
                           $form->addElement ( 'hidden', 'exerciseType', $arr[0], array ('id' => "exe" )); 
                           $form->addElement ( 'select', 'static', get_lang ( 'ExamProperty' ), $tbl_row, array ('style' => "width:15%" ) );  
                            
                        }  else {
                            
                                 $ty=Database::getval ("select name from exam_type where id=".$type, __FILE__, __LINE__ );
                                $form->addElement ( 'hidden', 'exerciseType', $type );
				$form->addElement ( 'static', '', get_lang ( 'ExamProperty' ), $ty );
                            
                        }
                        
                        
                        
                        
		} else {
                        $ty=Database::getval ("select name from exam_type where id=".$this->selectType (), __FILE__, __LINE__ );
                        $form->addElement ( 'static', '', get_lang ( 'ExamProperty' ), $ty );
			$form->addElement ( 'hidden', 'exerciseType', $this->selectType () );
			if ($this->cc) $form->addElement ( 'text', 'ref_course', get_lang ( 'CourseRef' ), 'class="inputText" style="width:66%" readonly' );
		}
		
		//考试名称
		$form->addElement ( 'text', 'exerciseTitle', get_lang ( 'ExerciseName' ), 'class="inputText" style="width:60%" onfocus="javascript:this.select();"' );
		
		//通过分数
		$form->addElement ( 'text', 'pass_score', get_lang ( 'PassScore' ), array ('maxlength' => 3, 'style' => "width:100px;text-align:left", 'class' => 'inputText', 'onfocus' => 'javascript:this.select();' ) );
		$form->addRule ( 'pass_score', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
		
		if (empty ( $this->id )) { //新增
			$form->addElement ( "hidden", "active", "0" );
		} else { //编辑
			if (empty ( $this->active )) {
				$form->addElement ( "checkbox", "active", get_lang ( 'isPublishedNow' ), get_lang ( "Yes" ) . "&nbsp;&nbsp;&nbsp;&nbsp;" . get_lang ( "QuizPublishTip" ) );
			} else {
				$form->addElement ( "hidden", "active", "1" );
			}
		}
		
		/*$radios = array ();
			$radios [] = FormValidator::createElement ( 'radio', 'exerciseType', null, get_lang ( 'SimulationExercise' ), QUIZ_TYPE_SM, array ("id" => "exerciseType1" ) );
			$radios [] = FormValidator::createElement ( 'radio', 'exerciseType', null, get_lang ( 'HomeworkPractice' ), QUIZ_TYPE_HW, array ("id" => "exerciseType2" ) );
			$form->addGroup ( $radios, null, get_lang ( 'ExerciseType' ), '&nbsp;', false );
			$form->addElement ( "hidden", "exerciseType", QUIZ_TYPE_SM );*/
		
		//可进入测验的时间
		//$form->add_calendar_duration ( null, "start_time", 'end_time', get_lang ( 'ExerciseDuration' ), TRUE );
		$form->addElement ( "hidden", "start_time", date ( 'Y-m-d H:i' ) );
		$form->addElement ( "hidden", "end_time", date ( 'Y-m-d H:i', strtotime ( "+ 10 year" ) ) );
		
		//考试时间
		$test_duration_options = array ("0" => get_lang ( 'Infinite' ), 
				"900" => "15 " . get_lang ( 'Minites' ), 
				"1800" => "30 " . get_lang ( 'Minites' ), 
				"2700" => "45 " . get_lang ( 'Minites' ), 
				"3600" => "60 " . get_lang ( 'Minites' ), 
				"5400" => "90 " . get_lang ( 'Minites' ), 
				"7200" => "120 " . get_lang ( 'Minites' ), 
				"9000" => "150 " . get_lang ( 'Minites' ), 
				"10800" => "180 " . get_lang ( 'Minites' ) );
		$form->addElement ( 'select', 'exerciseDuration', get_lang ( 'QuizAllowedDuration' ), $test_duration_options, array ('style' => "width:15%" ) );
		
		//尝试次数
//		$attempt_option = range ( 0, 10 );
//		$attempt_option [0] = get_lang ( 'Infinite' );
//		$form->addElement ( 'select', 'exerciseAttempts', get_lang ( 'ExerciseAttempts' ), 1, array ('style' => "width:15%" ) );
//		$form->addRule ( 'exerciseAttempts', get_lang ( 'Numeric' ), 'numeric' );
                $form->addElement ( "hidden", "exerciseAttempts", 1 );
		
		//显示考试成绩
		$radios_results_disabled = array ();
		$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'Yes' ), '0' );
		$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'No' ), '1' );
		$form->addGroup ( $radios_results_disabled, null, get_lang ( 'ShowResultsToStudents' ), "&nbsp;&nbsp;", FALSE );
		
		//显示答案
		$radios_feedback = array ();
		$radios_feedback [] = FormValidator::createElement ( 'radio', 'exerciseFeedbackType', null, get_lang ( 'ExerciseAtTheEndOfTheTest' ), '0' );
		$radios_feedback [] = FormValidator::createElement ( 'radio', 'exerciseFeedbackType', null, get_lang ( 'NoFeedback' ), '2' );
		$form->addGroup ( $radios_feedback, null, get_lang ( 'FeedbackType' ), "&nbsp;&nbsp;", FALSE );
		
		//随机显示题目顺序
		$radio_random = array ();
		$radio_random [] = FormValidator::createElement ( 'radio', 'random', null, get_lang ( 'RandomQuestions' ), 1 );
		$radio_random [] = FormValidator::createElement ( 'radio', 'random', null, get_lang ( 'FixedQuestions' ), 0 );
		$form->addGroup ( $radio_random, null, get_lang ( 'ExamQuestionDisplayOrder' ), "&nbsp;&nbsp;", FALSE );
		
		//显示方式
		$group = array ();
		//$group [] = & HTML_QuickForm::createElement ( 'radio', 'display_type', null, get_lang ( 'ExamDisplayType0' ), ALL_ON_ONE_PAGE );
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'display_type', null, get_lang ( 'ExamDisplayType2' ), ONE_TYPE_PER_PAGE );
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'display_type', null, get_lang ( 'ExamDisplayType1' ), ONE_PER_PAGE );
		$form->addGroup ( $group, null, get_lang ( 'ExamDisplayType' ), '&nbsp;&nbsp;&nbsp;&nbsp;', false );
		
		$sql="select user_id, username from user";
                            $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                             $tbl_row = array ();
                            while ($row = Database::fetch_array ( $result, 'ASSOC' )){
                               
                                $exam_manager[$row['user_id']]=$row['username'];
                                
                            }
                $form->addElement ( 'select', 'exam_manager', get_lang ( 'ExamManager' ), $exam_manager, array ('style' => "width:15%" ) );  
                            

//		$modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'EXAM_MANAGER', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/pop_frame.php?", 'form_name' => 'exercise_admin', 'TO_NAME' => 'TO_NAME_ADMIN', 'TO_ID' => 'TO_ID_ADMIN' );
//		$form->addElement ( 'modaldialog_select', 'exam_manager', get_lang ( 'ExamManager' ), NULL, $modaldialog_select_options );
//		$form->addRule ( 'exam_manager', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//		
		//显示顺序
		/*		$form->addElement ( "text", 'display_order', get_lang ( 'DisplayOrder' ), array ('style' => "width:80px", 'class' => 'inputText', 'maxlength' => 5 ) );
		$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
		$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredPositiveInteger' ), 'regex', '/^[1-9]\d*$/' );*/
		
		//说明指南
		/*		global $fck_attribute;
		$fck_attribute = array ();
		$fck_attribute ['Height'] = '250';
		$fck_attribute ['Width'] = '100%';
		$fck_attribute ['ToolbarSet'] = 'NewTest';
		$form->addElement ( 'html_editor', 'exerciseDescription', get_lang ( 'Description' ) );*/
		$form->addElement ( 'textarea', 'exerciseDescription', get_lang ( 'Description' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:200px' ) );
		
		// submit
		$group = array ();
		$group [] = $form->createElement ( 'submit', 'submitExercise', get_lang ( 'Next' ), 'class="inputSubmit"' );
		$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
		$form->addGroup ( $group, 'submitExercise', '&nbsp;', null, false );
		
		// rules
		$form->addRule ( 'exerciseTitle', get_lang ( 'GiveExerciseName' ), 'required' );
		
		$defaults = array ();
		if ($this->id) { //编辑
			$defaults ['exerciseType'] = $this->selectType ();
			$defaults ['exerciseTitle'] = $this->selectTitle ();
			$defaults ['exerciseDescription'] = $this->selectDescription ();
			$defaults ['exerciseAttempts'] = $this->selectAttempts ();
                        //$defaults ['exerciseAttempts'] = 1;
			$defaults ['active'] = $this->selectStatus ();
			$defaults ['exerciseFeedbackType'] = $this->selectFeedbackType ();
			$defaults ['results_disabled'] = $this->selectResultsDisabled ();
			$defaults ['exerciseDuration'] = $this->selectDuration ();
			$defaults ['start_time'] = $this->selectStartTime ();
			$defaults ['end_time'] = $this->selectEndTime ();
			$defaults ['display_order'] = $this->display_order;
			$defaults ["pass_score"] = $this->pass_score;
			$defaults ["display_type"] = $this->display_type;
			$defaults ["random"] = $this->random;
			if ($this->cc) {
				$sql = "SELECT title FROm " . Database::get_main_table ( TABLE_MAIN_COURSE ) . " WHERE code=" . Database::escape ( $this->cc );
				$course_title = Database::getval ( $sql, __FILE__, __LINE__ );
				$defaults ['ref_course'] = $course_title;
			}
			require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
			$exam_manager = UserManager::get_user_info_by_id ( $this->exam_manager );
			$defaults ['exam_manager'] ['TO_NAME_ADMIN'] = $exam_manager ['firstname'];
			$defaults ['exam_manager'] ['TO_ID_ADMIN'] = $this->exam_manager;
                        $defaults ['exam_manager']  =$this->exam_manager; 
		} else {
			$defaults ['exerciseTitle'] = "Exam-" . date ( 'YmdHs' );
			if ($this->cc && $this->type == 2) $defaults ['exerciseTitle'] = '' . $course_info ['name'] . '--' . get_lang ( 'ExamProperty2' );
			$defaults ['exerciseDescription'] = '<style>.exercise_guide li {padding-top:6px}</style><table width="99%" border="0" cellpadding="3" cellspacing="0"><tr><td width="100%" valign="top" align="left">' . get_lang ( "ExerciseGuide" ) . '</td></tr></table>';
			$defaults ['exerciseAttempts'] = 1;
			$defaults ['active'] = 0;
			$defaults ['results_disabled'] = 0; //显示成绩?
			$defaults ['exerciseFeedbackType'] = 2; //不显示答案
			//$defaults ['start_time'] = date ( 'Y-m-d H:i', time () + (24 * 3600) );
			//$defaults ['end_time'] = date ( 'Y-m-d H:i', time () + (10 * 24 * 3600) );
			$defaults ["display_order"] = $this->selectNextDisplayOrder ();
			$defaults ["pass_score"] = 60;
			$defaults ["display_type"] = ONE_TYPE_PER_PAGE;
			$defaults ['random'] = 0;
			$defaults ['exam_manager'] ['TO_NAME_ADMIN'] = api_get_user_firstname ();
			$defaults ['exam_manager'] ['TO_ID_ADMIN'] = api_get_user_id ();
                        $defaults ['exam_manager']  = Database::getval('select user_id from user where username="root"',__FILE__,__LINE__);
		}
//		echo '<pre>';var_dump( $exam_manager);echo '</pre>';
//                echo '<pre>';var_dump( $defaults);echo '</pre>';
		$form->setDefaults ( $defaults );
		
		Display::setTemplateBorder ( $form, '98%' );
	
	}

	function processCreation($form) {
		//var_dump($form->getSubmitValue ( 'exam_manager' ));exit;
		$this->updateTitle ( $form->getSubmitValue ( 'exerciseTitle' ) );
		$this->updateDescription ( $form->getSubmitValue ( 'exerciseDescription' ) );
		$this->updateType ( $form->getSubmitValue ( 'exerciseType' ) );
		$this->updateAttempts ( $form->getSubmitValue ( 'exerciseAttempts' ) );
		$this->updateDuration ( $form->getSubmitValue ( 'exerciseDuration' ) );
		$this->updateFeedbackType ( $form->getSubmitValue ( 'exerciseFeedbackType' ) );
		//$this->updateResultsDisabled ( $form->getSubmitValue ( 'results_disabled' ) );
		$this->results_disabled = $form->getSubmitValue ( 'results_disabled' );
		$this->active = $form->getSubmitValue ( 'active' );
		$this->updateStartTime ( $form->getSubmitValue ( 'start_time' ) );
		$this->updateEndTime ( $form->getSubmitValue ( 'end_time' ) );
		$this->display_order = $form->getSubmitValue ( 'display_order' );
		$this->pass_score = $form->getSubmitValue ( 'pass_score' );
		$this->display_type = $form->getSubmitValue ( 'display_type' );
		$this->random = $form->getSubmitValue ( 'random' );
		$ref_course = $form->getSubmitValue ( 'ref_course' );
		$this->cc = trim ( $ref_course );
		$this->exam_manager = $form->getSubmitValue ( 'exam_manager' );
                //var_dump($ref_exam_manager);exit();
//		$this->exam_manager = trim ( $ref_exam_manager ['TO_ID_ADMIN'] );
		//$this->cc = $ref_course ['TO_ID_CRS'];
		$this->save ();
	}

         /*
         * 获得平均分
         * dengxin 
         */
        function get_average($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT left(AVG(exe_result),3) FROM  exam_track AS t3 WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND status = 'completed'";
		return Database::getval ( $query, __FILE__, __LINE__ );
            
        }
        function get_highest($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT MAX(exe_result) FROM  exam_track AS t3 WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND status = 'completed'";
		return Database::getval ( $query, __FILE__, __LINE__ );
            
        }
         function get_lowest($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT MIN(exe_result) FROM  exam_track AS t3 WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND status = 'completed'";
		return Database::getval ( $query, __FILE__, __LINE__ );
            
        }
         function get_passing($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT COUNT(exe_result) FROM  exam_track AS t3 WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND status = 'completed' AND exe_result >=(SELECT pass_score FROM exam_main WHERE id =". Database::escape ( $exerciseId ).")";
		return Database::getval ( $query, __FILE__, __LINE__ );
            
        }
         function get_begin($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT user.firstname  FROM  user LEFT JOIN  exam_track AS t3 ON user.user_id = t3.exe_user_id WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND t3.status = 'completed' ORDER BY t3.exe_result DESC limit 0,10";
		$rs = api_sql_query ( $query, __FILE__, __LINE__ );
                $datalist = api_store_result_array ( $rs );

		//return $query;
               return $datalist;
            
        }
        function get_end($exerciseId){
          //  $tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT user.firstname  FROM  user LEFT JOIN  exam_track AS t3 ON user.user_id = t3.exe_user_id WHERE t3.exe_exo_id=" . Database::escape ( $exerciseId )."AND t3.status = 'completed' ORDER BY t3.exe_result ASC limit 0,10";
		//return Database::fetch_row ( $query, __FILE__, __LINE__ );
            
                $rs = api_sql_query ( $query, __FILE__, __LINE__ );
                $datalist = api_store_result_array ( $rs );

		//return $query;
                return $datalist;
            
        }
        

	/**
	 * 获取用户参加本次考试的总次数
	 * @param unknown_type $user_id
	 * @param unknown_type $course_code
	 */
	function get_user_attempts($user_id, $course_code) {
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		if (empty ( $user_id ) or empty ( $course_code )) return 0;
		$stat_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$sql = "SELECT count(*) FROM $stat_table WHERE exe_exo_id = '" . $this->selectId () . "'
				AND exe_user_id = '$user_id'
				AND exe_cours_id = '$course_code'";
		$sql .= " AND status != 'incomplete'";
		//echo $sql."<br/>";
		$attempt = Database::getval ( $sql, __FILE__, __LINE__ );
		return $attempt;
	}

	/**
	 * 删除用户本考试的提交及相关记录
	 * @param $exe_id
	 */
	function del_exercise_tracking($exe_id) {
		if (empty ( $exe_id )) return false;
		$stat_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$exercice_attemp_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
		$sql = "DELETE FROM " . $exercice_attemp_table . " WHERE exe_id=" . Database::escape ( $exe_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		$sql = "DELETE FROM " . $stat_table . " WHERE exe_id=" . Database::escape ( $exe_id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function selectNextDisplayOrder() {
		global $TBL_EXERCICES;
		$sql = "SELECT MAX(display_order) FROM " . $TBL_EXERCICES . " WHERE cc='" . api_get_course_code () . "'";
		$display_order = Database::getval ( $sql, __FILE__, __LINE__ );
		return $display_order + 1;
	}

	function getQuestionList($questionType = 0) {
		$TBL_QUESTIONS = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION ); //exam_question
		$TBL_EXERCICE_QUESTION = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION ); //crs_quiz_rel_question
		$sql = "SELECT t2.*,t1.question_order,t1.question_score FROM $TBL_EXERCICE_QUESTION as t1 ,$TBL_QUESTIONS as t2
	     WHERE t1.question_id=t2.`id` AND t1.`exercice_id`='" . $this->selectId () . "'";
		if ($questionType) $sql .= " AND t2.type='" . escape ( $questionType ) . "'";
		$sql .= " ORDER BY t1.question_order";
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		//$res=api_sql_query_array_assoc($sql,__FILE__,__LINE__);
		$tab = array ();
		while ( $row = Database::fetch_array ( $rs, "ASSOC" ) ) {
			$tab [$row ["id"]] = $row;
		}
		return $tab;
	}

	function getAllQuestionsByType() {
		$rtn = cache ( 'exam_paper_' . $this->selectId (), '' );
		if (empty ( $rtn ) or is_null ( $rtn )) {
			$quiz_question_type = $this->getQuizQuestionTypes ( $this->selectId () );
			$quiz_qt = array_keys ( $quiz_question_type );
			foreach ( $quiz_qt as $qtype ) {
				$rtn [$qtype] = $this->getQuestionList ( $qtype );
			}
			cache ( 'exam_paper_' . $this->selectId (), $rtn );
		}
		return $rtn;
	}

	function getQuizQuestionTypes($quiz_id) {
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		$sql = "SELECT DISTINCT(type) as qt,COUNT(*) AS cnt,SUM(t1.question_score) AS total_score FROM $TBL_EXERCICE_QUESTION as t1 ,$TBL_QUESTIONS as t2
	     WHERE t1.question_id=t2.`id` AND t1.`exercice_id`='" . escape ( $quiz_id ) . "' GROUP BY 1 ORDER BY qt";
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$tab = array ();
		while ( $row = Database::fetch_array ( $rs, 'ASSOC' ) ) {
			$tab [$row ["qt"]] = array ($row ["cnt"], $row ["total_score"] );
		}
		return $tab;
	}

	public static function get_quiz_total_score($exerciseId) {
		$tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
		$query = "SELECT SUM(question_score) FROM  $tbl_exam_rel_question AS t3 WHERE t3.exercice_id=" . Database::escape ( $exerciseId );
		return Database::getval ( $query, __FILE__, __LINE__ );
	}

	/**
	 * 用户可参加的考试列表
	 * @param unknown_type $sqlwhere
	 * @param unknown_type $page_size
	 * @param unknown_type $offset
	 */
	function get_user_exam_pagelist($user_id, $sqlwhere = "", $page_size = NULL, $offset = 0) {
		$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
		$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
		$sql = "SELECT COUNT(*) FROM $tbl_exam_rel_user AS t1," . $tbl_exam_main . " AS t2 WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.exam_id=t2.id";
		//$sql = "SELECT COUNT(*) FROM $tbl_exam_rel_user AS t1 WHERE t1.user_id=" . Database::escape ( $user_id );
		if ($sqlwhere) $sql .= " AND " . $sqlwhere;
		$total_rows = Database::getval ( $sql, __FILE__, __LINE__ );
		
		$sql = "SELECT t1.*,t2.* FROM $tbl_exam_rel_user AS t1," . $tbl_exam_main . " AS t2 WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.exam_id=t2.id";
		if ($sqlwhere) $sql .= " AND " . $sqlwhere;
		$sql .= " ORDER BY t2.id DESC";
		if (empty ( $offset )) $offset = 0;
		if (isset ( $page_size )) $sql .= " LIMIT " . $offset . "," . $page_size;
		//echo $sql;
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$row = api_store_result ( $rs );
		return array ("data_list" => $row, "total_rows" => $total_rows );
	}

	static function add_user_to_exam($exam_id, $user_id, $start_date = null, $end_date = null) {
		$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
		if (empty ( $start_date ) or empty ( $end_date )) {
			$TBL_EXERCICES = Database::get_main_table ( TABLE_QUIZ_TEST );
			$sql = "SELECT * FROM " . $TBL_EXERCICES . " WHERE id=" . Database::escape ( $exam_id );
			$exam_info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
			$start_date = $exam_info ["start_time"];
			$end_date = $exam_info ["end_time"];
		}
		
		$sqlwhere = " exam_id=" . Database::escape ( $exam_id ) . " AND user_id=" . Database::escape ( $user_id );
		$sql = "SELECT * FROM $tbl_exam_rel_user WHERE " . $sqlwhere;
		if (! Database::if_row_exists ( $sql ) && $exam_id && $user_id) {
			$sql_data = array ("exam_id" => $exam_id, "user_id" => $user_id, 'available_start_date' => $start_date, 'available_end_date' => $end_date );
			$sql = Database::sql_insert ( $tbl_exam_rel_user, $sql_data );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			$sql_data = array ('available_start_date' => $start_date, 'available_end_date' => $end_date );
			$sql = Database::sql_update ( $tbl_exam_rel_user, $sql_data, $sqlwhere );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	/**
	 * 从考试中删除用户(删除关联)
	 * @param unknown_type $exam_id
	 * @param unknown_type $user_id
	 */
	static function del_user_from_exam($exam_id, $user_id) {
		$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
		$tbl_exam_result = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		
		//不允许删除有成绩的学生
		$sql = "SELECT * FROM $tbl_exam_result WHERE exe_exo_id=" . Database::escape ( $exam_id ) . " AND exe_user_id=" . Database::escape ( $user_id );
		if (! Database::if_row_exists ( $sql )) {
			$sql = "DELETE FROM $tbl_exam_rel_user WHERE exam_id=" . Database::escape ( $exam_id ) . " AND user_id=" . Database::escape ( $user_id );
			//echo $sql;exit;
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	/**
	 * 检查用户是否有权限进入某个考试
	 * @param $exam_id
	 * @param $user_id
	 */
	static function do_exam_available($exam_id, $user_id) {
		global $_user;
		$allwedAccess = FALSE;
		$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST );
		$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
		$table_attempt = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
		
		$sql = "SELECT t1.* FROM " . $tbl_exam_main . " AS t1 WHERE id='" . escape ( $exam_id ) . "'";
		$exam_info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
		
		if (empty ( $exam_info )) { //试卷不存在
			return 101;
		} else { //试卷存在
			if ($exam_info ["active"] != 1) { //试卷存在,但是未发布草稿状态
				return 102;
			}
			
			//试卷用户表exam_rel_user中有记录才允许参加考试
			$sql = "SELECT id,available_start_date,available_end_date FROM $tbl_exam_rel_user WHERE exam_id=" . Database::escape ( $exam_id ) . " AND user_id=" . Database::escape ( $user_id ) . " ORDER BY available_start_date DESC";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) { //有记录,表明是考试用户
				// 可参加考试时间段限制
				$examTimeAllowed = false;
				/* if ((empty ( $exam_info ["start_time"] ) && empty ( $exam_info ["end_time"] )) or ($exam_info ["start_time"] == '0000-00-00 00:00:00' && $exam_info ["end_time"] == '0000-00-00 00:00:00')) { //没有时间限制
					$examTimeAllowed = TRUE;
				} else { //有时间限制 */
				// 用户可参加考试时间有特殊安排时
				$sql = "SELECT id,available_start_date,available_end_date FROM $tbl_exam_rel_user WHERE NOW() BETWEEN available_start_date AND available_end_date AND exam_id=" . Database::escape ( $exam_id ) . " AND user_id=" . Database::escape ( $user_id );
				$examTimeAllowed = Database::if_row_exists ( $sql );
				//}
				if ($examTimeAllowed === FALSE) {
					return 104;
				}
				
				//是否到最大允许次数
				if ($exam_info ["max_attempt"] > 0) {
					$attempt = self::get_exam_user_attempts ( $exam_id, $user_id );
					if ($attempt >= $exam_info ["max_attempt"]) { //超过最大次数限制时
						return 105;
					}
				}
				
				//已通过的考试不允许再进入考试了
				$sqlwhere = " exam_id=" . Database::escape ( $exam_id ) . " AND user_id='" . $user_id . "'";
				$sql = "SELECT score FROM $tbl_exam_rel_user WHERE " . $sqlwhere;
				$max_score = Database::getval ( $sql, __FILE__, __LINE__ );
				if ($max_score >= $exam_info ['pass_score']) return 106;
				return SUCCESS;
			} else {
				return 103;
			}
		}
		return FAILURE;
	}

	static function is_user_pass_exam($exam_id, $user_id) {
		$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
		$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
		if ($exam_id && $user_id) {
			$sql = "SELECT * FROM $tbl_exam_rel_user AS t1 WHERE t1.exam_id=" . Database::escape ( $exam_id ) . " AND t1.user_id=" . Database::escape ( $user_id ) . " AND t1.score>=(SELECT pass_score FROM " . $tbl_exam_main . " WHERE id=" . Database::escape ( $exam_id ) . ")";
			//echo $sql;
			return Database::if_row_exists ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	static function get_exam_user_attempts($exam_id, $user_id) {
		$table_track_exercice = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$sql = "SELECT count(*) FROM $table_track_exercice WHERE exe_exo_id = '" . escape ( $exam_id ) . "'
				AND exe_user_id = '" . escape ( $user_id ) . "' AND status != 'incomplete'";
		//echo $sql."<br/>";
		$attempt = Database::getval ( $sql, __FILE__, __LINE__ );
		return $attempt;
	}

	static function get_exam_time($user_id, $exam_id, $exam_info = null) {
		global $tbl_exam_rel_user;
		$sql = "SELECT available_start_date,available_end_date FROM $tbl_exam_rel_user WHERE exam_id=" . Database::escape ( $exam_id ) . " AND user_id=" . Database::escape ( $user_id ) . " ORDER BY available_start_date DESC LIMIT 1";
		list ( $start_date, $end_date ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
		if ((empty ( $start_date ) && empty ( $end_date )) or ($start_date == '0000-00-00 00:00:00' && $end_date == '0000-00-00 00:00:00')) {
			if ($exam_info && is_object ( $exam_info )) {
				$start_date = $exam_info->selectStartTime ();
				$end_date = $exam_info->selectEndTime ();
			}
		}
		return array ('start_date' => $start_date, 'end_date' => $end_date );
	}

	static function create_event_exercice($exo_id, $status) {
		$TABLETRACK_EXERCICES = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$user_id = api_get_user_id ();
		$course_code = api_get_course_code ();
		
		$sqlwhere = " exe_user_id='" . $user_id . "' AND exe_exo_id= '" . escape ( $exo_id ) . "' AND status='" . escape ( $status ) . "'";
		if ($course_code)
			$sqlwhere .= " AND exe_cours_id='" . escape ( $course_code ) . "'";
		else $sqlwhere .= " AND (exe_cours_id='' OR exe_cours_id IS NULL)";
		$sql = "SELECT exe_id FROM $TABLETRACK_EXERCICES WHERE " . $sqlwhere . " ORDER BY start_date DESC";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $res ) > 0) {
			$row = Database::fetch_array ( $res );
			$id = $row ['exe_id'];
			$sql_data = array ('status' => $status );
			if (! empty ( $_SESSION ['quizStartTime'] )) $sql_data ['start_date'] = date ( 'Y-m-d H:i:s', $_SESSION ['quizStartTime'] );
			$sql = Database::sql_update ( $TABLETRACK_EXERCICES, $sql_data, " exe_id='" . $id . "'" );
			$res = @api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			$sql_data = array ('exe_user_id' => $user_id, 'exe_cours_id' => $course_code, 'exe_exo_id' => $exo_id, 'status' => $status );
			if (! empty ( $_SESSION ['quizStartTime'] )) $sql_data ['start_date'] = date ( 'Y-m-d H:i:s', $_SESSION ['quizStartTime'] );
			$sql = Database::sql_insert ( $TABLETRACK_EXERCICES, $sql_data );
			$res = @api_sql_query ( $sql, __FILE__, __LINE__ );
			$id = Database::get_last_insert_id ();
		}
		return $id;
	}

	static function get_quiz_track_id($exo_id) {
		$TABLETRACK_EXERCICES = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$user_id = api_get_user_id ();
		$course_code = api_get_course_code ();
		$sqlwhere = " exe_user_id='" . $user_id . "' AND exe_exo_id= '" . escape ( $exo_id ) . "' AND status='incomplete'";
		if ($course_code)
			$sqlwhere .= " AND exe_cours_id='" . escape ( $course_code ) . "'";
		else $sqlwhere .= " AND (exe_cours_id='' OR exe_cours_id IS NULL)";
		$sql = "SELECT exe_id FROM $TABLETRACK_EXERCICES WHERE " . $sqlwhere . " ORDER BY start_date DESC";
		return Database::getval ( $sql, __FILE__, __LINE__ );
	}

	static function update_score($track_id, $quiz_id, $user_id, $weighting, $update_time = FALSE) {
		if ($track_id) {
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
			$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER );
			$tbl_exam_track = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
			
			$sql = "SELECT * FROM $tbl_exam_main WHERE id=" . Database::escape ( $quiz_id );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$exam_info = Database::fetch_array ( $res );
			$is_course_exam = FALSE;
			if ($exam_info && $exam_info ['type'] == 2 && $exam_info ['cc']) $is_course_exam = TRUE;
			
			if (empty ( $weighting )) $weighting = self::get_quiz_total_score ( $quiz_id );
			
			// 更新exam_rel_user表中的数据:主要为最终成绩
			$sqlwhere = " exe_user_id='" . $user_id . "' AND exe_exo_id=" . Database::escape ( $quiz_id ) . " AND status='completed'";
			
			//参加考试次数
			$sql = "SELECT COUNT(exe_result) FROM $tbl_exam_track WHERE " . $sqlwhere;
			$attempt_times = Database::getval ( $sql, __FILE__, __LINE__ );
			
			//最高分
			$sql = "SELECT MAX(exe_result) FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1";
			$best_attempt_score = Database::getval ( $sql, __FILE__, __LINE__ );
			$sql = "SELECT exe_id,start_date,exe_duration FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1 AND exe_result='" . $best_attempt_score . "' ORDER BY start_date DESC LIMIT 1";
			list ( $max_score_result_id, $max_score_attempt_date, $max_score_spent ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			
			//最低分
			$sql = "SELECT MIN(exe_result) FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1";
			$min_attempt_score = Database::getval ( $sql, __FILE__, __LINE__ );
			$sql = "SELECT exe_id,start_date,exe_duration FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1 AND exe_result='" . $min_attempt_score . "' ORDER BY start_date DESC LIMIT 1";
			list ( $min_score_result_id, $min_score_attempt_date, $min_score_spent ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			
			//首次考试
			$sql = "SELECT exe_id,start_date,exe_duration,exe_result FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1 ORDER BY start_date ASC LIMIT 1";
			list ( $first_result_id, $first_attempt_date, $first_attempt_spent, $first_attempt_score ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			
			//最后一次参加考试
			$sql = "SELECT exe_id,start_date,exe_duration,exe_result FROM $tbl_exam_track WHERE " . $sqlwhere . "  AND fb_status=1 ORDER BY start_date DESC LIMIT 1";
			list ( $last_result_id, $last_attempt_date, $last_attempt_spent, $last_attempt_score ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			
			//$exam_score = round ( $best_attempt_score / $weighting, 2 ) * 100; //百分比成绩
			$exam_score = $weighting > 0 ? (round ( round ( $score ) / $weighting * 100 )) : 0; 
			$is_pass = ($exam_score >= $exam_info ['pass_score']);
			
			$sql_data = array ('attempt_times' => $attempt_times, 
					'score' => $exam_score, 
					'is_pass' => ($is_pass ? 1 : 0), 
					'track_id' => $max_score_result_id, 
					'best_attempt_score' => $best_attempt_score, 
					'first_attempt_score' => $first_attempt_score, 
					'last_attempt_score' => $last_attempt_score, 
					'paper_score' => $weighting, 
					'min_score' => $min_attempt_score );
			if ($update_time) {
				$sql_data ['best_attempt_date'] = $max_score_attempt_date;
				$sql_data ['best_attempt_spent'] = $max_score_spent;
				$sql_data ['first_attempt_date'] = $first_attempt_date;
				$sql_data ['first_attempt_spent'] = $first_attempt_spent;
				$sql_data ['last_attempt_date'] = $last_attempt_date;
				$sql_data ['last_attempt_spent'] = $last_attempt_spent;
			}
			$sql = Database::sql_update ( $tbl_exam_rel_user, $sql_data, " user_id='" . $user_id . "' AND exam_id=" . Database::escape ( $quiz_id ) );
			   $res = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			if ($is_course_exam) {
				$sql = "SELECT credit FROM " . $tbl_course . " WHERE code=" . Database::escape ( trim ( $exam_info ['cc'] ) );
				$coruse_credit = Database::getval ( $sql, __FILE__, __LINE__ );
				$sql_data = array ('exam_score' => $exam_score, 'exam_status' => ($is_pass ? LESSON_STATUS_PASSED : LESSON_STATUS_FAILED), 'completed_date' => date ( 'Y-m-d' ), 'is_pass' => ($is_pass ? LEARNING_STATE_PASSED : LEARNING_STATE_FAILED), 'got_credit' => ($is_pass ? $coruse_credit : 0) );
				$sql = Database::sql_update ( $tbl_course_user, $sql_data, " user_id=" . Database::escape ( $user_id ) . " AND course_code=" . Database::escape ( trim ( $exam_info ['cc'] ) ) );
				$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			
			return $res;
		}
		return FALSE;
	}

	/**
	 * 保存考试结果
	 * @param unknown_type $track_id track_id
	 * @param unknown_type $exo_id 测验id
	 * @param unknown_type $score 考试得分(原始分)
	 * @param unknown_type $weighting 试卷分数
	 * @param unknown_type $duration 考试时间
	 */
	static function update_event_exercice($track_id, $quiz_id, $user_id, $score, $weighting, $data_tracking) {
		if (! empty ( $track_id )) {
			$tbl_exam_track = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
			$score100 = round ( $score * 100 / $weighting, 1 );
			
			$sql_data = array ('exe_exo_id' => $quiz_id, 'exe_result' => $score, 'exe_weighting' => $weighting, 'score' => $score100, 'exe_duration' => 0, 'exe_date' => date ( 'Y-m-d H:i:s' ), 'status' => 'completed', 'fb_status' => 1, 'data_tracking' => $data_tracking );
			if (! empty ( $_SESSION ['quizStartTime'] )) $sql_data ['start_date'] = date ( 'Y-m-d H:i:s', $_SESSION ['quizStartTime'] );
			$sql = Database::sql_update ( $tbl_exam_track, $sql_data, "exe_id=" . Database::escape ( $track_id ) );
			$res = @api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$is_exam_need_judge_by_hand = self::is_exam_need_judge_by_hand ( $quiz_id );
			
			$sql = "UPDATE  $tbl_exam_track SET exe_duration=UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date),fb_status='" . ($is_exam_need_judge_by_hand ? 0 : 1) . "' WHERE exe_id=" . Database::escape ( $track_id );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			return self::update_score ( $track_id, $quiz_id, $user_id, $weighting, TRUE );
		} else
			return false;
	}

	/**
	 * 主观题批改的保存(简答题)
	 * @param unknown_type $exam_id
	 * @param unknown_type $result_id
	 * @param unknown_type $questionGotScore
	 * @param unknown_type $questionGotComment
	 */
	function save_subjective_question_judge_result($exam_id, $user_id, $result_id, $questionGotScore, $questionGotComment) {
		global $tbl_exam_rel_user;
		if ($result_id && $questionGotScore && is_array ( $questionGotScore )) {
			$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
			$tbl_exam_result = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
			$tbl_exam_attempt = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
			
			$sql = "SELECT * FROM $tbl_exam_main WHERE id=" . Database::escape ( $exam_id );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$exam_info = Database::fetch_row ( $res );
			
			$questionIDs = array_keys ( $questionGotScore );
			$totalGotScore = 0;
			foreach ( $questionIDs as $question_id ) {
				$score = $questionGotScore [$question_id];
				$comment = $questionGotComment [$question_id];
				$sql_data = array ('marks' => $score, 'teacher_comment' => $comment );
				$sqlwhere = "exe_id=" . Database::escape ( $result_id ) . " AND question_id=" . Database::escape ( $question_id );
				$sql = Database::sql_update ( $tbl_exam_attempt, $sql_data, $sqlwhere );
				//echo $sql,'<br/>';
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$totalGotScore += $score;
			}
			
			//更新学生成绩
			if ($totalGotScore && $result_id) {
				$sql = "SELECT exe_result,exe_weighting FROM $tbl_exam_result WHERE exe_id=" . Database::escape ( $result_id );
				list ( $exe_result, $exe_weighting ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
				$total_score = $exe_result + $totalGotScore;
				$sql = "UPDATE $tbl_exam_result SET exe_result=" . $total_score . ",score=" . round ( $total_score / $exe_weighting ) . ",essay_question_score='" . $totalGotScore . "',fb_status=1 WHERE fb_status=0 AND exe_id=" . Database::escape ( $result_id );
				$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			
			return self::update_score ( $result_id, $exam_id, $user_id, null, TRUE );
		}
	}

	/**
	 * 是否需要手工批改试卷(是否含有简答题)
	 * @param $paper_id
	 * @param $rand_paper_id
	 */
	function is_exam_need_judge_by_hand($quiz_id) {
		global $TBL_EXERCICE_QUESTION;
		$is_need_judgement = 0;
		if ($quiz_id) {
			$sql = "SELECT question_type,question_id FROM $TBL_EXERCICE_QUESTION WHERE exercice_id='" . escape ( $quiz_id ) . "' AND question_type IN (" . FREE_ANSWER . ",".COMBAT_QUESTION.")";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) {
				$is_need_judgement = 1;
			}
		}
		return $is_need_judgement;
	}

	function is_course_exam() {
		return $this->type == 2 && is_not_blank ( $this->cc );
	}

	/**
	 * 实际参加考试人数
	 * @param unknown_type $exam_id
	 * @param unknown_type $dept_id
	 */
	function stat_exam_attempt_user_count($exam_id, $dept_id = null, $is_sub_included = TRUE) {
		$tbl_exam_result = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		if (empty ( $dept_id )) {
			$sql = "SELECT COUNT(DISTINCT(t1.exe_user_id)) FROM $tbl_exam_result AS t1 WHERE t1.exe_exo_id=" . Database::escape ( $exam_id ) . " AND t1.status='completed'";
		} else {
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			if ($is_sub_included) {
				require_once (LIBRARY_PATH . 'dept.lib.inc.php');
				$objDept = new DeptManager ();
				$tmpsql = $objDept->get_subdept_sql ( $dept_id, 't2.dept_sn' );
				if ($tmpsql) $sql_where .= " AND " . $tmpsql;
			} else {
				$sql_where = " AND t2.dept_id=" . Database::escape ( $dept_id );
			}
			$sql = "SELECT COUNT(DISTINCT(t1.exe_user_id)) FROM $tbl_exam_result AS t1 LEFT JOIN $tbl_user AS t2 ON t1.exe_user_id=t2.user_id WHERE t1.eexe_exo_idxam_id=" . Database::escape ( $exam_id ) . " AND t1.status='completed'" . $sql_where;
		}
		$rtn = Database::getval ( $sql, __FILE__, __LINE__ );
		//echo $sql.'<br/>';
		return $rtn;
	}

	/**
	 * 需要评改考试人数
	 * @param unknown_type $exam_id
	 * @param unknown_type $dept_id
	 */
	function stat_exam_tobecorrect_user_count($exam_id, $dept_id = null, $is_sub_included = TRUE) {
		$tbl_exam_result = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		if (empty ( $dept_id )) {
			$sql = "SELECT COUNT(DISTINCT(t1.exe_user_id)) FROM $tbl_exam_result AS t1 WHERE t1.exe_exo_id=" . Database::escape ( $exam_id ) . " AND t1.status='completed' AND fb_status=0";
		} else {
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			if ($is_sub_included) {
				require_once (LIBRARY_PATH . 'dept.lib.inc.php');
				$objDept = new DeptManager ();
				$tmpsql = $objDept->get_subdept_sql ( $dept_id, 't2.dept_sn' );
				if ($tmpsql) $sql_where .= " AND " . $tmpsql;
			} else {
				$sql_where = " AND t2.dept_id=" . Database::escape ( $dept_id );
			}
			$sql = "SELECT COUNT(DISTINCT(t1.exe_user_id)) FROM $tbl_exam_result AS t1 LEFT JOIN $tbl_user AS t2 ON t1.exe_user_id=t2.user_id WHERE t1.exe_exo_id=" . Database::escape ( $exam_id ) . " AND t1.status='completed' AND fb_status=0 " . $sql_where;
		}
		$rtn = Database::getval ( $sql, __FILE__, __LINE__ );
		//echo $sql . '<br/>';
		return $rtn;
	}

	function is_exam_manager($exam_id, $user_id) {
		$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz;
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if ($exam_id && $user_id) {
			$sql = "SELECT * FROM " . $tbl_exam_main . " WHERE exam_manager='" . escape ( $user_id ) . "' AND id=" . Database::escape ( $exam_id );
			return Database::if_row_exists ( $sql, __FILE__, __LINE__ );
		}
		return FALSE;
	}

	/**
	 * 保存学生提交的考试答案
	 * @param unknown_type $score 分数
	 * @param unknown_type $answer 答案
	 * @param unknown_type $quesId 试题
	 * @param unknown_type $exeId 测验ID
	 * @param unknown_type $j
	 */
	static function exercise_attempt($score, $answer, $quesId, $exeId, $j,$file) {
		global $_configuration, $_user, $_cid;
		
		//if ($_configuration ['tracking_enabled']) {
			$reallyNow = time ();
			$user_id = api_get_user_id ();
			$sql_data = array ('course_code' => $_cid, 'user_id' => $user_id, 'exe_id' => $exeId, 'question_id' => $quesId, 'answer' => $answer, 'marks' => $score, 'position' => $j,'file'=>$file );
			$table_attempt = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
			$sql = Database::sql_insert ( $table_attempt, $sql_data );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		//}
	}

	function get_incomplete_attempt($user_id) {
		$tbl_exam_attempt = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$sqlwhere = "exe_user_id=" . Database::escape ( $user_id ) . " AND exe_exo_id=" . Database::escape ( $this->id );
		$sql = "SELECT * FROM $tbl_exam_attempt WHERE status='incomplete' AND " . $sqlwhere;
		$sql .= " ORDER BY exe_id DESC";
		return Database::fetch_one_row ( $sql, __FILE__, __LINE__ );
	}

	//========================================
	/**
	 * changes the exercise sound file
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $sound - exercise sound file
	 * @param - string $delete - ask to delete the file
	 */
	function updateSound($sound, $delete) {
		global $audioPath, $documentPath, $_course, $_user;
		$TBL_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT );
		$TBL_ITEM_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
		
		if ($sound ['size'] && (strstr ( $sound ['type'], 'audio' ) || strstr ( $sound ['type'], 'video' ))) {
			$this->sound = $sound ['name'];
			
			if (@move_uploaded_file ( $sound ['tmp_name'], $audioPath . '/' . $this->sound )) {
				$query = "SELECT 1 FROM $TBL_DOCUMENT " . " WHERE path='" . str_replace ( $documentPath, '', $audioPath ) . '/' . $this->sound . "'";
				$result = api_sql_query ( $query, __FILE__, __LINE__ );
				
				if (! Database::num_rows ( $result )) {
					/*$query="INSERT INTO $TBL_DOCUMENT(path,filetype) VALUES "
					 ." ('".str_replace($documentPath,'',$audioPath).'/'.$this->sound."','file')";
					 api_sql_query($query,__FILE__,__LINE__);*/
					$id = add_document ( $_course, str_replace ( $documentPath, '', $audioPath ) . '/' . $this->sound, 'file', $sound ['size'], $sound ['name'] );
					
					api_item_property_update ( $_course, TOOL_DOCUMENT, $id, 'DocumentAdded', api_get_user_id () );
					item_property_update_on_folder ( $_course, str_replace ( $documentPath, '', $audioPath ), $_user ['user_id'] );
				}
			}
		} elseif ($delete && is_file ( $audioPath . '/' . $this->sound )) {
			$this->sound = '';
		}
	}

	function updateStartTime($startTime) {
		if (preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+)$/', $startTime )) {
			$this->start_time = $startTime . ":00";
		} else {
			$this->start_time = $startTime;
		}
	
	}

	function updateEndTime($endTime) {
		if (preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+)$/', $endTime )) {
			$this->end_time = $endTime . ":00";
		} else {
			$this->end_time = $endTime;
		}
	
	}

	/**
	 * selects questions randomly in the question list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - if the exercise is not set to take questions randomly, returns the question list
	 * without randomizing, otherwise, returns the list with questions selected randomly
	 */
	function selectRandomList() {
		// if the exercise is not a random exercise, or if there are not at least 2 questions
		if (! $this->random || $this->selectNbrQuestions () < 2) {
			return $this->questionList;
		}
		
		// takes all questions
		if ($this->random == - 1 || $this->random > $this->selectNbrQuestions ()) {
			$draws = $this->selectNbrQuestions ();
		} else {
			$draws = $this->random;
		}
		
		srand ( ( double ) microtime () * 1000000 );
		
		$randQuestionList = array ();
		$alreadyChosed = array ();
		
		// loop for the number of draws
		for($i = 0; $i < $draws; $i ++) {
			// selects a question randomly
			do {
				$rand = rand ( 0, $this->selectNbrQuestions () - 1 );
			} while ( in_array ( $rand, $alreadyChosed ) );
			
			$alreadyChosed [] = $rand;
			
			$j = 0;
			
			foreach ( $this->questionList as $key => $val ) {
				// if we have found the question chosed above
				if ($j == $rand) {
					$randQuestionList [$key] = $val;
					break;
				}
				
				$j ++;
			}
		}
		
		return $randQuestionList;
	}

	/**
	 * 增加到本测验的相关题目列表当中
	 * adds a question into the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if the question has been added, otherwise false
	 */
	function addToList($questionId) {
		if (! $this->isInList ( $questionId )) {
			if (! $this->selectNbrQuestions ()) {
				$pos = 1;
			} else {
				$pos = max ( array_keys ( $this->questionList ) ) + 1;
			}
			$this->questionList [$pos] = $questionId;
			return true;
		}
		return false;
	}

	/**
	 * removes a question from the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if the question has been removed, otherwise false
	 */
	function removeFromList($questionId) {
		$pos = array_search ( $questionId, $this->questionList );
		if ($pos === false) {
			return false;
		} else {
			unset ( $this->questionList [$pos] );
			return true;
		}
	}

	function removeQuestion($questionId) {
		$tbl_exam_rel_question = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION ); //exam_main
		$sql = "DELETE FROM " . $tbl_exam_rel_question . " WHERE exercice_id=" . Database::escape ( $this->selectId () ) . " AND question_id=" . Database::escape ( $questionId );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		$this->load_question_list ( $this->selectId () );
	}

	function selectId() {
		return $this->id;
	}

	/**
	 * returns the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise title
	 */
	function selectTitle() {
		return $this->exercise;
	}

	/**
	 * returns the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise description
	 */
	function selectDescription() {
		return $this->description;
	}

	function selectStartTime() {
		if (preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+:\d+)$/', $this->start_time )) {
			preg_match ( '/^(\d+-\d+-\d+\s+\d+:\d+)/', $this->start_time, $matches );
			return empty ( $matches ) ? $this->start_time : $matches [0];
		}
		return $this->start_time;
	}

	function selectEndTime() {
		if (preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+:\d+)$/', $this->end_time )) {
			preg_match ( '/^(\d+-\d+-\d+\s+\d+:\d+)/', $this->end_time, $matches );
			return empty ( $matches ) ? $this->end_time : $matches [0];
		}
		return $this->end_time;
	}

	/**
	 * returns the exercise sound file
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise description
	 */
	function selectSound() {
		return $this->sound;
	}

	/**
	 * returns the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - exercise type
	 */
	function selectType() {
		return $this->type;
	}

	/** returns the number of FeedbackType  *
	 * 0=>Feedback , 1=>DirectFeedback, 2=>NoFeedback
	 * @return - numeric - exercise attempts
	 */
	function selectFeedbackType() {
		return $this->feedbacktype;
	}

	/**
	 * tells if questions are selected randomly, and if so returns the draws
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - 0 if not random, otherwise the draws
	 */
	function isRandom() {
		return $this->random;
	}

	/**
	 * tells if questions are selected randomly, and if so returns the draws
	 *
	 * @author - Carlos Vargas
	 * @return - integer - results disabled exercise
	 */
	function selectResultsDisabled() {
		return $this->results_disabled;
	}

	/**
	 * returns the number of attempts setted
	 *
	 * @return - numeric - exercise attempts
	 */
	function selectAttempts() {
		return $this->attempts;
	}

	function selectDuration() {
		return $this->duration;
	}

	/**
	 * returns the exercise status (1 = enabled ; 0 = disabled)
	 *
	 * @author - Olivier Brouckaert
	 * @return - boolean - true if enabled, otherwise false
	 */
	function selectStatus() {
		return $this->active;
	}

	/**
	 * returns the array with the question ID list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - question ID list
	 */
	function selectQuestionList() {
		return $this->questionList;
	}

	/**
	 * returns the number of questions in this exercise
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of questions
	 */
	function selectNbrQuestions() {
		return sizeof ( $this->questionList );
	}

	/**
	 * returns 'true' if the question ID is in the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if in the list, otherwise false
	 */
	function isInList($questionId) {
		return in_array ( $questionId, $this->questionList );
	}

	/**
	 * changes the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $title - exercise title
	 */
	function updateTitle($title) {
		$this->exercise = $title;
	}

	/**
	 * changes the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $description - exercise description
	 */
	function updateDescription($description) {
		$this->description = $description;
	}

	function updateAttempts($attempt) {
		$this->attempts = $attempt;
	}

	function updateDuration($d) {
		$this->duration = $d;
	}

	/**
	 * changes the exercise feedback type
	 *
	 * @param - numeric $attempts - exercise max attempts
	 */
	function updateFeedbackType($feedback_type) {
		$this->feedbacktype = $feedback_type;
	}

	/**
	 * changes the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $type - exercise type
	 */
	function updateType($type) {
		$this->type = $type;
	}

	/**
	 * sets to 0 if questions are not selected randomly
	 * if questions are selected randomly, sets the draws
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $random - 0 if not random, otherwise the draws
	 */
	function setRandom($random) {
		$this->random = $random;
	}

	/**
	 * enables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function enable() {
		$this->active = 1;
	}

	/**
	 * disables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function disable() {
		$this->active = 0;
	}

	function disable_results() {
		$this->results_disabled = true;
	}

	function enable_results() {
		$this->results_disabled = false;
	}

	function updateResultsDisabled($results_disabled) {
		if ($results_disabled == 1) {
			$this->results_disabled = true;
		} else {
			$this->results_disabled = false;
		}
	}

}
