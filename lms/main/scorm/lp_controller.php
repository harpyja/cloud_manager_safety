<?php
define ( "IN_SCORM", TRUE );
define ( 'SCORM_DEBUG', 10 );
$debug = SCORM_DEBUG;

$language_file = array ("course_home", "scormdocument", "scorm", "learnpath", "resourcelinker", "tracking", "registration" );
$use_anonymous = true;

require_once ('learnpath.class.php');
require_once ('learnpathItem.class.php');
require_once ('scorm.class.php');
require_once ('back_compat.inc.php');

$is_allowd_to_edit = api_is_allowed_to_edit ();
$lpfound = false;
$myrefresh = 0;
$myrefresh_id = 0;
if (! empty ( $_SESSION ['refresh'] ) && $_SESSION ['refresh'] == 1) {
	api_session_unregister ( 'refresh' );
	$myrefresh = 1;
	if ($debug > 0) api_scorm_log ( 'New LP - Refresh asked', __FILE__, __LINE__ );
}
if ($debug > 0) api_scorm_log ( 'New LP - Passed refresh check', __FILE__, __LINE__ );

$lp_controller_touched = 1;
$lp_found = false;

if (isset ( $_SESSION ['lpobject'] )) {
	if ($debug > 0) api_scorm_log ( 'SESSION[lpobject] is defined', __FILE__, __LINE__ );
	$oLP = unserialize ( $_SESSION ['lpobject'] );
	if (is_object ( $oLP )) {
		if ($debug > 0) api_scorm_log ( 'New LP - oLP is object', __FILE__, __LINE__ );
		if ($myrefresh == 1 or (empty ( $oLP->cc )) or $oLP->cc != api_get_course_id ()) {
			if ($debug > 0) api_scorm_log ( 'New LP - Course has changed, discard lp object', __FILE__, __LINE__ );
			if ($myrefresh == 1) {
				$myrefresh_id = $oLP->get_id ();
			}
			$oLP = null;
			api_session_unregister ( 'oLP' );
			api_session_unregister ( 'lpobject' );
		} else {
			$_SESSION ['oLP'] = $oLP;
			$lp_found = true;
		}
	}
}

//oLP数据检测结束
if ($debug > 0) api_scorm_log ( ' Passed data remains check', __FILE__, __LINE__ );

//没有找到则实例化 或 新请求的L
if ($lp_found == false or (! empty ( $_REQUEST ['lp_id'] ) && $_SESSION ['oLP']->get_id () != $_REQUEST ['lp_id'])) {
	if ($debug > 0) api_scorm_log ( 'New LP - oLP is not object, has changed or refresh been asked, getting new', __FILE__, __LINE__ );
	//regenerate a new lp object? Not always as some pages don't need the object (like upload?)
	if (! empty ( $_REQUEST ['lp_id'] ) || ! empty ( $myrefresh_id )) {
		if ($debug > 0) api_scorm_log ( 'New LP - lp_id is defined', __FILE__, __LINE__ );
		//select the lp in the database and check which type it is (scorm/ZLMS/aicc) to generate the right object
		$lp_table = Database::get_course_table ( TABLE_LP_MAIN );
		if (! empty ( $_REQUEST ['lp_id'] )) {
			$lp_id = intval($_REQUEST ['lp_id']);
		} else {
			$lp_id = intval($myrefresh_id);
		}
		if (is_numeric ( $lp_id )) {
			$lp_id = Database::escape_string ( $lp_id );
			$sel = "SELECT * FROM $lp_table WHERE id = $lp_id";
			if ($debug > 0) api_scorm_log ( 'querying ' . $sel, __FILE__, __LINE__ );
			$res = api_sql_query ( $sel, __FILE__, __LINE__ );
			if (Database::num_rows ( $res ) > 0) {
				$row = Database::fetch_array ( $res, "ASSOC" );
				$type = $row ['lp_type'];
				if ($debug > 0) api_scorm_log ( ' found row - type =' . $type . ' - Calling constructor with ' . api_get_course_id () . ' - ' . $lp_id . ' - ' . api_get_user_id (), __FILE__, __LINE__ );
				switch ($type) {
					case 2 : //导入的SCORM课件
						if ($debug > 0) api_scorm_log ( ' found row - type scorm - Calling constructor with ' . api_get_course_id () . ' - ' . $lp_id . ' - ' . api_get_user_id (), __FILE__, __LINE__ );
						$oLP = new scorm ( api_get_course_id (), $lp_id, api_get_user_id () );
						if ($oLP !== false) {
							$lp_found = true;
						} else {
							api_scorm_log ( $oLP->error, __FILE__, __LINE__ );
						}
						break;
					
					default : //其它
						if ($debug > 0) api_scorm_log ( ' found row - type other - Calling constructor with ' . api_get_course_id () . ' - ' . $lp_id . ' - ' . api_get_user_id (), __FILE__, __LINE__ );
						$oLP = new learnpath ( api_get_course_id (), $lp_id, api_get_user_id () );
						if ($oLP !== false) {
							$lp_found = true;
						} else {
							api_scorm_log ( $oLP->error, __FILE__, __LINE__ );
						}
						break;
				}
			}
		} else {
			if ($debug > 0) api_scorm_log ( ' Request[lp_id] is not numeric', __FILE__, __LINE__ );
		}
	
	} else {
		if ($debug > 0) api_scorm_log ( ' Request[lp_id] and refresh_id were empty', __FILE__, __LINE__ );
	}
	
	if ($lp_found) {
		$_SESSION ['oLP'] = $oLP;
	}
}

