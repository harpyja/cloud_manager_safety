<?php
$language_file = array('admin');
$cidReset = true;

require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();
$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);

require_once (api_get_path(INCLUDE_PATH).'lib/mail.lib.inc.php');

$this_module=isset($_REQUEST['module'])?getgpc('module'):'sys_announce';
$tbl_category=Database::get_main_table ( TABLE_CATEGORY );
$table_position = Database :: get_main_table(TABLE_MAIN_SYS_POSITION);

$id = isset($_GET['id'])?  intval(getgpc('id','G')):'0';
$pid = isset ( $_GET ['pid'] ) ? intval(getgpc('pid')) : '0';

if (isset ( $_GET ['action'] )) {
	switch (getgpc("action","G")) {
		case 'show_message' :
			if (isset ( $_GET ['message'] ))
			Display::display_normal_message ( stripslashes ( urldecode (getgpc("message","G") ) ) );
			break;
		case 'delete' :
			$sql="DELETE FROM ".$tbl_category." WHERE id=".Database::escape($id);
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			//$log_msg=get_lang('DelDeptInfo')."id=".getgpc ( 'id', 'G' );
			//api_logging($log_msg,'DEPT');
			api_redirect("category_list.php?module=".$this_module."&message=".get_lang ( 'CategoryDeleteSuccess' ));
			break;
	}
}


//JQuery,Thickbox
$htmlHeadXtra [] = Display::display_thickbox ();

$htmlHeadXtra[]='<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';
if(!empty($_GET['message'])){
	$message = urldecode(getgpc('message'));
}
//$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('Exam'));

Display::display_header ( $tool_name ,FALSE);

//顶部链接
echo '<div class="actions">';
echo link_button('add_dept.gif', 'AddCategory', 'category_update.php?action=add&module='.$this_module, '40%', '60%');
echo '</div>';


display_category_list();


function display_category_list() {
	global $tbl_category,$this_module;

	$table_header[] = array(get_lang("CategoryName"));
	$table_header [] = array (get_lang ( "CategoryCode" ) );
	//$table_header[] = array(get_lang("Remark"));
	$table_header[] = array(get_lang("Actions"),false,null,array('width'=>'80'));

	$sql = "SELECT * FROM " . $tbl_category . " WHERE module='".$this_module."' AND parent_id=0 ORDER BY sort_order";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$categories=api_store_result_array($res);
	$cnt=count($categories);
	//while ( $pool_set = Database::fetch_array ( $res, 'ASSOC' ) ) {
	$row_index=1;
		foreach($categories as $item){
			$row = array ();
			$row [] = $item ['name'];
			$row [] = $item ['code'];
			//$row [] = api_trunc_str2($item ['description'],70);

			$action = '';
			$action .= '&nbsp;&nbsp;'.link_button('edit.gif', 'Edit', 'category_update.php?action=edit&id=' . intval($item ['id']) . '&module='.$item['module'], '80%', '80%',false);
			$href='category_list.php?action=delete&amp;id=' . intval($item ['id']);
			$action .= '&nbsp;&nbsp;'.confirm_href('delete.gif', 'ConfirmYourChoice', 'Delete', $href);
			$row [] = $action;

			$table_data[] = $row;
			$row_index ++;
		}
		$query_vars['module']=$this_module;
		echo Display::display_table($table_header,$table_data);
}

Display::display_footer();
?>