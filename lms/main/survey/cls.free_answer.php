<?php
/*
    问答题
*/

/**
 * File containing the FreeAnswer class.
 * This class allows to instantiate an object of type FREE_ANSWER,
 * extending the class question
 * @package zllms.exercise
 * @author Eric Marguin
 * @version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */

if (! class_exists ( 'FreeAnswer' )) :

	class FreeAnswer extends Question {
		
		static $typePicture = 'open_answer.gif';
		static $explanationLangVar = 'freeAnswer';

		/**
		 * Constructor
		 */
		function FreeAnswer() {
			parent::question ();
			$this->type = FREE_ANSWER;
		}

		/**
		 * function which redifines Question::createAnswersForm
		 * @param the formvalidator instance
		 */
		function createAnswersForm($form) {
			
			//$form -> addElement('text','weighting',get_lang('Weighting'),array('class'=>'inputText','style'=>"width:8%;text-align:right"));
			if (! empty ( $this->id )) {
				$form->setDefaults ( array ('questionScore' => $this->weighting ) );
			} else {
				$form->setDefaults ( array ('questionScore' => '1' ) );
			}
		
		}

		/**
		 * abstract function which creates the form to create / edit the answers of the question
		 * @param the formvalidator instance
		 */
		function processAnswersCreation($form) {
			$this->save ();
		}

		function display_question($questionId, $questionName, $seq, $option_display_type = 1) {
			$html = '<div class="exam_problem dd7">';
			if ($option_display_type == 1) { //垂直显示
				$html .= '<div><b>' . $seq . "</b>、" . $questionName . '</div>';
				$html .= '<div style="height: 9px; overflow: hidden;"></div>
					<div style="margin-left: 10px;">';
				$html .= '<textarea id="q_' . $seq . '" name="choice[' . $questionId . ']" style="width:99%;height:100px;margin-bottom:5px"></textarea>';
				$html .= '</div>';
			} else {
				$html .= '<div style="width:100%"><div style="float:left;"><b>' . $seq . "</b>、 " . $questionName . '</div>';
				$html .= '<div style="float:right;">';
				$html .= '<textarea name="choice[' . $questionId . ']" id="q_' . $seq . '" style="width:500px;height:100px;margin-bottom:5px"></textarea>';
				$html .= '</div></div>';
			}
			$html .= '<input type="hidden" id="qt_' . $seq . '" value="' . FREE_ANSWER . '" />';
			$html .= '<div class="clearall"></div></div>';
			$html .= '<div class="clearall"></div>';
			return $html;
		}

		static function save_result($survey_id, $user_id, $questionId, $examResult) {
			global $tbl_survey_question_option, $_configuration;
			$choice = $examResult [$questionId]; //为 选项顺序值
			$answer = $choice;
			if (! empty ( $answer )) {
				SurveyManager::save_survey_submit_question ( $survey_id, $user_id, $questionId, $answer, 0 );
			}
		}

		static function display_result($questionId, $questionName, $seq, $examResult) {
			$choice = $examResult [$questionId];
			$html = '<div class="exam_problem dd7">';
			
			$html .= '<div style="height: auto; border-right: 0 dashed #c3c3c3; float: left; width: 100%; padding: 10px 0;">';
			$html .= '<div><b>' . $seq . "</b>、 " . $questionName . '</div>';
			$html .= '<div style="height: 9px; overflow: hidden;"></div>';
			$html .= '<div style="width:100%">' . $choice . '</div>';
			$html .= '<div class="clearall"></div></div>';
			
			$html .= '<div class="clearall"></div></div>';
			return array ("html" => $html );
		}

		static function display_stat_result($questionId, $questionName, $seq) {
			global $tbl_survey_answer;
			$tbl_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
			
			$html = '<div class="exam_problem dd7">';
			
			$html .= '<div style="height: auto; border-right: 0 dashed #c3c3c3; float: left; width: 100%; padding: 10px 0;">';
			$html .= '<div><b>' . $seq . "</b>、 " . $questionName . '</div>';
			$html .= '<div style="height: 9px; overflow: hidden;"></div>';
			$html .= '<div style="width:100%">
			<table class="tbl_exam_options" style="width:100%;text-align: center; margin-top: 10px;" cellspacing="0">
		<tr><th>用户</th><th>'.get_lang('InOrg').'</th><th>'.get_lang('InDept').'</th><th>内容</th></tr>';
			$sql = "SELECT t2.firstname,t2.org_name,t2.dept_name,t1.option_id FROM $tbl_survey_answer AS t1, 
				$tbl_user_dept AS t2 WHERE t1.question_id=" . Database::escape ( $questionId ) . " AND t1.user_id=t2.user_id";
			
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$html .= '<tr><td>' . $row ['firstname'] . '</td>
        				<td>' . $row ['org_name'] . '</td>
        				<td>' . $row ['dept_name'] . '</td>
        				<td style="text-align:left;padding-left:8px">' . $row ['option_id'] . '</td>
        				</tr>';
			}
			$html .= '</table>';
			$html .= '<div class="clearall"></div>';
			$html .= '</div>';
			$html .= '<div class="clearall"></div></div>';
			$html .= '<div class="clearall"></div></div>';
			return array ("html" => $html );
		}
	
	}

endif;
