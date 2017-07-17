<?php
$language_file = array ('customer_qihang' );
include_once ('../../main/inc/global.inc.php');
header ( "Content-Type: text/html;charset=UTF-8" );
$user_id = api_get_user_id ();
$action = getgpc ( 'action' );
if (isset ( $_REQUEST ['action'] )) {
	switch ($action) {
		case "check_old_pwd" : //验证旧密码是否正确
			api_block_anonymous_users ();
			$password = api_get_encrypted_password ( getgpc ( "old_pass" ), SECURITY_SALT );
			if (isset ( $_SESSION ["_user"] ) && $_SESSION ["_user"] ["password"] == $password) {
				echo "1";
			} else {
				echo "0";
			}
			break;
		
		case "check_email" :
			$table_user = Database::get_main_table ( TABLE_MAIN_USER );
			$table_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
			$email = getgpc ( "email" );
			$sql = "SELECT * FROM $table_user_register WHERE email = '" . escape ( $email ) . "'";
			$sql2 = "SELECT * FROM $table_user WHERE email = '" . escape ( $email ) . "'";
			if (Database::if_row_exists ( $sql ) or Database::if_row_exists ( $sql2 )) {
				echo 1;
			} else {
				echo 0;
			}
			break;
		
		case 'check_username' :
			$table_user = Database::get_main_table ( TABLE_MAIN_USER );
			$table_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
			$username = getgpc ( "username" );
			$sql = "SELECT * FROM $table_user_register WHERE username = '" . escape ( $username ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql2 = "SELECT * FROM $table_user WHERE username = '" . escape ( $username ) . "'";
			$res2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
			if (Database::num_rows ( $res ) > 0 or Database::num_rows ( $res2 ) > 0) {
				echo 1;
			} else {
				echo 0;
			}
			break;
		
		case 'check_cardno' :
			$tbl_card = Database::get_main_table ( 'bos_card' );
			$sql = "SELECT * FROM $tbl_card WHERE enabled=1 AND card_no='" . escape ( getgpc ( 'card_no' ) ) . "'";
			if (api_get_setting ( 'enabled_learning_card' ) == 'true' && Database::if_row_exists ( $sql )) {
				echo 1; //存在
			} else {
				echo 0;
			}
			break;
		case 'check_cardpwd' :
			$tbl_card = Database::get_main_table ( 'bos_card' );
			$sql = "SELECT * FROM $tbl_card WHERE enabled=1 AND card_no='" . escape ( getgpc ( 'card_no' ) ) . "' AND passwd='" . escape ( getgpc ( 'card_pwd' ) ) . "'";
			if (api_get_setting ( 'enabled_learning_card' ) == 'true' && Database::if_row_exists ( $sql )) {
				echo 1; //合法
			} else {
				echo 0;
			}
			break;
		case 'save_survey_suggestion' :
			api_block_anonymous_users ();
			include_once (api_get_path ( SYS_CODE_PATH ) . "survey/survey.inc.php");
			$survey_id = (isset ( $_REQUEST ['survey_id'] ) ? getgpc ( 'survey_id' ) : "");$survey_id=(int)$survey_id;
			$suggestion = Database::escape_string ( getgpc ( "suggestion" ) );
			$sql = "UPDATE $tbl_survey_user SET suggestion='" . $suggestion . "' WHERE survey_id=" . Database::escape ( $survey_id ) . " AND user_id=" . Database::escape ( $user_id );
			$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
			echo ($rtn ? 1 : 0);
			break;
		
		case 'track_cw_learning_time' :
			api_block_anonymous_users ();
			api_protect_course_script ();
			require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
			$objStat = new ScormTrackStat ();
			$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
			$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
			$course_code = getgpc ( "course_code" );
			if (empty ( $course_code )) $course_code = api_get_course_code ();
			$cw_id = getgpc ( 'cw_id' );$cw_id=(int)$cw_id;
			$learn_time = getgpc ( 'learn_time' );
			if (empty ( $learn_time )) $learn_time = 0;
			
			//更新课件学习时间
			$rtn = evnet_courseware ( $course_code, $user_id, $cw_id, $learn_time, 'add' );
			
			//更新课件学习进度
			$sqlwhere = " t1.cc='" . escape ( $course_code ) . "' AND t1.user_id='" . escape ( $user_id ) . "' AND cw_id=" . Database::escape ( $cw_id );
			$sql = "SELECT ROUND((total_time/(learning_time*60)*100)) FROM $tbl_track_cw AS t1, $tbl_courseware AS t2 WHERE t1.cw_id=t2.id AND $sqlwhere ";
			$progress = Database::get_scalar_value ( $sql );
			$sql_data = array ('progress' => $progress >= 100 ? 100 : $progress );
			$sql = Database::sql_update ( $tbl_track_cw . ' AS t1', $sql_data, $sqlwhere );
			$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			//更新课程总体学习进度
			$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$cu_sql_where = " course_code=" . Database::escape ( $course_code ) . " AND user_id=" . Database::escape ( $user_id );
			$progress = $objStat->get_course_progress ( $course_code, $user_id );
			$sql_data = array ('progress' => $progress );
			$sql = Database::sql_update ( $tbl_course_user, $sql_data, $cu_sql_where );
			$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			echo $rtn ? 1 : 0;
			break;
		
		case 'paper_auto_save_hanlder' :
			api_block_anonymous_users ();
			$tbl_exam_result = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES ); //考试总体结果
			$exam_id = getgpc ( 'exerciseId' );$exam_id=(int)$exam_id;
			$result_id = getgpc ( 'result_id' );$result_id=(int)$result_id;
			$choice = getgpc ( 'choice', 'P' ); //提交的答案
			$data_tracking = serialize ( $choice );
			if (empty ( $result_id )) {
				$sql = "SELECT MAX(exe_id)  FROM " . $tbl_exam_result . " WHERE status='imcomplete' AND exe_user_id='" . escape ( $user_id ) . "' AND exe_exo_id=" . Database::escape ( $exam_id );
				$result_id = Database::getval ( $sql, __FILE__, __LINE__ );
			}
			$sql_data = array ('data_tracking' => $data_tracking, 'last_save_time' => date ( 'Y-m-d H:i:s' ) );
			$sql = Database::sql_update ( $tbl_exam_result, $sql_data, "exe_id=" . Database::escape ( $result_id ) );
			$rtn = api_sql_query ( $sql, __FILE__, __LINE__ );
			echo $rtn ? 1 : 0;
			break;
                case 'formatvideo':
                    //执行查询格式化视频进程的命令
                    exec("ps axf | grep -e '255_2014-07-05-11-22-49_2_201212508926.mp4' | grep -v grep | awk '{print $1}'",$output);
                    var_dump($output);
                        break;
	}
}
