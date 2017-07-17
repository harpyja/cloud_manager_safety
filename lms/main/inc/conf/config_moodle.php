<?php
//Moodle Settings
unset($CFG);
$CFG = new stdClass();
$CFG->dbtype    = 'mysql';
$CFG->dbhost    = $_configuration['db_host'];
$CFG->dbname    = $_configuration['main_database'];
$CFG->dbuser    = $_configuration['db_user'];
$CFG->dbpass    = $_configuration['db_password'];
$CFG->dbpersist =  false;
$CFG->prefix    = '';

$CFG->wwwroot   = $_configuration['root_web'];
$CFG->dirroot   = $_configuration['root_sys'];
$CFG->dataroot  = SERVER_DATA_DIR;
$CFG->libdir	= $CFG->dirroot."lib/";
$CFG->admin     = 'admin'; //密码:Admin_888

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode

$CFG->passwordsaltmain = 'CDF+882t{IXlvXNl9)(6n <5iz)t';