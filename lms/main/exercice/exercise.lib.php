<?php
/*
公共包含文件
*/
define ( "QUIZ_TYPE_SM", 1 ); //模拟测验
define ( "QUIZ_TYPE_HW", 2 ); //课后练习

$_question_types = array ('0' => get_lang ( "All" ), UNIQUE_ANSWER => get_lang ( "UniqueSelect" ), MULTIPLE_ANSWER => get_lang ( "MultipleSelect" ), TRUE_FALSE_ANSWER => get_lang ( "TrueFalseAnswer" ),COMBAT_QUESTION=>"实战题" );
if ($_configuration ['enable_question_fillblanks']) $_question_types [FILL_IN_BLANKS] = get_lang ( "FillBlanks" );
if ($_configuration ['enable_question_freeanswer']) $_question_types [FREE_ANSWER] = get_lang ( "FreeAnswer" );
$_question_level = array ('0' => get_lang ( "All" ), '1' => get_lang ( "DifficultyEasier" ), '2' => get_lang ( "DifficultyEasy" ), '3' => get_lang ( "DifficultyNormal" ), '4' => get_lang ( "DifficultyHard" ), '5' => get_lang ( "DifficultyHarder" ) );

$tbl_exam_question_pool = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION_POOL );
$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER );
$tbl_exam_question = $TBL_QUESTIONS = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION ); //exam_question
$tbl_exam_answer = $TBL_REPONSES = Database::get_main_table ( TABLE_MAIN_EXAM_ANSWER ); //exam_answer
$tbl_exam_main = $TBL_EXERCICES = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
$TBL_EXERCICE_QUESTION = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION ); //crs_quiz_rel_question
$TBL_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT ); //crs_document
$tbl_exam_result = $TBL_TRACK_EXERCICES = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES ); //track_e_exercices
$tbl_exam_attempt = $TBL_TRACK_ATTEMPT = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT ); //track_e_attempt
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );

require_once (ROOT_PATH . 'main/exercice/exercise.class.php');
require_once (ROOT_PATH . 'main/exercice/question.class.php');
require_once (ROOT_PATH . 'main/exercice/answer.class.php');
require_once (ROOT_PATH . 'main/exercice/unique_answer.class.php');
require_once (ROOT_PATH . 'main/exercice/multiple_answer.class.php');
require_once (ROOT_PATH . 'main/exercice/true_false_answer.class.php');
require_once (ROOT_PATH . 'main/exercice/fill_blanks.class.php');
require_once (ROOT_PATH . 'main/exercice/freeanswer.class.php');

function api_protect_quiz_script($exam_manager_id) {
	//$is_allowed = FALSE;
	//if (isRoot ()) return TRUE;
	//if (api_is_platform_admin ()) return TRUE;
	//if (api_get_user_id () == $exam_manager_id) $is_allowed = TRUE;
	//if (api_is_allowed_to_edit ()) $is_allowed = TRUE;
	//if (! $is_allowed) api_not_allowed ();
}

/**
 * 判断多选题的正误
 *
 * @param array $corrct_multiple_answers 正确答案
 * @param array $choice 学生提交答案
 * @return unknown
 */
function juge_multiple_judge($corrct_answers, $choice) {
	if (is_array ( $corrct_answers ) and is_array ( $choice )) {
		$is_multiple_judge_correct = true;
		//与$choice对比
		if (sizeof ( $corrct_answers ) != sizeof ( $choice )) { //多选或者少选
			$is_multiple_judge_correct = false;
		} else { //选择个数相等时
			$answer_diff = array_diff ( $choice, $corrct_answers );
			if (is_array ( $answer_diff )) {
				if (sizeof ( $answer_diff ) != 0) {
					$is_multiple_judge_correct = false;
				}
			}
		}
		return $is_multiple_judge_correct;
	}
	return false;
}

/**
 * 显示题目列表
 *
 * @param unknown_type $questionId
 * @param unknown_type $onlyAnswers 是否只显示问题的答案：false全部显示
 * @param unknown_type $origin
 * @return unknown
 */
