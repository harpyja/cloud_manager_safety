<?php

/*
 ==============================================================================
 FAILURE MANAGEMENT
 ==============================================================================
 */

/*
 * The Failure Management module is here to compensate
 * the absence of an 'exception' device in PHP 4.
 */
/**
 * $api_failureList - array containing all the failure recorded
 * in order of arrival.
 */
$api_failureList = array ();

/**
 * Fills a global array called $api_failureList
 * This array collects all the failure occuring during the script runs
 * The main purpose is allowing to manage the display messages externaly
 * from the functions or objects. This strengthens encupsalation principle
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  string $failureType - the type of failure
 * @global array $api_failureList
 * @return bolean false to stay consistent with the main script
 */
function api_set_failure($failureType) {
	global $api_failureList;
	$api_failureList [] = $failureType;
	return false;
}

/**
 * get the last failure stored in $api_failureList;
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param void
 * @return string - the last failure stored
 */
function api_get_last_failure() {
	global $api_failureList;
	return $api_failureList [count ( $api_failureList ) - 1];
}

/**
 * collects and manage failures occuring during script execution
 * The main purpose is allowing to manage the display messages externaly
 * from functions or objects. This strengthens encupsalation principle
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @package zllms.library
 */
class api_failure {
	/*
	 * IMPLEMENTATION NOTE : For now the $api_failureList list is set to the
	 * global scope, as PHP 4 is unable to manage static variable in class. But
	 * this feature is awaited in PHP 5. The class is already written to minize
	 * the change when static class variable will be possible. And the API won't
	 * change.
	 */
	var $api_failureList = array ();

	/**
	 * Pile the last failure in the failure list
	 *
	 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
	 * @param  string $failureType - the type of failure
	 * @global array  $api_failureList
	 * @return bolean false to stay consistent with the main script
	 */
	function set_failure($failureType) {
		global $api_failureList;
		$api_failureList [] = $failureType;
		return false;
	}

	/**
	 * get the last failure stored
	 *
	 * @author Hugues Peeters <hugues.peeters@claroline.net>
	 * @param void
	 * @return string - the last failure stored
	 */
	function get_last_failure() {
		global $api_failureList;
		return $api_failureList [count ( $api_failureList ) - 1];
	}
}

/*$dsn = "数据库类型名称://入口帐号:入口密码@数据库主机名:端口号/数据库名/是否总是打开新的连接";
 $log_conf = array('dsn' => 'mysql://root@localhost:3306/zllms_main_db/true',
 'sql'=>'INSERT INTO sys_log (id, logtime, ident, priority, message) VALUES(?, CURRENT_TIMESTAMP, ?, ?, ?)');$logger = &Log::singleton('sql', 'log_table', 'ident', $conf);
 $log = &Log::singleton('sql', 'sys_log', '-', $log_conf);

 $log_conf = array('filename' => $_configuration['root_sys'].'logs/log.db', 'mode' => 0666, 'persistent' => true);
 $log =& Log::factory('sqlite', 'sys_log', 'ident', $log_conf);*/

class DBHandler {
	private static $instance = NULL;

	private function __construct() {}

	public static function getInstance() {
		global $_configuration;
		try {
			if (! self::$instance) {
				if ($_configuration && is_array ( $_configuration )) {
					$mysql_dsn = $_configuration ['db_dsn'];
					$dbusername = $_configuration ['db_user'];
					$dbpwd = $_configuration ['db_password'];
					//echo $mysql_dsn,"<br/>".$dbusername;
					self::$instance = new PDO ( $mysql_dsn, $dbusername, $dbpwd, array (PDO::ATTR_PERSISTENT => false ) );
					self::$instance->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				} else {
					die ( 'Configuration is Empty' );
				}
			}
			self::$instance->exec ( "set names utf8;" );
		} catch ( PDOException $ex ) {
			die ( 'PDO Connection Faild' );
		}
		
		return self::$instance;
	}

	private function __clone() {}
}

//$_dbh=DBHandler::getInstance();


/**
 * 读结果缓存文件
 *
 * @params  string  $cache_name
 *
 * @return  array   $data
 */
function read_static_cache($cache_name) {
	if (DEBUG_MODE == 2) {
		return false;
	}
	static $result = array ();
	if (! empty ( $result [$cache_name] )) {
		return $result [$cache_name];
	}
	$cache_file_path = SERVER_CACHE_DIR . 'static_caches/' . $cache_name . '.php';
	if (file_exists ( $cache_file_path )) {
		include_once ($cache_file_path);
		$result [$cache_name] = $data;
		return $result [$cache_name];
	} else {
		return false;
	}
}

/**
 * 写结果缓存文件
 *
 * @params  string  $cache_name
 * @params  string  $caches
 *
 * @return
 */
function write_static_cache($cache_name, $caches) {
	if (DEBUG_MODE == 2) {
		return false;
	}
	$cache_file_path = SERVER_CACHE_DIR . 'static_caches/' . $cache_name . '.php';
	$content = "<?php\r\n";
	$content .= "\$data = " . var_export ( $caches, true ) . ";\r\n";
	$content .= "?>";
	file_put_contents ( $cache_file_path, $content, LOCK_EX );
}

function api_get_course_users($course_code = NULL, $sqlwhere = "") {
	$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
	$t_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$t_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	if (! $course_code) $course_code = api_get_course_code ();
	
	$sql = "SELECT user.*,IF(user.status=1,'" . get_lang ( 'Teacher' ) . "','" . get_lang ( 'Student' ) . "')	 AS user_type, cu.status cu_status,cu.tutor_id,cu.is_course_admin,class_id
					FROM    $view_user_dept AS user, $t_course_user   cu WHERE user.user_id = cu.user_id
					AND   cu.course_code = '" . $course_code . "' ";
	if ($sqlwhere) $sql .= $sqlwhere;
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($result === false) {
		return array ();
	}
	$users = array ();
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$users [$row ['user_id']] = $row;
	}
	return $users;

}

function api_get_mail_type() {
	global $_configuration;
	return $_configuration ['mail_type'];
}

function api_check_cookie_enabled() {
	if (empty ( $_COOKIE [TEST_COOKIE] )) {
		if (! headers_sent ()) {
			include (api_get_path ( INCLUDE_PATH ) . "message_header.inc.php");
		}
		echo '<br/>';
		Display::display_error_message ( get_lang ( "CookieNotAllowedError" ), false );
		Display::display_footer ();
		die ();
	}
}

function api_encrypt($data, $security_key, $cipher = MCRYPT_3DES) {
	$td = mcrypt_module_open ( $cipher, '', MCRYPT_MODE_ECB, '' ); //MCRYPT_TRIPLEDES,MCRYPT_3DES
	if (IS_WINDOWS_OS) {
		$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
	} else {
		$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_DEV_RANDOM );
	}
	$key = substr ( $security_key, 0, mcrypt_enc_get_key_size ( $td ) );
	mcrypt_generic_init ( $td, $key, $iv );
	
	$ret = base64_encode ( mcrypt_generic ( $td, $data ) );
	
	mcrypt_generic_deinit ( $td );
	mcrypt_module_close ( $td );
	return trim ( $ret );
}

function api_decrypt($value, $security_key, $cipher = MCRYPT_3DES) {
	$td = mcrypt_module_open ( $cipher, '', MCRYPT_MODE_ECB, '' ); //'tripledes',MCRYPT_3DES
	if (IS_WINDOWS_OS) {
		$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
	} else {
		$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_DEV_RANDOM );
	}
	$key = substr ( $security_key, 0, mcrypt_enc_get_key_size ( $td ) );
	mcrypt_generic_init ( $td, $key, $iv );
	
	$ret = trim ( mdecrypt_generic ( $td, base64_decode ( $value ) ) );
	
	mcrypt_generic_deinit ( $td );
	mcrypt_module_close ( $td );
	return trim ( $ret );
}

function api_deny_access($display_goback = true) {
	if (! headers_sent ()) Display::display_header ( NULL, FALSE );
	
	if (! isset ( $_user ['user_id'] ) && empty ( $_SESSION ['_user'] ['user_id'] )) {
		Display::display_error_message ( '<p>' . get_lang ( 'AccessDeny' ) . '&nbsp;' . get_lang ( 'AccessDenyReson2' ) . '<a href="' . $home_url . '" target="_top">' . get_lang ( "LoginAgain" ) . '</a><br/>', false );
	} else { //没有权限
		$html = '<p>' . get_lang ( 'AccessDeny' ) . '&nbsp;' . get_lang ( 'AccessDenyReson1' ) . "</p>";
		if ($display_goback) $html .= '<a href="' . ($use_referer_back ? $_SERVER ['HTTP_REFERER'] : 'javascript:history.back();') . '">' . get_lang ( "ClickHere" ) . get_lang ( "ReturnTo" ) . '</a><br/>';
		Display::display_error_message ( $html, false );
	}
	//echo '</div>';
	Display::display_footer ();
	die ();
}

/**
 * This style is only for ie 6 or lower, the css is defined in the default.css
 * @author zhong
 * @param
 * @return string style css
 */

function get_table_style_ie6() {
	$ret = <<<EOT
	
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 6]>
<style type="text/css" media="projection, screen">
.row_odd {
	behavior: expression(
		this.onmouseover = new Function("this.className += '_hover';"),
		this.onmouseout = new Function("this.className = this.className.replace('_hover', '');"),
		this.style.behavior = null
	);
}
.row_even {
	behavior: expression(
		this.onmouseover = new Function("this.className += '_hover';"),
		this.onmouseout = new Function("this.className = this.className.replace('_hover', '');"),
		this.style.behavior = null
	);
}
</style>
<![endif]-->

EOT;
	
	return $ret;
}

/**
 * 显示底部分页的导航栏
 *
 * @param unknown_type $start
 * @param unknown_type $total
 * @param unknown_type $url_param
 * @return unknown
 */
function display_nav_page_bar($total, $start = 0, $url_param = '') {
	
	$next = ($start + 1);
	$prev = ($start - 1);
	
	if (($total % NUMBER_PAGE) == 0) {
		$pages = intval ( $total / NUMBER_PAGE );
	} else {
		$pages = intval ( $total / NUMBER_PAGE ) + 1;
	}
	
	$html = "<div style='float:left;margin-top:5px;'>";
	$html .= ($start + 1) . " / " . $pages;
	$html .= " (" . get_lang ( 'Total' ) . ": " . $total . ") ";
	$html .= "</div>";
	$html .= "<div style='float:right;margin-top:5px;'>";
	if ($start == 0) {
		$html .= Display::return_icon ( 'prev.gif', get_lang ( 'PreviousPage' ) ) . "&nbsp;&nbsp;&nbsp;";
		if ($total > NUMBER_PAGE) {
			$html .= "<a href='{$_SERVER['PHP_SELF']}?start={$next}" . $url_param . "'>" . Display::return_icon ( 'next.gif', get_lang ( 'NextPage' ) ) . "</a>\n";
		} else {
			$html .= Display::return_icon ( 'next.gif', get_lang ( 'NextPage' ) );
		}
	} else {
		$html .= "<a href='{$_SERVER['PHP_SELF']}?start={$prev}" . $url_param . "'>" . Display::return_icon ( 'prev.gif', get_lang ( 'PreviousPage' ) ) . "</a>&nbsp;&nbsp;&nbsp;\n";
		
		if ($total - ($start * NUMBER_PAGE) > NUMBER_PAGE) {
			$html .= "<a href='{$_SERVER['PHP_SELF']}?start={$next}'>" . Display::return_icon ( 'next.gif', get_lang ( 'NextPage' ) ) . "</a>\n";
		} else {
			$html .= Display::return_icon ( 'next.gif', get_lang ( 'NextPage' ) );
		}
	}
	$html .= "</div>";
	
	return $html;
}

function get_encodings($inc_sys_charset = false) {
	$lists = array ('UTF-8', 'GBK', 'BIG5', 'ISO-8859-15', 'EUC-JP' );
	$encodings = array ();
	foreach ( $lists as $value ) {
		if ($inc_sys_charset) {
			$encodings [$value] = $value;
		} else {
			if (strtolower ( $value ) != strtolower ( SYSTEM_CHARSET )) {
				$encodings [$value] = $value;
			}
		}
	}
	
	return $encodings;
}

function get_default_encoding() {
	switch (api_get_setting ( 'platformLanguage' )) {
		case 'simpl_chinese' :
			$encoding = 'GBK';
			break;
		case 'trad_chinese' :
			$encoding = 'BIG5';
			break;
		case 'japanese' :
			$encoding = 'EUC-JP';
			break;
		default :
			$encoding = 'ISO-8859-15';
	}
	return $encoding;
}

function api_check_course_expired($course_code) {
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	global $_course;
	if ((! $_course ['is_started'] or $_course ['is_expired']) and ! api_is_allowed_to_edit ()) {
		Display::display_message_header ();
		if (! $_course ['is_started']) {
			Display::display_warning_message ( get_lang ( 'CourseNotStart' ) . ", " . get_lang ( 'ReturnMain' ), false );
		}
		if ($_course ['is_expired']) {
			Display::display_warning_message ( get_lang ( 'CourseExpiration' ) . ", " . get_lang ( 'ReturnMain' ), false );
		}
		Display::display_footer ();
		exit ();
	}
}

