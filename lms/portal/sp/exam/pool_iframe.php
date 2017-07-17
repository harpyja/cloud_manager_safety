<?php
$language_file = array ('exercice', 'admin' );
require_once ('../inc/global.inc.php');

api_block_anonymous_users ();
api_protect_admin_script();
$this_section = SECTION_EXAM;

//include_once (api_get_path ( SYS_CODE_PATH ) . "exam/examination.inc.php");
//$restrict_org_id = protect_exam_script ();

$htmlHeadXtra[]='<script type="text/javascript">
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);  
else  window.addEventListener("load",  iframeAutoFit,  false);
function hidetree() {
	var tree = G("treeview");
	var hidebtn = G("hidebtn"); 
	if (tree.style.display!="none") {				
		G("treeview").style.display = "none";
		G("List").contentWindow.iframeAutoFit();
		G("frm").style.width = "99%";				
	}else {
		G("treeview").style.display = "";
		G("List").contentWindow.iframeAutoFit();
		G("frm").style.width = "80%";						
	}
	//alert(G("frm").style.width);
}
$(document).ready(function(){
	//G("treeview").style.display = "none";
	//G("frm").style.width = "99%";
});
</script>

<style type="text/css">
.framePage {border-top-style:none;	width:100%;	padding-top:0px;	text-align:left;}
#Resources {width:100%;}
#Resources #treeview {	float:left;	border:#999 solid 1px;	width:14%;	}
#Resources #frm {	float:left;	width:85%;}
#Resources #hidebtn {background:url("../../themes/img/hidebtn_off.gif") no-repeat scroll 0 0 transparent;
	color:#818DA5;cursor:pointer;float:left;font-size:12px;height:105px;margin-top:160px;width:6px;}
#Resources #hidebtn a {background:url("../../themes/img/hidebtn.gif") no-repeat scroll 0 0 transparent;
	display:block;height:105px;width:6px;}
#Resources #hidebtn a:hover{background:url(../../../themes/img/hidebtn_off.gif) no-repeat;}
</style>
';
$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';

Display::display_header();

?>
<body>

<!--<center>-->
<!--<div class="framePage stud">-->
<!--<div id="Resources">-->
<!--<div id="treeview"><iframe id="CategoryTree" name="CategoryTree"-->
<!--	src="pool_tree.php" frameborder="0" width="100%" style="min-height:440px"></iframe></div>-->
<!--	<div id="hidebtn"><a href="javascript:hidetree();"></a></div>-->
<!--<div id="frm"><iframe id="List" name="List" src="pool_list.php"-->
<!--	frameborder="0" width="100%" style="min-height:440px"></iframe></div>-->
<!--</div>-->
<!--</div>-->
<!--</center>-->
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


//Display::display_header ( null, FALSE );


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


if ($_GET ["refresh"]) echo '<script>refresh_tree();</script>';

//Display::display_footer ();
?>
<aside id="sidebar" class="column exercice open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/exercice/exercice.php">考试管理</a> &gt; 题库管理</h4>

    <div class="managerSearch">
      <?php
        if (isRoot ()) echo str_repeat ( '&nbsp;', 2 ) . link_button ( 'stats_access.gif', 'AddPool', 'pool_update.php?action=add_pool', '40%', '60%' );
        if($total_question_cnt>0)
            echo str_repeat ( '&nbsp;', 2 ) . link_button ( 'surveyadd.gif', 'AllQuestions', '../exercice/question_base.php', NULL, NULL );?>
    </div>
    <article class="module width_full hidden">
        <table cellspacing="0" cellpadding="0" class="p-table">
           <?php
            echo Display::display_table ( $table_header, $table_data );
            ?>
        </table>
    </article>
    <div class="manage-page">
        <div class="page-selsect">
            <a class="last-page"></a>
            <a class="min-last-page"></a>
            <a class="min-next-page"></a>
            <a class="next-page"></a>
            <input type="text" value="4" id="jumpvalue">
            <input type="button" value="转" id="jumpbutton">
            <select>
                <option>10</option>
                <option>30</option>
                <option>60</option>
                <option>90</option>
                <option>110</option>
            </select>
            页次：<span class="sizehight">4/4</span> 共有<span class="sizehight">158</span>条记录
        </div>
    </div>
</section>
</body>
</html>


