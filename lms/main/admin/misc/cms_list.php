<?php
/**
 ==============================================================================
 * 
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();

include_once (api_get_path ( LIBRARY_PATH ) . 'cls.cms.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );

$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$form_action = getgpc ( "action" );

$htmlHeadXtra [] = Display::display_thickbox ();
$tool_name = get_lang ( 'News' );

$redirect_url = 'main/admin/misc/cms_list.php';
if (isset ( $_GET ['action'] )) {
	switch ($form_action) {
		case "make_visible" : //显示
			CMSManager::set_visibility (intval(getgpc('id')), 1 );
			$log_msg = get_lang ( 'AnnoMakeVisible' ) . "news_id=" . intval(getgpc('id'));
			api_logging ( $log_msg, 'CMS', 'AnnoMakeVisible' );
			break;
		
		case "make_invisible" : //隐藏
			CMSManager::set_visibility ( intval(getgpc('id')), 0 );
			$log_msg = get_lang ( 'AnnoMakeVisible' ) . "news_id=" . intval(getgpc('id'));
			api_logging ( $log_msg, 'CMS', 'AnnoMakeInvisible' );
			break;
		
		case "delete" : // 删除		
			CMSManager::delete ( intval(getgpc('id')) );
			$log_msg = get_lang ( 'DelNews' ) . "id=" . intval(getgpc('id'));
			api_logging ( $log_msg, 'CMS', 'DelAnnouncement' );
			Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url);
			break;
	}
}

if (isset ( $_POST ['action'] )) {
	switch ($form_action) {
		case "" : // 批量删除
			foreach ( getgpc('id') as $index => $id ) {
				CMSManager::delete ( intval($id) );
				$ann_ids .= intval($id) . ",";
			}
			$log_msg = get_lang ( 'BatchDelAnnouncemnet' ) . "ids=" . $ann_ids;
			api_logging ( $log_msg, 'CMS', 'BatchDelNews' );
			Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url);
			break;
	}
}

Display::display_header ( $tool_name);
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$form->addElement ( 'text', 'keyword', null, array ('style' => "width:20%", 'class' => 'inputText', 'title' => '' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

if (isset ( $_GET ['message'] ) && $_GET ['message']) {
	Display::display_normal_message ( urldecode ( getgpc ( 'message', 'G' ) ) );
}

//新增按钮
echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
echo link_button ( 'announce_add.gif', 'AddNews', 'cms_update.php?action=add', '80%' ,'80%');
echo link_button ( 'new_folder.gif', 'CategoriesMgr', '../misc/category_list.php?module=sys_cms', '90%', '80%' );
echo '</span>';
$form->display ();
echo '</div>';

$query_vars = array ();
$sql_where = "";
if (isset ( $_GET ['keyword'] )) {
	$query_vars ['keyword'] = getgpc('keyword');
	$keyword = trim ( Database::escape_str (getgpc("keyword","G"), TRUE ) );
	if (! empty ( $keyword )) {
		$sql_where .= " AND  t1.title LIKE '%" . $keyword . "%'";
	}
}

//列表
$table_header [] = array ();
$table_header [] = array (get_lang ( 'AnnouncementsCategory' ) );
$table_header [] = array (get_lang ( 'Title' ) );
$table_header [] = array (get_lang ( 'CreationDate' ), FALSE, null, array ('width' => '140px' ) );
$table_header [] = array (get_lang ( 'State0' ), TRUE, null, array ('width' => '40px' ) );
$table_header [] = array (get_lang ( 'Actions' ), FALSE, null, array ('width' => '60px' ) );

$datalist = CMSManager::get_list ();
foreach ( $datalist as $index => $item ) {
	$row = array ();
	$row [] = intval($item ['id']);
	$row [] = $item ['name'];
	$row [] = $item ['title'];
	$row [] = substr ( $item ['created_date'], 0, 16 );
	if ($item ['visible'] == 1) {
		$row [] = icon_href ( 'visible.gif', 'MakeInvisiable', 'cms_list.php?action=make_invisible&id=' . intval($item ['id']) );
	} else {
		$row [] = icon_href ( 'invisible.gif', 'MakeVisiable', 'cms_list.php?action=make_visible&id=' . intval($item ['id']) );
	}
	
	$action = link_button ( 'edit.gif', 'Edit', 'cms_update.php?action=edit&id=' . intval($item ['id']), '80%','80%', false, false );
	$action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'cms_list.php?action=delete&id=' . intval($item ['id']) );
	$row[]=$action;
	$table_data [] = $row;
}

$query_vars ['keyword'] = getgpc ( 'keyword' );
$sorting_options = array ();
$form_actions = array ('delete_selected' => get_lang ( 'BatchDelete' ) );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, $form_actions, "" );

Display::display_footer (TRUE);
