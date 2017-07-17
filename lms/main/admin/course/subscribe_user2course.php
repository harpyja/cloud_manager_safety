<?php
/*
 ==============================================================================
 分配用户到课程 
 ==============================================================================
 */
/**
 ==============================================================================
 *	This script allows platform admins to add users to courses.
 *	It displays a list of users and a list of courses;
 *	you can select multiple users and courses and then click on
 *	'Add to this(these) course(s)'.
 *
 *	@package zllms.admin
 ==============================================================================
 */

/*
 ==============================================================================
 INIT SECTION
 ==============================================================================
 */
// name of the language file that needs to be included
$language_file = 'admin';

$cidReset = true;

include ('../../inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script();
$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);

$form_sent = 0;
$first_letter_user = '';
$first_letter_course = '';

$course_code=  getgpc("code");

$tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);
//$tbl_user 	= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_user 	= Database :: get_main_table(VIEW_USER_DEPT);
$tbl_course_user=Database::get_main_table(TABLE_MAIN_COURSE_USER);

$this_section=SECTION_PLATFORM_ADMIN;

$interbreadcrumb[] = array ("url" => api_get_path(WEB_ADMIN_PATH).'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => 'course_list.php', "name" => get_lang('CourseList'));
$htmlHeadXtra[] ="<script type='text/javascript'>
function sendForm(formSent)
{
	document.formulaire.formSent.value='2';
	if (formSent)
	{
		 document.formulaire.submit();
	}	
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
	moveItem(document.getElementById('UserSelectedAvailable'), document.getElementById('UserSelected'));
}

function moveRight2Left(){
	moveItem(document.getElementById('UserSelected'), document.getElementById('UserSelectedAvailable'))
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
	var options = document.getElementById('UserSelected').options;
	for (i = 0 ; i<options.length ; i++)
		options[i].selected = true;
	document.formulaire.submit();
}
</script>";

$tool_name = get_lang('AddUsersToACourse');
Display::display_header ( $tool_name ,FALSE);

$sql = "SELECT title FROM {$tbl_course} WHERE code='".escape($course_code)."'";
$course_name=Database::get_scalar_value($sql);

//已注册到该课程中的用户
function get_users_subscirbed2course($course_code){
	global $tbl_course_user;
	$sql="select t1.user_id from {$tbl_course_user} AS t1 WHERE status=".STUDENT." AND t1.course_code='".Database::escape_string($course_code)."'";
	$db_users_subscribed_array = api_sql_query_array_assoc($sql, __FILE__, __LINE__);
	$db_users_subscribed_arr=array();
	if(count($db_users_subscribed_array)>0){
		$db_users_subscribed_arr=array();
		foreach($db_users_subscribed_array as $tmp_user){
			$db_users_subscribed_arr[]=$tmp_user['user_id'];
		}
	}
	return $db_users_subscribed_arr;
}
$sql="select t1.user_id from {$tbl_course_user} AS t1 WHERE status=".STUDENT." AND t1.course_code='".escape($course_code)."'";
$db_users_subscribed_arr=Database::get_into_array($sql,__FILE__,__LINE__);
$db_users_subscribed_str=implode(',',$db_users_subscribed_arr);

//print_r($db_users_subscribed_arr);
//echo $db_users_subscribed_str;

/*
 ==============================================================================
 MAIN CODE
 ==============================================================================
 */


if ($_POST['formSent'])
{
	//var_dump($_POST);exit;
	$form_sent = getgpc('formSent');
	$first_letter_user = getgpc('firstLetterUser');

	$users = is_array(getgpc('UserList')) ? getgpc('UserList') : array() ;
	$users_subscribe=is_array(getgpc('UsersSubscribe'))?getgpc('UsersSubscribe'):array();

	//var_dump($db_users_subscribed_arr);
	//var_dump($users_subscribe);
	
	/*if(is_array($users_subscribe)){
		foreach($users_subscribe as $key => $value)
		{
		$users_subscribe[$key] = intval($value);
		}
		}*/

	if (is_equal(getgpc("formSent","P"),"1"))
	{
		/*if ( count($users_subscribe) == 0)
		 {
			Display :: display_error_message(get_lang('AtLeastOneUserAndOneCourse'));
			}
			else*/
		{
			if(count($db_users_subscribed_arr)>=count($users_subscribe)){//注销部分用户
				$users_unsubscribe2course=array_diff($db_users_subscribed_arr,$users_subscribe);
				//var_dump($users_unsubscribe2course);
				foreach ($users_unsubscribe2course as $user_id)
				{
					CourseManager::unsubscribe_user($user_id,$course_code);

					$log_msg=get_lang('UnsubscribeUserToCourse')."code=".$course_code.",user_id=".$user_id;
					api_logging($log_msg,'COURSE','UnsubscribeUserToCourse');
				}
				Display :: display_normal_message(get_lang('UsersAreUnSubscibedToCourse'));
			}
			elseif(count($db_users_subscribed_arr)<count($users_subscribe)){//增加注册部分用户
				$users_subscribe2course=array_diff($users_subscribe,$db_users_subscribed_arr);
				//var_dump($users_unsubscribe2course);
				foreach ($users_subscribe2course as $user_id)
				{
					CourseManager::subscribe_user($user_id,$course_code);
					$log_msg=get_lang('SubscribeUserToCourse')."code=".$course_code.",user_id=".$user_id;
					api_logging($log_msg,'COURSE','SubscribeUserToCourse');
				}
				Display :: display_normal_message(get_lang('UsersAreSubscibedToCourse'));
			}elseif(count($db_users_subscribed_arr)==count($users_subscribe)){

			}
		}
	}
}


//部门数据
$deptObj = new DeptManager ( );
$dept_options[0]=get_lang('All');

$dept_tree=$deptObj->get_sub_dept_ddl(0);
foreach ( $dept_tree as $dept_info ) {
	$dept_options [$dept_info ['id']] = str_repeat ( '&nbsp;', 2 * ($dept_info ['level']) ) . $dept_info ['dept_name'] . ($dept_info ['dept_no']?' - ' . $dept_info ['dept_no']:"");
}

define("SELECT_LIMIT_USER",400);

//可注册到该课程中的学生用户
$db_users_subscribed_arr=get_users_subscirbed2course($course_code);
//$db_users_subscribed_str=implode(',',$db_users_subscribed_arr);
$sql = "SELECT user_id,firstname,username,dept_id,org_id FROM {$tbl_user}
		WHERE active=1 AND status=".STUDENT;
if(count($db_users_subscribed_arr) > 0){
	$sql .= " AND (user_id NOT IN(".implode(',',$db_users_subscribed_arr).")) ";
}
if(is_not_blank($_REQUEST['firstLetterUser'])){
	$sql .= " AND (firstname LIKE '%{$first_letter_user}%' OR username LIKE '%{$first_letter_user}%') ";
}

if(isset($_REQUEST['keyword_deptid'])){
	if($_REQUEST['keyword_deptid']=='0'){
		$sql .= " LIMIT ".SELECT_LIMIT_USER;
	}else{
		$dept_id=intval(Database::escape_string(getgpc('keyword_deptid')));
		$dept_sn=$deptObj->get_sub_dept_sn($dept_id);
		if($dept_sn)	$sql .=" AND dept_sn LIKE '".$dept_sn."%'";
		else $sql .= " LIMIT ".SELECT_LIMIT_USER;
	}
}else{
	$sql .= " LIMIT ".SELECT_LIMIT_USER;
}
//echo $sql."<br/>";
if(api_strpos($sql,"LIMIT")===false){
	$sql .= " ORDER BY  firstname";
}else{
	$sql = api_substr($sql,0,api_strpos($sql,"LIMIT"))." ORDER BY firstname LIMIT ".SELECT_LIMIT_USER;
}
//echo $sql;
$result = api_sql_query($sql, __FILE__, __LINE__);
$db_users = api_store_result($result);


//已注册到该课程中的学生用户
$sql="select t2.user_id,t2.firstname,t2.username,t2.dept_id from {$tbl_course_user} AS t1
	LEFT OUTER JOIN {$tbl_user} AS t2 ON t1.user_id=t2.user_id 
	WHERE t2.active=1 AND t1.status=".STUDENT." AND t1.course_code='".escape($course_code)."'";
//echo $sql;
$result = api_sql_query($sql, __FILE__, __LINE__);
$db_user_selected = api_store_result($result);

?>

<form name="formulaire" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin: 0px;"><input
	type="hidden" name="formSent" value="1" /> <input type="hidden"
	name="code" value="<?=$course_code ?>" />
<table border="0" cellpadding="5" cellspacing="0" align="center">
	<tr>
		<th class="formTableTh" colspan="3"><?=$tool_name ?>:&nbsp;&nbsp;<?=$course_name ?>(<?=$course_code ?>)</th>
	</tr>

	<tr>
		<td class="formTableTd" colspan="3"><input type="text"
			name="firstLetterUser" value="<?php echo $first_letter_user; ?>"
			onkeydown="sendForm(false);" class="inputText" /> <select
			name="keyword_deptid">
			<?php foreach($dept_options as $key=>$val) {?>
			<option value="<?=$key?>"
			<?=(isset($_REQUEST['keyword_deptid']) && $key==$_REQUEST['keyword_deptid']?'selected':'') ?>><?=$val ?></option>
			<?php } ?>
		</select>
		<button type="button" class="search" name="searchLeft"
			value="<?php echo get_lang('Search'); ?>" onclick="sendForm(true);"><?php echo get_lang('Search'); ?></button>
		</td>
	</tr>
	<tr>
		<td class="formTableTd" style="border-right: 0px"><b><?php echo get_lang('UserSelectedAvailable'); ?></b>&nbsp;&nbsp;:</td>
		<td class="formTableTd" style="border: 0px"></td>
		<td class="formTableTd" style="border-left: 0px"><b><?php echo get_lang('UserSelected'); ?>:
		</b>&nbsp;&nbsp;</td>
	</tr>

	<tr>
		<td align="center" class="formTableTd"><select
			id="UserSelectedAvailable" name="UserList[]" multiple="multiple"
			size="16" style="width: 340px;" ondblclick="moveLeft2Right();">
			<?php	foreach ($db_users as $user) {
				$deptObj->dept_path="";	?>
			<option value="<?php echo $user['user_id']; ?>"
			<?php if(in_array($user['user_id'],$users)) echo 'selected="selected"'; ?>>
				<?php echo $user['firstname'].' ('.$user['username'].' | '.$deptObj->get_dept_path($user['dept_id']).')'; ?></option>
				<?php	}		?>
		</select></td>
		<td valign="middle" align="center" class="formTableTd"><input
			type="button" onclick="moveLeft2Right()" name="addCourse"
			class="inputSubShort" value="&gt;&gt;" /> <br />
		<br>
		<input type="button" onclick="moveRight2Left();" name="removeCourse"
			class="inputSubShort" value="&lt;&lt;" /></td>
		<td align="center" class="formTableTd"><select id="UserSelected"
			name="UsersSubscribe[]" ondblclick="moveRight2Left()"
			multiple="multiple" size="16" style="width: 340px;">
			<?php	foreach ($db_user_selected as $user_selected){
				$deptObj->dept_path="";	?>
			<option value="<?php echo $user_selected['user_id']; ?>"><?php echo $user_selected['firstname'].'('.$user_selected['username'].' | '.$deptObj->get_dept_path($user_selected['dept_id']).' ) '; ?></option>
			<?php	}	?>
		</select></td>
	</tr>
	<tr>
		<td colspan="3" align="center" class="formTableTd"><input
			type="button" name="removeCourse" class="inputSubmit"
			value="<?=get_lang("Ok") ?>" onclick="valide()" />&nbsp;&nbsp;
		<!-- <button class="cancel" onclick="javascript:self.parent.tb_remove();"><?=get_lang('Cancel')?></button> -->

		</td>
	</tr>
</table>
<br>
</form>
			<?php	Display :: display_footer();	?>