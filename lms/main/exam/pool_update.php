<?php
$language_file = array ('exercice', 'admin' );
include_once ('../inc/global.inc.php');

api_protect_admin_script();
if (! isRoot ()) api_not_allowed ();

include ('cls.question_pool.php');
$objQuestionPool = new QuestionPool ();

$id = isset ( $_REQUEST ["id"] ) ? intval(getgpc ( "id" ) ): "0";
 
 
$htmlHeadXtra [] = import_assets ( "commons.js" );

//$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('Exam'));
$tool_name = get_lang ( 'QuestionPool' );

$form = new FormValidator ( 'exam_ae' );

//$form->addElement ( 'header', 'header', $tool_name );

if (is_equal ( $_GET ['action'], 'add_pool' )) {
	$form->addElement ( 'hidden', 'action', 'add_pool_save' );
}  elseif (is_equal ( $_GET ['action'], "edit_pool" )) {
	$item_info = $objQuestionPool->get_info ( $id );
	$form->addElement ( 'hidden', 'id', $id );
	$form->addElement ( 'hidden', 'action', 'edit_pool_save' );
} 

$form->addElement ( 'text', 'pool_name', get_lang ( 'PoolName' ), array ('maxlength' => 40, 'style' => "width:70%", 'class' => 'inputText' ) );
$form->addRule ( 'pool_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
if (is_equal ( $_GET ['action'], 'add_pool' )) {
	$pool_pos = $objQuestionPool->get_next_display_order ( );
	$item_info ["display_order"] = $pool_pos;
} 

//其它说明
//	$form->addElement('textarea', 'description', get_lang('Remark'),array('cols'=>50,'rows'=>5,'class'=>'inputText'));

//提交按钮
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="save"' );
if (is_equal ( $_GET ['action'], 'add_pool' )) {
	$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( 'SaveAndAdd' ), 'class="plus"' );
}
//$group[] = $form->createElement('style_button', 'button',get_lang('Back'), array('type'=>'button','value'=>get_lang("Back"),'class'=>"save",'onclick'=>'location.href=\'pool_list.php\';'));
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $item_info );

Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	$pool_name = ($data ['pool_name']);
	$pool_pos = ($data ['display_order']);
	$pool_desc = ($data ['description']);
	if (is_equal ( $_REQUEST ['action'], 'add_pool_save' )) {

		$sql_data = array ("pool_name" => $pool_name, "display_order" => $pool_pos );
		$objQuestionPool->add ( $sql_data );
		if (isset ( $data ['submit_plus'] )) {
			$redirect_url = "pool_update.php?action=add_pool&pid=" . $parent_id . "&refresh=1";
			api_redirect ( $redirect_url );
		}
		$redirect_url = 'pool_iframe.php';
		tb_close($redirect_url);
	}
	
	if (is_equal ( $_REQUEST ['action'], 'edit_pool_save' )) {
		$sql_data = array ( "pool_name" => $pool_name, "display_order" => $pool_pos );
		$objQuestionPool->edit ( $sql_data, "id='" . escape ( $data ["id"] ) . "'" );
		
		$redirect_url = 'pool_iframe.php?refresh=1';
		tb_close($redirect_url);
	}

}

Display::display_header($tool_name,FALSE);

if (! empty ( $_GET['message'] )) {
	$message = urldecode ( getgpc('message',"G") );
	Display::display_normal_message ( urldecode ( stripslashes ( $message ) ) );
}

if (isset ( $_GET['refresh'])) {
	echo '<script>self.parent.refresh_tree();</script>';
}

$form->display ();
