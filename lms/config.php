<?php
//define('SERVER_ROOT_PATH', str_replace('htdocs', '',str_replace('\\', '/', $_SERVER["DOCUMENT_ROOT"])));
define ( 'APP_ROOT_PATH', str_replace ( '\\', '/', $_SERVER ['DOCUMENT_ROOT'] ) );
define ( 'SERVER_DATA_DIR', str_replace ( '\\', '/', dirname ( __FILE__ ) ) . '/storage/DATA/' );
include_once (SERVER_DATA_DIR . "config.inc.php");
define ( 'URL_APPEND', URL_APPEDND . '/' );
define ( 'BIN_PATH', dirname ( APP_ROOT_PATH ) . '/sbin/' ); //路径相关


define ( 'WEB_ROOT', 'http://' . $_SERVER ['HTTP_HOST'] . URL_APPEND );
define ( 'SYS_ROOT', APP_ROOT_PATH . URL_APPEND );
define ( 'SERVER_ERR_LOG_DIR', SERVER_DATA_DIR . 'logs/' );
define ( 'SERVER_CACHE_DIR', SERVER_DATA_DIR . 'temp/' );
define ( 'SYS_GARBAGE_DIR', SERVER_DATA_DIR . 'garbage/' ); // 垃圾箱文件夹，应该在Web不能访问的路径
define ( 'LOCAL_FTP_DIR', SYS_ROOT . 'storage/ftp/' );

define ( 'DEBUG_MODE', FALSE );
define ( 'TEST_MODE', FALSE );
define ( 'VERSION', '2.4.3.385' ); //版本号
define ( 'PORTAL_LAYOUT', 'portal/sp/' );
