<?php

include_once ('question.class.php');
include_once ('answer.class.php');
include_once ('exercise.class.php');
$language_file = 'exercice';
include_once ('../inc/global.inc.php');

include_once ('exercise.lib.php');
//api_protect_quiz_script ();

$delete = getgpc ( 'delete', 'G' );
$fromExercise = getgpc ( 'fromExercise', 'G' );
$exerciseId = intval ( getgpc ( 'exerciseId', 'G') );
$pool_id = intval ( getgpc ( 'pool_id' ));
$redirect_url = 'main/exam/pool_iframe.php';
$close_url ='main/exercice/question_base.php';

api_session_unregister ( 'objExercise' );
api_session_unregister ( 'objQuestion' );
api_session_unregister ( 'objAnswer' );
api_session_unregister ( 'questionList' );
api_session_unregister ( 'exerciseResult' );

if ($delete && is_equal ( $_GET ['action'], 'del_question' )) {
	 $objQuestionTmp = Question::read ( $delete );
	//if ($objQuestionTmp && is_object ( $objQuestionTmp )) {
		$delete_rtn = $objQuestionTmp->delete ();
		//if ($delete_rtn == 101)
		//	Display::display_msgbox ( '该试题已在使用中,不允许删除!', $close_url, 'warning' );
		//elseif ($delete_rtn == 102)
		//	Display::display_msgbox ( '您只有删除权限范围内的试题!', $close_url, 'warning' );
		//elseif ($delete_rtn == SUCCESS)
		//	Display::display_msgbox ( '试题删除成功', $close_url);
		//else Display::display_msgbox ( '试题删除失败!', $close_url, 'error' );
	//}
 	tb_close('question_base.php?pool_id='.getgpc("pool_id","G"));
	unset ( $objQuestionTmp );
}

$htmlHeadXtra [] = Display::display_thickbox ();
$nameTools = get_lang ( 'QuestionPoolManagement' );
include_once ('../inc/header.inc.php');

if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'delete' :
			$number_of_selected_items = count ( $_POST ['id'] );
			$number_of_deleted_items = 0;
			foreach ( $_POST ['id'] as $index => $item_id ) {

				$objQuestionTmp = Question::read ( $item_id );
				if ($objQuestionTmp && $objQuestionTmp->delete () == SUCCESS) $number_of_deleted_items ++;
				//unset ( $objQuestionTmp );
				//echo $item_id."<br>";
			}
			//if ($number_of_selected_items == $number_of_deleted_items) {
			//	Display::display_msgbox ( get_lang ( 'SelectedQuestionDeleted' ), $redirect_url );
			//} else {
			//	Display::display_msgbox ( get_lang ( 'SomeQuestionNotDeleted' ), $redirect_url, 'warning' );
			//}
			//echo 'sssssssssss';
			break;
        case 'batchMoveTo' :
            $number_of_selected_items = count ( $_POST ['id'] );
            if ($number_of_selected_items > 0) {
                $id_str = implode ( ",", $_POST ['id'] );
                $form = new FormValidator ( 'batch_change_dept', 'post' );
                $form->addElement ( "hidden", "action", 'batchMoveToSave' );
                $form->addElement ( "hidden", "id_str", $id_str );
                $form->addElement ( "hidden", "pool_id", $pool_id );
                $renderer = $form->defaultRenderer ();
                $renderer->setElementTemplate ( '<span>{element}</span> ' );
                $form->addElement ( 'static', 'text1', null, get_lang ( "PlsSelectThePoolMoveTo" ) );
                $tbl_exam_question_pool = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION_POOL );
                $sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . "ORDER BY display_order ASC";
                $all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
                //var_dump($all_pools);
                $form->addElement ( 'select', 'to_pool_id', get_lang ( "QuestionPool" ), $all_pools, array ('id' => "pool_id", 'style' => 'min-width:150px;height:30px;border:1px solid #999' ) );
                //$form->addRule ( 'to_pool_id', get_lang ( 'ThisFieldIsRequired' ), 'required' );
                $form->addElement ( 'submit', 'submit', get_lang ( 'Save' ), 'class="inputSubmit"' );
                $form->addElement ( 'button', 'btn', get_lang ( 'Cancel' ), 'class="cancel" onclick="javascript:location.href=\'question_base.php?pool_id=' . $pool_id . '\';"' );
                echo '  <aside id="sidebar" class="column exercice open">
                                        <div id="flexButton" class="closeButton close">
                                        </div>
                                        </aside>';
                echo '<div class="actions" style=" width:50%;float:left;margin-top:50px;margin-left:100px">';
                $form->display ();
                echo '已选择的题目总数:' . $number_of_selected_items;
                echo '</div>';
            } else {
                Display::display_msgbox ( get_lang ( 'PlsSelectedQuestions' ), $redirect_url, 'warning' );
            }
            exit ();
            break;


        case 'batchMoveToSave' :
			$id_str = getgpc ( 'id_str', 'P' );
			$to_pool_id = intval ( getgpc ( 'to_pool_id', 'P') );
			if ($id_str && $to_pool_id) {
				$sql_data = array ('pool_id' => $to_pool_id );
				$sql = Database::sql_update ( $TBL_QUESTIONS, $sql_data, " id IN (" . $id_str . ")" );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				Display::display_msgbox ( get_lang ( 'SelectedQuestionInPoolChanged' ), $redirect_url );
			} else {
				Display::display_msgbox ( '请选择一个目标题库', $redirect_url, 'warning' );
			}
			break;
	}
}

