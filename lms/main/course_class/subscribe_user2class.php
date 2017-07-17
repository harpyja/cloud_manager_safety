<?php
/*
 ==============================================================================
 往班级中添加学生
 ==============================================================================
 */
$language_file = array ('class_of_course' );
require_once ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

api_block_anonymous_users ();
api_protect_course_script ();

$is_allowed_edit = api_is_allowed_to_edit ();
$tbl_class = $table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );

$course = getgpc ( 'course', 'G' );
$class_id = getgpc ( 'idclass', 'G' );
$form_sent = 0;
$error_message = '';
$first_letter_left = '';
$first_letter_right = '';
$left_user_list = array ();
$right_user_list = array ();

$class_info = CourseClassManager::get_class_info ( $class_id );
$class_name = $class_info ['name'];
if (! $class_info) tb_close ( 'class_list.php' );

$all_classes = CourseClassManager::get_all_classes_info (null,false);

$htmlHeadXtra [] = "<script type='text/javascript'>
function sendForm(formSent){
	document.formulaire.formSent.value='2';
	if (formSent)		 document.formulaire.submit();
}
function moveItem(origin , destination){
	for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			destination.options[destination.length] = new Option(origin.options[i].text,origin.options[i].value);
			origin.options[i]=null;
			i = i-1;
		}
	}
	destination.selectedIndex = -1;
	sortOptions(destination.options);
}

function moveLeft2Right(){
	moveItem(document.getElementById('LeftUserList'), document.getElementById('RightUserList'));
}

function moveRight2Left(){
	moveItem(document.getElementById('RightUserList'), document.getElementById('LeftUserList'))
}

function sortOptions(options) {
	newOptions = new Array();
	for (i = 0 ; i<options.length ; i++)
		newOptions[i] = options[i];

	newOptions = newOptions.sort(mysort);
	options.length = 0;
	for(i = 0 ; i < newOptions.length ; i++)
		options[i] = newOptions[i];
}

function mysort(a, b){
	if(a.text.toLowerCase() > b.text.toLowerCase()){
		return 1;
	}
	if(a.text.toLowerCase() < b.text.toLowerCase()){
		return -1;
	}
	return 0;
}

function valide(){
	var options = document.getElementById('RightUserList').options;
	for (i = 0 ; i<options.length ; i++)
		options[i].selected = true;
	document.formulaire.submit();
}
</script>";

//已注册到该班级中的用户
function get_users_subscirbed2class($class_id) {
	global $table_course_user;
	$sql = "select t1.user_id from {$table_course_user} AS t1 WHERE  t1.class_id='" . Database::escape_string ( $class_id ) . "' AND course_code='" . api_get_course_code () . "'";
	$db_users_subscribed_array = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	$db_users_subscribed_arr = array ();
	if (count ( $db_users_subscribed_array ) > 0) {
		$db_users_subscribed_arr = array ();
		foreach ( $db_users_subscribed_array as $tmp_user ) {
			$db_users_subscribed_arr [] = $tmp_user ['user_id'];
		}
	}
	return $db_users_subscribed_arr;
}

$db_users_subscribed_arr = get_users_subscirbed2class ( $class_id );
$db_users_subscribed_str = implode ( ',', $db_users_subscribed_arr );

if ($_POST ['formSent']) {
	$form_sent = $_POST ['formSent'];
	$first_letter_left = $_POST ['firstLetterLeft'];
	$first_letter_right = $_POST ['firstLetterRight'];
	//$left_user_list = is_array($_POST['LeftUserList']) ? $_POST['LeftUserList'] : array();
	$right_user_list = is_array ( $_POST ['RightUserList'] ) ? $_POST ['RightUserList'] : array ();
	
	if ($form_sent == 1) {
		if (count ( $db_users_subscribed_arr ) >= count ( $right_user_list )) { //注销部分用户
			$users_unsubscribe2class = array_diff ( $db_users_subscribed_arr, $right_user_list );
			foreach ( $users_unsubscribe2class as $user_id ) {
				CourseClassManager::unsubscribe_user ( $user_id, $class_id );
			}
			$message = (get_lang ( 'UsersAreUnSubscibedToClass' ));
		} elseif (count ( $db_users_subscribed_arr ) < count ( $right_user_list )) { //增加注册部分用户
			$users_subscribe2class = array_diff ( $right_user_list, $db_users_subscribed_arr );
			foreach ( $users_subscribe2class as $user_id ) {
				CourseClassManager::add_user ( $user_id, $class_id );
			}
			$message = (get_lang ( 'UsersAreSubscibedToClass' ));
		} elseif (count ( $db_users_subscribed_arr ) == count ( $right_user_list )) {
		
		}
		
		$redirect_url = 'class_list.php?actino=show_message&message=' . urlencode ( $message );
		echo '<script>self.parent.location.href="' . $redirect_url . '";self.parent.tb_remove();</script>';
		exit ();
	
	//api_redirect($redirect_url);
	}
}

Display::display_header ( null, FALSE );

