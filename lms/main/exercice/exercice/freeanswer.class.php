<?php

/*
    问答题
*/

class FreeAnswer extends Question {
	
	static $typePicture = 'open_answer.gif';
	static $explanationLangVar = 'freeAnswer';

	function FreeAnswer() {
		parent::question ();
		$this->type = FREE_ANSWER;
	}

	function createAnswersForm($form) {
		//$form -> addElement('text','weighting',get_lang('Weighting'),array('class'=>'inputText','style'=>"width:8%;text-align:right"));
		$defaults = array ();
		if ($this->id) {
			$objAnswer = new Answer ( $this->id );
			$defaults ['answer'] = $objAnswer->selectAnswer ( 1 );
			$defaults ['questionScore'] = $this->weighting;
		}
		$form->addElement ( 'textarea', 'answer', get_lang ( 'QuestionStdAnswer' ), array ('id' => "answer", 'class' => 'inputText', 'style' => 'width:99%;height:120px' ) );
		$renderer = $form->defaultRenderer ();
		$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
		$renderer->setElementTemplate ( $default_template, 'answer' );
		if (empty ( $this->id )) $defaults ['questionScore'] = 10;
		$form->setDefaults ( $defaults );
	}

	function processAnswersCreation($form) {
		$answer = $form->getSubmitValue ( 'answer' );
		$objAnswer = new Answer ( $this->id );
		$objAnswer->createAnswer ( $answer, 0, '', 0, '' );
		$objAnswer->save ();
		
		$this->weighting = $form->getSubmitValue ( 'questionScore' );
		$this->save ();
	}

	static function get_correct_answer_str($questionId) {
		$objAnswerTmp = new Answer ( $questionId );
		$answer = $objAnswerTmp->getAnswer ( 1 );
		return trim ( $answer );
	}

	static function get_score($result_id,$question_id) {
		$tbl_exam_attempt = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT ); //track_e_attempt
	    $sql="SELECT marks FROM $tbl_exam_attempt WHERE exe_id=".Database::escape($result_id)." AND question_id=".Database::escape($question_id);//." AND user_id=".Database::escape($user_id);
		return Database::getval($sql,__FILE__,__LINE__);
	}
	
	static function get_teacher_comment($result_id,$question_id) {
		$tbl_exam_attempt = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT ); //track_e_attempt
	    $sql="SELECT teacher_comment FROM $tbl_exam_attempt WHERE exe_id=".Database::escape($result_id)." AND question_id=".Database::escape($question_id);//." AND user_id=".Database::escape($user_id);
		return Database::getval($sql,__FILE__,__LINE__);
	}
	
	
}
