<?php

class UniqueAnswer extends Question {
	
	static $typePicture = 'mcua.gif';
	static $explanationLangVar = 'UniqueSelect';

	/**
	 * Constructor
	 */
	function UniqueAnswer() {
		parent::Question ();
		$this->type = UNIQUE_ANSWER;
	}

	/**
	 * function which redifines Question::createAnswersForm
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function createAnswersForm($form) {
		$p_nb_answers=  getgpc('nb_answers');
                $p_moreanswers=  getgpc('moreanswers');
		$nb_answers = isset ( $_POST ['nb_answers'] ) ? $p_nb_answers : api_get_setting ( 'default_options_unique_answer' );
		$nb_answers += (isset ( $_POST ['lessAnswers'] ) ? - 1 : (isset ( $p_moreanswers ) ? 1 : 0));
		if ($nb_answers <= 0) $nb_answers = 0;
		$html = '<tr class="containerBody">
				<td class="formLabel">
			' . get_lang ( 'Answers' ) . '
			</td><td class="formTableTd" align="left">
				<table class="quiz_data_table" width="100%">
					<tr style="text-align: center;">
						<th>' . get_lang ( 'Number' ) . '</th>
						<th>' . get_lang ( 'True' ) . '</th>
						<th width="85%">' . get_lang ( 'AnswerOptions' ) . '</th>					
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
		
		for($i = 1; $i <= $nb_answers; ++ $i) {
			if (is_object ( $answer )) {
				if ($answer->correct [$i]) {
					$correct = $i;
				}
				$defaults ['answer[' . $i . ']'] = $answer->answer [$i];
			}
			
			$renderer = $form->defaultRenderer ();
			$renderer->setElementTemplate ( '<td>{element}<!-- BEGIN error --><span class="onError">{error}</span><!-- END error --></td>' );
			
			$answer_number = $form->addElement ( 'text', null, null, 'value="' . self::$alpha [$i] . '"' );
			$answer_number->freeze ();
			
			$form->addElement ( 'radio', 'correct', null, null, $i );
			
			$form->addElement ( 'text', 'answer[' . $i . ']', null, array ('class' => 'inputText', 'style' => "width:98%" ) );
			$form->addRule ( 'answer[' . $i . ']', get_lang ( 'ThisFieldIsRequired' ), 'required' );
			
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
		global $TBL_QUESTIONS, $TBL_EXERCICE_QUESTION, $_course;
		$questionWeighting = trim ( $form->getSubmitValue ( 'questionScore' ) );
		
		$nbrGoodAnswers = 0;
		$correct = $form->getSubmitValue ( 'correct' );
		
		$objAnswer = new Answer ( $this->id );
		$nb_answers = $form->getSubmitValue ( 'nb_answers' ); //选项总数
		

		for($i = 1; $i <= $nb_answers; $i ++) {
			$answer = trim ( $form->getSubmitValue ( 'answer[' . $i . ']' ) );
			$goodAnswer = ($correct == $i) ? true : false;
			if ($goodAnswer) {
				$weighting = abs ( $questionWeighting );
				$this->answer_txt = self::$alpha [$i];
			} else {
				$weighting = 0;
			}
			
			$objAnswer->createAnswer ( $answer, $goodAnswer, '', $weighting, $i );
		
		}
		
		//liyu: 组合题型的子题目
		$this->pid = $form->getSubmitValue ( 'pid' );
		if (! isset ( $this->pid ) || empty ( $this->pid )) $this->pid = 0; //没有值时为顶级类型的题目
		

		// saves the answers into the data base
		$objAnswer->save ();
		
		// sets the total weighting of the question 更新该题目的总分数
		$this->save ();
		
		//liyu: 如果是混合题型，则更新主题干分数
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
			$sql = "SELECT position FROM $TBL_REPONSES WHERE correct=1 AND question_id=" . Database::escape ( $questionId );
			$correct_answer = Database::get_scalar_value ( $sql );
			return ($userAnswer == $correct_answer);
		}
		return FALSE;
	}
}
