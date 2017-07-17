<?php
//error_reporting(E_ALL);
define ( 'SCO_ALL', 0 );
define ( 'SCO_DATA', 1 );
define ( 'SCO_ONLY', 2 );
define ( "SCORM_DEFAULT_MAX_ATTEMPT", 100 );
if (! defined ( "WEB_QH_PATH" )) {
	define ( "WEB_QH_PATH", api_get_path ( WEB_PATH ) . "portal/sp/" );
}
define ( "WEB_SCORM_URL", api_get_path ( WEB_CODE_PATH ) . "scorm2/" );

//$tbl_crs_scorm = Database::get_course_table ( TABLE_SCORM );
//$tbl_crs_scorm_scoes = Database::get_course_table ( TABLE_SCORM_SCOES );
//$tbl_crs_scorm_scoes_data = Database::get_course_table ( TABLE_SCORM_SCOES_DATA );
$tbl_crs_scorm_scoes_track = Database::get_course_table ( TABLE_SCORM_SCOES_TRACK );

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
$tbl_lp_item_view = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
$tbl_lp_view = Database::get_course_table ( TABLE_LP_VIEW );

$tbl_user = Database::get_main_table ( VIEW_USER );
$sql = "SELECT *,user_id AS id FROM " . $tbl_user . " WHERE user_id='" . api_get_user_id () . "'";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
$USER = Database::fetch_object ( $rs );
$user_id = $USER->id;
unset ( $rs );

function scorm_add_instance($scorm) {
	scorm_parse ( $scorm );
}

function scorm_parse($scorm) {
	
	$course_code = api_get_course_id ();
	//$referencedir = SYS_ROOT . '/' . $scorm->course . '/moddata/scorm/' . $scorm->id;
	$referencedir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_id () . "/scorm/" . substr ( $scorm->path, 0, - 1 );
	
	require_once ('scormlib.php');
	$scorm->launch = scorm_parse_scorm ( $referencedir, $scorm->id );
	
	return $scorm->launch;
}

function print_string($identifier, $module = '', $a = NULL) {
	echo get_string ( $identifier, $module, $a );
}

function get_string($identifier, $module = '', $a = NULL, $extralocations = NULL) {
	return get_lang ( $identifier );
}

function scorm_get_last_attempt($scormid, $userid) {
	/// Find the last attempt number for the given user id and scorm id
	global $tbl_crs_scorm, $tbl_crs_scorm_scoes, $tbl_crs_scorm_scoes_data, $tbl_crs_scorm_scoes_track;
	$sql = "SELECT max(attempt) as a FROM $tbl_crs_scorm_scoes_track WHERE userid='" . escape ( $userid ) . "' AND scormid='" . escape ( $scormid ) . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$lastattempt = Database::fetch_object ( $rs );
	if ($lastattempt) {
		if (empty ( $lastattempt->a )) {
			return '1';
		} else {
			return $lastattempt->a;
		}
	}
}

/**
 * Returns an object (array) containing all the scoes data related to the given sco ID
 *
 * @param integer $id The sco ID
 * @param integer $organisation an organisation ID - defaults to false if not required
 * @return mixed (false if there are no scoes or an array)
 */
function scorm_get_scoes($id, $organisation = false) {
	global $tbl_crs_scorm, $tbl_crs_scorm_scoes, $tbl_crs_scorm_scoes_data, $tbl_crs_scorm_scoes_track;
	global $tbl_lp_item;
	
	$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id='" . $id . "' AND cc='" . api_get_course_code () . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_object ( $rs ) ) {
		$scoes [] = $row;
	}
	if ($scoes) {
		
		//		foreach ( $scoes as $sco ) {
		//			if ($scodatas = get_records ( $tbl_crs_scorm_scoes_data, 'scoid', $sco["id"] )) {
		//				foreach ( $scodatas as $scodata ) {
		//					$sco->{$scodata->name} = stripslashes_safe ( $scodata->value );
		//				}
		//			}
		//		}
		return $scoes;
	} else {
		return false;
	}
}

