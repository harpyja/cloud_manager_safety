<?php
$language_file = array ('admin' );
require_once ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
//api_protect_admin_script ();
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');

//liyu: 课程分类树
/*$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
$course_categories=array();
$crs_categories=array();
$sql = "SELECT id,code,name FROM ".$table_course_category." WHERE parent_id IS NULL AND auth_course_child ='TRUE' ORDER BY tree_pos";
$res = api_sql_query($sql, __FILE__, __LINE__);
$has_children=Database::num_rows($res)>0;
while ($cat = Database::fetch_array($res,'ASSOC')){
	$course_categories[$cat['id']] = $cat['name'].'-'.$cat['code'];
	$crs_categories[$cat['id']] = $cat;
	if($has_children){
		CourseManager::get_categories($cat['id'],0);
	}
}*/
$objCrsMng=new CourseManager();
$category_tree=$objCrsMng->get_all_categories_tree(TRUE);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<title></title>

<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/radio_dtree.js"
	type=text/javascript></script>
<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>


<script type="text/javascript"
	src="<?php echo api_get_path(WEB_JS_PATH) ?>utility.js"></script>
<script type="text/javascript">
	var parent_window = getOpenner();
	var to_form = parent_window.<?php echo getgpc('FORM_NAME') ?>;	

	var to_id =   to_form.<?php echo getgpc('TO_ID');?>;
	var to_name = to_form.<?php echo getgpc('TO_NAME');?>;

	function select_single_dept(dept_id,dept_name)
	{
		//alert(dept_id+" "+dept_name);
		to_id.value=dept_id;
		to_name.value=dept_name;		
	}
	
	function getRadioSelected(dept_id,dept_name){
		//var nname = mytree.aNodes[nodeId].name;  
		//alert(dept_id+" "+dept_name);
		to_id.value=dept_id;
		to_name.value=dept_name;	
		top.window.close();
	}
</script>
<style type="text/css">
.NoteBox {
	background: #E2EFF6;
	border: 1px solid #5C9EBF;
	text-align: left;
	letter-spacing: 0px;
	padding: 5px 5px 5px 10px;
	text-indent: 0px;
	line-height: 20px;
	margin: 0 auto;
	font-size: 12px;
}
</style>
</head>
<body>
<div id="confirmMsg" class="NoteBox" style="display: none">双击分类名称前单选框：选择一个分类并关闭本窗口；单击分类名称链接：选择一个分类，然后点击底部关闭按钮。<a
	href="#" onclick="javascript:$$('confirmMsg').style.display='none';"><span
	style="font-size: 12px; float: right">隐藏</span></a></div>
<div class=dtree>
<table cellspacing="5">
	<tr>
		<td><a href="javascript: d.openAll();"><?=get_lang('OpenAll') ?></a> |
		<a href="javascript: d.closeAll();"><?=get_lang('CloseAll') ?></a>
		&nbsp;<a href="#"
			onclick="javascript:$$('confirmMsg').style.display='';"><span
			style="font-size: 12px;">说明</span></a> &nbsp;</td>
	</tr>
	<tr>
		<td><script type=text/javascript>
<!--
d = new dTree('d','../../res/dtree/img/dept/');
d.config.useIcons=false;
d.config.useRadio=true;
//d.config.useCheckbox=true;
/*d.config.closeSameLevel=true;*/
d.add(0,-1,'<?php echo get_lang('CourseCategories');?>');
<?php
foreach ($category_tree as $category){		
		if($category ['parent_id']){
			echo 'd.add(' . $category ['id'] . ',' . $category ['parent_id'] . ',\'' . $category ['name'] . '\',"javascript:select_single_dept('.$category['id'].',\''.$category['name'].'\');");'."\n";
			//echo 'd.add("' . $category ['code'] . '","' . $category ['parent_id'] . '","' . $category ['name'] . '");'."\n";
		}else{
			echo 'd.add(' . $category ['id'] . ',0,\'' . $category ['name'] . '\',"javascript:select_single_dept('.$category['id'].',\''.$category['name'].'\');");'."\n";
		}
	}
?>
document.write(d);

//-->
</script></td>
	</tr>
</table>
</div>
</body>
</html>
