<?php
require_once ("../../main/inc/global.inc.php");
require_once('scorm.inc.php');

$id =  intval(getgpc ( "id" )); // Course Module ID, or
$a = getgpc ( "a" ); // scorm ID
$scoid =  intval(getgpc ( "scoid" )); // sco ID
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

//require_login ( $course->id, false, $cm );


if ((! empty ( $scoid ))) {
	$result = true;
	$request = null;
	$submitted_data=data_submitted();
	foreach ( $submitted_data as $element => $value ) {
		$element = str_replace ( '__', '.', $element );
		if (substr ( $element, 0, 3 ) == 'cmi') {
			$netelement = preg_replace ( '/\.N(\d+)\./', "\.\$1\.", $element );
			$result = scorm_insert_track ( $USER->id, $scorm->id, $scoid, $attempt, $netelement, $value ) && $result;
		}
	}
	if ($result) {
		echo "true\n0";
	} else {
		echo "false\n101";
	}
}
?>