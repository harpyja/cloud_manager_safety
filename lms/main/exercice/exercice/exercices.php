<?php
/*
 测验列表
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');
//$cidReset = TRUE;
$language_file = array ('exercice', 'admin' );
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');

//api_protect_quiz_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$TBL_USER = Database::get_main_table ( TABLE_MAIN_USER );
$TBL_ITEM_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );

$show = (isset ( $_GET ['show'] ) && $_GET ['show'] == 'result') ? 'result' : 'test';
$type = getgpc ( 'type', 'G' );
$key=getgpc ( 'key', 'G' );
if($key!=null){
    
    $ty=Database::getval ("select `id` from `exam_type` where name like '%".$key."%'", __FILE__, __LINE__ );
    
    if($ty==null){
        $type=-3; 
    }  else {
        $type=$ty;
    }
   
}
$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$exam_id = (isset ( $_REQUEST ['exam_id'] ) ? intval(getgpc ( 'exam_id' )) : "");
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';
$exerciseId = Database::escape_string ( intval(getgpc ( 'exerciseId' )) );
$choice = getgpc ( 'choice' );

api_session_unregister ( 'objExercise' );
api_session_unregister ( 'objQuestion' );
api_session_unregister ( 'objAnswer' );
api_session_unregister ( 'questionList' );
api_session_unregister ( 'exerciseResult' );


if (isset($exerciseId) && $_GET['choice']=='able') {
	//$sql = "SELECT title FROM $TBL_EXERCICES WHERE id='" . $exerciseId . "'";
	//$exerciseTitle = Database::get_scalar_value ( $sql );
                $able_sql="UPDATE  `vslab`.`exam_main` SET  `active` =  '0' WHERE  `exam_main`.`id` =".$exerciseId;
                api_sql_query($able_sql,__FILE__,__LINE__);
                tb_close('exercices.php');
}

if (is_equal ( $_GET ['choice'], 'export' )) {
	require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
	$data_header = array (get_lang ( 'FirstName' ), get_lang ( 'LoginName' ), get_lang ( 'OfficialCode' ), get_lang ( 'UserInDept' ), get_lang ( 'ExamStartDate' ), get_lang ( 'AnswerTime' ), get_lang ( 'Score' ), get_lang ( 'StudentScore' ) );
	$export_data = get_result_date ( $exerciseId, TRUE );
	//var_dump($export_data);exit;
	$filename = '在线考试_' . $exerciseTitle . '_' . date ( 'Ymd' ); //导出文件名
	array_unshift ( $export_data, $data_header );
	Export::export_data ( $export_data, $filename, 'xls' );
}

$redirect = 'main/exercice/exercices.php?type=' . $type;
if (! empty ( $choice )) { //管理操作
	$objExerciseTmp = new Exercise ();
	if ($objExerciseTmp->read ( $exerciseId )) {
		switch ($choice) {
			case 'delete' : // 删除测验
				$objExerciseTmp->delete ( FALSE );
                                tb_close('exercices.php');
				//Display::display_msgbox ( get_lang ( 'ExerciseDeleted' ),'main/exercice/exercices.php' );
				break;
			case 'enable' : // 显示
				if ($objExerciseTmp->selectNbrQuestions ()) {
					$objExerciseTmp->enable ();
					$objExerciseTmp->save ();
                                        tb_close('exercices.php');
//					Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect );
				} else {
					Display::display_msgbox ( '没有设置题目的考试不允许发布!', $redirect, 'warning' );
				}
				break;
			case 'disable' : // 隐藏
				$objExerciseTmp->disable ();
				$objExerciseTmp->save ();
				//api_item_property_update ( $_course, TOOL_QUIZ, $exerciseId, "invisible" );
                                tb_close('exercices.php');
//				Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect );
				break;
		}
	}
	unset ( $objExerciseTmp );
}

//by changzf at 82-86 line
$htmlHeadXtra [] = Display::display_thickbox ();

if($type) $nameTools=''; 
// Display::display_header ( $nameTools, true );
include ('../inc/header.inc.php');

//测验试卷列表
$form = new FormValidator ( 'search_simple', 'get' );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$form->addElement ( 'hidden', 'type', $type );
$keyword_tip = get_lang ( 'ExerciseName' );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:45%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
$form->addElement ( 'static', 'exam_type', $nameTools);


$sql = "SELECT id,title,type,active,description,max_attempt,max_duration,cc,id,created_user FROM $TBL_EXERCICES AS ce
        WHERE active<>'-1' ".($type==''?'':"and type=".escape ( $type ));

$g_keyword=  getgpc('keyword');
if(isset($g_keyword) && $g_keyword=='输入搜索关键词'){
    $g_keyword='';
}
if (is_not_blank ( $g_keyword )) {
    $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
    $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%'
        OR title LIKE '%" . trim ( $keyword ) . "%')";
}
$sql.=($g_keyword==''?'':$sql_where);
$sql.=" ORDER BY id ASC";
//echo $sql;
$result = api_sql_query ( $sql, __FILE__, __LINE__ );

//$table_header [] = array ("", 'width="30"' );
$table_header [] = array (get_lang ( 'ExerciseName' ),false, array ('style' => 'width:450px' ));
$table_header [] = array (get_lang ( '竞赛名称' ) );
$table_header [] = array (get_lang ( 'QuizAllowedDuration' ));
$table_header [] = array (get_lang ( 'ExerciseAttempts' ), 'width="120"' );
$table_header [] = array (get_lang ( 'QuestionCount' ), null, array ('width' => "80" ) );
$table_header [] = array (get_lang ( 'QuizTotalScore' ), 'width="80"' );
//$table_header[] = array(get_lang('AverageScore'),true);
$table_header [] = array (get_lang ( 'isPublishedNow' ), 'width="80"' );
$table_header [] = array (get_lang ( 'Preview' ), 'width="30"' );
$table_header [] = array (get_lang ( '安排考生' ), 'width="30"' );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('width' => "140" ) );

//$total_rows=Database::num_rows($result);
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
	$tbl_row = array ();
	//$tbl_row [] = $row ['id'];
	//原来exercice_submit.php
	//$tbl_row [] = '<a href="exercice_intro.php?' . api_get_cidreq () . "&exerciseId=" . $row ['id'] . '" ' . (! $row ['active'] ? ' class="invisible"' : "") . ">" . $row ['title'] . '</a>';
	$tbl_row [] = '<span ' . (! $row ['active'] ? ' class="invisible"' : "") . ">" . $row ['title'] . '</span>';
	/*		if ($row ['type'] == 1) {
			$tbl_row [] = get_lang ( 'ExamProperty1' );
		} else {
			$sql = "SELECT title FROm " . Database::get_main_table ( TABLE_MAIN_COURSE ) . " WHERE code=" . Database::escape ( $row ['cc'] );
			$course_title = Database::get_scalar_value ( $sql );
			$tbl_row [] = get_lang ( 'ExamProperty2' ) . ' (' . $course_title . ')';
		}*/
	 $ty=Database::getval ("select `name` from `exam_type` where `id`=".$row['type'], __FILE__, __LINE__ );
	
	$tbl_row [] = $ty;
	$tbl_row [] = $row ['max_duration'] == 0 ? get_lang ( "Infinite" ) : ($row ['max_duration'] / 60) . "&nbsp;" . get_lang ( "Minites" );
	
	$tbl_row [] = ($row ['max_attempt'] == 0 ? get_lang ( "Infinite" ) : $row ['max_attempt']);
	
	$sqlquery = "SELECT count(*) FROM $TBL_EXERCICE_QUESTION WHERE `exercice_id` = '" . $row ['id'] . "'";
	$questionCount = Database::get_scalar_value ( $sqlquery );
	$tbl_row [] = $questionCount;
	
	$tbl_row [] = Exercise::get_quiz_total_score ( $row ['id'] );
	//$tbl_row[]=get_average_score($row['id']);
	

	if ($row ['active']) {
		//$visible_html = '<a href="exercices.php?choice=disable&exerciseId='.$row['id'].'">'. Display::return_icon('right.gif', get_lang('Deactivate')).'</a> ';
//		$visible_html =Display::return_icon ( 'right.gif', get_lang ( 'QuizPublished' ) ) . "&nbsp;" . get_lang ( 'QuizPublished' );
//		$visible_html = '<a href="exercices.php?choice=able&exerciseId='.$row ['id'].'>'.Display::return_icon ( 'right.gif', get_lang ( 'QuizPublished' ) ).'</a>';
        $visible_html = '<a href="exercices.php?choice=able&exerciseId=' . $row ['id'] . '" onclick="return confirm(\'' . get_lang ( "注意：您确定要关闭这份考试试卷吗?" ) . '\');">' .Display::return_icon ( 'right.gif', get_lang ( '点击关闭考试' ) ) . '</a>';

    } else {
		$visible_html = '<a href="exercices.php?choice=enable&exerciseId=' . $row ['id'] . '" onclick="return confirm(\'' . get_lang ( "QuizPublishConfirm" ) . '\');">' .Display::return_icon ( 'wrong.gif', get_lang ( 'ClickToPublishQuiz' ) ) . '</a>';
	}
	$tbl_row [] = $visible_html;
	
	//预览
	//$tbl_row [] = $questionCount > 0 ? '&nbsp;&nbsp;' . link_button ( 'preview.gif', 'Preview', 'exercise_preview.php?type=exercise&id=' . $row ["id"], '96%', '96%', FALSE ) : '';
	$tbl_row [] = $questionCount > 0 ? '&nbsp;&nbsp;' . icon_href( 'preview.gif', 'Preview', '../exam/paper_preview.php?exam_id=' . $row ["id"],'_blank') : '';

    $tbl_row [] = '&nbsp;&nbsp;' . link_button ( 'add_user_big.gif', 'ExamInfoSetting', '../exam/manage/tobe_arranged.php?modifyExercise=yes&exam_id=' . $row ["id"], '90%', '80%', FALSE );

	$action_html = "";
	//V2.4
	/*if ($row ['a`ctive'] != 1) {
			$action_html .= '&nbsp;&nbsp;' . icon_href ( 'wizard.gif', 'BuildQuiz', 'admin.php?exerciseId=' . $row ["id"] );
		} else {
			$action_html .= '&nbsp;&nbsp;' . Display::return_icon ( 'wizard_gray.gif', get_lang ( 'BuildQuiz' ), array ('style' => 'vertical-align: middle;' ) );
		}*/
	
	//安排考生
	//$action_html .= '&nbsp;&nbsp;' . link_button ( 'edit_group.gif', 'ArrageExaminees', '../exam/manage/have_arranged.php?exam_id=' . $row ['id'], '94%', '90%', FALSE );
	
