<?php
/**
 ==============================================================================
 * @package zllms.admin
 ==============================================================================
 */

// name of the language file that needs to be included
$language_file = array ('class_of_course' );
require_once ('../inc/global.inc.php');

api_block_anonymous_users ();
api_protect_course_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');
$is_allowed_edit = api_is_allowed_to_edit ();
if (! $is_allowed_edit) api_not_allowed ();

$tool_name = get_lang ( 'AddClasses' );

$form = new FormValidator ( 'add_class' );
//$form->addElement ( 'header', 'header', get_lang ( 'AddClasses' ) );
$form->add_textfield ( 'name', get_lang ( 'ClassName' ), true, array ('style' => "width:250px", 'class' => 'inputText' ) );
//$form->add_textfield('code',get_lang('ClassCode'),false,array('style'=>"width:250px",'class'=>'inputText'));
$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '99%' );

if ($form->validate ()) {
	$values = $form->exportValues ();
	if (CourseClassManager::class_name_exists ( $values ['name'] )) {
		$message = get_lang ( 'CourseClassNameExist' );
	} else {
		CourseClassManager::create_class ( $values ['name'], $values ['code'] );
		$redirect_url = 'class_list.php';
		tb_close ( $redirect_url );
	}

}

Display::display_header ( $tool_name, FALSE );

$form->display ();

Display::display_footer ();
