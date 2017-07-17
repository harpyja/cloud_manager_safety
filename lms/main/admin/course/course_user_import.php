<?php
/*
==============================================================================
	导入课程与用户的关系
==============================================================================
*/

function validate_data($users_courses) {
	$errors = array ();
	$coursecodes = array ();
	foreach ( $users_courses as $index => $user_course ) {
		$user_course ['line'] = $index + 1;
		
		//1. check if mandatory fields are set
		$mandatory_fields = array ('UserName', 'CourseCode' );
		foreach ( $mandatory_fields as $key => $field ) {
			if (! isset ( $user_course [$field] ) || strlen ( $user_course [$field] ) == 0) {
				$user_course ['error'] = get_lang ( $field . 'Mandatory' );
				$errors [] = $user_course;
			}
		}
		
		//2. check if coursecode exists
		if (isset ( $user_course ['CourseCode'] ) && strlen ( $user_course ['CourseCode'] ) != 0) {
			//2.1 check if code allready used in this CVS-file
			if (! isset ( $coursecodes [$user_course ['CourseCode']] )) {
				//2.1.1 check if code exists in DB
				$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
				$sql = "SELECT * FROM $course_table WHERE code = '" . Database::escape_string ( $user_course ['CourseCode'] ) . "'";
				$res = api_sql_query ( $sql, __FILE__, __LINE__ );
				if (Database::num_rows ( $res ) == 0) {
					$user_course ['error'] = get_lang ( 'CodeDoesNotExists' );
					$errors [] = $user_course;
				} else {
					$coursecodes [$user_course ['CourseCode']] = 1;
				}
			}
		}
		
		//3. check if username exists
		if (isset ( $user_course ['UserName'] ) && strlen ( $user_course ['UserName'] ) != 0) {
			if (UserManager::is_username_available ( $user_course ['UserName'] )) {
				$user_course ['error'] = get_lang ( 'UnknownUser' );
				$errors [] = $user_course;
			}
		}
	
	}
	return $errors;
}

function save_data($users_courses,$type) {
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );
	$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$csv_data = array ();
	foreach ( $users_courses as $index => $user_course ) {
		$sql = "SELECT user_id FROM $user_table u WHERE u.username = " . Database::escape ( $user_course ['UserName'] );
		$user_id = Database::get_scalar_value ( $sql );
		CourseManager::subscribe_user2course ( $user_id, $user_course ['CourseCode'], $user_course ['IsRequiredCourse'], 
		excelTime($user_course ['StartDate']), excelTime($user_course ['EndDate']), $type );
	}
}


$language_file = array ('admin', 'registration' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'import.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

$tool_name = get_lang ( 'AddUsersToACourse' ) . ' CSV';
$htmlHeadXtra [] = Display::display_thickbox ();

$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$form = new FormValidator ( 'course_user_import' );
//$form->addElement ( 'header', 'header', get_lang ( 'AddUsersToACourse' ) . ' CSV' );

$form->addElement ( 'file', 'import_file', get_lang ( 'ImportFileLocation' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'import_file', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$allowed_file_types = array ('xls' );
$form->addRule ( 'import_file', get_lang ( 'InvalidExtension' ) . ' (' . implode ( ',', $allowed_file_types ) . ')', 'filetype', $allowed_file_types );

$group = array ();
$group [] = $form->createElement ( 'radio', 'type', null, '如果导入模板中数据在系统中已存在,则忽略处理','add');
$group [] = $form->createElement ( 'radio', 'type', '', '如果导入模板中数据在系统中已存在,则覆盖处理','replace' );
$form->addGroup ( $group, null, get_lang ( 'ImportOptions' ), '<br/>', false );
$defaults ['type'] = "add";

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	set_time_limit ( 0 );
	//储存的是UserName,CourseCode,Status 的数组(此为一个数据元素,即一行CSV数据)
	$file = $_FILES ['import_file'] ['tmp_name'];
	$data = Import::parse_to_array ( $file, 'xls' );
	$users_courses = $data ['data'];
	$errors = validate_data ( $users_courses );
	if (count ( $errors ) == 0) {
		save_data ( $users_courses,getgpc('type','P') );
		$redirect_url = 'course_user_import.php?action=show_message&message=' . urlencode ( get_lang ( 'FileImported' ) );
		api_redirect ( $redirect_url );
	}
}

Display::display_header ( $tool_name ,FALSE);

if (count ( $errors ) != 0) {
	$error_message = '<ul>';
	foreach ( $errors as $index => $error_course ) {
		$error_message .= '<li>' . get_lang ( 'Line' ) . ' ' . $error_course ['line'] . ': <b>' . $error_course ['error'] . '</b>: ';
		$error_message .= $error_course ['UserName'] . ',&nbsp;' . $error_course ['CourseCode'] . ',&nbsp;' . $error_course ['IsRequiredCourse'];
		$error_message .= '</li>';
	}
	$error_message .= '</ul>';
	Display::display_error_message ( $error_message, false );
}

if (isset ( $_GET ['action'] ) && $_GET ['action'] = 'show_message') {
	Display::display_normal_message ( stripslashes (getgpc("message","G") ) );
}

$temp = 'storage/examples/import_files/tpl_import_course_user.xls';
if (is_file ( api_get_path ( SYS_PATH ) . $temp )) {
	$url_csv = api_get_path ( WEB_PATH ) . $temp;
} else {
	$url_csv = api_get_path ( WEB_PATH ) . 'storage/examples/import_files/tpl_import_course_user.xls';
}

echo '<div style="float:left"><input alt="#TB_inline?height=150&amp;width=400&amp;inlineId=myOnPageContent" title="' . get_lang ( 'UserGuideAndNotice' ) . '" class="thickbox" type="button" value="' . get_lang ( "UserGuideAndNotice" ) . '" />
<a href="'.$url_csv.'">下载模板</a></div>';
echo '<div style="clear:both">';
echo '<div id="myOnPageContent" style="display:none"><p>' . get_lang ( 'CourseUserImportNotes' ) . '</p></div>';
$form->display ();
?>
<table align="center" width="60%">
	<tr>
		<td>
		<div id="cvs" style="display: block">
		<p>xls文件头说明: &nbsp;&nbsp;&nbsp;&nbsp;</p>
		<blockquote><b>UserName</b>:登录名<br />
		<b>CourseCode</b>:课程编号<br />
		<b>IsRequredCourse</b>:是否为必修课课程,1表示是,0表示 否 <br />
		<b>StartDate</b>:学习期限,起始时间,格式:YYYY-mm-dd<br />
		<b>EndDate</b>:学习期限,结束时间,格式:YYYY-mm-dd<br />
		</blockquote>
		</div>
		</td>
	</tr>
</table>
<?php
Display::display_footer ();
