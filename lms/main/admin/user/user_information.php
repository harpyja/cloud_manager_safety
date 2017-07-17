<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;
include ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();
if (! isset ( $_GET ['user_id'] )) {
	api_not_allowed ();
}

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$deptObj = new DeptManager ();

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$user = api_get_user_info ( $_GET ['user_id'] );
$tool_name = $user ['firstName'] . ' ' . $user ['lastName'] . (empty ( $user ['official_code'] ) ? '' : ' (' . $user ['official_code'] . ')');

Display::display_header ( $tool_name, FALSE );

$get_tab=  getgpc("tabAction");
$tabAction = (isset ( $get_tab ) ? $get_tab : 'General');
$myTools ['General'] = array ($user ['username'] . ' - ' . get_lang ( 'GeneralInfo' ), 'students.gif' );
$myTools ['Course'] = array ($user ['username'] . ' - ' . get_lang ( 'CourseListSubAndArrange' ), 'courses.gif' );

$tabAction = (isset ( $get_tab ) ? $get_tab : 'General');
$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = ($tabAction == $key ? 'class="selected"' : '');
        $g_user_id=  intval(getgpc("user_id"));
	$html .= '<li  ' . $strClass . '><a href="user_information.php?user_id=' .$g_user_id . '&tabAction=' . $key . '"><em>' . $value [0] . '</em></a></li>';
}
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

if ($tabAction == "General") {
	?>
<table width="100%">
	<tr>
		<td width="15%" valign="top"><?php
	$image = $user ['picture_uri'];
	$image_file = ((! empty ( $image ) && file_exists ( api_get_path ( SYS_PATH ) . "storage/users_picture/{$image}" )) ? api_get_path ( WEB_PATH ) . "storage/users_picture/{$image}" : api_get_path ( WEB_IMG_PATH ) . 'unknown.jpg');
	echo '<p><img src="' . $image_file . '" style="width:120px;"/></p>';
	//echo '<input type="button" value="'.get_lang('Edit').'" class="inputSubmitShort" onclick="location.href=\'user_edit.php?user_id='.getgpc('user_id','G').'\';">';
	echo '<button type="button" value="' . get_lang ( 'Edit' ) . '" class="simple" onclick="location.href=\'user_edit.php?user_id=' . getgpc ( 'user_id', 'G' ) . '\';">' . get_lang ( 'Edit' ) . "</button>";
	?> <!-- <button type="button" class="add">add</button><button type="button" class="save">save</button>
<button type="button" class="cancel">cancel</button><button type="button" class="refresh">refresh</button>
<button type="button" class="upload">upload</button><button type="button" class="search">search</button>
<button type="button" class="login">login</button><button type="button" class="plus">plus</button>
<button type="button" class="minus">minus</button><button type="button" class="next">next</button>
<button type="button" class="back">back</button> --></td>
		<td>
		<table class="data_table">
			<tr class="row_odd">
				<th width="20%" colspan="4" align="left"><?=$tool_name?></th>
			</tr>
			<tr>
				<td><?=get_lang ( 'UserName' )?></td>
				<td><?=$user ['username']?></td>
				<td><?=get_lang ( 'FirstName' )?></td>
				<td><?=$user ['firstname']?></td>
			</tr>

			<!-- 	<tr>
					<td><?=get_lang ( 'OfficialCode' )?></td>
				<td><?=$user ['official_code']?></td>
				<td><?=get_lang ( 'EnglishName' )?></td>
				<td><?=$user ['en_name']?></td>
			</tr> -->

			<tr>
				<td><?=get_lang ( 'Sex' )?></td>
				<td><?=$user ['sex']?></td>
				<td><?=get_lang ( 'JobTitle' )?></td>
				<td><?=$user ['lastName']?></td>
			</tr>

			<tr>
				<td><?=get_lang ( 'Email' )?></td>
				<td><?=Display::encrypted_mailto_link ( $user ['mail'], $user ['mail'] )?></td>
				<td><?=get_lang ( 'IDCard' )?></td>
				<td><?=$user ['credential_no']?></td>
			</tr>

			<tr>
				<td><?=get_lang ( 'PhoneNumber' )?></td>
				<td><?=$user ['phone']?></td>
				<td><?=get_lang ( 'MobilePhone' )?></td>
				<td><?=$user ['mobile']?></td>
			</tr>


			<!-- <tr>
				<td><?=get_lang ( 'RegistrationDate' )?></td>
				<td><?=$user ['registration_date']?></td>
				<td><?=get_lang ( 'ExpirationDate' )?></td>
				<td><?=$user ['expiration_date'] == '0000-00-00 00:00:00' ? get_lang ( 'NeverExpires' ) : $user ['expiration_date']?></td>
			</tr>


			<tr>
				<td><?=get_lang ( 'ActiveAccount' )?></td>
				<td><?=$user ['active'] == 1 ? get_lang ( 'Active' ) : get_lang ( 'Inactive' )?></td>
				<td><?=get_lang ( 'UserType' )?></td>
				<td><?=($user ['status'] == 1 ? get_lang ( 'Teacher' ) : get_lang ( 'Student' ))?></td>
			</tr> -->
<!--
			<tr>
				<td><?php //get_lang ( 'UserInDept' )	?>所在单位</td>
				<td colspan="3"><?=get_dept_path ( $user ['dept_id'], FALSE, TRUE );?></td>
			</tr>
			<!-- <tr>
				<td><?=get_lang ( 'Remark' )?></td>
				<td colspan="3"><?=$user ['description']?></td>
			</tr> -->
			<?php
	if ($_configuration ['enable_user_ext_info']) {
		?><!--
			<tr>
				<td><?=get_lang ( 'Age' )?></td>
				<td><?=$user ['age'] ? $user ['age'] : '未设置'?></td>
				<td>资格证号</td>
				<td><?=$user ['certificate_no_qualification']?></td>
			</tr>
			<tr>
				<td>等级证号</td>
				<td><?=$user ['certificate_no_grade']?></td>
				<td>等级</td>
				<td><?=$user ['grade']?></td>
			</tr>
			<tr>
				<td>年审日期</td>
				<td><?=$user ['annual_auditing_date']?></td>
				<td>发卡日期</td>
				<td><?=$user ['issue_date']?></td>
			</tr>
			<tr>
				<td>语种</td>
				<td><?=$user ['lang']?></td>
				<td>QQ号码:</td>
				<td><?=$user ['qq']?></td>
			</tr>
			<tr>
				<td>民族</td>
				<td><?=$user ['nation']?></td>
				<td>学历</td>
				<td><?=$user ['academic']?></td>
			</tr>
			<tr>
				<td>工作性质</td>
				<td><?=$user ['work_type']?></td>
				<td>劳动合同</td>
				<td><?=$user ['is_sign_contract'] ? '有' : '无'?></td>
			</tr>
			<tr>
				<td>医疗保险金</td>
				<td><?=$user ['is_insurance1'] ? '有' : '无'?></td>
				<td>养老保险金</td>
				<td><?=$user ['is_insurance2'] ? '有' : '无'?></td>
			</tr>

			<tr>
				<td>失业保险金</td>
				<td><?=$user ['is_insurance3'] ? '有' : '无'?></td>
				<td>免考条件</td>
				<td><?=$user ['avoid_exam'] ?></td>
			</tr>-->
			<?php
	}
	?>
		</table>
		</td>
	</tr>
</table>

<?php
}

