<?php
//require_once ('main/inc/conf/config.php');
require_once ('main/inc/global.inc.php');
require_once(SYS_ROOT.'main/inc/lib/commons/seccode.class.php');

function generate_verify_code(){
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	
	global $_configuration;
	$authkey = md5($_configuration['security_key'].$_SERVER['HTTP_USER_AGENT'].get_onlineip());
	$seccodeauth = getgpc('seccodeauth');
	//$seccode=rand()."".time();
	$seccode = authcode($seccodeauth, 'DECODE', $authkey);
	$code = new seccode();
	$code->code = $seccode;
	$code->type = 0;
	$code->width = 70;
	$code->height = 21;
	$code->background = 0;
	$code->adulterate = 1;
	$code->ttf = 1;
	$code->angle = 0;
	$code->color = 1;
	$code->size = 0;
	$code->shadow = 1;
	$code->animator = 0;
	$code->fontpath = SYS_ROOT.'res/images/fonts/';
	$code->datapath = SYS_ROOT.'res/images/';
	$code->includepath = '';
	$code->display();
	
	/*if(!isset($_SESSION)) session_start();
	seccode::seccodeconvert($seccode);
	$_SESSION['seccode']=$seccode;*/
	//echo $_SESSION['seccode'];
}

if(isset($_GET['m']) && $_GET['m']=='seccode'){
	generate_verify_code();
}
?>