/**
 * Returns an object containing all datas relative to the given sco ID
 *
 * @param integer $id The sco ID
 * @return mixed (false if sco id does not exists)
 */
function scorm_get_sco($id, $what = SCO_ALL) {
	global $tbl_crs_scorm, $tbl_crs_scorm_scoes, $tbl_crs_scorm_scoes_data, $tbl_crs_scorm_scoes_track;
	global $tbl_lp_item;
	$sql = "SELECT * FROM $tbl_lp_item WHERE id='" . escape ( $id ) . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$sco = Database::fetch_object ( $rs );
	if ($sco) {
		return $sco;
	} else {
		return false;
	}
}

/**
 * 插入或更新跟踪信息
 * @param unknown_type $userid
 * @param unknown_type $scormid
 * @param unknown_type $scoid
 * @param unknown_type $attempt
 * @param unknown_type $element
 * @param unknown_type $value
 */
function scorm_insert_track($userid, $scormid, $scoid, $attempt, $element, $value) {
	global $tbl_crs_scorm_scoes_track;
	$course_code = api_get_course_code ();
	$id = null;
	
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes_track WHERE userid='$userid' AND scormid='$scormid' AND scoid='$scoid' AND attempt='$attempt' AND element='$element' AND cc='" . $course_code . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$track = Database::fetch_object ( $rs );
	if ($track) {
		if ($element != 'x.start.time') { //don't update x.start.time - keep the original value.
			$track->value = addslashes_js ( $value );
			$track->timemodified = time ();
			$id = update_record ( $tbl_crs_scorm_scoes_track, $track );
		}
	} else {
		$track->userid = $userid;
		$track->scormid = $scormid;
		$track->scoid = $scoid;
		$track->attempt = $attempt;
		$track->element = $element;
		$track->value = addslashes_js ( $value );
		$track->timemodified = time ();
		$track->cc = $course_code;
		
		if ($userid && $scormid && $scoid) {
			//$id = insert_record ( $tbl_crs_scorm_scoes_track, $track );
			$sql_data = array ('userid' => $userid, 'scormid' => $scormid, 'scoid' => $scoid, 'attempt' => $attempt, 'element' => $element, 'value' => addslashes_js ( $value ), 'timemodified' => time (), 'cc' => $course_code );
			$sql = Database::sql_insert ( $tbl_crs_scorm_scoes_track, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$id = Database::get_last_insert_id ();
		}
	}
	lms_update_track ( $course_code, $userid, $scormid, $scoid, $attempt );
	
	return $id;
}

function scorm_get_tracks($scoid, $userid, $attempt = '') {
	global $tbl_crs_scorm, $tbl_crs_scorm_scoes, $tbl_crs_scorm_scoes_data, $tbl_crs_scorm_scoes_track;
	global $CFG;
	
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes_track WHERE userid='" . escape ( $userid ) . "' AND scoid='" . escape ( $scoid ) . "' " . $attemptsql . " ORDER BY element ASC";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_object ( $rs ) ) {
		$tracks [] = $row;
	}
	
	if ($tracks) {
		$usertrack->userid = $userid;
		$usertrack->scoid = $scoid;
		// Defined in order to unify scorm1.2 and scorm2004
		$usertrack->score_raw = '';
		$usertrack->status = '';
		$usertrack->total_time = '00:00:00';
		$usertrack->session_time = '00:00:00';
		$usertrack->timemodified = 0;
		foreach ( $tracks as $track ) {
			$element = $track->element;
			$track->value = stripslashes_safe ( $track->value );
			$usertrack->{$element} = $track->value;
			switch ($element) {
				case 'cmi.core.lesson_status' :
				case 'cmi.completion_status' :
					if ($track->value == 'not attempted') {
						$track->value = 'notattempted';
					}
					$usertrack->status = $track->value;
					break;
				case 'cmi.core.score.raw' :
				case 'cmi.score.raw' :
					$usertrack->score_raw = ( float ) sprintf ( '%2.2f', $track->value );
					break;
				case 'cmi.core.session_time' :
				case 'cmi.session_time' :
					$usertrack->session_time = $track->value;
					break;
				case 'cmi.core.total_time' :
				case 'cmi.total_time' :
					$usertrack->total_time = $track->value;
					break;
			}
			if (isset ( $track->timemodified ) && ($track->timemodified > $usertrack->timemodified)) {
				$usertrack->timemodified = $track->timemodified;
			}
		}
		if (is_array ( $usertrack )) {
			ksort ( $usertrack );
		}
		return $usertrack;
	} else {
		return false;
	}
}

