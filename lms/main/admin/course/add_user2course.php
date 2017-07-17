<?php
/**
 ==============================================================================
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$user_id = getgpc ( 'user_id' );
$action = getgpc ( "action" );
$code = getgpc ( 'code' );

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$objDept = new DeptManager ();
$course_info = api_get_course_info ( $code );

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( $tool_name, FALSE );

if (isset ( $_POST ['formSent'] ) && is_equal (getgpc("formSent","P"), "1" )) {
	$rel_users = $_REQUEST['target_select'];//getgpc ( "target_select" );
	$send_mail = getgpc ( 'send_mail' );
	if ($rel_users && is_array ( $rel_users )) {
		//$is_required_crs=getgpc ( "is_required_course", "P" );
		$is_required_crs = 1;
		foreach ( $rel_users as $user_id ) {
			$rtn = CourseManager::subscribe_user ( $user_id, $code, STUDENT, getgpc ( "begin_date", "P" ), getgpc ( "finish_date", "P" ), $is_required_crs );
			if ($rtn) {
				$user_info = api_get_user_info ( $user_id );
				if ($user_info && $user_info ['email'] && $send_mail) {
					$emailTo = trim ( $user_info ['email'] );
					$emailSubject = '给你安排了必修课程';
					$emailBody = get_lang ( 'Dear' ) . ' ' . $user_info ['firstname'] . ":<br/>" . "<br/>";
					$emailBody .= '课程名称:' . $course_info ['name'] . '<br/>';
					$emailBody .= '课程编号:' . $course_info ['code'] . '<br/>';
					$emailBody .= '学习期限:' . getgpc ( "begin_date", "P" ) . ' 至 ' . getgpc ( "finish_date", "P" );
					$emailBody .= '<br/>请注意抓紧时间学完,如果课程有毕业考试,也请注意参加时间!';
					email_body_txt_add ( $emailBody );
					api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
				}
				
				$log_msg = get_lang ( 'SubscribeUserToCourse' ) . "code=" . $code . ",user_id=" . $user_id;
				api_logging ( $log_msg, 'COURSE', 'SubscribeUserToCourse' );
			}
		}
		//Display :: display_normal_message(get_lang('CoursesAreSubscibedToUser'));
		$redirect_url = "course_subscribe_user_list.php?code=" . $code;
		tb_close ( $redirect_url );
	} else {
		$error_message = get_lang ( 'AtLeastOneUser' );
		Display::display_error_message ( $error_message );
	}
}

$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
?>

<form name="theForm" method="post" action="add_user2course.php"><input
	type="hidden" name="formSent" value="1" /><input type="hidden"
	name="code" value="<?=$code?>" />

<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="98%">
	<!-- <tr>
		<th class="formTableTh" colspan="3"><?=$tool_name?></th>
	</tr> -->

	<tr class="containerBody">
		<td class="formLabel"><span class="form_required">*</span><?=get_lang ( "ValidLearningDate" )?></td>
		<td class="formTableTd" align="left">
		<div id="append_parent"></div>
		<script src="<?=api_get_path ( WEB_JS_PATH )?>js_calendar.js"
			type="text/javascript"></script> <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="begin_date" value="<?=date ( "Y-m-d" )?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="begin_date" />&nbsp;至&nbsp; <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="finish_date"
			value="<?=date ( "Y-m-d", strtotime ( "+ 1 year" ) )?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="finish_date" /></td>
	</tr>

	<tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "SubscribeUsers" )?></td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3">
				<div class="actions"><input type="text" name="keyword"
					class="inputText" />
				<?php
				//echo form_dropdown ( "org_id", $orgs, NULL, 'id="org_id" style="height:22px;"' );
				echo form_dropdown ( "dept_id", $depts, NULL, 'id="dept_id" style="height:22px;"' );
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="search()" /></div>
				</td>
			</tr>
			<!-- <tr align="center">
				<th>可选课程</th>
				<th>操作</th>
				<th>已选课程</th>
			</tr> -->
			<tr>
				<td><select name="source_select[]" size="10" id="source_select[]"
					style="width: 250px;"
					ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)"
					multiple="true">
				</select></td>
				<td align="center">
				<p><input type="button" value=">>"
					onclick="moveItem_l2r(this.form.elements['source_select[]'],this.form.elements['target_select[]'],true)"
					class="formbtn" /></p>
				<p><input type="button" value=">"
					onclick="moveItem_l2r(this.form.elements['source_select[]'],this.form.elements['target_select[]'],false)"
					class="formbtn" /></p>
				<p><input type="button" value="<" onclick="
					moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)" class="formbtn" /></p>
				<p><input type="button" value="<<" onclick="
					moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],true)" class="formbtn" /></p>
				<p><input type="button" value="<?=get_lang ( "Empty" )?>"
					onclick="clearOptions(this.form.elements['source_select[]'])"
					class="formbtn" /></p>
				</td>
				<td align="left"><select name="target_select[]" id="target_select[]"
					size="10" style="width: 250px" multiple
					ondblclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)">
					<?php
					//$sql = "SELECT c.code,c.title FROM $tbl_course c , $tbl_course_user cu WHERE c.code=cu.course_code AND cu.user_id='".escape($user_id)."'";
					//s$res = api_sql_query($sql, __FILE__, __LINE__);
					while ( $item = Database::fetch_array ( $res, "ASSOC" ) ) {
						?>
					<option value="<?=$item ['code']?>"><?=$item ['title']?> (<?=$item ['code']?>)</option>
					<?php
					}
					?>
				</select></td>
			</tr>
		</table>
		</td>
	</tr>

	<!-- <tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "CourseType" )?></td>
		<td class="formTableTd" align="left"><?php
		$course_types = array ('1' => get_lang ( "RequiredCourse" ), '0' => get_lang ( "OpticalCourse" ) );
		echo form_dropdown ( 'is_required_course', $course_types, 1 );
		?></td>
	</tr> -->

	<tr class="containerBody">
		<td class="formLabel">发送通知邮件</td>
		<td class="formTableTd" align="left"><input name="send_mail" value="1"
			type="radio" id="send_mail1" /><label for="send_mail1">是</label>&nbsp;
		<input name="send_mail" value="0" type="radio" id="send_mail0"
			checked="checked" /><label for="send_mail0">否</label>&nbsp;&nbsp;</td>
	</tr>

	<tr>
		<td colspan="3" align="center" class="formTableTd"><input
			type="submit" name="removeCourse" class="inputSubmit"
			value="<?=get_lang ( "Ok" )?>" onclick="validate()" />&nbsp;&nbsp;
		<button class="cancel" type="button"
			onclick="javascript:self.parent.tb_remove();"><?=get_lang ( 'Cancel' )?></button>
		</td>
	</tr>
</table>
<script type="text/javascript">

var elements = document.forms['theForm'].elements;

function search()
{
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "admin/ajax_actions.php"?>";
    var keyword_val=elements['keyword'].value;
    if(keyword_val=="undefined") keyword_val="";
    $.ajax({type:"post", data:{action:"get_user_list_without_cur_crsuser",keyword:keyword_val,
        dept_id:$("#dept_id").val(),org_id:$("#org_id").val(),code:"<?=$code?>"},
        url:url, dataType:"json",cache:false,
				success:function(data){
					var obj=elements['source_select[]'];
					obj.length = 0;
					for ( var i = 0; i < data.length; i++) {
							var opt = document.createElement("OPTION");
							opt.value = data[i].user_id;
							opt.text = data[i].firstname+" ("+data[i].username+", "+data[i].dept_name+")";
							obj.options.add(opt);
					}
				},
				error:function() { alert("Server is Busy, Please Wait...");}
	      });
}


function validate(){
	select_items("target_select[]");
	return true;
}
</script></form>
<?php

Display::display_footer ();
?>