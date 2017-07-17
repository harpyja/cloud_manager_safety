<?php
$cwdir = getcwd ();
require_once ("../inc/global.inc.php");
require_once (api_get_path ( SYS_CODE_PATH ) . SCORM_PATH . 'lp_upload.php');
chdir ( $cwdir );
// $message = api_failure::get_last_failure ();
// if (empty ( $message )) $message = get_lang ( 'UplUploadSucceeded' );

//$url = api_get_path ( WEB_CODE_PATH ) . 'scorm/lp_controller.php?action=list&dialog_box=' . urlencode ( $message );
tb_close ();