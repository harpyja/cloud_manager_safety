<?php
$language_file=array( 'course_home','scormdocument','scorm','learnpath','tracking','registration');
require_once ("../../main/inc/global.inc.php");
include_once ("../../main/inc/conf/config_moodle.php");
include_once ("scorm.inc.php");

$id =  intval(getgpc ( "id" )); // Course Module ID, or
$a = getgpc ( "a" ); // scorm ID
$scoid =  intval(getgpc ( "scoid" )); // sco ID


$delayseconds = 3; // Delay time before sco launch, used to give time to browser to define API


if (! empty ( $id )) {
	$sql = "SELECT *  FROM " . $tbl_lp . " WHERE id='" . escape ( $id ) . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$scorm = Database::fetch_object ( $rs );
	if (! $scorm) {
		exit ( "SCORM is incorrect" );
	}
	$scorm->maxattempt = 10;
	
	$sql = "SELECT *  FROM " . $tbl_course . " WHERE code='" . $scorm->cc . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$coruse = Database::fetch_object ( $rs );
	if (! $coruse) {
		exit ( "Course is misconfigured" );
	}
} else {
	exit ( "A required parameter is missing" );
}

if (! empty ( $scoid )) {
	if ($sco = scorm_get_sco ( $scoid )) {
		if ($sco->path == '') {
			// Search for the next launchable sco
			$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id='" . $scorm->id . "' AND path<>'' AND id>'" . $sco->id . "' ORDER BY id ASC";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			$scoes = Database::fetch_object ( $rs );
			if ($scoes) {
				$sco = current ( $scoes );
			}
		}
	}
}
//
// If no sco was found get the first of SCORM package
//
if (! isset ( $sco )) {
	$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id='" . $scorm->id . "' AND path<>'' ORDER BY id ASC";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$scoes = Database::fetch_object ( $rs );
	$sco = current ( $scoes );
}

if ($sco->item_type == 'asset') {
	$attempt = scorm_get_last_attempt ( $scorm->id, api_get_user_id());
	$element = 'cmi.core.lesson_status';
	$value = 'completed';
	$result = scorm_insert_track ( api_get_user_id(), $scorm->id, $sco->id, $attempt, $element, $value );
}

//
// Forge SCO URL
//
$connector = '';
if ((isset ( $sco->parameters ) && (! empty ( $sco->parameters ))) ) {
	if (stripos ( $sco->path, '?' ) !== false) {
		$connector = '&';
	} else {
		$connector = '?';
	}
	if ((isset ( $sco->parameters ) && (! empty ( $sco->parameters ))) && ($sco->parameters [0] == '?')) {
		$sco->parameters = substr ( $sco->parameters, 1 );
	}
}

if (isset ( $sco->parameters ) && (! empty ( $sco->parameters ))) {
	$launcher = $sco->path . $connector . $sco->parameters;
} else {
	$launcher = $sco->path;
}

$rel_path="/scorm/" . substr ( $scorm->path, 0, - 1 );
$basedir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_id () .$rel_path;
//$result = api_get_path ( WEB_PATH ) . 'file.php/' . $scorm->cc .  $rel_path  . $launcher;
$result = api_get_path ( WEB_PATH ) . 'storage/courses/' .( $scorm->cc .  $rel_path  . $launcher);
//$result='http://demo.zlms.org/demo/index_lms.html';

$scormpixdir =api_get_path ( WEB_CODE_PATH ).'scorm2/pix';

// which API are we looking for
$LMS_api = 'API';
header ( "Content-Type: text/html;charset=".SYSTEM_CHARSET );
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=SYSTEM_CHARSET ?>" />
<script type="text/javascript">
        //<![CDATA[
        var apiHandle = null;
        var findAPITries = 0;

        function getAPIHandle() {
           if (apiHandle == null) {
              apiHandle = getAPI();
           }
           return apiHandle;
        }

        function findAPI(win) {
           while ((win.API == null) && (win.parent != null) && (win.parent != win)) {
              findAPITries++;
              // Note: 7 is an arbitrary number, but should be more than sufficient
              if (findAPITries > 7) {
                 return null;
              }
              win = win.parent;
           }
           return win.API;
        }

        // hun for the API - needs to be loaded before we can launch the package
        function getAPI() {
           var theAPI = findAPI(window);
           if ((theAPI == null) && (window.opener != null) && (typeof(window.opener) != "undefined")) {
              theAPI = findAPI(window.opener);
           }
           if (theAPI == null) {
              return null;
           }
           return theAPI;
        }

        function doredirect() {
            if (getAPI() != null) {
                location = "<?=$result?>";
            }
            else {
                document.body.innerHTML = "<p><?=get_string ( 'activityloading', 'scorm' );?>
                     <span id='countdown'><?=$delayseconds?></span> <?php
																					echo get_string ( 'numseconds' );
																					?>. &nbsp; <img src='<?php
																					echo $scormpixdir;
																					?>/wait.gif'><p>";
                var e = document.getElementById("countdown");
                var cSeconds = parseInt(e.innerHTML);
                var timer = setInterval(function() {
                                                if( cSeconds && getAPI() == null ) {
                                                    e.innerHTML = --cSeconds;
                                                } else {
                                                    clearInterval(timer);
                                                    document.body.innerHTML = "<p><?php
																																																				echo get_string ( 'activitypleasewait', 'scorm' );
																																																				?></p>";
                                                    location = "<?php
																																																				echo $result?>";
                                                }
                                            }, 1000);
            }
        }
        //]]>
        </script>
<noscript>
<meta http-equiv="refresh" content="0;url=<?php
echo $result?>" />
</noscript>
</head>
<body onload="doredirect();">
<p><?php
echo get_string ( 'activitypleasewait', 'scorm' );
?></p>

</body>
</html>