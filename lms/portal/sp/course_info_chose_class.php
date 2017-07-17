<?php
$cidReset = true;
include_once ("inc/app.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');
$course_code = getgpc ( "course_code" );
$objCourse = new CourseManager ();

if (isset ( $_POST ['formSent'] ) && is_equal ( $_POST ['formSent'], "1" )) {
	
}
include_once ("inc/page_header2.php");
$all_classes= CourseClassManager::get_all_classes_info($course_code);
echo form_open('course_info_chose_class.php','method="post" id="theForm"',array('course_code'=>$course_code,'formSent'=>'1'));
?>
<style>table td{padding:6px;}</style>
<table >
<tr><td colspan="2">选修课程时建议你也注册到一个班级中去</td></tr>
<tr><td>课程班级</td><td><?=form_dropdown('course_class_id',$all_classes,'','id="course_class_id"')?></td></tr>
<tr><td colspan="2" align="center"><input type="button" value="确定" class="inputSubmit" onclick="validate();"/>&nbsp;
&nbsp;<input type="button" class="cancel" onclick="javascript:self.parent.tb_remove();" name="cancle" value="取消" />&nbsp;&nbsp;
</td></tr>
</table>
<script type="text/javascript">
function validate(){
	if($("#course_class_id").val()=='' || $("#course_class_id").val()=='0'){
		$.prompt("请选择一个课程班级! 如果没有可用的班级,请联系管理员");
		return false;
	}
	subscribe2Course('<?=$course_code?>');
	//return true;
}
</script>
<?php 

echo form_close();
?>