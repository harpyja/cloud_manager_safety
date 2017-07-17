<?php
$tbl_course_description = Database::get_course_table(TABLE_COURSE_DESCRIPTION);
$tbl_attachment	= Database::get_course_table(TABLE_TOOL_ATTACHMENT);
$course_dir   = api_get_path(SYS_COURSE_PATH).$_course['path'];
$cur_course_url=api_get_path('WEB_COURSE_PATH').api_get_course_id()."/";
$http_www=$cur_course_url.'attachments/';
$allowed_to_edit=api_is_allowed_to_edit() && api_is_course_admin();

$show_description_list = true;
$show_peda_suggest = true;
define('ADD_BLOCK', 8);
// Default descriptions
$default_description_titles = array();//标题
$default_description_titles[1]= get_lang('GeneralDescription');
$default_description_titles[2]= get_lang('Objectives');
$default_description_titles[3]= get_lang('Topics');
/*$default_description_titles[4]= get_lang('Methodology');
$default_description_titles[5]= get_lang('CourseMaterial');
$default_description_titles[6]= get_lang('HumanAndTechnicalResources');
$default_description_titles[7]= get_lang('Assessment');
$default_description_titles[8]= get_lang('NewBloc');*/


$question = array();//提示问题,建议填写的内容
$question[1]= get_lang('GeneralDescriptionQuestions');
$question[2]= get_lang('ObjectivesQuestions');
$question[3]= get_lang('TopicsQuestions');
/*$question[4]= get_lang('MethodologyQuestions');
$question[5]= get_lang('CourseMaterialQuestions');
$question[6]= get_lang('HumanAndTechnicalResourcesQuestions');
$question[7]= get_lang('AssessmentQuestions');
$question[8]= get_lang('OtherQuestions');*/

$information = array();//提示信息
$information[1]= get_lang('GeneralDescriptionInformation');
$information[2]= get_lang('ObjectivesInformation');
$information[3]= get_lang('TopicsInformation');
/*$information[4]= get_lang('MethodologyInformation');
$information[5]= get_lang('CourseMaterialInformation');
$information[6]= get_lang('HumanAndTechnicalResourcesInformation');
$information[7]= get_lang('AssessmentInformation');
$information[8]= get_lang('OtherInformation');*/


$default_description_icon = array();//图片
$default_description_icon[1]= 'edu_miscellaneous.gif';
$default_description_icon[2]= 'spire.gif';
$default_description_icon[3]= 'kcmdf_big.gif';
/*$default_description_icon[4]= 'misc.gif';
$default_description_icon[5]= 'laptop.gif';
$default_description_icon[6]= 'personal.gif';
$default_description_icon[7]= 'korganizer.gif';
$default_description_icon[8]= 'ktip.gif';*/

$default_description_small_icon = array();//小图片
$default_description_small_icon[1]= 'edu_miscellaneous_small.gif';
$default_description_small_icon[2]= 'spire_small.gif';
$default_description_small_icon[3]= 'kcmdf_big_small.gif';
/*$default_description_small_icon[4]= 'misc_small.gif';
$default_description_small_icon[5]= 'laptop_small.gif';
$default_description_small_icon[6]= 'personal_small.gif';
$default_description_small_icon[7]= 'korganizer_small.gif';
$default_description_small_icon[8]= 'ktip_small.gif';
*/

$default_description_title_editable = array();
$default_description_title_editable[1] = false;
$default_description_title_editable[2] = true;
$default_description_title_editable[3] = true;
/*$default_description_title_editable[4] = true;
$default_description_title_editable[5] = true;
$default_description_title_editable[6] = true;
$default_description_title_editable[7] = true;
$default_description_title_editable[8] = true;*/
?>