function get_sqlwhere() {
	$sql = '';
	if (is_not_blank ( $_GET ['pool_id'] )) {
		$sql .= " AND pool_id='" . Database::escape_string ( intval ( getgpc ( 'pool_id', 'G' )) ) . "'";
	}
	if (is_not_blank ( $_GET ['question_type'] )) {
		$sql .= " AND type='" . Database::escape_string ( getgpc ( 'question_type', 'G' ) ) . "'";
	}
	if (is_not_blank ( $_GET ['level'] )) {
		$sql .= " AND level=" . Database::escape ( getgpc ( 'level' ) );
	}

	if(isset($_GET['keyword']) && $_GET['keyword']=='输入搜索关键词'){
        	$_GET['keyword']='';
    	}
        else if (is_not_blank ( $_GET ['keyword'] )) {
            $keyword = Database::escape_str ( getgpc ( 'keyword', "G" ), TRUE );
            $sql .= " AND (question_code LIKE '%" . $keyword . "%' OR question LIKE '%" . $keyword . "%')";
            }




return $sql;
}

function get_number_of_data() {
	global $TBL_QUESTIONS;
	$sql = "SELECT COUNT(id) AS total_number_of_items FROM $TBL_QUESTIONS WHERE 1 ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	return Database::get_scalar_value ( $sql );
}

function get_data($from, $number_of_items, $column, $direction) {
	global $TBL_QUESTIONS, $exerciseId, $_question_types, $_question_level;
	$fields = array ('t1.id', 'type', 'question','t2.pool_name', 'level', 'ponderation', 'cc', 't1.id', 't1.created_user','t1.pool_id');
	$sql = "SELECT SQL_CACHE " . sql_field_list ( $fields ) . " FROM $TBL_QUESTIONS AS t1 left join exam_question_pool as t2 on t2.id=t1.pool_id WHERE pid=0 ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$table_data = array ();
	while ( $row = Database::fetch_array ( $res, 'NUM' ) ) {

		$row_render = array ();
		$row_render [0] = $row [0];
		$type = $row [1];
		$created_user = $row [7];
		unset ( $row [7] );
		$question_type = $_question_types [$row [1]];

		if ($row [1] == COMBO_QUESTION or $row [1] == CLOZE_QUESTION) {
			$question_type .= "&nbsp;(" . Question::get_combo_sub_question_count ( $row [0] ) . ")";
		}
		$row_render [1] = $question_type;
		$row_render [2] = api_trunc_str2 ( strip_tags ( $row [2] ), 40 );
        if($row [9] == 0){$row_render [3]="无";}
        else{$row_render [3] = $row [3];}
		$row_render [4] = $_question_level [$row [4]];
		$row_render [5] = $row [5];
		//$row_render [6] = $row [5];
		//$row_render [] = $row ['pool_name'];
		//$row_render [] = api_trunc_str2 ( $row ['question_code'], 10 );
		$action_html = "";
		if ($type == COMBO_QUESTION) {
			$action_html .= '<a href="question_update_combo.php?pid=' . $row [0] . '">' . Display::return_icon ( 'wizard_small.gif', get_lang ( 'Build' ), array ('align' => 'absmiddle' ) ) . '</a>';
		}
		if ($type == CLOZE_QUESTION) {
			$action_html .= '<a href="question_update_cloze.php?pid=' . $row [0] . '">' . Display::return_icon ( 'wizard_small.gif', get_lang ( 'Build' ), array ('align' => 'absmiddle' ) ) . '</a>';
		}
		//if (can_do_my_bo ( $created_user )) {
			$action_html .= link_button ( 'edit.gif', 'Modify', 'question_update.php?action=edit&qid=' . $row [0] . '&answerType=' . $row [1], '90%', '80%', FALSE );
			$href = 'question_base.php?action=del_question&delete=' . $row [0].'&pool_id='.getgpc ( 'pool_id' );
			$action_html .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', $href );
		//}
		$row_render [6] = $action_html;
		$table_data [] = $row_render;
	}

	return $table_data;
}