function showQuestion($questionId, $onlyAnswers = false, $origin = false) {
	global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
	$objQuestionTmp = Question::read ( $questionId );
	if (! $objQuestionTmp) return false;
	
	$answerType = $objQuestionTmp->selectType ();
	$pictureName = $objQuestionTmp->selectPicture ();
	
	//显示题干
	if (! $onlyAnswers) {
		$questionName = $objQuestionTmp->selectTitle ();
		$questionDescription = $objQuestionTmp->selectDescription ();
		$questionName = api_parse_tex ( $questionName );
		$questionDescription = api_parse_tex ( $questionDescription );
		$questionPonderation = $objQuestionTmp->selectWeighting ();
		
		$s = "<tr><td>&nbsp;</td><td valign='top'>&nbsp;" . $questionName . "</td></tr>"; //题干	
		if (! empty ( $pictureName )) {
			$s .= "<tr><td>&nbsp;</td><td align='center' >
				<img src='../document/download.php?doc_url=%2Fimages%2F'" . $pictureName . "' border='0'>
				</td></tr>";
		}
	} // end if(!$onlyAnswers)
	

	//显示答案
	$objAnswerTmp = new Answer ( $questionId );
	$nbrAnswers = $objAnswerTmp->selectNbrAnswers ();
	
	// 匹配题
	if ($answerType == MATCHING) {
		$cpt1 = 'A';
		$cpt2 = 1;
		$Select = array ();
	} 

	//简答题
	elseif ($answerType == FREE_ANSWER) {
		//$comment = $objAnswerTmp->selectComment(1);
		$upload_path = api_get_path ( REL_COURSE_PATH ) . $_SESSION ['_course'] ['path'] . '/document/';
		$oFCKeditor = new FCKeditor ( "choice[" . $questionId . "]" );
		$oFCKeditor->BasePath = api_get_path ( WEB_PATH ) . 'main/inc/lib/fckeditor/';
		$oFCKeditor->Config ['CustomConfigurationsPath'] = api_get_path ( REL_PATH ) . "main/inc/lib/fckeditor/myconfig.js";
		$oFCKeditor->Config ['IMUploadPath'] = 'upload/test/';
		$oFCKeditor->ToolbarSet = "Small";
		$oFCKeditor->Width = '100%';
		$oFCKeditor->Height = '250';
		$oFCKeditor->Value = '';
		
		$TBL_LANGUAGES = Database::get_main_table ( TABLE_MAIN_LANGUAGE );
		$sql = "SELECT isocode FROM " . $TBL_LANGUAGES . " WHERE english_name='" . $_SESSION ["_course"] ["language"] . "'";
		$isocode_language = Database::get_scalar_value ( $sql );
		$oFCKeditor->Config ['DefaultLanguage'] = $isocode_language;
		
		$s .= "<tr><td>&nbsp;</td><td >" . $oFCKeditor->CreateHtml () . "</td></tr>";
	} 

	//完形填空
	elseif ($answerType == CLOZE_QUESTION) {
		
		$s .= "<tr><td>&nbsp;</td><td align='center'><table width=\"90%\">";
		$sql = "SELECT * FROM " . $TBL_QUESTIONS . " WHERE pid=" . Database::escape ( $questionId ) . " ORDER BY position";
		//echo $sql;
		$sub_rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$ii = 1;
		while ( $sub_question_row = Database::fetch_array ( $sub_rs ) ) {
			$subQuestionId = $sub_question_row ['id'];
			$objSubQuestionTmp = Question::read ( $subQuestionId );
			$objSubAnswerTmp = new Answer ( $subQuestionId );
			$nbrSubAnswers = $objSubAnswerTmp->selectNbrAnswers ();
			$subAnswerType = $objSubQuestionTmp->selectType ();
			
			//题干
			$s .= "<tr><td align='left'><div class=\"sectiontitle\">" . Display::return_icon ( "next0.gif" ) . "&nbsp;" . $ii;
			$s .= "&nbsp;" . ($sub_question_row ['ponderation'] >= 0 ? "(" . round ( $sub_question_row ['ponderation'] ) . get_lang ( 'Fen' ) . ")&nbsp;&nbsp;" : "");
			
			$s .= "</div></td></tr>";
			
			//$s.="<tr><td></td><td>".$sub_question_row['description']."</td></tr>";
			//V1.4
			if ($subAnswerType == UNIQUE_ANSWER) {
				$s .= "<tr><td><div id='selectQsnAnswer'><ul>";
				for($answerId = 1; $answerId <= $nbrSubAnswers; $answerId ++) {
					$subAnswer = $objSubAnswerTmp->selectAnswer ( $answerId );
					$subAnswerCorrect = $objSubAnswerTmp->isCorrect ( $answerId );
					
					$s .= "<li><input class='checkbox' type='radio' name='choice[" . $subQuestionId . "]' value='" . $answerId . "'><div style='margin-left:5px;display:inline'>";
					$s .= Question::$alpha [$answerId] . ". <label>" . api_parse_tex ( $subAnswer ) . "</label>";
					$s .= "</div></li>";
				
				}
				$s .= "</ul></div></td></tr>";
			}
			$ii ++;
		}
		$s .= "</table></td></tr>";
	} 

	//综合题
	elseif ($answerType == COMBO_QUESTION) {
		//$TBL_QUESTIONS  = Database::get_course_table(TABLE_QUIZ_QUESTION);
		$s .= " <tr> <td>&nbsp;</td><td> ";
		$s .= api_parse_tex ( $answer );
		$s .= "</td></tr>";
		
		$s .= "<tr><td></td><td align='center'><table width=\"90%\">";
		$sql = "SELECT * FROM " . $TBL_QUESTIONS . " WHERE pid=" . Database::escape ( $questionId ) . " ORDER BY position";
		//echo $sql;
		$sub_rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$sub_question_order = 1;
		while ( $sub_question_row = Database::fetch_array ( $sub_rs, "ASSOC" ) ) {
			$subQuestionId = $sub_question_row ['id'];
			$objSubQuestionTmp = Question::read ( $subQuestionId );
			$objSubAnswerTmp = new Answer ( $subQuestionId );
			$nbrSubAnswers = $objSubAnswerTmp->selectNbrAnswers ();
			$subAnswerType = $objSubQuestionTmp->selectType ();
			
			//子题目题干
			$s .= "<tr><td align='left'><div class=\"sectiontitle\">" . Display::return_icon ( "next0.gif" ) . "&nbsp;" . $sub_question_order . ".&nbsp;&nbsp;" . Question::get_question_type_name ( $subAnswerType );
			$s .= "&nbsp;" . ($sub_question_row ['ponderation'] >= 0 ? "(" . round ( $sub_question_row ['ponderation'] ) . get_lang ( 'Fen' ) . ")&nbsp;&nbsp;" : "");
			$s .= "&nbsp;&nbsp;" . $sub_question_row ['question'];
			$s .= "</div></td></tr>";
			
			//$s.="<tr><td></td><td>".$sub_question_row['description']."</td></tr>";
			//V1.4
			if ($subAnswerType == UNIQUE_ANSWER or $subAnswerType == TRUE_FALSE_ANSWER or $subAnswerType == MULTIPLE_ANSWER) {
				$s .= "<tr><td><div id='selectQsnAnswer'><ul>";
				for($answerId = 1; $answerId <= $nbrSubAnswers; $answerId ++) {
					$subAnswer = $objSubAnswerTmp->selectAnswer ( $answerId );
					$subAnswerCorrect = $objSubAnswerTmp->isCorrect ( $answerId );
					
					// unique answer
					if ($subAnswerType == UNIQUE_ANSWER or $subAnswerType == TRUE_FALSE_ANSWER) {
						$s .= "<li><input class='checkbox' type='radio' name='choice[" . $subQuestionId . "]' value='" . $answerId . "'><div style='margin-left:5px;display:inline'>";
						$s .= Question::$alpha [$answerId] . ". <label >" . api_parse_tex ( $subAnswer ) . "</label>";
						$s .= "</div></li>";
					} 

					//multiple answer...
					elseif ($subAnswerType == MULTIPLE_ANSWER) {
						$s .= "<li><input class='checkbox' type='checkbox' id='q_" . $subQuestionId . "_" . $answerId . "' name='choice[" . $subQuestionId . "][" . $answerId . "]' value='1'>&nbsp;";
						$s .= Question::$alpha [$answerId] . ". <label >" . api_parse_tex ( $subAnswer ) . "</label>";
						$s .= "</li>";
					}
				}
				$s .= "</ul></div></td></tr>";
			} elseif ($subAnswerType == FILL_IN_BLANKS) {
				for($answerId = 1; $answerId <= $nbrSubAnswers; $answerId ++) {
					$answer = $objSubAnswerTmp->selectAnswer ( $answerId );
					$answerCorrect = $objSubAnswerTmp->isCorrect ( $answerId );
					
					list ( $answer ) = explode ( '::', $answer );
					
					$startlocations = strpos ( $answer, '[tex]' );
					$endlocations = strpos ( $answer, '[/tex]' );
					
					if ($startlocations !== false && $endlocations !== false) {
						$texstring = api_substr ( $answer, $startlocations, $endlocations - $startlocations + 6 );
						$answer = str_replace ( $texstring, '{texcode}', $answer );
					}
					$answer = ereg_replace ( '\[[^]]+\]', '<input type="text" name="choice[' . $questionId . '][]" class="inputText">', nl2br ( $answer ) );
					
					$texstring = api_parse_tex ( $texstring );
					$answer = str_replace ( "{texcode}", $texstring, $answer );
					
					$s .= "<tr><td align='left'>$answer</td></tr>";
				}
			} elseif ($subAnswerType == FREE_ANSWER) {
				$upload_path = api_get_path ( REL_COURSE_PATH ) . $_SESSION ['_course'] ['path'] . '/document/';
				$oFCKeditor = new FCKeditor ( "choice[" . $questionId . "]" );
				$oFCKeditor->BasePath = api_get_path ( WEB_PATH ) . 'main/inc/lib/fckeditor/';
				$oFCKeditor->Config ['CustomConfigurationsPath'] = api_get_path ( REL_PATH ) . "main/inc/lib/fckeditor/myconfig.js";
				$oFCKeditor->Config ['IMUploadPath'] = 'upload/test/';
				$oFCKeditor->ToolbarSet = "Small";
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '250';
				$oFCKeditor->Value = '';
				
				$TBL_LANGUAGES = Database::get_main_table ( TABLE_MAIN_LANGUAGE );
				$sql = "SELECT isocode FROM " . $TBL_LANGUAGES . " WHERE english_name='" . $_SESSION ["_course"] ["language"] . "'";
				$oFCKeditor->Config ['DefaultLanguage'] = Database::get_scalar_value ( $sql );
				
				$s .= "<tr><td >" . $oFCKeditor->CreateHtml () . "</td></tr>";
			}
			
			$sub_question_order ++; //一道子题目结束
		}
		$s .= "</table></td></tr>";
	} 

	//V1.4: 选择题
	elseif ($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER or $answerType == MULTIPLE_ANSWER) {
		$s .= "<tr><td>&nbsp;</td><td><div id='selectQsnAnswer'><ul>";
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->selectAnswer ( $answerId );
			$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
			
			if ($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER) {
				$s .= "<li><input class='checkbox' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "'><div style='margin-left:5px;display:inline'>";
				$s .= Question::$alpha [$answerId] . ". <label>" . api_parse_tex ( $answer ) . "</label>";
				$s .= "</div></li>";
			
			} elseif ($answerType == MULTIPLE_ANSWER) {
				$s .= "<li><input class='checkbox' type='checkbox' id='q_" . $questionId . "_" . $answerId . "' name='choice[" . $questionId . "][" . $answerId . "]' value='1'>&nbsp;";
				
				$s .= Question::$alpha [$answerId] . ". <label for='q_" . $questionId . "_" . $answerId . "'>" . api_parse_tex ( $answer ) . "</label>";
				$s .= "</li>";
			}
		}
		$s .= "</ul></div></td></tr>";
	} elseif ($answerType == FILL_IN_BLANKS) {
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->selectAnswer ( $answerId );
			
			// splits text and weightings that are joined with the character '::'
			list ( $answer ) = explode ( '::', $answer );
			
			// because [] is parsed here we follow this procedure:
			// 1. find everything between the [tex] and [/tex] tags
			$startlocations = strpos ( $answer, '[tex]' );
			$endlocations = strpos ( $answer, '[/tex]' );
			
			if ($startlocations !== false && $endlocations !== false) {
				$texstring = api_substr ( $answer, $startlocations, $endlocations - $startlocations + 6 );
				// 2. replace this by {texcode}
				$answer = str_replace ( $texstring, '{texcode}', $answer );
			}
			
			// 3. do the normal matching parsing
			

			// replaces [blank] by an input field
			$answer = ereg_replace ( '\[[^]]+\]', '<input type="text" name="choice[' . $questionId . '][]" class="inputText">', nl2br ( $answer ) );
			// 4. replace the {texcode by the api_pare_tex parsed code}
			$texstring = api_parse_tex ( $texstring );
			$answer = str_replace ( "{texcode}", $texstring, $answer );
			
			$s .= "<tr><td>&nbsp;</td><td >$answer</td></tr>";
		}
	} // end for()
	

	unset ( $objAnswerTmp );
	unset ( $objQuestionTmp );
	
	if ($origin != 'export') {
		echo $s;
	} else {
		return ($s);
	}
	
	echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
	
	return $nbrAnswers;
}

