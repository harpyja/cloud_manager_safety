<?php
$language_file = 'survey';
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "survey_edit");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";


$nameTools = get_lang('ExamStat');
Display::display_header ( $nameTools ,FALSE);

$myTools['survey_users'] = array(get_lang('SurveyUsers'),'add_user.gif');
$myTools['survey_questioins'] = array(get_lang('SurveyQuestions'),'quiz.gif');
//$myTools['survey_items'] = array(get_lang('SurveyItems'),'show_test_results.gif');
$myTools['survey_edit'] = array(get_lang('EditSurvey'),'edit.gif');


$total_tag_cnt=count($myTools);
$width_percent=round((101-$total_tag_cnt)/($total_tag_cnt+1))."%";

$g_action=  getgpc('action');
$strActionType =  (isset($g_action)?$g_action:'survey_edit');
echo '<table width="100%"><tr><td align="center"><table width="99%" class="tabTable"><tr>' . "\n";
echo '<td width="5%" class="tabOther"  height="25">&nbsp;</td>' . "\n";
foreach($myTools as $key => $value)
{
	$strClass = ($strActionType == $key ? 'tabSelected' : 'tabUnSelected');
	echo '<td width="'.$width_percent.'" class="' . $strClass . '" valign="bottom"><a href="survey_modify_iframe.php?survey_id='.$survey_id.'&action=' . $key . '">' . Display::return_icon($value[1], array ('style' => 'vertical-align: middle;' )) . "&nbsp;" .$value[0] . "</a></td>\n";
	echo '<td width="1%" class="tabOther">&nbsp;</td>' . "\n";
}
echo '<td class="tabOther">&nbsp;</td>' . "\n";
echo '</tr></table></td></tr></table>' . "\n";

echo '<div id="frm"><iframe id="List" name="List" src="'.$action.'.php?survey_id='.$survey_id.'"
	frameborder="0" width="100%" height="400px"></iframe></div>';
//include $action.'.php';

Display::display_footer ();
?>