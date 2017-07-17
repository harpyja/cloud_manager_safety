<?php
$language_file [] = 'admin';
include_once ("inc/app.inc.php");

include ("send_learn_info.php");

api_protect_course_script ();

include_once (api_get_path ( SYS_CODE_PATH ) . 'exercice/exercise.class.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'announcements/announcements.inc.php');

$is_course_user = (isset ( $_SESSION ["is_allowed_in_course"] ) && $_SESSION ["is_allowed_in_course"] ? TRUE : FALSE);

$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
$tbl_lp_item_view = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
$tbl_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
$tbl_document = Database::get_course_table ( TABLE_DOCUMENT );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$tbl_assignment = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
$tbl_assignment_submission = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );

$learning_status = array (LEARNING_STATE_NOTATTEMPT => '未开始', LEARNING_STATE_COMPLETED => '已学完', LEARNING_STATE_IMCOMPLETED => '学习中', LEARNING_STATE_PASSED => '已通过' );

if (empty ( $cidReq )) api_redirect ( "index.php" );
$user_id = api_get_user_id ();
$course_code = (isset ( $_GET ["code"] ) ? getgpc ( "code", "G" ) : $cidReq);
if (empty ( $course_code )) $course_code = api_get_course_code ();

$action = (is_not_blank ( $_GET ["action"] ) ? getgpc ( "action", "G" ) : "outline");

$user=api_get_user_name ();//用户名称
$n_cidReq=$_GET ["cidReq"];
$sql_class="select `title` from `course` where `code` = '$n_cidReq' ";
$class=  Database::getval($sql_class,__FILE__, __LINE__ );  //课程名称  
$report_name=$user.'_'.$class;
$sql_look="select `id` from `report` where `report_name` = '$report_name' ";
$report_id=  Database::getval($sql_look,__FILE__, __LINE__ );   //report id
//echo '<br><br><br><br>';
//var_dump($report_id);
//$class=  explode('-', $class);
//$class=$class['1']."(".$class['0'].")";
$sql_user="select `username` from `user` where `user_id` = (select `created_user` from `course` where `code` = '$n_cidReq') ";
$create_user=  Database::getval($sql_user,__FILE__, __LINE__ ); 
//图片
//$sql_img="";
$sql_img="select `code` from `course_category` where `id` = (select `category_code` from `course` where `code` = '$n_cidReq')";
$img=Database::getval($sql_img,__FILE__, __LINE__ );
//echo '<>'
//下载文档
if (is_equal ( $action, "documents" ) && is_equal ( $_GET ['todo'], 'download' )) download_document ( $_course, getgpc ( 'id', 'G' ) );

$objStat = new ScormTrackStat ();
$isCourseAdmin = CourseManager::is_course_admin ( $user_id );