function get_average_score($exerciseId) {
	global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES, $TBL_QUESTIONS;
	global $TBL_TRACK_EXERCICES, $TBL_TRACK_ATTEMPT;
	$sql = "SELECT DISTINCT(exe_user_id) FROM " . $TBL_TRACK_EXERCICES . " WHERE exe_exo_id=" . Database::escape ( $exerciseId );
	$user_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
	$total = $average = 0;
	if ($user_ids && is_array ( $user_ids )) {
		//$user_ids_in=Database::create_in($user_ids,"" );
		foreach ( $user_ids as $user_id ) {
			$sql = "SELECT exe_result FROM $TBL_TRACK_EXERCICES WHERE exe_exo_id=" . Database::escape ( $exerciseId ) . " AND exe_user_id='" . ($user_id) . "' AND status!='incomplete' ORDER BY exe_date DESC LIMIT 1";
			$exe_result = Database::get_scalar_value ( $sql );
			$total += $exe_result;
		}
		return count ( $user_ids ) > 0 ? round ( $total / (count ( $user_ids )), 1 ) : 0;
	}
	return 0;
}

function show_choice_question($answerType, $questionName, $questionPonderation, $userAnswer, $isShowCorrectAnswer = false, $comment = "") {
	global $show_order;
	$s = "";
	$objAnswerTmp = new Answer ( $questionId );
	$nbrAnswers = $objAnswerTmp->selectNbrAnswers ();
	$questionScore = 0;
	
	if ($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER or $answerType == MULTIPLE_ANSWER) {
		$s .= '<div id="question">';
		$s .= '<div class="sectiontitle">' . "&nbsp;" . get_lang ( "Question" ) . ' ' . ($show_order);
		$s .= ":&nbsp;" . Question::get_question_type_name ( $answerType );
		$s .= '<div >(' . ($questionPonderation) . get_lang ( 'Fen' ) . ')</div>';
		$s .= '</div>';
		$s .= '<div id="qstnName">' . $questionName . '</div>';
		
		$s .= '<div id="qstnAnswer">';
		$s .= "<div id='selectQsnAnswer'><ul>";
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->selectAnswer ( $answerId );
			$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
			
			if ($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER) {
				$s .= "<li><input class='checkbox' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "'>";
				$s .= "<div style='margin-left:5px;display:inline'>";
				$s .= Question::$alpha [$answerId] . ". " . api_parse_tex ( $answer );
				$s .= "</div></li>";
			} 

			elseif ($answerType == MULTIPLE_ANSWER) {
				$s .= "<li><input class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1'>&nbsp;";
				$s .= "<div style='margin-left:5px;display:inline'>";
				$s .= Question::$alpha [$answerId] . ". " . api_parse_tex ( $answer );
				$s .= "</div></li>";
			}
		}
		$s .= "</ul></div>";
		$s .= '</div>';
		$s .= '</div>' . "\r\n";
		
		if ($userAnswer) {
			$s .= '<div id="correctAnswer">' . get_lang ( "UserAsnwer" ) . ": " . $userAnswer . '</div>';
		}
		
		if ($isShowCorrectAnswer) {
			$s .= '<div id="correctAnswer">' . get_lang ( "StandardAsnwer" ) . ": " . $objAnswerTmp->correctAnswer . '</div>';
		}
	}
	return $s;
}

