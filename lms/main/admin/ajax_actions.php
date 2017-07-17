<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../inc/global.inc.php');
header ( "Content-Type: text/html;charset=UTF-8" );
$lib_path = SYS_ROOT . "main/inc/lib/";

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'payment.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

api_block_anonymous_users ();
if (! (isset ( $_user ['user_id'] ) && $_user ['user_id'])) {
	//exit;
}
//api_protect_admin_script ();


$action = getgpc ( 'action' );
if (isset ($action )) {
	switch ($action) {
		case 'get_sub_dept' :
			header ( "Cache-Control: must-revalidate" );
			header ( "Cache-Control: post-check=0, pre-check=0", false );
			header ( "Pragma: no-cache" );
			header ( "Expires: " . gmdate ( "D, d M Y H:i:s", mktime ( date ( "H" ) - 2, date ( "i" ), date ( "s" ), date ( "m" ), date ( "d" ), date ( "Y" ) ) ) . " GMT" );
			header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
			header ( "Content-Type: text/xml;charset=" . SYSTEM_CHARSET );
			//sleep(1);
			echo '<?xml version="1.0" encoding="' . SYSTEM_CHARSET . '"?><root>';
			$pid = (isset ( $_REQUEST ['id'] ) ? intval(getgpc ( 'id' )) : '0');
			$deptObj = new DeptManager ();
			$sub_depts = $deptObj->get_sub_dept ( $pid );
			if ($sub_depts && is_array ( $sub_depts )) {
				foreach ( $sub_depts as $dept_info ) {
					if ($dept_info ['pid'] == 0) {
						echo '<item parent_id="' . intval($dept_info ['pid']) . '"  id="' . intval($dept_info ['id']) . '" state="closed"><content><name><![CDATA[' . $dept_info ['dept_name'] . ']]></name><state>closed</state></content></item>';
					} else {
						echo '<item parent_id="' . intval($dept_info ['pid']) . '"  id="' . intval($dept_info ['id']) . '"><content><name><![CDATA[' . $dept_info ['dept_name'] . ']]></name><level>' . $dept_info ['level'] . '</level></content></item>';
					}
				}
			}
			echo '</root>';
			exit ();
			break;
		case 'get_sub_dept2' :
			header ( "Cache-Control: must-revalidate" );
			header ( "Cache-Control: post-check=0, pre-check=0", false );
			header ( "Pragma: no-cache" );
			header ( "Expires: " . gmdate ( "D, d M Y H:i:s", mktime ( date ( "H" ) - 2, date ( "i" ), date ( "s" ), date ( "m" ), date ( "d" ), date ( "Y" ) ) ) . " GMT" );
			header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
			header ( "Content-Type: text/html;charset=" . SYSTEM_CHARSET );
			sleep ( 1 );
			
			echo '[';
			$pid = (isset ( $_REQUEST ['id'] ) ? intval(getgpc ( 'id' )) : '0');
			$deptObj = new DeptManager ();
			$sub_depts = $deptObj->get_sub_dept ( $pid, true );
			if ($sub_depts && is_array ( $sub_depts )) {
				foreach ( $sub_depts as $dept_info ) {
					//if($dept_info['pid']==0){
					

					/*echo "\t".'{ attributes: { id : "'.$dept_info['id'].'"},'
						.($dept_info['has_sub_depts']=='true'?' state: "closed",':"").' data: {title:"'.$dept_info['dept_name'].'",href:"dept_list.php?pid='.$dept_info['id'].'",target:"_blank"  },';*/
					echo "\t" . '{ attributes: { id : "' . intval($dept_info ['id']) . '"},' . ($dept_info ['has_sub_depts'] == 'true' ? ' state: "closed",' : "") . ' data: "' . $dept_info ['dept_name'] . '"},';
					
				/*}else{
						echo '['."\n";
						echo "\t".'{ attributes: { id : "'.$dept_info['id'].'" }, data: "'.$dept_info['dept_name'].'" },'."\n";
						echo ']'."\n";
						}*/
				}
			}
			echo ']' . "\n";
			exit ();
		case 'cancelOrder' :
			$check_exists = api_is_platform_admin () ? false : true;
			$result = Payment::change_order_status ( getgpc ( 'order_sn' ), getgpc ( 'new_status' ), api_get_user_id (), $check_exists );
			echo $result;
			exit ();
			break;
		
		case "options_get_all_sub_depts" : //某机构下部门下拉框
			$deptObj = new DeptManager ();
			$org_id = intval(getgpc ( "org_id") );
			if (empty ( $org_id )) exit ();
			$all_sub_depts = $deptObj->get_sub_dept_ddl ( $org_id );
			//if($all_sub_depts && is_array($all_sub_depts)) unset($all_sub_depts[$org_id]);
			//var_dump($all_sub_depts);exit;
			$html = "";
			foreach ( $all_sub_depts as $item ) {
				$html .= '<option value="' . intval($item ['id']) . '">' . str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'] . '</option>';
			}
			echo $html;
			break;
		
		
		case "get_courses" : //学习班中课程查询
			$first_letter_left = getgpc ( "keyword" );
			$leftOrgID = getgpc ( "type" );
			$class_id = intval(getgpc ( "class_id" ));
			
			$tbl_class = Database::get_main_table ( TABLE_MAIN_CLASS );
			$tbl_course_class = Database::get_main_table ( TABLE_MAIN_COURSE_CLASS );
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			
			$sql = "SELECT c.code,title FROM $tbl_course c LEFT JOIN $tbl_course_class cc ON c.code=cc.course_code WHERE class_id IS NULL ";
			
			if (isset ( $first_letter_left ) && ! empty ( $first_letter_left )) {
				$sql .= " AND (title LIKE '%" . $first_letter_left . "%' OR code LIKE '%" . $first_letter_left . "%') ";
			}
			if ($class_id) {
				$sql .= " AND c.code NOT IN(select course_code from {$tbl_course_class} AS t1 WHERE  t1.class_id='" . Database::escape_string ( $class_id ) . "')";
			}
			
			$sql .= " ORDER BY  title";
			//echo $sql;
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			$left_courses = api_store_result ( $result );
			echo api_json_encode ( $left_courses );
			break;
		
		case "option_get_org_course_category" : //机构的课程分类
			//$org_id = getgpc ( "org_id" );
			$containd_empty_top = (isset ( $_REQUEST ['empty_top'] ) ? getgpc ( "empty_top" ) : "true");
			$objCrsMng = new CourseManager ();
			$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, - 1 );
			$html = (is_equal ( $containd_empty_top, "true" ) ? '<option value="0"></option>' : "");
			foreach ( $category_tree as $item ) {
				$html .= '<option value="' . intval($item ['id']) . '">' . str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $item ['name'] . '</option>';
			}
			echo $html;
			break;
		
		case 'get_course_list' :
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$keyword = getgpc ( "keyword" );
			$category_code = intval(getgpc ( "category_code") );
			//$org_id = getgpc ( "org_id" );
			$sql = "SELECT code,title FROM " . $tbl_course . " WHERE 1 ";
			if ($keyword) $sql .= " AND title LIKE '%" . escape ( $keyword, TRUE ) . "%'";
			if ($category_code) $sql .= " AND category_code='" . $category_code . "'";
			//if ($org_id) $sql .= " AND org_id=" . Database::escape ( $org_id );
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
		
		case 'get_course_list2' : //安排必修课程使用
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_competency_course = Database::get_main_table ( TABLE_MAIN_COMPETENCY_COURSE );
			$keyword = getgpc ( "keyword" );
			$category_code =intval( getgpc ( "category_code"));
			$competency_id = intval(getgpc ( 'competency_id') );
			$sql = "SELECT code,title FROM " . $tbl_course . " WHERE 1 ";
			if ($keyword) $sql .= " AND title LIKE '%" . escape ( $keyword, TRUE ) . "%'";
			if ($category_code) $sql .= " AND category_code='" . $category_code . "'";
			//if ($competency_id) $sql .= " AND code IN (SELECT course_code FROM $tbl_competency_course WHERE competency_id=" . Database::escape ( $competency_id ) . ")";
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
		
		case 'get_course_list_without_mime' :
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$user_id =intval( getgpc ( "user_id" ));
			$keyword = getgpc ( "keyword" );
			$category_code = intval(getgpc ( "category_code" ));
			//$org_id = getgpc ( "org_id" );
			$sql = "SELECT code,title FROM " . $tbl_course . " WHERE code NOT IN (SELECT c.code FROM " . $tbl_course_user . " cu, " . $tbl_course . " c WHERE cu.user_id = '" . $user_id . "' AND cu.course_code = c.code)  ";
			if ($keyword) $sql .= " AND title LIKE '%" . escape ( $keyword, TRUE ) . "%'";
			if ($category_code) $sql .= " AND category_code='" . $category_code . "'";
			//if ($org_id) $sql .= " AND org_id=" . Database::escape ( $org_id );
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
			
		case 'get_user_list' : //获取某部门下用户列表
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id =intval( getgpc ( "dept_id" ));
			$sql = "SELECT user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1 WHERE 1 ";
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . intval(Database::escape ( $dept_id ));
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
			}
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
		
		case 'get_user_list_without_cur_crsuser' : //获取用户列表(除当前课程用户)
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$course_code = getgpc ( "code" );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id = intval(getgpc ( "dept_id") );
			$sql = "SELECT user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1 WHERE user_id NOT IN (SELECT cu.user_id FROM " . $tbl_course_user . " cu, " . $tbl_course . " c WHERE cu.course_code = '" . $course_code . "' AND cu.course_code = c.code)  ";
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . intval(Database::escape ( $dept_id ));
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '%" . $dept_sn . "%'";
			}
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
			
		case 'get_user_list_without_cur_crsadmin' : //获取用户列表(除当前课程管理员)
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$course_code = getgpc ( "code" );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id = intval(getgpc ( "dept_id") );
			$sql = "SELECT user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1 WHERE t1.status<>".STUDENT." AND t1.user_id NOT IN (SELECT cu.user_id FROM " . $tbl_course_user . " cu, " . $tbl_course . " c WHERE cu.course_code = '" . $course_code . "' AND cu.course_code = c.code AND cu.is_course_admin=1)  ";
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . intval(Database::escape ( $dept_id ));
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '%" . $dept_sn . "%'";
			}
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
		
		case 'get_user_list_without_curr_surveyuser' : //调查问卷用户
			include_once ("../survey/survey.inc.php");
			$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id") ) : "";
			$keyword = getgpc ( "keyword" );
			
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id =intval( getgpc ( "dept_id" ));
			$sql = "SELECT user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1 WHERE  user_id NOT IN (SELECT cu.user_id FROM " . $tbl_survey_user . " cu, " . $tbl_survey . " c WHERE cu.survey_id = " . Database::escape ( $survey_id ) . " AND cu.survey_id = c.id)  ";
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . intval(Database::escape ( $dept_id ));
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
			}
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
	}
}
