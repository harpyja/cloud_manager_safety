<?php
/*
 ==============================================================================
 教学大纲的编辑与显示
 ==============================================================================
 */
$language_file = array ('course_description' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );

$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header(null,FALSE);

$description_id = isset ( $_REQUEST ['description_id'] ) ? intval ( getgpc ( 'description_id' ) ) : 14;

$sql = "SELECT description, description1, description2,description3,description4,description5,description6,description7,description8,description9,description10,description11,description12,description13 FROM " . $tbl_course . " WHERE code=" . Database::escape ( api_get_course_code () );
//echo $sql;
list ( $description, $description1, $description2,$description3,$description4,$description5,$description6,$description7,$description8,$description9,$description10,$description11,$description12,$description13 ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
//var_dump($_GET['cidReq']);
$lessonid=getgpc('cidReq','G');
$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li  ' . ($description_id == 10 ? 'class="selected"' : '') . '><a href="../admin/course/course_edit.php?cidReq='.$lessonid.'&description_id=' . 10 . '"><em>' . get_lang ( '课程设置' ) . '</em></a></li>';

$html .= '<li  ' . ($description_id == 0 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 0 . '"><em>' . get_lang ( 'GeneralDescription' ) . '</em></a></li>';
//$html .= '<li  ' . ($description_id == 1 ? 'class="selected"' : '') . '><a href="index.php?description_id=' . 1 . '"><em>' . get_lang ( 'Objectives' ) . '</em></a></li>';
//$html .= '<li  ' . ($description_id == 2 ? 'class="selected"' : '') . '><a href="index.php?description_id=' . 2 . '"><em>' . get_lang ( 'Topics' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 8 . '"><em>' . get_lang ( 'Sybzh' ) . '</em></a></li>';
//$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="step.php?cidReq='.$lessonid.'"><em>' . get_lang ( '模拟仿真实验' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 7 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 7 . '"><em>' . get_lang ( '教学大纲' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 14 ? 'class="selected"' : '') . '><a href="lessontop.php?cidReq='.$lessonid.'"><em>' . get_lang ( 'Topology') . '</em></a></li>';

//$html .= '<li style="float:right">' . link_button ( 'edit.gif', 'Edit', 'desc_update.php?action=edit&description_id=' . $description_id, '100%', '100%' ) . '</li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;




header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');

//require_once (api_get_path ( LIBRARY_PATH ) . 'networkmap.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$lessonid = getgpc('cidReq','G');

$top_id = "SELECT description13 FROM  `vslab`.`course` WHERE  `course`.`code` = '{$lessonid}'";


$result = api_sql_query ( $top_id, __FILE__, __LINE__ );

$vmid = Database::fetch_row ( $result);
$_SESSION['net_id'] = $vmid[0];
//var_dump($vmid);
$networkmap = Database::get_main_table ( networkmap);

$sql = "select id,xml  FROM  $networkmap   WHERE id = '{$vmid[0]}'";

$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm = Database::fetch_row ( $res);

echo "<iframe src='lessontop2.php?cidReq=$lessonid'  width='100%' style='min-height:640px'></iframe>";
echo '</div></div></div>';

Display::display_footer ();
?>
