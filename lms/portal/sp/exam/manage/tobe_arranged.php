<?php
/**
 ==============================================================================
 ==============================================================================
 */
include_once ('../../exercice/exercise.class.php');
$language_file = array ('exercice', 'admin' );
include_once ('../../inc/global.inc.php');

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once ('../../exercice/exercise.lib.php');
api_protect_quiz_script ();

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$exam_id = (isset ( $_REQUEST ['exam_id'] ) ? getgpc ( 'exam_id' ) : "");
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';

$objExercise = new Exercise ();
$objExercise->read ( $exam_id );

//if ($objExercise->is_course_exam ()) {
//$course_info=api_get_course_info();
//$default_begin_date=
//} else {
$default_begin_date = date ( 'Y-m-d H:i' );
$default_finish_date = date ( 'Y-m-d H:i', strtotime ( "+ 7200 seconds" ) );
//}
//$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );


if (isset ( $_POST ['action'] ) && is_equal ( getgpc ( 'action', 'P' ), 'arrange_save' )) {
	$user_ids = getgpc ( 'target_select', 'P' );
	$send_mail = getgpc ( 'send_mail' );
	$number_of_selected_items = count ( $user_ids );
	$number_of_deleted_items = 0;
	$start_time= getgpc ( 'begin_date', 'P' );
	$end_time=getgpc ( 'finish_date', 'P' );
	foreach ( $user_ids as $index => $item_id ) {
		if (Exercise::add_user_to_exam ( $exam_id, $item_id,$start_time, $end_time )) {
			$number_of_deleted_items ++;
			$user_info = api_get_user_info ( $item_id );
			//$exam_time=Exercise::get_exam_time($item_id,$exam_id, $objExercise);
			if ($user_info && $user_info ['email'] && $send_mail) {
				$emailTo = trim ( $user_info ['email'] );
				$emailSubject = get_setting ( 'siteName' ) . ':' . '给你安排了新考试';
				$emailBody = get_lang ( 'Dear' ) . ' ' . $user_info ['firstname'] . ":<br/>" . "<br/>";
				$emailBody.='考试名称:'.$objExercise->exercise.'<br/>';
				$emailBody.='可参加考试时间:'. $start_time.' 至 '.$end_time;
				$emailBody.='<br/>请注意考试时间,准时参加!';
				email_body_txt_add($emailBody);
				api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
			}
		}
	}
	tb_close ();
}

$htmlHeadXtra [] = import_assets ( "select.js" );

$objDept = new DeptManager ();

/*$sql = "SELECT id,name from " . $table_position . " WHERE is_enabled=1";
$positions = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$positions = array_insert_first ( $positions, array ("" => "---" . get_lang ( "All" ) . "---" ) );*/

$tool_name = get_lang ( 'ArrageExaminees' );
Display::display_header ( $tool_name, FALSE );

Display::display_normal_message ( '注意: 如果已安排考生选项打勾,则查询用户为当前可参加考试的用户;重新安排会覆盖之前的可考试时间!' );
?>
<form name="theForm" method="post" action="tobe_arranged.php"><input
	type="hidden" name="action" value="arrange_save" /> <input
	type="hidden" name="exam_id" value="<?=$exam_id?>" />

<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="100%">

	<tr class="containerBody">
		<td class="formLabel"><span class="form_required">*</span><?=get_lang ( "ExerciseDuration" )?></td>
		<td class="formTableTd" align="left">
		<div id="append_parent"></div>
		<script src="<?=api_get_path ( WEB_JS_PATH )?>js_calendar.js"
			type="text/javascript"></script> <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="begin_date"
			onclick="showcalendar(event,this,true,'<?=date ( "Y-m-d H:i" )?>', '<?=date ( 'Y-m-d H:i', strtotime ( "+ 30 days" ) )?>')"
			name="begin_date" value="<?=$default_begin_date?>" />&nbsp;至&nbsp; <input
			readonly="readonly" class="inputText"
			style="width: 120px; text-align: right" id="finish_date"
			onclick="showcalendar(event,this,true,'<?=date ( "Y-m-d H:i" )?>', '<?=date ( 'Y-m-d H:i', strtotime ( "+ 30 days" ) )?>')"
			name="finish_date" value="<?=$default_finish_date?>" /></td>
	</tr>

	<tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "ExamineesHaveArraged" )?></td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3">
				<div class="actions">登录名/姓名:&nbsp;<input type="text" name="keyword"
					class="inputText"
					title="<?=get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" )?>" />
				<?php
				//echo form_dropdown ( "position_id", $positions, NULL, 'id="position_id" style="height:22px;" title="'.get_lang("Position").'"' );
				$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
				echo '&nbsp;&nbsp;所属部门:&nbsp;' . form_dropdown ( "dept_id", $depts, NULL, 'id="dept_id" style="height:22px;" title="' . get_lang ( "InDept" ) . '"' );
				echo '<br/>查询考生范围:&nbsp;' . form_checkbox ( "contain_arranged", 1, FALSE, 'id="contain_arranged"' );
				echo form_label ( '在已安排考生中查询', 'contain_arranged' );
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
					size="10" style="width: 240px" multiple
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
		<button class="cancel" type="button"
			onclick="javascript:self.parent.tb_remove();"><?=get_lang ( 'Cancel' )?></button>
		<br>
		</br>
		</td>
	</tr>
</table>
<script type="text/javascript">

var elements = document.forms['theForm'].elements;

function search()
{
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "exam/ajax_actions.php"?>";
    var keyword_val=elements['keyword'].value;
    if(keyword_val=="undefined") keyword_val="";
    if($("#contain_arranged").attr('checked')){
		var action="get_user_list_within_curr_exam";
    }else{
		var action="get_user_list_without_curr_exam";
    }

    $.ajax({type:"post", data:{action:action,keyword:keyword_val,
        dept_id:$("#dept_id").val(),org_id:$("#org_id").val(),quiz_id:"<?=$exam_id?>",cc:"<?=$objExercise->cc?>"},
        url:url, dataType:"json",cache:false,
				success:function(data){
					//alert(data);
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