function show_fill_in_blanks_question($answerType, $questionName, $questionPonderation, $userAnswer, $isShowCorrectAnswer = false, $comment = "") {
	$s = "";
	$objAnswerTmp = new Answer ( $questionId );
	$nbrAnswers = $objAnswerTmp->selectNbrAnswers ();
	$questionScore = 0;
	if ($answerType == FILL_IN_BLANKS) {
		$s .= '<div id="question">';
		$s .= '<div class="sectiontitle">' . "&nbsp;" . get_lang ( "Question" ) . ' ' . ($idx) . '</div>';
		$s .= '<div id="qstnName">' . $questionName . '</div>';
		
		$s .= '<div id="qstnAnswer">';
		for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
			$answer = $objAnswerTmp->selectAnswer ( $answerId );
			
			// splits text and weightings that are joined with the character '::'
			list ( $answer ) = explode ( '::', $answer );
			
			// because [] is parsed here we follow this procedure:
			// 1. find everything between the [tex] and [/tex] tags
			$startlocations = strpos ( $answer, '[tex]' );
			$endlocations = strpos ( $answer, '[/tex]' );
			
			if ($startlocations !== false && $endlocations !== false) {
				$texstring = api_substr ( $answer, $startlocations, $endlocations - $startlocations + 6 );
				// 2. replace this by {texcode}
				$answer = str_replace ( $texstring, '{texcode}', $answer );
			}
			
			// 3. do the normal matching parsing
			

			// replaces [blank] by an input field
			$answer = ereg_replace ( '\[[^]]+\]', '<input type="text" name="choice[' . $questionId . '][]" class="inputText">', nl2br ( $answer ) );
			// 4. replace the {texcode by the api_pare_tex parsed code}
			$texstring = api_parse_tex ( $texstring );
			$answer = str_replace ( "{texcode}", $texstring, $answer );
			
			$s .= $answer;
		}
		
		$s .= '</div>';
		$s .= '</div>' . "\r\n";
		
		if ($userAnswer) {
			$s .= '<div id="correctAnswer">' . get_lang ( "UserAsnwer" ) . ": " . $userAnswer . '</div>';
		}
		
		if ($isShowCorrectAnswer) {
			$s .= '<div id="correctAnswer">' . get_lang ( "StandardAsnwer" ) . ": " . $objAnswerTmp->correctAnswer . '</div>';
		}
	}
	return $s;
}

