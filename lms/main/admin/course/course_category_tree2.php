<?php
$language_file = array ('courses','admin');
require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_block_anonymous_users();
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');

$tbl_course  = Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_category = Database::get_main_table(TABLE_MAIN_CATEGORY);

$sql="SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
$category_cnt=Database::get_into_array2($sql);

$objCrsMng=new CourseManager();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree.js"
	type=text/javascript></script>
<!-- <link href="<?=api_get_path ( WEB_CSS_PATH )?>default.css"
	type=text/css rel=StyleSheet>	 -->

<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>


<script language="JavaScript" type="text/JavaScript">
	var $$ = function(id) {return document.getElementById(id);}
	var is_show=0;
	function show_hide_oc(){
		if(is_show==1){
			d.closeAll();
			$$("oc").innerText="<?=get_lang('OpenAll') ?>";
			is_show=0;
		}else{
			
			d.openAll();
			$$("oc").innerText="<?=get_lang('CloseAll') ?>";
			is_show=1;
		}
	}
</script>
<style type="text/css">
html {
	SCROLLBAR-ARROW-COLOR: #88bad3;
	SCROLLBAR-FACE-COLOR: #dff1f5;
	SCROLLBAR-DARKSHADOW-COLOR: #f1fcff;
	SCROLLBAR-HIGHLIGHT-COLOR: #f1fcff;
	SCROLLBAR-3DLIGHT-COLOR: #a3c7df;
	SCROLLBAR-SHADOW-COLOR: #a3c7df;
	SCROLLBAR-TRACK-COLOR: #f2f7f9;
}
</style>

</head>
<body>
<a href='javascript:show_hide_oc();'>
<div id='oc' style='float: middle;'><?=get_lang('OpenAll') ?></div>
</a>
<div class=dtree>
<table cellspacing="5">
	<tr>
		<td align="left"></td>
	</tr>
	<tr>
		<td><script type=text/javascript>		
<!--
d = new dTree('d');
/*d.config.closeSameLevel=true;*/
d.config.useCookies=true;
d.config.useIcons=false;
d.add(0,-1,'<?=get_lang ( "CourseCategories" )?>','course_list.php','','List');
<?php

$objCrsMng->all_category_tree=array();
$category_tree=$objCrsMng->get_all_categories_tree(TRUE,-1);
	foreach ($category_tree as $category){		
		$url="course_list.php?category_id=".$category['id'];
		$cate_name=$category ['name'].(($category_cnt[$category ['id']])?"&nbsp;(".$category_cnt[$category ['id']].")":"" );
		if($category ['parent_id']){
			echo 'd.add(' . $category ['id'] . ',' . $category ['parent_id'] . ',"' . $cate_name. '","'.$url.'","","List");'."\n";
		}else{
			echo 'd.add(' . $category ['id'] . ',"0","' . $cate_name . '","'.$url.'","","List");'."\n";
		}
}
$my_org_id=$_SESSION["_user"]["org_id"];




?>
document.write(d);

//-->
</script></td>
	</tr>
</table>
</div>
</body>
</html>
