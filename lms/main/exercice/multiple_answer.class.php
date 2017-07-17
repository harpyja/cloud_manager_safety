<?php

/*
 多选题
 */

class MultipleAnswer extends Question {
	
	static $typePicture = 'mcma.gif';
	static $explanationLangVar = 'MultipleSelect';

	/**
	 * Constructor
	 */
	function MultipleAnswer() {
		parent::question ();
		$this->type = MULTIPLE_ANSWER;
	}

	/**
	 * function which redifines Question::createAnswersForm
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function createAnswersForm($form) {
		
		global $fck_attribute;
		
		$fck_attribute = array ();
		$fck_attribute ['Width'] = '100%';
		$fck_attribute ['Height'] = '100px';
		$fck_attribute ['ToolbarSet'] = 'Test';
		$fck_attribute ['Config'] ['IMUploadPath'] = 'upload/test/';
		$fck_attribute ['Config'] ['FlashUploadPath'] = 'upload/test/';
		
                $p_nb_answers=  getgpc('nb_answers');
		$nb_answers = isset ( $_POST ['nb_answers'] ) ? $p_nb_answers : api_get_setting ( 'default_options_multiple_answer' );
		$nb_answers += (isset ( $_POST ['lessAnswers'] ) ? - 1 : (isset ( $_POST ['moreAnswers'] ) ? 1 : 0));
		if ($nb_answers <= 0) $nb_answers = 0;
		
		$html = '<tr class="containerBody">
				<td class="formLabel">
			' . get_lang ( 'Answers' ) . '
			</td><td class="formTableTd" align="left">
				<table  class="quiz_data_table" width="100%">
					<tr style="text-align: center;">
						<th>
							' . get_lang ( 'Number' ) . '
						</th>
						<th>
							' . get_lang ( 'True' ) . '
						</th>
						<th width="88%">
							' . get_lang ( 'AnswerOptions' ) . '
						</th>
						<!--<th>
							' . get_lang ( 'Comment' ) . '
						</th>
						<th>
							' . get_lang ( 'Weighting' ) . '
						</th>-->						
					</tr>';
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
		
		$boxes_names = array ();
		
		for($i = 1; $i <= $nb_answers; ++ $i) {
			
			if (is_object ( $answer )) {
				$defaults ['answer[' . $i . ']'] = $answer->answer [$i];
				$defaults ['correct[' . $i . ']'] = $answer->correct [$i];
			
		//$defaults['comment['.$i.']'] = $answer -> comment[$i]; //V1.4.0
			//$defaults['weighting['.$i.']'] = $answer -> weighting[$i];
			}
			
			$renderer = $form->defaultRenderer ();
			$renderer->setElementTemplate ( '<td>{element}<!-- BEGIN error --><span class="onError">{error}</span><!-- END error --></td>' );
			
			$answer_number = $form->addElement ( 'text', null, null, 'value="' . self::$alpha [$i] . '"' );
			$answer_number->freeze ();
			
			$form->addElement ( 'checkbox', 'correct[' . $i . ']', null, null, $i );
			$boxes_names [] = 'correct[' . $i . ']';
			
			//$form->addElement('html_editor', 'answer['.$i.']',null, 'style="vertical-align:middle"');
			//$form->addElement('textarea', 'answer['.$i.']', null,array('cols'=>45,'rows'=>4,'class'=>'inputText'));
			$form->addElement ( 'text', 'answer[' . $i . ']', null, array ('class' => 'inputText', 'style' => "width:98%" ) );
			$form->addRule ( 'answer[' . $i . ']', get_lang ( 'ThisFieldIsRequired' ), 'required' );
			
			//$form->addElement('html_editor', 'comment['.$i.']',null, 'style="vertical-align:middle"');
			//$form->addElement('textarea', 'comment['.$i.']', null,array('cols'=>45,'rows'=>4,'class'=>'inputText')); //V1.4.0
			

			//$form->addElement('text', 'weighting['.$i.']',null, 'style="vertical-align:middle" size="5" value="0"');
			

			$form->addElement ( 'html', '</tr>' );
		}
		$form->addElement ( 'html', '</table></td></tr>' );
		
		$form->add_multiple_required_rule ( $boxes_names, get_lang ( 'ChooseAtLeastOneCheckbox' ), 'multiple_required' );
		$form->addElement ( 'html', '<tr class="containerBody"><td class="formLabel"></td><td class="formTableTd" align="left">' );
		$form->addElement ( 'submit', 'lessAnswers', get_lang ( 'LessAnswer' ), 'class="inputSubmit"' );
		$form->addElement ( 'submit', 'moreAnswers', get_lang ( 'PlusAnswer' ), 'class="inputSubmit"' );
		$form->addElement ( 'html', '</td></tr>' );
		
		if (is_object ( $renderer )) {
			$renderer->setElementTemplate ( '{element}&nbsp;', 'lessAnswers' );
			$renderer->setElementTemplate ( '{element}', 'moreAnswers' );
		}
		//$form -> addElement ('html', '</div></div>');
		

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
		global $TBL_QUESTIONS, $TBL_EXERCICE_QUESTION, $_course;
		$questionWeighting = trim ( $form->getSubmitValue ( 'questionScore' ) );
		
		$nbrGoodAnswers = 0;
		$correct = $form->getSubmitValue ( 'correct' );
		
		$objAnswer = new Answer ( $this->id );
		
		$nb_answers = $form->getSubmitValue ( 'nb_answers' );
		
		for($i = 1; $i <= $nb_answers; $i ++) {
			$goodAnswer = trim ( $form->getSubmitValue ( 'correct[' . $i . ']' ) );
			if ($goodAnswer) {
				$nbrGoodAnswers ++;
			}
		}
		$weighting = round ( abs ( $questionWeighting ) / $nbrGoodAnswers, 1 );
		
		$multi_answer = array ();
		for($i = 1; $i <= $nb_answers; $i ++) {
			//$comment = trim($form -> getSubmitValue('comment['.$i.']'));
			//$weighting = trim($form -> getSubmitValue('weighting['.$i.']'));
			$answer = trim ( $form->getSubmitValue ( 'answer[' . $i . ']' ) );
			$goodAnswer = trim ( $form->getSubmitValue ( 'correct[' . $i . ']' ) );
			$weight = ($goodAnswer ? $weighting : 0);
			if ($goodAnswer) $multi_answer [] = self::$alpha [$i];
			$objAnswer->createAnswer ( $answer, $goodAnswer, '', $weight, $i );
		}
		$this->answer_txt = implode ( '|', $multi_answer );
		// 保存答案选项
		$objAnswer->save ();
		
		$this->save ();
		
		if ($this->pid) {
			$sql = "SELECT SUM(ponderation) FROM " . $TBL_QUESTIONS . " WHERE pid='" . $this->pid . "' AND id<>'" . $this->id . "'";
			//echo $sql;
			$combo_question_weighting = Database::get_scalar_value ( $sql );
			$combo_question_weighting += $questionWeighting;
			
			$sql = "UPDATE " . $TBL_QUESTIONS . " SET ponderation='" . $combo_question_weighting . "' WHERE id='" . $this->pid . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	
	}

	function is_correct($questionId,$userAnswer) {
		if ($userAnswer) {
			global $TBL_REPONSES;
			$sql = "SELECT position FROM $TBL_REPONSES WHERE correct=1 AND  question_id=" . Database::escape ( $questionId );
			$corrct_answers = Database::get_into_array ( $sql, __FILE__, __LINE__ ); //标准答案
			$key_choice = array_keys ( $userAnswer ); //用户答案
			return juge_multiple_judge ( $corrct_answers, $key_choice );
		}
		return FALSE;
	}

}