function get_fill_blank_answer_array($str) {
	$all_match = array ();
	do {
		preg_match ( "/\\[\\W+\\]/", $str, $match );
		$str = $match [0];
		$str_match = substr ( $str, 0, strpos ( $str, "]" ) + 1 );
		($str_match) && $all_match [] = $str_match;
		$str = ltrim ( $str, $str_match );
	} while ( ! empty ( $match ) );
	return $all_match;
}

function get_exam_user_list($exam_id, $sqlwhere = "") {
	global $tbl_exam_rel_user;
	$tbl_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT t1.*,t1.user_id,t2.username,t2.firstname,t2.lastname,t2.dept_name,t2.org_name FROM " . $tbl_exam_rel_user . " AS t1 LEFT JOIN " . $tbl_user_dept . " AS t2 ON t1.user_id=t2.user_id  WHERE t1.exam_id=" . Database::escape ( $exam_id );
	if ($sqlwhere) $sql .= " " . $sqlwhere;
	//echo $sql;
	$rs = Database::query ( $sql, __FILE__, __LINE__ );
	$row = api_store_result_array ( $rs );
	return $row;
}

function disp_manage_tab($curr, $url_param) {
	//$myTools ['manage/have_arranged'] = array (get_lang ( 'ArrageExaminees' ), 'show_test_results.gif' );
	$myTools ['manage/tobe_corrected'] = array (get_lang ( 'ExamineesToBeCorrected' ), 'quiz.gif' );
	
	$html .= '<ul class="yui-nav">';
	foreach ( $myTools as $key => $value ) {
		$strClass = ($curr == $key ? 'class="selected"' : '');
		$html .= '<li  ' . $strClass . '><a href="' . api_add_url_param ( api_get_path ( WEB_CODE_PATH ) . 'exam/' . $key . '.php', $url_param ) . '"><em>' . $value [0] . '</em></a></li>';
	}
	$html .= '</ul>';
	return $html;
}

