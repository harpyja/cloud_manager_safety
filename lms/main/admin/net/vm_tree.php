<?php
$language_file = array ('admin' );
require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$tree_url = getgpc ( 'url' );

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$dept_tree = array ();

$deptObj = new DeptManager ();
$dept_tree = $deptObj->get_all_dept_tree ();
$top_dept = $deptObj->get_top_dept ();

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$sql = "SELECT dept_id, count(user_id) FROM $tbl_user WHERE dept_id>0 GROUP BY dept_id";
$user_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );

$sql = "SELECT org_id, count(user_id) FROM $tbl_user GROUP BY org_id";
$org_user_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree.js"
	type=text/javascript></script>
<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>

</head>
<body>
<div class=dtree>
<table cellspacing="5">
<table cellspacing="5">
	<tr>
		<td><a href="javascript: d.openAll();"><?=get_lang('OpenAll') ?></a> |
		<a href="javascript: d.closeAll();"><?=get_lang('CloseAll') ?></a></td>
	</tr>
	<tr>
		<td><script type=text/javascript>
<!--
d = new dTree('d');
/*d.config.closeSameLevel=true;*/
d.add(0,-1,'<?php
//echo api_get_setting ( "Institution" );
echo get_lang('DeptView');
?>');
<?php
if ($top_dept) {
	echo 'd.add(' . intval($top_dept ['id']) . ',' . intval($top_dept ['pid']) . ',"' . trim ( $top_dept ['dept_name'] ) . '","' . 'user_list.php' . '","","List");' . "\n";
	foreach ( $dept_tree as $dept ) {
		if ($dept ['pid'] == DEPT_TOP_ID) {
			$tree_url = "user_list.php?keyword_org_id=" . intval($dept ['id']);
			$dept_name = trim($dept ['dept_name']) . ($user_cnt [$dept ['id']] ? ' (' . $user_cnt [intval($dept ['id'])] . ', ' . $org_user_cnt [$dept ['id']] . ') ' : '');
		} else {
			$tree_url = "user_list.php?keyword_deptid=" . intval($dept ['id']);
			$dept_name = trim($dept ['dept_name']) . ($user_cnt [$dept ['id']] ? ' (' . $user_cnt [intval($dept ['id'])] . ') ' : '');
		}
		
		echo 'd.add(' . $dept ['id'] . ',' . $dept ['pid'] . ',"' . $dept_name . '","' . $tree_url . '","","List");' . "\n";
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
