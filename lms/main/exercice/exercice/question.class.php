<?php
/*
 各种题型的基类
 */
define ( 'UNIQUE_ANSWER', 1 ); //单选题
define ( 'MULTIPLE_ANSWER', 2 ); //多选题
define ( 'FILL_IN_BLANKS', 4 ); //填空题 3=>4
define ( 'MATCHING', 5 ); //匹配题 4=>5
define ( 'FREE_ANSWER', 6 ); //简答题 5=>6
define ( 'TRUE_FALSE_ANSWER', 3 ); //判断题 6=>3
define ( 'CLOZE_ANSWER', 7 ); //完形填空题
define ( 'COMBO_QUESTION', 8 ); //多题型组合题
define ( 'CLOZE_QUESTION', 9 ); //完形填空(字谜)
define ( 'COMBAT_QUESTION', 10 ); //实战


abstract class Question {
	static $alpha = array ('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
	
	var $id;
	var $question;
	var $description;
	var $comment;
	var $weighting;
	var $vm_name;
    var $is_up;
	var $position;
	var $type;
	var $level;
	var $picture;
	var $answer_txt;
	var $pid;
	var $question_code;
	var $pool_id;
	var $cc;
	
	var $exerciseList; // array with the list of exercises which this question is in
	var $subQuestionList;
	
	static $typePicture = 'new_question.png';
	static $explanationLangVar = '';
	
	static $questionTypes = array (UNIQUE_ANSWER => array ('unique_answer.class.php', 'UniqueAnswer' ), 
			MULTIPLE_ANSWER => array ('multiple_answer.class.php', 'MultipleAnswer' ), 
			TRUE_FALSE_ANSWER => array ('true_false_answer.class.php', 'TrueFalseAnswer' ), 
			FILL_IN_BLANKS => array ('fill_blanks.class.php', 'FillBlanks' ), 
			FREE_ANSWER => array ('freeanswer.class.php', 'FreeAnswer' ), 
			COMBO_QUESTION => array ('combo_question.class.php', 'ComboQuestion' ), 
			CLOZE_QUESTION => array ('cloze_question.class.php', 'ClozeQuestion' ) ,
        COMBAT_QUESTION => array ('combat_question.class.php', 'CombatQuestion' ) ,
    );

	function Question() {
		$this->id = 0;
		$this->question = '';
		$this->description = '';
		$this->comment = "";
		$this->weighting = 0;
		$this->vm_name = "";
        $this->is_up = 0;

        $this->position = 1;
		$this->level = 3;
		$this->picture = '';
		$this->pid = 0;
		$this->question_code = "";
		$this->pool_id = "";
		$this->cc = "";
		$this->answer_txt = '';
		$this->exerciseList = array ();
		$this->subQuestionList = array ();
	}

	/**
	 * reads question informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID
	 * @return - boolean - true if question exists, otherwise false
	 */
	static function read($id) {
		global $_course;
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		
		$sql = "SELECT * FROM $TBL_QUESTIONS WHERE id=" . Database::escape ( $id );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($object = Database::fetch_object ( $result )) {
			$objQuestion = Question::getInstance ( $object->type );
			$objQuestion->id = $id;
			$objQuestion->question = $object->question;
			$objQuestion->description = $object->description;
			$objQuestion->comment = $object->comment;
			$objQuestion->weighting = $object->ponderation;
			$objQuestion->vm_name = $object->vm_name;
            $objQuestion->is_up = $object->is_up;
			$objQuestion->position = $object->position;
			$objQuestion->level = $object->level;
			$objQuestion->type = $object->type;
			$objQuestion->picture = $object->picture;
			$objQuestion->pid = $object->pid;
			$objQuestion->question_code = $object->question_code;
			$objQuestion->cc = $object->cc;
			$objQuestion->pool_id = $object->pool_id;
			$objQuestion->answer_txt = $object->answer;
			
			/*$sql = "SELECT exercice_id FROM $TBL_EXERCICE_QUESTION WHERE question_id='" . intval ( $id ) . "'";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $object = Database::fetch_object ( $result ) ) {
				$objQuestion->exerciseList [] = $object->exercice_id;
			}*/
			
			if ($objQuestion->type == COMBO_QUESTION or $objQuestion->type == CLOZE_QUESTION) {
				$sql = "SELECT * FROM $TBL_QUESTIONS WHERE pid='" . Database::escape_string ( $objQuestion->id ) . "' ORDER BY position";
				$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
				while ( $object1 = Database::fetch_object ( $result1 ) ) {
					while ( isset ( $objQuestion->subQuestionList [$object1->position] ) ) {
						$object1->position ++;
					}
					$objQuestion->subQuestionList [$object1->position] = $object1->id;
				}
			}
			return $objQuestion;
		}
		return false;
	}

	function updateType($type) {
		global $TBL_REPONSES;
		if ($type != $this->type) {
			if (! in_array ( $this->type, array (UNIQUE_ANSWER, MULTIPLE_ANSWER ) ) || ! in_array ( $type, array (UNIQUE_ANSWER, MULTIPLE_ANSWER ) )) {
				$sql = "DELETE FROM $TBL_REPONSES WHERE question_id='" . $this->id . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			$this->type = $type;
		}
	}

	/**
	 * updates the question in the data base
	 * if an exercise ID is provided, we add that exercise ID into the exercise list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $exerciseId - exercise ID if saving in an exercise
	 */
	function save($exerciseId = 0) {
		global $TBL_QUESTIONS, $TBL_EXERCICE_QUESTION, $_course;
		
		$id = $this->id;
		$question = ($this->question);
		$description = ($this->description);
		$comment = ($this->comment);
		$weighting = $this->weighting;
		if (empty ( $this->weighting )) $weighting = 0;
        $vm_name=$this->vm_name;
		$level = $this->level;
        $is_up=$this->is_up;
        $position = $this->position;
		$type = $this->type;
		$picture = ($this->picture);
		$pid = $this->pid;
		if (empty ( $pid )) $pid = 0;
		$question_code = $this->question_code;
		
		if ($id) {
                    if($picture!=''){
                        $sql_data = array ('question' => $question, 'description' => $description, 'comment' => $comment, 'ponderation' => $weighting, 'position' => $position, 'type' => $type, 'picture' => $picture,
                'question_code' => $question_code, 'level' => $level );
                    }else{
                        $sql_data = array ('question' => $question, 'description' => $description, 'comment' => $comment, 'ponderation' => $weighting, 'position' => $position, 'type' => $type,
                'question_code' => $question_code, 'level' => $level );
                    }
			
			$sql_data ['cc'] = $this->cc;
			$sql_data ['pool_id'] = $this->pool_id;
			$sql_data ['vm_name'] = $this->vm_name;
            $sql_data ['is_up'] = $this->is_up;
            $sql_data ['answer'] = $this->answer_txt;
			$sql = Database::sql_update ( $TBL_QUESTIONS, $sql_data, "id=" . Database::escape ( $id ) );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} 

		else { //新增
			if (! isset ( $this->pid ) or empty ( $this->pid )) {
				$sql = "SELECT max(position) FROM $TBL_QUESTIONS as question, $TBL_EXERCICE_QUESTION as test_question WHERE question.id=test_question.question_id AND test_question.exercice_id='$exerciseId'";
			} else {
				$sql = "SELECT max(position) FROM $TBL_QUESTIONS as question WHERE pid='" . Database::escape_string ( $this->pid ) . "'";
			}
			$current_position = Database::getval ( $sql, __FILE__, __LINE__ );
			$this->updatePosition ( $current_position + 1 );
			$position = $this->position;
			
			$sql_data = array ('question' => $question, 'description' => $description, 'comment' => $comment, 'ponderation' => $weighting, 'position' => $position, 'type' => $type, 'picture' => $picture,
                'question_code' => $question_code, 'level' => $level, 'pid' => $pid );
			$sql_data ['created_user'] = $sql_data ['last_updated_user'] = api_get_user_id ();
			$sql_data ['created_date'] = $sql_data ['last_updated_date'] = date ( 'Y-m-d H:i:s' );
			$sql_data ['cc'] = $this->cc;
			$sql_data ['pool_id'] = $this->pool_id;
			$sql_data ['vm_name'] = $this->vm_name;
            $sql_data ['is_up'] = $this->is_up;
            $sql_data ['answer'] = $this->answer_txt;
			$sql = Database::sql_insert ( $TBL_QUESTIONS, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$this->id = Database::get_last_insert_id ();
		}
		
		if ($exerciseId) $this->addToList ( $exerciseId );
	}

	function addToList($exerciseId) {
		global $TBL_EXERCICE_QUESTION;
		
		$sql = "SELECT max(question_order) FROM  $TBL_EXERCICE_QUESTION as test_question WHERE  test_question.exercice_id=" . Database::escape ( $exerciseId ) . " AND question_type=" . $this->type;
		$max_order = Database::getval ( $sql, __FILE__, __LINE__ );
		$max_order = ($max_order ? $max_order + 1 : 1);
		
		$id = $this->id; //question.id
		if (! in_array ( $exerciseId, $this->exerciseList ) && empty ( $this->pid )) {
			$this->exerciseList [] = $exerciseId;
			$sql_data = array ('question_id' => $id, 'exercice_id' => $exerciseId, 'question_order' => $max_order, 'question_type' => $this->type,'question_score' => $this->weighting );
			$sql_data ['cc'] = $this->cc;
			$sql = Database::sql_insert ( $TBL_EXERCICE_QUESTION, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	function removeFromList($exerciseId) {
		global $TBL_EXERCICE_QUESTION;
		$id = $this->id;
		$pos = array_search ( $exerciseId, $this->exerciseList );
		if ($pos === false) {
			return false;
		} else {
			unset ( $this->exerciseList [$pos] );
			$sql = "DELETE FROM $TBL_EXERCICE_QUESTION WHERE question_id='$id' AND exercice_id='$exerciseId'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			return true;
		}
	}

	/**
	 * deletes a question from the database
	 * the parameter tells if the question is removed from all exercises (value = 0),
	 * or just from one exercise (value = exercise ID)
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $deleteFromEx - exercise ID if the question is only removed from one exercise
	 */
function delete($deleteFromEx = 0) {
		global $TBL_EXERCICE_QUESTION, $TBL_QUESTIONS, $TBL_REPONSES;
		$id = $this->id;
	//	$sql = "SELECT COUNT(*) FROM $TBL_EXERCICE_QUESTION WHERE question_id='" . escape ( $id ) . "'";
		//if (Database::getval ( $sql ) > 0) return 101;
		
		//if (empty ( $deleteFromEx )) {
			//判断是否为COMBO或CLOZE题型
			$sql = "SELECT pid,ponderation,created_user FROM $TBL_QUESTIONS WHERE id=" . Database::escape ( $id );
			list ( $pid, $weighting, $created_user ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			
			if (can_do_my_bo ( $created_user )) {
				$sql = "DELETE FROM $TBL_EXERCICE_QUESTION WHERE question_id='" . escape ( $id ) . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				$sql = "DELETE FROM $TBL_QUESTIONS WHERE id='" . escape ( $id ) . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				$sql = "DELETE FROM $TBL_REPONSES WHERE question_id='" . escape ( $id ) . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				if ($pid) {
					$sql = "UPDATE $TBL_QUESTIONS SET ponderation=ponderation-" . $weighting . "WHERE id='" . $pid . "'";
					$res = api_sql_query ( $sql, __FILE__, __LINE__ );
				}
				$this->removePicture ();
				$this->Question ();
				return SUCCESS;
			}else{
				return false;
			}
		//} else {
			//return $this->removeFromList ( $deleteFromEx );
		//}
		//return false;
	}

	/**
	 * Returns an instance of the class corresponding to the type
	 * @param integer $type the type of the question
	 * @return an instance of a Question subclass (or of Questionc class by default)
	 */
	static function getInstance($type) {
		list ( $file_name, $class_name ) = self::$questionTypes [$type];
		include_once ($file_name);
		if (class_exists ( $class_name )) {
			return new $class_name ();
		} else {
			echo 'Can\'t instanciate class ' . $class_name . ' of type ' . $type;
			return null;
		}
	}

	function _check_question_code($inputValue) {
		$tbl_question = Database::get_course_table ( TABLE_QUIZ_QUESTION );
		$sql = "SELECT * FROM " . $tbl_question . " WHERE question_code= " . Database::escape ( $inputValue );
		return Database::if_row_exists ( $sql ) == false;
	}

	function _get_question_code() {
		return date ( "YmdHis" ) . "_" . api_get_user_id () . "_" . num_rand ( 6 );
	}

	/**
	 * 题干
	 * Creates the form to create / edit a question
	 * A subclass can redifine this function to add fields...
	 * @param FormValidator $form the formvalidator instance (by reference)
	 */
	function createForm(&$form) {
		
		$html = '<table align="center" width="100%" cellpadding="4" cellspacing="0">';
		//$html .= '<tr><th class="formTableTh" colspan="2">' . self::get_question_type_name ( $this->type ) . '</th></tr>';
		//$form->addElement ( 'static', null, null, $html );
		

		$required_html = '<span class="form_required">*</span>';
		$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}<!-- BEGIN error --><span class="onError">{error}</span><!-- END error --></td></tr>';
		$renderer = $form->defaultRenderer ();
		
		$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$tbl_exam_question_pool = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION_POOL );
		$sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . "  ORDER BY display_order ASC";
		$all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
        $all_pools[0]="请选择题库";
        $all_pools = array_reverse($all_pools, TRUE);
		$form->addElement ( 'select', 'pool_id', get_lang ( "QuestionPool" ), $all_pools, array ('style' => 'min-width:120px' ) );
		$renderer->setElementTemplate ( $default_template, 'pool_id' );
		
		$sql = "SELECT code,title FROM " . $tbl_course . "";
		$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		//$form->addElement ( 'select', 'cc', get_lang ( "Courses" ), $all_courses );
		$renderer->setElementTemplate ( $default_template, 'cc' );



		// 题型
		$answerType = intval ( $_REQUEST ['answerType'] );
		$form->addElement ( 'hidden', 'answerType', $_REQUEST ['answerType'] );
		//标记dengxin
                $form->addElement ( 'hidden', 'check', $required_html,array("id"=>"check" ) );
		// 题目
//		$test=$form->addElement('text','questionCode',$required_html.get_lang('题目'),array("id"=>"questionCode",'class'=>"inputText",'style'=>"width:79%"));
//		 $renderer->setElementTemplate($default_template,'questionCode');
//		 $form->addRule('questionCode', get_lang('GiveQuestion'), 'required');
//		 
		//编号---- > 改成题目
		$test = $form->addElement ( 'text', 'questionCode', $required_html . get_lang ( 'QuestionCode' ), array ("id" => "questionCode", 'class' => "inputText", 'style' => "width:40%" ) );
		$renderer->setElementTemplate ( $default_template, 'questionCode' );
		if (empty ( $this->question_code )) {
			$form->addRule ( 'questionCode', get_lang ( 'PleaseEnterQuestionCode' ), 'required' );
			//$form->addRule ( 'questionCode', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'username' );
			$form->addRule ( 'questionCode', get_lang ( 'QuestionCodeExist' ), 'callback', '_check_question_code' );
			$defaults ['questionCode'] = $this->_get_question_code ();
		} else {
			$defaults ['questionCode'] = $this->question_code;
			//$form->freeze ( array ("questionCode" ) );
		}
		
		//题目内容
        if ($GLOBALS ['hide_question_name'] == false) {
            $form->addElement ( 'textarea', 'questionName', $required_html . get_lang ( 'Question' ), array ('id' => 'description', 'style' => 'width:100%;height:150px', 'wrap' => 'virtual', 'class' => 'inputText' ) );
            $renderer->setElementTemplate ( $default_template, 'questionName' );
            $form->addRule ( 'questionName', get_lang ( 'GiveQuestion' ), 'required' );

            $form->addElement ( 'file', 'media_file', get_lang ( '上传文件' ), array ('style' => "width:350px", 'class' => 'inputText' ,'id'=>'media_file' ) );
            $renderer->setElementTemplate ( $default_template, 'media_file' );
            if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'edit') {
                $attachment = AttachmentManager::get_sys_attachment2 ( $this->picture, 'QUESTION' );
                //var_dump($attachment); echo $attachment['url'];
                $attachment_url = api_get_path ( WEB_PATH ) . "main/course/download.php?doc_url=" . urlencode ( $attachment ['url'] );
                $form->addElement ( 'static', 'fileUpload', "",
                    get_lang ( "Attachment" ) . ($attachment ['url'] ? "" . $attachment ['old_name'] . "(" . format_file_size ( intval ( $attachment ['size'] ) ) . ")" : get_lang ( 'None' )) . "," . get_lang ( 'fileUploadedTip' ) );
                //get_lang ( "Attachment" ) . ($attachment ['url'] ? "<a href=\"" . $attachment_url . "\">" . $attachment ['old_name'] . "</a>(" . format_file_size ( intval ( $attachment ['size'] ) ) . ")" : get_lang ( 'None' )) . "," . get_lang ( 'fileUploadedTip' ) );
                $renderer->setElementTemplate ( $default_template, 'fileUpload' );
            }
            $form->addRule ( 'media_file', get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( 0 ) . ' MB', 'maxfilesize', get_upload_max_filesize ( 0 ) * 1048576 );
            $form->addRule ( 'media_file', get_lang ( 'UploadFileNameAre' ) . ' *.flv,*.mp4,*.mp3,*.zip,*.rar', 'filename', '/\\.(flv|mp4|mp3|zip|rar)$/' );
        }


        //难度
		$group = array ();
		$group [] = $form->createElement ( 'radio', 'level', null, get_lang ( 'DifficultyEasier' ), 1 );
		$group [] = $form->createElement ( 'radio', 'level', null, get_lang ( 'DifficultyEasy' ), 2 );
		$group [] = $form->createElement ( 'radio', 'level', null, get_lang ( 'DifficultyNormal' ), 3 );
		$group [] = $form->createElement ( 'radio', 'level', null, get_lang ( 'DifficultyHard' ), 4 );
		$group [] = $form->createElement ( 'radio', 'level', null, get_lang ( 'DifficultyHarder' ), 5 );
		$form->addGroup ( $group, "level", get_lang ( 'DifficultyLevel' ), '&nbsp;&nbsp;', false );
		/*$level_options=array('1'=>get_lang("DifficultyEasier"),'2'=>get_lang("DifficultyEasy"),'3'=>get_lang("DifficultyNormal"),
		 '4'=>get_lang("DifficultyHard"),'5'=>get_lang("DifficultyHarder"));
		 $form->addElement("select","level",get_lang("DifficultyLevel"),$level_options,array("style"=>'width:10%'));*/
		$renderer->setElementTemplate ( $default_template, 'level' );
        //是否有报告提交
        $group = array ();
        $group [] = $form->createElement ( 'radio', 'is_up', null, get_lang ( '否' ),0 );
        $group [] = $form->createElement ( 'radio', 'is_up', null, get_lang ( '是' ),1 );

        $form->addGroup ( $group, "is_up", get_lang ( '是否有报告提交' ), '&nbsp;&nbsp;', false );
        /*$level_options=array('1'=>get_lang("DifficultyEasier"),'2'=>get_lang("DifficultyEasy"),'3'=>get_lang("DifficultyNormal"),
         '4'=>get_lang("DifficultyHard"),'5'=>get_lang("DifficultyHarder"));
         $form->addElement("select","level",get_lang("DifficultyLevel"),$level_options,array("style"=>'width:10%'));*/
        $renderer->setElementTemplate ( $default_template, 'is_up' );

        //分数
		$test = $form->addElement ( 'text', 'questionScore', $required_html . get_lang ( 'QuestionWeighting' ), array ("id" => "questionScore", 'class' => "inputText", 'style' => "width:10%" ) );
		$form->addRule ( 'questionScore', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'questionScore', get_lang ( 'ThisFieldShouldBeNumeric' ), 'numeric' );
		$renderer->setElementTemplate ( $default_template, 'questionScore' );
		
		//题目解析
		$fck_attribute ['Height'] = '100';
		$form->addElement ( 'textarea', 'questionComment', get_lang ( 'QuestionAnalysis' ), array ('style' => 'width:100%;height:60px', 'wrap' => 'virtual', 'class' => 'inputText' ) );
		//$form->add_html_editor ( 'questionComment', get_lang ( 'QuestionAnalysis' ), false );
		$renderer->setElementTemplate ( $default_template, 'questionComment' );
		
		// hidden values
		$form->addElement ( 'hidden', 'myid', $_REQUEST ['myid'] );
		
		//$defaults = array();
		$defaults ['pool_id'] = empty ( $this->pool_id ) ?intval( getgpc ( 'pool_id') ) : $this->pool_id;
		$defaults ['vm_name'] = empty ( $this->vm_name ) ? getgpc ( 'vm_name' ) : $this->vm_name;
        $defaults ['is_up'] = empty ( $this->id ) ? 0 : $this->is_up;
        $defaults ['cc'] = empty ( $this->cc ) ? getgpc ( 'cc' ) : $this->cc;
		$defaults ['questionName'] = $this->question;
		$defaults ['questionDescription'] = $this->description;
		$defaults ['questionComment'] = $this->comment;
                $defaults ['media_file'] = $this->picture;
                $defaults ['check'] = $this->picture;
                //echo "$this->picture";
		$defaults ['level'] = empty ( $this->id ) ? api_get_setting ( 'default_question_level_option' ) : $this->level;
		$defaults ['questionScore'] = empty ( $this->weighting ) ? 1 : $this->weighting;
		$form->setDefaults ( $defaults );
	}

	/**
	 * 问题题干的创建（名称及描述）
	 * function which process the creation of questions
	 * @param FormValidator $form the formvalidator instance
	 * @param Exercise $objExercise the Exercise instance
	 */
	function processCreation($form, $objExercise) {
		
		//题目
		$this->updateTitle ( trim ( $form->getSubmitValue ( 'questionName' ) ) );
		
		//描述
		$this->updateDescription ( trim ( $form->getSubmitValue ( 'questionDescription' ) ) );
		
		//解析
		$questionComment = trim ( $form->getSubmitValue ( 'questionComment' ) );
		$this->updateComment ( $questionComment );
		
		//难度
		$this->updateLevel ( $form->getSubmitValue ( 'level' ) );
		
		//缺省分数
		$questionWeighting = trim ( $form->getSubmitValue ( 'questionScore' ) );
		$this->updateWeighting ( $questionWeighting );
		
		//编号
		$questionCode = trim ( $form->getSubmitValue ( 'questionCode' ) );
		$this->updateQuestionCode ( $questionCode );
		
		$answer_txt = $form->getSubmitValue ( 'answer' );
		if (is_string ( $answer_txt )) $this->answer_txt = trim ( $answer_txt );
		
		//上级编号
		$this->updatePid ( $form->getSubmitValue ( "pid" ) );
		
		$this->pool_id = trim ( $form->getSubmitValue ( 'pool_id' ) );
		$this->vm_name = trim ( $form->getSubmitValue ( 'vm_name' ) );
		$this->cc = trim ( $form->getSubmitValue ( 'cc' ) );
        $this->is_up = trim ( $form->getSubmitValue ( 'is_up' ) );

        if (! is_null ( $objExercise ) && is_object ( $objExercise )) {
			$this->save ( $objExercise->id ); //新增编辑试题,$objExercise -> id 为空的话,不会增加到quiz_rel_question表
			$objExercise->addToList ( $this->id );
			$objExercise->save (); //保存测验主信息
		} else {
			$this->save ( 0 );
			$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH ) . 'exam_questions/';
			$file_element = & $form->getElement ( 'media_file' );
			$this->uploadPicture ( $file_element, $sys_attachment_path );
		}
	}

	abstract function createAnswersForm($form);

	abstract function processAnswersCreation($form);

	/**
	 * Displays the menu of question types
	 */
	static function display_type_menu() {
		global $exerciseId;
		//V1.4
		/*foreach(self::$questionTypes as $i=>$a_type)
		 {

			// include the class of the type
			include_once($a_type[0]);

			// get the picture of the type and the langvar which describes it
			eval('$img = '.$a_type[1].'::$typePicture;');
			eval('$explanation = get_lang('.$a_type[1].'::$explanationLangVar);');

			echo '
			<div id="answer_type_'.$i.'" style="float: left; width:120px; text-align:center">
			<a href="admin.php?newQuestion=yes&answerType='.$i.'&exerciseId='.$exerciseId.'">
			<div>' . Display::return_icon($img) . '</div>
			<div>' . $explanation . '</div>
			</a>
			</div>';
			}*/
		echo '<div id="answer_type" style="float: left; width:120px; text-align:center">
				<a href="question_pool.php?fromExercise=' . $exerciseId . '">
					<div>' . Display::return_icon ( 'database.gif' ) . '</div>
					<div>' . get_lang ( 'GetExistingQuestion' ) . '</div>
				</a>
			</div>
		<div style="clear:both"></div>';
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

	/**
	 * 更改题目位置
	 *
	 * @param unknown_type $id
	 * @param unknown_type $direction 方向UP与DOWN
	 */
	static function changePosition($id, $direction = 'UP') {
		//$TBL_QUESTIONS         = Database::get_course_table(TABLE_QUIZ_QUESTION);
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		$objQuestion = Question::read ( $id );
		if (is_object ( $objQuestion )) {
			if (isset ( $objQuestion->pid ) && ! empty ( $objQuestion->pid )) {
				//$subQuestionList=$objQuestion->selectSubQuestionList();
				$sql = "SELECT position,id FROM $TBL_QUESTIONS WHERE pid='" . escape ( $objQuestion->pid ) . "' ORDER BY position";
				$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
				while ( $object1 = Database::fetch_object ( $result1 ) ) {
					$subQuestionList [$object1->position] = $object1->id;
				}
				
				$subQuestionList2 = Question::_arrange_questionList ( $subQuestionList, $id, $direction );
				
				foreach ( $subQuestionList2 as $position => $qid ) {
					$sql = "UPDATE " . $TBL_QUESTIONS . " SET position='" . $position . "' WHERE id='" . $qid . "'";
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
			}
		}
	}

	static function _arrange_questionList($questionList, $id, $direction) {
		if (! is_array ( $questionList ) or ! isset ( $id ) or ! isset ( $direction )) return;
		foreach ( $questionList as $position => $questionId ) {
			// if question ID found
			if ($questionId == $id) {
				// position of question in the array
				$pos1 = $position;
				
				if ($direction == 'UP') {
					prev ( $questionList );
				} else {
					next ( $questionList );
				}
				
				// position of previous question in the array
				$pos2 = key ( $questionList );
				
				// error, can't move question
				if (! $pos2) {
					return;
				}
				
				$id2 = $questionList [$pos2];
				
				// exits foreach()
				break;
			}
			
			// goes to next question
			next ( $questionList );
		}
		
		// permutes questions in the array
		$temp = $questionList [$pos2];
		$questionList [$pos2] = $questionList [$pos1];
		$questionList [$pos1] = $temp;
		return $questionList;
	}

	function get_combo_sub_question_count($pid) {
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
		if (empty ( $pid )) return 0;
		$sql = "SELECT COUNT(*) FROM " . $TBL_QUESTIONS . " WHERE pid=" . Database::escape ( $pid );
		return Database::getval ( $sql, __FILE__, __LINE__ );
	}

	//=============================================
	/**
	 * 以前的picture字段，现在修改为多媒体文件
	 * @param unknown_type $Picture
	 * @param unknown_type $PictureName
	 */
	
	function uploadPicture($file_element, $sys_attachment_path) {
		global $TBL_EXERCICE_QUESTION;
		$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
               $filename = $file_element->getValue ();
		if ($file_element &&  $filename ['name'] !='' ) {
			if ($this->id && $this->picture) {
				AttachmentManager::update_sys_attachment ( 'QUESTION', $this->id, $sys_attachment_path );
                        }
                            $file = AttachmentManager::hanle_sys_upload ( $file_element, 'QUESTION', $sys_attachment_path );
                            
                       
			if (isset ( $file ) && $file && is_array ( $file )) {
				$this->picture = $file ['save_uri'];
				$uniqid = $file ['attachment_uniqid'];
				$sql = "UPDATE " . $tbl_attachment . " SET ref_id='" . $this->id . "' WHERE name='" . $uniqid . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			if ($this->picture) {
				$sql = "UPDATE " . $TBL_EXERCICE_QUESTION . " SET picture=" . Database::escape ( $this->picture ) . " WHERE id=" . Database::escape ( $this->id );
				return true;
			}
		}
		
		return false;
	}

	function removePicture() {
		global $picturePath;
		
		// if the question has got an ID and if the picture exists
		if ($this->id) {
			$picture = $this->picture;
			$this->picture = '';
			return @unlink ( $picturePath . '/' . $picture ) ? true : false;
		}
		return false;
	}

	function exportPicture($questionId) {
		global $TBL_QUESTIONS, $picturePath;
		if ($this->id && ! empty ( $this->picture )) {
			$picture = explode ( '.', $this->picture );
			$Extension = $picture [sizeof ( $picture ) - 1];
			$picture = 'quiz-' . $questionId . '.' . $Extension;
			$sql = "UPDATE $TBL_QUESTIONS SET picture='$picture' WHERE id='$questionId'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			return @copy ( $picturePath . '/' . $this->picture, $picturePath . '/' . $picture ) ? true : false;
		}
		
		return false;
	}

	/**
	 * saves the picture coming from POST into a temporary file
	 * Temporary pictures are used when we don't want to save a picture right after a form submission.
	 * For example, if we first show a confirmation box.
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $Picture - temporary path of the picture to move
	 * @param - string $PictureName - Name of the picture
	 */
	function setTmpPicture($Picture, $PictureName) {
		global $picturePath;
		
		$PictureName = explode ( '.', $PictureName );
		$Extension = $PictureName [sizeof ( $PictureName ) - 1];
		
		// saves the picture into a temporary file
		@move_uploaded_file ( $Picture, $picturePath . '/tmp.' . $Extension );
	}

	/**
	 * moves the temporary question "tmp" to "quiz-$questionId"
	 * Temporary pictures are used when we don't want to save a picture right after a form submission.
	 * For example, if we first show a confirmation box.
	 *
	 * @author - Olivier Brouckaert
	 * @return - boolean - true if moved, otherwise false
	 */
	function getTmpPicture() {
		global $picturePath;
		
		// if the question has got an ID and if the picture exists
		if ($this->id) {
			if (file_exists ( $picturePath . '/tmp.jpg' )) {
				$Extension = 'jpg';
			} elseif (file_exists ( $picturePath . '/tmp.gif' )) {
				$Extension = 'gif';
			} elseif (file_exists ( $picturePath . '/tmp.png' )) {
				$Extension = 'png';
			}
			
			$this->picture = 'quiz-' . $this->id . '.' . $Extension;
			
			return @rename ( $picturePath . '/tmp.' . $Extension, $picturePath . '/' . $this->picture ) ? true : false;
		}
		
		return false;
	}

	//=============================================
	

	function selectId() {
		return $this->id;
	}

	function selectPid() {
		return $this->pid;
	}

	function selectQuestionCode() {
		return $this->question_code;
	}

	function selectTitle() {
		$this->question = api_parse_tex ( $this->question );
		return $this->question;
	}

	function selectDescription() {
		$this->description = api_parse_tex ( $this->description );
		return $this->description;
	}

	function selectComment() {
		$this->comment = api_parse_tex ( $this->comment );
		return $this->comment;
	}

	function selectWeighting() {
		return $this->weighting;
	}

	function selectPosition() {
		return $this->position;
	}

	function selectLevel() {
		return $this->level;
	}

	function selectType() {
		return $this->type;
	}

    function updateVmname($vm_name) {
        $this->vm_name = $vm_name;
    }

    function updateIsup($is_up) {
        $this->is_up = $is_up;
    }

	function selectPicture() {
		return $this->picture;
	}

	function selectExerciseList() {
		return $this->exerciseList;
	}

	function selectSubQuestionList() {
		return $this->subQuestionList;
	}

	function selectNbrExercises() {
		return sizeof ( $this->exerciseList );
	}

	function updateTitle($title) {
		$this->question = $title;
	}

	function updateDescription($description) {
		$this->description = $description;
	}

	function updateComment($comment) {
		$this->comment = $comment;
	}

	function updateWeighting($weighting) {
		$this->weighting = $weighting;
	}

	function updatePosition($position) {
		$this->position = $position;
	}

	function updateLevel($level) {
		$this->level = $level;
	}

	function updatePid($pid) {
		$this->pid = $pid;
	}

	function updateQuestionCode($questionCode) {
		$this->question_code = $questionCode;
	}

}
