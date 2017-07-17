<?php
$language_file = 'admin';
include ("../inc/global.inc.php");
api_protect_course_script();
$courseFolderName = getgpc('cidReq','G');

if (! file_exists ( api_get_path ( SYS_EXTENSIONS_PATH ) . "zlmeet/" ) or !api_get_setting ( 'online_meeting_server' )) {
	Display::display_message_header ();
	Display::display_warning_message ( get_lang ( "VideoConfNotPropertyInstall" ) );
	exit ();
}

//echo $courseFolderName;
if (isset ( $courseFolderName ) && ! empty ( $courseFolderName )) {
	$url = api_get_path ( WEB_COURSE_PATH ) . $courseFolderName . "/zlmeet/zlmeet.php?cf=" . $courseFolderName;
	api_redirect($url);
	?>
<script type="text/javascript">

/*var fethure="toolbar=no,location=no,directories=no,status=yes,menubar=yes,scrollbars=no,resizable=yes,top=0,left=0";
var s=window.open("<?=$url?>","",fethure);*/

</script>
<?php
} else {
	exit ( "NO COURSE FOLDER NAME ARE SET!" );
}
?>