function scorm_count_launchable($scormid, $organization = '') {

}

function scorm_repeater($what, $times) {
	if ($times <= 0) {
		return null;
	}
	$return = '';
	for($i = 0; $i < $times; $i ++) {
		$return .= $what;
	}
	return $return;
}

function scorm_array_search($item, $needle, $haystacks, $strict = false) {
	if (! empty ( $haystacks )) {
		foreach ( $haystacks as $key => $element ) {
			if ($strict) {
				if ($element->{$item} === $needle) {
					return $key;
				}
			} else {
				if ($element->{$item} == $needle) {
					return $key;
				}
			}
		}
	}
	return false;
}

/**
 * Build up the JavaScript representation of an array element
 *
 * @param string $sversion SCORM API version
 * @param array $userdata User track data
 * @param string $element_name Name of array element to get values for
 * @param array $children list of sub elements of this array element that also need instantiating
 * @return None
 */
function scorm_reconstitute_array_element($sversion, $userdata, $element_name, $children) {
	// reconstitute comments_from_learner and comments_from_lms
	$current = '';
	$current_subelement = '';
	$current_sub = '';
	$count = 0;
	$count_sub = 0;
	$scormseperator = '_';
	if ($sversion == 'scorm_13') { //scorm 1.3 elements use a . instead of an _
		$scormseperator = '.';
	}
	// filter out the ones we want
	$element_list = array ();
	foreach ( $userdata as $element => $value ) {
		if (substr ( $element, 0, strlen ( $element_name ) ) == $element_name) {
			$element_list [$element] = $value;
		}
	}
	
	// sort elements in .n array order
	uksort ( $element_list, "scorm_element_cmp" );
	
	// generate JavaScript
	foreach ( $element_list as $element => $value ) {
		if ($sversion == 'scorm_13') {
			$element = preg_replace ( '/\.(\d+)\./', ".N\$1.", $element );
			preg_match ( '/\.(N\d+)\./', $element, $matches );
		} else {
			$element = preg_replace ( '/\.(\d+)\./', "_\$1.", $element );
			preg_match ( '/\_(\d+)\./', $element, $matches );
		}
		if (count ( $matches ) > 0 && $current != $matches [1]) {
			if ($count_sub > 0) {
				echo '    ' . $element_name . $scormseperator . $current . '.' . $current_subelement . '._count = ' . $count_sub . ";\n";
			}
			$current = $matches [1];
			$count ++;
			$current_subelement = '';
			$current_sub = '';
			$count_sub = 0;
			$end = strpos ( $element, $matches [1] ) + strlen ( $matches [1] );
			$subelement = substr ( $element, 0, $end );
			echo '    ' . $subelement . " = new Object();\n";
			// now add the children
			foreach ( $children as $child ) {
				echo '    ' . $subelement . "." . $child . " = new Object();\n";
				echo '    ' . $subelement . "." . $child . "._children = " . $child . "_children;\n";
			}
		}
		
		// now - flesh out the second level elements if there are any
		if ($sversion == 'scorm_13') {
			$element = preg_replace ( '/(.*?\.N\d+\..*?)\.(\d+)\./', "\$1.N\$2.", $element );
			preg_match ( '/.*?\.N\d+\.(.*?)\.(N\d+)\./', $element, $matches );
		} else {
			$element = preg_replace ( '/(.*?\_\d+\..*?)\.(\d+)\./', "\$1_\$2.", $element );
			preg_match ( '/.*?\_\d+\.(.*?)\_(\d+)\./', $element, $matches );
		}
		
		// check the sub element type
		if (count ( $matches ) > 0 && $current_subelement != $matches [1]) {
			if ($count_sub > 0) {
				echo '    ' . $element_name . $scormseperator . $current . '.' . $current_subelement . '._count = ' . $count_sub . ";\n";
			}
			$current_subelement = $matches [1];
			$current_sub = '';
			$count_sub = 0;
			$end = strpos ( $element, $matches [1] ) + strlen ( $matches [1] );
			$subelement = substr ( $element, 0, $end );
			echo '    ' . $subelement . " = new Object();\n";
		}
		
		// now check the subelement subscript
		if (count ( $matches ) > 0 && $current_sub != $matches [2]) {
			$current_sub = $matches [2];
			$count_sub ++;
			$end = strrpos ( $element, $matches [2] ) + strlen ( $matches [2] );
			$subelement = substr ( $element, 0, $end );
			echo '    ' . $subelement . " = new Object();\n";
		}
		
		echo '    ' . $element . ' = \'' . $value . "';\n";
	}
	if ($count_sub > 0) {
		echo '    ' . $element_name . $scormseperator . $current . '.' . $current_subelement . '._count = ' . $count_sub . ";\n";
	}
	if ($count > 0) {
		echo '    ' . $element_name . '._count = ' . $count . ";\n";
	}
}

