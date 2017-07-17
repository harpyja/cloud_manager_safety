<?php
/**
 * 单个学生的课程相关统计信息
 */

$language_file = array ('registration', 'index', 'tracking', 'exercice', 'admin' );
include_once ('../inc/global.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . SCORM_PATH . "learnpath.class.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');

api_block_anonymous_users ();

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_stats_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
$tbl_stats_exercices_attempts = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
$tbl_course_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_course_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
$tbl_course_lp_view_item = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
$tbl_course_lp_view = Database::get_course_table ( TABLE_LP_VIEW );

$g_action=  intval(getgpc('user_id'));
$g_course_code=  getgpc('course_code');
$stat_user_id = (is_not_blank ( $g_action ) ? $g_action : $_user ['user_id']);
$stat_course_code = (is_not_blank ( $g_course_code ) ? $g_course_code : api_get_course_code ());
$user_info = UserManager::get_user_info_by_id ( $stat_user_id );

$g_user_id= intval( getgpc('user_id'));
$interbreadcrumb [] = array ("url" => "user_learning_stat.php?user_id=" . $g_user_id, "name" => get_lang ( 'MySpace' ) );
//$interbreadcrumb[] = array ("url" => "user_learning_stat.php?user_id=".$_GET['user_id'], "name" => $user_name);


$nameTools = $user_info ["firstname"] . "(" . $user_info ["username"] . ")";
Display::display_header ( $nameTools, FALSE );

$myTools ['scorm'] = array (get_lang ( 'SCORMProgress' ), 'kcmdf_big_small.gif' );
$myTools ['quiz'] = array (get_lang ( 'QuizStatInfo' ), 'quiz.gif' );
$myTools ['other'] = array (get_lang ( 'OtherStatInfo' ), 'acces_tool.gif' );
$total_tag_cnt = count ( $myTools );
$width_percent = round ( (101 - $total_tag_cnt) / ($total_tag_cnt + 2) ) . "%";
$g_tabaction=  getgpc('tabAction');
$strActionType = (isset ( $g_tabaction ) ? $g_tabaction : 'scorm');
echo '<table width="100%"><tr><td align="center"><table width="95%" class="tabTable"><tr>' . "\n";
echo '<td width="' . $width_percent . '" class="tabOther"  height="24">&nbsp;</td>' . "\n";
$uri = api_add_url_param ( $_SERVER ["REQUEST_URI"], "" );
foreach ( $myTools as $key => $value ) {
	$strClass = ($strActionType == $key ? 'tabSelected' : 'tabUnSelected');
	echo '<td width="' . $width_percent . '" class="' . $strClass . '" valign="bottom"><a href="' . (api_add_url_param ( $_SERVER ["REQUEST_URI"], "tabAction=" . $key )) . '">' . Display::return_icon ( $value [1] ) . "&nbsp;&nbsp;" . $value [0] . "</a></td>\n";
	echo '<td width="1%" class="tabOther">&nbsp;</td>' . "\n";
}
echo '<td class="tabOther">&nbsp;</td>' . "\n";
echo '</tr></table></td></tr></table><br>' . "\n";

