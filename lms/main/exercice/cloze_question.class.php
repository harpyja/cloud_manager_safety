<?php

/**
 *	File containing the UNIQUE_ANSWER class.
 *	@package zllms.exercise
 * 	@author Eric Marguin
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */

if(!class_exists('ClozeQuestion')):
include_once('question.class.php');
/**
 CLASS CLOZE_QUESTION
 *
 *	This class allows to instantiate an object of type UNIQUE_ANSWER (MULTIPLE CHOICE, UNIQUE ANSWER),
 *	extending the class question
 *
 *	@author Eric Marguin
 *	@package zllms.exercise
 **/
class ClozeQuestion extends Question {

	static $typePicture = 'hotspot.gif';
	static $explanationLangVar = 'ClozeQuestion';

	/**
	 * Constructor
	 */
	function ClozeQuestion(){
		parent::Question();
		$this -> type = CLOZE_QUESTION;
	}

	/**
	 * function which redifines Question::createAnswersForm
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function createAnswersForm ($form) {


	}


	/**
	 * abstract function which creates the form to create / edit the answers of the question
	 * @param the formvalidator instance
	 * @param the answers number to display
	 */
	function processAnswersCreation($form) {
		$this->pid=0;
		$this->save();
	}

	function move_up($pid,$id,$position){
		global $TBL_QUESTIONS;
		$sql="SELECT id,position FROM $TBL_QUESTIONS WHERE  position<".Database::escape($position)
		." AND pid=".Database::escape($pid);
		 $sql .=" AND cc='".api_get_course_code()."' ";
		$sql.=" ORDER BY position DESC LIMIT 1";
		$result=api_sql_query($sql,__FILE__,__LINE__);

		if($row=Database::fetch_array($result,'ASSOC')){
			//交换位置
			$sql="UPDATE $TBL_QUESTIONS SET position='".$row['position']."' WHERE id=".Database::escape($id);
			 $sql .=" AND cc='".api_get_course_code()."' ";
			api_sql_query($sql,__FILE__,__LINE__);

			$sql="UPDATE $TBL_QUESTIONS SET position=".Database::escape($position)." WHERE id='".$row['id']."'";
			$sql .=" AND cc='".api_get_course_code()."' ";
			api_sql_query($sql,__FILE__,__LINE__);

			return true;
		}
		return false;
	}

	function move_down($pid,$id,$position){
		global $TBL_QUESTIONS;
		$sql="SELECT id,position FROM $TBL_QUESTIONS WHERE  position>".Database::escape($position)
		." AND pid=".Database::escape($pid);
		$sql .=" AND cc='".api_get_course_code()."' ";
		$sql.=" ORDER BY position ASC LIMIT 1";
		$result=api_sql_query($sql,__FILE__,__LINE__);

		if($row=Database::fetch_array($result,'ASSOC')){
			//交换位置
			$sql="UPDATE $TBL_QUESTIONS SET position='".$row['position']."' WHERE id=".Database::escape($id);
			$sql .=" AND cc='".api_get_course_code()."' ";
			api_sql_query($sql,__FILE__,__LINE__);

			$sql="UPDATE $TBL_QUESTIONS SET position=".Database::escape($position)." WHERE id='".$row['id']."'";
			$sql .=" AND cc='".api_get_course_code()."' ";
			api_sql_query($sql,__FILE__,__LINE__);

			return true;
		}
		return false;
	}

}
endif;
?>