function lms_update_track($course_code, $userid, $scormid, $scoid, $attempt = 1) {
	global $tbl_crs_scorm_scoes_track;
	global $tbl_lp, $tbl_lp_item, $tbl_lp_view, $tbl_lp_item_view;
	$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
	$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
	require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
	$objStat = new ScormTrackStat ();
	
	//TODO: 对单SCO的有用
	$sql = "SELECT id FROM $tbl_lp_view WHERE cc='" . escape ( $course_code ) . "' AND lp_id='" . escape ( $scormid ) . "' AND user_id='" . escape ( $userid ) . "' AND view_count='" . escape ( $attempt ) . "'";
	$lp_view_id = Database::get_scalar_value ( $sql );
	
	//如果进度为100%(完成)时,不需要更新任何状态
	/*$sql = "SELECT status FROM " . $tbl_lp_item_view . " WHERE lp_view_id ='" . $lp_view_id . "' ORDER BY start_time DESC";
	$learning_status = Database::get_scalar_value ( $sql );
	if (in_array ( $learning_status, array ('completed', 'passed', 'succeeded', 'failed' ) )) {
		return;
	}*/
	
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes_track WHERE userid='" . escape ( $userid ) . "' AND scormid='" . escape ( $scormid ) . "' AND scoid='" . escape ( $scoid ) . "' AND attempt='" . escape ( $attempt ) . "' AND cc='" . escape ( $course_code ) . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$sql_data = array ();
	$is_finished = false;
	while ( $track = Database::fetch_array ( $rs ) ) {
		if ($track ["value"]) {
			switch ($track ["element"]) {
				case 'x.start.time' :
					$sql_data ["start_time"] = $track ["value"];
					break;
				case 'cmi.core.lesson_status' :
					$sql_data ["status"] = $track ["value"];
					if (in_array ( $track ["value"], array ('completed', 'passed', 'succeeded', 'failed' ) )) {
						$is_finished = true;
					}
					break;
				case 'cmi.core.exit' :
					$sql_data ["core_exit"] = $track ["value"];
					break;
				case 'cmi.suspend_data' :
					//case 'cmi.core.suspend_data':
					$sql_data ["suspend_data"] = $track ["value"];
					break;
				case 'cmi.core.total_time' :
					$total_time = $track ["value"];
					$sql_data ["total_time"] = _convert_scorm_to_sec ( $total_time );
					break;
				case 'cmi.core.lesson_location' :
					$sql_data ["lesson_location"] = $track ["value"];
					break;
				case 'cmi.core.score.raw' :
					$sql_data ["score"] = $track ["value"];
					break;
			}
		}
	}
	
	//更新SCORM数据
	if ($sql_data && is_array ( $sql_data ) && count ( $sql_data ) > 0) {
		$sql_where = " cc='" . escape ( $course_code ) . "' AND lp_item_id='" . escape ( $scoid ) . "' AND lp_view_id='" . $lp_view_id . "' AND view_count='" . escape ( $attempt ) . "'";
		$sql = Database::sql_update ( $tbl_lp_item_view, $sql_data, $sql_where );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//更新课件总体状态: 更新track_e_cw表学习时间
		$sql = "SELECT id FROM $tbl_courseware WHERE cc='" . escape ( $course_code ) . "' AND cw_type='scorm' AND attribute=" . Database::escape ( $scormid );
		$cw_id = Database::get_scalar_value ( $sql );
		$totaltime = $objStat->get_scorm_learning_time ( $userid, $course_code, $scormid );
		evnet_courseware ( $course_code, $userid, $cw_id, $totaltime, 'update' );
	}
	
	$progress = ($is_finished ? 100 : $objStat->get_learning_progress ( $userid, $course_code, $scormid ));
	if ($progress > 100) $progress = 100;
	
	//更新crs_lp_view
	$sql_where = " cc='" . escape ( $course_code ) . "' AND lp_id='" . escape ( $scormid ) . "' AND user_id='" . escape ( $userid ) . "' AND view_count='" . escape ( $attempt ) . "'";
	$sql_data = array ("last_item" => $scoid, "progress" => $progress );
	$sql = Database::sql_update ( $tbl_lp_view, $sql_data, $sql_where );
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	//更新track_e_cw进度
	$sqlwhere = " cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $userid ) . "' AND cw_id=" . Database::escape ( $cw_id );
	$update_data = array ('progress' => $progress );
	$sql = Database::sql_update ( $tbl_track_cw, $update_data, $sqlwhere );
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$cu_sql_where = " course_code=" . Database::escape ( $course_code ) . " AND user_id=" . Database::escape ( $userid );
	//检测课程是否已学完,如果学完,则自动设置课程学完状态
	$is_course_completed = $objStat->is_course_finish ( $userid, $course_code, TRUE );
	if ($is_course_completed) {
		//更新表course_rel_user中的课程总体进度
		$sql_data = array ("is_pass" => LEARNING_STATE_COMPLETED, 'progress' => 100 );
		$sql = Database::sql_update ( $tbl_course_user, $sql_data, $cu_sql_where . " AND is_pass<>" . LEARNING_STATE_PASSED );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//更新crs_lp_view中SCORM进度为100
		$sql = "UPDATE " . $tbl_lp_view . " SET progress=100 WHERE cc='" . escape ( $course_code ) . "' AND user_id='" . escape ( $userid ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	} else {
		//课程没有完成,只更新track_e_cw中进度
		$progress = $objStat->get_course_progress ( $course_code, $userid );
		if ($progress > 100) $progress = 100;
		$sql_data = array ("is_pass" => LEARNING_STATE_IMCOMPLETED, 'progress' => $progress );
		$sql = Database::sql_update ( $tbl_course_user, $sql_data, $cu_sql_where );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

/**
 * 初始化SCORM跟踪记录，些方法暂不使用
 * @param unknown_type $course_code
 * @param unknown_type $lp_id
 * @param unknown_type $user_id
 * @deprecated
 */
function lms_scorm_init($course_code, $lp_id, $user_id) {
	$lp_item_table = Database::get_course_table ( TABLE_LP_ITEM );
	$lp_table = Database::get_course_table ( TABLE_LP_VIEW );
	$lp_item_view_table = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
	
	$sql = "SELECT * FROM $lp_table WHERE lp_id = '$lp_id' AND user_id = '$user_id'";
	$sql .= " AND cc='" . escape ( $course_code ) . "' ";
	$sql .= " ORDER BY view_count DESC";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$view_id = 0; //used later to query lp_item_view
	if (Database::num_rows ( $res ) > 0) {
		$row = Database::fetch_array ( $res, "ASSOC" );
		$lp_view_id = $row ['id'];
		$attempt = $row ['view_count'];
		$last_item_seen = $row ['last_item'];
		$progress_db = $row ['progress'];
	} else {
		$this->attempt = 1;
		$sql_data = array ('lp_id' => $lp_id, 'user_id' => $user_id, 'view_count' => 1, "cc" => $course_code );
		$sql_ins = Database::sql_insert ( $lp_table, $sql_data );
		$res_ins = api_sql_query ( $sql_ins, __FILE__, __LINE__ );
		$lp_view_id = Database::get_last_insert_id ();
	}
	
	$sql = "SELECT * FROM $lp_item_table WHERE lp_id = '" . escape ( $lp_id ) . "'  AND cc='" . escape ( $course_code ) . "' "; //liyu: V1.4
	$sql .= " ORDER BY parent_item_id, display_order";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res, "ASSOC" ) ) {
		$sql = "SELECT * FROM $lp_item_view_table  WHERE lp_view_id = " . $lp_view_id . " " . "AND lp_item_id = " . $row ['id'];
		$sql .= " AND cc='" . escape ( $course_code ) . "' ";
		$sql .= " ORDER BY view_count DESC ";
		$res2 = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $res2 ) > 0 == false) {
			$sql_data = array ('lp_item_id' => $row ["id"], 'lp_view_id' => $lp_view_id, 'view_count' => 1, 'status' => 'not attempted', 'cc' => $course_code );
			$sql_ins = Database::sql_insert ( $lp_item_view_table, $sql_data );
			$res_ins = api_sql_query ( $sql_ins, __FILE__, __LINE__ );
		}
	}
}