//是否为有效期内课程
$condition = "course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "'";
if ($is_course_user) {
  $sql = "SELECT is_valid_date,is_pass FROM $view_course_user WHERE " . $condition;
 	list ( $is_valide_date_course, $learn_status ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
	if (isset ( $is_valide_date_course ) && $is_valide_date_course == 0 && ! $isCourseAdmin && ! api_is_platform_admin ()) api_redirect ( "learning_center.php" );
}

$sql = "UPDATE $tbl_course_user SET access_times=access_times+1 WHERE " . $condition;
api_sql_query ( $sql, __FILE__, __LINE__ );

$sql = "SELECT COUNT(*) FROM " . $table_courseware . "  WHERE visibility=1 AND cc=" . Database::escape ( $course_code );
$cw_count = Database::get_scalar_value( $sql, __FILE__, __LINE__ );
if (empty ( $cw_count )) $action = 'documents';   //changzf

//$last_learning = $objStat->get_last_scorm_lp_item ( $cidReq, api_get_user_id () );
$sql = "SELECT * FROM $table_courseware WHERE cc='" . escape ( $course_code ) . "' AND  visibility=1 AND cw_type='scorm' ORDER BY id ";
$start_cw = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
if ($start_cw) {
	$start_url = api_get_path ( WEB_SCORM_PATH ) . 'lp_controller.php?cidReq=' . $course_code . '&action=read&lp_id=' . $start_cw ["attribute"] . '&cw_id=' . $start_cw ["id"];
}

$sql = "SELECT COUNT(*) FROM " . $tbl_document . "  AS docs WHERE docs.path!='/learnpath' AND filetype='file' AND docs.cc=" . Database::escape ( $course_code );
$doc_cnt = Database::get_scalar_value ( $sql, __FILE__, __LINE__ );

//课程公告
$sql_notice = CourseAnnouncementManager::get_announcemet_list_sql ( $user_id, $course_code, '0000-00-00 00:00:00' ) . " ORDER BY t1.end_date DESC";
//echo $sql_notice;
$result_notice = api_sql_query ( $sql_notice, __FILE__, __LINE__ );
$notice_cnt = Database::num_rows ( $result_notice );

//课程作业
$sql_assignment = "SELECT t1.id,t1.title, IF(published_time='0000-00-00 00:00:00','',published_time) as published_time,t1.creation_time,deadline,is_published,	t1.assignment_type FROM " . $tbl_assignment . " as t1 ";
$sql_assignment .= "WHERE assignment_type='INDIVIDUAL' and is_published=1  AND t1.cc='" . escape ( $course_code ) . "' ";
$sql_assignment .= "ORDER BY t1.deadline desc ";
$result_assignment = api_sql_query ( $sql_assignment, __FILE__, __LINE__ );
$assignment_cnt = Database::num_rows ( $result_assignment );

$crs_progress = round ( $objStat->get_course_progress ( $course_code, $user_id ) ) . '%';

//是否有毕业考试
$display_exam_btn = $have_exam = FALSE;
if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true') {
	$exam_info = $objStat->get_course_exam_info ( $course_code );
	if ($exam_info) {
		$exam_url = WEB_QH_PATH . 'quiz_intro.php?' . api_get_cidreq () . '&exerciseId=' . $exam_info ['id'];
		$is_exam_pass = Exercise::is_user_pass_exam ( $exam_info ['id'], $user_id );
		//echo $is_exam_pass;
		$display_exam_btn = ($is_exam_pass ? FALSE : TRUE);
		$have_exam = TRUE;
	}
} else {
	$display_exam_btn = FALSE;
}
//
//$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
//$interbreadcrumb [] = array ("url" => 'learning_center.php', "name" => "学习中心" ); 
//$nameTools = $_course ["name"];
include_once ("inc/page_header.php");

if ($display_exam_btn) {
	?>

<script type="text/javascript">
	var exam_url="<?=$exam_url?>";
	$(document).ready( function() {
		$("#applyFinish").click(function(){
			var code="<?=$course_code?>";
			var txt = '您确认学完该门课程,并立即进行毕业考试吗?<input type="hidden" id="course_code" name="course_code" value="'+ code +'" />';
			$.prompt(txt,{
				buttons:{'确定':true, '取消':false},
				callback: function(v,m,f){
					if(v){
						if(exam_url!="")
						location.href=exam_url;
					}
				}
			});
		});
	});
        
       

</script>


<?php
}
display_tab ( TAB_LEARNING_CENTER ); 
echo getgpc('action1');  
echo getgpc('id');             
if (is_equal ( $action, "introduction" )) {
    ?>
        
        <?php
    include ("course_modules/introduction.php");
} //END: 课程介绍
if ($is_course_user) {
    //课程资料文档下载
    //课程测验
    if (is_equal ( $action, "quiz" )) include ("course_modules/quiz.php");

    //课程公告消息
    if (is_equal ( $action, "notice" )) include ("course_modules/notice.php");

    //课程作业
    if (is_equal ( $action, "assignment" )) include ("course_modules/assignment.php");

    //学习进度统计
    if (is_equal ( $action, "progress" )) {
        include ("course_modules/progress.php");
        //AAA 关闭拓朴，清除文件锁
        $current_username=api_get_user_name();
    }
}
            ?>
</body>
</html>