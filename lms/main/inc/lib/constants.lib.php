<?php
define ( 'MAGIC_QUOTES_GPC', get_magic_quotes_gpc () );

define ( 'ANONYMOUS', 0 );
define ( 'COURSEMANAGER', 1 ); //教师
define ( 'STUDENT', 5 ); //学生
define ( "PLATFORM_ADMIN", 10 ); //超级管理员


//角色定义: 超级管理员，培训管理员，财务管理员，考评管理员，教师，学生
define ( 'ROLE_SUPER_ADMIN', 'ROLE_SUPER_ADMIN' ); //超级管理员
//define('ROLE_ADMIN','ROLE_ADMIN');						//一般管理员
//define('ROLE_MANAGER','ROLE_MANAGER');					//总经理
define ( 'ROLE_FINANCAL_ADMIN', 'ROLE_FINANCAL_ADMIN' ); //财务主管
define ( 'ROLE_TRAINING_ADMIN', 'ROLE_TRAINING_ADMIN' ); //部门主管/培训管理员
//define('ROLE_HR_ADMIN','ROLE_HR_ADMIN');					//人事主管
define ( 'ROLE_EXAM_ADMIN', 'ROLE_EXAM_ADMIN' );
define ( 'ROLE_TEACHER', 'ROLE_TEACHER' ); //讲师,教师
define ( 'ROLE_USER', 'ROLE_USER' ); //普通用户
define ( 'ROLE_GUEST', 'ROLE_GUEST' );

//COURSE VISIBILITY CONSTANTS
define ( 'COURSE_VISIBILITY_CLOSED', 0 );
define ( 'COURSE_VISIBILITY_REGISTERED', 1 );
define ( 'COURSE_VISIBILITY_OPEN_PLATFORM', 2 );
define ( 'COURSE_VISIBILITY_OPEN_WORLD', 3 );

define ( 'SUBSCRIBE_ALLOWED', 1 );
define ( 'SUBSCRIBE_NOT_ALLOWED', 0 );
define ( 'UNSUBSCRIBE_ALLOWED', 1 );
define ( 'UNSUBSCRIBE_NOT_ALLOWED', 0 );

//CONSTANTS FOR api_get_path FUNCTION
define ( 'WEB_PATH', 'WEB_PATH' );
define ( 'SYS_PATH', 'SYS_PATH' );
define ( 'REL_PATH', 'REL_PATH' );
define ( 'WEB_COURSE_PATH', 'WEB_COURSE_PATH' );
define ( 'SYS_COURSE_PATH', 'SYS_COURSE_PATH' );
define ( 'REL_COURSE_PATH', 'REL_COURSE_PATH' );
define ( 'REL_CODE_PATH', 'REL_CODE_PATH' ); //liyu
define ( 'WEB_CODE_PATH', 'WEB_CODE_PATH' );
define ( 'WEB_ADMIN_PATH', 'WEB_ADMIN_PATH' );
define ( 'SYS_CODE_PATH', 'SYS_CODE_PATH' );
define ( 'SYS_LANG_PATH', 'SYS_LANG_PATH' );
define ( 'WEB_IMG_PATH', 'WEB_IMG_PATH' );
define ( 'SYS_IMG_PATH', 'SYS_IMG_PATH' ); //liyu
define ( 'WEB_COMM_IMG_PATH', 'WEB_COMM_IMG_PATH' );
define ( 'WEB_CSS_PATH', 'WEB_CSS_PATH' );
define ( 'WEB_JS_PATH', 'WEB_JS_PATH' ); //liyu
define ( 'SYS_CSS_PATH', 'SYS_CSS_PATH' );
define ( 'GARBAGE_PATH', 'GARBAGE_PATH' );
define ( 'PLUGIN_PATH', 'PLUGIN_PATH' );
define ( 'SYS_EXTENSIONS_PATH', 'SYS_EXTENSIONS_PATH' );
define ( 'WEB_EXTENSIONS_PATH', 'WEB_EXTENSIONS_PATH' );
define ( 'SYS_ARCHIVE_PATH', 'SYS_ARCHIVE_PATH' );
define ( 'SYS_ATTACHMENT_PATH', 'SYS_ATTACHMENT_PATH' ); //liyu
define ( 'SYS_FTP_ROOT_PATH', 'SYS_FTP_ROOT_PATH' ); //liyu
define ( 'INCLUDE_PATH', 'INCLUDE_PATH' );
define ( 'LIBRARY_PATH', 'LIBRARY_PATH' );
define ( 'LIB_PATH', 'LIB_PATH' );
define ( 'CONFIGURATION_PATH', 'CONFIGURATION_PATH' );
define ( 'SYS_DATA_PATH', 'SYS_DATA_PATH' );
define ( 'WEB_SCORM_PATH', 'WEB_SCORM_PATH' );
define ( 'SYS_SCORM_PATH', 'SYS_SCORM_PATH' );
define ( 'WEB_PORTAL_PATH', 'WEB_PORTAL_PATH' );

