<?php
include_once ('../inc/global.inc.php');
require_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php'); //check_name_exists()


function api_scorm_log($msg, $file = '', $line = 0, $save_log_file = 'scorm.log') {
	return api_error_log ( $msg, $file, $line, $save_log_file );
}

if (defined ( "SYS_SCORM_PATH" ) == false) define ( 'SYS_SCORM_PATH', str_replace ( '\\', '/', dirname ( __FILE__ ) ) ); //D:/ZLMS/htdocs/webcs/main/scorm 
//api_block_anonymous_users();
//api_protect_course_script();
//api_check_course_expired(api_get_course_code());


if (defined ( "SCORM_DEBUG" ) == false) define ( 'SCORM_DEBUG', 0 );
$this_section = SECTION_COURSES;