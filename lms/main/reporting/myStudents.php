<?php
/**
 * 单个学生的课程相关统计信息
 */

// name of the language file that needs to be included
$language_file = array ('registration', 'index', 'tracking', 'exercice', 'admin' );

//liyu: 是否需要刷新课程信息，等测试？
$cidReset = true;

include ('../inc/global.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'scorm/learnpath.class.php');

$g_export=  getgpc('export');
$export_csv = isset ( $g_export ) && $g_export == 'csv' ? true : false;
if ($export_csv) {
	ob_start ();
}
$csv_content = array ();

$nameTools = get_lang ( "StudentDetails" );

$g_details=  getgpc('details');
if (isset ( $g_details )) {
	$nameTools = get_lang ( "DetailsStudentInCourse" );
} else {
	$interbreadcrumb [] = array ("url" => "index.php", "name" => get_lang ( 'MySpace' ) );
}

api_block_anonymous_users ();

Display::display_header ( NULL, FALSE );

function calculHours($seconds) {
	//How many hours ?
	$hours = floor ( $seconds / 3600 );
	
	//How many minutes ?
	$min = floor ( ($seconds - ($hours * 3600)) / 60 );
	if ($min < 10) $min = "0" . $min;
	
	//How many seconds
	$sec = $seconds - ($hours * 3600) - ($min * 60);
	if ($sec < 10) $sec = "0" . $sec;
	
	return $hours . "h" . $min . "m" . $sec . "s";
}

function is_teacher($course_code) {
	global $_user;
	$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$sql = "SELECT 1 FROM $tbl_course_user WHERE user_id='" . $_user ["user_id"] . "' AND course_code='" . $course_code . "' AND status='1'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	return (mysql_result ( $result ) != 1);
}

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_stats_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
$tbl_stats_exercices_attempts = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
$g_user_id=  intval ( getgpc('user_id'));
$i_user_id = (is_not_blank ( $g_user_id ) ? $g_user_id : api_get_user_id ());

$g_student=  getgpc('student');
if (is_not_blank ( $g_student )) {
	$student_id = intval ( $g_student );
	$a_infosUser = UserManager::get_user_info_by_id ( $student_id );
	$sql = "SELECT course_code FROM " . $tbl_course_user . " WHERE user_id='" . escape ( $student_id ) . "'";
	$a_courses = Database::get_into_array ( $sql, __FILE__, __LINE__ );
        $g_course=  getgpc('course');
	$a_infosCours = CourseManager::get_course_information ( $g_course );
	
	echo '<div class="actions"><div align="right">
			' . get_lang ( "Course" ) . ":&nbsp;&nbsp;<span style='font-weight:bold;font-size:14px'>" . $a_infosCours ["title"] . "</span>" . str_repeat ( "&nbsp;", 
			16 ) . '
		<a href="#" onclick="window.print()">' . Display::return_icon ( 'printmgr.gif', get_lang ( 'Print' ), array ('align' => 'absbottom' ) ) . '&nbsp;' . get_lang ( 'Print' ) . '</a>
		<a href="' . $_SERVER ['PHP_SELF'] . '?' . $_SERVER ['QUERY_STRING'] . '&export=csv">' .
			 Display::return_icon ( 'excel.gif', get_lang ( 'ExportAsCSV' ), array ('align' => 'absbottom' ) ) . '&nbsp;' . get_lang ( 'ExportAsCSV' ) . '</a>
	  </div></div>';

$myTools ['general'] = array (get_lang ( 'GeneralInfo' ), 'students.gif' );
$myTools ['details'] = array (get_lang ( 'CourseTracking' ), 'courses.gif' );
$total_tag_cnt = count ( $myTools );
$width_percent = round ( (101 - $total_tag_cnt) / ($total_tag_cnt + 2) ) . "%";
$g_tabaction=  getgpc('tabAction');
$strActionType = (isset ( $g_tabaction ) ? $g_tabaction : 'details');
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

if (is_equal ( $strActionType, "general" )) {
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

// 用户信息


$a_infosUser ['name'] = $a_infosUser ['firstname'] . ' ' . $a_infosUser ['lastname'];
$avg_student_progress = $avg_student_score = $nb_courses = 0;

//liyu: 增加统计平均进度及测验得分(用户参与的所有课程)


if (is_array ( $a_courses ) && count ( $a_courses ) > 0) {
	foreach ( $a_courses as $course_code ) {
		$nb_courses ++;
		$avg_student_progress += Tracking::get_avg_student_progress ( $a_infosUser ['user_id'], $course_code );
		$avg_student_score += Tracking::get_avg_student_score ( $a_infosUser ['user_id'], $course_code );
	}
}

$g_course=  getgpc('course');
if (! empty ( $g_course )) {
	$course_code = $g_course;
	$avg_student_progress = Tracking::get_avg_student_progress ( $a_infosUser ['user_id'], $course_code );
	$avg_student_score = Tracking::get_avg_student_score ( $a_infosUser ['user_id'], $course_code );
}

//最后登录时间
$last_connection_date = Tracking::get_last_connection_date ( $a_infosUser ['user_id'] );
if ($last_connection_date == '') {
	$last_connection_date = get_lang ( 'NoConnexion' );
}

//平台总的学习时间
$seconds_on_the_platform = Tracking::get_time_spent_on_the_platform ( $a_infosUser ['user_id'] );
$time_spent_on_the_platform = api_time_to_hms ( $seconds_on_the_platform );

// CSV informations
$csv_content [] = array (get_lang ( 'Informations' ) );
$csv_content [] = array (get_lang ( 'Name' ), get_lang ( 'Email' ), get_lang ( 'Tel' ) );
$csv_content [] = array ($a_infosUser ['name'], $a_infosUser ['email'], $a_infosUser ['phone'] );

$csv_content [] = array ();
$csv_content [] = array (get_lang ( 'Tracking' ) );
$csv_content [] = array (get_lang ( 'LatestLogin' ), get_lang ( 'TimeSpentOnThePlatform' ), get_lang ( 'AvgProgress' ), get_lang ( 'AvgScore' ) );
$csv_content [] = array ($last_connection_date, $time_spent_on_the_platform, $avg_student_progress . ' %', $avg_student_score . ' %' );

?>

<a name="infosStudent"></a>
<table class="data_table">
	<tr>
		<td class="border">
			<table width="100%" border="0">
				<tr>

			<?php
//头像
if (! empty ( $a_infosUser ['picture_uri'] ) && file_exists ( api_get_path ( SYS_PATH ) . "storage/users_picture/" . $a_infosUser ['picture_uri'] )) {
	echo '<td class="borderRight" width="10%"><img src="' . api_get_path ( WEB_PATH ) . 'storage/users_picture/' . $a_infosUser ['picture_uri'] . '" width="100" /></td>';
} else {
	echo '<td class="borderRight" width="10%">' . Display::return_icon ( 'unknown.jpg' ) . '</td>';
}

?>
				<td class="none" width="40%">
						<table width="100%">
							<tr>
								<th><?php
echo get_lang ( 'Informations' );
?></th>
							</tr>
							<tr>
								<td>
									<table>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'FirstName' );
?></td>
											<td class="none" align="left"><?php
echo $a_infosUser ['name'];
?></td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'Email' );
?></td>
											<td class="none" align="left"><?php
