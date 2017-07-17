<?php
/*
 ==============================================================================
 课程用户详细信息
 ==============================================================================
 */

$language_file = array ('registration', 'userInfo', 'admin' );
require_once ("../inc/global.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
api_protect_course_script ();
$tbl_coursUser = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$nameTools = get_lang ( "UserInfo" );
if (isset ( $_REQUEST ['editMainUserInfo'] )) $editMainUserInfo = getgpc ( 'editMainUserInfo' ); //要编辑的用户ID
if (isset ( $_REQUEST ['uInfo'] )) $uInfo =  getgpc('uInfo','R') ;
$userIdViewed = $uInfo; // Id of the user we want to view coming from the user.php
$courseCode = $currentCourseID = api_get_course_code ();
$userIdViewer = api_get_user_id (); // id fo the user currently online
$allowedToEditContent = ($userIdViewer == $userIdViewed) || $is_platformAdmin;
$allowedToEdit = api_is_allowed_to_edit ();
$is_allowedToTrack = api_is_allowed_to_edit () && $_configuration ['tracking_enabled'];

$course_code = api_get_course_code ();
$course = CourseManager::get_course_information ( $course_code );

foreach ( $_POST as $key => $value ) {
	$$key = replace_dangerous_char ( $value );
}

if ($allowedToEdit) {
	if (isset ( $_REQUEST ['submitMainUserInfo'] )) {
		$submitMainUserInfo = getgpc ( "submitMainUserInfo" );
		$promoteCourseAdmin = getgpc ( "promoteCourseAdmin" );
		$promoteTutor = getgpc ( "promoteTutor" );
		$passCourse = getgpc ( "passCourse" );
		
		$userIdViewed = $submitMainUserInfo;
		$promoteTutor ? $userProperties ['tutor'] = 1 : $userProperties ['tutor'] = 0;
		$promoteCourseAdmin ? $userProperties ['status'] = COURSEMANAGER : $userProperties ['status'] = STUDENT;
		$userProperties ['role'] = $role;
		$passCourse ? $userProperties ['is_pass'] = 1 : $userProperties ['is_pass'] = 0;
		update_user_course_properties ( $userIdViewed, $courseCode, $userProperties );
		tb_close ();
	}
}

Display::display_header ( NULL, FALSE );
//管理员编辑
if ($editMainUserInfo) {
	$userInfo = UserManager::get_user_info_by_id ( $editMainUserInfo, TRUE );
	$mainUserInfo = get_main_user_info ( $editMainUserInfo, $courseCode );
	if ($mainUserInfo) {
		($mainUserInfo ['status'] == 1) ? $courseAdminChecked = "checked" : $courseAdminChecked = "";
		($mainUserInfo ['tutor_id'] == 1) ? $tutorChecked = "checked" : $tutorChecked = "";
		($mainUserInfo ['is_pass'] == 1) ? $passChecked = "checked" : $passChecked = "";
		
		echo '<div style="float:left">';
		if ($mainUserInfo ['picture'] != '') {
			$size = @ getImageSize ( api_get_path ( SYS_PATH ) . 'storage/users_picture/' . $mainUserInfo ['picture'] );
			$vertical_space = (($size [1] > 200) ? 'height="200"' : '');
			echo "<img src=\"" . api_get_path ( WEB_PATH ) . 'storage/users_picture/' . $mainUserInfo ['picture'] . "\" $vertical_space border=\"1\">";
		} else {
			Display::display_icon ( 'unknown.jpg', get_lang ( 'Unknown' ) );
		}
		echo '</div>';
		
		echo '<div style="float:right;width:85%">';
		echo "<form action=\"" . $_SERVER ['PHP_SELF'] . "\" method=\"post\">\n", "<input type=\"hidden\" name=\"submitMainUserInfo\" value=\"$editMainUserInfo\" />\n";
		echo '<table align="center" width="98%" cellpadding="4" cellspacing="0">';
		echo '<tr><th class="formTableTh" colspan="2">' . $nameTools . '</th></tr>';
		
		$elem_html = htmlize ( $mainUserInfo ['firstName'] ) . ' &nbsp;&nbsp;' . Display::encrypted_mailto_link ( $mainUserInfo ['email'], $mainUserInfo ['email'] );
		echo Display::table_tr ( get_lang ( 'Name' ), $elem_html );
		
		echo '<tr class="containerBody"><td class="formLabel">', get_lang ( 'InDept' ), '</td>';
		echo '<td class="formTableTd">' . $userInfo ['dept_path'] . "</td>";
		echo "</tr>";
		
		/* if ($allowedToEdit && $course ["is_subscribe_enabled"] && api_get_user_id () != $editMainUserInfo) {
			$elem_html = "<input class=\"checkbox\" type=\"checkbox\" name=\"promoteCourseAdmin\" value=\"1\"" . $courseAdminChecked . " />\n";
			$elem_html .= get_lang ( 'CourseManager' );
		} else {
			$elem_html = get_lang ( 'CourseManager' );
		}
		echo Display::table_tr ( get_lang ( 'Role' ), $elem_html ); */
		
		if ($allowedToEdit) {
			$elem_html = "<input class=\"checkbox\" type=\"checkbox\" id=\"passCourse\" name=\"passCourse\" value=\"1\"" . $passChecked . " />\n";
			$elem_html .= '<label for="passCourse">' . get_lang ( 'IsPassCourse' ) . "</label>";
		} else {
			$elem_html = get_lang ( 'Passed' );
		}
		echo Display::table_tr ( get_lang ( 'PassCourse' ), $elem_html );
		
		$elem_html = '<textarea name ="role" cols=50 rows=5 class="inputText">' . $mainUserInfo ['role'] . '</textarea>';
		echo Display::table_tr ( get_lang ( 'TutorComment' ), $elem_html );
		
		$elem_html = "<input type=\"submit\" class=\"inputSubmit\" name=\"submit\" value=\"" . get_lang ( 'Ok' ) . "\" />";
		$elem_html .= '&nbsp;&nbsp; <button type="button" onclick="javascript:self.parent.tb_remove();" class="cancel">' . get_lang ( "Cancel" ) . '</button>';
		echo Display::table_tr ( "", $elem_html );
		
		echo "</table>", "</form>\n";
		
		echo '</div>';
	}
} else // 默认显示
{
	$virtual_course_code = getgpc("virtual_course");
	if (isset ( $virtual_course_code )) {
		$courseCode = $virtual_course_code;
		$allowedToEdit = false;
	}
	
	$mainUserInfo = get_main_user_info ( $userIdViewed, $courseCode );
	$userInfo = UserManager::get_user_info_by_id ( $userIdViewed, TRUE );
	
	if ($mainUserInfo) {
		echo '<div style="float:left">';
		if ($mainUserInfo ['picture'] != '' && file_exists ( api_get_path ( SYS_PATH ) . "storage/users_picture/" . $mainUserInfo ['picture'] )) {
			$size = @ getImageSize ( api_get_path ( SYS_PATH ) . 'storage/users_picture/' . $mainUserInfo ['picture'] );
			$vertical_space = (($size [1] > 200) ? 'height="200"' : '');
			echo "<img src=\"" . api_get_path ( WEB_PATH ) . 'storage/users_picture/' . $mainUserInfo ['picture'] . "\" $vertical_space border=\"1\">";
		} else {
			Display::display_icon ( 'unknown.jpg', get_lang ( 'UnknownUser' ) );
		}
		echo '</div>';
		
		echo '<div style="float:right;width:70%">';
		echo '<table align="center" width="98%" cellpadding="4" cellspacing="0">';
		echo '<tr><th class="formTableTh" colspan="2">' . $nameTools . '</th></tr>';
		echo '<tr class="containerBody"><td class="formLabel">' . get_lang ( 'Name' ) . '</td><td class="formTableTd">' . htmlize ( $mainUserInfo ['firstName'] ) . ' &nbsp;&nbsp;' . Display::encrypted_mailto_link ( $mainUserInfo ['email'], $mainUserInfo ['email'] ) . '</td></tr>';
		if ($allowedToEdit) {
			echo '<tr class="containerBody"><td class="formLabel">', get_lang ( 'Description' ), '</td><td class="formTableTd">' . htmlize ( $mainUserInfo ['role'] ) . '</td></tr>';
		}
		
		echo '<tr class="containerBody"><td class="formLabel">', get_lang ( 'CourseAdmin' ), '</td>';
		if ($mainUserInfo ['is_course_admin'] == 1) {
			echo '<td class="formTableTd">' . get_lang ( "Yes" ) . "</td>";
		} else {
			echo '<td class="formTableTd">' . get_lang ( "No" ) . "</td>\n";
		}
		echo "</tr>";
		
		echo '<tr class="containerBody"><td class="formLabel">', get_lang ( 'InDept' ), '</td>';
		echo '<td class="formTableTd">' . $userInfo ['dept_path'] . "</td>";
		echo "</tr>";
		
		echo '<tr class="containerBody"><td class="formLabel">', get_lang ( 'Actions' ), '</td><td class="formTableTd">';
		if ($allowedToEdit) {
			echo "<a href=\"" . $_SERVER ['PHP_SELF'] . "?editMainUserInfo=$userIdViewed\">", Display::return_icon ( 'edit.gif' ), "</a>";
		}
		if ($is_allowedToTrack) {
			//echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"../reporting/myStudents.php?origin=user_course&student=$userIdViewed&details=true&course=" . $_course ['id'] . "\" target='_parent'>", Display::return_icon ( 'statistics.gif', get_lang ( 'Tracking' ) . ' : ' . $userIdViewed ), "</a>";
		}
		echo "</td></tr>";
		
		echo "</table>";
		
		echo '</div>';
	}
}

function update_user_course_properties($user_id, $course_code, $properties) {
	global $tbl_coursUser, $_user;
	$sql_data = array ();
	
	if ($user_id != $_user ['user_id']) $sql_data ['status'] = $properties ['status'];
	$sql_data ['role'] = $properties ['role'];
	$sql_data ['tutor_id'] = $properties ['tutor'];
	$sql_data ['is_pass'] = $properties ['is_pass'];
	$sql_where = "user_id= '" . $user_id . "' AND course_code='" . $course_code . "'";
	$sql = Database::sql_update ( $tbl_coursUser, $sql_data, $sql_where );
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	return (mysql_affected_rows () > 0);
}

function get_main_user_info($user_id, $courseCode) {
	if (empty ( $user_id ) or empty ( $courseCode )) return false;
	$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	$sql = "SELECT	u.firstname firstName,
	                u.email, u.picture_uri picture, cu.role, 
	                cu.`status` `status`, cu.tutor_id,cu.is_course_admin,cu.is_pass
	        FROM    $table_user u, $table_course_user cu
	        WHERE   u.user_id = cu.user_id
	        AND     u.user_id = '$user_id'
	        AND     cu.course_code = '$courseCode'";
	$userInfo = Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
	return $userInfo;
}

function htmlize($phrase) {
	return nl2br ( htmlspecialchars ( $phrase ) );
}

Display::display_footer ();
?>