function api_logging($message, $module = '', $i18n_key = NULL, $user_id = NULL, $username = NULL, $display_name = NULL) {
	global $_configuration;
	if ($_configuration ['open_sys_logging']) {
		global $_user;
		if (! isset ( $user_id )) $user_id = api_get_user_id ();
		if (! isset ( $username )) $username = api_get_user_name ();
		if (! isset ( $display_name )) $display_name = $_user ['firstName'];
		$table_sys_logging = Database::get_main_table ( TABLE_MAIN_SYS_LOGGING );
		
		$sql_data = array ();
		$sql_data ['user_id'] = $user_id;
		$sql_data ['username'] = $username;
		$sql_data ['display_name'] = $display_name;
		$sql_data ['module_name'] = $module;
		$sql_data ['message'] = $message;
		$sql_data ['i18n_key'] = $i18n_key;
		$url=  explode("?", api_request_uri ());
		$sql_data ['access_uri'] =$url[0] ;
		$sql_data ['log_ip'] = get_onlineip ();
		$sql = Database::sql_insert ( $table_sys_logging, $sql_data );
		
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

function api_log($msg, $mode = LOG_DEBUG) {
	global $log;
	switch ($mode) {
		case LOG_INFO :
			$log->info ( $msg );
			break;
		case LOG_ERROR :
			$log->err ( $msg );
			break;
		case LOG_WARN :
			$log->warning ( $msg );
			break;
		default :
			$log->debug ( $msg );
	}
}

function api_error_log($msg, $file = '', $line = 0, $save_log_file = 'system.log') {
	if (! isset ( $msg ) or empty ( $msg )) return FALSE;
	$log_path = SERVER_DATA_DIR . "logs/";
	//$msg_prefix=date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$file."-".$line."\t";
	$msg_prefix = date ( "[m-d H:i]" ) . " -[" . str_replace ( URL_APPEND, '', $_SERVER ['REQUEST_URI'] ) . "] :" . (empty ( $line ) ? "" : $line) . "\t";
	if (is_string ( $msg )) {
		return error_log ( $msg_prefix . $msg . "\n", 3, $log_path . "/" . $save_log_file );
	}
	if (is_array ( $msg )) {
		ob_start ();
		//var_dump($msg);
		print_r ( $msg );
		return error_log ( $msg_prefix . "\r\n" . ob_get_clean (), 3, $log_path . DIRECTORY_SEPARATOR . $save_log_file );
	}
}

function log_error($msg, $file = '', $line = 0) {
	if (! isset ( $msg )) return FALSE;
	$msg_prefix = date ( "[Y-m-d H:i:s]" ) . " -[" . $_SERVER ['REQUEST_URI'] . "] :" . $file . "-" . $line . "\t";
	if (is_string ( $msg )) {
		return error_log ( $msg_prefix . $msg . "\n", 3, SERVER_ERR_LOG_DIR . "/webcs_error.log" );
	}
	if (is_array ( $msg )) {
		ob_start ();
		var_dump ( $msg );
		return error_log ( $msg_prefix . "\r\n" . ob_get_clean (), 3, SERVER_ERR_LOG_DIR . "/webcs_error.log" );
	}
}

function api_get_last_login_time($user_id) {
	$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
	$sql = "select login_date from " . $tbl_track_login . " WHERE login_user_id='" . escape ( $user_id ) . "' ORDER BY login_id DESC LIMIT 2";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$last_login_date = (Database::num_rows ( $result ) == 1 ? '0000-00-00 00:00:00' : mysql_result ( $result, 1 ));
	return $last_login_date;
}

/**
 * 取文件名的后缀名
 *
 * @param unknown_type $file_name
 * @return unknown
 */
function getFileExt($file_name) {
	/*while($dot = strpos($file_name, "."))
	 {
		$file_name = substr($file_name, $dot+1);
		}
		return $file_name;*/
	
	if (isset ( $file_name ) and ! (substr ( $file_name, 0, 1 ) === ".") and strrpos ( $file_name, "." )) {
		return end ( explode ( ".", $file_name ) );
	}
	return '';
}

function remove_dir($dirName) {
	$result = false;
	if (! is_dir ( $dirName )) {
		//trigger_error("目录名称错误", E_USER_ERROR);
		return false;
	} else {
		$handle = opendir ( $dirName );
		while ( ($file = readdir ( $handle )) !== false ) {
			if ($file != '.' && $file != '..') {
				$dir = $dirName . DIRECTORY_SEPARATOR . $file;
				is_dir ( $dir ) ? remove_dir ( $dir ) : unlink ( $dir );
			}
		}
		closedir ( $handle );
		$result = rmdir ( $dirName ) ? true : false;
		return $result;
	}
}

/**
 * 递归获取某目录下所有文件的大小
 *
 * @param unknown_type $dir
 */
function get_dir_size($dir) {
	$dir_size = 0;
	if ($dh = @opendir ( $dir )) {
		while ( ($filename = readdir ( $dh )) ) {
			if ($filename != "." && $filename != "..") {
				$tmp_filename = $dir . "/" . $filename;
				if (is_file ( $tmp_filename )) $dir_size += filesize ( $tmp_filename );
				if (is_dir ( $tmp_filename )) $dir_size += get_dir_size ( $tmp_filename );
				unset ( $tmp_filename );
			}
		}
	}
	@closedir ( $dh );
	return $dir_size;
}

/**
 * 递归获取某目录下所有文件
 *
 * @param unknown_type $path
 */
function get_all_files($path) {
	$list = array ();
	foreach ( glob ( $path . DIRECTORY_SEPARATOR . '*' ) as $item ) {
		if (is_dir ( $item )) {
			$list = array_merge ( $list, get_all_files ( $item ) );
		} else {
			$list [] = $item;
		}
	}
	return $list;
}

function copy_dir($fromDir, $toDir) {
	if (is_dir ( $fromDir ) && is_dir ( $toDir )) {
		if (api_is_windows_os ()) {
			$fromDir = str_replace ( '/', '\\', $fromDir );
			$toDir = str_replace ( '/', '\\', $toDir );
			$cmd = "xcopy $fromDir $toDir /E /I /C /K /Y";
		} else {
			$cmd = "cp -r -b -f $fromDir $toDir";
		}
		exec ( $cmd, $output, $return_var );
	}
}

/**
 * 检查目标文件夹是否存在，如果不存在则自动创建该目录
 *
 * @access      public
 * @param       string      folder     目录路径。不能使用相对于网站根目录的URL
 *
 * @return      bool
 */
function make_dir($folder) {
	$reval = false;
	
	if (! file_exists ( $folder )) {
		/* 如果目录不存在则尝试创建该目录 */
		@umask ( 0 );
		
		/* 将目录路径拆分成数组 */
		preg_match_all ( '/([^\/]*)\/?/i', $folder, $atmp );
		
		/* 如果第一个字符为/则当作物理路径处理 */
		$base = ($atmp [0] [0] == '/') ? '/' : '';
		
		/* 遍历包含路径信息的数组 */
		foreach ( $atmp [1] as $val ) {
			if ('' != $val) {
				$base .= $val;
				
				if ('..' == $val || '.' == $val) {
					/* 如果目录为.或者..则直接补/继续下一个循环 */
					$base .= '/';
					
					continue;
				}
			} else {
				continue;
			}
			
			$base .= '/';
			
			if (! file_exists ( $base )) {
				/* 尝试创建目录，如果创建失败则继续循环 */
				if (@mkdir ( rtrim ( $base, '/' ), 0777 )) {
					@chmod ( $base, 0777 );
					$reval = true;
				}
			}
		}
	} else {
		/* 路径已经存在。返回该路径是不是一个目录 */
		$reval = is_dir ( $folder );
	}
	
	clearstatcache ();
	
	return $reval;
}

/**
 * 获取文件后缀名,并判断是否合法
 *
 * @param string $file_name
 * @param array $allow_type
 * @return blob
 */
function get_file_suffix($file_name, $allow_type = array()) {
	$file_suffix = strtolower ( array_pop ( explode ( '.', $file_name ) ) );
	if (empty ( $allow_type )) {
		return $file_suffix;
	} else {
		if (in_array ( $file_suffix, $allow_type )) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * 检查文件类型
 *
 * @access      public
 * @param       string      filename            文件名
 * @param       string      realname            真实文件名
 * @param       string      limit_ext_types     允许的文件类型
 * @return      string
 */
function check_file_type($filename, $realname = '', $limit_ext_types = '') {
	if ($realname) {
		$extname = strtolower ( substr ( $realname, strrpos ( $realname, '.' ) + 1 ) );
	} else {
		$extname = strtolower ( substr ( $filename, strrpos ( $filename, '.' ) + 1 ) );
	}
	
	if ($limit_ext_types && stristr ( $limit_ext_types, '|' . $extname . '|' ) === false) {
		return '';
	}
	
	$str = $format = '';
	
	$file = @fopen ( $filename, 'rb' );
	if ($file) {
		$str = @fread ( $file, 0x400 ); // 读取前 1024 个字
		@fclose ( $file );
	} else {
		if (stristr ( $filename, ROOT_PATH ) === false) {
			if (in_array ( $extname, array ('jpg', 'jpeg', 'gif', 'png', 'doc', 'xls', 'txt', 'zip', 'rar', 'ppt', 'pdf', 'rm', 'mid', 'wav', 'bmp', 'swf', 'chm', 'sql', 'cert' ) )) {
				$format = $extname;
			}
		} else {
			return '';
		}
	}
	
	if ($format == '' && strlen ( $str ) >= 2) {
		if (substr ( $str, 0, 4 ) == 'MThd' && $extname != 'txt') {
			$format = 'mid';
		} elseif (substr ( $str, 0, 4 ) == 'RIFF' && $extname == 'wav') {
			$format = 'wav';
		} elseif (substr ( $str, 0, 3 ) == "\xFF\xD8\xFF") {
			$format = 'jpg';
		} elseif (substr ( $str, 0, 4 ) == 'GIF8' && $extname != 'txt') {
			$format = 'gif';
		} elseif (substr ( $str, 0, 8 ) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
			$format = 'png';
		} elseif (substr ( $str, 0, 2 ) == 'BM' && $extname != 'txt') {
			$format = 'bmp';
		} elseif ((substr ( $str, 0, 3 ) == 'CWS' || substr ( $str, 0, 3 ) == 'FWS') && $extname != 'txt') {
			$format = 'swf';
		} elseif (substr ( $str, 0, 4 ) == "\xD0\xCF\x11\xE0") { // D0CF11E == DOCFILE == Microsoft Office Document
			if (substr ( $str, 0x200, 4 ) == "\xEC\xA5\xC1\x00" || $extname == 'doc') {
				$format = 'doc';
			} elseif (substr ( $str, 0x200, 2 ) == "\x09\x08" || $extname == 'xls') {
				$format = 'xls';
			} elseif (substr ( $str, 0x200, 4 ) == "\xFD\xFF\xFF\xFF" || $extname == 'ppt') {
				$format = 'ppt';
			}
		} elseif (substr ( $str, 0, 4 ) == "PK\x03\x04") {
			$format = 'zip';
		} elseif (substr ( $str, 0, 4 ) == 'Rar!' && $extname != 'txt') {
			$format = 'rar';
		} elseif (substr ( $str, 0, 4 ) == "\x25PDF") {
			$format = 'pdf';
		} elseif (substr ( $str, 0, 3 ) == "\x30\x82\x0A") {
			$format = 'cert';
		} elseif (substr ( $str, 0, 4 ) == 'ITSF' && $extname != 'txt') {
			$format = 'chm';
		} elseif (substr ( $str, 0, 4 ) == "\x2ERMF") {
			$format = 'rm';
		} elseif ($extname == 'sql') {
			$format = 'sql';
		} elseif ($extname == 'txt') {
			$format = 'txt';
		}
	}
	
	if ($limit_ext_types && stristr ( $limit_ext_types, '|' . $format . '|' ) === false) {
		$format = '';
	}
	
	return $format;
}

/**
 * 处理上传文件，并返回上传图片名(上传失败时返回图片名为空）
 *
 * @access  public
 * @param array     $upload     $_FILES 数组
 * @param array     $type       图片所属类别，即data目录下的文件夹名
 *
 * @return string               上传图片名
 */
function upload_file($upload, $type) {
	if (! empty ( $upload ['tmp_name'] )) {
		$ftype = check_file_type ( $upload ['tmp_name'], $upload ['name'], '|png|jpg|jpeg|gif|doc|xls|txt|zip|ppt|pdf|rar|' );
		if (! empty ( $ftype )) {
			$name = date ( 'Ymd' );
			for($i = 0; $i < 6; $i ++) {
				$name .= chr ( mt_rand ( 97, 122 ) );
			}
			
			$name = $_SESSION ['user_id'] . '_' . $name . '.' . $ftype;
			
			$target = ROOT_PATH . DATA_DIR . '/' . $type . '/' . $name;
			if (! move_upload_file ( $upload ['tmp_name'], $target )) {
				$GLOBALS ['err']->add ( $GLOBALS ['_LANG'] ['upload_file_error'], 1 );
				
				return false;
			} else {
				return $name;
			}
		} else {
			$GLOBALS ['err']->add ( $GLOBALS ['_LANG'] ['upload_file_type'], 1 );
			
			return false;
		}
	} else {
		$GLOBALS ['err']->add ( $GLOBALS ['_LANG'] ['upload_file_error'] );
		return false;
	}
}

function random_str($length) {
	$pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ'; //字符池
	for($i = 0; $i < $length; $i ++) {
		$key .= $pattern {mt_rand ( 0, 61 )}; //生成php随机数
	}
	return $key;
}

function random_key($length) {
	$output = '';
	for($a = 0; $a < $length; $a ++) {
		$output .= chr ( mt_rand ( 33, 126 ) ); //生成php随机数
	}
	return $output;
}

function is_not_blank($var) {
	return isset ( $var ) && ! empty ( $var );
}

function is_equal($var, $action = '') {
	return is_not_blank ( $var ) && trim ( $var ) == $action;
}

function daddslashes($string, $force = 0, $strip = FALSE) {
	if (! MAGIC_QUOTES_GPC || $force) {
		if (is_array ( $string )) {
			foreach ( $string as $key => $val ) {
				$string [$key] = daddslashes ( $val, $force );
			}
		} else {
			$string = addslashes ( $strip ? stripslashes ( $string ) : $string );
		}
	}
	return $string;
}

//SQL ADDSLASHES
function saddslashes($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = saddslashes ( $val );
		}
	} else {
		$string = addslashes ( $string );
	}
	return $string;
}

//去掉slassh
function sstripslashes($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = sstripslashes ( $val );
		}
	} else {
		$string = stripslashes ( $string );
	}
	return $string;
}

