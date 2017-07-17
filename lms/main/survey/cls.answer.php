<?php

class Answer {
	
	var $survey_id;
	var $questionId;
	var $answer;
	var $weighting;
	var $position;
	
	// these arrays are used to save temporarily new answers
	// then they are moved into the arrays above or deleted in the event of cancellation
	var $new_answer_id;
	var $new_answer;
	var $new_weighting;
	var $new_position;
	
	var $nbrAnswers;
	var $new_nbrAnswers;
	
	//正确答案
	var $correctAnswer;


	function Answer($questionId) {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		
		$this->questionId = $questionId;
		
		$sql = "SELECT type FROM " . $tbl_survey_question . " WHERE id=" . Database::escape ( $questionId );
		$this->questionType = Database::get_scalar_value ( $sql );
		
		$this->answer_id = array ();
		$this->answer = array ();
		$this->weighting = array ();
		$this->position = array ();
		$this->correctAnswer = "";
		
		$this->cancel ();
		
		$this->read ();
	}


	/**
	 * reads answer informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function read() {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$questionId = $this->questionId;
		$sql = "SELECT * FROM $tbl_survey_question_option WHERE question_id=" . Database::escape ( $questionId );
		$sql .= " ORDER BY sort";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$i = 1;
		while ( $object = Database::fetch_object ( $result ) ) {
			$this->answer_id [$i] = $object->id;
			$this->answer [$i] = $object->option_text;
			$this->weighting [$i] = $object->value;
			$this->position [$i] = $object->sort;
			
			$i ++;
		}
		
		$this->nbrAnswers = $i - 1;
	}


	/**
	 * records answers into the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save() {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$questionId = $this->questionId;
		
		// removes old answers before inserting of new ones
		$sql = "DELETE FROM $tbl_survey_question_option WHERE question_id='$questionId'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		// inserts new answers into data base
		$sql = "INSERT INTO $tbl_survey_question_option (question_id,option_text,value,sort,survey_id";
		$sql .= ") VALUES";
		
		for($i = 1; $i <= $this->new_nbrAnswers; $i ++) {
			$answer = addslashes ( $this->new_answer [$i] );
			$weighting = $this->new_weighting [$i];
			$position = $this->new_position [$i];
			
			if (empty ( $position )) $position = 0;
			
			$sql .= "('" . $questionId . "','" . $answer . "','" . $weighting . "','" . $position . "'";
			$sql .= ",'" . $this->survey_id . "'),";
		}
		
		$sql = substr ( $sql, 0, - 1 );
		
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		// moves $new_* arrays
		$this->answer_id = $this->new_answer_id;
		$this->answer = $this->new_answer;
		$this->weighting = $this->new_weighting;
		$this->position = $this->new_position;
		
		$this->nbrAnswers = $this->new_nbrAnswers;
		
		$this->cancel ();
	}


	function cancel() {
		$this->new_answer_id = array ();
		$this->new_answer = array ();
		$this->new_weighting = array ();
		$this->new_position = array ();
		$this->new_nbrAnswers = 0;
	}


	/**
	 * Returns a list of answers
	 * @author Yannick Warnier <ywarnier@beeznest.org>
	 * @return array	List of answers where each answer is an array of (id, answer, comment, grade) and grade=weighting
	 */
	function getAnswersList() {
		$list = array ();
		for($i = 1; $i <= $this->nbrAnswers; $i ++) {
			if (! empty ( $this->answer [$i] )) {
				$list [] = array ('id' => $i, 'answer' => $this->answer [$i], 'value' => $this->weighting [$i] );
			}
		}
		return $list;
	}


	/**
	 * Returns the question type
	 * @author	Yannick Warnier <ywarnier@beeznest.org>
	 * @return	integer	The type of the question this answer is bound to
	 */
	function getQuestionType() {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$sql = "SELECT type FROM $tbl_survey_question WHERE id = " . Database::escape ( $this->questionId );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $res ) <= 0) {
			return null;
		}
		$row = Database::fetch_array ( $res );
		return $row ['type'];
	}


	/**
	 * returns the answer title
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer title
	 */
	function getAnswer($id) {
		return $this->answer [$id];
	}


	function getAnswerId($id) {
		return $this->answer_id [$id];
	}


	/**
	 * returns answer weighting
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer weighting
	 */
	function getWeighting($id) {
		return $this->weighting [$id];
	}


	/**
	 * returns answer position
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer position
	 */
	function getPosition($id) {
		return $this->position [$id];
	}


	function isCorrect($id) {
		return $this->correct [$id];
	}


	/**
	 * creates a new answer
	 *
	 * @author Olivier Brouckaert
	 * @param string 	answer title
	 * @param integer 	0 if bad answer, not 0 if good answer
	 * @param string 	answer comment
	 * @param integer 	answer weighting
	 * @param integer 	answer position
	 * @param coordinates 	Coordinates for hotspot exercises (optional)
	 * @param integer		Type for hotspot exercises (optional)
	 */
	function createAnswer($answer, $weighting, $position) {
		$this->new_nbrAnswers ++;
		$id = $this->new_nbrAnswers;
		$this->new_answer [$id] = $answer;
		$this->new_weighting [$id] = $weighting;
		$this->new_position [$id] = $position;
	
	}


	/**
	 * updates an answer
	 *
	 * @author Toon Keppens
	 * @param	string	Answer title
	 * @param	string	Answer comment
	 * @param	integer	Answer weighting
	 * @param	integer	Answer position
	 */
	function updateAnswers($answer, $comment, $weighting, $position) {
		global $tbl_survey, $tbl_survey_question, $tbl_survey_question_option;
		$questionId = $this->questionId;
		$sql_data = array ('option_text' => $answer, 'value' => $weighting, 'sort' => $position );
		$where = "id=" . Database::escape ( $position ) . " AND question_id=" . Database::escape ( $questionId );
		$sql = Database::sql_update ( $tbl_survey_question_option, $sql_data, $where );
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

}