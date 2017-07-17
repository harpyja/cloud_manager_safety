<?php
$language_file = array ('admin' );
require_once ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$dept_tree = array ();

$deptObj=new DeptManager();
$dept_tree=$deptObj->get_all_dept_tree();
$top_dept=$deptObj->get_top_dept();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>

<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/single_tree.js"
	type=text/javascript></script>
<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>
	
<script type="text/javascript" src="<?php echo api_get_path(WEB_JS_PATH) ?>utility.js"></script>
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
<div id="confirmMsg" class="NoteBox"  style="display:none">双击部门名称前单选框：选择一个部门并关闭本窗口；单击部门名称链接：选择一个部门,然后点击底部关闭按钮。<a href="#" onclick="javascript:$$('confirmMsg').style.display='none';"><span style="font-size: 12px; float:right">隐藏</span></a></div>
<div class=dtree>
<table cellspacing="5"><tr><td>
<a href="javascript: d.openAll();"><?=get_lang('OpenAll') ?></a> | <a href="javascript: d.closeAll();"><?=get_lang('CloseAll') ?></a>
&nbsp;<a href="#" onclick="javascript:$$('confirmMsg').style.display='';"><span style="font-size: 12px;">说明</span></a>
&nbsp;
</td>
</tr>
<tr><td>
<script type=text/javascript>
<!--
d = new dTree('d','../../res/dtree/img/dept/');
d.config.useIcons=false;
d.config.useRadio=true;
//d.config.useCheckbox=true;
/*d.config.closeSameLevel=true;*/
d.add(0,-1,'<?php //echo api_get_setting ( "Institution" );
echo get_lang('DeptView');
?>');
<?php
if($top_dept){
	echo 'd.add("' . $top_dept ['id'] . '","' . $top_dept ['pid'] . '","' . $top_dept ['dept_name'] . '","javascript:select_single_dept(\''.$top_dept['id'].'\',\''.$top_dept['dept_name'].'\');");'."\n";
	foreach ($dept_tree as $dept){
		//echo 'd.add(' . $dept_info ['id'] . ',' . $dept_info ['pid'] . ',"' . $dept_info ['dept_name'] . '");';
		echo 'd.add(' . $dept ['id'] . ',' . $dept ['pid'] . ',"' . $dept ['dept_name'] . '","javascript:select_single_dept(\''.$dept['id'].'\',\''.$dept['dept_name'].'\');");'."\n";
	}
}
?>
document.write(d);
/*for(var ii=0;ii<d.aNodes.length;ii++){
	document.write(d.aNodes[ii].id+"&nbsp;"+d.aNodes[ii].pid+"&nbsp;"+d.aNodes[ii].name+"<br/>");
}*/
//-->
</script>
</td></tr></table>
</div>
</body>
</html>