/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value) {
	if (empty ( $value )) {
		return $value;
	} else {
		return is_array ( $value ) ? array_map ( 'addslashes_deep', $value ) : addslashes ( $value );
	}
}

/**
 * 将对象成员变量或者数组的特殊字符进行转义
 *
 * @access   public
 * @param    mix        $obj      对象或者数组
 * @author   Xuan Yan
 *
 * @return   mix                  对象或者数组
 */
function addslashes_deep_obj($obj) {
	if (is_object ( $obj ) == true) {
		foreach ( $obj as $key => $val ) {
			$obj->$key = addslashes_deep ( $val );
		}
	} else {
		$obj = addslashes_deep ( $obj );
	}
	
	return $obj;
}

/**
 * 递归方式的对变量中的特殊字符去除转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function stripslashes_deep($value) {
	if (empty ( $value )) {
		return $value;
	} else {
		return is_array ( $value ) ? array_map ( 'stripslashes_deep', $value ) : stripslashes ( $value );
	}
}

/**
 * 对 MYSQL LIKE 的内容进行转义
 *
 * @access      public
 * @param       string      string  内容
 * @return      string
 */
function mysql_like_quote($str) {
	return strtr ( $str, array ("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%' ) );
}

if (! function_exists ( 'escape' )) {

	function escape($str, $like = FALSE) {
		if (is_array ( $str )) {
			foreach ( $str as $key => $val ) {
				$str [$key] = escape ( $val, $like );
			}
			return $str;
		}
		
		if (get_magic_quotes_gpc ()) {
			$str = stripslashes ( $str );
		}
		
		if (function_exists ( 'mysql_real_escape_string' )) {
			$str = mysql_real_escape_string ( $str );
		} elseif (function_exists ( 'mysql_escape_string' )) {
			$str = mysql_escape_string ( $str );
		} else {
			$str = addslashes ( $str );
		}
		
		// escape LIKE condition wildcards
		if ($like === TRUE) {
			$str = str_replace ( array ('%', '_' ), array ('\\%', '\\_' ), $str );
		}
		
		return trim ( $str );
	}
}

//取消HTML代码
function shtmlspecialchars($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = shtmlspecialchars ( $val );
		}
	} else {
		$string = preg_replace ( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', str_replace ( array ('&', '"', '<', '>' ), array ('&amp;', '&quot;', '&lt;', '&gt;' ), $string ) );
	}
	return $string;
}

function gpc_pre($str = '') {
	if ($str === '' or $str == NULL or ! isset ( $str )) {
		return '';
	}
	$temp = '__TEMP_AMPERSANDS__';
	
	// Replace entities to temporary markers so that
	// htmlspecialchars won't mess them up
	$str = preg_replace ( "/&#(\d+);/", "$temp\\1;", $str );
	$str = preg_replace ( "/&(\w+);/", "$temp\\1;", $str );
	$str = htmlspecialchars ( $str );
	// In case htmlspecialchars misses these.
	$str = str_replace ( array ("'", '"' ), array ("&#39;", "&quot;" ), $str );
	// Decode the temp markers back to entities
	$str = preg_replace ( "/$temp(\d+);/", "&#\\1;", $str );
	$str = preg_replace ( "/$temp(\w+);/", "&\\1;", $str );
	return $str;
}
 
function getgpc($k, $t = 'R') {
	switch ($t) {
		case 'P' :
			$var = &$_POST;
			break;
		case 'G' :
			$var = &$_GET;
			break;
		case 'C' :
			$var = &$_COOKIE;
			break;
		case 'R' :
			$var = &$_REQUEST;
			break;
	}
                   // $var=  htmlspecialchars($var);
//        if (version_compare(PHP_VERSION, '5.3.0','<')) {
             if(@get_magic_quotes_gpc()){
                 return isset ( $var [$k] ) ? (is_array ( $var [$k] ) ?  htmlspecialchars($var [$k])  : trim ( htmlspecialchars($var [$k]) )) : NULL;
             }else{
                 return isset ( $var [$k] ) ? (is_array ( $var [$k] ) ? addslashes(htmlspecialchars($var [$k])) : trim ( addslashes(htmlspecialchars($var [$k])) )) : NULL;
             }
//        }
	
}

function get_gpc($k, $t = 'R', $default = "") {
	switch ($t) {
		case 'P' :
			$var = &$_POST;
			break;
		case 'G' :
			$var = &$_GET;
			break;
		case 'C' :
			$var = &$_COOKIE;
			break;
		case 'R' :
			$var = &$_REQUEST;
			break;
	}
//        if (version_compare(PHP_VERSION, '5.3.0','<')) {  
             if(get_magic_quotes_gpc()){
	return isset ( $var [$k] ) ? (trim ( htmlspecialchars($var [$k])  )) : $default;
             }else{
                   return isset ( $var [$k] ) ? (trim ( addslashes(htmlspecialchars($var [$k])) )) : $default;
             }
//        }
}

function getgpc_prepare($k, $t = 'R') {
	$input = getgpc ( $k, $t );
	if (isset ( $input )) {
		if (is_array ( $input )) {
			$rtn = array ();
			foreach ( $input as $val ) {
				$rtn [] = gpc_pre ( $val );
			}
			return $rtn;
		} else {
			return gpc_pre ( $input );
		}
	}
	return NULL;
}

//清空cookie
function clearcookie() {
	obclean ();
	ssetcookie ( 'auth', '', - 86400 * 365 );

}

//cookie设置
function ssetcookie($var, $value, $life = 0) {
	global $_SGLOBAL, $_SC, $_SERVER;
	setcookie ( $_SC ['cookiepre'] . $var, $value, $life ? ($_SGLOBAL ['timestamp'] + $life) : 0, $_SC ['cookiepath'], $_SC ['cookiedomain'], $_SERVER ['SERVER_PORT'] == 443 ? 1 : 0 );
}

function set_cookie($var, $value = '', $time = 0) {
	$time = $time > 0 ? $time : ($value == '' ? PHP_TIME - 3600 : 0);
	$s = $_SERVER ['SERVER_PORT'] == '443' ? 1 : 0;
	$var = COOKIE_PRE . $var;
	$_COOKIE [$var] = $value;
	if (is_array ( $value )) {
		foreach ( $value as $k => $v ) {
			setcookie ( $var . '[' . $k . ']', $v, $time, COOKIE_PATH, COOKIE_DOMAIN, $s );
		}
	} else {
		setcookie ( $var, $value, $time, COOKIE_PATH, COOKIE_DOMAIN, $s );
	}
}

function get_cookie($var) {
	$var = COOKIE_PRE . $var;
	return isset ( $_COOKIE [$var] ) ? $_COOKIE [$var] : false;
}

function auth_code($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
	global $_configuration;
	if ($_configuration ['crypted_method'] == 'md5') {
		return md5 ( $string );
	}
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	
	$ckey_length = 4; // 随机密钥长度 取值 0-32;
	// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	// 当此值为 0 时，则不产生随机密钥
	

	$key = md5 ( $key ? $key : UC_KEY );
	$keya = md5 ( substr ( $key, 0, 16 ) );
	$keyb = md5 ( substr ( $key, 16, 16 ) );
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';
	
	$cryptkey = $keya . md5 ( $keya . $keyc );
	$key_length = strlen ( $cryptkey );
	
	$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
	$string_length = strlen ( $string );
	
	$result = '';
	$box = range ( 0, 255 );
	
	$rndkey = array ();
	for($i = 0; $i <= 255; $i ++) {
		$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
	}
	
	for($j = $i = 0; $i < 256; $i ++) {
		$j = ($j + $box [$i] + $rndkey [$i]) % 256;
		$tmp = $box [$i];
		$box [$i] = $box [$j];
		$box [$j] = $tmp;
	}
	
	for($a = $j = $i = 0; $i < $string_length; $i ++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box [$a]) % 256;
		$tmp = $box [$a];
		$box [$a] = $box [$j];
		$box [$j] = $tmp;
		$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
	}
	
	if ($operation == 'DECODE') {
		if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
			return substr ( $result, 26 );
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
	}

}

/**
 * 重定向浏览器到指定的 URL
 *
 * @param string $url 要重定向的 url
 * @param int $delay 等待多少秒以后跳转
 * @param bool $js 指示是否返回用于跳转的 JavaScript 代码
 * @param bool $jsWrapped 指示返回 JavaScript 代码时是否使用 <script> 标签进行包装
 * @param bool $return 指示是否返回生成的 JavaScript 代码
 */
function api_redirect($url, $delay = 0, $js = false, $jsWrapped = true, $return = false) {
	$delay = ( int ) $delay;
	if (! $js) {
		if (headers_sent () && $delay > 0) {
			echo <<<EOT
			<html><head><meta http-equiv="refresh" content="{$delay};URL={$url}" /></head></html>
EOT;
			exit ();
		} else {
			header ( "Location: {$url}" );
			exit ();
		}
	}
	
	$out = '';
	if ($jsWrapped) {
		$out .= '<script language="JavaScript" type="text/javascript">';
	}
	$url = rawurlencode ( $url );
	if ($delay > 0) {
		$out .= "window.setTimeOut(function () { document.location='{$url}'; }, {$delay});";
	} else {
		$out .= "document.location='{$url}';";
	}
	if ($jsWrapped) {
		$out .= '</script>';
	}
	
	if ($return) {
		return $out;
	}
	
	echo $out;
	exit ();
}

function nameToSafe($name, $maxlen = 250) {
	$noalpha = '�����������������������������������������������������@���';
	$alpha = 'AEIOUYaeiouyAEIOUaeiouAEIOUaeiouAEIOUaeiouyAaOoAaNnCcaooa';
	
	$name = substr ( $name, 0, $maxlen );
	$name = strtr ( $name, $noalpha, $alpha );
	// not permitted chars are replaced with "_"
	return preg_replace ( '/[^a-zA-Z0-9,._\+\()\-]/', '_', $name );
}

function get_microtime_str($n = 3) {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return $sec . "" . substr ( $usec, 2, $n );
}

function get_unique_name($n = 3) {
	//return api_get_user_name () . "_" . get_microtime_str ( $n );
	return get_microtime_str ( $n );
}

function get_onlineip() {
	$cip = getenv ( 'HTTP_CLIENT_IP' );
	$xip = getenv ( 'HTTP_X_FORWARDED_FOR' );
	$rip = getenv ( 'REMOTE_ADDR' );
	$srip = $_SERVER ['REMOTE_ADDR'];
	if ($cip && strcasecmp ( $cip, 'unknown' )) {
		$onlineip = $cip;
	} elseif ($xip && strcasecmp ( $xip, 'unknown' )) {
		$onlineip = $xip;
	} elseif ($rip && strcasecmp ( $rip, 'unknown' )) {
		$onlineip = $rip;
	} elseif ($srip && strcasecmp ( $srip, 'unknown' )) {
		$onlineip = $srip;
	}
	preg_match ( "/[\d\.]{7,15}/", $onlineip, $match );
	$onlineip = $match [0] ? $match [0] : 'unknown';
	
	return $onlineip;
}

//判断提交是否正确
function submit_check($var) {
	if (! empty ( $_POST [$var] ) && $_SERVER ['REQUEST_METHOD'] == 'POST') {
		if ((empty ( $_SERVER ['HTTP_REFERER'] ) || preg_replace ( "/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER ['HTTP_REFERER'] ) == preg_replace ( "/([^\:]+).*/", "\\1", $_SERVER ['HTTP_HOST'] )) && $_POST ['formhash'] == formhash ()) {
			return true;
		} else {
			//showmessage('submit_invalid');
			return false;
		}
	} else {
		return false;
	}
}

/**
 * 获得用户的真实IP地址
 *
 * @return  string
 */
function real_ip() {
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			
			/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
			foreach ( $arr as $ip ) {
				$ip = trim ( $ip );
				
				if ($ip != 'unknown') {
					$realip = $ip;
					
					break;
				}
			}
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$realip = $_SERVER ['HTTP_CLIENT_IP'];
		} else {
			if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
				$realip = $_SERVER ['REMOTE_ADDR'];
			} else {
				$realip = '0.0.0.0';
			}
		}
	} else {
		if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
			$realip = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
			$realip = getenv ( 'HTTP_CLIENT_IP' );
		} else {
			$realip = getenv ( 'REMOTE_ADDR' );
		}
	}
	
	preg_match ( "/[\d\.]{7,15}/", $realip, $onlineip );
	$realip = ! empty ( $onlineip [0] ) ? $onlineip [0] : '0.0.0.0';
	
	return $realip;
}

