<?php
/**
 ==============================================================================
 ==============================================================================
 */
$language_file = 'admin';
require_once ("../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_course_script ();
$is_allowed_edit = api_is_allowed_to_edit ();
$course_code = api_get_course_code ();
if (empty ( $course_code )) $course_code = getgpc ( 'code', 'P' );
$course = CourseManager::get_course_information ( $course_code );
if (! $is_allowed_edit or ($course ["is_subscribe_enabled"] == 0)) api_not_allowed ();

$user_id = intval(getgpc ( 'user_id' ));
$action = getgpc ( "action" );

$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$objDept = new DeptManager ();

//
$p_formsent=  getgpc('formSent');
if (isset ( $p_formsent ) && is_equal ( $p_formsent, "1" )) {
	$rel_users = getgpc ( "target_select" );
	if ($rel_users && is_array ( $rel_users )) {
		//$is_required_crs=getgpc ( "is_required_course", "P" );
		$is_required_crs = 1;
		foreach ( $rel_users as $user_id ) {
			CourseManager::subscribe_user ( $user_id, $course_code, STUDENT, getgpc ( "begin_date", "P" ), getgpc ( "finish_date", "P" ), $is_required_crs );
			$log_msg = get_lang ( 'SubscribeUserToCourse' ) . "code=" . $course_code . ",user_id=" . $user_id;
			api_logging ( $log_msg, 'COURSE', 'SubscribeUserToCourse' );
		}
		//Display :: display_normal_message(get_lang('CoursesAreSubscibedToUser'));
		$redirect_url = "user.php?" . api_get_cidreq ();
	} else {
		$error_message = get_lang ( 'AtLeastOneUser' );
		$redirect_url = "user.php?" . api_get_cidreq () . '&message=' . urlencode ( $error_message );
	}
	tb_close ( $redirect_url );
}

$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( NULL, FALSE );

?>

<form name="theForm" method="post" action="add_user2course.php"><input
	type="hidden" name="formSent" value="1" /><input type="hidden"
	name="code" value="<?=$course_code?>" />

<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="98%">
	<tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "CourseType" )?></td>
		<td class="formTableTd" align="left"><?php
		echo form_radio ( "is_required_course", "1", TRUE, 'id="is_required_course1"' );
		echo form_label ( get_lang ( "RequiredCourse" ), "is_required_course1" ), "&nbsp;&nbsp;&nbsp;&nbsp;";
		//echo form_radio ( "is_required_course", "0", FALSE, 'id="is_required_course0"' );
		//echo form_label ( get_lang ( "OpticalCourse" ), "is_required_course0" );
		?></td>
	</tr>

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
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="finish_date" /></td>
	</tr>

	<tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "SubscribeUsers" )?></td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3"><div class="actions"><input type="text" name="keyword" class="inputText" />
				<?php
				echo form_dropdown ( "dept_id", $depts, NULL, 'id="dept_id" style="height:22px;min-width:60px"' );
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="search()" /></div></td>
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
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "course/ajax_actions.php"?>";
    var keyword_val=elements['keyword'].value;
    if(keyword_val=="undefined") keyword_val="";
    $.ajax({type:"post", data:{ajaxAction:"get_user_list_without_cur_crsuser",keyword:keyword_val,
        dept_id:$("#dept_id").val(),code:"<?=$course_code?>"},
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
<?php

Display::display_footer ();
?>