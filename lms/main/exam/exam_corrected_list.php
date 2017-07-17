<?php
$language_file = array ('exercice', 'admin' );
$cidReset = true;
include_once ('../inc/global.inc.php');
api_block_anonymous_users ();
require_once (api_get_path ( SYS_CODE_PATH ) . "exercice/exercise.lib.php");

$user_id = api_get_user_id ();
$objExam = new Exercise ();
$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$type = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : 0);
$id = (isset ( $_REQUEST ['id'] ) ? intval(getgpc ( 'id' )) : "");
$htmlHeadXtra [] = Display::display_thickbox ();
$tool_name = get_lang ( 'Exam' );
include ('../inc/header.inc.php');

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$keyword_tip = get_lang ( 'ExerciseName' );
$form->addElement ( 'text', 'keyword', get_lang ( 'ExerciseName' ), array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );


$ty = "SELECT id,name FROM  `exam_type` ";
$typ = api_sql_query ( $ty, __FILE__, __LINE__ );
$datatype = api_store_result_array ( $typ );
$all_types =array();
foreach($datatype as $k => $v){
     
    //var_dump ($v);
    $all_types[$v['id']] = $v['name'];
}

$form->addElement ( 'select', 'type', get_lang ( "Type" ), $all_types );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table_header [] = array (get_lang ( 'ExerciseName' ), true );
$table_header [] = array (get_lang ( 'ExamProperty' ), true );
$table_header [] = array (get_lang ( 'QuizAllowedDuration' ), true );
$table_header [] = array (get_lang ( 'ExerciseAttempts' ), true );
$table_header [] = array (get_lang ( 'QuestionCount' ), true );
$table_header [] = array (get_lang ( 'QuizTotalScore' ), true );
$table_header [] = array (get_lang ( 'ExamAttemptUserCount' ) );
$table_header [] = array (get_lang ( 'ExamToCorrectUserCount' ) );
$table_header [] = array (get_lang ( 'Preview' ),false,null,array('width'=>'5%'));
$table_header [] = array (get_lang ( 'Actions' ),false,null,array('width'=>'5%'));

$query_vars = array ();
$sql_where = " t1.active=1 ";
if ($type) $sql_where .= " AND type=" . Database::escape ( $type );

if (! api_is_platform_admin ()) {
	$sql_where .= " AND t1.exam_manager= " . Database::escape ( $user_id );
}

$g_keyword=  getgpc('keyword','G');
if (isset ( $g_keyword ) && ! empty ( $g_keyword )) {
	if($g_keyword=='输入搜索关键词'){
	    $g_keyword='';
	}
	$query_vars ['keyword'] = $g_keyword;
	$keyword = trim ( Database::escape_str ( $g_keyword, TRUE ) );
	if (! empty ( $keyword )) {
		$sql_where .= " AND  (title LIKE '%" . $keyword . "%')";
	}
}
if (isset ( $_GET ['type'] ) && is_not_blank ( $_GET ['type'] )) $query_vars ['type'] = intval(getgpc ( 'type', 'G' ));

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$sql = "SELECT t1.* FROM " . $TBL_EXERCICES . " AS t1 WHERE 1 ";
if ($sql_where) $sql .= " AND " . $sql_where;
$sql .= " ORDER BY t1.id DESC";
//echo $sql;
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
$datalist = api_store_result_array ( $rs );
foreach ( $datalist as $data ) {
	//var_dump($data);exit;
	$tbl_row = array ();
	$exam_id = intval ( $data ['id'] );
	$tbl_row [] = $data ['title'];
        
	$tbl_row [] = Database::getval ("select `name` from `exam_type` where `id`=".$data ['type'], __FILE__, __LINE__ );
	 
	$tbl_row [] = $data ['max_duration'] == 0 ? get_lang ( "Infinite" ) : ($data ['max_duration'] / 60) . "&nbsp;" . get_lang ( "Minites" );
	$tbl_row [] = ($data ['max_attempt'] == 0 ? get_lang ( "Infinite" ) : $data ['max_attempt']);
	$sqlquery = "SELECT count(*) FROM $TBL_EXERCICE_QUESTION WHERE `exercice_id` = '" . $exam_id . "'";
	$questionCount = Database::get_scalar_value ( $sqlquery, __FILE__, __LINE__ );
	$tbl_row [] = $questionCount;
	
	$tbl_row [] = Exercise::get_quiz_total_score ( $data ['id'] );
	
	$tbl_row [] = $objExam->stat_exam_attempt_user_count ( $data ["id"] );
	//$tobecorrect_user_count = $objExam->stat_exam_tobecorrect_user_count ( $data ["id"] );
	$tobecorrect_user_count = $objExam->stat_exam_tobecorrect_user_count ( $exam_id );
	$tbl_row [] = $tobecorrect_user_count;
	
	$tbl_row [] = $questionCount > 0 ? '&nbsp;&nbsp;' . link_button ( 'preview.gif', 'Preview', '../exercice/exercise_preview.php?type=exercise&id=' . $exam_id, '90%', '90%', FALSE ) : '';
	
	//考试管理
	$action='';
	$action .= '&nbsp;&nbsp;' . link_button ( 'statistics.gif', 'ExamResultQuery', '../exercice/exercise_result.php?exerciseId=' .$exam_id, '90%', '96%', FALSE );
	if ($tobecorrect_user_count > 0) {
		$action .= '&nbsp;&nbsp;' . icon_href ( 'plugin.gif', 'ExamSubPapers', 'manage/tobe_corrected.php?exam_id=' . $exam_id, '_blank' );
	}
	
	$tbl_row [] = $action;
	$table_data [] = $tbl_row;
}
unset ( $data, $tbl_row );


if($platform==3){
    $nav='exercices';
}else{
    $nav='exercice';
}
?>
<aside id="sidebar" class="column  exercices open">
    <div id="flexButton" class="closeButton close">
    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;<a href="<?=URL_APPEDND;?>/main/exam/pool_iframe.php">考试管理</a>  &gt; 考卷批改</h4>
    <div class="managerSearch">
    
           <?php

            $form->display ();

            ?>
       
    </div>
    <article class="module width_full hidden">
        <table cellspacing="0" cellpadding="0" class="p-table">
         <?php Display::display_sortable_table ( $table_header, $table_data, array (), array (), $query_vars, array (), NAV_BAR_BOTTOM );
            ?>
        </table>
    </article>
</section>
</body>
</html>