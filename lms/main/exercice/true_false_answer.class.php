<?php

/*
 判断题
 */

class TrueFalseAnswer extends Question {
	
	static $typePicture = 'yesno.gif';
	static $explanationLangVar = 'TrueFalseAnswer';

	/**
	 * Constructor
	 */
	function TrueFalseAnswer() {
		parent::question ();
		$this->type = TRUE_FALSE_ANSWER;
	}

	/**
	 * function which redifines Question::createAnswersForm
	 * @param the formvalidator instance
	 */
	function createAnswersForm($form) {
		$group = array ();
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'answer', null, get_lang ( 'QuestionRight' ), 1 );
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'answer', null, get_lang ( 'QuestionWrong' ), 0 );
		$form->addGroup ( $group, "TFAnswer", get_lang ( 'Answer' ), '', false );
		$renderer = $form->defaultRenderer ();
		$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
		$renderer->setElementTemplate ( $default_template, 'TFAnswer' );
		
		$correct = 0;
		
		if (! empty ( $this->id )) {
			$answer = new Answer ( $this->id );
			$answer->read ();
			//var_dump($answer);exit;
			if (is_object ( $answer )) {
				if ($answer->correct [1])
					$an = 1;
				else $an = 0;
			}
			
			$form->setDefaults ( array ('answer' => $an ) );
		} else {
			$an = (api_get_setting ( 'default_tfquestion_option' ) == 'true' ? 1 : 0);
			$form->setDefaults ( array ('answer' => $an ) );
		}
	}

	function processAnswersCreation($form) {
		global $TBL_QUESTIONS, $TBL_EXERCICE_QUESTION, $_course;
		$objAnswer = new Answer ( $this->id );
		$an = $form->getSubmitValue ( 'answer' );
		$goodAnswer = ($an == 1 ? true : false);
		$weighting = ($goodAnswer ? $this->weighting : 0);
		$this->answer_txt = ($goodAnswer ? get_lang ( "QuestionRight" ) : get_lang ( "QuestionWrong" ));
		$objAnswer->createAnswer ( get_lang ( "QuestionRight" ), $goodAnswer, "", ($goodAnswer ? $this->weighting : 0), 1 );
		$objAnswer->createAnswer ( get_lang ( "QuestionWrong" ), ! $goodAnswer, "", (! $goodAnswer ? $this->weighting : 0), 2 );
		
		$objAnswer->save ();
		$this->save ();
		
		if ($this->pid) {
			$sql = "SELECT SUM(ponderation) FROM " . $TBL_QUESTIONS . " WHERE pid='" . $this->pid . "' AND id<>'" . $this->id . "'";
			$combo_question_weighting = Database::get_scalar_value ( $sql );
			$combo_question_weighting += $weighting;
			
			$sql = "UPDATE " . $TBL_QUESTIONS . " SET ponderation='" . $combo_question_weighting . "' WHERE id='" . $this->pid . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	function is_correct($questionId,$userAnswer) {
		if ($userAnswer) {
			global $TBL_REPONSES;
			$sql = "SELECT position FROM $TBL_REPONSES WHERE correct=1 AND question_id=" . Database::escape ( $questionId );
			return ($userAnswer == Database::get_scalar_value ( $sql ));
		}
		return FALSE;
	}
}
