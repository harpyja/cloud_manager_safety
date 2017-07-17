<?php

/*

*/

class Answer {
	var $questionId;
	
	var $answer;
	var $correct;
	var $comment;
	var $weighting;
	var $position;
	var $hotspot_coordinates;
	var $hotspot_type;
	
	// these arrays are used to save temporarily new answers
	// then they are moved into the arrays above or deleted in the event of cancellation
	var $new_answer;
	var $new_correct;
	var $new_comment;
	var $new_weighting;
	var $new_position;
	
	var $nbrAnswers;
	var $new_nbrAnswers;
	
	//正确答案
	var $correctAnswer;

	/**混合题型中的子题目*/
	//var $sub_questionsList;
	

	/**
	 * constructor of the class
	 *
	 * @author 	Olivier Brouckaert
	 * @param 	integer	Question ID that answers belong to
	 */
	function Answer($questionId) {
		$this->questionId = $questionId;
		$this->questionType =$this->getQuestionType();
		
		$this->answer = array ();
		$this->correct = array ();
		$this->comment = array ();
		$this->weighting = array ();
		$this->position = array ();
		$this->correctAnswer = "";
		
		//$this->sub_questionsList=array();//混合题型中的子题目
		$this->cancel ();
		$this->read ();
	}

	/**
	 * reads answer informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function read() {
		global $_course;
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS, $TBL_REPONSES;
		
		$questionId = $this->questionId;
		$sql = "SELECT answer,correct,comment,ponderation,position FROM $TBL_REPONSES WHERE question_id=" . Database::escape ( $questionId );
		$sql .= " ORDER BY position";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$i = 1;
		while ( $object = Database::fetch_object ( $result ) ) {
			$this->answer [$i] = $object->answer;
			$this->correct [$i] = $object->correct;
			$this->comment [$i] = $object->comment;
			$this->weighting [$i] = $object->ponderation;
			$this->position [$i] = $object->position;
			
			//正确答案
			if ($this->questionType != COMBO_QUESTION or $this->questionType != CLOZE_QUESTION) {
				switch ($this->questionType) {
					case TRUE_FALSE_ANSWER :
					case UNIQUE_ANSWER :
						if ($object->correct) $this->correctAnswer = Question::$alpha [$i];
						break;
					case MULTIPLE_ANSWER :
						if ($object->correct) $this->correctAnswer .= Question::$alpha [$i] . ",";
						break;
					
					case FILL_IN_BLANKS :
						$all_match = array ();
						$str = $object->answer;
						do {
							preg_match ( "/\\[\\W+\\]/", $str, $match );
							$str = $match [0];
							$str_match = substr ( $str, 0, strpos ( $str, "]" ) + 1 );
							($str_match) && $all_match [] = $str_match;
							$str = ltrim ( $str, $str_match );
						} while ( ! empty ( $match ) );
						$this->correctAnswer = (empty ( $all_match ) && ! is_array ( $all_match ) ? "" : implode ( ",", $all_match ));
						break;
					
					case FREE_ANSWER :
					default :
						$this->correctAnswer = "";
				}
			}
			
			$i ++;
		}
		
		$this->nbrAnswers = $i - 1;
	}

	/**
	 * clears $new_* arrays
	 *
	 * @author - Olivier Brouckaert
	 */
	function cancel() {
		$this->new_answer = array ();
		$this->new_correct = array ();
		$this->new_comment = array ();
		$this->new_weighting = array ();
		$this->new_position = array ();
		$this->new_hotspot_coordinates = array ();
		$this->new_hotspot_type = array ();
		
		$this->new_nbrAnswers = 0;
	}

