<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'sortabletable.class.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');

api_protect_admin_script ();

if (! is_root ( api_get_user_name () )) {
	api_not_allowed ();
}

$tbl_card = Database::get_main_table ( 'bos_card' );


function get_sqlwhere() {
	$sql = "";
	if (is_not_blank ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
		$sql .= " AND  (card_no LIKE '%" . $keyword . "%' OR username LIKE '%" . $keyword . "%')";
	}
	
	$sql = trim ( $sql );
	return substr ( $sql, 3 );
}


function get_number_of_data() {
	global $tbl_card;
	$sql = "SELECT COUNT(id) AS total_number_of_items FROM $tbl_card ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	return Database::get_scalar_value ( $sql );
}


function get_data($from, $number_of_items, $column, $direction) {
	global $tbl_card;
	$sql = "SELECT
	id				AS col0,
	card_no		AS col1,
	passwd		AS col2,
	username	AS col3,
	created_date		AS col4,
	username	AS col5,
	id				AS col6
	FROM  $tbl_card ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	$data = array ();
	while ( $adata = Database::fetch_array ( $res, 'NUM' ) ) {
		$data [] = $adata;
	}
	return $data;
}


function modify_filter($log_id, $url_params) {
	$result .= '<a href="logging_list.php?action=delete_log&amp;id=' . intval($log_id) . '&amp;' . $url_params . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon (
			'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	return $result;
}


function delete_card($log_id) {
	global $tbl_card;
	$sql = "DELETE FROM $tbl_card WHERE id='" . intval(escape ( $log_id )) . "'";
	return api_sql_query ( $sql, __FILE__, __LINE__ );
}

$tool_name = '学习卡管理';
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$interbreadcrumb [] = array ("url" => 'card_list.php', "name" => $tool_name );
Display::display_header ( $tool_name ,FALSE);

if (isset ( $_GET ['action'] )) {
	switch ($_GET ['action']) {
		case 'show_message' :
			Display::display_normal_message ( stripslashes ( $_GET ['message'] ) );
			break;
		case 'delete' : //删除单条记录
			if (delete_card (getgpc('id','G') )) {
				Display::display_normal_message ( get_lang ( 'LogDeleted' ) );
			} else {
				Display::display_error_message ( get_lang ( 'CannotDeleteLog' ) );
			}
			break;
	}
}
if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'delete' : //批量删除
			$number_of_selected_items = count ( getgpc('id') );
			$number_of_deleted_items = 0;
			foreach ( getgpc('id') as $index => $item_id ) {
				if (delete_card ( $item_id )) {
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

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( ' {element} ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:150px", 'class' => 'inputText', 'title' => get_lang ( 'keyword' ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
echo '<div class="actions">';
$form->display ();
echo '</div>';

if (isset ( $_GET ['keyword'] )) {
	$parameters ['keyword'] = getgpc('keyword');
}

$table = new SortableTable ( 'adminLoggings', 'get_number_of_data', 'get_data', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );

$header_idx = 0;
$table->set_header ( $header_idx ++, '', false );
$table->set_header ( $header_idx ++, '卡号', true, null, array ('style' => 'width:160px' ) );
$table->set_header ( $header_idx ++, '密码', true, null, array ('width' => '80' ) );
$table->set_header ( $header_idx ++, '注册用户名', true, null, array ('width' => '80' ) );
$table->set_header ( $header_idx ++, '创建时间' );
$table->set_header ( $header_idx ++, '状态' );
$table->set_header ( $header_idx ++, '操作' );
$table->set_column_filter ( 6, 'modify_filter' );
$table->set_form_actions ( array ('delete' => get_lang ( 'BatchDelete' ) ) );
$table->display ();

Display::display_footer ();
?>