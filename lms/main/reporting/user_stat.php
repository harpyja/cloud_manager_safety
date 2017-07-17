<?php
/**----------------------------------------------------------------

 liyu: 2012-2-20
 *----------------------------------------------------------------*/
$language_file = array ('tracking', 'admin', 'course_home' );
$cidReset = true;
include_once ('../inc/global.inc.php');

api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once (api_get_path ( LIB_PATH ) . 'FusionCharts/Includes/FusionCharts_Gen.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/statistics/statistics.lib.php');
$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );

$g_action=  getgpc('action');
$strAction = (isset ( $g_action ) ? getgpc ( 'action', 'G' ) : 'by_sex');
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header ( NULL );

$myTools ['by_sex'] = '按性别';
$myTools ['by_grade'] = '按等级';
$myTools ['by_lang'] = '按语种';
$myTools ['by_nation'] = '按民族';
$myTools ['by_academic'] = '按学历';
$myTools ['by_age'] = '按年龄';
$myTools ['by_worktype'] = '按工作性质';
//$myTools ['by_contract'] = '按劳动合同';
//$myTools ['by_insurance'] = '按劳动保险';


$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = ($strAction == $key ? 'class="selected"' : '');
	$html .= '<li  ' . $strClass . '><a href="user_stat.php?action=' . trim ( $key ) . '"><em>' . $value . '</em></a></li>';
}
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

switch ($strAction) {
	case 'by_sex' :
		$sql = "SELECT (CASE sex WHEN 1 THEN '先生' WHEN 2 THEN '女士' END) AS sextilte,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " GROUP BY sex";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按性别', $data, "Pie3D", 480, 420 );
		break;
	case 'by_grade' :
		$sql = "SELECT grade,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND grade IS NOT NULL AND grade<>'' GROUP BY grade";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按等级', $data, "Pie3D", 480, 420 );
		break;
	case 'by_lang' :
		$sql = "SELECT lang,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND lang IS NOT NULL AND lang<>'' GROUP BY lang";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按语种', $data, "Pie3D", 480, 420 );
		break;
	case 'by_nation' :
		$sql = "SELECT nation,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND nation IS NOT NULL AND nation<>'' GROUP BY nation";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按民族', $data, "Pie3D", 480, 420 );
		break;
	case 'by_academic' :
		$sql = "SELECT academic,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND academic IS NOT NULL AND academic<>'' GROUP BY academic";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按学历', $data, "Pie3D", 480, 420 );
		break;
	case 'by_age' :
		$data ['10-19岁'] = _cnt_user_by_age ( 10, 19 );
		$data ['20-29岁'] = _cnt_user_by_age ( 20, 29 );
		$data ['30-39岁'] = _cnt_user_by_age ( 30, 39 );
		$data ['40-49岁'] = _cnt_user_by_age ( 40, 49 );
		$data ['50-59岁'] = _cnt_user_by_age ( 50, 59 );
		$data ['60-69岁'] = _cnt_user_by_age ( 60, 69 );
		$data ['70-79岁'] = _cnt_user_by_age ( 70, 79 );
		$data ['80-89岁'] = _cnt_user_by_age ( 80, 89 );
		Statistics::display_stat_chart ( '按年龄', $data, "Pie3D", 480, 420 );
		break;
	case 'by_worktype' :
		$sql = "SELECT work_type,COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND work_type IS NOT NULL AND work_type<>'' GROUP BY work_type";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按工作性质', $data, "Pie3D", 480, 420 );
		break;
	case 'by_contract' :
		$sql = "SELECT (CASE is_sign_contract WHEN 1 THEN '已签订' WHEN 0 THEN '未签订' END),COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND is_sign_contract IS NOT NULL GROUP BY is_sign_contract";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按是否签订劳动合同', $data, "Pie3D", 480, 420 );
		break;
	case 'by_insurance' :
		$sql = "SELECT (CASE is_insurance1 WHEN 1 THEN '已缴纳' WHEN 0 THEN '未缴纳' END),COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND is_insurance1 IS NOT NULL GROUP BY is_insurance1";
		$data = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		Statistics::display_stat_chart ( '按是否缴纳劳动保险', $data, "Pie3D", 480, 420 );
		break;
}

function _cnt_user_by_age($start, $end, $sqlwhere = '') {
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	//$this_year=date('Y');
	//$sql = "SELECT COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND birthday IS NOT NULL AND {$this_year} - YEAR(birthday) BETWEEN " . intval ( $start ) . " AND " . intval ( $end );
	$sql = "SELECT COUNT(*) AS cnt FROM $tbl_user WHERE status=" . STUDENT . " AND age BETWEEN " . intval ( $start ) . " AND " . intval ( $end );
	if ($sqlwhere) $sql .= $sqlwhere;
	//echo $sql.'<br/>';
	return Database::getval ( $sql, __FILE__, __LINE__ );
}

echo '</div></div></div>';
Display::display_footer ( TRUE );