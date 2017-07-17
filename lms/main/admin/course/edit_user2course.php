<?php
/**
 ==============================================================================
 ==============================================================================
 */
$language_file = array ('admin', 'userInfo' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

api_protect_admin_script ();

$user_id = intval(getgpc ( 'user_id' ));
$action = getgpc ( "action" );
$code = getgpc ( "code" );
if (empty ( $user_id ) or empty ( $code )) tb_close ();

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

if (isset ( $_POST ['formSent'] ) && is_equal ( $_POST ['formSent'], "1" )) {
	if (isset ( $_POST ["user_id"] ) && isset ( $_POST ["code"] )) {
		$sql_data = array ("begin_date" => getgpc ( "begin_date", "P" ), "finish_date" => getgpc ( "finish_date", "P" ), "is_required_course" => getgpc ( "is_required_course", "P" ) );
		$sql_data ['is_pass'] = (isset ( $_POST ['passCourse'] ) ? trim ( getgpc('passCourse') ) : 0);
		$sql_data ['role'] = trim ( getgpc('role') );
		$sqlwhere = " course_code='" . escape ( getgpc ( "code" ), "P" ) . "' AND user_id='" . intval(escape ( getgpc ( "user_id", "P" ) )) . "'";
		$sql = Database::sql_update ( $tbl_course_user, $sql_data, $sqlwhere );
		//echo $sql;exit;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$redirect_url = "course_subscribe_user_list.php?code=" . $code;
		$redirect_url = 'course_user_manage.php';
		tb_close ();
	}
} else {
	$sql = "SELECT t1.user_id,t1.is_required_course,t1.begin_date,t1.finish_date,t2.username FROM " . $tbl_course_user . " AS t1 LEFT JOIN " . $tbl_user . " AS t2 ON t1.user_id=t2.user_id WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.course_code=" . Database::escape ( $code );
	$info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
}

$htmlHeadXtra [] = import_assets ( "select.js" );

$tool_name = get_lang ( 'ArrangeCourse' ) . ' - ' . $info ["username"] . '';
Display::display_header ( $tool_name, FALSE );

?>
<form name="theForm" method="post" action="edit_user2course.php"><input
	type="hidden" name="formSent" value="1" /><input type="hidden"
	name="code" value="<?=$code?>" /> <input type="hidden"
	value="<?=$user_id?>" name="user_id" />
<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="98%">
	<!-- 	<tr>
		<th class="formTableTh" colspan="3"><?=$tool_name?></th>
	</tr> -->

	<tr class="containerBody">
		<td class="formLabel"><span class="form_required">*</span><?=get_lang ( "ValidLearningDate" )?></td>
		<td class="formTableTd" align="left">
		<div id="append_parent"></div>
		<script src="<?=api_get_path ( WEB_JS_PATH )?>js_calendar.js"
			type="text/javascript"></script> <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="begin_date" value="<?php
			echo $info ["begin_date"];
			?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="begin_date" />&nbsp;至&nbsp; <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="finish_date" value="<?php
			echo $info ["finish_date"];
			?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="finish_date" /></td>
	</tr>

	<?php
	$elemnt_html = form_radio ( "is_required_course", "1", $info ["is_required_course"] == 1, 'id="is_required_course1"' );
	$elemnt_html .= form_label ( get_lang ( "RequiredCourse" ), "is_required_course1" ) . "&nbsp;&nbsp;&nbsp;&nbsp;";
	$elemnt_html .= form_radio ( "is_required_course", "0", $info ["is_required_course"] == 0, 'id="is_required_course0"' );
	$elemnt_html .= form_label ( get_lang ( "OpticalCourse" ), "is_required_course0" );
	echo Display::table_tr ( get_lang ( "CourseType" ), $elemnt_html );
	
	$elem_html = form_checkbox ( "passCourse", "1", FALSE, 'id="passCourse" class="checkbox"' );
	$elem_html .= form_label ( get_lang ( 'IsPassCourse' ), "passCourse" );
	echo Display::table_tr ( get_lang ( 'PassCourse' ), $elem_html );
	
	//只有学习完课程才会出现
	$elem_html = '<textarea name ="role" cols=50 rows=5 class="inputText">' . $mainUserInfo ['role'] . '</textarea>';
	echo Display::table_tr ( get_lang ( 'TutorComment' ), $elem_html );
	
	$elem_html = '<input	type="submit" name="removeCourse" class="inputSubmit"	value="' . get_lang ( "Ok" ) . '" onclick="validate()" />&nbsp;&nbsp; <button class="cancel" type="button"
			onclick="javascript:self.parent.tb_remove();">' . get_lang ( 'Cancel' ) . '</button>';
	echo Display::table_tr ( "", $elem_html );
	?>

</table>
<br>
</form>
<?php

Display::display_footer ();
?>