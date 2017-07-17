<?php

/**
 ==============================================================================
 * 新统计图表，增加FLASH动态显示，原来为 index.php
 * @package zllms.statistics
 ==============================================================================
 */

$language_file = array ('tracking', 'admin', 'course_home' );
$cidReset = true;
include ('../../inc/global.inc.php');
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once (api_get_path ( LIB_PATH ) . 'FusionCharts/Includes/FusionCharts_Gen.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/statistics/statistics.lib.php');

$strAction = (isset ( $_GET ['action'] ) ? getgpc('action') : 'recentlogins');
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval(getgpc ( 'keyword_deptid', 'G') ) : '0';

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path(WEB_JS_PATH ));
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';


$htmlHeadXtra [] = '<script language="JavaScript" type="text/JavaScript">
	$(document).ready( function() {
		$("#org_id").change(function(){
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"options_get_all_sub_depts",org_id:$("#org_id").val()},
				function(data,textStatus){
					//alert(data);
					$("#dept_id").html(data);
				});
		});
	});
</script>';


Display::display_header(NULL);



$strCourse = get_lang ( 'Courses' );
$strUsers = get_lang ( 'Users' );

$myTools ['recentlogins'] = get_lang ( 'Logins' );
$myTools ['logins_month'] = get_lang ( 'Logins' ) . '/' . get_lang ( 'PeriodMonth' );
$myTools ['logins_week'] = get_lang ( 'Logins' ) . '/' . get_lang ( 'PeriodWeek' );
$myTools ['logins_hour'] = get_lang ( 'Logins' ) . '/' . get_lang ( 'PeriodHour' );

$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = $strAction == $key ?'class="selected"' : '';
	$html .= '<li  ' . $strClass . '><a href="' . $_SERVER ['PHP_SELF'] . '?action=' . $key . '"><em>' . $value . '</em></a></li>';
}
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;


$objDept = new DeptManager ();
/*$all_org = $objDept->get_all_org ();
$orgs [''] = get_lang ( 'All' );
foreach ( $all_org as $org ) {
	$orgs [$org ['id']] = $org ['dept_name'];
}

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$form->addElement ( 'hidden', 'action', $strAction );
$form->addElement ( 'select', 'keyword_orgid', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;', 'title' => get_lang ( 'InOrg' ) ) );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), null, array ('id' => "dept_id", 'style' => 'height:22px;' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
echo '<div class="actions">';
$form->display ();
echo '</div>';*/

if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
	$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
	if ($dept_sn) $sql_where .= " AND t2.dept_sn LIKE '" . $dept_sn . "%'";
}
//echo "<center>";
switch ($strAction) {
	case 'recentlogins' :
		echo "<table><tr valign='middle'><td>";
		print_recent_login_stats ();
		echo "</td><td>";
		statistics::print_recent_login_stats ();
		echo "</td></tr></table>";
		break;
	
	case 'logins_month' :
		echo "<table><tr valign='top'><td>";
		print_login_stats ( 'month', $dept_id );
		echo "</td><td>";
		statistics::print_login_stats ( 'month', $dept_id );
		echo "</td></tr></table>";
		break;
		
	case 'logins_week' :
		echo "<table><tr valign='top'><td>";
		print_login_stats ( 'week', $dept_id );
		echo "</td><td>";
		statistics::print_login_stats ( 'week', $dept_id );
		echo "</td></tr></table>";
		break;
		
	case 'logins_hour' :
		echo "<table><tr valign='top'><td>";
		print_login_stats ( 'hour', $dept_id );
		echo "</td><td>";
		statistics::print_login_stats ( 'hour', $dept_id );
		echo "</td></tr></table>";
		break;
	
	
	case 'logins' :
		echo "<table><tr valign='top'><td>";
		print_login_stats ( getgpc('type','G'),$dept_id );
		echo "</td><td>";
		statistics::print_login_stats ( getgpc('type','G'),$dept_id );
		echo "</td></tr></table>";
		break;
}
//echo "</center>";
echo '</div></div></div>';