/**
 * 检查输入参数值是否为安全字符，
 * @param $param
 * @param $param_type 参数类型，1数字或字符，0其它
 * @return TRUE安全通过
 */
function is_safe_request_param($param, $param_type = 0) {
	if ($param_type == 0) {
		$p = "/(|\'|(\%27)|\;|(\%3b)|\=|(\%3d)|\(|(\%28)|\)|(\%29)|(\/*)|(\%2f%2a)|(\*/)|(\%2a%2f)|\+|(\%2b)|\<|(\%3c)|\>|(\%3e)|(\(--))|\[|\%5b|\]|\%5d)/";
	} else if ($param_type == 1) {
		$p = "/[^\w+$]/";
	}
	if (preg_match ( $p, $param ) > 0)
		return FALSE;
	else return TRUE;
}

//ob
function obclean($gzipcompress = false) {
	ob_end_clean ();
	if ($gzipcompress && function_exists ( 'ob_gzhandler' )) {
		ob_start ( 'ob_gzhandler' );
	} else {
		ob_start ();
	}
}

function xml_out($content) {
	
	@header ( "Expires: -1" );
	@header ( "Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE );
	@header ( "Pragma: no-cache" );
	@header ( "Content-type: application/xml; charset=" . SYSTEM_CHARSET . "\"" );
	echo '<' . "?xml version=\"1.0\" encoding=\"" . SYSTEM_CHARSET . "\"?>\n";
	echo "<root><![CDATA[" . trim ( $content ) . "]]></root>";
	exit ();
}

function api_json_encode($value) {
	if (SYSTEM_CHARSET == 'UTF-8' && function_exists ( 'json_encode' )) {
		return json_encode ( $value );
	}
	
	$props = '';
	if (is_object ( $value )) {
		foreach ( get_object_vars ( $value ) as $name => $propValue ) {
			if (isset ( $propValue )) {
				$props .= $props ? ',' . api_json_encode ( $name ) : api_json_encode ( $name );
				$props .= ':' . api_json_encode ( $propValue );
			}
		}
		return '{' . $props . '}';
	} elseif (is_array ( $value )) {
		$keys = array_keys ( $value );
		if (! empty ( $value ) && ! empty ( $value ) && ($keys [0] != '0' || $keys != range ( 0, count ( $value ) - 1 ))) {
			foreach ( $value as $key => $val ) {
				$key = ( string ) $key;
				$props .= $props ? ',' . api_json_encode ( $key ) : api_json_encode ( $key );
				$props .= ':' . api_json_encode ( $val );
			}
			return '{' . $props . '}';
		} else {
			$length = count ( $value );
			for($i = 0; $i < $length; $i ++) {
				$props .= ($props != '') ? ',' . api_json_encode ( $value [$i] ) : api_json_encode ( $value [$i] );
			}
			return '[' . $props . ']';
		}
	} elseif (is_string ( $value )) {
		//$value = stripslashes($value);
		$replace = array ('\\' => '\\\\', "\n" => '\n', "\t" => '\t', '/' => '\/', "\r" => '\r', "\b" => '\b', "\f" => '\f', '"' => '\"', chr ( 0x08 ) => '\b', chr ( 0x0C ) => '\f' );
		$value = strtr ( $value, $replace );
		if (CHARSET == 'big5' && $value {strlen ( $value ) - 1} == '\\') {
			$value = substr ( $value, 0, strlen ( $value ) - 1 );
		}
		return '"' . $value . '"';
	} elseif (is_numeric ( $value )) {
		return $value;
	} elseif (is_bool ( $value )) {
		return $value ? 'true' : 'false';
	} elseif (empty ( $value )) {
		return '""';
	} else {
		return $value;
	}
}

//处理搜索关键字
function stripsearchkey($string) {
	$string = trim ( $string );
	$string = str_replace ( '*', '%', addcslashes ( $string, '%_' ) );
	$string = str_replace ( '_', '\_', $string );
	return $string;
}

//连接字符
function simplode($ids) {
	return "'" . implode ( "','", $ids ) . "'";
}

//格式化大小函数
function format_size($size) {
	$prec = 3;
	$size = round ( abs ( $size ) );
	$units = array (0 => " B ", 1 => " KB", 2 => " MB", 3 => " GB", 4 => " TB" );
	if ($size == 0) return str_repeat ( " ", $prec ) . "0$units[0]";
	$unit = min ( 4, floor ( log ( $size ) / log ( 2 ) / 10 ) );
	$size = $size * pow ( 2, - 10 * $unit );
	$digi = $prec - 1 - floor ( log ( $size ) / log ( 10 ) );
	$size = round ( $size * pow ( 10, $digi ) ) * pow ( 10, - $digi );
	return $size . $units [$unit];
}

//获取文件内容
function sread_file($filename) {
	$content = '';
	if (function_exists ( 'file_get_contents' )) {
		@$content = file_get_contents ( $filename );
	} else {
		if (@$fp = fopen ( $filename, 'r' )) {
			@$content = fread ( $fp, filesize ( $filename ) );
			@fclose ( $fp );
		}
	}
	return $content;
}

//写入文件
function swrite_file($filename, $writetext, $openmod = 'w') {
	if (@$fp = fopen ( $filename, $openmod )) {
		flock ( $fp, 2 );
		fwrite ( $fp, $writetext );
		fclose ( $fp );
		return true;
	} else {
		//runlog('error', "File: $filename write error.");
		return false;
	}
}

//产生随机字符
function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand ( ( double ) microtime () * 1000000 ) : mt_srand ();
	$seed = base_convert ( md5 ( print_r ( $_SERVER, 1 ) . microtime () ), 16, $numeric ? 10 : 35 );
	$seed = $numeric ? (str_replace ( '0', '', $seed ) . '012340567890') : ($seed . 'zZ' . strtoupper ( $seed ));
	$hash = '';
	$max = strlen ( $seed ) - 1;
	for($i = 0; $i < $length; $i ++) {
		$hash .= $seed [mt_rand ( 0, $max )];
	}
	return $hash;
}

//判断字符串是否存在
function str_exists($haystack, $needle) {
	return ! (strpos ( $haystack, $needle ) === FALSE);
}

//获取文件名后缀
function file_ext($filename) {
	return strtolower ( trim ( substr ( strrchr ( $filename, '.' ), 1 ) ) );
}

//编码转换
function siconv($str, $out_charset, $in_charset = '') {
	$in_charset = empty ( $in_charset ) ? strtoupper ( SYSTEM_CHARSET ) : strtoupper ( $in_charset );
	$out_charset = strtoupper ( $out_charset );
	if ($in_charset != $out_charset) {
		if (function_exists ( 'iconv' ) && (@$outstr = iconv ( "$in_charset//IGNORE", "$out_charset//IGNORE", $str ))) {
			return $outstr;
		} elseif (function_exists ( 'mb_convert_encoding' ) && (@$outstr = mb_convert_encoding ( $str, $out_charset, $in_charset ))) {
			return $outstr;
		}
	}
	return $str; //转换失败
}

function get_week_name($w) {
	$name = "";
	switch ($w) {
		case 0 :
			$name = get_lang ( 'SundayLong' );
			break;
		case 1 :
			$name = get_lang ( 'MondayLong' );
			break;
		case 2 :
			$name = get_lang ( 'TuesdayLong' );
			break;
		case 3 :
			$name = get_lang ( 'WednesdayLong' );
			break;
		case 4 :
			$name = get_lang ( 'ThursdayLong' );
			break;
		case 5 :
			$name = get_lang ( 'FridayLong' );
			break;
		case 6 :
			$name = get_lang ( 'SaturdayLong' );
			break;
	}
	return $name;
}

//截取链接
function sub_url($url, $length) {
	if (strlen ( $url ) > $length) {
		$url = str_replace ( array ('%3A', '%2F' ), array (':', '/' ), rawurlencode ( $url ) );
		$url = substr ( $url, 0, intval ( $length * 0.5 ) ) . ' ... ' . substr ( $url, - intval ( $length * 0.3 ) );
	}
	return $url;
}

//ip访问允许
function ipaccess($ipaccess) {
	return empty ( $ipaccess ) ? true : preg_match ( "/^(" . str_replace ( array ("\r\n", ' ' ), array ('|', '' ), preg_quote ( $ipaccess, '/' ) ) . ")/", getonlineip () );
}

//ip访问禁止
function ipbanned($ipbanned) {
	return empty ( $ipbanned ) ? false : preg_match ( "/^(" . str_replace ( array ("\r\n", ' ' ), array ('|', '' ), preg_quote ( $ipbanned, '/' ) ) . ")/", getonlineip () );
}

/**
 * 获得当前的域名
 *
 * @return  string
 */
function get_domain() {
	// 协议
	$protocol = (isset ( $_SERVER ['HTTPS'] ) && (strtolower ( $_SERVER ['HTTPS'] ) != 'off')) ? 'https://' : 'http://';
	
	//域名或IP地址
	if (isset ( $_SERVER ['HTTP_X_FORWARDED_HOST'] )) {
		$host = $_SERVER ['HTTP_X_FORWARDED_HOST'];
	} elseif (isset ( $_SERVER ['HTTP_HOST'] )) {
		$host = $_SERVER ['HTTP_HOST'];
	} else {
		// 端口
		if (isset ( $_SERVER ['SERVER_PORT'] )) {
			$port = ':' . $_SERVER ['SERVER_PORT'];
			
			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
				$port = '';
			}
		} else {
			$port = '';
		}
		
		if (isset ( $_SERVER ['SERVER_NAME'] )) {
			$host = $_SERVER ['SERVER_NAME'] . $port;
		} elseif (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$host = $_SERVER ['SERVER_ADDR'] . $port;
		}
	}
	
	return $protocol . $host;
}

//取数组中的随机个
function sarray_rand($arr, $num = 1) {
	$r_values = array ();
	if ($arr && count ( $arr ) > $num) {
		if ($num > 1) {
			$r_keys = array_rand ( $arr, $num );
			foreach ( $r_keys as $key ) {
				$r_values [$key] = $arr [$key];
			}
		} else {
			$r_key = array_rand ( $arr, 1 );
			$r_values [$r_key] = $arr [$r_key];
		}
	} else {
		$r_values = $arr;
	}
	return $r_values;
}

/**
 * 获得网站的URL地址
 *
 * @return  string
 */
function site_url() {
	return get_domain () . substr ( PHP_SELF, 0, strrpos ( PHP_SELF, '/' ) );
}

//检查是否操作创始人
function is_root($username = '') {
	global $_configuration;
	$founders = $_configuration ['default_administrator_name'];
	
	//$founders = empty ( $default_super_admin ) ? array () : explode ( ',', $default_super_admin );
	if ($username && $founders) {
		return in_array ( $username, $founders );
	} else {
		return false;
	}
}

function isRoot($username = '') {
	global $_configuration;
	$default_super_admin = $_configuration ['default_administrator_name'];
	if (empty ( $username )) $username = api_get_user_name ();
	if ($username && $default_super_admin) {
		return in_array ( $username, $default_super_admin );
	} else {
		return false;
	}
}

//获取目录
function sreaddir($dir, $extarr = array()) {
	$dirs = array ();
	if ($dh = opendir ( $dir )) {
		while ( ($file = readdir ( $dh )) !== false ) {
			if (! empty ( $extarr ) && is_array ( $extarr )) {
				if (in_array ( strtolower ( fileext ( $file ) ), $extarr )) {
					$dirs [] = $file;
				}
			} else if ($file != '.' && $file != '..') {
				$dirs [] = $file;
			}
		}
		closedir ( $dh );
	}
	return $dirs;
}

/**
 * 验证输入的邮件地址是否合法
 *
 * @param   string      $email      需要验证的邮件地址
 *
 * @return bool
 */
function is_email($user_email) {
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
	if (strpos ( $user_email, '@' ) !== false && strpos ( $user_email, '.' ) !== false) {
		if (preg_match ( $chars, $user_email )) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @param   string  $time
 * @return  void
 */
function is_time($time) {
	$pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
	
	return preg_match ( $pattern, $time );
}

function is_ip($ip) {
	$pattern = '/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/';
	return preg_match ( $pattern, $ip );
}

/**
 * 格式化费用：可以输入数字或百分比的地方
 *
 * @param   string      $fee    输入的费用
 */
function format_fee($fee) {
	$fee = make_semiangle ( $fee );
	if (strpos ( $fee, '%' ) === false) {
		return floatval ( $fee );
	} else {
		return floatval ( $fee ) . '%';
	}
}

/**
 * 获取服务器的ip
 *
 * @access      public
 *
 * @return string
 **/
function real_server_ip() {
	static $serverip = NULL;
	
	if ($serverip !== NULL) {
		return $serverip;
	}
	
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$serverip = $_SERVER ['SERVER_ADDR'];
		} else {
			$serverip = '0.0.0.0';
		}
	} else {
		$serverip = getenv ( 'SERVER_ADDR' );
	}
	
	return $serverip;
}