if ($debug > 0) api_scorm_log ( ' Passed oLP creation check', __FILE__, __LINE__ );

$_SESSION ['oLP']->update_queue = array (); //reinitialises array used by javascript to update items in the TOC
$_SESSION ['oLP']->message = ''; //should use ->clear_message() method but doesn't work


//操作控制器分支
$action = (! empty ( $_REQUEST ['action'] ) ? $_REQUEST ['action'] : '');
if (! empty ( $action ) && $debug > 0) api_scorm_log ( ' Begin trigged action=' . $action, __FILE__, __LINE__ );

switch ($action) {
	
	//V2.1 起航教育使用
	case 'read' :
		if ($debug > 0) api_scorm_log ( ' read action triggered', __FILE__, __LINE__ );
		if ($lp_found == false) {
			api_scorm_log ( ' No learnpath given for read', __FILE__, __LINE__ );
			api_redirect ( api_get_path ( WEB_PATH ) . PORTAL_LAYOUT . "course_home.php?" . api_get_cidreq () );
		} else {
			if ($debug > 0) {
				api_scorm_log ( ' Trying to set current item to ' . intval($_REQUEST ['item_id']), __FILE__, __LINE__ );
			}
			if (! empty ( $_REQUEST ['item_id'] )) {
				$_SESSION ['oLP']->set_current_item ( intval($_REQUEST ['item_id']) );
			}
			include_once (api_get_path ( SYS_PATH ) . PORTAL_LAYOUT . "scorm_player.php");
		}
		break;
	
	case 'upload' : //导入上传
		if (! $is_allowd_to_edit) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' upload action triggered', __FILE__, __LINE__ );
		$cwdir = getcwd ();
		require ('lp_upload.php');
		chdir ( $cwdir );
		//require ('lp_list.php');
		break;
	
	case 'delete' : //删除
		if (! $is_allowd_to_edit) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' delete action triggered', __FILE__, __LINE__ );
		if (! $lp_found) {
			api_scorm_log ( 'No learnpath given for delete', __FILE__, __LINE__ );
			require ('lp_list.php');
		} else {
			$_SESSION ['refresh'] = 1;
			$_SESSION ['oLP']->delete ( null, intval ( getgpc ( 'lp_id', 'G' ) ), 'remove' );
			api_session_unregister ( 'oLP' );
			//require ('lp_list.php');
			api_redirect ( api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_list.php?' . api_get_cidreq () );
		}
		break;
	
	case 'edit' : //编辑学习路径
		if (! $is_allowd_to_edit) api_not_allowed ( true );
		if ($debug > 0) api_scorm_log ( 'edit action triggered', __FILE__, __LINE__ );
		if (! $lp_found) {
			api_scorm_log ( ' No learnpath given for edit', __FILE__, __LINE__ );
			require ('lp_list.php');
		} else {
			require ('lp_edit.php');
		}
		break;
	
	case 'update_lp' : //处理更新配置
		if (! $is_allowd_to_edit) api_not_allowed ( true );
		if ($debug > 0) api_scorm_log ( ' update_lp action triggered', __FILE__, __LINE__ );
		if ($lp_found) {
			
			$_SESSION ['refresh'] = 1;
			$_SESSION ['oLP']->set_name ( escape ( getgpc('lp_name','R') ) );
			//$_SESSION ['oLP']->set_author ( $_REQUEST ['lp_author'] );
			$_SESSION ['oLP']->set_encoding ( getgpc('lp_encoding','R')  );
			$_SESSION ['oLP']->set_maker ( getgpc('content_maker','R')  );
			$_SESSION ['oLP']->set_proximity ( getgpc('lp_proximity','R')  );
			//$_SESSION ['oLP']->set_theme ( $_REQUEST ['lp_theme'] );
			$_SESSION ['oLP']->set_learning_time ( getgpc('learning_time','R') );
			$_SESSION ['oLP']->set_learning_order ( getgpc('learning_order','R')  );
			
			$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
			$sql_data = array ('learning_time' => getgpc ( "learning_time" ), 'display_order' => getgpc ( "learning_order" ) );
			$sql = Database::sql_update ( $tbl_courseware, $sql_data, " attribute=" . Database::escape ( $_SESSION ['oLP']->get_id () ) );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			//全局搜索开启
			if (api_get_setting ( 'search_enabled' ) === 'true') {
			
			}
		}
		tb_close ();
		break;
	
	case 'delete_item' : //删除内容项
		if (! $is_allowd_to_edit) {
			api_not_allowed ( true );
		}
		
		if ($debug > 0) api_scorm_log ( ' delete item action triggered', __FILE__, __LINE__ );
		
		if (! $lp_found) {
			api_scorm_log ( ' No learnpath given for delete item', __FILE__, __LINE__ );
			require ('lp_list.php');
		} else {
			$_SESSION ['refresh'] = 1;
			
			if (is_numeric ( $_GET ['id'] )) {
				$_SESSION ['oLP']->delete_item ( $_GET ['id'] );
				
				$is_success = true;
			}
			
			if (isset ( $_GET ['view'] ) && $_GET ['view'] == 'build') {
				require ('lp_build.php');
			} else {
				require ('lp_admin_view.php');
			}
		}
		break;
	
	//显示隐藏
	case 'toggle_visible' : //change lp visibility (inside lp tool)
		if (! $is_allowd_to_edit) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' visibility action triggered', __FILE__, __LINE__ );
		if (! $lp_found) {
			api_scorm_log ( 'New LP - No learnpath given for visibility', __FILE__, __LINE__ );
			require ('lp_list.php');
		} else {
			$oLP->toggle_visibility ( getgpc ('lp_id'), getgpc ('new_status') );
			require ('lp_list.php');
		}
		break;
	
	case 'view' :
		if ($is_allowed_in_course == false) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' view action triggered', __FILE__, __LINE__ );
		if (! $lp_found) {
			api_scorm_log ( ' No learnpath given for view', __FILE__, __LINE__ );
			require ('lp_list.php');
		} else {
			if ($debug > 0) {
				api_scorm_log ( ' Trying to set current item to ' . intval($_REQUEST ['item_id']), __FILE__, __LINE__ );
			}
			if (! empty ( $_REQUEST ['item_id'] )) {
				$_SESSION ['oLP']->set_current_item ( intval($_REQUEST ['item_id']) );
			}
			require ('lp_view.php');
		}
		break;
	
	case 'list' :
		if ($is_allowed_in_course == false) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' list action triggered', __FILE__, __LINE__ );
		if ($lp_found) {
			$_SESSION ['refresh'] = 1;
			$_SESSION ['oLP']->save_last ();
		}
		require ('lp_list.php');
		break;
	
	case 'switch_reinit' :
		if ($is_allowed_in_course == false) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' switch_reinit action triggered', __FILE__, __LINE__ );
		if (! $lp_found) {
			api_scorm_log ( ' No learnpath given for switch', __FILE__, __LINE__ );
			require ('lp_list.php');
		}
		$_SESSION ['refresh'] = 1;
		$_SESSION ['oLP']->update_reinit ();
		require ('lp_list.php');
		break;
	
	default :
		$is_allowed_in_course = api_is_course_admin ();
		if ($is_allowed_in_course == false) {
			api_not_allowed ( true );
		}
		if ($debug > 0) api_scorm_log ( ' default action triggered', __FILE__, __LINE__ );
		//$_SESSION['refresh'] = 1;
		require ('lp_list.php');
		break;
}
if (! empty ( $_SESSION ['oLP'] )) {
	$_SESSION ['lpobject'] = serialize ( $_SESSION ['oLP'] );
	if ($debug > 0) api_scorm_log ( ' lpobject is serialized in session', __FILE__, __LINE__ );
}
