<?php
// 题型
define ( 'UNIQUE_ANSWER', 1 ); //单选题
define ( 'MULTIPLE_ANSWER', 2 ); //多选题（选中所有才给分）
define ( 'FREE_ANSWER', 5 ); //简答题 5=>7


define ( 'ALL_ON_ONE_PAGE', 0 );
define ( 'ONE_PER_PAGE', 1 );
define ( 'ONE_TYPE_PER_PAGE', 2 );

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );

$_question_types = array ('0' => get_lang ( "All" ), UNIQUE_ANSWER => get_lang ( "MultipleChoice" ), MULTIPLE_ANSWER => get_lang ( "MultipleResponse" ), FREE_ANSWER => get_lang ( "FreeAnswer" ) );

$tbl_survey = Database::get_main_table ( TABLE_SURVEY );
$tbl_survey_question_group = Database::get_main_table ( TABLE_SURVEY_QUESTIOIN_GROUP );
$tbl_survey_question = Database::get_main_table ( TABLE_SURVEY_QUESTIOIN );
$tbl_survey_question_option = Database::get_main_table ( TABLE_SURVEY_QUESTIOIN_OPTION );
$tbl_survey_answer = Database::get_main_table ( TABLE_SURVEY_ANSWER );
$tbl_survey_user = Database::get_main_table ( TABLE_SURVEY_USER );

require_once (api_get_path ( LIBRARY_PATH ) . "surveymanager.lib.php");
require_once ("cls.question.php");
require ("cls.answer.php");
require ("cls.unique_answer.php");
require ("cls.multiple_answer.php");
require ("cls.free_answer.php");

