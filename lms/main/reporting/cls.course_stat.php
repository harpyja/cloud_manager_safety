<?php

class TrackStat {

	function TrackStat() {
	}

	function __construct() {
		$this->TrackStat ();
	}

	function get_student_count_in_course($course_code) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT count(*) FROM " . $tbl_course_user . " WHERE course_code='" . $course_code . "' AND status=" . STUDENT;
		$nb_students_in_course = Database::get_scalar_value ( $sql );
		return $nb_students_in_course;
	}

	/**
	 * 课程测验平均分
	 * @param $course_code
	 */
	function get_average_student_score($course_code) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$tbl_track_exercise = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		
		//学员总数
		$nb_students_in_course = $this->get_student_count_in_course ( $course_code );
		
		//平均分总和
		$sql = "SELECT  SUM(exe_result)/SUM(exe_weighting)	FROM " . $tbl_track_exercise . " AS t1,
		$tbl_course_user AS t2	WHERE t1.exe_user_id=t2.user_id AND t2.status=" . STUDENT . " AND t1.exe_cours_id = '" . escape ( $course_code ) . "' AND t2.course_code='" . escape ( $course_code ) . "'";
		//echo $sql."<br/>";
		$pourcentageScore = Database::get_scalar_value ( $sql );
		
		if ($nb_students_in_course > 0) {
			$avg_score_in_course = round ( ($pourcentageScore * 100) / $nb_students_in_course, 1 ) . ' %';
		}
		return $avg_score_in_course;
	}

	function get_time_spent_on_the_platform($user_id) {
		if ($user_id) {
			$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
			
			$sql = "SELECT ABS(SUM(UNIX_TIMESTAMP(logout_date)-UNIX_TIMESTAMP(login_date))) FROM '.$tbl_track_login.'
				WHERE login_user_id = " . Database::escape ( $user_id );
			//echo $sql;
			return Database::get_scalar_value ( $sql );
		}
		return 0;
	}

	/**
	 * 每个给定用户的平均进度: 某一用户总进度和/LP数
	 * @param $student_id
	 * @param $course_code
	 */
	function get_avg_student_progress($student_id, $course_code) {
		$avg_progress = 0;
		if ($student_id && $course_code) {
			require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
			$tbl_course_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$tbl_course_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
			
			//课程内所有LP
			$sql = 'SELECT id FROM ' . $tbl_course_lp . " WHERE cc='" . $course_code . "'";
			$lp_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
			
			$sqlProgress = "SELECT AVG(progress) FROM " . $tbl_course_lp_view . " AS lp_view
				 LEFT JOIN $tbl_course_lp AS t2 ON lp_view.lp_id=t2.id
			WHERE lp_view.user_id = " . Database::escape ( $student_id ) . " AND " . Database::create_in ( $lp_ids, "lp_view.lp_id" );
			//echo $sqlProgress."<br/>";
			$avg_progress = Database::get_scalar_value ( $sqlProgress );
		}
		
		return $avg_progress;
	}

	function get_avg_student_score($student_id, $course_code) {
		$sqlScore = "SELECT  exe_result,exe_weighting
		 				FROM " . Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES ) . "
						WHERE exe_user_id = '" . escape ( $student_id ) . "'
		 				AND exe_cours_id = '" . escape ( $course_code ) . "'";
		$resultScore = api_sql_query ( $sqlScore );
		$score = 0;
		while ( $a_score = Database::fetch_array ( $resultScore ) ) {
			$score = $score + $a_score ['exe_result'];
			$weighting = $weighting + $a_score ['exe_weighting'];
		}
		
		if ($weighting > 0) {
			$pourcentageScore = round ( ($score * 100) / $weighting );
		}
		
		return $pourcentageScore;
	}
}