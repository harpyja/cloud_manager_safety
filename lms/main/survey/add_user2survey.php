<?php

$language_file = array ('survey', 'admin' );
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id") ) : "";
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval(getgpc ( 'keyword_deptid', 'G' )) : '0';

$sql = "SELECT title,avail_from,avail_till,code FROM $tbl_survey WHERE id=" . Database::escape ( $survey_id );
list ( $survey_name, $start_date, $end_date,$survey_code ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
if (empty ( $survey_code )) {
	$redirect_url = 'survey_users.php?survey_id=' . $survey_id;
	tb_close ( $redirect_url );
}
$tool_name = get_lang ( 'AddUsersToSurvey' ) . ' (' . $survey_name . ')';

$htmlHeadXtra [] = import_assets ( "select.js" );

Display::display_header ( $tool_name, FALSE );

$p_formsent=  getgpc('formSent');
if (isset ( $p_formsent ) && is_equal ( $p_formsent, "1" )) //保存
{
	$rel_users = $_REQUEST['target_select'];
	$start_date = getgpc ( "start_date", "P" );
	$end_date = getgpc ( "end_date", "P" );
	$send_mail = getgpc ( 'send_mail' );
	if ($rel_users && is_array ( $rel_users )) {
		foreach ( $rel_users as $user_id ) {
			if (SurveyManager::add_user ( $survey_id, $user_id, $start_date, $end_date )) {
				$user_info = api_get_user_info ( $user_id );
				if ($user_info && $user_info ['email'] && $send_mail) {
					$emailTo = trim ( $user_info ['email'] );
					$emailSubject = '邀请你参与调查问卷:'.$survey_name;
					$emailBody = get_lang ( 'Dear' ) . ' ' . $user_info ['firstname'] . ":<br/>" . "<br/>";
					$emailBody .= '调查名称:' . $survey_name . '<br/>';
					$emailBody .= '调查编号:' . $survey_code . '<br/>';
					$emailBody .= '可参与时间:' . $start_date . ' 至 ' . $end_date;
					email_body_txt_add ( $emailBody );
					api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
				}
			}
		}
		SurveyManager::update_invited ( $survey_id );
		//		$redirect_url = 'survey_users.php?survey_id=' . $survey_id;
		tb_close ();
	} else {
		$error_message = get_lang ( 'AtLeastOneUser' );
		Display::display_error_message ( $error_message );
	}
}

$objDept = new DeptManager ();
?>

<body>
<div>

<form name="theForm" method="post" action="add_user2survey.php"><input
	type="hidden" name="formSent" value="1" /><input type="hidden"
	name="survey_id" value="<?=$survey_id?>" />

<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="98%">
	<tr>
		<th class="formTableTh" colspan="3"><?=$survey_name?></th>
	</tr>

	<tr class="containerBody">
		<td class="formLabel"><span class="form_required">*</span><?=get_lang ( "AttemptDuration" )?></td>
		<td class="formTableTd" align="left">
		<div id="append_parent"></div>
		<script src="<?=api_get_path ( WEB_JS_PATH )?>js_calendar.js"
			type="text/javascript"></script> <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="start_date" value="<?=$start_date?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="start_date" />&nbsp;至&nbsp; <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="end_date" value="<?=$end_date?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="end_date" /></td>
	</tr>


	<tr class="containerBody">
		<td class="formLabel"><span class="form_required">*</span><?=get_lang ( "SurveyUsers" )?></td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="center">
			<tr>
				<td colspan="3"><input type="text" name="keyword" class="inputText" />
				<?php
				$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
				echo form_dropdown ( "dept_id", $depts, NULL, 'id="dept_id" style="height:22px;"' );
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="search()" /></td>
			</tr>
			<!-- <tr align="center">
				<th>可选课程</th>
				<th>操作</th>
				<th>已选课程</th>
			</tr> -->
			<tr>
				<td><select name="source_select[]" size="10" id="source_select[]"
					style="width: 300px;"
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
					size="10" style="width: 300px" multiple
					ondblclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)">

				</select></td>
			</tr>
		</table>
		</td>
	</tr>
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
		<button type="button" class="cancel"
			onclick="javascript:self.parent.tb_remove();"><?=get_lang ( 'Cancel' )?></button>
		</td>
	</tr>
</table>
<script type="text/javascript">

var elements = document.forms['theForm'].elements;

function search(){	     
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "admin/ajax_actions.php"?>";
    var keyword_val=elements['keyword'].value;	     
    if(keyword_val=="undefined") keyword_val="";
    $.ajax({type:"post", data:{action:"get_user_list_without_curr_surveyuser",keyword:keyword_val,
        dept_id:$("#dept_id").val(),org_id:$("#org_id").val(),survey_id:"<?=$survey_id?>"}, 
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
</script> <br>
</form>
</div>
</body>
</html>