if (is_equal ( $strActionType, "scorm" )) {
	$table_header [] = array (get_lang ( "LearningOrder" ), null, array ('width' => '60' ) );
	$table_header [] = array (get_lang ( 'Name' ) );
	$table_header [] = array (get_lang ( 'Progress' ), null, array ('width' => '186' ) );
	//$table_header [] = array (get_lang ( 'SCORMQuizAvgScore' ), null, array ('width' => '140' ) );
	$table_header [] = array (get_lang ( 'SCORMLearningTotalTime' ), null, array ('width' => '100' ) );
	$table_header [] = array (get_lang ( 'SCORMLastAccess' ), null, array ('width' => '160' ) );
	$table_header [] = array (get_lang ( 'Details' ), null, array ('width' => '60' ) );
	
	$sqlLearnpath = "SELECT lp.name,lp.id,learning_order FROM $tbl_course_lp AS lp ";
	$sqlLearnpath .= " WHERE cc='" . escape ( $stat_course_code ) . "' ";
	$sqlLearnpath .= " ORDER BY learning_order";
	$resultLearnpath = api_sql_query ( $sqlLearnpath, __FILE__, __LINE__ );
	if (Database::num_rows ( $resultLearnpath ) > 0) {
		$i = 0;
		while ( $a_learnpath = Database::fetch_array ( $resultLearnpath, "ASSOC" ) ) {
			$row = array ();
			$row [] = $a_learnpath ["learning_order"];
			$row [] = stripslashes ( $a_learnpath ['name'] );
			
			$progress = learnpath::get_db_progress ( $a_learnpath ['id'], $stat_user_id );
			$row [] = learnpath::get_progress_bar ( '', $progress, "" );
			
			//课件内测验平均得分
			/*$sql = 'SELECT id as item_id, max_score FROM ' . $tbl_course_lp_item . ' AS lp_item
							WHERE lp_id=' . $a_learnpath ['id'] . ' AND item_type="quiz"';
			 $sql .= " AND lp_item.cc='" . escape ( $stat_course_code ) . "' ";
			$rsItems = api_sql_query ( $sql, __FILE__, __LINE__ );
			$total_score = $total_weighting = 0;
			while ( $item = Database::fetch_array ( $rsItems, 'ASSOC' ) ) {
				$sql = 'SELECT score as student_score FROM ' . $tbl_course_lp_view . ' as lp_view LEFT JOIN ' . $tbl_course_lp_view_item . ' as lp_view_item ON lp_view.id = lp_view_item.lp_view_id
					AND lp_view_item.lp_item_id = ' . $item ['item_id'] . "
					WHERE lp_view.user_id = '" . escape ( $stat_user_id ) . "' AND lp_ivew.lp_id='" . $a_learnpath ['id'] . "'";
				$sql .= " AND lp_view_item.cc='" . getgpc ( "course" ) . "' ";
				$total_score += Database::get_scalar_value ( $sql );
				$total_weighting += $item ['max_score'];
			}
			$row [] = round ( $total_score / $total_weighting * 100, 2 );*/
			
			//课件学习时间
			$sql = 'SELECT SUM(total_time)	FROM ' . $tbl_course_lp_view_item . ' AS item_view
					INNER JOIN ' . $tbl_course_lp_view . " AS view	ON item_view.lp_view_id = view.id
					AND view.lp_id = '" . $a_learnpath ['id'] . "'	AND view.user_id = '" . escape ( $stat_user_id ) . "'";
			$sql .= " WHERE item_view.cc='" . escape ( $stat_course_code ) . "' ";
			$total_time = Database::get_scalar_value ( $sql );
			$row [] = api_time_to_hms ( $total_time );
			
			$sql = 'SELECT MAX(start_time) FROM ' . $tbl_course_lp_view_item . ' AS item_view
					 INNER JOIN ' . $tbl_course_lp_view . " AS view ON item_view.lp_view_id = view.id
					 AND view.lp_id = '" . $a_learnpath ['id'] . "' AND view.user_id = '" . escape ( $stat_user_id ) . "'";
			$sql .= " WHERE item_view.cc='" . escape ( $stat_course_code ) . "' ";
			$start_time = Database::get_scalar_value ( $sql );
			$row [] = (empty ( $start_time ) ? "-" : strftime ( '%Y-%m-%d', $start_time ));
			
                        $g_origin=  getgpc('origin');
			if (($progress > 0 || $score > 0)) {
				$row [] = '<a target="_blank" href="lp_tracking.php?course=' . $stat_course_code . '&origin=' . $g_origin . '&lp_id=' . $a_learnpath ['id'] . '&student_id=' . $stat_user_id . '">' . Display::return_icon ( '2rightarrow.gif' ) . '</a>';
			}
			
			$table_data [] = $row;
		}
		$query_vars ['tabAction'] = 'scorm';
		$sorting_options = array ();
		Display::display_non_sortable_table ( $table_header, $table_data, array (), $query_vars, array (), NAV_BAR_BOTTOM );
	}
}
if (is_equal ( $strActionType, "quiz" )) {
	$table_header [] = array (get_lang ( "Exercices" ) );
	$table_header [] = array (get_lang ( 'Attempts' ) );
	$table_header [] = array (get_lang ( 'MaxScore' ) );
	$table_header [] = array (get_lang ( 'MinScore' ) );
	$table_header [] = array (get_lang ( 'AverageScore' ) );
	$table_header [] = array (get_lang ( 'LastScore' ) );
	$table_header [] = array (get_lang ( 'LastAttempDate' ) );
	//$table_header[] = array(get_lang('CorrectTest'));
	$tbl_course_quiz = Database::get_course_table ( 'quiz', $a_infosCours ['db_name'] );
	
	$sqlExercices = "SELECT quiz.title,id FROM " . $tbl_course_quiz . " AS quiz";
	$sqlExercices .= " WHERE cc='" . escape ( $stat_course_code ) . "' ";
	$resultExercices = api_sql_query ( $sqlExercices, __FILE__, __LINE__ );
	$i = 0;
	if (Database::num_rows ( $resultExercices ) > 0) {
		while ( $a_exercices = Database::fetch_array ( $resultExercices, "ASSOC" ) ) {
			$row = array ();
			$sqlEssais = "SELECT COUNT(ex.exe_id) as essais FROM $tbl_stats_exercices AS ex
								WHERE  ex.exe_cours_id = '" . escape ( $stat_course_code ) . "'
								AND ex.exe_exo_id = " . $a_exercices ['id'] . "
								AND exe_user_id='" . escape ( $stat_user_id ) . "'";
			$resultEssais = api_sql_query ( $sqlEssais, __FILE__, __LINE__ );
			$a_essais = mysql_fetch_array ( $resultEssais );
			$row [] = $a_exercices ['title'];
			
			$row [] = $a_essais ['essais'];
			
			$sql = "SELECT MAX(exe_result),MIN(exe_result),AVG(exe_result) FROM $tbl_stats_exercices AS ex
						WHERE  ex.exe_cours_id = '" . escape ( $stat_course_code ) . "'
						AND ex.exe_exo_id = " . $a_exercices ['id'] . "	AND exe_user_id='" . escape ( $stat_user_id ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			list ( $max_score, $min_score, $avg_score ) = Database::fetch_row ( $res );
			/*$max_score=Database::result($res,0,0);
			 $min_score=Database::result($res,0,1);
			 $avg_score=Database::result($res,0,2);*/
			
			$sqlScore = "SELECT exe_id, exe_result,exe_weighting,exe_date
								 FROM $tbl_stats_exercices
								 WHERE exe_user_id = " . escape ( $stat_user_id ) . "
								 AND exe_cours_id = '" . escape ( $stat_course_code ) . "'
								 AND exe_exo_id = " . $a_exercices ['id'] . "
								 ORDER BY exe_date DESC LIMIT 1";
			$resultScore = api_sql_query ( $sqlScore, __FILE__, __LINE__ );
			$score = 0;
			while ( $a_score = mysql_fetch_array ( $resultScore ) ) {
				$score = $score + $a_score ['exe_result'];
				$weighting = $weighting + $a_score ['exe_weighting'];
				$exe_id = $a_score ['exe_id'];
				$exe_date = $a_score ['exe_date'];
			}
			
			$pourcentageMaxScore = (round ( $max_score / $weighting, 4 ) * 100) . ' %';
			$pourcentageMinScore = (round ( $min_score / $weighting, 4 ) * 100) . ' %';
			$pourcentageAvgScore = (round ( $avg_score / $weighting, 4 ) * 100) . ' %';
			$pourcentageScore = (round ( $score / $weighting, 4 ) * 100) . ' %';
			
			$weighting = 0;
			
			$row [] = $pourcentageMaxScore;
			$row [] = $pourcentageMinScore;
			$row [] = $pourcentageAvgScore;
			$row [] = $pourcentageScore;
			$row [] = $exe_date;
			
                        $g_student=  getgpc('student');
			$sql_last_attempt = 'SELECT exe_id FROM ' . $tbl_stats_exercices . ' WHERE exe_exo_id="' . $a_exercices ['id'] . '" AND exe_user_id="' . $g_student . '" AND exe_cours_id="' . $a_infosCours ['code'] . '" ORDER BY exe_date DESC LIMIT 1';
			$id_last_attempt = Database::get_scalar_value ( $sql_last_attempt );
			if ($a_essais ['essais'] > 0) {
				//$row[]='<a href="../exercice/exercise_show.php?id='.$id_last_attempt.'&cidReq='.$a_infosCours['code'].'&student='.$_GET['student'].'&origin='.(empty($_GET['origin']) ? 'tracking' : $_GET['origin']).'">' . Display::return_icon('quiz.gif') . ' </a>';
			} else {
				//$row[]="";
			}
			
			$table_data [] = $row;
		}
		$query_vars ['tabAction'] = 'quiz';
		$sorting_options = array ();
		Display::display_non_sortable_table ( $table_header, $table_data, array (), $query_vars, array (), NAV_BAR_BOTTOM );
	}
}

