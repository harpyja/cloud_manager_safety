<?php
$language_file = 'survey';
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "survey_edit");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";

$defaults = SurveyManager::get_survey ( $survey_id );

$tool_name = get_lang ( 'EditSurvey' );
$form = new FormValidator ( 'frm_survey' );
//$form->addElement ( 'header', 'header', $tool_name );
$form->addElement ( 'hidden', 'action', 'edit_save' );
$form->addElement ( "hidden", "id", $survey_id );

$survey_code = $form->addElement ( 'text', 'code', get_lang ( 'SurveyCode' ), array ('class' => 'inputText', 'maxlength' => '30', 'readonly' => 'true' ) );
$form->addRule ( 'code', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'code', '', 'maxlength', 30 );

$form->addElement ( 'text', 'title', get_lang ( 'SurveyTitle' ), 'class="inputText" style="width:66%"' );
$form->addRule ( 'title', get_lang ( 'PleaseEnterSurveyTitle' ), 'required' );

//可进入的时间
$form->add_calendar_duration ( null, "start_date", 'end_date', get_lang ( 'ValidDuration' ), FALSE );
$defaults ['start_date'] = $defaults ['avail_from'];
$defaults ['end_date'] = $defaults ['avail_till'];

//选项显示方式
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'option_display_type', null, get_lang ( 'OptionDisplayTypeHorizontal' ), 0 );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'option_display_type', null, get_lang ( 'OptionDisplayTypeVertical' ), 1 );
$form->addGroup ( $group, null, get_lang ( 'OptionDisplayType' ), '&nbsp;&nbsp;&nbsp;&nbsp;', false );

//尝试次数
//$attempt_option = range ( 0, 5 );
//$attempt_option [0] = get_lang ( 'Infinite' );
//$form->addElement ( 'select', 'max_attempts', get_lang ( 'MaxAttempts' ), $attempt_option, array ('style' => "width:15%" ) );
//$form->addRule ( 'max_attempts', get_lang ( 'Numeric' ), 'numeric' );
$form->addElement ( "hidden", "max_attempt", "1" );

//显示结果
$radios_results_disabled = array ();
$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'Yes' ), '0' );
$radios_results_disabled [] = FormValidator::createElement ( 'radio', 'results_disabled', null, get_lang ( 'No' ), '1' );
$form->addGroup ( $radios_results_disabled, null, get_lang ( 'ShowResultsToUser' ), "&nbsp;&nbsp;", FALSE );

$form->addElement ( 'textarea', 'intro', get_lang ( 'SurveyIntroduction' ), array ('id' => 'description', 'style' => 'width:100%;height:250px', 'wrap' => 'virtual', 'class' => 'inputText' ) );
//$fck_attribute ['Height'] = '100';
//$form->addElement ( 'html_editor', 'surveythanks', get_lang ( 'SurveyThanks' ), null, array ('ToolbarSet' => 'Survey', 'Width' => '100%', 'Height' => '130', 'ToolbarStartExpanded' => false ) );


// submit
$group = array ();
$group [] = $form->createElement ( 'submit', 'submitExercise', get_lang ( 'Ok' ), 'class="inputSubmit"' );
//$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submitExercise', '&nbsp;', null, false );
$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$values = $form->exportValues ();
	//var_dump($values);exit;
	$return = SurveyManager::store_survey ( $values );
	//var_dump($return);exit;

	if ($return ['type'] == 'error') {
		Display::display_header ( $tool_name );
		Display::display_error_message ( get_lang ( $return ['message'] ), false );
		$form->display ();
	}
	$redirect_url = "survey_edit.php?survey_id=" . $return ['id'];
	api_redirect ( $redirect_url );
} else {
	$htmlHeadXtra [] = Display::display_thickbox ();
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description');
	$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
	$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
	Display::display_header ( NULL ,FALSE);
	
	$html = '<div id="demo" class="yui-navset">';
	$html .= '<ul class="yui-nav">';
	$html .= '<li><a href="survey_users.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'SurveyUsers' ) . '</em></a></li>';
	$html .= '<li><a href="survey_questioins.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'SurveyQuestions' ) . '</em></a></li>';
	$html .= '<li class="selected"><a href="survey_edit.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'EditSurvey' ) . '</em></a></li>';
	$html .= '</ul>';
	$html .= '<div class="yui-content"><div id="tab1">';
	echo $html;
	$form->display ();
	echo '</div></div></div>';
}
Display::display_footer();