//--------------------------------------------------------------------------


/**
 * Build up the JavaScript representation of an array element
 *
 * @param string $a left array element
 * @param string $b right array element
 * @return comparator - 0,1,-1
 */
function scorm_element_cmp($a, $b) {
	preg_match ( '/.*?(\d+)\./', $a, $matches );
	$left = intval ( $matches [1] );
	preg_match ( '/.?(\d+)\./', $b, $matches );
	$right = intval ( $matches [1] );
	if ($left < $right) {
		return - 1; // smaller
	} elseif ($left > $right) {
		return 1; // bigger
	} else {
		// look for a second level qualifier eg cmi.interactions_0.correct_responses_0.pattern
		if (preg_match ( '/.*?(\d+)\.(.*?)\.(\d+)\./', $a, $matches )) {
			$leftterm = intval ( $matches [2] );
			$left = intval ( $matches [3] );
			if (preg_match ( '/.*?(\d+)\.(.*?)\.(\d+)\./', $b, $matches )) {
				$rightterm = intval ( $matches [2] );
				$right = intval ( $matches [3] );
				if ($leftterm < $rightterm) {
					return - 1; // smaller
				} elseif ($leftterm > $rightterm) {
					return 1; // bigger
				} else {
					if ($left < $right) {
						return - 1; // smaller
					} elseif ($left > $right) {
						return 1; // bigger
					}
				}
			}
		}
		// fall back for no second level matches or second level matches are equal
		return 0; // equal to
	}
}

