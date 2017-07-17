<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'sortabletable.class.php');

$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();
/*$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];*/

$display_admin_menushortcuts = (api_get_setting ( 'display_admin_menushortcuts' ) == 'true' ? TRUE : FALSE);
$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );

$this_module = 'position';
$pid = isset ( $_GET ['category_id'] ) ? intval(getgpc('category_id')) : '0';

function get_sqlwhere() {
	global $this_module;
	
	if (isset ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_string (getgpc("keyword","G") ) );
		$sql .= " AND  (name LIKE '%" . $keyword . "%'";
	}
	
	if (isset ( $_GET ['category_id'] )) {
		$sql .= " AND category_id=" . Database::escape (intval(getgpc("category_id","G")) );
	}
	
	//$sql .= " AND module='".$this_module."'";
	

	$sql = trim ( $sql );
	return substr ( $sql, 3 );
}

function get_number_of_data() {
	$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );
	$sql = "SELECT COUNT(id) AS total_number_of_items FROM $table_position ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	return Database::get_scalar_value ( $sql );
}

function get_data($from, $number_of_items, $column, $direction) {
	$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );
	$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
	
	$sql = "SELECT
	t1.id				AS col0,
	t1.name		AS col1,
	t1.en_name		AS col2,
	t1.code		AS col3,
	t2.name		AS col4,
	t1.created_date		    AS col5,
	is_enabled		AS col6,
	t1.id				AS col7
	FROM  $table_position As t1 
	LEFT JOIN $tbl_category AS t2 ON t1.category_id=t2.id";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	//return api_sql_query_array_assoc($sql,__FILE__, __LINE__);
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$data = array ();
	while ( $adata = Database::fetch_array ( $res, 'NUM' ) ) {
		$data [] = $adata;
	}
	return $data;
}

function modify_filter($id, $url_params) {
	$result = '<a class="thickbox" href="position_update.php?action=edit&id=' . intval($id) . '&KeepThis=true&TB_iframe=true&height=350&width=580&modal=true">' . Display::return_icon ( 'edit.gif', get_lang ( 'Edit' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
	$result .= '<a href="position_list.php?action=delete_item&amp;id=' . intval($id) . '&amp;' . $url_params . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' .
			 Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	return $result;
}

function active_filter($active, $url_params, $row) {
	if ($active == '1') {
		$action = 'lock';
		$image = 'right';
	}
	if ($active == '0') {
		$action = 'unlock';
		$image = 'wrong';
	}
	
	$result = '<a href="position_list.php?action=' . $action . '&amp;' . $url_params . '">' . Display::return_icon ( $image . '.gif', get_lang ( "" ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	
	return $result;
}

function delete_item($id) {
	$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );
	$sql = "DELETE FROM $table_position WHERE id=" . Database::escape ( intval($id) );
	return api_sql_query ( $sql, __FILE__, __LINE__ );
}

$htmlHeadXtra [] = '<script type="text/javascript">function $$(id) {return document.getElementById(id);}</script>';

$htmlHeadXtra [] = Display::display_thickbox ();

$tool_name = get_lang ( 'PositionManagement' );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$interbreadcrumb [] = array ("url" => 'category_list.php', "name" => $tool_name );
Display::display_header ( $tool_name, FALSE );

if (isset ( $_GET ['action'] )) {
	switch (getgpc("action","G")) {
		case 'show_message' :
			Display::display_normal_message ( stripslashes (getgpc("message","G") ) );
			break;
		case 'delete_item' : //删除单条记录
			if (delete_position (intval(getgpc('id')) )) {
				Display::display_normal_message ( get_lang ( 'LogDeleted' ) );
			} else {
				Display::display_error_message ( get_lang ( 'CannotDeleteLog' ) );
			}
			break;
	}
}
if (isset ( $_POST ['action'] )) {
	switch (getgpc("action","P")) {
		case 'delete' : //批量删除
			$number_of_selected_items = count ( getgpc('id') );
			$number_of_deleted_items = 0;
			foreach ( getgpc('id') as $index => $item_id ) {
				if (delete_position ( $item_id )) {
					$number_of_deleted_items ++;
				}
			}
			if ($number_of_selected_items == $number_of_deleted_items) {
				Display::display_normal_message ( get_lang ( 'SelectedItemsDeleted' ) );
			} else {
				Display::display_error_message ( get_lang ( 'SomeItemNotDeleted' ) );
			}
			break;
	}
}

function delete_position($item_id) {
	$sql = "DELETE FROM " . $GLOBALS ['table_position'] . " WHERE id=" . Database::escape ( intval($item_id) );
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	return TRUE;
}

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( ' {element} ' );

$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:150px", 'class' => 'inputText', 'title' => get_lang ( 'keyword' ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );

echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">', '<a  class="thickbox" href="' . api_get_path ( WEB_CODE_PATH ) . 'admin/position/position_update.php?action=add&category_id=' . intval(getgpc ( 'category_id', 'G' )) . '&KeepThis=true&TB_iframe=true&height=360&width=580&modal=true">' .
		 Display::return_icon ( 'add_user_big.gif', get_lang ( 'AddPosition' ) ) . get_lang ( 'AddPosition' ) . '</a>', '</span>';
$form->display ();
echo '</div>';
if (isset ( $_GET ['keyword'] )) {
	$parameters ['keyword'] = getgpc('keyword');
}

$table = new SortableTable ( 'adminLoggings', 'get_number_of_data', 'get_data', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'PositionName' ) );
$table->set_header ( $idx ++, get_lang ( 'PositionEnName' ) );
$table->set_header ( $idx ++, get_lang ( 'PositionCode' ) );
$table->set_header ( $idx ++, get_lang ( 'InCategories' ) );
//$table->set_header($idx++, get_lang('PositionLevel'));
$table->set_header ( $idx ++, get_lang ( 'CreationDate' ) );
$table->set_header ( $idx ++, get_lang ( 'IsAvailable' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false );
$table->set_column_filter ( 6, 'active_filter' );
$table->set_column_filter ( 7, 'modify_filter' );
$table->set_form_actions ( array ('delete' => get_lang ( 'DeleteFromPlatform' ) ) );
//$table->set_dispaly_style_navigation_bar(NAV_BAR_BOTTOM);
$table->display ();

Display::display_footer ();
