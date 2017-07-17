<?php
$language_file = array ('admin' );
require_once ('../inc/global.inc.php');
//$this_section = SECTION_PLATFORM_ADMIN;
//api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$tree_url=getgpc('url');

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$dept_tree = array ();

$deptObj=new DeptManager();
//$dept_tree=$deptObj->get_all_dept_tree();
$top_dept=$deptObj->get_top_dept();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/ajax_dtree.js"
	type=text/javascript></script>
<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>

</head>
<body>
<br/>
<div class=dtree>
<table cellspacing="5"><tr><td>
<a href="javascript: d.openAll();"><?=get_lang('OpenAll') ?></a> | <a href="javascript: d.closeAll();"><?=get_lang('CloseAll') ?></a></td>
</tr>
<tr><td>
<div id="myTree" class="dtree"></div>
<script type=text/javascript>
<!--
d = new dTree('d');
//d.config.imagePath="";
/*d.config.closeSameLevel=true;*/

d.config.drawObj = document.getElementById('myTree');
d.config.url='ajax_actions.php?action=get_sub_dept';
d.config.target='mainFrame';
d.add({id:'0',pid:'-1',name:'<?php echo get_lang('DeptView');?>',hasChild:true });

<?php
if($top_dept){
	echo 'd.add({id:\'' . $top_dept ['id'] . '\',pid:\'' . $top_dept ['pid'] . '\',name:\'' . $top_dept ['dept_name'] . '\',url:\''.$tree_url.'?'.$_SERVER["QUERY_STRING"].'\',target:\'mainFrame\',hasChild:true});';	
}
?>
d.render();
//-->
</script>
</td></tr></table>
</div>
</body>
</html>
