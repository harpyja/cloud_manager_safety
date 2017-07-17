<?php
/**
 ==============================================================================

 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

api_protect_admin_script ();

$user_id = intval(getgpc ( 'user_id' ));
$action = getgpc ( "action" );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$sql = "SELECT firstname FROM $tbl_user WHERE user_id='$user_id'";
$user_info = UserManager::get_user_info_by_id ( $user_id );
if (! $user_info) api_redirect ( "user_list.php" );
$firstname = $user_info ["firstname"];



$objCrsMng = new CourseManager ();
//$orgs = get_restrict_org_dd ( $restrict_org_id ); //所属机构
//$orgs = array_insert_first ( $orgs, array ("-1" => "---" . get_lang ( "SharedCourseCategory" ) . "---" ) );

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' ) . ' - ' . $firstname . '';
Display::display_header ( $tool_name, FALSE );

if (isset ( $_POST ['formSent'] ) && is_equal ( $_POST ['formSent'], "1" )) {
	$rel_courses = $_POST['target_select'];
	if ($rel_courses && is_array ( $rel_courses )) {
		$is_required_crs=getgpc ( "is_required_course", "P" );
		foreach ( $rel_courses as $course ) {
			CourseManager::subscribe_user ( $user_id, $course, STUDENT, getgpc ( "begin_date", "P" ), getgpc ( "finish_date", "P" ), $is_required_crs );
			$log_msg = get_lang ( 'SubscribeUserToCourse' ) . "code=" . $course . ",user_id=" . $user_id;
			api_logging ( $log_msg, 'COURSE', 'SubscribeUserToCourse' );
		}
		//Display :: display_normal_message(get_lang('CoursesAreSubscibedToUser'));
		$redirect_url = "user_subscribe_course_list.php?user_id=" . $user_id;
		tb_close ( $redirect_url );
	} else {
		$error_message = get_lang ( 'AtLeastOneCourse' );
		Display::display_error_message ( $error_message );
	}
}

?>

<form name="theForm" method="post" action="subscribe_course2user.php"
	onsubmit="javascript:return validate();"><input type="hidden"
	name="formSent" value="1" /><input type="hidden" name="user_id"
	value="<?=$user_id?>" />

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
			id="begin_date" value="<?=date ( 'Y-m-d' )?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="begin_date" />&nbsp;至&nbsp; <input readonly="readonly"
			class="inputText" style="width: 120px; text-align: right"
			id="finish_date"
			value="<?=date ( 'Y-m-d', strtotime ( "+ " . DEFAULT_LEARNING_DAYS . " day" ) )?>"
			onclick="showcalendar(event,this,false,'<?=date ( "Y-m-d" )?>', '<?=date ( 'Y-m-d', strtotime ( "+ 90 seconds" ) )?>')"
			name="finish_date" /></td>
	</tr>

	<tr class="containerBody">
		<td class="formLabel">要安排的课程</td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3"><input type="text" name="keyword" class="inputText" />
				<?php
				$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, - 1 );
				$parent_cate_option ["0"] = "";
				foreach ( $category_tree as $item ) {
					$parent_cate_option [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $item ['name'];
				}
				echo form_dropdown ( "category_code", $parent_cate_option, NULL, 'id="category_code" style="height:22px;"' );
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="searchCourses('keyword')" /></td>
			</tr>
			<!-- <tr align="center">
				<th>可选课程</th>
				<th>操作</th>
				<th>已选课程</th>
			</tr> -->
			<tr>
				<td><select name="source_select[]" size="10" id="source_select[]"
					style="width: 240px;"
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
		<td class="formLabel"><?=get_lang ( "CourseType" )?></td>
		<td class="formTableTd" align="left"><?php
		$course_types = array ('1' => get_lang ( "RequiredCourse" ), '0' => get_lang ( "OpticalCourse" ) );
		echo form_dropdown ( 'is_required_course', $course_types, 1 );
		/*	echo form_radio ( "is_required_course", "1", TRUE, 'id="is_required_course1"' );
		echo form_label ( get_lang ( "RequiredCourse" ), "is_required_course1" ), "&nbsp;&nbsp;&nbsp;&nbsp;";
		echo form_radio("is_required_course","0",FALSE,'id="is_required_course0"');
		echo form_label(get_lang("OpticalCourse"),"is_required_course0");*/
		?></td>
	</tr>


	<tr>
		<td colspan="3" align="center" class="formTableTd"><input
			type="submit" name="removeCourse" class="inputSubmit"
			value="<?=get_lang ( "Ok" )?>" />&nbsp;&nbsp;
		<button class="cancel" type="button"
			onclick="javascript:self.parent.tb_remove();"><?=get_lang ( 'Cancel' )?></button>
		</td>
	</tr>
</table>
<script type="text/javascript">

var elements = document.forms['theForm'].elements;

function searchCourses(keyword)
{
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "admin/ajax_actions.php"?>";
    var keyword_val=elements['keyword'].value;
    if(keyword_val=="undefined") keyword_val="";
    $.ajax({type:"post", data:{action:"get_course_list_without_mime",keyword:keyword_val,
        category_code:$("#category_code").val(),user_id:<?=$user_id?>},
        url:url, dataType:"json",cache:false,
				success:function(data){
					var obj=elements['source_select[]'];
					obj.length = 0;
					for ( var i = 0; i < data.length; i++) {
							var opt = document.createElement("OPTION");
							opt.value = data[i].code;
							opt.text = data[i].title+" ("+data[i].code+")";
							obj.options.add(opt);
					}
				},
				error:function() { alert("Server is Busy, Please Wait...");}
	      });
}


function validate(){
	var begin_date=elements['begin_date'];
	var finish_date=elements['finish_date'];
	if(begin_date.value==''){
		alert("请设置学习期限开始日期!");
		begin_date.focus();
		return false;
	}
	if(finish_date.value==''){
		alert("请设置学习期限结束日期!");
		finish_date.focus();
		return false;
	}
	
	select_items("target_select[]");
	return true;
}
</script> <br>
</form>
<?php

Display::display_footer ();
