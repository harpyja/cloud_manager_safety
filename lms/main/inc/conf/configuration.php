<?php
/*多个数据库的配置文件*/
if (! defined ( 'ROOT_PATH' )) define ( 'ROOT_PATH', substr ( str_replace ( DIRECTORY_SEPARATOR, '/', dirname ( __FILE__ ) ), 0, - 13 ) );
include_once (ROOT_PATH . 'config.php');
date_default_timezone_set ( 'Asia/Shanghai' ); //PRC


define ( 'LICENSE_USER_COUNT', 255 ); //不限用户数
define ( 'EXPIRATION_DATE', mktime ( 0, 0, 0, 3, 31, 2015 ) );

define ( 'WEB_QH_PATH', URL_APPEND . PORTAL_LAYOUT );

//============================================================================
//	 MySQL 连接配置
//============================================================================
$_configuration ['db_host'] = DB_HOST . ':' . DB_PORT; // MySQL服务器地址
$_configuration ['db_user'] = DB_USER; // MySQL服务器连接用户名
$_configuration ['db_password'] = DB_PWD; // MySQL服务器密码


//============================================================================
//   数据库相关配置
//============================================================================
$_configuration ['table_prefix'] = 'crs_'; // 数据库表前缀(不要修改该选项)
$_configuration ['db_glue'] = '`.`'; // 数据库名及表名之间的分隔符号(不要修改该选项)
$_configuration ['db_prefix'] = 'lms_'; // 新建的数据库名前缀
$_configuration ['main_database'] = DB_NAME; //主数据库zllms_main_db
$_configuration ['statistics_database'] = DB_NAME; //// 统计数据库zllms_stats_db
$_configuration ['user_personal_database'] = DB_NAME; //// 用户个人数据库,存储用户的个人信息,如个人日程项目及课程排序等zllms_user_db
$_configuration ['db_dsn'] = 'mysql:host=' . DB_HOST . ';dbname=' . $_configuration ['main_database'];

$_configuration ['root_web'] = WEB_ROOT;
$_configuration ['root_sys'] = SYS_ROOT;
$_configuration ['url_append'] = URL_APPEND;
$_configuration ['code_append'] = 'main/';
$_configuration ['admin_append'] = 'main/admin/';
$_configuration ['extensions'] = 'extensions/';
$_configuration ['course_folder'] = 'storage/courses/';
$_configuration ['attachment_folder'] = 'storage/attachment/';
$_configuration ['ftp_root_folder'] = 'storage/ftp/';
$_configuration ['rootAdminAppend'] = 'admin/';
$_configuration ['archive_dir_name'] = 'storage/archive/'; // 打包文件目录


$_configuration ['gzipcompress'] = FALSE;
$_configuration ['security_key'] = 'b23fb864d880ed87f481aa83b7a45b66'; //密钥
$_configuration ['crypted_method'] = 'none'; //'rijndael_256';// 密码加密方法
$_configuration ['store_session_in_db'] = false;
$_configuration ['session_lifetime'] = 0;
$_configuration ['session_name'] = 'zlms-sid';

$_configuration ['cache_open'] = TRUE;
$_configuration ['open_sys_logging'] = DEBUG_MODE;
$_configuration ['cache_dir'] = SERVER_CACHE_DIR;

$_configuration ['loadavg'] = FALSE;
$_configuration ['unicodecleanfilename'] = TRUE; //用unicode去除特殊字符
$_configuration ['pathtodu'] = BIN_PATH . 'du.exe'; //计算文件夹大小
$_configuration ['dataroot'] = SERVER_DATA_DIR;
$_configuration ['directorypermissions'] = '00777';
define ( 'ZIP_FILE_MAX_EXTRACT_SIZE', 50 * 1048576 );
define ( 'SCORM_PATH', 'scorm/' );
//============================================================================
//  业务逻辑相关配置
//============================================================================
if (EXTERNAL_AUTH) { //第三方认证登录模块
	$external_auth_name = EXTERNAL_AUTH;
	$extAuthSource [EXTERNAL_AUTH] ['login'] = SYS_ROOT . $_configuration ['extensions'] . 'auth/' . EXTERNAL_AUTH . '/login.php';
	$extAuthSource [EXTERNAL_AUTH] ['newUser'] = SYS_ROOT . $_configuration ['extensions'] . 'auth/' . EXTERNAL_AUTH . '/newUser.php';
}

$_configuration ['default_administrator_name'] = array ('root' );
$_configuration ['tracking_enabled'] = TRUE; // 是否使用跟踪信息?
$_configuration ['verbose_backup'] = false;
$_configuration ['remove_user_also_delete_track'] = TRUE;
$_configuration ['fetch_user_settings_from_session'] = FALSE; //取登录用户配置信息的方式: TRUE从SESSION中取值, false:从DB中取值
$_configuration ['enable_question_fillblanks'] = 0;
$_configuration ['enable_question_freeanswer'] = 1;
$_configuration ['enable_module_survey'] = 1;
$_configuration ['enable_scorm_multi_scoes'] = 1;
$_configuration ['enable_question_combatquestion'] = 10;//2013-07-12
$_configuration ['enable_user_ext_info'] = 1;
$_configuration ['enable_display_courseware_track_info'] = 0;
$_configuration ['enable_ukey_login'] = 0;
$_configuration ['exam_auto_save_time'] = 3;

//liyu:
if (! file_exists ( SERVER_ERR_LOG_DIR )) mkdir ( SERVER_ERR_LOG_DIR, 0777 );
ini_set ( 'error_log', SERVER_ERR_LOG_DIR . DIRECTORY_SEPARATOR . date ( 'Y_W' ) . '.log' );
ini_set ( 'auto_detect_line_endings', '1' );

$includePath = ROOT_PATH . $_configuration ['code_append'] . 'inc/';