/**
 * 选择题/判断题的显示
 * @param $answerType
 * @param $studentChoice
 * @param $answer
 * @param $answerComment
 * @param $answerCorrect
 * @return unknown_type
 */
function display_unique_or_multiple_answer($answerType, $studentChoice, $answer, $answerCorrect) {
	$file_name1 = '';
	$file_name2 = '';
	if ($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER) {
		$file_name1 = 'radio';
		$file_name2 = 'radio';
	} else {
		$file_name1 = 'checkbox';
		$file_name2 = 'checkbox';
	}
	
	$file_name1 .= ($studentChoice ? '_on' : '_off');
	$file_name2 .= ($answerCorrect ? '_on' : '_off');
	
	$html = '<tr>';
	$html .= '<td>' . Display::return_icon ( $file_name1 . '.gif' ) . '</td>';
	$html .= '<td>' . Display::return_icon ( $file_name2 . '.gif' ) . '</td><td class="tbl_answer">' . api_parse_tex ( $answer ) . '</td></tr>';
	echo $html;
}

function display_question_kgt($answerType, $answer, $answerCorrect, $questionIdx, $answerId) {
	$input_type = (($answerType == UNIQUE_ANSWER or $answerType == TRUE_FALSE_ANSWER) ? 'radio' : 'checkbox');
	$html = '<li>';
	$html .= '<span style=""><input type="' . $input_type . '" id="q_' . $questionIdx . '" ' . ($answerCorrect ? ' checked' : '') . '/></span>';
	$html .= '<span style="padding-left:10px"><label	for="q_' . $questionIdx . '">' . Question::$alpha [$answerId] . '. ' . $answer . '</label></span></li>';
	echo $html;
}

