<?php
define('ROOT_PATH', str_replace('main/inc/lib/commons/application.inc.php', '',
str_replace('\\', '/', __FILE__)));

$includePath = dirname(__FILE__);

include_once("config.php");
include_once($includePath."/ezsql/ez_sql_core.php");
include_once($includePath."/ezsql/ez_sql_mysql.php");
//include_once($includePath."/ezsql/ez_sql_postgresql.php");
//include_once($includePath."/ezsql/ez_sql_mssql.php");
//include_once($includePath."/ezsql/ez_sql_oracle8_9.php");

/*$_db_pgsql = new ezSQL_postgresql($_config['db_pgsql_username'],
			$_config['db_pgsql_pwd'],$_config['db_pgsql_name'],
			$_config['db_pgsql_host'],$_config['db_pgsql_port']);*/
			
$_db_mysql= new ezSQL_mysql($_config['db_mysql_username'],
			$_config['db_mysql_pwd'],$_config['db_mysql_name'],
			$_config['db_mysql_host'],$_config['db_mysql_port']);
$_db_mysql->query("SET NAMES utf8");
?>