/**
 * 获得用户操作系统的换行符
 *
 * @access  public
 * @return  string
 */
function get_crlf() {
	/* LF (Line Feed, 0x0A, \N) 和 CR(Carriage Return, 0x0D, \R) */
	if (stristr ( $_SERVER ['HTTP_USER_AGENT'], 'Win' )) {
		$the_crlf = "\r\n";
	} elseif (stristr ( $_SERVER ['HTTP_USER_AGENT'], 'Mac' )) {
		$the_crlf = "\r"; // for old MAC OS
	} else {
		$the_crlf = "\n";
	}
	
	return $the_crlf;
}

/**
 * 编码转换函数
 *
 * @author  wj
 * @param string $source_lang       待转换编码
 * @param string $target_lang         转换后编码
 * @param string $source_string      需要转换编码的字串
 * @return string
 */
function api_iconv($source_lang, $target_lang, $source_string = '') {
	static $chs = NULL;
	
	/* 如果字符串为空或者字符串不需要转换，直接返回 */
	if ($source_lang == $target_lang || $source_string == '' || preg_match ( "/[\x80-\xFF]+/", $source_string ) == 0) {
		return $source_string;
	}
	
	if ($chs === NULL) {
		require_once (ROOT_PATH . '/includes/cls.iconv.php');
		$chs = new Chinese ( ROOT_PATH . '/' );
	}
	
	return strtolower ( $target_lang ) == 'utf-8' ? addslashes ( stripslashes ( $chs->Convert ( $source_lang, $target_lang, $source_string ) ) ) : $chs->Convert ( $source_lang, $target_lang, $source_string );
}

/**
 * 删除目录,不支持目录中带 ..
 *
 * @param string $dir
 *
 * @return boolen
 */
function api_rmdir($dir) {
	$dir = str_replace ( array ('..', "\n", "\r" ), array ('', '', '' ), $dir );
	$ret_val = false;
	if (is_dir ( $dir )) {
		$d = @dir ( $dir );
		if ($d) {
			while ( false !== ($entry = $d->read ()) ) {
				if ($entry != '.' && $entry != '..') {
					$entry = $dir . '/' . $entry;
					if (is_dir ( $entry )) {
						api_rmdir ( $entry );
					} else {
						@unlink ( $entry );
					}
				}
			}
			$d->close ();
			$ret_val = rmdir ( $dir );
		}
	} else {
		$ret_val = unlink ( $dir );
	}
	
	return $ret_val;
}

/**
 * 设置COOKIE
 *
 * @access public
 * @param  string $key     要设置的COOKIE键名
 * @param  string $value   键名对应的值
 * @param  int    $expire  过期时间
 * @return void
 */
function ecm_setcookie($key, $value, $expire = 0, $cookie_path = COOKIE_PATH, $cookie_domain = COOKIE_DOMAIN) {
	setcookie ( $key, $value, $expire, $cookie_path, $cookie_domain );
}

/**
 * 获取COOKIE的值
 *
 * @access public
 * @param  string $key    为空时将返回所有COOKIE
 * @return mixed
 */
function ecm_getcookie($key = '') {
	return isset ( $_COOKIE [$key] ) ? $_COOKIE [$key] : 0;
}

/**
 * 对数组转码
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function api_iconv_deep($source_lang, $target_lang, $value) {
	if (empty ( $value )) {
		return $value;
	} else {
		if (is_array ( $value )) {
			foreach ( $value as $k => $v ) {
				$value [$k] = api_iconv_deep ( $source_lang, $target_lang, $v );
			}
			return $value;
		} elseif (is_string ( $value )) {
			return api_iconv ( $source_lang, $target_lang, $value );
		} else {
			return $value;
		}
	}
}

/**
 * fopen封装函数
 *
 * @author wj
 * @param string $url
 * @param int    $limit
 * @param string $post
 * @param string $cookie
 * @param boolen $bysocket
 * @param string $ip
 * @param int    $timeout
 * @param boolen $block
 * @return responseText
 */
function api_fopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true) {
	$return = '';
	$matches = parse_url ( $url );
	$host = $matches ['host'];
	$path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
	$port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;
	
	if ($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
	if (! $fp) {
		return '';
	} else {
		stream_set_blocking ( $fp, $block );
		stream_set_timeout ( $fp, $timeout );
		@fwrite ( $fp, $out );
		$status = stream_get_meta_data ( $fp );
		if (! $status ['timed_out']) {
			while ( ! feof ( $fp ) ) {
				if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
					break;
				}
			}
			
			$stop = false;
			while ( ! feof ( $fp ) && ! $stop ) {
				$data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
				$return .= $data;
				if ($limit) {
					$limit -= strlen ( $data );
					$stop = $limit <= 0;
				}
			}
		}
		@fclose ( $fp );
		return $return;
	}
}

/**
 * 危险 HTML代码过滤器
 *
 * @param   string  $html   需要过滤的html代码
 *
 * @return  string
 */
function func_html_filter($html) {
	$filter = array ("/\s/", "/<(\/?)(script|i?frame|style|html|body|title|link|object|meta|\?|\%)([^>]*?)>/isU", "/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU" );
	
	$replace = array (" ", "&lt;\\1\\2\\3&gt;", "\\1\\2" );
	
	$str = preg_replace ( $filter, $replace, $html );
	return $str;
}

/**
 * 返回是否是通过浏览器访问的页面
 *
 * @author wj
 * @param  void
 * @return boolen
 */
function is_from_browser() {
	static $ret_val = null;
	if ($ret_val === null) {
		$ret_val = false;
		$ua = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? strtolower ( $_SERVER ['HTTP_USER_AGENT'] ) : '';
		if ($ua) {
			if ((strpos ( $ua, 'mozilla' ) !== false) && ((strpos ( $ua, 'msie' ) !== false) || (strpos ( $ua, 'gecko' ) !== false))) {
				$ret_val = true;
			} elseif (strpos ( $ua, 'opera' )) {
				$ret_val = true;
			}
		}
	}
	return $ret_val;
}

function price_format($price, $price_format = NULL) {
	if (empty ( $price )) $price = '0.00';
	$price = number_format ( $price, 2 );
	
	if ($price_format === NULL) {
		$price_format = get_lang ( 'PriceFormat' );
	}
	
	return sprintf ( $price_format, $price );
}

/**
 * 获得所有模块的名称以及链接地址
 *
 * @access      public
 * @param       string      $directory      插件存放的目录
 * @return      array
 */
function read_modules($directory = '.') {
	$dir = @opendir ( $directory );
	$modules = array ();
	while ( false !== ($file = @readdir ( $dir )) ) {
		//if (preg_match("/^.*?\.php$/", $file))
		$is_dir = is_dir ( $directory . '/' . $file );
		if ($file != "." && $file != ".." && $is_dir === true) {
			$inc_file = $directory . '/' . $file . "/index.php";
			include_once ($inc_file);
		}
	}
	@closedir ( $dir );
	
	/*foreach ($modules AS $key => $value)
	 {
		ksort($modules[$key]);
		}
		ksort($modules);*/
	
	return $modules;
}

function get_seccode() {
	global $_configuration;
	$authkey = md5 ( $_configuration ['security_key'] . $_SERVER ['HTTP_USER_AGENT'] . get_onlineip () );
	$rand = rand ( 100000, 999999 );
	$seccodeinit = rawurlencode ( authcode ( $rand, 'ENCODE', $authkey, 180 ) );
	return $seccodeinit;
}

function getFLVDuration($flv_path) {
	require_once (api_get_path ( LIB_PATH ) . "getid3/getid3.php");
	$getid3 = new getID3 ();
	$getid3->encoding = SYSTEM_CHARSET;
	try {
		$getid3->Analyze ( $flv_path );
		return $getid3->info ['playtime_seconds'];
	} catch ( Exception $e ) {
		return 0;
	}
}

function get_upload_max_filesize($size = 0) {
	$upload_max_filesize = ini_get ( 'upload_max_filesize' );
	$post_max_size = ini_get ( 'post_max_size' );
	$memory_limit = ini_get ( 'memory_limit' ); //max_execution_time = 300 ; max_input_time = 600
	$upload_max_filesize = intval ( str_replace ( 'M', '', $upload_max_filesize ) );
	$post_max_size = intval ( str_replace ( 'M', '', $post_max_size ) );
	$memory_limit = intval ( str_replace ( 'M', '', $memory_limit ) );
	if ($size == 0 or empty ( $size ))
		return min ( $upload_max_filesize, $post_max_size, $memory_limit );
	elseif ($size > 0)
		return min ( $upload_max_filesize, $post_max_size, $memory_limit, $size );
}

function calendar_compare_lte($inputValues) {
	$values = array_values ( $inputValues );
	$date1 = $values [0];
	$date2 = $values [1];
	//var_dump($values);exit;
	if ($date1 && $date2) {
		if (preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+:\d+)$/', $date1 ) or preg_match ( '/(\d+-\d+-\d+\s+\d+:\d+)$/', $date1 )) {
			$format = 'Y-m-d H:i';
		}
		if (preg_match ( '/(\d+-\d+-\d+)$/', $date1 ) && preg_match ( '/(\d+-\d+-\d+)$/', $date2 )) {
			$format = 'Y-m-d';
		}
		//echo $format;exit;
		if ($format == 'Y-m-d') {
			list ( $year1, $month1, $day1 ) = explode ( '-', $date1 );
			list ( $year2, $month2, $day2 ) = explode ( '-', $date2 );
			$time1 = mktime ( 0, 0, 0, $month1, $day1, $year1 );
			$time2 = mktime ( 0, 0, 0, $month2, $day2, $year2 );
		} elseif ($format == 'Y-m-d H:i') {
			list ( $date_tmp, $time_tmp ) = preg_split ( '/\s+/', $date1 );
			list ( $year1, $month1, $day1 ) = explode ( '-', $date_tmp );
			list ( $hour1, $min1 ) = explode ( ':', $time_tmp );
			$time1 = mktime ( $hour1, $min1, 0, $month1, $day1, $year1 );
			
			list ( $date_tmp, $time_tmp ) = preg_split ( '/\s+/', $date2 );
			list ( $year2, $month2, $day2 ) = explode ( '-', $date_tmp );
			list ( $hour2, $min2 ) = explode ( ':', $time_tmp );
			$time2 = mktime ( $hour2, $min2, 0, $month2, $day2, $year2 );
		}
		
		return $time1 < $time2;
	}
	
	return false;
}

function format_textarea($string) {
	return nl2br ( str_replace ( ' ', '&nbsp;', htmlspecialchars ( $string ) ) );
}

function stripstr($str) {
	return str_replace ( array ('..', "\n", "\r" ), array ('', '', '' ), $str );
}

function is_date($ymd, $sep = '-') {
	if (empty ( $ymd )) return FALSE;
	list ( $year, $month, $day ) = explode ( $sep, $ymd );
	return checkdate ( $month, $day, $year );
}

function is_email2($email) {
	return strlen ( $email ) > 6 && preg_match ( "/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email );
}

function file_down($filepath, $filename = '') {
	if (! $filename) $filename = basename ( $filepath );
	if (is_ie ()) $filename = rawurlencode ( $filename );
	$filetype = fileext ( $filename );
	$filesize = sprintf ( "%u", filesize ( $filepath ) );
	if (ob_get_length () !== false) @ob_end_clean ();
	header ( 'Pragma: public' );
	header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
	header ( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header ( 'Cache-Control: pre-check=0, post-check=0, max-age=0' );
	header ( 'Content-Transfer-Encoding: binary' );
	header ( 'Content-Encoding: none' );
	header ( 'Content-type: ' . $filetype );
	header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header ( 'Content-length: ' . $filesize );
	readfile ( $filepath );
	exit ();
}

function check_submit($var) {
	if (empty ( $GLOBALS [$var] )) return false;
	if (empty ( $_SERVER ['HTTP_REFERER'] )) return true;
	return strpos ( $_SERVER ['HTTP_REFERER'], DOMAIN ) === 7;
}

function check_in($id, $ids = '', $s = ',') {
	if (! $ids) return false;
	$ids = explode ( $s, $ids );
	return is_array ( $id ) ? array_intersect ( $id, $ids ) : in_array ( $id, $ids );
}

function ip() {
	if (getenv ( 'HTTP_CLIENT_IP' ) && strcasecmp ( getenv ( 'HTTP_CLIENT_IP' ), 'unknown' )) {
		$ip = getenv ( 'HTTP_CLIENT_IP' );
	} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' ) && strcasecmp ( getenv ( 'HTTP_X_FORWARDED_FOR' ), 'unknown' )) {
		$ip = getenv ( 'HTTP_X_FORWARDED_FOR' );
	} elseif (getenv ( 'REMOTE_ADDR' ) && strcasecmp ( getenv ( 'REMOTE_ADDR' ), 'unknown' )) {
		$ip = getenv ( 'REMOTE_ADDR' );
	} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], 'unknown' )) {
		$ip = $_SERVER ['REMOTE_ADDR'];
	}
	return preg_match ( "/[\d\.]{7,15}/", $ip, $matches ) ? $matches [0] : 'unknown';
}

