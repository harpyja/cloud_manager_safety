<?php
$language_file = array ('course_home', 'scormdocument', 'scorm', 'learnpath', 'tracking', 'registration' );
require_once ("../../../main/inc/global.inc.php");
include_once ("../../../main/inc/conf/config_moodle.php");
include_once ("scorm.inc.php");
header ( "Content-Type: text/html;charset=".SYSTEM_CHARSET );

$id =  intval(getgpc ( "id" )); // Course Module ID, or
//$a = getgpc ( "a" ); // scorm ID
$scoid =  intval(getgpc ( "scoid" )); // sco ID
$newattempt = getgpc ( 'newattempt' ); // the user request to start a new attempt
if (empty ( $newattempt ))
	$newattempt = "off";
$currentorg = getgpc ( 'currentorg' );

//IE 6 Bug workaround
if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE 6' ) !== false && ini_get ( 'zlib.output_compression' ) == 'On') {
	ini_set ( 'zlib.output_compression', 'Off' );
}

if (! empty ( $id )) {
	$sql = "SELECT *  FROM " . $tbl_lp . " WHERE id='" . escape ( $id ) . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$scorm = Database::fetch_object ( $rs );
	if (! $scorm) {
		exit ( "SCORM is incorrect" );
	}
	
	$sql = "SELECT *  FROM " . $tbl_course . " WHERE code='" . $scorm->cc . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$coruse = Database::fetch_object ( $rs );
	if (! $coruse) {
		exit ( "Course is misconfigured" );
	}
} else {
	exit ( "A required parameter is missing" );
}
$scorm->maxattempt = SCORM_DEFAULT_MAX_ATTEMPT;
$scorm->auto=0;
$scorm->hidetoc = 0;
$scorm->hidenav = 0;

$scorm_dir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_id () . "/scorm/" . substr ( $scorm->path, 0, - 1 );

$pagetitle = strip_tags ( "$course->title: " . ($scorm->name) );
Display::display_header($pagetitle,FALSE);

require_once ('scorm_12lib.php');
$attempt = scorm_get_last_attempt ( $scorm->id, $user_id );
//再次尝试（$newattempt=="on"
if (($newattempt == 'on') && (($attempt < $scorm->maxattempt) || ($scorm->maxattempt == 0))) {
	$attempt ++;
	$mode = 'normal';
}
$attemptstr = '&amp;attempt=' . $attempt;

$result = scorm_get_toc ( $USER, $scorm, 'structurelist', $currentorg, $scoid, $mode, $attempt, true );
$sco = $result->sco;

if ($mode != 'browse') {
	if ($trackdata = scorm_get_tracks ( $sco->id, $USER->id, $attempt )) {
		if(in_array($trackdata->status,array('completed','passed','failed'))){
			$mode = 'review';
		} else {
			$mode = 'normal';
		}
	} else {
		$mode = 'normal';
	}
}

$scoidstr = '&amp;scoid=' . $sco->id;
$scoidpop = '&scoid=' . $sco->id;
$modestr = '&amp;mode=' . $mode;
if ($mode == 'browse') {
	$modepop = '&mode=' . $mode;
} else {
	$modepop = '';
}
$orgstr = '&currentorg=' . $currentorg;

Display::display_header(NULL,FALSE);
?>
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />

<script type="text/javascript" src="request.js"></script>
<script type="text/javascript"
	src="api.php?id=<?php
	echo $id . $scoidstr . $modestr . $attemptstr?>"></script>
<?php
//}
if (($sco->previd != 0) && ((! isset ( $sco->previous )) || ($sco->previous == 0))) {
	$scostr = '&scoid=' . $sco->previd;
	echo '    <script type="text/javascript">' . "\n//<![CDATA[\n" . 'var prev="' . WEB_SCORM_URL.'scorm_player.php?id=' . $cm->id . $orgstr . $modepop . $scostr . "\";\n//]]>\n</script>\n";
} else {
	echo '    <script type="text/javascript">' . "\n//<![CDATA[\n" . 'var prev="' . api_get_path ( WEB_SCORM_PATH ) . 'scorm/new/view.php?id=' . $cm->id . "\";\n//]]>\n</script>\n";
}
if (($sco->nextid != 0) && ((! isset ( $sco->next )) || ($sco->next == 0))) {
	$scostr = '&scoid=' . $sco->nextid;
	echo '    <script type="text/javascript">' . "\n//<![CDATA[\n" . 'var next="' . WEB_SCORM_URL.'scorm_player.php?id=' . $cm->id . $orgstr . $modepop . $scostr . "\";\n//]]>\n</script>\n";
} else {
	echo '    <script type="text/javascript">' . "\n//<![CDATA[\n" . 'var next="' . api_get_path ( WEB_SCORM_PATH ) . 'scorm/new/view.php?id=' . $cm->id . "\";\n//]]>\n</script>\n";
}
?>