function display_fill_blank_answer($choice, $correctAnswer, $questionPonderation, $isAllowedToSeeAnswer, $isAllowedToSeePaper) {
	$nb = preg_match_all ( '/\[[^\]]*\]/', $correctAnswer, $blanks );
	$correctAnswerArray = $blanks [0];
	$answer_txt = implode ( ' ', $correctAnswerArray );
	$blankPonderation = (count ( $correctAnswerArray ) != 0 ? round ( $questionPonderation / count ( $correctAnswerArray ), 1 ) : 0);
	$temp = $correctAnswer;
	$answer = '<div style="padding-left:20px">';
	foreach ( $correctAnswerArray as $j => $answerContent ) {
		$answerContent = trim ( $answerContent );
		$answerContent = api_substr ( $answerContent, 1, api_strlen ( $answerContent ) - 2 );
		$answer_contents = explode ( '&', $answerContent );
		$choice [$j] = trim ( $choice [$j] );
		//if (strtolower ( $answerContent ) == stripslashes ( strtolower ( $choice [$j] ) )) {
		if (in_array ( stripslashes ( strtolower ( $choice [$j] ) ), $answer_contents )) {
			$questionScore += $blankPonderation;
			$answer .= ($isAllowedToSeePaper ? ' <div>' . stripslashes ( $choice [$j] ) : '');
		} elseif (! empty ( $choice [$j] )) {
			$answer .= ($isAllowedToSeePaper ? ' <div><font color="red"><s>' . stripslashes ( $choice [$j] ) . '</s></font>' : '');
		} else {
			$answer .= '<li>&nbsp;&nbsp;&nbsp;';
		}
		if ($isAllowedToSeeAnswer) {
			$answer .= ' / <font color="green"><b>' . $answerContent . '</b></font>&nbsp;';
		}
		$answer .= '</div>';
	}
	$answer .= "</div>";
	
	return array ("html" => $answer, "score" => $questionScore );
}