function str_cut($string, $length, $dot = '...') {
	$strlen = strlen ( $string );
	if ($strlen <= $length) return $string;
	$string = str_replace ( array ('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;' ), array (' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…' ), $string );
	$strcut = '';
	if (strtolower ( CHARSET ) == 'utf-8') {
		$n = $tn = $noc = 0;
		while ( $n < $strlen ) {
			$t = ord ( $string [$n] );
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n ++;
				$noc ++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t < 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n ++;
			}
			if ($noc >= $length) break;
		}
		if ($noc > $length) $n -= $tn;
		$strcut = substr ( $string, 0, $n );
	} else {
		$dotlen = strlen ( $dot );
		$maxi = $length - $dotlen - 1;
		for($i = 0; $i < $maxi; $i ++) {
			$strcut .= ord ( $string [$i] ) > 127 ? $string [$i] . $string [++ $i] : $string [$i];
		}
	}
	$strcut = str_replace ( array ('&', '"', "'", '<', '>' ), array ('&amp;', '&quot;', '&#039;', '&lt;', '&gt;' ), $strcut );
	return $strcut . $dot;
}

function datecheck($ymd, $sep = '-') {
	if (! empty ( $ymd )) {
		list ( $year, $month, $day ) = explode ( $sep, $ymd );
		return checkdate ( $month, $day, $year );
	} else {
		return FALSE;
	}
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url ( $url );
	$host = $matches ['host'];
	$path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
	$port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;
	
	if ($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
	if (! $fp) {
		return '';
	} else {
		stream_set_blocking ( $fp, $block );
		stream_set_timeout ( $fp, $timeout );
		@fwrite ( $fp, $out );
		$status = stream_get_meta_data ( $fp );
		if (! $status ['timed_out']) {
			while ( ! feof ( $fp ) ) {
				if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
					break;
				}
			}
			
			$stop = false;
			while ( ! feof ( $fp ) && ! $stop ) {
				$data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
				$return .= $data;
				if ($limit) {
					$limit -= strlen ( $data );
					$stop = $limit <= 0;
				}
			}
		}
		@fclose ( $fp );
		return $return;
	}
}

function dhtmlspecialchars($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = dhtmlspecialchars ( $val );
		}
	} else {
		$string = preg_replace ( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', //$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
str_replace ( array ('&', '"', '<', '>' ), array ('&amp;', '&quot;', '&lt;', '&gt;' ), $string ) );
	}
	return $string;
}

function dheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace ( array ("\r", "\n" ), array ('', '' ), $string );
	if (empty ( $http_response_code ) || PHP_VERSION < '4.3') {
		@header ( $string, $replace );
	} else {
		@header ( $string, $replace, $http_response_code );
	}
	if (preg_match ( '/^\s*location:/is', $string )) {
		exit ();
	}
}

function dreferer($default = '') {
	global $referer, $indexname;
	
	$default = empty ( $default ) ? $indexname : '';
	if (empty ( $referer ) && isset ( $GLOBALS ['_SERVER'] ['HTTP_REFERER'] )) {
		$referer = preg_replace ( "/([\?&])((sid\=[a-z0-9]{6})(&|$))/i", '\\1', $GLOBALS ['_SERVER'] ['HTTP_REFERER'] );
		$referer = substr ( $referer, - 1 ) == '?' ? substr ( $referer, 0, - 1 ) : $referer;
	} else {
		$referer = dhtmlspecialchars ( $referer );
	}
	
	if (strpos ( $referer, 'logging.php' )) {
		$referer = $default;
	}
	return $referer;
}

function getrobot() {
	if (! defined ( 'IS_ROBOT' )) {
		$kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
		$kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
		if (! str_exists ( $_SERVER ['HTTP_USER_AGENT'], 'http://' ) && preg_match ( "/($kw_browsers)/i", $_SERVER ['HTTP_USER_AGENT'] )) {
			define ( 'IS_ROBOT', FALSE );
		} elseif (preg_match ( "/($kw_spiders)/i", $_SERVER ['HTTP_USER_AGENT'] )) {
			define ( 'IS_ROBOT', TRUE );
		} else {
			define ( 'IS_ROBOT', FALSE );
		}
	}
	return IS_ROBOT;
}

function implodeids($array) {
	if (! empty ( $array )) {
		return "'" . implode ( "','", is_array ( $array ) ? $array : array ($array ) ) . "'";
	} else {
		return '';
	}
}

function writelog($file, $log) {
	global $timestamp, $_DCACHE;
	$yearmonth = gmdate ( 'Ym', $timestamp + $_DCACHE ['settings'] ['timeoffset'] * 3600 );
	$logdir = DISCUZ_ROOT . './forumdata/logs/';
	$logfile = $logdir . $yearmonth . '_' . $file . '.php';
	if (@filesize ( $logfile ) > 2048000) {
		$dir = opendir ( $logdir );
		$length = strlen ( $file );
		$maxid = $id = 0;
		while ( $entry = readdir ( $dir ) ) {
			if (strexists ( $entry, $yearmonth . '_' . $file )) {
				$id = intval ( substr ( $entry, $length + 8, - 4 ) );
				$id > $maxid && $maxid = $id;
			}
		}
		closedir ( $dir );
		
		$logfilebak = $logdir . $yearmonth . '_' . $file . '_' . ($maxid + 1) . '.php';
		@rename ( $logfile, $logfilebak );
	}
	if ($fp = @fopen ( $logfile, 'a' )) {
		@flock ( $fp, 2 );
		$log = is_array ( $log ) ? $log : array ($log );
		foreach ( $log as $tmp ) {
			fwrite ( $fp, "<?PHP exit;?>\t" . str_replace ( array ('<?', '?>' ), '', $tmp ) . "\n" );
		}
		fclose ( $fp );
	}
}

/**
 * 分析服务器负载
 *
 * 只针对*unix服务器有效
 *
 * @param int $loadavg 负载最大值
 * @return boolean 是否超过最大负载
 */
function pwLoadAvg($loadavg) {
	$avgstats = 0;
	if (file_exists ( '/proc/loadavg' )) {
		if ($fp = @fopen ( '/proc/loadavg', 'r' )) {
			$avgdata = @fread ( $fp, 6 );
			@fclose ( $fp );
			list ( $avgstats ) = explode ( ' ', $avgdata );
		}
	}
	if ($avgstats > $loadavg) {
		return true;
	} else {
		return false;
	}
}

function utf8_trim($str) {
	$hex = '';
	$len = strlen ( $str ) - 1;
	for($i = $len; $i >= 0; $i -= 1) {
		$ch = ord ( $str [$i] );
		$hex .= " $ch";
		if (($ch & 128) == 0 || ($ch & 192) == 192) {
			return substr ( $str, 0, $i );
		}
	}
	return $str . $hex;
}

function randstr($lenth) {
	return substr ( md5 ( num_rand ( $lenth ) ), mt_rand ( 0, 32 - $lenth ), $lenth );
}

function num_rand($lenth) {
	mt_srand ( ( double ) microtime () * 1000000 );
	$randval = '';
	for($i = 0; $i < $lenth; $i ++) {
		$randval .= mt_rand ( 0, 9 );
	}
	return $randval;
}

function is_in_array($needle, $haystack) {
	if (! $needle || empty ( $haystack ) || ! in_array ( $needle, $haystack )) {
		return false;
	}
	return true;
}

/**
 * 针对SQL语句的变量进行反斜线过滤,并两边添加单引号
 *
 * @param mixed $var 过滤前变量
 * @param boolean $strip 数据是否经过stripslashes处理
 * @param boolean $is_array 变量是否为数组
 * @return mixed 过滤后变量
 */
function sql_escape($var, $strip = false, $is_array = false) {
	if (is_array ( $var )) {
		if (! $is_array) return " '' ";
		foreach ( $var as $key => $value ) {
			$var [$key] = trim ( sql_escape ( $value, $strip ) );
		}
		return $var;
	} elseif (is_numeric ( $var )) {
		return " '" . $var . "' ";
	} else {
		return " '" . addslashes ( $strip ? stripslashes ( $var ) : $var ) . "' ";
	}
}

/**
 * SQL查询中,构造LIMIT语句
 *
 * @param integer $start 开始记录位置
 * @param integer $num 读取记录数目
 * @return string SQL语句
 */
function sql_limit($start, $num = false) {
	return ' LIMIT ' . ($start <= 0 ? 0 : ( int ) $start) . ($num ? ',' . abs ( $num ) : '');
}

function force_download($filename = '', $data = '') {
	if ($filename == '' or $data == '') {
		return FALSE;
	}
	
	// Try to determine if the filename includes a file extension. We need it in order to set the MIME type
	if (FALSE === strpos ( $filename, '.' )) {
		return FALSE;
	}
	
	// Grab the file extension
	$x = explode ( '.', $filename );
	$extension = end ( $x );
	
	// Load the mime types
	@include ('../conf/mimes' . EXT);
	
	// Set a default mime if we can't find it
	if (! isset ( $mimes [$extension] )) {
		$mime = 'application/octet-stream';
	} else {
		$mime = (is_array ( $mimes [$extension] )) ? $mimes [$extension] [0] : $mimes [$extension];
	}
	
	// Generate the server headers
	if (strstr ( $_SERVER ['HTTP_USER_AGENT'], "MSIE" )) {
		header ( 'Content-Type: "' . $mime . '"' );
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header ( 'Expires: 0' );
		header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header ( "Content-Transfer-Encoding: binary" );
		header ( 'Pragma: public' );
		header ( "Content-Length: " . strlen ( $data ) );
	} else {
		header ( 'Content-Type: "' . $mime . '"' );
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header ( "Content-Transfer-Encoding: binary" );
		header ( 'Expires: 0' );
		header ( 'Pragma: no-cache' );
		header ( "Content-Length: " . strlen ( $data ) );
	}
	
	exit ( $data );
}

/**
 * Detect agent browser
 *
 * @since 3.6.0
 * @return string The client's browser
 */
function detectBrowser() {
	$mobileAgents = array ('iphone', 'ipod', 'blackberry', 'htc', 'palm', 'windows ce', 'opera mini', 'android', 'midp', 'symbian' );
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	switch (true) {
		case preg_match ( "/(" . implode ( "|", $mobileAgents ) . ")/i", $agent ) != 0 :
			$browser = 'mobile';
			break;
		case stripos ( $agent, 'firefox' ) !== false :
			$browser = 'Firefox';
			break;
		case stripos ( $agent, 'msie 6.0' ) !== false :
			$browser = 'IE6';
			break;
		case stripos ( $agent, 'msie 7.0' ) !== false :
			$browser = 'IE7';
			break;
		case stripos ( $agent, 'msie 8.0' ) !== false :
			$browser = 'IE8';
			break;
		case stripos ( $agent, 'msie' ) !== false :
			$browser = 'IE';
			break;
		case stripos ( $agent, 'chrome' ) !== false :
			$browser = 'Chrome';
			break;
		case stripos ( $agent, 'safari' ) !== false :
			$browser = 'Safari';
			break;
		default :
			$browser = 'IE';
			break;
	}
	return $browser;
}

/**
 * Redirect to another page
 *
 * This function implements either server-side (php) or client side (javascript) redirection
 * <br/>Example:
 * <code>
 * </code>
 *
 * @param string $url The url to redirect to. If 'self' is used, it is equivalent to a reload (only it isn't)
 * @param boolean $js Whether to use js-based redirection
 * @param string $target which frame to reload (only applicable when $js is true). Can be 'top', 'window' or any frame name
 * @since 3.6.0
 */
function s_redirect($url, $js = false, $target = 'top') {
	$parts = parse_url ( $url );
	if (isset ( $parts ['query'] ) && $parts ['query']) {
		if ($GLOBALS ['configuration'] ['encrypt_url']) {
			$parts ['query'] = 'cru=' . encryptString ( $parts ['query'] );
		}
		$parts ['query'] = '?' . $parts ['query'];
	} else {
		$parts ['query'] = '';
	}
	$url = WEB_ROOT . basename ( $parts ['path'] ) . $parts ['query'];
	if ($js) {
		echo "<script language='JavaScript'>$target.location='$url'</script>";
	} else {
		header ( "location: $url" );
	}
	exit ();
}

/**
 * Encrypt a string based on the specified parameter
 *
 * @param string $string The string to encode
 * @param string $method The method to use
 * @return string The encoded string
 * @since 3.6.0
 */
function encryptString($string, $method = 'base64') {
	switch ($method) {
		case 'rot13' :
			$encodedString = urlencode ( str_rot13 ( $string ) );
			break;
		case 'base64' :
			$encodedString = urlencode ( base64_encode ( $string ) );
			break;
		default :
			$encodedString = $string;
			break;
	}
	return $encodedString;
}

/**
 * Decode a string based on the specified parameter
 *
 * @param string $string The string to encode
 * @param string $method The method to use
 * @return string The decoded string
 * @since 3.6.0
 */
function decryptString($string, $method = 'base64') {
	switch ($method) {
		case 'rot13' :
			$decodedString = str_rot13 ( urldecode ( $string ) );
			break;
		case 'base64' :
			$decodedString = base64_decode ( urldecode ( $string ) );
			break;
		default :
			$decodedString = $string;
			break;
	}
	return $decodedString;
}

