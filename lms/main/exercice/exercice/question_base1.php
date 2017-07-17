<?php

include_once ('question.class.php');
include_once ('answer.class.php');
include_once ('exercise.class.php');
$language_file = 'exercice';
include_once ('../inc/global.inc.php');

include_once ('exercise.lib.php');
api_protect_quiz_script ();

$delete = intval(getgpc ( 'delete', 'G' ));
$fromExercise = getgpc ( 'fromExercise', 'G' );
$exerciseId = intval(getgpc ( 'exerciseId', 'G' ));
$pool_id = intval(getgpc ( 'pool_id') );
$redirect_url = 'main/exam/pool_iframe.php';
$close_url ='main/exercice/question_base1.php';

api_session_unregister ( 'objExercise' );
api_session_unregister ( 'objQuestion' );
api_session_unregister ( 'objAnswer' );
api_session_unregister ( 'questionList' );
api_session_unregister ( 'exerciseResult' );

if ($delete && is_equal ( $_GET ['action'], 'del_question' )) {
	$objQuestionTmp = Question::read ( $delete );
	if ($objQuestionTmp && is_object ( $objQuestionTmp )) {
		$delete_rtn = $objQuestionTmp->delete ();
		if ($delete_rtn == 101)
			Display::display_msgbox ( '该试题已在使用中,不允许删除!', $close_url, 'warning' );
		elseif ($delete_rtn == 102)
			Display::display_msgbox ( '您只有删除权限范围内的试题!', $close_url, 'warning' );
		elseif ($delete_rtn == SUCCESS)
			Display::display_msgbox ( '试题删除成功', $close_url);
		else Display::display_msgbox ( '试题删除失败!', $close_url, 'error' );
	}
	unset ( $objQuestionTmp );
}

$htmlHeadXtra [] = Display::display_thickbox ();
$nameTools = get_lang ( 'QuestionPoolManagement' );
Display::display_header ();



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
				$sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . " WHERE id<>" . Database::escape ( $pool_id ) . "  ORDER BY display_order ASC";
				$all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
				//var_dump($all_pools);
				$form->addElement ( 'select', 'to_pool_id', get_lang ( "QuestionPool" ), $all_pools, array ('id' => "pool_id", 'style' => 'min-width:150px;height:22px;border:1px solid #999' ) );
				//$form->addRule ( 'to_pool_id', get_lang ( 'ThisFieldIsRequired' ), 'required' );
				$form->addElement ( 'submit', 'submit', get_lang ( 'Save' ), 'class="inputSubmit"' );
				$form->addElement ( 'button', 'btn', get_lang ( 'Cancel' ), 'class="cancel" onclick="javascript:location.href=\'question_base1.php?pool_id=' . $pool_id . '\';"' );
				echo '<div class="actions" style="width:100%;margin-bottom:15px;margin-top:10px">';
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
			$to_pool_id = intval(getgpc ( 'to_pool_id', 'P' ));
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
		$sql .= " AND pool_id='" . Database::escape_string ( intval(getgpc ( 'pool_id', 'G' )) ) . "'";
	}
	if (is_not_blank ( $_GET ['question_type'] )) {
		$sql .= " AND type='" . Database::escape_string ( getgpc ( 'question_type', 'G' ) ) . "'";
	}
	if (is_not_blank ( $_GET ['level'] )) {
		$sql .= " AND level=" . Database::escape ( getgpc ( 'level' ) );
	}
	if (is_not_blank ( $_GET ['keyword'] )) {
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
	$fields = array ('t1.id', 'type', 'question', 'level', 'ponderation', 'cc', 't1.id', 't1.created_user' );
	$sql = "SELECT SQL_CACHE " . sql_field_list ( $fields ) . " FROM $TBL_QUESTIONS AS t1  WHERE pid=0 ";
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
		$row_render [3] = $_question_level [$row [3]];
		$row_render [4] = $row [4];
		$row_render [5] = $row [5];
		//$row_render [] = $row ['pool_name'];
		//$row_render [] = api_trunc_str2 ( $row ['question_code'], 10 );
		$action_html = "";
		if ($type == COMBO_QUESTION) {
			$action_html .= '<a href="question_update_combo.php?pid=' . $row [0] . '">' . Display::return_icon ( 'wizard_small.gif', get_lang ( 'Build' ), array ('align' => 'absmiddle' ) ) . '</a>';
		}
		if ($type == CLOZE_QUESTION) {
			$action_html .= '<a href="question_update_cloze.php?pid=' . $row [0] . '">' . Display::return_icon ( 'wizard_small.gif', get_lang ( 'Build' ), array ('align' => 'absmiddle' ) ) . '</a>';
		}
		if (can_do_my_bo ( $created_user )) {
			$action_html .= link_button ( 'edit.gif', 'Modify', 'question_update.php?action=edit&qid=' . $row [0] . '&answerType=' . $row [1], '90%', '80%', FALSE );
			$href = 'question_base1.php?action=del_question&delete=' . $row [0];
			$action_html .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', $href );
		}
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
$form->addElement ( "select", "level", get_lang ( "DifficultyLevel" ), $_question_level, array ("style" => 'width:10%' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );

$parameters = array ('question_type' => getgpc ( 'question_type', 'G' ), 'keyword' => getgpc ( 'keyword', 'G' ), 'pool_id' => $pool_id );
$table = new SortableTable ( 'question_base', 'get_number_of_data', 'get_data', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );
$header_idx = 0;
$table->set_header ( $header_idx ++, '', false );
$table->set_header ( $header_idx ++, get_lang ( 'QuestionType' ), false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Question' ), false, null, array ('width' => '20%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'DifficultyLevel' ), true, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'QuestionWeighting' ), true, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Course' ), true, null, array ('width' => '17%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '15%' ) );
$actions = array ('batchMoveTo' => get_lang ( 'BatchChangeQuestionPool' ), 'delete' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );
//$table->set_dispaly_style_navigation_bar(NAV_BAR_BOTTOM);