function print_recent_login_stats() {
	//global $restrict_org_id;
	global $sql_where;
	$total_logins = array ();
	$table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	$tbl_dept = Database::get_main_table ( VIEW_USER_DEPT );
	
	$sql [get_lang ( 'Thisday' )] = "SELECT count(login_user_id) AS number FROM $table AS t1, $tbl_dept AS t2 WHERE DATE_ADD(login_date, INTERVAL 1 DAY) >= NOW() AND t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	$sql [get_lang ( 'Last7days' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1, $tbl_dept AS t2 WHERE DATE_ADD(login_date, INTERVAL 7 DAY) >= NOW() AND t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	$sql [get_lang ( 'Last10days' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1, $tbl_dept AS t2 WHERE DATE_ADD(login_date, INTERVAL 10 DAY) >= NOW() AND t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	$sql [get_lang ( 'Last14days' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1, $tbl_dept AS t2 WHERE DATE_ADD(login_date, INTERVAL 14 DAY) >= NOW() AND t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	$sql [get_lang ( 'Last31days' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1, $tbl_dept AS t2 WHERE DATE_ADD(login_date, INTERVAL 31 DAY) >= NOW() AND t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	$sql [get_lang ( 'Total' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1, $tbl_dept AS t2 WHERE t1.login_user_id=t2.user_id " . ($sql_where ? $sql_where : '');
	
	foreach ( $sql as $index => $query ) {
		//echo $query.'<br/>';
		$total_logins [$index] = Database::get_scalar_value ( $query );
	}
	Statistics::display_stat_chart ( get_lang ( 'Logins' ), $total_logins ,"Column3D",480,420);
}

function print_login_stats($type,$dept_id) {
	global $restrict_org_id, $sql_where;
	$table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$tbl_dept = Database::get_main_table ( VIEW_USER_DEPT );
	
	$objDept = new DeptManager ();
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where = "  t2.dept_sn LIKE '" . $dept_sn . "%'";
	switch ($type) {
		case 'month' :
			$period = get_lang ( 'PeriodMonth' );
			if (empty ( $dept_id )) {
				$sql = "SELECT DATE_FORMAT( login_date, '%Y-%m' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY login_date ";
			} else {
				$sql = "SELECT DATE_FORMAT( login_date, '%Y-%m' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  WHERE  " . $sql_where . " GROUP BY stat_date ORDER BY login_date ";
			}
			
			break;
		case 'hour' :
			$period = get_lang ( 'PeriodHour' );
			if (empty ( $dept_id )) {
				$sql = "SELECT DATE_FORMAT( login_date, '%H' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY stat_date ";
			} else {
				$sql = "SELECT DATE_FORMAT( login_date, '%H' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE  " . $sql_where . "  GROUP BY stat_date ORDER BY stat_date ";
			}
			break;
		case 'day' :
			$period = get_lang ( 'PeriodDay' );
			if (empty ( $dept_id )) {
				$sql = "SELECT DATE_FORMAT( login_date, '%W' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%w' ) ";
			} else {
				$sql = "SELECT DATE_FORMAT( login_date, '%W' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  WHERE  " . $sql_where . "  GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%w' ) ";
			}
			break;
		case 'week' :
			$period = get_lang ( "PeriodWeek" );
			if (empty ( $dept_id )) {
				$sql = "SELECT DATE_FORMAT( login_date, '%Y-第%u周' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " WHERE YEAR(login_date)=YEAR(NOW()) GROUP BY stat_date  ORDER BY DATE_FORMAT( login_date, '%u' ) ";
			} else {
				$sql = "SELECT DATE_FORMAT( login_date, '%Y-第%u周' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id
						WHERE YEAR(login_date)=YEAR(NOW()) AND " . $sql_where . "  GROUP BY stat_date  ORDER BY DATE_FORMAT( login_date, '%u' )";
			}
			break;
	}
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$result = array ();
	while ( $obj = Database::fetch_array($res,'ASSOC') ) {
		$result [$obj['stat_date']] = $obj['number_of_logins'];
	}
	Statistics::display_stat_chart ( get_lang ( 'Logins' ) . ' (' . $period . ')', $result, "Bar2D" ,480,420);
}

Display::display_footer (TRUE);