function delete_files($directory) {
	if (is_dir ( $directory )) {
		$files = scandir ( $directory );
		set_time_limit ( 0 );
		foreach ( $files as $file ) {
			if (($file != '.') && ($file != '..')) {
				if (! is_dir ( $directory . '/' . $file )) {
					unlink ( $directory . '/' . $file );
				} else {
					delete_files ( $directory . '/' . $file );
				}
			}
		}
		rmdir ( $directory );
		return true;
	}
	return false;
}

function error($text, $notfound = false) {
	header ( $notfound ? 'HTTP/1.0 404 Not Found' : 'HTTP/1.0 500 Internal Server Error' );
	header ( 'Content-Type: text/plain' );
	print $text;
	exit ();
}

function array_insert_first($array, $e) {
	$new_arr = array ();
	if ($e && is_array ( $e )) {
		$new_arr [key ( $e )] = $e [key ( $e )];
		if ($array && is_array ( $array )) {
			foreach ( $array as $key => $val ) {
				$new_arr [$key] = $val;
			}
		}
	}
	return $new_arr;
}

function get_real_size($size = 0) {
	if (! $size) {
		return 0;
	}
	$scan ['MB'] = 1048576;
	$scan ['Mb'] = 1048576;
	$scan ['M'] = 1048576;
	$scan ['m'] = 1048576;
	$scan ['KB'] = 1024;
	$scan ['Kb'] = 1024;
	$scan ['K'] = 1024;
	$scan ['k'] = 1024;
	
	while ( list ( $key ) = each ( $scan ) ) {
		if ((strlen ( $size ) > strlen ( $key )) && (substr ( $size, strlen ( $size ) - strlen ( $key ) ) == $key)) {
			$size = substr ( $size, 0, strlen ( $size ) - strlen ( $key ) ) * $scan [$key];
			break;
		}
	}
	return $size;
}

/**
 * Adds up all the files in a directory and works out the size.
 *
 * @param string $rootdir  ?
 * @param string $excludefile  ?
 * @return array
 * @todo Finish documenting this function
 */
function get_directory_size($rootdir, $excludefile = '') {
	
	global $_configuration;
	
	// do it this way if we can, it's much faster
	if (IS_WINDOWS_OS == FALSE && ! empty ( $_configuration ["pathtodu"] ) && is_executable ( trim ( $_configuration ["pathtodu"] ) )) {
		$command = trim ( $_configuration ["pathtodu"] ) . ' -sk ' . escapeshellarg ( $rootdir );
		$output = null;
		$return = null;
		exec ( $command, $output, $return );
		if (is_array ( $output )) {
			return get_real_size ( intval ( $output [0] ) . 'k' ); // we told it to return k.
		}
	}
	
	if (! is_dir ( $rootdir )) { // Must be a directory
		return 0;
	}
	
	if (! $dir = @opendir ( $rootdir )) { // Can't open it for some reason
		return 0;
	}
	
	$size = 0;
	while ( false !== ($file = readdir ( $dir )) ) {
		$firstchar = substr ( $file, 0, 1 );
		if ($firstchar == '.' or $file == 'CVS' or $file == $excludefile) {
			continue;
		}
		$fullfile = $rootdir . '/' . $file;
		if (filetype ( $fullfile ) == 'dir') {
			$size += get_directory_size ( $fullfile, $excludefile );
		} else {
			$size += filesize ( $fullfile );
		}
	}
	closedir ( $dir );
	
	return $size;
}

/**
 * Returns current name of file on disk if it exists.
 *
 * @param string $newfile File to be verified
 * @return string Current name of file on disk if true
 */
function valid_uploaded_file($newfile) {
	if (empty ( $newfile )) {
		return '';
	}
	if (is_uploaded_file ( $newfile ['tmp_name'] ) and $newfile ['size'] > 0) {
		return $newfile ['tmp_name'];
	} else {
		return '';
	}
}

/**
 * Cleans a given filename by removing suspicious or troublesome characters
 * Only these are allowed: alphanumeric _ - .
 * Unicode characters can be enabled by setting $CFG->unicodecleanfilename = true in config.php
 *
 * WARNING: unicode characters may not be compatible with zip compression in backup/restore,
 * because native zip binaries do weird character conversions. Use PHP zipping instead.
 *
 * @param string $string  file name
 * @return string cleaned file name
 */
function clean_filename($string) {
	global $_configuration;
	require_once (api_get_path ( LIB_PATH ) . 'textlib.class.php');
	if (empty ( $_configuration ['unicodecleanfilename'] )) {
		$textlib = textlib_get_instance ();
		$string = $textlib->specialtoascii ( $string );
		$string = preg_replace ( '/[^\.a-zA-Z\d\_-]/', '_', $string ); // only allowed chars
	} else {
		//clean only ascii range
		$string = preg_replace ( "/[\\000-\\x2c\\x2f\\x3a-\\x40\\x5b-\\x5e\\x60\\x7b-\\177]/s", '_', $string );
	}
	$string = preg_replace ( "/_+/", '_', $string );
	$string = preg_replace ( "/\.\.+/", '.', $string );
	return $string;
}

/**
 * Create a directory.
 *
 * @uses $CFG
 * @param string $directory  a string of directory names under $CFG->dataroot eg  stuff/assignment/1
 * param bool $shownotices If true then notification messages will be printed out on error.
 * @return string|false Returns full path to directory if successful, false if not
 */
function make_upload_directory($directory, $shownotices = true) {
	global $_configuration;
	$currdir = $_configuration ["dataroot"];
	umask ( 0000 );
	if (! file_exists ( $currdir )) {
		if (! mkdir ( $currdir, $_configuration ["directorypermissions"] )) {
			if ($shownotices) {
				echo '<div class="notifyproblem" align="center">ERROR: You need to create the directory ' . $currdir . ' with web server write access</div>' . "<br />\n";
			}
			return false;
		}
	}
	
	// Make sure a .htaccess file is here, JUST IN CASE the files area is in the open
	if (! file_exists ( $currdir . '/.htaccess' )) {
		if ($handle = fopen ( $currdir . '/.htaccess', 'w' )) { // For safety
			//@fwrite($handle, "deny from all\r\nAllowOverride None\r\n");
			@fwrite ( $handle, "order deny,allow\r\ndeny from all" );
			@fclose ( $handle );
		}
	}
	
	$dirarray = explode ( '/', $directory );
	foreach ( $dirarray as $dir ) {
		$currdir = $currdir . '/' . $dir;
		if (! file_exists ( $currdir )) {
			if (! mkdir ( $currdir, $_configuration ["directorypermissions"] )) {
				if ($shownotices) {
					echo '<div class="notifyproblem" align="center">ERROR: Could not find or create a directory (' . $currdir . ')</div>' . "<br />\n";
				}
				return false;
			}
		
		//@chmod($currdir, $CFG->directorypermissions);  // Just in case mkdir didn't do it
		}
	}
	return $currdir;
}

/**
 * Generate and return a random string of the specified length.
 *
 * @param int $length The length of the string to be created.
 * @return string
 */
function random_string($length = 15) {
	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$pool .= 'abcdefghijklmnopqrstuvwxyz';
	$pool .= '0123456789';
	$poollen = strlen ( $pool );
	mt_srand ( ( double ) microtime () * 1000000 );
	$string = '';
	for($i = 0; $i < $length; $i ++) {
		$string .= substr ( $pool, (mt_rand () % ($poollen)), 1 );
	}
	return $string;
}

/**
 * make_unique_id_code
 *
 * @param string $extra ?
 * @return string
 * @todo Finish documenting this function
 */
function make_unique_id_code($extra = '') {
	
	$hostname = 'unknownhost';
	if (! empty ( $_SERVER ['HTTP_HOST'] )) {
		$hostname = $_SERVER ['HTTP_HOST'];
	} else if (! empty ( $_ENV ['HTTP_HOST'] )) {
		$hostname = $_ENV ['HTTP_HOST'];
	} else if (! empty ( $_SERVER ['SERVER_NAME'] )) {
		$hostname = $_SERVER ['SERVER_NAME'];
	} else if (! empty ( $_ENV ['SERVER_NAME'] )) {
		$hostname = $_ENV ['SERVER_NAME'];
	}
	
	$date = gmdate ( "ymdHis" );
	
	$random = random_string ( 6 );
	
	if ($extra) {
		return $hostname . '+' . $date . '+' . $random . '+' . $extra;
	} else {
		return $hostname . '+' . $date . '+' . $random;
	}
}

/*
 * Given some text (which may contain HTML) and an ideal length,
 * this function truncates the text neatly on a word boundary if possible
 * @param string $text - text to be shortened
 * @param int $ideal - ideal string length
 * @param boolean $exact if false, $text will not be cut mid-word
 * @return string $truncate - shortened string
 */

function shorten_text($text, $ideal = 30, $exact = false) {
	
	global $_configuration;
	$ending = '...';
	
	// if the plain text is shorter than the maximum length, return the whole text
	if (strlen ( preg_replace ( '/<.*?>/', '', $text ) ) <= $ideal) {
		return $text;
	}
	
	// splits all html-tags to scanable lines
	preg_match_all ( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
	
	$total_length = strlen ( $ending );
	$open_tags = array ();
	$truncate = '';
	
	foreach ( $lines as $line_matchings ) {
		// if there is any html-tag in this line, handle it and add it (uncounted) to the output
		if (! empty ( $line_matchings [1] )) {
			// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
			if (preg_match ( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings [1] )) {
				// do nothing
			// if tag is a closing tag (f.e. </b>)
			} else if (preg_match ( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings [1], $tag_matchings )) {
				// delete tag from $open_tags list
				$pos = array_search ( $tag_matchings [1], array_reverse ( $open_tags, true ) ); // can have multiple exact same open tags, close the last one
				if ($pos !== false) {
					unset ( $open_tags [$pos] );
				}
			
		// if tag is an opening tag (f.e. <b>)
			} else if (preg_match ( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings [1], $tag_matchings )) {
				// add tag to the beginning of $open_tags list
				array_unshift ( $open_tags, strtolower ( $tag_matchings [1] ) );
			}
			// add html-tag to $truncate'd text
			$truncate .= $line_matchings [1];
		}
		
		// calculate the length of the plain text part of the line; handle entities as one character
		$content_length = strlen ( preg_replace ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings [2] ) );
		if ($total_length + $content_length > $ideal) {
			// the number of characters which are left
			$left = $ideal - $total_length;
			$entities_length = 0;
			// search for html entities
			if (preg_match_all ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings [2], $entities, PREG_OFFSET_CAPTURE )) {
				// calculate the real length of all entities in the legal range
				foreach ( $entities [0] as $entity ) {
					if ($entity [1] + 1 - $entities_length <= $left) {
						$left --;
						$entities_length += strlen ( $entity [0] );
					} else {
						// no more characters left
						break;
					}
				}
			}
			$truncate .= substr ( $line_matchings [2], 0, $left + $entities_length );
			// maximum lenght is reached, so get off the loop
			break;
		} else {
			$truncate .= $line_matchings [2];
			$total_length += $content_length;
		}
		
		// if the maximum length is reached, get off the loop
		if ($total_length >= $ideal) {
			break;
		}
	}
	
	// if the words shouldn't be cut in the middle...
	if (! $exact) {
		// ...search the last occurance of a space...
		for($k = strlen ( $truncate ); $k > 0; $k --) {
			if (! empty ( $truncate [$k] ) && ($char = $truncate [$k])) {
				if ($char == '.' or $char == ' ') {
					$breakpos = $k + 1;
					break;
				} else if (ord ( $char ) >= 0xE0) { // Chinese/Japanese/Korean text
					$breakpos = $k; // can be truncated at any UTF-8
					break; // character boundary.
				}
			}
		}
		
		if (isset ( $breakpos )) {
			// ...and cut the text in this position
			$truncate = substr ( $truncate, 0, $breakpos );
		}
	}
	
	// add the defined ending to the text
	$truncate .= $ending;
	
	// close all unclosed html-tags
	foreach ( $open_tags as $tag ) {
		$truncate .= '</' . $tag . '>';
	}
	
	return $truncate;
}

function init_eaccelerator() {
	global $_configuration;
	global $MCACHE;
	
	include_once (api_get_path ( LIBRARY_PATH ) . 'cache/eaccelerator.class.php');
	$MCACHE = new eaccelerator ();
	if ($MCACHE->status ()) {
		return true;
	}
	unset ( $MCACHE );
	return false;
}

function import_assets($file, $file_path = "") {
	$html = "";
	if ($file) {
		$file_type = file_ext ( $file );
		if ($file_type == "js") {
			if (empty ( $file_path )) {
				$file_path = api_get_path ( WEB_JS_PATH );
			}
			$html = '<script type="text/javascript" src="' . $file_path . $file . '"></script>' . "\n";
		} elseif ($file_type == "css") {
			if (empty ( $file_path )) {
				$file_path = api_get_path ( WEB_CSS_PATH );
			}
			$html = '<link href="' . $file_path . $file . '" rel="stylesheet" type="text/css" media="screen" />' . "\n";
		}
	}
	return $html;
}

