<?php
/**
 ==============================================================================
 * @package zllms.admin
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

api_protect_admin_script ();
$objDept = new DeptManager ();
$user_id = intval(getgpc ( 'user_id' ));
$action = getgpc ( "action" );
$code = getgpc ( 'code' );

if (isset ( $_POST ['action'] ) && is_equal ( getgpc ( 'action', 'P' ), 'arrange_save' )) {
	$tbl_course_openscope = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
	$user_ids = getgpc ( 'target_select', 'P' );
	$course_codes = getgpc ( 'target_select2', 'P' );
	$redirect_url = 'main/admin/course/course_user_open_plan.php';
	if ($user_ids && $course_codes) {
		foreach ( $user_ids as $user_id ) {
			foreach ( $course_codes as $course_code ) {
				if (! empty ( $user_id ) and ! empty ( $course_code )) {
					$sql = "SELECT * FROM $tbl_course_openscope WHERE course_code=" . Database::escape ( $course_code ) . ' AND user_id=' . Database::escape ( $user_id );
					if (Database::if_row_exists ( $sql ) == FALSE) {
						$sql_data = array ("course_code" => $course_code, "user_id" => $user_id );
						$sql = Database::sql_insert ( $tbl_course_openscope, $sql_data, TRUE );
						api_sql_query ( $sql, __FILE__, __LINE__ );
					}
				}
			}
		}
		Display::display_msgbox ( get_lang ( 'ArrangeOpticalCoursesSuccess' ), $redirect_url );
	} else {
		Display::display_msgbox ( get_lang ( 'OperationFailed' ), $redirect_url, 'error' );
	}
}

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$htmlHeadXtra [] = import_assets ( "select.js" );

$objCrsMng = new CourseManager ();

$tool_name = get_lang ( 'ArrangeOpticalCourses' );
Display::display_header ( $tool_nae, false );
Display::display_normal_message('在这里可批量设置课程开放给哪些用户去选修学习,只有开放权限的课程前台学员登录后才会在"选课中心"中出现, 学员选择所开放的课程后成为课程的选修用户');

?>

<div style="margin-top: 3px">

<form name="theForm" method="post" action="arrange_optical_courses.php"><input
	type="hidden" name="action" value="arrange_save" />

<table border="0" cellpadding="5" cellspacing="0" align="center"
	width="98%">
<!-- 	<tr>
		<th class="formTableTh" colspan="3"><?=$tool_name?></th>
	</tr>
 -->
	<tr class="containerBody">
		<td class="formLabel"><?=get_lang ( "SubscribeUsers" )?></td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3"><input type="text" name="keyword" class="inputText" />
				<?php
				$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
				
				echo form_dropdown ( "dept_id", $depts, NULL, 'id="dept_id" style="height:22px;min-width:120px"' );
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="search()" /></td>
			</tr>
			<!-- <tr align="center">
				<th>可选课程</th>
				<th>操作</th>
				<th>已选课程</th>
			</tr> -->
			<tr>
				<td align="left"><select name="source_select[]" size="12"
					id="source_select[]" style="width: 300px;"
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
					size="12" style="width: 300px" multiple
					ondblclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)">

				</select></td>
			</tr>
		</table>
		</td>
	</tr>

	<tr class="containerBody">
		<td class="formLabel">可选修的课程</td>
		<td style="text-align: left;" class="formTableTd">
		<table id="linkgoods-table" align="left">
			<tr>
				<td colspan="3"><input type="text" name="keyword2" class="inputText" />
				<?php
				//echo form_dropdown("org_id",$orgs,NULL,'id="org_id" style="height:22px;"');
				$sql = "SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
				$category_cnt = Database::get_into_array2 ( $sql );
				$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, - 1 );
				$parent_cate_option ["0"] = "";
				foreach ( $category_tree as $item ) {
					$cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [intval($item ['id'])] . ")" : "");
					$parent_cate_option [$item ['id']] = str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $cate_name;
				}
				echo form_dropdown ( "category_code", $parent_cate_option, NULL, 'id="category_code" style="height:22px;"' );
				
				/*$tbl_course_package = Database::get_main_table ( TABLE_MAIN_COURSE_PACKAGE );
				$tbl_course_rel_package = Database::get_main_table ( TABLE_MAIN_COURSE_REL_PACKAGE );
				$sql="SELECT pkg_id,count(*) FROM $tbl_course_rel_package GROUP BY pkg_id";
				$competency_cnt=Database::get_into_array2($sql);
				$competencies["0"]="";
				$sql="SELECT id,pkg_name FROM $tbl_course_package WHERE is_enabled=1 ORDER BY id";
				$res = api_sql_query ( $sql, __FILE__, __LINE__ );
				while ( $row = Database::fetch_row ( $res ) ) {
					$competencies [$row ['id']] = $row ['name'] . (($competency_cnt [$row ['id']]) ? "&nbsp;(" . $competency_cnt [$row ['id']] . ")" : "");
				}
				echo form_dropdown ( "competency_id", $competencies, NULL, 'id="competency_id" style="height:22px;"' );*/
				?> <input type="button" value=" 搜索 " class="inputSubmit"
					onclick="searchCourses('keyword')" /></td>
			</tr>
			<!-- <tr align="center">
				<th>可选课程</th>
				<th>操作</th>
				<th>已选课程</th>
			</tr> -->
			<tr>
				<td align="left"><select name="source_select2[]" size="12"
					id="source_select2[]" style="width: 300px;"
					ondblclick="moveItem_l2r(G('source_select2[]'),G('target_select2[]'),false)"
					multiple="true">
				</select></td>
				<td align="center">
				<p><input type="button" value=">>"
					onclick="moveItem_l2r(this.form.elements['source_select2[]'],this.form.elements['target_select2[]'],true)"
					class="formbtn" /></p>
				<p><input type="button" value=">"
					onclick="moveItem_l2r(this.form.elements['source_select2[]'],this.form.elements['target_select2[]'],false)"
					class="formbtn" /></p>
				<p><input type="button" value="<" onclick="
					moveItem_r2l(this.form.elements['target_select2[]'],this.form.elements['source_select2[]'],false)" class="formbtn" /></p>
				<p><input type="button" value="<<" onclick="
					moveItem_r2l(this.form.elements['target_select2[]'],this.form.elements['source_select2[]'],true)" class="formbtn" /></p>
				<p><input type="button" value="<?=get_lang ( "Empty" )?>"
					onclick="clearOptions(this.form.elements['source_select2[]'])"
					class="formbtn" /></p>
				</td>
				<td align="left"><select name="target_select2[]"
					id="target_select2[]" size="12" style="width: 300px" multiple
					ondblclick="moveItem_r2l(this.form.elements['target_select2[]'],this.form.elements['source_select2[]'],false)">
				</select></td>
			</tr>
		</table>
		</td>
	</tr>

	<tr>
		<td colspan="3" align="center" class="formTableTd"><input
			type="submit" name="removeCourse" class="inputSubmit"
			value="<?=get_lang ( "Ok" )?>" onclick="validate()" />&nbsp;&nbsp;  &nbsp;&nbsp;
		<input class="cancel" type="button"
			onclick="javascript:self.parent.tb_remove();" value="<?=get_lang ( 'Cancel' )?>" />
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
    $.ajax({type:"post", data:{action:"get_user_list",keyword:keyword_val,
        dept_id:$("#dept_id").val(),org_id:$("#org_id").val()},
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

function searchCourses(keyword)
{
    var url="<?=api_get_path ( WEB_CODE_PATH ) . "admin/ajax_actions.php"?>";
    var keyword_val=elements['keyword2'].value;
    if(keyword_val=="undefined") keyword_val="";
    $.ajax({type:"post", data:{action:"get_course_list2",keyword:keyword_val,
        category_code:$("#category_code").val(),competency_id:$("#competency_id").val()},
        url:url, dataType:"json",cache:false,
				success:function(data){
					var obj=elements['source_select2[]'];
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
	select_items("target_select[]");
	select_items("target_select2[]");
	return true;
}
</script></form>
</div>
<?php
		Display::display_footer ();
