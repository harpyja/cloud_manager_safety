<?php
$language_file = array ('index');
require_once ('../../main/inc/global.inc.php');
api_block_anonymous_users();
$my_code_path = api_get_path(WEB_PATH);
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="1800"> 

<style type="text/css" media="screen, projection">
/*<![CDATA[*/
<?='@import "'.$my_code_path.'themes/default/default.css";'."\n" ?>
/*]]>*/
</style>
<script type="text/javascript" src="<?=api_get_path(WEB_JS_PATH)?>commons.js"></script>
<script type="text/javascript" src="<?=api_get_path(WEB_JS_PATH)?>jquery-latest.js"></script>
<script type="text/javascript">
	function show_online_user_list(){
		this.location.reload();
		window.parent.lefttop.show_online_user_list();
	}
	
	function show_online_course_user_list(){
		this.location.reload();
		window.parent.lefttop.show_online_course_user_list();
	}

	function set_debug_time(time){
		if(document.getElementById('debug_time'))
		document.getElementById('debug_time').innerHTML ="<?=get_lang("ExcutionTime")?>: "+time+" s";
	}
</script>
</head>
<body topmargin="0">


<div id="footer">

<div class="copyright"> 
	<?php //echo get_lang("CopyRight")."&copy;".get_lang('FrameRightDownCopyright') ."&nbsp;V ".  VERSION ."&copy;". date('Y'); 		?>
</div>

<div class="copyright"><span id="Version"><?=get_lang ( "FrameRightDownCopyright" ) ."&nbsp;V ".  VERSION ."&copy;". date('Y')?></span>
</div>

<div id="debug_time" style="float: right; margin-right: 20px;"></div>

<?php


	$userName = $_SESSION ['_user'] ['firstName'] . " " . $_SESSION ['_user'] ['lastName'];
	$userNo = $_SESSION ['_user'] ['username'];
	if (! is_not_blank ( $userNo )) {
		$user_info = api_get_user_info ( api_get_user_id () );
		$userNo = $user_info ['username'];
		$userName = $user_info ['firstName'] . " " . $user_info ['lastName'];
	}
	
	echo '<div style="display:inline;margin-right:20px;">';
	echo $userName, "(", $userNo, ")";
	echo '</div>';


	echo '<div style="display:inline;margin-right:20px;">';
	echo get_lang ( 'TodayIs' ), ":&nbsp;", date ( get_lang ( 'ChineseTimeFormatShort' ) );
	echo "&nbsp;(", get_week_name ( date ( 'w' ) ), ",", date ( get_lang ( 'WeekOfYear' ) ) . ")";
	echo '</div>';



?>&nbsp;</div>

</body>
</html>