function api_add_url_querystring($url, $param, $filter_xss = false) {
	if (empty ( $param )) {
		return $url;
	}
	
	if (is_string ( $param )) {
		if ($param [0] == '&') {
			$param_str = substr ( $param, 1 );
		} else {
			$param_str = $param;
		}
	}
	
	if (is_array ( $param )) {
		foreach ( $param as $key => $val ) {
			if (isset ( $key ) && $key != '' && isset ( $val ) && $val != '') {
				$param_str .= '&' . trim ( $key ) . '=' . trim ( $val );
			}
		}
	}
	
	if (strstr ( $url, '?' )) {
		$url .= $param_str;
	} else {
		$url .= '?' . substr ( $param_str, 1 );
	}
	
	if ($filter_xss === true) {
		$url = Security::remove_XSS ( urldecode ( $url ) );
	}
	return $url;
}

function get_files_in_dir($path, $filter = array()) {
	$list = array ();
	$dh = opendir ( $path );
	if ($dh) {
		while ( $entry = readdir ( $dh ) ) {
			if (substr ( $entry, 0, 1 ) == '.') { /*ignore files starting with . */			} else {
				if ($filter && is_array ( $filter )) {
					foreach ( $filter as $filetype ) {
						$filetype_str .= "\." . $filetype . "|";
					}
					$filetype_str = substr ( $filetype_str, 0, - 1 );
					if ($filetype_str) {
						if (preg_match ( '/^.*[' . $filetype_str . ']$/i', $entry )) {
							$file = fopen ( $path . $entry, "r" );
							$file_info = fstat ( $file );
							$filesize = (round ( $file_info ['size'] / 1024 ) < 1024 ? round ( $file_info ['size'] / 1024, 1 ) . "KB" : round ( $file_info ['size'] / 1024 / 1024, 2 ) . "MB");
							$mtime = date ( 'Y-m-d H:i:s', $file_info ['mtime'] );
							$list [$entry] = $entry . str_repeat ( "&nbsp;", 8 ) . $filesize . str_repeat ( "&nbsp;", 6 ) . $mtime . "";
							fclose ( $file );
						}
					}
				} else { //所有文件
					$file = fopen ( $path . $entry, "r" );
					$file_info = fstat ( $file );
					$filesize = (round ( $file_info ['size'] / 1024 ) < 1024 ? round ( $file_info ['size'] / 1024, 1 ) . "KB" : round ( $file_info ['size'] / 1024 / 1024, 2 ) . "MB");
					$mtime = date ( 'Y-m-d H:i:s', $file_info ['mtime'] );
					$list [$entry] = $entry . str_repeat ( "&nbsp;", 8 ) . $filesize . str_repeat ( "&nbsp;", 6 ) . $mtime . "";
					fclose ( $file );
				}
			}
		}
		natcasesort ( $list );
		closedir ( $dh );
	}
	return $list;
}

/**
 * Checks to see if is a browser matches the specified
 * brand and is equal or better version.
 *
 * @uses $_SERVER
 * @param string $brand The browser identifier being tested
 * @param int $version The version of the browser
 * @return bool true if the given version is below that of the detected browser
 */
function check_browser_version($brand = 'MSIE', $version = 5.5) {
	if (empty ( $_SERVER ['HTTP_USER_AGENT'] )) {
		return false;
	}
	
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	
	switch ($brand) {
		
		case 'Camino' : /// Mozilla Firefox browsers
			

			if (preg_match ( "/Camino\/([0-9\.]+)/i", $agent, $match )) {
				if (version_compare ( $match [1], $version ) >= 0) {
					return true;
				}
			}
			break;
		
		case 'Firefox' : /// Mozilla Firefox browsers
			

			if (preg_match ( "/Firefox\/([0-9\.]+)/i", $agent, $match )) {
				if (version_compare ( $match [1], $version ) >= 0) {
					return true;
				}
			}
			break;
		
		case 'Gecko' : /// Gecko based browsers
			

			if (substr_count ( $agent, 'Camino' )) {
				// MacOS X Camino support
				$version = 20041110;
			}
			
			// the proper string - Gecko/CCYYMMDD Vendor/Version
			// Faster version and work-a-round No IDN problem.
			if (preg_match ( "/Gecko\/([0-9]+)/i", $agent, $match )) {
				if ($match [1] > $version) {
					return true;
				}
			}
			break;
		
		case 'MSIE' : /// Internet Explorer
			

			if (strpos ( $agent, 'Opera' )) { // Reject Opera
				return false;
			}
			$string = explode ( ';', $agent );
			if (! isset ( $string [1] )) {
				return false;
			}
			$string = explode ( ' ', trim ( $string [1] ) );
			if (! isset ( $string [0] ) and ! isset ( $string [1] )) {
				return false;
			}
			if ($string [0] == $brand and ( float ) $string [1] >= $version) {
				return true;
			}
			break;
		
		case 'Opera' : /// Opera
			

			if (preg_match ( "/Opera\/([0-9\.]+)/i", $agent, $match )) {
				if (version_compare ( $match [1], $version ) >= 0) {
					return true;
				}
			}
			break;
		
		case 'Safari' : /// Safari
			// Look for AppleWebKit, excluding strings with OmniWeb, Shiira and SimbianOS
			if (strpos ( $agent, 'OmniWeb' )) { // Reject OmniWeb
				return false;
			} elseif (strpos ( $agent, 'Shiira' )) { // Reject Shiira
				return false;
			} elseif (strpos ( $agent, 'SimbianOS' )) { // Reject SimbianOS
				return false;
			}
			
			if (preg_match ( "/AppleWebKit\/([0-9]+)/i", $agent, $match )) {
				if (version_compare ( $match [1], $version ) >= 0) {
					return true;
				}
			}
			
			break;
	
	}
	
	return false;
}

function get_files_in_ftp($path, $filter = array()) {
	$list = array ();
	if (substr ( $path, - 1 ) != '/') $path = $path . '/';
	$dh = opendir ( $path );
	if ($dh) {
		while ( $entry = readdir ( $dh ) ) {
			if (substr ( $entry, 0, 1 ) == '.') { /*ignore files starting with . */			} else {
				if ($filter && is_array ( $filter )) {
					foreach ( $filter as $filetype ) {
						$filetype_str .= "\." . $filetype . "|";
					}
					$filetype_str = substr ( $filetype_str, 0, - 1 );
					
					if ($filetype_str) {
						if (preg_match ( '/^.*[' . $filetype_str . ']$/i', $entry )) {
							$file = fopen ( $path . $entry, "r" ); //echo $path . $entry,'<br/>';
							$file_info = fstat ( $file );
							$filesize = (round ( $file_info ['size'] / 1024 ) < 1024 ? round ( $file_info ['size'] / 1024, 1 ) . "KB" : round ( $file_info ['size'] / 1024 / 1024, 2 ) . "MB");
							$mtime = date ( 'Y-m-d H:i:s', $file_info ['mtime'] );
							$list [$entry] = $entry . str_repeat ( "&nbsp;", 8 ) . $filesize . str_repeat ( "&nbsp;", 6 ) . $mtime . "";
							fclose ( $file );
						}
					}
				} else { //所有文件
					$file = fopen ( $path . $entry, "r" );
					$file_info = fstat ( $file );
					$filesize = (round ( $file_info ['size'] / 1024 ) < 1024 ? round ( $file_info ['size'] / 1024, 1 ) . "KB" : round ( $file_info ['size'] / 1024 / 1024, 2 ) . "MB");
					$mtime = date ( 'Y-m-d H:i:s', $file_info ['mtime'] );
					$list [$entry] = $entry . str_repeat ( "&nbsp;", 8 ) . $filesize . str_repeat ( "&nbsp;", 6 ) . $mtime . "";
					fclose ( $file );
				}
			}
		}
		natcasesort ( $list );
		closedir ( $dh );
	}
	return $list;
}

/**
 * 解压zip
 * @param unknown_type $src_file
 * @param unknown_type $dest_file
 */
function unzip_file($zip_file_path, $dest_path) {
	$info = pathinfo ( $zip_file_path );
	$filename = $info ['basename'];
	$extension = $info ['extension'];
	if (substr ( $dest_path, - 1 ) != '/') $dest_path = $dest_path . '/';
	if ('zip' == strtolower ( $extension )) {
		if (! file_exists ( $dest_path )) mkdir ( $dest_path, CHMOD_NORMAL );
		if (! file_exists ( $dest_path )) exit ( "目标解压路径不存在: " . $dest_path );
		$extract_ok = false;
		if (get_cfg_var ( 'safe_mode' ) == false) {
			if ((PHP_OS == 'Linux' || PHP_OS == 'Darwin')) {
				/* $cmd = "unzip -l " . $zip_file_path;
				if (DEBUG_MODE) api_error_log ( 'Linux system, using CMD=' . $cmd, __FILE__, __LINE__ );
				exec ( $cmd, $cmd_output_zipFileList ); */
				$cmd = "unzip -d \"" . $dest_path . "\" " . $zip_file_path;
				exec ( $cmd, $out_msg );
				if (DEBUG_MODE) api_error_log ( 'Linux system, using CMD=' . $cmd, ",Result=" . $out_msg, __FILE__, __LINE__ );
				$extract_ok = true;
			} elseif (PHP_OS == "WINNT" && is_dir ( BIN_PATH ) && file_exists ( BIN_PATH . 'unzip.exe' )) {
				/* $cmd = BIN_PATH . "unzip.exe -l " . $zip_file_path;
				if (DEBUG_MODE) api_error_log ( 'Windows system, using CMD=' . $cmd, __FILE__, __LINE__ );
				exec ( $cmd, $cmd_output_zipFileList ); */
				
				$cmd = BIN_PATH . "unzip.exe -d \"" . $dest_path . "\" " . $zip_file_path;
				exec ( $cmd, $out_msg );
				if (DEBUG_MODE) api_error_log ( 'Windows system, using CMD=' . $cmd, ",Result=" . $out_msg, __FILE__, __LINE__ );
				
				$extract_ok = true;
			} else {
				if (DEBUG_MODE) api_error_log ( ' Changing dir to ' . $dest_path, __FILE__, __LINE__ );
				$zipFile = new pclZip ( $zip_file_path );
				$saved_dir = getcwd ();
				chdir ( $dest_path );
				$unzippingState = $zipFile->extract ( PCLZIP_CB_PRE_EXTRACT, 'clean_up_files_in_zip' );
				if (! $unzippingState) {
					api_error_log ( "ERROR: Unzip zip file Failed", __FILE__, __LINE__ );
					exit ( "解压 zip 文件失败!" );
				}
				for($j = 0; $j < count ( $unzippingState ); $j ++) {
					$state = $unzippingState [$j];
					$extension = strrchr ( $state ["stored_filename"], "." );
					if (DEBUG_MODE) {
						api_error_log ( ' found extension ' . $extension . ' in ' . $state ['stored_filename'], __FILE__, __LINE__ );
					}
				}
				$extract_ok = true;
			}
		} else {
			api_error_log ( 'Extract zip file=' . $zip_file_path . " Failed! check php.ini setting", __FILE__, __LINE__ );
			exit ( 'Extract zip file=' . basename ( $zip_file_path ) . " Failed! check php.ini setting: safe_mode OR unzip command in path =" . BIN_PATH );
		}
		if ($extract_ok) {
			if ($dir = @opendir ( $dest_path )) {
				while ( $file = readdir ( $dir ) ) {
					if ($file != '.' && $file != '..') {
						$filetype = "file";
						if (is_dir ( $dest_path . $file )) $filetype = "folder";
						$safe_file = replace_dangerous_char ( $file, 'strict' );
						@rename ( $dest_path . $file, $dest_path . $safe_file );
					}
				}
				closedir ( $dir );
			}
		}
		return $extract_ok;
	}
	return FALSE;
}

function excelTime($days, $time = false) {
	if (is_numeric ( $days )) {
		//based on 1900-1-1
		$jd = GregorianToJD ( 1, 1, 1970 );
		$gregorian = JDToGregorian ( $jd + intval ( $days ) - 25569 );
		$myDate = explode ( '/', $gregorian );
		$myDateStr = str_pad ( $myDate [2], 4, '0', STR_PAD_LEFT ) . "-" . str_pad ( $myDate [0], 2, '0', STR_PAD_LEFT ) . "-" . str_pad ( $myDate [1], 2, '0', STR_PAD_LEFT ) . ($time ? " 00:00:00" : '');
		return $myDateStr;
	}
	return $days;
}

function sql_field_list($fields = array()) {
	if ($fields && is_array ( $fields )) {
		foreach ( $fields as $index => $field ) {
			$field_array [] = trim ( $field ) . ' AS col' . $index;
		}
		return implode ( ',', $field_array );
	}
	return '*';
}

function shuffle_assoc($list) {
	if (! is_array ( $list )) return $list;
	
	$keys = array_keys ( $list );
	shuffle ( $keys );
	$random = array ();
	foreach ( $keys as $key )
		$random [$key] = $list [$key];
	
	return $random;
}

function invisible_wrap($content, $invisible = TRUE) {
	return '<span ' . ($invisible ? ' class="invisible"' : "") . ">" . $content . '</span>';
}

function cache($name, $value = '') {
	global $_objCache;
	$is_obj = (! is_null ( $_objCache ) && isset ( $_objCache ) && is_object ( $_objCache ));
	if ('' === $value) {
		if ($is_obj) return $_objCache->get ( $name );
	} else {
		if (is_null ( $value )) {
			if ($is_obj) return $_objCache->remove ( $name );
		} else {
			if ($is_obj) return $_objCache->save ( $value, $name );
		}
	}
	return NULL;
}
