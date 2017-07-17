<?php

function get_category_name($categoryID) {
	$tbl_courses_nodes = Database::get_main_table ( TABLE_MAIN_CATEGORY );
	$sql = "SELECT name FROM {$tbl_courses_nodes} WHERE id='{$categoryID}'";
	$categoryName = Database::get_scalar_value ( $sql, __FILE__, __LINE__ );
	return $categoryName;
}

function count_courses_in_category($categoryID) {
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	if ($categoryID) {
		$sql = "SELECT count(*) FROM $tbl_course WHERE category_code='" . Database::escape_string ( $categoryID ) . "'";
	} else {
		$sql = "SELECT count(*) FROM $tbl_course WHERE category_code IS NULL OR category_code=0";
	}
	return Database::get_scalar_value ( $sql, __FILE__, __LINE__ );
}

function get_courses_in_category($categoryID, $start) {
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$sql_find = "FROM {$tbl_course} WHERE category_code " . (empty ( $categoryID ) ? " IS NULL" : "='" . $categoryID . "'");
	
	$sql = "SELECT count(*) " . $sql_find;
	$result_find_count = api_sql_query ( $sql, __FILE__, __LINE__ );
	$row = mysql_fetch_array ( $result_find_count, MYSQL_NUM );
	$courses [] = $row [0];
	
	$sql = "SELECT * " . $sql_find . " LIMIT " . ($start * NUMBER_PAGE) . "," . NUMBER_PAGE;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $sql, 'ASSOC' ) ) {
		if ($row ['registration_code'] == '') {
			$registration_code = false;
		} else {
			$registration_code = true;
		}
		$course [] = array ("code" => $row ['code'], 
				"directory" => $row ['directory'], 
				"db" => $row ['db_name'], 
				"visual_code" => $row ['visual_code'], 
				"title" => $row ['title'], 
				"tutor" => $row ['tutor_name'], 
				"subscribe" => $row ['subscribe'], 
				"unsubscribe" => $row ['unsubscribe'], 
				'registration_code' => $row ['registration_code'], 
				"visibility" => $row ['visibility'] );
	}
	$courses [] = $course;
	
	return $courses;
}

/**
 * 获取当前用户已注册的所有课程
 * retrieves all the courses that the user has already subscribed to
 * @author Zhong <poopsoft@163.com>
 * @param int $user_id: the id of the user
 * @return array an array containing all the information of the courses of the given user
 */
function get_courses_of_user($user_id) {
	$TABLECOURS = Database::get_main_table ( TABLE_MAIN_COURSE );
	$TABLECOURSUSER = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	$user_id = intval ( $user_id );
	$sql_select_courses = "SELECT course.code k, course.visual_code  vc, course.subscribe subscr, course.unsubscribe unsubscr,
	course.title i, course.tutor_name t, course.db_name db, course.directory dir, course_rel_user.status status,
	course_rel_user.sort sort
	FROM    $TABLECOURS       course,
	$TABLECOURSUSER  course_rel_user
	WHERE course.code = course_rel_user.course_code
	AND   course_rel_user.user_id = '" . $user_id . "'
	ORDER BY course_rel_user.sort ASC";
	//echo $sql_select_courses;
	$result = api_sql_query ( $sql_select_courses, __FILE__, __LINE__ ) or die ( mysql_error () );
	while ( $row = Database::fetch_array ( $result ) ) {
		$courses [] = array ("db" => $row ['db'], "code" => $row ['k'], "visual_code" => $row ['vc'], "title" => $row ['i'], "directory" => $row ['dir'], "status" => $row ['status'], "tutor" => $row ['t'], "subscribe" => $row ['subscr'], "unsubscribe" => $row ['unsubscr'], "sort" => $row ['sort'] );
	}
	
	return $courses;
}

?>
