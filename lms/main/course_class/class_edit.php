<?php
/*
==============================================================================
	编辑班级信息
==============================================================================
*/
/**
==============================================================================
*	@package zllms.admin
==============================================================================
*/

// name of the language file that needs to be included 
$language_file = array('class_of_course');
require_once('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'course_class_manager.lib.php');

api_block_anonymous_users();
$this_section = SECTION_COURSES;
api_protect_course_script();

$is_allowed_edit=api_is_allowed_to_edit();

/*if(!$is_allowed_in_course){
	api_not_allowed();
}*/

//$htmlHeadXtra[] = get_table_style_ie6();
$interbreadcrumb[] = array ("url" => "class_list.php", "name" => get_lang('Class_of_course_list'));
$tool_name = get_lang('Class_of_course');

// setting the name of the tool
$tool_name = get_lang("EditClasses");


$tool_name = get_lang('ModifyClassInfo');
$idclass=getgpc('idclass','G');
$class_id = intval($idclass);
$class = CourseClassManager :: get_class_info($class_id);

$form = new FormValidator('edit_class','post','class_edit.php?idclass='.$class_id);

$form->addElement('header', 'header', get_lang('ModifyClassInfo'));
$form->add_textfield('name',get_lang('ClassName'),true,array('style'=>"width:250px",'class'=>'inputText'));
//$form->add_textfield('code',get_lang('ClassCode'),false,array('style'=>"width:250px",'class'=>'inputText'));

$group = array ();
$group[] = $form->createElement('submit', 'submit', get_lang('Ok'), 'class="inputSubmit"');
$group[] =$form->createElement('style_button', 'cancle',null,array('type'=>'button','class'=>"cancel",
	'value'=>get_lang('Cancel'),'onclick'=>'javascript:self.parent.tb_remove();'));
$form->addGroup($group, 'submit', '&nbsp;', null, false);

$defaults['name']=$class['name'];
$defaults['code']=$class['code'];
$form->setDefaults($defaults);

Display::setTemplateBorder($form, '99%');

if($form->validate())
{
	$values = $form->exportValues();
	if(CourseClassManager::class_name_exists($values['name']) && $values['name']!=$class['name']){
		$message=get_lang('CourseClassNameExist');
	}else{
		CourseClassManager :: update_name($values['name'], $class_id,$values['code']);
		$redirect_url='class_list.php';
		echo '<script>self.parent.location.href="'.$redirect_url.'";self.parent.tb_remove();</script>';exit;
	}	
}


Display::display_header($tool_name,FALSE);

if(isset($message) && !empty($message)){
	Display :: display_error_message($message);
}
$form->display();

Display :: display_footer();