define ( 'TOOL_DOCUMENT', 'document' );
define ( 'TOOL_LINK', 'link' );
define ( 'TOOL_COURSE_DESCRIPTION', 'course_description' );
define ( 'TOOL_LEARNPATH', 'learnpath' );
define ( 'TOOL_ANNOUNCEMENT', 'announcement' );
define ( 'TOOL_FORUM', 'forum' );
define ( 'TOOL_THREAD', 'thread' );
define ( 'TOOL_POST', 'post' );
define ( 'TOOL_QUIZ', 'quiz' );
define ( 'TOOL_USER', 'user' );
define ( 'TOOL_GROUP', 'group' );
define ( 'TOOL_CLASS', 'class_of_course' ); //liyu: 课程班级
define ( 'TOOL_ZLMEET', 'zlmeet' ); //liyu
define ( 'TOOL_SMS', 'sms' ); //liyu
define ( 'TOOL_CONFERENCE', 'conference' );
define ( 'TOOL_ASSIGNMENT', 'assignment' ); //liyu
define ( 'TOOL_TRACKING', 'tracking' );
define ( 'TOOL_SURVEY', 'survey' );
define ( 'TOOL_WIKI', 'wiki' );
define ( 'TOOL_FORUM_ATTACH', 'forum_attachment' ); //liyu
define ( 'TOOL_COURSEWARE_PACKAGE', 'html_courseware_package' ); //liyu
define ( 'TOOL_COURSEWARE_MEDIA', 'html_courseware_media' ); //liyu


// CONSTANTS defining ZLMS sections
define ( 'SECTION_CAMPUS', 'mycampus' );
define ( 'SECTION_COURSES', 'mycourses' );
define ( 'SECTION_MYPROFILE', 'myprofile' );
define ( 'SECTION_MYAGENDA', 'myagenda' );
define ( 'SECTION_COURSE_ADMIN', 'course_admin' );
define ( 'SECTION_PLATFORM_ADMIN', 'platform_admin' );
define ( 'SECTION_PROGRESS', 'progress' );
define ( 'SECTION_TRADE', 'trade' );
define ( 'SECTION_EXAM', 'exam' );