$form = new FormValidator ( 'question_pool_form', 'get' );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label} {element}</span> ' );
$form->addElement ( 'hidden', 'pool_id', $pool_id );
$form->addElement ( 'text', 'keyword', get_lang ( 'Question' ), array ('class' => 'inputText', 'style' => 'width:150px','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'select', 'question_type', get_lang ( 'QuestionType' ), $_question_types );
//pool seach
$sql="select `id`, `pool_name` from `exam_question_pool`";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$pools = array ();
while ($row = Database::fetch_array ( $result, 'ASSOC' )){

    $pools[$row['id']]=$row['pool_name'];

}
array_unshift($pools, '全部');
//$form->addElement ( "select", "pool", "题库", $pools, array ("style" => 'width:10%' ) );
$form->addElement ( "select", "level", get_lang ( "DifficultyLevel" ), $_question_level, array ("style" => 'width:10%' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );

$parameters = array ('question_type' => getgpc ( 'question_type', 'G' ), 'keyword' => getgpc ( 'keyword', 'G' ), 'pool_id' => $pool_id );
$table = new SortableTable ( 'question_base', 'get_number_of_data', 'get_data', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );
$header_idx = 0;
$table->set_header ( $header_idx ++, '', false );
$table->set_header ( $header_idx ++, get_lang ( 'QuestionType' ), false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Question' ), false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, "当前题库", false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'DifficultyLevel' ), true, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'QuestionWeighting' ), true, null, array ('width' => '15%' ) );
//$table->set_header ( $header_idx ++, get_lang ( 'Course' ), true, null, array ('width' => '17%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '15%' ) );
//$actions = array ( 'delete' => get_lang ( 'BatchDelete' ) );
$actions = array ( 'delete' => get_lang ( 'BatchDelete' ),'batchMoveTo' => get_lang ( 'BatchChangeQuestionPool' ) );
$table->set_form_actions ( $actions );
//$table->set_dispaly_style_navigation_bar(NAV_BAR_BOTTOM);

if($platform==3){
    $nav='exercices';
}else{
    $nav='exercice';
}
?>
<aside id="sidebar" class="column exercices open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/exercice/exercice.php">考试管理</a> &gt; 所有考题管理</h4>
    <div class="managerSearch">
        <?php
        echo "<span class='seachtxt right'>";
        echo link_button ( 'backup.gif', 'ImportQuestions', 'question_base_import.php?pool_id=' .intval (  getgpc ( 'pool_id', 'G' )), '90%', '50%', TRUE, TRUE );
        echo str_repeat ( "&nbsp;", 4 ) . link_button ( 'export_data.gif', 'ExportQuestions', 'question_base_export.php?pool_id=' . $pool_id, '50%', '50%', TRUE, TRUE );
       // Display::display_icon ( 'i.gif' );
        echo "</span>";
	echo str_repeat ( "&nbsp;", 2 ) ./* Display::return_icon ( 'save.png', get_lang ( 'NewQuestion' ), array ('align' => 'absbottom' ) ) .*/ '<b>' . get_lang ( "NewQuestion" ) . '</b>:';
        echo str_repeat ( "&nbsp;", 4 ) . link_button ( 'question_add.gif', $_question_types [UNIQUE_ANSWER], 'question_update.php?action=add&answerType=' . UNIQUE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [MULTIPLE_ANSWER], 'question_update.php?action=add&answerType=' . MULTIPLE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [TRUE_FALSE_ANSWER], 'question_update.php?action=add&answerType=' . TRUE_FALSE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        //if ($_configuration ['enable_question_fillblanks']) echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [FILL_IN_BLANKS], 'question_update.php?action=add&answerType=' . FILL_IN_BLANKS . '&pool_id=' . $pool_id, '90%', '80%' );
        if ($_configuration ['enable_question_freeanswer']) echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [FREE_ANSWER], 'question_update.php?action=add&answerType=' . FREE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );

        if($platform==3){
            if ($_configuration ['enable_question_freeanswer']) echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', "实战题", 'question_update.php?action=add&answerType=' . COMBAT_QUESTION . '&pool_id=' . $pool_id, '90%', '80%' );
        }
//echo str_repeat ( "&nbsp;", 2 ) . link_button ( '', $_question_types [COMBO_QUESTION], 'question_update.php?action=add&answerType=' . COMBO_QUESTION . '&pool_id=' . $pool_id, 400, 720 );
//echo str_repeat ( "&nbsp;", 2 ) . link_button ( '', $_question_types [CLOZE_QUESTION], 'question_update.php?action=add&answerType=' . CLOZE_QUESTION . '&pool_id=' . $pool_id, 400, 720 );


       ?>
    </div>
    <div class="managerSearch">
        <?php $form->display ();?>
    </div>
       <article class="module width_full hidden">
          <form name="form1" method="post" action="">
              <?php $table->display ();?>
          </form>
       </article>
        </div>
    </div>
</section>
</body>
</html>
