<?php
$language_file = array ('admin');
require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();
/*$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];*/

$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);

$this_module='position';
$tbl_category=Database::get_main_table ( TABLE_CATEGORY );
$table_position = Database :: get_main_table(TABLE_MAIN_SYS_POSITION);

$sql="SELECT id,count(*) FROM $tbl_category WHERE module='".$this_module."' GROUP BY id";
$res= api_sql_query($sql,__FILE__,__LINE__);
while($row=Database::fetch_array($res,'NUM')){
	$category_cnt[$row[0]]=$row[1];
}
Database::free_result($res);


function get_all_tree() {
	global $tbl_category,$table_position,$this_module;
	$all_tree=array();

	$sql = "SELECT * FROM " . $tbl_category . " WHERE module='".$this_module."' AND parent_id=0 ORDER BY sort_order";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );

	while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
		$all_tree [] = array ('id' => $row ['id'], 'parent_id' => $row ['parent_id'], 'name' => $row ['name'], 'description' => $row ['description'],'sort_order' => $row ['sort_order'] );
		/*$sql = "SELECT * FROM " . $tbl_category . " WHERE parent_id='".$row ['id']."' ORDER BY sort_order";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $row2 = Database::fetch_array ( $rs, 'ASSOC' ) ) {
			$all_tree [] = array ('id' => $row ['id'], 'parent_id' => $row ['parent_id'], 'name' => $row ['name'], 'description' => $row ['description'],'sort_order' => $row ['sort_order'] );			
			unset($row2);		
		}*/
	}
	return $all_tree;
}

function get_category_items($category_id){
	global $tbl_category,$table_position,$this_module;
	
	$sql = "SELECT * FROM  $table_position As t1 LEFT JOIN $tbl_category AS t2 ON t1.category_id=t2.id
	";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );

	
}

$all_trees=get_all_tree();

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
d.add(0,-1,'<?=get_lang ( "PositionStructure" )?>','category_list.php','','List');

<?php
	foreach ($all_trees as $item){		
		//$url=$item ['paren_id']?"position_list.php?id=".$item['id']:"position_list.php?pid=".$item['id'];
		$url="position_list.php?category_id=".$item['id'];
	
		echo 'd.add(' . $item ['id'] . ',' . $item ['parent_id'] . ',"' . $item ['name']. '","'.$url.'","","List");'."\n";
		
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