function _convert_scorm_to_sec($total_time) {
	list ( $time, $micro_sec ) = explode ( ".", $total_time );
	list ( $hours, $minutes, $seconds ) = explode ( ":", $time );
	$tt = intval ( $hours ) * 3600 + intval ( $minutes ) * 60 + $seconds + round ( $micro_sec / 1000 );
	return $tt;
}

/**
 * Insert a record into a table and return the "id" field if required
 *
 * If the return ID isn't required, then this just reports success as true/false.
 * $dataobject is an object containing needed data
 *
 * @uses $db
 * @uses $CFG
 * @param string $table The database table to be checked against.
 * @param object $dataobject A data object with values for one or more fields in the record
 * @param bool $returnid Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
 * @param string $primarykey (obsolete) This is now forced to be 'id'.
 */
function insert_record($table, $dataobject, $returnid = true, $primarykey = 'id') {
	
	global $db, $CFG;
	
	/// Check we are handling a proper $dataobject
	if (is_array ( $dataobject )) {
		$dataobject = ( object ) $dataobject;
	}
	
	/// In Moodle we always use auto-numbering fields for the primary key
	/// so let's unset it now before it causes any trouble later
	unset ( $dataobject->{$primarykey} );
	
	/// Get the correct SQL from adoDB
	if (! $insertSQL = _GetInsertSQL ( $table, ( array ) $dataobject, true )) {
		return false;
	}
	
	/// Run the SQL statement
	if (! $rs = api_sql_query ( $insertSQL, __FILE__, __LINE__ )) {
		return false;
	}
	
	/// We already know the record PK if it's been passed explicitly,
	/// or if we've retrieved it from a sequence (Postgres and Oracle).
	if (! empty ( $dataobject->{$primarykey} )) {
		return $dataobject->{$primarykey};
	}
	
	/// This only gets triggered with MySQL and MSQL databases
	/// however we have some postgres fallback in case we failed
	/// to find the sequence.
	$id = Database::get_last_insert_id ();
	
	return ( integer ) $id;
}

