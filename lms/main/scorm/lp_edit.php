<?php
include_once ("content_makers.inc.php");

api_protect_course_script();
Display::display_header(NULL,FALSE);

$defaults = array ();
$form = new FormValidator ( 'form1', 'post', 'lp_controller.php' );

//$form->addElement ( 'header', null, get_lang ( 'EditLPSettings' ) );

$form->addElement ( 'text', 'lp_name', (get_lang ( 'Title' )), array ('style' => 'width:60%', 'class' => 'inputText' ) );
//$form->applyFilter('lp_name', 'html_filter');
$form->addRule ( 'lp_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$lp_orig = $_SESSION ['oLP']->get_maker ();
/*$origin_select = &$form->addElement ( 'select', 'lp_maker', get_lang ( 'Origin' ) );
include_once ('content_makers.inc.php');
foreach ( $content_origins as $key => $origin ) {
	if ($lp_orig == $key) {
		$s_selected_origin = $key;
	}
	$origin_select->addOption ( $origin, $key );
}
$origin_select->setSelected ( $s_selected_origin );*/

$group = array ();
foreach ( $content_origins as $index => $origin ) {
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'content_maker', null, $origin, $index, array ('id' => 'maker_' . $index ) );
}
$form->addGroup ( $group, 'cm', get_lang ( 'ContentMaker' ), '&nbsp;&nbsp;&nbsp;', false );
$defaults['content_maker']=$lp_orig;

$form->addElement ( "hidden", "lp_encoding", "UTF-8" );

//推荐学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'LPLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:100px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ["learning_time"] = $_SESSION ['oLP']->get_learning_time ();

//学习顺序
$form->add_textfield ( 'learning_order', get_lang ( 'DisplayOrder' ), true, array ('id'=>'learning_order','style' => "width:100px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule('learning_order', get_lang('ThisFieldIsRequiredNumeric'), 'numeric');
$form->addRule('learning_order', get_lang('MustLargerThan0'), 'callback', 'range_check');
$defaults["learning_order"]=$_SESSION['oLP']->get_learning_order();

//作者 liyu:20091031
/*$fck_attribute['Height'] = '180';
$form->addElement('html_editor', 'lp_author', get_lang('Author'), array('size'=>80), array('ToolbarSet' => 'LearningPathAuthor', 'Width' => '100%', 'Height' => '100px') );*/
$form->addElement ( 'textarea', 'lp_author', get_lang ( 'Description' ), array ('cols' => 45, 'rows' => 3, 'class' => 'inputText' ) );
$form->applyFilter ( 'lp_author', 'html_filter' );

$defaults ['lp_name'] = ($_SESSION ['oLP']->get_name ());
$defaults ['lp_author'] = Security::remove_XSS ( $_SESSION ['oLP']->get_author () );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'SaveLPSettings' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->addElement ( 'hidden', 'action', 'update_lp' );
$form->addElement ( 'hidden', 'lp_id', $_SESSION ['oLP']->get_id () );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
$form->display ();
Display::display_footer ();
