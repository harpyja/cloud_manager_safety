<?php

/*
 填空题
 */

class FillBlanks extends Question {
	
	static $typePicture = 'fill_in_blanks.gif';
	static $explanationLangVar = 'FillBlanks';

	function FillBlanks() {
		parent::question ();
		$this->type = FILL_IN_BLANKS;
	}

	function createAnswersForm($form) {
		$defaults = array ();
		if ($this->id) {
			$objAnswer = new answer ( $this->id );
			$a_answer = explode ( '::', $objAnswer->selectAnswer ( 1 ) );
			$defaults ['answer'] = $a_answer [0];
			$a_weightings = explode ( ',', $a_answer [1] );
			$defaults ['answer'] = $this->answer_txt;
		} else {
			//$defaults ['answer'] = get_lang ( 'DefaultTextInBlanks' );
		}
		
		// answer
		$form->addElement ( 'html', '<br /><tr class="containerBody"><td class="formLabel"></td><td class="formTableTd" align="left">' . get_lang ( 'TypeTextBelow' ) . ', ' . get_lang ( 'UseTagForBlank' ) . '</td></tr>' );
		$form->addElement ( 'textarea', 'answer', get_lang ( 'Answer' ), array ('id' => "answer", 'class' => 'inputText', "cols" => "70", "rows" => "6" ) );
		$form->addRule ( 'answer', get_lang ( 'GiveText' ), 'required' );
		$form->addRule ( 'answer', get_lang ( 'DefineBlanks' ), 'regex', '/\[.*\]/' );
		$renderer = $form->defaultRenderer ();
		$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
		$renderer->setElementTemplate ( $default_template, 'answer' );
		
		//$form -> addElement('html','<tr class="containerBody"><td class="formLabel">'.get_lang('Weighting').'</td><td class="formTableTd" align="left"><div id="blanks_weighting"></div></td></tr>');
		

		$form->setDefaults ( $defaults );
	}

	function processAnswersCreation($form) {
		$answer = $form->getSubmitValue ( 'answer' );
		$objAnswer = new answer ( $this->id );
		$objAnswer->createAnswer ( $this->answer_txt, 0, '', 0, '' );
		$objAnswer->save ();
		
		//$answer = str_replace('::','',$answer);
		$nb = preg_match_all ( '/\[[^\]]*\]/', $answer, $blanks );
		$this->answer_txt = implode ( ' ', $blanks [0] );
		
		$this->save ();
	}

}
