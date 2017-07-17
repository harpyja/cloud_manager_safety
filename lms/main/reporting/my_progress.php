<?php
$language_file = array ('registration', 'tracking' );

$cidReset = true;

require ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');

$nameTools = get_lang ( 'MyProgress' );

api_block_anonymous_users ();

$this_section = SECTION_PROGRESS;

$htmlHeadXtra [] = Display::display_thickbox();

Display::display_header ( $nameTools );

// Database table definitions
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );

$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$tbl_stats_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
$tbl_course_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
$tbl_course_lp_view_item = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
$tbl_course_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_course_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
$tbl_course_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );

$sql = "SELECT DISTINCT code,title, db_name	FROM $tbl_course AS t1
			LEFT JOIN $tbl_course_user AS t2 ON t2.course_code = t1.code
			WHERE t2.user_id = '" . api_get_user_id () . "' ORDER BY creation_time DESC";
//echo $sql;
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$Courses = api_store_result ( $result );

$now = date ( 'Y-m-d' );

display_my_courses_list ( api_get_user_id () );

function display_my_courses_list($user_id) {
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	$table_header [] = array (get_lang ( 'Course' ), true );
	$table_header [] = array (get_lang ( 'LearningStatus' ), true );
	$table_header [] = array (get_lang ( 'LearningTime' ), true );
	$table_header [] = array (get_lang ( 'AvgStudentsProgress' ), true );
	$table_header [] = array (get_lang ( 'QuizAvgScore' ), true );
	$table_header [] = array (get_lang ( 'Details' ), false );
	
	$i = 0;
	$totalWeighting = 0;
	$totalScore = 0;
	$totalItem = 0;
	$totalProgress = 0;
	
	$sql = "SELECT DISTINCT code,title,is_pass,visibility,t2.status,t2.tutor_id,t2.is_course_admin,directory,
			IF(UNIX_TIMESTAMP(t1.expiration_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_expired,
			IF(UNIX_TIMESTAMP(t1.start_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_started
			FROM $tbl_course AS t1 LEFT JOIN $tbl_course_user AS t2 ON t2.course_code = t1.code
			WHERE t2.user_id = '" . api_get_user_id () . "' ORDER BY creation_time DESC";
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $data = Database::fetch_array ( $result, "ASSOC" ) ) {
		$course_code = $data ['code'];
		$row = array ();
		
		$title = "";
		$course_visibility = $data ['visibility'];
		$user_in_course_status = $data ["status"]; //教师还是学生
		if ($course_visibility != COURSE_VISIBILITY_CLOSED || $user_in_course_status == COURSEMANAGER) {
			if (($data ['is_course_started'] and ! $data ['is_course_expired']) or ($data ['tutor_id'] or $data ['is_course_admin'] or api_is_platform_admin ())) {
				$title .= '<a href="' . api_get_path ( WEB_COURSE_PATH ) . $data ["directory"] . '/">' . $data ["title"] . '</a>';
			} else {
				$title .= '<a href="javascript:void(0);" disabled="true">' . $data ["title"] . '</a>';
			}
			
			if (! $course ['is_course_started']) {
				$state .= get_lang ( 'CourseNotStarted' );
			}
			
			if ($course ['is_course_expired']) {
				$state .= get_lang ( 'CourseExpired' );
			}
		} else {
			$title .= '<a href="javascript:void(0);" disabled="true">' . $data ["title"] . '</a>';
			$state .= get_lang ( "CourseClosed" );
		}
		
		$row [] = $title;
		$row [] = ($data ['is_pass'] == 1 ? get_lang ( "Passed" ) : get_lang ( "InLearning" ));
		$row [] = round ( Tracking::get_avg_student_progress ( $user_id, $course_code ), 1 ) . '%'; //平均进度
		$row [] = Tracking::get_avg_student_score ( $user_id, $course_code ); //测验平均分
		$row [] = '<a class="thickbox" href="../reporting/user_learning_stat.php?user_id=' . $user_id . '&course_code=' . $course_code . '&&height=420&width=810&TB_iframe=true&KeepThis=true&modal=true">' . Display::return_icon ( '2rightarrow.gif' ) . '</a>';
		$table_data [] = $row;
	}
	$sorting_options = array ('column' => 0, 'default_order_direction' => 'DESC' );
	$query_vars = array ('kewyword' => getgpc ( "keyword" ) );
	Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, array (), NAV_BAR_BOTTOM );
}
?>

<br />
<br />

<?php
/*
 * **********************************************************************************************
 *
 * 	Details for one course
 *
 * **********************************************************************************************
 */

$g_course=  getgpc('course');
if (isset ( $g_course )) {
	$sqlInfosCourse = "	SELECT course.code,course.title,course.db_name,
						CONCAT(user.firstname,' / ',user.email) as tutor_infos
						FROM $tbl_user as user,$tbl_course as course WHERE  course.code= '" . $g_course . "'";
	
	$resultInfosCourse = api_sql_query ( $sqlInfosCourse, __FILE__, __LINE__ );
	
	$a_infosCours = mysql_fetch_array ( $resultInfosCourse );
	$a_infosCours = CourseManager::get_course_information ( $_GET ['course'] );
	$tableTitle = $a_infosCours ['title'] . ' - ' . get_lang ( 'Tutor' ) . ' : ' . $a_infosCours ['tutor_infos'];
	
	?>
<table class="data_table" width="100%">
	<tr class="tableName">
		<td colspan="4"><strong><?php
	echo $tableTitle;
	?></strong></td>
	</tr>
	<tr>
		<th class="head"><?php
	echo get_lang ( 'Learnpath' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'Time' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'Progress' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'LastConnexion' );
	?></th>
	</tr>
	<?php
	$sqlLearnpath = "SELECT lp.name,lp.id FROM  " . $tbl_course_lp . " AS lp";
	$resultLearnpath = api_sql_query ( $sqlLearnpath, __FILE__, __LINE__ );
	if (mysql_num_rows ( $resultLearnpath ) > 0) {
		while ( $a_learnpath = mysql_fetch_array ( $resultLearnpath ) ) {
			$sqlProgress = "SELECT COUNT(DISTINCT lp_item_id) AS nbItem
										FROM " . $tbl_course_lp_view_item . " AS item_view
										INNER JOIN " . $tbl_course_lp_view . " AS view
											ON item_view.lp_view_id = view.id
											AND view.lp_id = " . $a_learnpath ['id'] . "
											AND view.user_id = " . $_user ['user_id'] . "
										WHERE item_view.status = 'completed' OR item_view.status = 'passed'";
			$resultProgress = api_sql_query ( $sqlProgress, __FILE__, __LINE__ );
			$a_nbItem = mysql_fetch_array ( $resultProgress );
			
			$sqlTotalItem = "SELECT	COUNT(item_type) AS totalItem FROM " . $tbl_course_lp_item . "
							WHERE lp_id = " . $a_learnpath ['id'] . " AND item_type != 'chapter'
							 AND item_type != 'webcs_chapter' AND item_type != 'dir'";
			$resultItem = api_sql_query ( $sqlTotalItem, __FILE__, __LINE__ );
			$a_totalItem = mysql_fetch_array ( $resultItem );
			
			$progress = round ( ($a_nbItem ['nbItem'] * 100) / $a_totalItem ['totalItem'] );
			
			// calculates last connection time
			$sql = 'SELECT MAX(start_time) FROM ' . $tbl_course_lp_view_item . ' AS item_view
					INNER JOIN ' . $tbl_course_lp_view . ' AS view
					ON item_view.lp_view_id = view.id AND view.lp_id = ' . $a_learnpath ['id'] . ' AND view.user_id = ' . $_user ['user_id'];
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			$start_time = mysql_result ( $rs, 0, 0 );
			
			// calculates time
			$sql = 'SELECT SUM(total_time) FROM ' . $tbl_course_lp_view_item . ' AS item_view
					INNER JOIN ' . $tbl_course_lp_view . ' AS view
					ON item_view.lp_view_id = view.id AND view.lp_id = ' . $a_learnpath ['id'] . ' AND view.user_id = ' . $_user ['user_id'];
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			$total_time = mysql_result ( $rs, 0, 0 );
			
			echo "<tr> <td> ";
			echo stripslashes ( $a_learnpath ['name'] );
			echo "</td> <td>";
			echo api_time_to_hms ( $total_time );
			echo "</td><td align='center'>";
			echo $progress . '%';
			echo "</td><td align='center'>";
			if ($start_time != '') {
				echo date ( "Y-m-d H:i", $start_time );
			} else {
				echo '-';
			}
			echo "</td></tr>";
		}
	} else {
		echo "<tr><td colspan='4'>" . get_lang ( 'NoLearnpath' ) . "</td></tr>";
	}
	?>
	<tr>
		<th class="head"><?php
	echo get_lang ( 'Exercices' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'Score' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'Attempts' );
	?></th>
		<th class="head"><?php
	echo get_lang ( 'Details' );
	?></th>
	</tr>

	<?php
	
	$sqlExercices = "SELECT quiz.title,id FROM " . $tbl_course_quiz . " AS quiz";
	
	$resuktExercices = api_sql_query ( $sqlExercices, __FILE__, __LINE__ );
	while ( $a_exercices = mysql_fetch_array ( $resuktExercices ) ) {
		$sqlEssais = "SELECT COUNT(ex.exe_id) as essais	FROM $tbl_stats_exercices AS ex
						WHERE ex.exe_user_id='" . $_user ['user_id'] . "' AND ex.exe_cours_id = '" . $a_infosCours ['code'] . "'
						AND ex.exe_exo_id = " . $a_exercices ['id'];
		$resultEssais = api_sql_query ( $sqlEssais, __FILE__, __LINE__ );
		$a_essais = mysql_fetch_array ( $resultEssais );
		
		$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM $tbl_stats_exercices
								 WHERE exe_user_id = " . $_user ['user_id'] . "
								 AND exe_cours_id = '" . $a_infosCours ['code'] . "'
								 AND exe_exo_id = " . $a_exercices ['id'] . "
								ORDER BY exe_date DESC LIMIT 1";
		
		$resultScore = api_sql_query ( $sqlScore, __FILE__, __LINE__ );
		$score = 0;
		while ( $a_score = mysql_fetch_array ( $resultScore ) ) {
			$score = $score + $a_score ['exe_result'];
			$weighting = $weighting + $a_score ['exe_weighting'];
			$exe_id = $a_score ['exe_id'];
		}
		$pourcentageScore = round ( ($score * 100) / $weighting );
		
		$weighting = 0;
		
		echo "<tr><td>";
		echo $a_exercices ['title'];
		echo "</td>";
		echo "<td align='center'>";
		echo $pourcentageScore . '%';
		echo "</td><td align='center'>";
		echo $a_essais ['essais'];
		echo '</td><td align="center" width="25">';
		echo "</td></tr>";
	}
	?>
</table>
<?php
}

Display::display_footer ();
?>