// 显示用户所注册的课程
if ($tabAction == "Course") {
	$header [] = array (get_lang ( 'CourseTitle' ), true );
	$header [] = array (get_lang ( 'Code' ), true );
	$header [] = array (get_lang ( 'ValidLearningDate' ), true );
	$header [] = array (get_lang ( 'RegistrationDate' ), true );
	$header [] = array (get_lang ( 'CourseType' ), true );
	$header [] = array (get_lang ( 'LearningState' ), true );
	$sql = 'SELECT cu.*,c.title,c.code FROM ' . $table_course_user . ' cu, ' . $table_course . ' c WHERE cu.user_id = ' . $user ['user_id'] . ' AND cu.course_code = c.code ORDER BY begin_date DESC';
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$data = array ();
	while ( $course = Database::fetch_object ( $res ) ) {
		$row = array ();
		$row [] = $course->title;
		$row [] = $course->code;
		$row [] = $course->begin_date . ' ' . get_lang ( "To" ) . ' ' . $course->finish_date;
		$row [] = substr ( $course->creation_time, 0, 10 );
		$row [] = ($course->is_required_course == 1 ? get_lang ( "RequiredCourse" ) : get_lang ( "OpticalCourse" ));
		$row [] = get_learning_status ( $course->is_pass );
		$data [] = $row;
	}
	echo Display::display_table ( $header, $data );
}
echo '</div></div></div>';
Display::display_footer ();