//dengxin   
	//$action_html .= '&nbsp;&nbsp;' . link_button ( 'statistics.gif', 'ExamResultQuery', 'exercise_result.php?exerciseId=' . $row ["id"], '90%', '96%', FALSE );
	
//	if (can_do_my_bo ( $row ['created_user'] )) {
		//编辑
		$action_html .= '&nbsp;&nbsp;' . link_button ( 'exercise22.png', 'ExamInfoSetting', 'exercise_admin.php?modifyExercise=yes&exerciseId=' . $row ["id"], '90%', '80%', FALSE );
		
		//删除
		$href = 'exercices.php?choice=delete&exerciseId=' . $row [id];
		$action_html .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'AreYouSureToDeleteExam', 'Delete', $href );
		
		$tobecorrect_user_count = Exercise::stat_exam_tobecorrect_user_count ( $row ["id"] );
                //用户提交的答案列表for  dengxin  
//		if ($tobecorrect_user_count > 0) {
//			$action_html .= '&nbsp;&nbsp;' . icon_href ( 'plugin.gif', 'ExamSubPapers', '../exam/manage/tobe_corrected.php?exam_id=' . $row ["id"], '_blank' ) . '(' . $tobecorrect_user_count . ')';
//		}
	
	//}
	
	$tbl_row [] = $action_html;
	$table_data [] = $tbl_row;
}
$sorting_options = array ('column' => 0, 'default_order_direction' => 'DESC' );
$query_vars = array ();
if (is_not_blank ( $_REQUEST ['keyword'] )) $query_vars ['keyword'] = getgpc ( 'keyword' );
if (is_not_blank ( $_REQUEST ['type'] )) $query_vars ['type'] = getgpc ( 'type' );