function _GetInsertSQL($tableName, $arrFields, $magicq = false) {
	$fieldInsertedCount = 0;
	$columns = _MetaColumns ( $tableName );
	foreach ( $columns as $field ) {
		$upperfname = strtolower ( $field->name );
		$fnameq = $upperfname;
		if (array_key_exists ( $upperfname, $arrFields )) {
			if (is_null ( $arrFields [$upperfname] ) || (empty ( $arrFields [$upperfname] ) && strlen ( $arrFields [$upperfname] ) == 0)) {
				//Set empty
				$arrFields [$upperfname] = "";
			
			}
			
			$val = $arrFields [$upperfname];
			if (is_numeric ( $val )) {
				$val = ( integer ) $val;
			} elseif (is_string ( $val )) {
				$val = Database::escape ( $val );
			}
			$values .= $val . ", ";
			$fieldInsertedCount ++;
			$fields .= $fnameq . ", ";
		}
	}
	if ($fieldInsertedCount <= 0) return false;
	$fields = substr ( $fields, 0, - 2 );
	$values = substr ( $values, 0, - 2 );
	
	$sql = 'INSERT INTO ' . $tableName . ' ( ' . $fields . ' ) VALUES ( ' . $values . ' )';
	return $sql;
}

/**
 * Update a record in a table
 *
 * $dataobject is an object containing needed data
 * Relies on $dataobject having a variable "id" to
 * specify the record to update
 *
 * @uses $CFG
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param object $dataobject An object with contents equal to fieldname=>fieldvalue. Must have an entry for 'id' to map to the table specified.
 * @return bool
 */
function update_record($table, $dataobject) {
	
	global $_db, $CFG;
	
	// integer value in id propery required
	if (empty ( $dataobject->id )) {
		return false;
	}
	$dataobject->id = ( int ) $dataobject->id;
	
	/// Check we are handling a proper $dataobject
	if (is_array ( $dataobject )) {
		$dataobject = ( object ) $dataobject;
	}
	
	// Determine all the fields in the table
	if (! $columns = _MetaColumns ( $table )) {
		return false;
	}
	$data = ( array ) $dataobject;
	
	// Pull out data matching these fields
	$update = array ();
	foreach ( $columns as $column ) {
		if ($column->name == 'id') {
			continue;
		}
		if (array_key_exists ( $column->name, $data )) {
			$key = $column->name;
			$value = $data [$key];
			if (is_null ( $value )) {
				$update [] = "$key = NULL"; // previously NULLs were not updated
			} else if (is_bool ( $value )) {
				$value = ( int ) $value;
				$update [] = "$key = $value"; // lets keep pg happy, '' is not correct smallint MDL-13038
			} else {
				$update [] = "$key = '$value'"; // All incoming data is already quoted
			}
		}
	}
	
	if ($update) {
		$query = "UPDATE {$table} SET " . implode ( ',', $update ) . " WHERE id = {$dataobject->id}";
		if (! $rs = api_sql_query ( $query, __FILE__, __LINE__ )) {
			return false;
		}
	}
	
	return true;
}

