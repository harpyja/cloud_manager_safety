<?php

class UniqueAnswer extends Question {
	
	static $typePicture = 'mcua.gif';
	static $explanationLangVar = 'UniqueSelect';


	/**
	 * Constructor
	 */
	function UniqueAnswer() {
		parent::__construct ();
		$this->type = UNIQUE_ANSWER;
	}


	/**
	 * function which redifines Question::createAnswersForm
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function createAnswersForm($form) {
		
		global $fck_attribute;
		
		$fck_attribute = array ();
		$fck_attribute ['Width'] = '90%';
		$fck_attribute ['Height'] = '100px';
		$fck_attribute ['ToolbarSet'] = 'Test';
		$fck_attribute ['Config'] ['IMUploadPath'] = 'exam/'; //Image
		$fck_attribute ['Config'] ['FlashUploadPath'] = 'exam/'; //Flash
		
                $p_nb_answers=  getgpc('nb_answers');
                $p_lessanswers=  getgpc('lessanswers');
                $p_moreanswers= getgpc('moreanswers');
		$nb_answers = isset ( $p_nb_answers ) ? $p_nb_answers : api_get_setting ( "default_options_unique_answer" );
		$nb_answers += (isset ( $p_lessanswers ) ? - 1 : (isset ( $p_moreanswers ) ? 1 : 0));
		if ($nb_answers <= 0) $nb_answers = 0;
		
		$element_html = '<table class="quiz_data_table" width="100%">
					<tr style="text-align: center;">
						<th>' . get_lang ( 'NoOfQuestions' ) . '</th>
						<th width="80%">' . get_lang ( 'AnswerOptions' ) . '</th>
						<th>' . get_lang ( 'Weighting' ) . '</th>
					</tr>';
		$html = Display::table_tr ( get_lang ( 'AnswerOptions' ), $element_html );
		$form->addElement ( 'html', $html );
		
		$defaults = array ();
		$correct = 0;
		if (! empty ( $this->id )) {
			$answer = new Answer ( $this->id );
			$answer->read ();
			if (count ( $answer->nbrAnswers ) > 0 && ! $form->isSubmitted ()) {
				$nb_answers = $answer->nbrAnswers;
			}
		}
		
		$form->addElement ( 'hidden', 'nb_answers' );
		
		for($i = 1; $i <= $nb_answers; ++ $i) {
			if (is_object ( $answer )) {
				if ($answer->correct [$i]) {
					$correct = $i;
				}
				$defaults ['answer[' . $i . ']'] = $answer->answer [$i];
				$defaults ['weighting[' . $i . ']'] = $answer->weighting [$i];
			}
			
			$renderer = $form->defaultRenderer ();
			$renderer->setElementTemplate ( '<td><!-- BEGIN error --><span class="onError">{error}</span><!-- END error -->{element}</td>' );
			
			$answer_number = $form->addElement ( 'text', null, null, 'value="' . self::$alpha [$i] . '"' );
			$answer_number->freeze ();
			
			$form->addElement ( 'text', 'answer[' . $i . ']', null, array ('class' => 'inputText', 'style' => "width:98%" ) );
			$form->addRule ( 'answer[' . $i . ']', get_lang ( 'ThisFieldIsRequired' ), 'required' );
			
			$form->addElement ( 'text', 'weighting[' . $i . ']', null, array ("style" => "vertical-align:middle;width:40px", 'class' => 'inputText', "value" => "1" ) );
			
			$form->addElement ( 'html', '</tr>' );
		}
		$form->addElement ( 'html', '</table></td></tr>' );
		
		$form->addElement ( 'html', '<tr class="containerBody"><td class="formLabel"></td><td class="formTableTd" align="left">' );
		$form->addElement ( 'submit', 'lessAnswers', get_lang ( 'LessAnswer' ), array ('class' => "inputSubmit" ) );
		$form->addElement ( 'submit', 'moreAnswers', get_lang ( 'PlusAnswer' ), array ('class' => "inputSubmit" ) );
		$form->addElement ( 'html', '</td></tr>' );
		if (is_object ( $renderer )) {
			$renderer->setElementTemplate ( '{element}&nbsp;', 'lessAnswers' );
			$renderer->setElementTemplate ( '{element}', 'moreAnswers' );
		}
		
		//We check the first radio button to be sure a radio button will be check
		if ($correct == 0) {
			$correct = 1;
		}
		$defaults ['correct'] = $correct;
		$form->setDefaults ( $defaults );
		
		$form->setConstants ( array ('nb_answers' => $nb_answers ) );
	}


	/**
	 * abstract function which creates the form to create / edit the answers of the question
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function processAnswersCreation($form) {
		global $tbl_exam_question;
		
		$objAnswer = new Answer ( $this->id );
		$objAnswer->survey_id = $this->survey_id;
		$nb_answers = $form->getSubmitValue ( 'nb_answers' ); //选项总数
		

		for($i = 1; $i <= $nb_answers; $i ++) {
			$answer = trim ( $form->getSubmitValue ( 'answer[' . $i . ']' ) );
			$weighting = trim ( $form->getSubmitValue ( 'weighting[' . $i . ']' ) );
			$objAnswer->createAnswer ( $answer, $weighting, $i );
		}
		
		// saves the answers into the data base
		$objAnswer->save ();
		
		$this->save ();
	
	}


	static function display_question($questionId, $questionName, $seq, $option_display_type = 1) {
		$html = '<div class="exam_problem dd7">';
		if ($option_display_type == 1) { //垂直显示
			$html .= '<div><b>' . $seq . "</b>、 " . $questionName . '</div>';
			$html .= '<div style="height: 9px; overflow: hidden;"></div>';
			$html .= '<ul style="margin-left: 20px;">';
			//显示答案
			$objAnswerTmp = new Answer ( $questionId );
			$nbrAnswers = $objAnswerTmp->nbrAnswers;
			for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
				$answer = $objAnswerTmp->getAnswer ( $answerId );
				$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
				$html .= '<li><input class="checkbox" type="radio" name="choice[' . $questionId . ']" value="' . $answerId . '" id="q_'.$seq.'_' .  $answerId . '" />';
				$html .= '<label	for="q_' . $seq . "_" . $answerId . '">' . Question::$alpha [$answerId] . '. ' . $answer . '</label></li>';
			}
			$html .= '</ul>';
		} else {
			$html .= '<div style="width:100%"><div style="float:left;"><b>' . $seq . "</b>、 " . $questionName . '</div>';
			$html .= '<div style="float:right;">
					<ul style="margin-left: 10px; list-style-type:none;">';
			$objAnswerTmp = new Answer ( $questionId );
			$nbrAnswers = $objAnswerTmp->nbrAnswers;
			for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
				$answer = $objAnswerTmp->getAnswer ( $answerId );
				$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
				$html .= '<li style="float:left;padding-left:20px"><input class="checkbox" type="radio" name="choice[' . $questionId . ']" value="' . $answerId . '" id="q_'.$seq.'_' .  $answerId . '" />';
				$html .= '<label for="q_' . $seq . "_" . $answerId . '">' . Question::$alpha [$answerId] . '. ' . $answer . '</label></li>';
			}
			$html .= '</ul></div></div>';
		
		}
		$html.='<input type="hidden" id="qt_'.$seq.'" value="'.UNIQUE_ANSWER.'" />';
		$html .= '<div class="clearall"></div>
				</div>';
		$html .= '<div class="clearall"></div>';
		return $html;
	}


	static function save_result($survey_id, $user_id, $questionId, $examResult, $myScore) {
		global $tbl_survey_question_option, $_configuration;
		$choice = $examResult [$questionId]; //为 选项顺序值
		$sql = "select id,value from $tbl_survey_question_option where question_id=" . Database::escape ( $questionId ) . " AND sort=" . Database::escape ( $choice );
		list ( $answer, $myScore ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
		SurveyManager::save_survey_submit_question ( $survey_id, $user_id, $questionId, $answer, $myScore );
	}


	/**
	 * 评分
	 * @param unknown_type $questionId
	 * @param unknown_type $examResult
	 */
	static function judge_question($questionId, $examResult) {
		$questionScore = 0;
		$choice = $examResult [$questionId];
		$objAnswerTmp = new Answer ( $questionId );
		$nbrAnswers = $objAnswerTmp->nbrAnswers;
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answerWeighting = $objAnswerTmp->getWeighting ( $answerId );
			$studentChoice = ($choice == $answerId) ? 1 : 0;
			if ($studentChoice) {
				$questionScore += $answerWeighting;
				break;
			}
		}
		return $questionScore;
	}


	static function display_result($questionId, $questionName, $seq, $examResult) {
		global $tbl_exam_paper_rel_reqestion, $tbl_exam_question_major;
		$choice = $examResult [$questionId];
		$html = '<div class="exam_problem dd7">';
		
		$html .= '<div style="height: auto; border-right: 1px dashed #c3c3c3; float: left; width: 800px; padding: 10px 0;">';
		$html .= '<div><b>' . $seq . "</b>、 " . $questionName . '</div>';
		$html .= '<div style="height: 9px; overflow: hidden;"></div>
		<table style="text-align: center; margin-top: 10px;" cellspacing="0">
		<tr><td width="80">我的选择</td><td width="600">选项</td><td>分值权重</td></tr>';
		
		//显示答案
		$objAnswerTmp = new Answer ( $questionId );
		$nbrAnswers = $objAnswerTmp->nbrAnswers;
		$questionScore = 0;
		$isRight = false;
		
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->getAnswer ( $answerId );
			$studentChoice = ($choice == $answerId) ? 1 : 0;
			if ($studentChoice) {
				$answerWeighting = $objAnswerTmp->getWeighting ( $answerId );
				$questionScore = $answerWeighting;
				break;
			}
		}
		
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->getAnswer ( $answerId );
			$studentChoice = ($choice == $answerId) ? 1 : 0;
			
			$file_name1 = 'radio';
			$file_name1 .= ($studentChoice ? '_on.gif' : '_off.gif');
			$html .= '<tr><td>' . Display::return_icon ( $file_name1 ) . '</td>
        				<td class="tbl_answer">' . api_parse_tex ( $answer ) . '</td>
        				<td>' . $objAnswerTmp->getWeighting ( $answerId ) . '</td>
        				</tr>';
		}
		$html .= '</table>';
		$html .= '<div class="clearall"></div>
				</div>';
		
		$html .= '<div style="float: left; width: 105px; text-align: center; margin-top: 60px;">';
		$html .= '<br /><strong>此题得' . ($questionScore) . '分</strong>';
		$html .= '</div>';
		
		$html .= '<div class="clearall"></div></div>';
		
		return array ("html" => $html, "score" => $questionScore );
	}


	static function display_stat_result($questionId, $questionName, $seq, $statResult) {
		global $tbl_exam_paper_rel_reqestion, $tbl_exam_question_major;
	
		$html = '<div class="exam_problem dd7">';
		$html .= '<div style="height: auto; border-right: 0px dashed #c3c3c3; float: left; width: 100%; padding: 10px 0;">';
		$html .= '<div><b>' . $seq . "</b>、 " . $questionName . '</div>';
		$html .= '<div style="height: 2px; overflow: hidden;"></div>
		<table class="tbl_exam_options" style="width:100%;text-align: center; margin-top: 10px;" cellspacing="0">
		<tr><th width="80">序号</th><th>选项</th><th>票数</th><th style="width:70px">比例</th><th style="width:300px">图示</th></tr>';
		
		//显示答案
		$objAnswerTmp = new Answer ( $questionId );
		$nbrAnswers = $objAnswerTmp->nbrAnswers;
		$questionScore = 0;
		$isRight = false;
		
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->getAnswer ( $answerId );
			$answer_id=$objAnswerTmp->getAnswerId($answerId);
			$result=$statResult[$answer_id];
			$cnt_opt_id=($result['cnt_opt_id']?$result['cnt_opt_id']:0); //票数
			$rate=($result['rate']?$result['rate']:0); //比例
			
			$html .= '<tr><td>' . Question::$alpha[$answerId] . '</td>
        				<td class="tbl_answer" style="border-bottom:1px;padding-left:10px">' . api_parse_tex ( $answer ) . '</td>
        				<td>'.($cnt_opt_id).'</td>
        				<td>'.($rate).'%</td>
        				<td><div class="tiao">
        					<div style="background: none repeat scroll 0% 0% rgb(0, 49, 92); height: 13px; width: '.$rate.'%;"></div>
						</div></td>
        				</tr>';
		}
		$html .= '</table>';
		$html .= '<div class="clearall"></div>
				</div>';
		
		$html .= '<div class="clearall"></div></div>';
		
		return array ("html" => $html, "score" => $questionScore );
	}

}