if($platform==3){
    $nav='exercices';
}else{
    $nav='exercice';
}
?>
<aside id="sidebar" class="column <?=$nav?> open">
    <div id="flexButton" class="closeButton close">
    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
    <a href="<?=URL_APPEDND;?>/main/exercice/exercices.php">所有的试卷</a>
        <?php
        if($type!=null){
       $ty=Database::getval ("select name from exam_type where id=".$type, __FILE__, __LINE__ );
         echo '&gt;'.$ty;
        }
        ?>
    </h4>
    <div class="managerSearch">
        <span class="searchtxt right">
            <?php
                echo str_repeat ( '&nbsp;', 2 ), link_button ( 'new_test.gif', 'NewEx', 'exercise_admin.php?type=' . $type, '90%', '80%' );
            ?>
        </span>
            <?php $form->display ();?>
                    <form action="<?php echo api_get_self ();?>" method="get">
            竞赛名称<?php 
                                        $sql="select `id`, `name` from `exam_type`";
                            $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                             $tbl_row = array ();
                             $tbl_row['请选择竞赛名称']='请选择竞赛名称';
                            while ($row = Database::fetch_array ( $result, 'ASSOC' )){
                               
                                $tbl_row[$row['id']]=$row['name'];
                                
                            }
                            $str="<select name='key'>";
                            foreach ($tbl_row as $va){
                               $str.="<option value='".$va."'>$va</option>"; 
                            }
                            $str.="</select>";
                            echo $str;
            
            ?><input type="submit" value="查询" class="inputSubmit">
        </form>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
               <?php  Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, $form_actions, NAV_BAR_BOTTOM );
                ?>
        </form>
    </article>
</section>
</body>
</html>