<div id="scormpage"><?php
if ($scorm->hidetoc == 0) { //左侧显示TOC
	?>
<div id="tocbox"><?php
	if ($scorm->hidenav == 0) { //显示导航按钮（上一节，下一节）
		?> 
		<!-- Bottons nav at left-->
<div id="tochead">
<form name="tochead" method="post"
	action="scorm_player.php?id=<?php
		echo $id?>" target="_top"><?php
		$orgstr = '&amp;currentorg=' . $currentorg;
		
		//上一节
		if (($scorm->hidenav == 0) && ($sco->previd != 0) && (! isset ( $sco->previous ) || $sco->previous == 0)) {
			// Print the prev LO button
			$scostr = '&amp;scoid=' . $sco->previd;
			$url = WEB_SCORM_URL.'scorm_player.php?id=' . $id . $orgstr . $modestr . $scostr;
			?> <input name="prev" type="button"
	value="<?php
			print_string ( 'prev', 'scorm' )?>"
	onClick="document.location.href=' <?php
			echo $url;
			?> '" /> <?php
		}
		
		//下一节
		if (($scorm->hidenav == 0) && ($sco->nextid != 0) && (! isset ( $sco->next ) || $sco->next == 0)) {
			// Print the next LO button
			$scostr = '&amp;scoid=' . $sco->nextid;
			$url = WEB_SCORM_URL.'scorm_player.php?id=' . $id . $orgstr . $modestr . $scostr;
			?> <input name="next" type="button"
	value="<?php
			print_string ( 'next', 'scorm' )?>"
	onClick="document.location.href=' <?php
			echo $url;
			?> '" /> <?php
		}
		
		?></form>
</div>
<!-- tochead --> <?php
	} //END: 显示导航按钮（上一节，下一节）
	?>
	
<div id="toctree" class="generalbox"><?php
	echo $result->toc; //内容结构图
	?></div>
<!-- toctree --></div>

<!--  tocbox --> <?php
	$class = ' class="toc"';
} //END: 左侧TOC
else {
	$class = ' class="no-toc"';
}
?>



<div id="scormbox"
	<?php
	echo $class;
	if (($scorm->hidetoc == 2) || ($scorm->hidetoc == 1)) {
		echo 'style="width:100%"';
	}
	?>>
	<?php
	// This very big test check if is necessary the "scormtop" div
	if (($mode != 'normal') || // We are not in normal mode so review or browse text will displayed
(($scorm->hidenav == 0) && // Teacher want to display navigation links
($scorm->hidetoc != 0) && // The buttons has not been displayed
((($sco->previd != 0) && // This is not the first learning object of the package
((! isset ( $sco->previous )) || ($sco->previous == 0))) || // Moodle must manage the previous link
(($sco->nextid != 0) && // This is not the last learning object of the package
((! isset ( $sco->next )) || ($sco->next == 0)))))// Moodle must manage the next link
 || ($scorm->hidetoc == 2)) // Teacher want to display toc in a small dropdown menu
{
		?>
<div id="scormtop"><?php
		echo $mode == 'browse' ? '<div id="scormmode" class="scorm-left">' . get_string ( 'browsemode', 'scorm' ) . "</div>\n" : '';
		?>
		<?php
		echo $mode == 'review' ? '<div id="scormmode" class="scorm-left">' . get_string ( 'reviewmode', 'scorm' ) . "</div>\n" : '';
		?>
		<?php
		if (($scorm->hidenav == 0) || ($scorm->hidetoc == 2) || ($scorm->hidetoc == 1)) {
			?>
			
<div id="scormnav" class="scorm-right"><?php
			$orgstr = '&amp;currentorg=' . $currentorg;
			if (($scorm->hidenav == 0) && ($sco->previd != 0) && (! isset ( $sco->previous ) || $sco->previous == 0) && (($scorm->hidetoc == 2) || ($scorm->hidetoc == 1))) {
				
				// Print the prev LO button
				$scostr = '&amp;scoid=' . $sco->previd;
				$url = WEB_QH_PATH.'scorm/scorm_player.php?id=' . $cm->id . $orgstr . $modestr . $scostr;
				?>
<form name="scormnavprev" method="post"
	action="scorm_player.php?id=<?php
				echo $cm->id?>" target="_top"
	style="display: inline"><input name="prev" type="button"
	value="<?php
				print_string ( 'prev', 'scorm' )?>"
	onClick="document.location.href=' <?php
				echo $url;
				?> '" /></form>
	<?php
			}
			if ($scorm->hidetoc == 2) {
				echo $result->tocmenu;
			}
			if (($scorm->hidenav == 0) && ($sco->nextid != 0) && (! isset ( $sco->next ) || $sco->next == 0) && (($scorm->hidetoc == 2) || ($scorm->hidetoc == 1))) {
				// Print the next LO button
				$scostr = '&amp;scoid=' . $sco->nextid;
				$url = WEB_QH_PATH.'scorm/scorm_player.php?id=' . $cm->id . $orgstr . $modestr . $scostr;
				?>
<form name="scormnavnext" method="post"
	action="scorm_player.php?id=<?php
				echo $cm->id?>" target="_top"
	style="display: inline"><input name="next" type="button"
	value="<?php
				print_string ( 'next', 'scorm' )?>"
	onClick="document.location.href='<?php
				echo $url;
				?> '" /></form>
	<?php
			}
			?></div>
<?php
		}
		?></div>
<!-- Scormtop --> <?php
	} // The end of the very big test
	?>
	
	
<div id="scormobject" class="scorm-right">
<noscript>
<div id="noscript"><?php
print_string ( 'noscriptnoscorm', 'scorm' ); // No Martin(i), No Party ;-) ?>
</div>
</noscript>
	<?php
	if ($result->prerequisites) {
		if ($scorm->popup == 0) {
			//echo "<script type=\"text/javascript\">scorm_resize(" . $scorm->width . ", " . $scorm->height . ");</script>\n";
			$fullurl = WEB_QH_PATH."scorm/loadSCO.php?id=" . $id . $scoidstr . $modestr;
			echo "<iframe id=\"scoframe1\" class=\"scoframe\" name=\"scoframe1\" src=\"{$fullurl}\" width=\"99%\" height=\"500\"></iframe>\n";
		}
	} else {
		print_simple_box ( get_string ( 'noprerequisites', 'scorm' ), 'center' );
	}
	?></div>
<!-- SCORM object --></div>
<!-- SCORM box  --></div>
<!-- SCORM page -->
<?php
//print_footer ( 'none' );
Display::display_footer ();
?>
