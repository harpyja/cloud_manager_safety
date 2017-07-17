<?php
/**
 ==============================================================================
 新增,编辑系统公告
 ==============================================================================
 */
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'cls.cms.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");

api_protect_admin_script ();

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$tbl_category = Database::get_main_table ( TABLE_CATEGORY );

$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$tool_name = get_lang ( 'SystemAnnouncements' );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {

	});</script>';

$form = new FormValidator ( 'system_announcement' );
//$form->addElement ( 'header', 'header', get_lang ( 'News' ) );

$form->add_textfield ( 'title', get_lang ( 'Title' ), true, array ('style' => "width:80%", 'class' => 'inputText' ) );

//分类
$sql = "SELECT id,name FROM " . $tbl_category . " WHERE module='sys_cms' AND parent_id=0 ORDER BY sort_order";
$cate_options = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$form->addElement ( 'select', 'category', get_lang ( 'AnnouncementsCategory' ), $cate_options, array () );

$group = array ();
$group [] = $form->createElement ( "radio", "visible", null, get_lang ( "StatusPublished" ), 1, array ('id' => 'visible_to1' ) );
$group [] = $form->createElement ( "radio", "visible", null, get_lang ( "StatusEdit" ), 0, array ('id' => 'visible_to0' ) );
$form->addGroup ( $group, null, get_lang ( 'State' ), '&nbsp;&nbsp;', false );

//内容
if (api_get_setting ( 'html_editor' ) == 'simple') {
	$form->addElement ( 'textarea', 'content', get_lang ( 'Content' ), array ('cols' => 50, 'rows' => 8 ) );
} else {
	$fck_attribute ['Width'] = '100%';
	$fck_attribute ['Height'] = '300';
	$fck_attribute ['ToolbarSet'] = 'Middle';
		$fck_attribute ["ToolbarStartExpanded"] = TRUE;
	$form->add_html_editor ( 'content', get_lang ( 'Content' ) );
}

//附件
/*
$form->addElement ( 'file', 'file', get_lang ( 'Attachment' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'edit') {
	$form->addElement ( 'static', 'fileUpload', "",
			get_lang ( "Attachment" ) . (! empty ( $attachment_uri ) ? "<a href=\"" . $http_www . "/" . $attachment_uri . "\">" . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)" : get_lang ( 'None' )) . "," . get_lang ( 'fileUploadedTip' ) );
}
$form->addRule ( 'file', get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( 0 ) . ' MB', 'maxfilesize', get_upload_max_filesize ( 0 ) * 1024 * 1024 );
$form->addRule ( 'file', get_lang ( 'UploadFileNameAre' ) . ' *.zip,*.rar,*.doc,*.xls,*.ppt,*.pdf', 'filename', '/\\.(zip|rar|doc|xls|ppt|pdf)$/' );
*/
$form->addElement ( 'hidden', 'action' );
$form->addElement ( 'hidden', 'id' );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

// 新增时表单
if (isset ( $_GET ['action'] ) && is_equal (getgpc("action","G"), 'add' )) {
	$values ['action'] = 'add_save';
	$values ['visible'] = 0;
}

//编辑
if (isset ( $_GET ['action'] ) && is_equal ( getgpc("action","G"), 'edit' )) {
	$values = CMSManager::get (intval(getgpc('id')) );
	$values ['action'] = 'edit_save';
	//var_dump($values);
	//附件
	$sql = "SELECT * FROM " . $tbl_attachment . " WHERE TYPE='SYS_CMS' AND ref_id='" . intval($values ['id']) . "'";
	$result1 = Database::query ( $sql, __FILE__, __LINE__ );
	$has_attachment = (Database::num_rows ( $result1 ) > 0);
	if ($has_attachment) {
		if ($attachment = Database::fetch_row ( $result1 )) {
			$attachment_id = intval($attachment ['id']);
			$attachment_name = $attachment ['old_name'];
			$attachment_uri = $attachment ['url'];
			$attachment_size = $attachment ['size'];
		}
	}
}
$form->setDefaults ( $values );

Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$values = $form->getSubmitValues ();
	//	var_dump ( $values ); exit ();
	$file_element = & $form->getElement ( 'file' );
	$redirect_url = 'main/admin/misc/cms_list.php';
	switch ($values ['action']) {
		case 'add_save' :
			$other_info = array ('created_user' => api_get_user_name (), 'created_date' => date ( 'Y-m-d H:i:s' ) );
			if ($insert_id = CMSManager::add ( $values ['title'], $values ['content'], $values ['category'], $values ['visible'], $other_info )) {
				
				//附件处理
				/*$file = AttachmentManager::hanle_sys_upload ( $file_element, 'SYS_CMS', $sys_attachment_path );
				if (isset ( $file ) && $file && is_array ( $file )) {
					$filename = $file ['name'];
					$fsize = $file ['size'];
					$file_uri = $file ['new_name'];
					$uniqid = $file ['attachment_uniqid'];
				} else {
					$redirect_url = $_SERVER ['PHP_SELF'] . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
					api_redirect ( $redirect_url );
				}
				
				//更新附件表
				$sql = "UPDATE " . $tbl_attachment . " SET ref_id='" . $insert_id . "' WHERE name='" . $uniqid . "'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$log_msg = get_lang ( 'AddNews' ) . "id=" . $insert_id;
				api_logging ( $log_msg, 'CMS', 'AddNews' );*/
				
				Display::display_msgbox ( get_lang ( 'NewsAdded' ), $redirect_url);
			}
			break;
		case 'edit_save' :
			$other_info = array ();
			CMSManager::update ( intval($values ['id']), $values ['title'], $values ['content'], $values ['category'], $values ['visible'], $other_info );
			
			/*$file = AttachmentManager::hanle_sys_upload ( $file_element, 'SYS_CMS', $sys_attachment_path );
			if (isset ( $file ) && $file && is_array ( $file )) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_uri = $file ['new_name'];
				$uniqid = $file ['attachment_uniqid'];
			} else {
				$redirect_url = $_SERVER ['PHP_SELF'] . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
				api_redirect ( $redirect_url );
			}
			
			if (strlen ( $file ['name'] ) > 0) {
				AttachmentManager::del_all_sys_attachment ( 'SYS_CMS', $values ['id'], $sys_attachment_path );
			}
			
			//更新附件表
			$sql = "UPDATE " . $tbl_attachment . " SET ref_id='" . Database::escape_string ( $values ['id'] ) . "' WHERE name='" . $uniqid . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$log_msg = get_lang ( 'EditNews' ) . "id=" . $values ['id'];
			api_logging ( $log_msg, 'CMS', 'EditNews' );*/
			
			Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url);
			break;
	}
}

Display::display_header ( $tool_name ,FALSE);
$form->display ();

Display::display_footer ();
