<?php
/**
 ==============================================================================

 ==============================================================================
 */
$mtime = explode ( ' ', microtime () );
$starttime = $mtime [1] + $mtime [0];

if (isset ( $_REQUEST ['GLOBALS'] ) or isset ( $_FILES ['GLOBALS'] )) exit ( 'Invalid Request attempted.' );
$already_installed = TRUE;
$includePath = dirname ( __FILE__ );
include_once ($includePath . "/conf/configuration.php");

//服务器类型:测试或者生产
if (function_exists ( 'ini_set' )) {
	ini_set ( "error_log", ROOT_PATH . 'data/logs/' . date ( "Y_W" ) . ".log" );
	ini_set ( 'auto_detect_line_endings', '1' );
	if (DEBUG_MODE) {
		ini_set ( "display_errors", "on" );
		if (version_compare ( PHP_VERSION, '5.3', '<' )) {
			error_reporting ( E_ALL & ~ E_NOTICE & ~ E_WARNING );
		} else {
			error_reporting ( E_ALL & ~ E_NOTICE & ~ E_WARNING & ~ E_DEPRECATED );
		}
	} else {
		ini_set ( 'display_errors', 'off' );
		error_reporting ( E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR );
	}
}

require_once ($includePath . '/lib/main_api.lib.php');
ini_set ( 'include_path', api_create_include_path_setting () );

if ($_configuration ['loadavg'] && substr ( PHP_OS, 0, 3 ) != 'WIN') {
	if (pwLoadAvg ( $_configuration ['loadavg'] )) {
		header ( "HTTP/1.0 503 Service Unavailable" );
		include_once ($includePath . '/serverbusy.htm');
		exit ();
	}
}

getrobot ();
if (defined ( 'NOROBOT' ) && IS_ROBOT) {
	exit ( header ( "HTTP/1.1 403 Forbidden" ) );
}

$_php_self = isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'];
define ( 'PHP_SELF', $_php_self );
$PHP_SELF = dhtmlspecialchars ( $_SERVER ['PHP_SELF'] ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] );

api_session_start ();

require_once ('Log.php');
$log_conf = array ('mode' => 0600, 'timeFormat' => '[%Y-%m-%d %H:%M:%S]', 'lineFormat' => '%{priority} %{timestamp} %{file} %{line} %{ident} %{message}' );
$log = &Log::singleton ( 'file', SERVER_ERR_LOG_DIR . "zlms.log", '-', $log_conf );
if (DEBUG_MODE) {
	$mask = Log::MASK ( PEAR_LOG_WARNING ) | Log::MASK ( PEAR_LOG_ERR ) | Log::MASK ( PEAR_LOG_INFO ) | Log::MASK ( PEAR_LOG_DEBUG ) | Log::MASK ( PEAR_LOG_NOTICE );
} else {
	$mask = Log::MASK ( PEAR_LOG_WARNING ) | Log::MASK ( PEAR_LOG_ERR );
}
$log->setMask ( $mask );

require_once ('Cache/Lite.php');
$_cache_options = array ('cacheDir' => $_configuration ['cache_dir'], 'caching' => $_configuration ['cache_open'], 'lifeTime' => 1800, 'pearErrorMode' => CACHE_LITE_ERROR_RETURN, 'automaticSerialization' => TRUE );
$_objCache = new Cache_Lite ( $_cache_options );

require_once ($includePath . 'lib/database.lib.php');
require_once ($includePath . 'lib/db.class.php');
require_once ($includePath . 'lib/display.lib.php');
require_once ($includePath . 'lib/text.lib.php');
require_once ($includePath . 'lib/security.lib.php');
require_once ($includePath . 'lib/events.lib.inc.php');
require_once ($includePath . 'lib/html_form.inc.php');
include_once ($includePath . 'lib/formvalidator/FormValidator.class.php');
require_once ($includePath . 'lib/sortabletable.class.php');

//连接到主数据库
$_database_connection = $_db = $db = NULL;
db_reconnect ();

$charset_initial_value = $charset = SYSTEM_CHARSET;
api_initialize_string_library ();
api_set_string_library_default_encoding ( $charset );

/*
 --------------------------------------------
 系统设置表settings_current中得到全局设置信息
 --------------------------------------------
 */
$_setting = cache ( CACHE_KEY_PLATFORM_SETTINGS, '' );
if ($_setting == NULL) {
	$_setting = get_platform_settings ();
	cache ( CACHE_KEY_PLATFORM_SETTINGS, $_setting );
}

require_once ($includePath . "/lib/formvalidator/Rule/allowed_tags.inc.php");
require_once (SYS_ROOT . "lib/htmlpurifier/library/HTMLPurifier.auto.php");

$ext_auth_file = api_get_path ( SYS_EXTENSIONS_PATH ) . "auth/" . $external_auth_name . "/local.inc.php";
if (file_exists ( $ext_auth_file )) {
	require_once ($ext_auth_file);
} else {
	require_once ($includePath . "/local.inc.php");
}

//记录登录用户
include_once ($includePath . "/lib/online.inc.php");
LoginCheck ( $_user ['user_id'] );

if (! MAGIC_QUOTES_GPC) { //没有打开 magic_quotes_gpc 时
	if (! empty ( $_GET )) {
		$_GET = addslashes_deep ( $_GET );
	}
	if (! empty ( $_POST )) {
		$_POST = addslashes_deep ( $_POST );
	}
	
	$_COOKIE = addslashes_deep ( $_COOKIE );
	$_REQUEST = addslashes_deep ( $_REQUEST );
}

/*
 -----------------------------------------------------------
 装载语言文件
 -----------------------------------------------------------
 */
$_SESSION ["user_language_choice"] = $language_interface = 'simpl_chinese';
//$language_interface=get_platform_language(); //liyu:20090620 提高性能,暂时不用
$language_interface_initial_value = $language_interface;

//公共语言文件
$language_files = array ('product_info', 'trad4all', 'logging' );
foreach ( $language_files as $lang_file ) {
	include_once (api_get_path ( SYS_PATH ) . 'lang/' . $language_interface . '/' . $lang_file . '.inc.php');
}
unset ( $language_files );

//模块语言文件
if (isset ( $language_file )) {
	$language_files = (is_array ( $language_file ) ? $language_file : array ($language_file ));
	foreach ( $language_files as $index => $language_file ) {
		include_once (api_get_path ( SYS_PATH ) . 'lang/' . $language_interface . '/' . $language_file . '.inc.php');
	}
}
unset ( $language_files, $lang_file, $index, $language_file );

event_update_logout_date ( api_get_user_id () );


function create_table($table_name,$sql_insert,$db_name){
    if(!is_not_blank($db_name)){
       $db_name = DB_NAME;
    }
    if(is_not_blank($table_name) && is_not_blank($sql_insert)){
        if(mysql_num_rows(mysql_query("SHOW TABLES LIKE `".$db_name."`.`".$table_name."`"))!=1){
            $sql_result=  api_sql_query ( $sql_insert,__FILE__, __LINE__ );
            if($sql_result){
                return true;
            }else{
                return false;
            }
        }
    }
}
include_once (URL_ROOT."/www".URL_APPEDND."/main/inc/create_table.php");