	/**
	 * reads answer informations from the data base ordered by parameter
	 * @param	string	Field we want to order by
	 * @param	string	DESC or ASC
	 * @author 	Frederic Vauthier
	 */
	function readOrderedBy($field, $order = ASC) {
		global $_course;
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS, $TBL_REPONSES;
		$field = Database::escape_string ( $field );
		if (empty ( $field )) {
			$field = 'position';
		}
		if ($order != 'ASC' and $order != 'DESC') {
			$order = 'ASC';
		}
		$questionId = $this->questionId;
		
		$sql = "SELECT answer,correct,comment,ponderation,position " . "FROM $TBL_REPONSES " . "WHERE question_id='$questionId' " . "ORDER BY $field $order";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$i = 1;
		
		// while a record is found
		while ( $object = Database::fetch_object ( $result ) ) {
			$this->answer [$i] = $object->answer;
			$this->correct [$i] = $object->correct;
			$this->comment [$i] = $object->comment;
			$this->weighting [$i] = $object->ponderation;
			$this->position [$i] = $object->position;
			$i ++;
		}
		
		$this->nbrAnswers = $i - 1;
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
				$list [] = array ('id' => $i, 'answer' => $this->answer [$i], 'comment' => $this->comment [$i], 'grade' => $this->weighting [$i], 'correct' => $this->correct [$i] );
			}
		}
		return $list;
	}

	/**
	 * returns the number of answers in this question
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of answers
	 */
	function selectNbrAnswers() {
		return $this->nbrAnswers;
	}

	/**
	 * returns the question ID which the answers belong to
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - the question ID
	 */
	function selectQuestionId() {
		return $this->questionId;
	}

	/**
	 * returns the answer title
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer title
	 */
	function selectAnswer($id) {
		return $this->answer [$id];
	}

	/**
	 * Returns a list of grades
	 * @author Yannick Warnier <ywarnier@beeznest.org>
	 * @return array	List of grades where grade=weighting (?)
	 */
	function getGradesList() {
		$list = array ();
		for($i = 0; $i < $this->nbrAnswers; $i ++) {
			if (! empty ( $this->answer [$i] )) {
				$list [$i] = $this->weighting [$i];
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
		global $TBL_QUESTIONS;
		$sql = "SELECT type FROM $TBL_QUESTIONS WHERE id = " . Database::escape ( $this->questionId );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $res ) <= 0) return null;
		$row = Database::fetch_array ( $res );
		return $row ['type'];
	}

	/**
	 * tells if answer is correct or not
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - 0 if bad answer, not 0 if good answer
	 */
	function isCorrect($id) {
		return $this->correct [$id];
	}

	/**
	 * returns answer comment
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer comment
	 */
	function selectComment($id) {
		return $this->comment [$id];
	}

	function selectWeighting($id) {
		return $this->weighting [$id];
	}

	function selectPosition($id) {
		return $this->position [$id];
	}

	function createAnswer($answer, $correct, $comment, $weighting, $position) {
		$this->new_nbrAnswers ++;
		$id = $this->new_nbrAnswers;
		$this->new_answer [$id] = $answer;
		$this->new_correct [$id] = $correct;
		$this->new_comment [$id] = $comment;
		$this->new_weighting [$id] = $weighting;
		$this->new_position [$id] = $position;
	}

	function updateAnswers($answer, $comment, $weighting, $position) {
		global $TBL_REPONSES;
		$questionId = $this->questionId;
		$sql_data = array ('answer' => $answer, 'comment' => $comment, 'ponderation' => $weighting, 'position' => $position );
		$where = "id=" . Database::escape ( $position ) . " AND question_id=" . Database::escape ( $questionId );
		$sql = Database::sql_update ( $TBL_REPONSES, $sql_data, $where );
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function save() {
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS, $TBL_REPONSES;
		$questionId = $this->questionId;
		$sql = "DELETE FROM $TBL_REPONSES WHERE question_id='$questionId'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		$sql = "INSERT INTO $TBL_REPONSES" . "(id,question_id,answer,correct,comment," . "ponderation,position";
		$sql .= ") VALUES";
		
		for($i = 1; $i <= $this->new_nbrAnswers; $i ++) {
			$answer = escape ( $this->new_answer [$i] );
			$correct = $this->new_correct [$i];
			$comment = escape ( $this->new_comment [$i] );
			$weighting = $this->new_weighting [$i];
			$position = $this->new_position [$i];
			if (empty ( $position )) $position = 0;
			
			$sql .= "('" . $i . "','" . $questionId . "','" . $answer . "','" . ($correct ? 1 : 0) . "','" . $comment . "',
					'" . $weighting . "','" . $position . "'";
			$sql .= "),";
		}
		
		$sql = substr ( $sql, 0, - 1 );
		
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$this->answer = $this->new_answer;
		$this->correct = $this->new_correct;
		$this->comment = $this->new_comment;
		$this->weighting = $this->new_weighting;
		$this->position = $this->new_position;
		$this->nbrAnswers = $this->new_nbrAnswers;
		$this->cancel ();
	}

	function getAnswer($id) {
		return $this->answer [$id];
	}

	function getAnswerId($id) {
		return $this->answer_id [$id];
	}

}