if (! empty ( $a_infosUser ['email'] )) {
	echo '<a href="mailto:' . $a_infosUser ['email'] . '">' . $a_infosUser ['email'] . '</a>';
} else {
	echo get_lang ( 'NoEmail' );
}
?></td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'Tel' );
?></td>
											<td class="none" align="left"><?php
if (! empty ( $a_infosUser ['phone'] )) {
	echo $a_infosUser ['phone'];
} else {
	echo get_lang ( 'NoTel' );
}
?></td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'OnLine' );
?></td>
											<td class="none" align="left"><?php
echo $online;
?></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>

					<td class="borderLeft" width="35%">
						<table width="100%">
							<tr>
								<th><?php
echo get_lang ( 'Tracking' );
?></th>
							</tr>
							<tr>
								<td>
									<table>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'LastConnexion' )?>
								</td>
											<td class="none" align="left"><?php
echo $last_connection_date?>
								</td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'TimeSpentOnThePlatform' )?>
								</td>
											<td class="none" align="left"><?php
echo $time_spent_on_the_platform?>
								</td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'AvgProgress' )?>
								</td>
											<td class="none" align="left"><?php
echo round ( $avg_student_progress, 1 ) . ' %'?>
								</td>
										</tr>
										<tr>
											<td class="none" align="right"><?php
