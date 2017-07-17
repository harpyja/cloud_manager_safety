<?php
$language_file = array('admin');
$cidReset = true;

require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();
$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);

require_once (api_get_path(INCLUDE_PATH).'lib/mail.lib.inc.php');

$this_module='position';
$tbl_category=Database::get_main_table ( TABLE_CATEGORY );
$table_position = Database :: get_main_table(TABLE_MAIN_SYS_POSITION);

$pid = isset ( $_GET ['pid'] ) ? getgpc('pid') : '0';

$htmlHeadXtra[] = '
<script language="JavaScript" type="text/JavaScript">
<!--
//-->
</script>';


$htmlHeadXtra[]=Display::display_thickbox();
$htmlHeadXtra[]='<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';
if(!empty($_GET['message'])){
	$message = urldecode(getgpc('message'));
}

Display::display_header ( null ,FALSE);

//顶部链接
echo '<div class="actions">';
//echo '<a class="thickbox" href="category_update.php?action=add&KeepThis=true&TB_iframe=true&height=300&width=580&modal=true">' . Display::return_icon ( "add_dept.gif" ) . '&nbsp;' . get_lang ( 'AddCategory' ) . '</a>';
echo '&nbsp;&nbsp;',
			 '<a  class="thickbox" href="'.api_get_path(WEB_CODE_PATH).'admin/position/position_update.php?action=add&KeepThis=true&TB_iframe=true&height=360&width=580&modal=true">'.Display::return_icon('add_user_big.gif',get_lang('AddPosition')).get_lang('AddPosition').'</a>';									
echo '</div>';

display_category_list();

function display_category_list() {
	global $tbl_category,$this_module;

	$sorting_options = array();
	$sorting_options['column']=0;
	$sorting_options['default_order_direction']='DESC';

	$table_header[] = array(get_lang("CategoryName"));
	$table_header[] = array(get_lang("PositionCount"));
	$table_header[] = array(get_lang("Remark"));
	$table_header[] = array(get_lang("Actions"),null,array('width'=>'150'));


	$sql = "SELECT * FROM " . $tbl_category . " WHERE module='".$this_module."' AND parent_id=0 ORDER BY sort_order";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$categories=api_store_result_array($res);
	$cnt=count($categories);
	//while ( $pool_set = Database::fetch_array ( $res, 'ASSOC' ) ) {
	$row_index=1;
	if(is_array($categories) && $cnt>0){
		foreach($categories as $item){
			$row = array ();
			$row [] = '<a href="position_list.php?category_id='.intval($item['id']).'">'.$item ['name'].'</a>';
			$row [] = _get_position_count(intval($item['id']));
			$row [] = $item ['description'];


			$action = '&nbsp;&nbsp;<a href="position_list.php?category_id=' . intval($item ['id']) . '">' . Display::return_icon ( '2rightarrow.gif', get_lang ( 'PositionInCategory' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
			//$action .= '&nbsp;&nbsp;<a href="pool_ae.php?id=' . $dept_info ['id'] . '">' . Display::return_icon ( 'edit.gif', get_lang ( 'Edit' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';

			//$action .= '&nbsp;&nbsp;<a href="dept_list.php?action=delete_dept&amp;id=' . $dept_info ['id'] . '&amp;pid='.$dept_info['pid'].'" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';

				
			$row [] = $action;

			$table_data[] = $row;
			$row_index ++;
		}
		Display::display_non_sortable_table($table_header,$table_data,array(),$query_vars);
	}else{
		Display::display_normal_message(get_lang('TheListIsEmpty'));
	}
}

function _get_position_count($category_id=NULL){
	global $tbl_category,$table_position,$this_module;
	if(! is_null($category_id)){
		$sql = "SELECT COUNT(*) FROM " . $table_position . " WHERE category_id=". Database::escape($category_id);
		return Database::get_scalar_value($sql);
	}
	return 0;
}


Display::display_footer(TRUE);
