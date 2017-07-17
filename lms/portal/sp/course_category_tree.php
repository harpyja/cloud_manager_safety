<?php
$language_file [] = 'courses';
$language_file [] = 'admin';
include_once ("inc/app.inc.php");

$tbl_course  = Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_category = Database::get_main_table(TABLE_MAIN_CATEGORY);
$tbl_course_openscore = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );

if (api_is_platform_admin () OR api_get_setting('course_center_open_scope')==1) {
	$sql = "SELECT category_code,count(*) FROM $tbl_course  GROUP BY category_code";
} else {
	$sql = "SELECT category_code,count(*) FROM $tbl_course WHERE code IN (SELECT course_code FROM " . $tbl_course_openscore . " WHERE user_id='" . api_get_user_id () . "')  GROUP BY category_code";
}
$category_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );

$objCrsMng=new CourseManager();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<script src="js/jquery.min.js"
	type=text/javascript></script>
	
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree2.js"
	type=text/javascript></script>

<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>
	
<script language="JavaScript"	type="text/JavaScript">
var is_show=0;
function show_hide_oc(){
	if(is_show==1){
		d.closeAll();
		$("#oc").html("<?=get_lang('OpenAll') ?>");
		is_show=0;
	}else{
		d.openAll();
		$("#oc").html("<?=get_lang('CloseAll') ?>");
		
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

.dtree {
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 12px;

}
</style>

</head>
<body style="border:0px solid gray;height:380px;width:210px;">
<!--a href='javascript:show_hide_oc();'>
<div id='oc' style='float: right; font-size:12px'><?=get_lang('OpenAll') ?></div>
</a-->
<div class='dtreee' >
<table cellspacing="5">
	<tr>
		<td align="left"></td>
	</tr>
	<tr>
		<td><script type=text/javascript>		
d = new dTree('d');
/*d.config.closeSameLevel=true;*/
d.config.useCookies=true;
d.config.useIcons=false;

<?php
$objCrsMng->all_category_tree = array ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, - 1 );
if($category_tree) echo 'd.add(0,-1,"'.get_lang ( "CourseCategories" ).'","course_catalog.php","","_parent");';
foreach ( $category_tree as $category ) {
	$url = "course_catalog.php?category_id=" . $category ['id'];
	$cate_name = $category ['name'] . (($category_cnt [$category ['id']]) ? "&nbsp;(" . $category_cnt [$category ['id']] . ")" : "");
	if ($category ['parent_id']) {
		echo 'd.add(' . $category ['id'] . ',' . $category ['parent_id'] . ',"' . $cate_name . '","' . $url . '","","_parent");' . "\n";
	} else {
		echo 'd.add(' . $category ['id'] . ',"0","' . $cate_name . '","' . $url . '","","_parent");' . "\n";
	}
}

?>
document.write(d);

</script></td>
	</tr>
</table>
</div>
</body>
</html>