if (is_equal ( $strActionType, "other" )) {
	$objStat = new ScormTrackStat ();
	
	echo get_lang ( "PlatformStat" ) . ":<br/>";
	// 用户是否在线 ?
	$statistics_database = Database::get_statistic_database ();
        $g_student=  getgpc('student');
	$a_usersOnline = WhoIsOnline ( $g_student, $statistics_database, 30 );
	$online = get_lang ( 'No' );
	if (is_array ( $a_usersOnline )) {
		foreach ( $a_usersOnline as $a_online ) {
			if ($g_student == $a_online [0]) {
				$online = get_lang ( 'Yes' );
				break;
			}
		}
	}
	//最后登录时间
	$last_connection_date = Tracking::get_last_connection_date ( $stat_user_id );
	if ($last_connection_date == '') {
		$last_connection_date = get_lang ( 'NoConnexion' );
	}
	
	//平台总的学习时间
	$seconds_on_the_platform = Tracking::get_time_spent_on_the_platform ( $stat_user_id );
	$time_spent_on_the_platform = api_time_to_hms ( $seconds_on_the_platform );
	
	$table_header [] = array (get_lang ( 'OnLine' ) );
	$table_header [] = array (get_lang ( "LastConnexion" ) );
	$table_header [] = array (get_lang ( 'TimeSpentOnThePlatform' ) );
	$table_header [] = array (get_lang ( 'AvgProgress' ) );
	$table_header [] = array (get_lang ( 'AvgScore' ) );
	Display::display_complex_table_header ( $properties, $table_header );
	$avg_student_progress = $objStat->get_avg_student_progress ( $stat_user_id, $stat_course_code );
	$avg_student_score = $objStat->get_avg_student_score ( $stat_user_id, $stat_course_code );
	$row = array ($online, $last_connection_date, $time_spent_on_the_platform, round ( $avg_student_progress, 1 ) . ' %', $avg_student_score . ' %' );
	Display::display_alternating_table_row ( $row );
	Display::display_table_footer ();
	echo "<br/><br/>";
	
	unset ( $table_header );
	$table_header [] = array (get_lang ( "LearningTotalTime" ) );
	$table_header [] = array (get_lang ( 'SCORMLastAccess' ) );
	$table_header [] = array (get_lang ( 'LearningProgress' ) );
	$table_header [] = array (get_lang ( 'QuizAvgScore' ) );
	Display::display_complex_table_header ( $properties, $table_header );
	
	$progress = round ( $objStat->get_avg_student_progress ( $stat_user_id, $stat_course_code ), 1 );
	$avg_score = round ( $objStat->get_avg_student_score ( $stat_user_id, $stat_course_code ), 2 );
	$row = array (api_time_to_hms ( $objStat->get_total_learning_time ( $stat_user_id, $stat_course_code ) ), $objStat->get_last_learning_time ( $stat_user_id, $stat_course_code ), learnpath::get_progress_bar ( '', $progress, "" ), $avg_score );
	Display::display_alternating_table_row ( $row );
	Display::display_table_footer ();
	echo "<br/><br/>";

}
Display::display_footer ();
?>