//Display::display_footer ();?>

<aside id="sidebar" class="column exercice open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/exercice/exercice.php">考试管理</a> &gt; 所有考题管理</h4>
    <div class="managerSearch">
<!--        <span class="searchtxt"><img src="images/backup.gif" align="absmiddle">  <a href="#">导入试题</a></span>-->
<!--        <span class="searchtxt"><img src="images/export_data.gif" align="absmiddle">  <a href="#">导出试题</a></span>-->
<!--        &nbsp;&nbsp;|&nbsp;&nbsp;-->
<!--        <strong>新增题目：</strong>-->
<!--        <span class="searchtxt"><img src="images/question_add.gif" align="absmiddle">  <a href="#">单选题</a></span>-->
<!--        <span class="searchtxt"><img src="images/question_add.gif" align="absmiddle">  <a href="#">多选题</a></span>-->
<!--        <span class="searchtxt"><img src="images/question_add.gif" align="absmiddle">  <a href="#">判断题</a></span>-->
<!--        <span class="searchtxt"><img src="images/question_add.gif" align="absmiddle">  <a href="#">简答题</a></span>-->
        <?php

        echo link_button ( 'backup.gif', 'ImportQuestions', 'question_base_import.php?pool_id=' . intval(getgpc ( 'pool_id', 'G' )), '50%', '70%', TRUE, TRUE );
        echo str_repeat ( "&nbsp;", 4 ) . link_button ( 'export_data.gif', 'ExportQuestions', 'question_base_export.php?pool_id=' . $pool_id, '50%', '70%', TRUE, TRUE );
        Display::display_icon ( 'i.gif' );
        echo str_repeat ( "&nbsp;", 2 ) ./* Display::return_icon ( 'save.png', get_lang ( 'NewQuestion' ), array ('align' => 'absbottom' ) ) .*/ '<b>' . get_lang ( "NewQuestion" ) . '</b>:';
        echo str_repeat ( "&nbsp;", 4 ) . link_button ( 'question_add.gif', $_question_types [UNIQUE_ANSWER], 'question_update.php?action=add&answerType=' . UNIQUE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [MULTIPLE_ANSWER], 'question_update.php?action=add&answerType=' . MULTIPLE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [TRUE_FALSE_ANSWER], 'question_update.php?action=add&answerType=' . TRUE_FALSE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
        if ($_configuration ['enable_question_fillblanks']) echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [FILL_IN_BLANKS], 'question_update.php?action=add&answerType=' . FILL_IN_BLANKS . '&pool_id=' . $pool_id, '90%', '80%' );
        if ($_configuration ['enable_question_freeanswer']) echo str_repeat ( "&nbsp;", 2 ) . link_button ( 'question_add.gif', $_question_types [FREE_ANSWER], 'question_update.php?action=add&answerType=' . FREE_ANSWER . '&pool_id=' . $pool_id, '90%', '80%' );
//echo str_repeat ( "&nbsp;", 2 ) . link_button ( '', $_question_types [COMBO_QUESTION], 'question_update.php?action=add&answerType=' . COMBO_QUESTION . '&pool_id=' . $pool_id, 400, 720 );
//echo str_repeat ( "&nbsp;", 2 ) . link_button ( '', $_question_types [CLOZE_QUESTION], 'question_update.php?action=add&answerType=' . CLOZE_QUESTION . '&pool_id=' . $pool_id, 400, 720 );


       ?>
    </div>
    <div class="managerSearch">
<!--        <form action="#" method="post" id="searchform">-->
<!--            <select>-->
<!--                <option>批量操作</option>-->
<!--                <option>删除</option>-->
<!--            </select>-->
<!--            <input type="button" id="searchbutton" value="应用">-->
<!--            <span class="searchtxt">题目：</span><input type="text" id="searchtext" value="请输入你想要搜索的内容">-->
<!--            <span class="searchtxt">题型：</span>-->
<!--            <select>-->
<!--                <option>全部</option>-->
<!--                <option>AAAAA</option>-->
<!--            </select>-->
<!--            <span class="searchtxt">难度：</span>-->
<!--            <select>-->
<!--                <option>全部</option>-->
<!--                <option>AAAAA</option>-->
<!--            </select>-->
<!--            <input type="button" id="searchbutton" value="搜索">-->
<!--        </form>-->
        <?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <?php $table->display ();?>
        </form>
    </article>
    <!--div class="manage-page">
        <div class="page-selsect">
            <a class="last-page"></a>
            <a class="min-last-page"></a>
            <a class="min-next-page"></a>
            <a class="next-page"></a>
            <form action="#" method="post" id="aa">
                <!--input type="text" value="4" id="jumpvalue">
                <input type="button" value="转" id="jumpbutton">
                <select>
                    <option>10</option>
                    <option>30</option>
                    <option>60</option>
                    <option>90</option>
                    <option>110</option>
                </select>
            </form-->
            <!--页次：<span class="sizehight">4/4</span> 共有<span class="sizehight">158</span>条记录-->
        </div>
    </div>
</section>
</body>
</html>
