<?php

require_once ("../../main/inc/global.inc.php");
require_once('scorm.inc.php');

$id =  intval(getgpc ( "id" )); // Course Module ID, or
$a = getgpc ( "a" ); // scorm ID
$scoid = intval(getgpc ( "scoid" )); // sco ID
$attempt = getgpc ( 'attempt' ); // the user request to start a new attempt
$mode=getgpc("mode");

//IE 6 Bug workaround
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false && ini_get('zlib.output_compression') == 'On') {
	ini_set('zlib.output_compression', 'Off');
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
$scorm->maxattempt = 10;
$scorm->auto=0;

// require_login($course->id, false, $cm);

if ($usertrack = scorm_get_tracks($scoid,$user_id,$attempt)) {
	if ((isset($usertrack->{'cmi.exit'}) && ($usertrack->{'cmi.exit'} != 'time-out')) || ($scorm->version != "SCORM_1.3")) {
		foreach ($usertrack as $key => $value) {
			$userdata->$key = addslashes_js($value);
		}
	} else {
		$userdata->status = '';
		$userdata->score_raw = '';
	}
} else {
	$userdata->status = '';
	$userdata->score_raw = '';
}
$userdata->student_id = addslashes_js($USER->username);
$userdata->student_name = addslashes_js($USER->lastname .', '. $USER->firstname);
$userdata->mode = 'normal';
if (isset($mode)) {
	$userdata->mode = $mode;
}
if ($userdata->mode == 'normal') {
	$userdata->credit = 'credit';
} else {
	$userdata->credit = 'no-credit';
}
if ($scodatas = scorm_get_sco($scoid, SCO_DATA)) {
	foreach ($scodatas as $key => $value) {
		$userdata->$key = addslashes_js($value);
	}
} else {
	error('Sco not found');
}
if (!$sco = scorm_get_sco($scoid)) {
	error('Sco not found');
}

include_once('scorm_12.js.php');

// set the start time of this SCO
scorm_insert_track($user_id,$scorm->id,$scoid,$attempt,'x.start.time',time());
?>

var errorCode = "0"; function underscore(str) { str =
String(str).replace(/.N/g,"."); return str.replace(/\./g,"__"); }