// CONSTANT name for local authentication source
define ( 'PLATFORM_AUTH_SOURCE', 'platform' ); //platform,ldap
define ( 'NUMBER_PAGE', 10 );
define ( 'CHMOD_NORMAL', 0766 );
define ( "SUCCESS", 1 );
define ( "FAILURE", 0 );
define ( 'SYSTEM_CHARSET', 'UTF-8' );
$charset = SYSTEM_CHARSET;
define ( 'IS_POST', (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST') );
define ( 'SECURITY_SALT', '' );

if (! defined ( "LOG_DEBUG" )) define ( 'LOG_DEBUG', 'LOG_DEBUG' );
if (! defined ( "LOG_INFO" )) define ( 'LOG_INFO', 'LOG_INFO' );
if (! defined ( "LOG_WARN" )) define ( 'LOG_WARN', 'LOG_WARN' );
if (! defined ( "LOG_ERROR" )) define ( 'LOG_ERROR', 'LOG_ERROR' );

if (! defined ( "DIRECTORY_SEPARATOR" )) define ( "DIRECTORY_SEPARATOR", "/" );
define ( 'TEST_COOKIE', 'webcs_test_cookie' );
define ( 'IS_WINDOWS_OS', api_is_windows_os () );

// Checks for installed optional php-extensions.
define ( 'INTL_INSTALLED', function_exists ( 'intl_get_error_code' ) ); // intl extension (from PECL), it is installed by default as of PHP 5.3.0
define ( 'ICONV_INSTALLED', function_exists ( 'iconv' ) ); // iconv extension, for PHP5 on Windows it is installed by default.
define ( 'MBSTRING_INSTALLED', function_exists ( 'mb_strlen' ) ); // mbstring extension.


//PPT使用固定的工具来转换
//define("OOGIE_FORCE_CONVERT",TRUE);
//define ( 'PPT_CONVERT_METHOD_MSOFFICE', 'msoffice' );
//define ( 'PPT_CONVERT_METHOD_OPENOFFICE', 'openoffice' );
//define('OOGIE_CURRENT_USE_METHOD',PPT_CONVERT_METHOD_MSOFFICE);


//Cache相关
define ( 'CACHE_KEY_PLATFORM_SETTINGS', 'platform_settings' );
define ( 'CACHE_KEY_PLATFORM_PLUGINS', 'platform_plugins' );
define ( 'CACHE_KEY_ADMIN_DEPT', 'administration_dept' );
define ( 'CACHE_KEY_COURSE_CATEGORIES', 'course_categories' );

//审核相关的全局变量
define ( 'AUDIT_REGISTER_INIT', 0 );
define ( 'AUDIT_REGISTER_PASS', 2 );
define ( 'AUDIT_REGISTER_REFUSE', 1 );

//审核开课申请
define ( 'AUDIT_CRS_CREATION_APPLY_INIT', 0 );
define ( 'AUDIT_CRS_CREATION_APPLY_PASS', 1 );
define ( 'AUDIT_CRS_CREATION_APPLY_REFUSE', 2 );

define ( "STATE_DISABLED", 0 );
define ( "STATE_EDIT", 1 );
define ( "STATE_PUBLISHED", 2 );

//审核学生注册课程申请
define ( 'AUDIT_CRS_SUBSCRITION_INIT', 0 );
define ( 'AUDIT_CRS_SUBSCRITION_PASS', 1 );
define ( 'AUDIT_CRS_SUBSCRITION_REFUSE', 2 );

//学习状态
define ( "LEARNING_STATE_NOTATTEMPT", 0 ); //未开始
define ( "LEARNING_STATE_IMCOMPLETED", 2 ); //学习中,未完成
define ( "LEARNING_STATE_COMPLETED", 3 ); //完成
define ( "LEARNING_STATE_PASSED", 1 ); //已通过
define ( "LEARNING_STATE_FAILED", 4 ); //未通过


define ( 'LESSON_STATUS_NOTATTEMPT', 'not_attempted' );
define ( 'LESSON_STATUS_INCOMPLETE', 'incomplete' );
define ( 'LESSON_STATUS_COMPLETED', 'completed' );
define ( 'LESSON_STATUS_PASSED', 'passed' );
define ( 'LESSON_STATUS_FAILED', 'failed' );

define ( "DEFAULT_LEARNING_DAYS", 365 ); //默认选修学习天数
define ( "DEPT_TOP_ID", 1 );

//====================================================================
//define ( 'TABLE_MAIN_MENU', 'sys_menu' ); //liyu
define ( 'TABLE_MAIN_DEPT', 'sys_dept' ); //liyu
define ( 'TABLE_MAIN_COURSE', 'course' );
define ( 'TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION', 'course_subscribe_requisition' ); //liyu
define ( 'TABLE_MAIN_USER', 'user' );
define ('TABLE_MAIN_NET','networkmap');//dengXin
define ( 'TABLE_MAIN_USER_REGISTER', 'user_register' ); //liyu
define ( 'TABLE_MAIN_SYS_SMS', 'sys_sms' ); //liyu
define ( 'TABLE_MAIN_SYS_SMS_RECEIVED', 'sys_sms_received' ); //liyu
define ( 'TABLE_MAIN_SYS_ATTACHMENT', 'sys_attachment' ); //liyu
define ( 'TABLE_MAIN_SYS_LOGGING', 'sys_logging' ); //liyu
define ( 'TABLE_MAIN_COURSE_USER', 'course_rel_user' );
define ( 'TABLE_MAIN_CATEGORY', 'course_category' );
define ( 'TABLE_MAIN_SYSTEM_CMS', 'sys_cms' );
define ( 'TABLE_MAIN_LANGUAGE', 'language' );
define ( 'TABLE_MAIN_SETTINGS_OPTIONS', 'settings_options' );
define ( 'TABLE_MAIN_SETTINGS_CURRENT', 'settings_current' );
define ( 'TABLE_CATEGORY', 'category' ); //liyu
define ( 'TABLE_MAIN_SYS_POSITION', "sys_position" ); //liyu
//define ( "TABLE_MAIN_COURSE_PACKAGE", "course_package" ); //liyu
//define ( "TABLE_MAIN_COURSE_REL_PACKAGE", "course_rel_package" ); //liyu
define ( 'TABLE_MAIN_COURSE_OPENSCOPE', 'course_openscope' );

//define ( 'TABLE_MAIN_CLASS', 'class' );
//define ( 'TABLE_MAIN_COURSE_CLASS', 'course_rel_class' );
//define ( 'TABLE_MAIN_CLASS_USER', 'class_user' );


define ( 'TABLE_SURVEY', 'survey' );
define ( 'TABLE_SURVEY_USER', 'survey_user' );
define ( 'TABLE_SURVEY_ANSWER', 'survey_answer' );
define ( 'TABLE_SURVEY_QUESTIOIN', 'survey_question' );
define ( 'TABLE_SURVEY_QUESTIOIN_GROUP', 'survey_question_group' );
define ( 'TABLE_SURVEY_QUESTIOIN_OPTION', 'survey_question_option' );

define ( 'TABLE_SURVEY', 'survey' );
define ( 'TABLE_SURVEY_QUESTION', 'survey_question' );
define ( 'TABLE_SURVEY_QUESTION_OPTION', 'survey_question_option' );
define ( 'TABLE_SURVEY_INVITATION', 'survey_invitation' );
define ( 'TABLE_SURVEY_ANSWER', 'survey_answer' );

//视图
define ( 'VIEW_USER', 'sys_user' );
define ( 'VIEW_USER_DEPT', 'sys_user_dept' );
define ( 'VIEW_COURSE_USER', 'view_course_user' );

//系统授权
define ( 'TABLE_MAIN_USER_ROLE', 'sys_user_role' );
//define ( 'TABLE_MAIN_PERMISSION', 'sys_permission' );
//define ( 'TABLE_MAIN_ROLE_PERMISSION', 'sys_role_permission' );
define ( 'VIEW_USER_ROLE', 'sys_user_rel_role' );

//统计分析用表
define ( 'TABLE_STATISTIC_TRACK_E_DOWNLOADS', 'track_e_downloads' );
define ( 'TABLE_STATISTIC_TRACK_E_LINKS', 'track_e_links' );
define ( 'TABLE_STATISTIC_TRACK_E_LOGIN', 'track_e_login' );
define ( 'TABLE_STATISTIC_TRACK_E_ONLINE', 'track_e_online' );
define ( 'TABLE_STATISTIC_TRACK_E_CW', 'track_e_cw' ); //liyu


//课程用表
define ( 'TABLE_ANNOUNCEMENT', 'announcement' );
define ( 'TABLE_DOCUMENT', 'document' );
define ( 'TABLE_COURSEWARE', 'courseware' ); //liyu
define ( 'TABLE_ITEM_PROPERTY', 'item_property' );
define ( 'TABLE_TOOL_COURSE_CLASS', 'course_class' ); //liyu: 课程班级
define ( 'TABLE_COURSE_SETTING', 'course_setting' );

//Assignment
define ( 'TABLE_TOOL_ASSIGNMENT_MAIN', 'assignment_main' ); //liyu
define ( 'TABLE_TOOL_ASSIGNMENT_SUBMISSION', 'assignment_submission' ); //liyu
define ( 'TABLE_TOOL_ASSIGNMENT_FEEDBACK', 'assignment_feedback' ); //liyu
define ( 'TABLE_TOOL_ATTACHMENT', 'attachment' ); //liyu


//视频课堂
define ( 'TABLE_ZLMEET_UPLOAD_FILE', 'zlmeet_upload_file' );
define ( 'TABLE_ZLMEET_UPLOAD_PPT_FILE', 'zlmeet_upload_ppt_file' );
define ( 'TABLE_TOOL_ATTACHMENT', 'attachment' ); //liyu


//SMS
define ( 'TABLE_TOOL_SMS', 'sms' ); //liyu
define ( 'TABLE_TOOL_SMS_RECEIVED', 'sms_received' ); //liyu
define ( 'VIEW_TOOL_SMS_RECEIVERS', 'view_sms_receivers' ); //liyu
define ( 'VIEW_TOOL_SMS_RECEIVERS_LIST', 'view_sms_receivers_list' ); //liyu


//define ( 'TABLE_QUIZ_QUESTION', 'quiz_question' ); //quiz_question
//define ( 'TABLE_QUIZ_ANSWER', 'quiz_answer' ); //quiz_answer
define ( 'TABLE_QUIZ_TEST', 'exam_main' );
define ( 'TABLE_QUIZ_TEST_QUESTION', 'exam_rel_question' );
define ( 'TABLE_MAIN_EXAM_QUESTION', 'exam_question' ); //liyu: 问题
define ( 'TABLE_MAIN_EXAM_ANSWER', 'exam_answer' ); //liyu: 答案
define ( 'TABLE_MAIN_EXAM_QUESTION_POOL', 'exam_question_pool' ); //liyu: 题库
define ( 'TABLE_MAIN_EXAM_REL_USER', 'exam_rel_user' ); //liyu: 可考试用户
define ( 'TABLE_STATISTIC_TRACK_E_EXERCICES', 'exam_track' );
define ( 'TABLE_STATISTIC_TRACK_E_ATTEMPT', 'exam_attempt' );

define ( 'TABLE_LP_MAIN', 'lp' );
define ( 'TABLE_LP_ITEM', 'lp_item' );
define ( 'TABLE_LP_VIEW', 'lp_view' );
define ( 'TABLE_LP_ITEM_VIEW', 'lp_item_view' );
define ( "TABLE_SCORM_SCOES_TRACK", "lp_scoes_track" );