function _MetaColumns($table, $normalize = false) {
	$metaColumnsSQL = "SHOW COLUMNS FROM %s";
	$sql = sprintf ( $metaColumnsSQL, ($normalize) ? strtoupper ( $table ) : $table );
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$retarr = array ();
	while ( $columns = Database::fetch_object ( $rs ) ) {
		$fld = new stdClass ();
		$fld->name = $columns->Field;
		$fld->type = $columns->Type;
		$retarr [strtolower ( $fld->name )] = $fld;
	}
	return $retarr;
}

/**
 * Moodle replacement for php stripslashes() function,
 * works also for objects and arrays.
 *
 * The standard php stripslashes() removes ALL backslashes
 * even from strings - so  C:\temp becomes C:temp - this isn't good.
 * This function should work as a fairly safe replacement
 * to be called on quoted AND unquoted strings (to be sure)
 *
 * @param mixed something to remove unsafe slashes from
 * @return mixed
 */
function stripslashes_safe($mixed) {
	// there is no need to remove slashes from int, float and bool types
	if (empty ( $mixed )) {
		//nothing to do...
	} else if (is_string ( $mixed )) {
		if (ini_get_bool ( 'magic_quotes_sybase' )) { //only unescape single quotes
			$mixed = str_replace ( "''", "'", $mixed );
		} else { //the rest, simple and double quotes and backslashes
			$mixed = str_replace ( "\\'", "'", $mixed );
			$mixed = str_replace ( '\\"', '"', $mixed );
			$mixed = str_replace ( '\\\\', '\\', $mixed );
		}
	} else if (is_array ( $mixed )) {
		foreach ( $mixed as $key => $value ) {
			$mixed [$key] = stripslashes_safe ( $value );
		}
	} else if (is_object ( $mixed )) {
		$vars = get_object_vars ( $mixed );
		foreach ( $vars as $key => $value ) {
			$mixed->$key = stripslashes_safe ( $value );
		}
	}
	
	return $mixed;
}

/**
 * This function makes the return value of ini_get consistent if you are
 * setting server directives through the .htaccess file in apache.
 * Current behavior for value set from php.ini On = 1, Off = [blank]
 * Current behavior for value set from .htaccess On = On, Off = Off
 * Contributed by jdell @ unr.edu
 *
 * @param string $ini_get_arg ?
 * @return bool
 * @todo Finish documenting this function
 */
function ini_get_bool($ini_get_arg) {
	$temp = ini_get ( $ini_get_arg );
	
	if ($temp == '1' or strtolower ( $temp ) == 'on') {
		return true;
	}
	return false;
}

/**
 * Does proper javascript quoting.
 * Do not use addslashes anymore, because it does not work when magic_quotes_sybase is enabled.
 *
 * @since 1.8 - 22/02/2007
 * @param mixed value
 * @return mixed quoted result
 */
function addslashes_js($var) {
	if (is_string ( $var )) {
		$var = str_replace ( '\\', '\\\\', $var );
		$var = str_replace ( array ('\'', '"', "\n", "\r", "\0" ), array ('\\\'', '\\"', '\\n', '\\r', '\\0' ), $var );
		$var = str_replace ( '</', '<\/', $var ); // XHTML compliance
	} else if (is_array ( $var )) {
		$var = array_map ( 'addslashes_js', $var );
	} else if (is_object ( $var )) {
		$a = get_object_vars ( $var );
		foreach ( $a as $key => $value ) {
			$a [$key] = addslashes_js ( $value );
		}
		$var = ( object ) $a;
	}
	return $var;
}

/**
 * Determine if there is data waiting to be processed from a form
 *
 * Used on most forms in Moodle to check for data
 * Returns the data as an object, if it's found.
 * This object can be used in foreach loops without
 * casting because it's cast to (array) automatically
 *
 * Checks that submitted POST data exists and returns it as object.
 *
 * @param string $url not used anymore
 * @return mixed false or object
 */
function data_submitted($url = '') {
	
	if (empty ( $_POST )) {
		return false;
	} else {
		return ( object ) $_POST;
	}
}