$db_users_subscribed_arr = get_users_subscirbed2class ( $class_id );
$db_users_subscribed_str = implode ( ',', $db_users_subscribed_arr );
//可注册到该课程中的学生用户
$sql = "SELECT u.user_id,lastname,firstname,username,u.dept_name,cu.class_id FROM {$tbl_user} AS u,{$table_course_user} AS cu
		WHERE cu.course_code='" . api_get_course_code () . "' AND cu.is_course_admin=0 
		AND cu.user_id=u.user_id AND u.active=1 AND cu.class_id<>'" . Database::escape_string ( $class_id ) . "'";
if (isset ( $first_letter_left ) && ! empty ( $first_letter_left )) $sql .= " AND (u.firstname LIKE '%{$first_letter_left}%' OR u.username LIKE '%{$first_letter_left}%') ";
$sql .= " ORDER BY cu.class_id,username";
//$sql.=(count($db_users_subscribed_arr) > 0 ? " AND (u.user_id NOT IN(".$db_users_subscribed_str.")) " : " ")." ORDER BY  firstname";
//echo $sql;
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$left_users = api_store_result ( $result );

//已注册到该班级中的学生用户
$sql = "SELECT u.user_id,lastname,firstname,username,u.dept_name FROM $tbl_user u,$table_course_user cu WHERE cu.user_id=u.user_id AND cu.class_id='$class_id' AND cu.course_code='" . api_get_course_code () . "'";
if (isset ( $first_letter_right ) && ! empty ( $first_letter_right )) $sql .= " AND (firstname LIKE '%{$first_letter_right}%' OR username LIKE '%{$first_letter_right}%') ";
$sql .= " ORDER BY firstname";
//echo $sql;
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$right_users = api_store_result ( $result );
if (! empty ( $error_message )) {
	Display::display_error_message ( $error_message );
}

?>
<form name="formulaire" method="post"
	action="subscribe_user2class.php?course=<?=urlencode ( $course )?>&amp;idclass=<?=$class_id?>"
	style="margin: 0px;">
	<input type="hidden" name="formSent" value="1" />
	<table border="0" cellpadding="5" cellspacing="1" align="center"
		width="99%" style="border-collapse: separate;">
		<!-- <tr>
		<th class="formTableTh" colspan="3"><?=$tool_name?></th>
	</tr> -->
		<tr>
			<td><b><?=get_lang ( 'UsersOutsideCourseClass' )?> </b>&nbsp;&nbsp; <input
				type="text" name="firstLetterLeft" value="<?=$first_letter_left?>"
				onkeydown="sendForm(false);" class="inputText" /> <input
				type="button" class="inputSubmit" name="searchLeft"
				value="<?=get_lang ( 'Search' )?>" onclick="sendForm(true);" /></td>
			<td width="20">&nbsp;</td>
			<td><b><?=get_lang ( 'UsersInsideCourseClass' );?> </b>&nbsp;&nbsp; <input
				type="text" name="firstLetterRight" value="<?=$first_letter_right?>"
				onkeydown="sendForm(false);" class="inputText" /> <input
				type="button" class="inputSubmit" name="searchRight"
				value="<?=get_lang ( 'Search' )?>" onclick="sendForm(true);" /></td>
		</tr>
		<tr>
			<td align="center"><select id="LeftUserList" name="LeftUserList[]"
				multiple="multiple" size="15" style="width: 90%;"
				ondblclick="moveLeft2Right();">
			<?php
			foreach ( $left_users as $user ) {
				?>
			<option value="<?=$user ['user_id']?>"
						<?php
				if (in_array ( $user ['user_id'], $right_user_list )) echo 'selected="selected"';
				?>><?= $user ['firstname'] . ' (' . $user ['username'] . ' - '.$user['dept_name']. ' , ' . $all_classes [$user ['class_id']] . ')'?></option>
			<?php
			}
			?>
		</select></td>

			<td valign="middle" align="center" class="formTableTd"><input
				type="button" onclick="moveLeft2Right()" name="addCourse"
				class="formbtn" value="&gt;" /> <br /> <br> <input type="button"
				onclick="moveRight2Left();" name="removeCourse" class="formbtn"
				value="&lt;" /></td>

			<td align="center"><select id="RightUserList" name="RightUserList[]"
				multiple="multiple" size="15" style="width: 90%;"
				ondblclick="moveRight2Left()">
			<?php
			foreach ( $right_users as $user ) {
				?>
			<option value="<?=$user ['user_id']?>"
						<?php
				if (in_array ( $user ['user_id'], $left_user_list )) echo 'selected="selected"';
				?>><?=$user ['firstname'] . ' (' . $user ['username'] . ' - '.$user['dept_name'].')'?></option>
			<?php
			}
			?>
		</select></td>
		</tr>
		<tr>
			<td colspan="3" align="center" class="formTableTd"><input
				type="button" name="removeCourse" class="inputSubmit"
				value="<?=get_lang ( "Ok" )?>" onclick="valide()" /> &nbsp;
				<button type="button" class="cancel"
					onclick="javascript:self.parent.tb_remove();" name="cancle"><?=get_lang ( "Cancel" )?></button>
				</</td>
		</tr>
	</table>
</form>
<?php
Display::display_footer ();
