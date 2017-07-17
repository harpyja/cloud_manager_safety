<?php
$language_file = array ('exercice', 'admin' );

include_once ('../inc/global.inc.php');
api_protect_admin_script ();
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
include_once ('cls.question_pool.php');
$objQuestionPool = new QuestionPool ();
$redirect_url = 'main/exam/pool_iframe.php';
$id = getgpc ( "id" );

if (isset ( $_GET ["action"] )) {
	switch (getgpc ( "action", "G" )) {
		case "delete" : //删除分类
			$rtn = $objQuestionPool->del_info ( $id );
			if ($rtn == - 1) {
				Display::display_msgbox ( get_lang ( 'DeletePoolsetFailed' ), $redirect_url, 'warning' );
			} elseif ($rtn == 1) {
				Display::display_msgbox ( get_lang ( 'DeletePoolsetSuccess' ), $redirect_url );
			}
			
			break;
		case "remove" : //删除题库
			if (! isRoot ()) api_not_allowed ();
			$rtn = $objQuestionPool->del_info ( $id );
			if ($rtn == 101) {
				$message = '删除题库失败! 原因: 题库下有题目,只允许删除空题库';
				Display::display_msgbox ( $message, $redirect_url, 'warning' );
			} elseif ($rtn == 1) {
				Display::display_msgbox ( get_lang ( 'DeletePoolsetSuccess' ), $redirect_url );
			}
			break;
	}
}

$total_question_cnt = $objQuestionPool->get_pool_question_count ();

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';

Display::display_header ( null, FALSE );
echo '<div class="actions">';
if (isRoot ()) echo str_repeat ( '&nbsp;', 2 ) . link_button ( 'stats_access.gif', 'AddPool', 'pool_update.php?action=add_pool', '40%', '60%' );
if($total_question_cnt>0)
echo str_repeat ( '&nbsp;', 2 ) . link_button ( 'surveyadd.gif', 'AllQuestions', '../exercice/question_base.php', NULL, NULL );
echo '</div>';

$table_header [] = array (get_lang ( "PoolName" ) );
$table_header [] = array (get_lang ( "PoolQuestionCount" ) );
$table_header [] = array (get_lang ( "UniqueSelect" ) );
$table_header [] = array (get_lang ( "MultipleSelect" ) );
$table_header [] = array (get_lang ( "TrueFalseAnswer" ) );
$table_header [] = array (get_lang ( "FreeAnswer" ) );
$table_header [] = array (get_lang ( "DisplayOrder" ), false, null, array ('width' => '80' ) );
//$table_header[] = array(get_lang("Remark"));
//$table_header[] = array(get_lang("LastUpdatedDate"),null,array('width'=>'150'));
$table_header [] = array (get_lang ( "Actions" ), false, null, array ('width' => '120' ) );

$poolset = $objQuestionPool->get_list ();
$cnt = count ( $poolset );
foreach ( $poolset as $k => $pool_set ) {
	$row = array ();
	$row [] = $pool_set ['pool_name'];
	$row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'] );
	$row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 1 );
	$row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 2 );
	$row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 3 );
	$row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 6 );
	//$row [] = $pool_set ['pool_desc'];
	$row [] = $pool_set ['display_order'];
	
	$action = '&nbsp;&nbsp;<a href="../exercice/question_base.php?pool_id=' . $pool_set ["id"] . '">' . Display::return_icon ( 'questionsdb.gif', get_lang ( 'QuestionList' ), array ('style' => 'vertical-align: middle;', 'width' => 24, 'height' => 24 ) ) . '</a>&nbsp;';
	if (isRoot ()) {
		$action .= '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'pool_update.php?action=edit_pool&id=' . $pool_set ['id'], 220, 600, FALSE );
		$href = 'pool_list.php?action=remove&amp;id=' . $pool_set ["id"] . '&amp;pid=' . $pool_set ['pid'];
		$action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'DeletePoolsetConfirm', 'Delete', $href );
	}
	$row [] = $action;
	$table_data [] = $row;
}
echo Display::display_table ( $table_header, $table_data );

if ($_GET ["refresh"]) echo '<script>refresh_tree();</script>';

Display::display_footer ();