echo get_lang ( 'AvgScore' )?>
								</td>
											<td class="none" align="left"><?php
echo $avg_student_score . ' %'?>
								</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>

					<td class="borderLeft" width="15%">
						<table width="100%">
							<tr>
								<th><?php
echo get_lang ( 'Actions' );
?></th>
							</tr>
							<tr>

					<?php
$sendMail = Display::encrypted_mailto_link ( $a_infosUser ['email'], ' ' . get_lang ( 'SendMail' ) );
if (! empty ( $a_infosUser ['email'] )) {
	echo "<td class='none'>";
	echo Display::return_icon ( 'send_mail.gif', '', array ('align' => 'absbottom' ) ) . '&nbsp;' . $sendMail;
	echo "</td>";
} else {
	echo "<td class='noLink none'>";
	echo Display::return_icon ( 'send_mail.gif', get_lang ( 'SendMail' ), array ('align' => 'absbottom' ) ) . '&nbsp; <strong> > ' . get_lang ( 'SendMail' ) . '</strong>';
	echo "</td>";
}
?>
					</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
}
?>


					<?php
if (is_equal ( $strActionType, "details" )) {
?>
<table class="data_table">

<?php
$g_details=  getgpc('details');
if (! empty ( $g_details )) {
	
	$tableTitle = $a_infosCours ['title'] . ' |  ' . get_lang ( 'Tutor' ) . ' : ' . stripslashes ( $a_infosCours ['tutor_name'] );
	
	$csv_content [] = array ();
	$csv_content [] = array ($tableTitle );
	
	?>
	<tr class="tableName">
		<td><strong><?php
	echo $tableTitle;
	?></strong></td>
	</tr>
	<tr>
		<!-- line about learnpaths -->
		<td>
			<table class="data_table">
				<tr>
					<th align="left"><?php
	echo get_lang ( 'LearnpathCW' );
	?></th>
					<th><?php
	echo get_lang ( 'Time' );
	?></th>
					<th><?php
	echo get_lang ( 'Score' );
	?></th>
					<th><?php
	echo get_lang ( 'Progress' );
	?></th>
					<th><?php
	echo get_lang ( 'LastAccess' );
	?></th>
					<th><?php
	echo get_lang ( 'Details' );
	?></th>
				</tr>
			<?php
	$a_headerLearnpath = array (get_lang ( 'Learnpath' ), get_lang ( 'Time' ), get_lang ( 'Progress' ), get_lang ( 'LastConnexion' ) );
	//$csv_content[] = array();
	$csv_content [] = array (get_lang ( 'Learnpath' ), get_lang ( 'Time' ), get_lang ( 'Progress' ), get_lang ( 'LastConnexion' ) );
	
	$tbl_course_lp = Database::get_course_table ( TABLE_LP_MAIN, $a_infosCours ['db_name'] );
	$tbl_course_lp_item = Database::get_course_table ( TABLE_LP_ITEM, $a_infosCours ['db_name'] );
	$tbl_course_lp_view_item = Database::get_course_table ( TABLE_LP_ITEM_VIEW, $a_infosCours ['db_name'] );
	$tbl_course_lp_view = Database::get_course_table ( TABLE_LP_VIEW, $a_infosCours ['db_name'] );
	
	$sqlLearnpath = "SELECT lp.name,lp.id FROM $tbl_course_lp AS lp ";
	$sqlLearnpath .= " WHERE cc='" . getgpc ( "course" ) . "' ";
	$resultLearnpath = api_sql_query ( $sqlLearnpath, __FILE__, __LINE__ );
	if (Database::num_rows ( $resultLearnpath ) > 0) {
		$i = 0;
		while ( $a_learnpath = mysql_fetch_array ( $resultLearnpath ) ) {
			//$progress = learnpath::get_db_progress ( $a_learnpath ['id'], $student_id, '%', $a_infosCours ['db_name'] );
			
                    $get_student=  getgpc('student');
			// 学习时间
			$sql = 'SELECT SUM(total_time)
								FROM ' . $tbl_course_lp_view_item . ' AS item_view
								INNER JOIN ' . $tbl_course_lp_view . ' AS view
									ON item_view.lp_view_id = view.id
									AND view.lp_id = ' . $a_learnpath ['id'] . '
									AND view.user_id = ' . intval ( $get_student );
			$sql .= " WHERE item_view.cc='" . getgpc ( "course" ) . "' ";
			$total_time = Database::get_scalar_value ( $sql );
			
			// 最后访问时间
			$sql = 'SELECT MAX(start_time)
								FROM ' . $tbl_course_lp_view_item . ' AS item_view
								INNER JOIN ' . $tbl_course_lp_view . ' AS view
									ON item_view.lp_view_id = view.id
									AND view.lp_id = ' . $a_learnpath ['id'] . '
									AND view.user_id = ' . intval ( $get_student );
			$sql .= " WHERE item_view.cc='" . getgpc ( "course" ) . "' ";
			$start_time = Database::get_scalar_value ( $sql );
			
			//测验平均分
			$sql = 'SELECT id as item_id, max_score FROM ' . $tbl_course_lp_item . ' AS lp_item
							WHERE lp_id=' . $a_learnpath ['id'] . ' AND item_type="quiz"';
			$sql .= " AND lp_item.cc='" . getgpc ( "course" ) . "' ";
			$rsItems = api_sql_query ( $sql, __FILE__, __LINE__ );
			$total_score = $total_weighting = 0;
			while ( $item = Database::fetch_array ( $rsItems, 'ASSOC' ) ) {
				$sql = 'SELECT score as student_score
								FROM ' . $tbl_course_lp_view . ' as lp_view
								LEFT JOIN ' . $tbl_course_lp_view_item . ' as lp_view_item
									ON lp_view.id = lp_view_item.lp_view_id
									AND lp_view_item.lp_item_id = ' . $item ['item_id'] . '
								WHERE lp_view.user_id = ' . intval ( $get_student ) . '
								AND ' . $a_learnpath ['id'];
				$sql .= " AND lp_view_item.cc='" . getgpc ( "course" ) . "' ";
				$rsScores = api_sql_query ( $sql, __FILE__, __LINE__ );
				$total_score += mysql_result ( $rsScores, 0, 0 );
				$total_weighting += $item ['max_score'];
			}
			$score = round ( $total_score / $total_weighting * 100, 2 );
			
			$s_css_class = ($i % 2 == 0 ? "row_odd" : "row_even");
			$i ++;
			
			$csv_content [] = array (stripslashes ( $a_learnpath ['name'] ), api_time_to_hms ( $total_time ), $progress . ' %', date ( 'Y-m-d', $start_time ) );
			
			?>
			<tr class="<?php
			echo $s_css_class;
			?>">
					<td><?php
			echo stripslashes ( $a_learnpath ['name'] );
			?></td>
					<td align="right"><?php
			echo api_time_to_hms ( $total_time )?></td>
					<td align="right"><?php
			echo $score?></td>
					<td align="right"><?php
			echo $progress?></td>
					<td align="center"><?php
			if ($start_time != '') //liyu
//echo format_locale_date(get_lang('DateFormatLongWithoutDay'),$start_time);
			echo strftime ( '%Y-%m-%d', $start_time );
			else echo '-';
			?></td>
					<td align="center"><?php
			if (($progress > 0 || $score > 0)) //V1.4.0
{
				?> <a
						href="lp_tracking.php?course=<?php
				echo $_GET ['course']?>&origin=<?php
				echo $_GET ['origin']?>&lp_id=<?php
				echo $a_learnpath ['id']?>&student_id=<?php
				echo $a_infosUser ['user_id']?>">
					<?php
				echo Display::return_icon ( '2rightarrow.gif' );
				?> </a> <?php
			}
			?></td>
				</tr>

			<?php
			$dataLearnpath [$i] [] = $a_learnpath ['name'];
			$dataLearnpath [$i] [] = $progress . '%';
			$i ++;
		}
	
	} else {
		echo "<tr><td colspan='6'>" . get_lang ( 'NoLearnpath' ) . "</td></tr>";
	}
	?>
		</table>
		</td>
	</tr>



	<tr>
		<!-- line about exercises -->
		<td>
			<table class="data_table">
				<tr>
					<th align="left"><?php
	echo get_lang ( 'Exercices' );
	?></th>
					<th><?php
	echo get_lang ( 'Attempts' );
	?></th>
					<th><?php
	echo get_lang ( 'MaxScore' )?></th>
					<th><?php
	echo get_lang ( 'MinScore' );
	?></th>
					<th><?php
	echo get_lang ( 'AverageScore' );
	?></th>
					<th><?php
	echo get_lang ( 'LastScore' );
	?></th>
					<th><?php
	echo get_lang ( 'LastAttempDate' );
	?></th>
					<th><?php
	echo get_lang ( 'CorrectTest' );
	?></th>
				</tr>
			<?php
	$csv_content [] = array ();
	$csv_content [] = array (get_lang ( 'Exercices' ), get_lang ( 'Attempts' ), get_lang ( 'MaxScore' ), get_lang ( 'MinScore' ), get_lang ( 'AverageScore' ), get_lang ( 'LastScore' ), get_lang ( 'LastAttempDate' ) );
	$tbl_course_quiz = Database::get_course_table ( 'quiz', $a_infosCours ['db_name'] );
	
	$sqlExercices = "SELECT quiz.title,id FROM " . $tbl_course_quiz . " AS quiz";
	$sqlExercices .= " WHERE cc='" . getgpc ( "course" ) . "' ";
	$resultExercices = api_sql_query ( $sqlExercices, __FILE__, __LINE__ );
	$i = 0;
	if (Database::num_rows ( $resultExercices ) > 0) {
            $student_get=  getgpc('student');
		while ( $a_exercices = Database::fetch_array ( $resultExercices, "ASSOC" ) ) {
			$sqlEssais = "SELECT COUNT(ex.exe_id) as essais FROM $tbl_stats_exercices AS ex
								WHERE  ex.exe_cours_id = '" . $a_infosCours ['code'] . "'
								AND ex.exe_exo_id = " . $a_exercices ['id'] . "
								AND exe_user_id='" . $student_get . "'";
			$resultEssais = api_sql_query ( $sqlEssais, __FILE__, __LINE__ );
			$a_essais = mysql_fetch_array ( $resultEssais );
			
			$sql = "SELECT MAX(exe_result),MIN(exe_result),AVG(exe_result) FROM $tbl_stats_exercices AS ex
						WHERE  ex.exe_cours_id = '" . $a_infosCours ['code'] . "'
						AND ex.exe_exo_id = " . $a_exercices ['id'] . "	AND exe_user_id='" . $student_get . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			list ( $max_score, $min_score, $avg_score ) = Database::fetch_row ( $res );
			/*$max_score=Database::result($res,0,0);
					 $min_score=Database::result($res,0,1);
					 $avg_score=Database::result($res,0,2);*/
			
			$sqlScore = "SELECT exe_id, exe_result,exe_weighting,exe_date
								 FROM $tbl_stats_exercices
								 WHERE exe_user_id = " . $student_get . "
								 AND exe_cours_id = '" . $a_infosCours ['code'] . "'
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
			
			$pourcentageMaxScore = (round ( $max_score / $weighting, 4 )) * 100;
			$pourcentageMinScore = (round ( $min_score / $weighting, 4 )) * 100;
			$pourcentageAvgScore = (round ( $avg_score / $weighting, 4 )) * 100;
			$pourcentageScore = round ( $score / $weighting, 4 ) * 100;
			
			$weighting = 0;
			
			$csv_content [] = array ($a_exercices ['title'], $a_essais ['essais'], $pourcentageMaxScore . " %", $pourcentageMinScore . " %", $pourcentageAvgScore . " %", $pourcentageScore . ' %', $exe_date );
			
			$s_css_class = ($i % 2 == 0 ? "row_odd" : "row_even");
			
			$i ++;
			
			echo "<tr class='$s_css_class'>";
			echo "<td>", $a_exercices ['title'], "</td>";
			echo "	<td align='right'>", $a_essais ['essais'], "</td>";
			echo "	<td align='right'>", $pourcentageMaxScore . ' %' . "</td>";
			echo "	<td align='right'>", $pourcentageMinScore . ' %' . "</td>";
			echo "	<td align='right'>", $pourcentageAvgScore . ' %' . "</td>";
			echo "	<td align='right'>", $pourcentageScore . ' %' . "</td>";
			echo "	<td align='center'>", $exe_date . "</td>";
			echo "<td align='center'>";
			$sql_last_attempt = 'SELECT exe_id FROM ' . $tbl_stats_exercices . ' WHERE exe_exo_id="' . $a_exercices ['id'] . '" AND exe_user_id="' . $_GET ['student'] . '" AND exe_cours_id="' . $a_infosCours ['code'] . '" ORDER BY exe_date DESC LIMIT 1';
			$resultLastAttempt = api_sql_query ( $sql_last_attempt, __FILE__, __LINE__ );
			$id_last_attempt = mysql_result ( $resultLastAttempt, 0, 0 );
			if ($a_essais ['essais'] > 0) echo '<a href="../exercice/exercise_show.php?id=' . $id_last_attempt . '&cidReq=' . $a_infosCours ['code'] . '&student=' . $_GET ['student'] . '&origin=' . (empty ( $_GET ['origin'] ) ? 'tracking' : $_GET ['origin']) . '">' . Display::return_icon ( 
					'quiz.gif' ) . ' </a>';
			echo "</td></tr>";
			
			$dataExercices [$i] [] = $a_exercices ['title'];
			$dataExercices [$i] [] = $pourcentageScore . '%';
			$dataExercices [$i] [] = $a_essais ['essais'];
			//$dataExercices[$i][] =  corrections;
			$i ++;
		}
	} else {
		echo "<tr><td colspan='6'>" . get_lang ( 'NoExercise' ) . "</td></tr>";
	}
	?>
		</table>
		</td>
	</tr>




</table>
<?php
} 

else //列表
{
	?>
<tr>
	<th><?php
	echo get_lang ( 'Course' );
	?></th>
	<th><?php
	echo get_lang ( 'Time' );
	?></th>
	<th><?php
	echo get_lang ( 'Progress' );
	?></th>
	<th><?php
	echo get_lang ( 'Score' );
	?></th>
	<th><?php
	echo get_lang ( 'Details' );
	?></th>
</tr>
<?php
	if (count ( $a_courses ) > 0) {
		$csv_content [] = array ();
		$csv_content [] = array (get_lang ( 'Course' ), get_lang ( 'Time' ), get_lang ( 'Progress' ), get_lang ( 'Score' ) );
		foreach ( $a_courses as $course_code ) {
			$course_infos = CourseManager::get_course_information ( $course_code );
			$progress = Tracking::get_avg_student_progress ( $a_infosUser ['user_id'], $course_code ) . ' %';
			$score = Tracking::get_avg_student_score ( $a_infosUser ['user_id'], $course_code ) . ' %';
			$csv_content [] = array ($course_infos ['title'], $time_spent_on_course, $progress, $score );
			echo '
				<tr>
					<td align="center">
						' . $course_infos ['title'] . '
					</td>
					<td align="right">
						' . $time_spent_on_course . '
					</td>
					<td align="right">
						' . $progress . '
					</td>
					<td align="right">
						' . $score . '
					</td>';
                        $g_id_coach=  getgpc('id_coach');
			if (isset ( $g_id_coach ) && intval ( $g_id_coach ) != 0) {
				echo '<td align="center">
							<a href="' . $_SERVER ['PHP_SELF'] . '?student=' . $a_infosUser ['user_id'] .
						 '&details=true&course=' . $course_infos ['code'] . '&id_coach=' . $g_id_coach . '#infosStudent">' . Display::return_icon ( '2rightarrow.gif' ) . '</a>
						</td>';
			} else {
				echo '<td align="center">
							<a href="' . $_SERVER ['PHP_SELF'] . '?student=' . $a_infosUser ['user_id'] . '&details=true&course=' . $course_infos ['code'] .
						 '#infosStudent">' . Display::return_icon ( '2rightarrow.gif' ) . '</a>
						</td>';
			}
			echo '</tr>';
		
		}
	} else {
		echo "<tr>
					<td colspan='5'>
						" . get_lang ( 'NoCourse' ) . "
					</td>
				  </tr>
				 ";
	}
} //end of else !empty($details)
?>
</table>
<br />
<?php
$g_details=  getgpc('details');
$g_origin=  getgpc('origin');
if (! empty ( $g_details ) && $g_origin != 'tracking_course' && $g_origin != 'user_course') {
	?>

<br />
<br />
<?php
}
$g_exe_id=  intval ( getgpc('exe_id'));
if (! empty ( $g_exe_id )) {
	$tbl_course_quiz_question = Database::get_course_table ( 'quiz_question', $a_infosCours ['db_name'] );
	$tbl_course_quiz_rel_question = Database::get_course_table ( 'quiz_rel_question', $a_infosCours ['db_name'] );
	$tbl_course_quiz = Database::get_course_table ( 'quiz', $a_infosCours ['db_name'] );
	$tbl_course_quiz_answer = Database::get_course_table ( 'quiz_answer', $a_infosCours ['db_name'] );
	
	$sqlExerciceDetails = " SELECT qq.question, qq.ponderation, qq.id
				 				FROM " . $tbl_course_quiz_question . " as qq
								INNER JOIN " . $tbl_course_quiz_rel_question . " as qrq
									ON qrq.question_id = qq.id
									AND qrq.exercice_id = " . $g_exe_id;
	$resultExerciceDetails = api_sql_query ( $sqlExerciceDetails, __FILE__, __LINE__ );
	
	$sqlExName = "	SELECT quiz.title
						FROM " . $tbl_course_quiz . " AS quiz
					 	WHERE quiz.id = " . $g_exe_id;
	$resultExName = api_sql_query ( $sqlExName, __FILE__, __LINE__ );
	$a_exName = mysql_fetch_array ( $resultExName );
	
	echo "<table class='data_table'>
			 	<tr>
					<th colspan='2'>
						" . $a_exName ['title'] . "
					</th>
				</tr>
             ";
	
	while ( $a_exerciceDetails = mysql_fetch_array ( $resultExerciceDetails ) ) {
		$sqlAnswer = "	SELECT qa.comment, qa.answer
							FROM  " . $tbl_course_quiz_answer . " as qa
							WHERE qa.question_id = " . $a_exerciceDetails ['id'];
		$resultAnswer = api_sql_query ( $sqlAnswer, __FILE__, __LINE__ );
		
		echo "<a name='infosExe'></a>";
		
		echo "
			<tr>
				<td colspan='2'>
					<strong>" . $a_exerciceDetails ['question'] . ' /' . $a_exerciceDetails ['ponderation'] . "</strong>
				</td>
			</tr>
			";
		while ( $a_answer = mysql_fetch_array ( $resultAnswer ) ) {
			echo "
				<tr>
					<td>
						" . $a_answer ['answer'] . "
					</td>
					<td>
				";
			if (! empty ( $a_answer ['comment'] )) echo $a_answer ['comment'];
			else echo get_lang ( 'NoComment' );
			echo "
					</td>
				</tr>
				";
		}
	}
	
	echo "</table>";
}
if (is_array ( $a_headerLearnpath ) && is_array ( $a_headerExercices ) && is_array ( $a_headerProductions )) $a_header = array_merge ( $a_headerLearnpath, $a_headerExercices, $a_headerProductions );
}
} //END: if(!empty($_GET['student']))


//@todo		User can choose the export encoding 	Zhong
if ($export_csv) {
$export_encoding = get_default_encoding ();
$export_contents = array ();
foreach ( $csv_content as $key => $contents ) {
$export_content = array ();
foreach ( $contents as $index => $content ) {
	$export_content [] = mb_convert_encoding ( $content, $export_encoding, SYSTEM_CHARSET );
}
$export_contents [] = $export_content;
}

ob_end_clean ();
Export::export_table_csv ( $export_contents, 'reporting_' . $a_infosUser ['username'] . '_' . date ( 'YmdHi' ) );
}

/*
 ==============================================================================
 FOOTER
 ==============================================================================
 */

Display::display_footer ();

?>