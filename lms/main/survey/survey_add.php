<?php
$language_file = 'survey';
$cidReset = true;
require_once ('../inc/global.inc.php');
require_once ('survey.inc.php');

api_protect_admin_script ();

$tool_name = get_lang ( 'CreateNewSurvey' );
$form = new FormValidator ( 'frm_survey' );
//$form->addElement ( 'header', 'header', $tool_name );
$form->addElement ( 'hidden', 'action', 'add_save' );
$form->addElement ( "hidden", "active", "0" );

//代码
$survey_code = $form->addElement ( 'text', 'code', get_lang ( 'SurveyCode' ), array ('class' => 'inputText', 'maxlength' => '30' ) );
$form->addRule ( 'code', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'code', '', 'maxlength', 30 );
$defaults ['code'] = 'Survey' . date ( 'mdHis' );

//标题
$form->addElement ( 'text', 'title', get_lang ( 'SurveyTitle' ), 'class="inputText" style="width:66%"' );
$form->addRule ( 'title', get_lang ( 'PleaseEnterSurveyTitle' ), 'required' );

//可进入的时间
$form->add_calendar_duration ( null, "start_date", 'end_date', get_lang ( 'ValidDuration' ), FALSE );
$defaults ['start_date'] = date ( 'Y-m-d' );
$startdateandxdays = time () + 864000; // today + 10 days
$defaults ['end_date'] = date ( 'Y-m-d', $startdateandxdays );

//选项显示方式
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'option_display_type', null, get_lang ( 'OptionDisplayTypeHorizontal' ), 0 );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'option_display_type', null, get_lang ( 'OptionDisplayTypeVertical' ), 1 );
$form->addGroup ( $group, null, get_lang ( 'OptionDisplayType' ), '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$defaults ["option_display_type"] = 1;

//尝试次数
//$attempt_option = range ( 0, 5 );
//$attempt_option [0] = get_lang ( 'Infinite' );
//$form->addElement ( 'select', 'max_attempts', get_lang ( 'MaxAttempts' ), $attempt_option, array ('style' => "width:15%" ) );
//$form->addRule ( 'max_attempts', get_lang ( 'Numeric' ), 'numeric' );
//$defaults['max_attempts']=1;
$form->addElement ( "hidden", "max_attempt", "1" );

//显示结果
$radios_results_disabled = array ();
$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'Yes' ), '0' );
$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'No' ), '1' );
$form->addGroup ( $radios_results_disabled, null, get_lang ( 'ShowResultsToUser' ), "&nbsp;&nbsp;", FALSE );
$defaults ['results_disabled'] = 1;

$form->addElement ( 'textarea', 'intro', get_lang ( 'SurveyIntroduction' ), array ('id' => 'description', 'style' => 'width:100%;height:150px', 'wrap' => 'virtual', 'class' => 'inputText' ) );
//$defaults ['intro'] = '<style>.exercise_guide li {padding-top:6px}</style><table width="99%" border="0" cellpadding="3" cellspacing="0"><tr><td width="100%" valign="top" align="left"></td></tr></table>';
//$fck_attribute ['Height'] = '100';


//$form->addElement ( 'html_editor', 'survey_thanks', get_lang ( 'SurveyThanks' ), null, array ('ToolbarSet' => 'Survey', 'Width' => '100%', 'Height' => '130', 'ToolbarStartExpanded' => false ) );


$group = array ();
$group [] = $form->createElement ( 'submit', 'submitExercise', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submitExercise', '&nbsp;', null, false );
$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$values = $form->exportValues ();
	$return = SurveyManager::store_survey ( $values );
	
	if ($return ['type'] == 'error') {
		Display::display_header ( $tool_name );
		Display::display_error_message ( get_lang ( $return ['message'] ), false );
		$form->display ();
	}
	$redirect_url = "index.php?message=" . $return ['message'];
	tb_close ( $redirect_url );

} else {
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description' );
	Display::display_header ( $tool_name, FALSE );
	$form->display ();
}
Display::display_footer ();