<?php
$language_file = array ('admin', 'registration' );
//$cidReset = true;

include_once ('../../main/inc/global.inc.php');//is_admins();
include_once ('../../main/assignment/assignment.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once('inc/page_header.php');

$action=htmlspecialchars($_GET ['action']);

function group_filter($group) {
    $result  = "";
    $result .=Database::getval('select name from group_user where id='.$group,__FILE__,__LINE__);
    return $result;
}
function template_filter($id) {
    $result = "";
    $href="template_info.php?id=".$id;
    $result .='<a href="'.$href.'"><img src="../../themes/img/visio.gif"/></a>';
    return $result;
}
function red_filter($id) {

    $name_id=Database::getval("select name from task where id =".$id,__FILE__,__LINE__);
    $name=Database::getval("select name from renwu where id =".$name_id,__FILE__,__LINE__);
    $result = "";
    $result .=link_button ( '../../themes/img/calendar_month.gif',  $name, 'red_template.php?id='.$id, '80%', '70%' ,FALSE);
    return $result;
}
function blue_filter($id) {
    $name_id=Database::getval("select name from task where id =".$id,__FILE__,__LINE__);
    $name=Database::getval("select name from renwu where id =".$name_id,__FILE__,__LINE__);
    $result = "";
    $result .=link_button ( '../../themes/img/calendar_personal.gif', $name , 'blue_template.php?id='.$id, '80%', '70%' ,FALSE);
    return $result;
}
function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc("keyword");
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
        $keyword = Database::escape_string ( $_GET['keyword'], TRUE );
        $sql_where .= " AND (`id` LIKE '%" . trim ( $keyword ) . "%' OR `name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%')";
    }
    $g_id=  getgpc('id');
    if (is_not_blank ( $g_id )) {
        $sql_where .= " AND id=" . Database::escape ( getgpc ( 'id', 'G' ) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_topo() {
    $labs_topo = Database::get_main_table (task);
    $group_id=Database::getval("select group_id from `vslab`.`user` where `user_id`=".$_SESSION['_user']['user_id'],__FILE__,__LINE__);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_topo." WHERE status=1 ";
    if($_SESSION['_user']['user_id']!=1){
        $sql.=' AND `group`='.$group_id;
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $labs_topo = Database::get_main_table (task);
    $group_id=Database::getval("select group_id from `vslab`.`user` where `user_id`=".$_SESSION['_user']['user_id'],__FILE__,__LINE__);

    $sql = "select `id`,`name`,`name`,`group`,`id` FROM ".$labs_topo." WHERE   status=1";
    if($_SESSION['_user']['user_id']!=1){
        $sql.=' AND `group`='.$group_id;
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $arr[1]=Database::getval("select name from renwu where id =".$arr[1],__FILE__,__LINE__);
        $arr[2]=Database::getval("select description from renwu where id =".$arr[2],__FILE__,__LINE__);
        $arrs [] = $arr;
    }
    return $arrs;
}
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'value'=>'输入搜索关键词','id'=>'searchkey','title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
//$table->set_header ( $idx ++, '序号',  false, null, null);
$table->set_header ( $idx ++, get_lang ( '编号' ), false, null );
$table->set_header ( $idx ++, get_lang ( '任务名称' ), false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, get_lang ( '任务描述' ), false, null, array ('style' => 'width:30%;text-align:left' ));
$table->set_header ( $idx ++, get_lang ( '用户组' ), false, null, array ('style' => 'width:20%' ));
//$table->set_header ( $idx ++, get_lang ( '靶机模板' ), false, null, array ('style' => 'width:15%' ));
//$table->set_header ( $idx ++, get_lang ( '渗透模板' ), false, null, array ('style' => 'width:15%' ));
$table->set_header ( $idx ++, get_lang ( '模板信息' ), false, null, array ('style' => 'width:20%' ));

$table->set_column_filter ( 3, 'group_filter' );
$table->set_column_filter ( 4, 'template_filter' );
//$table->set_column_filter ( 4, 'red_filter' );
//$table->set_column_filter ( 5, 'blue_filter' );
?>
<aside id="sidebar" class="column open control">
    <div id="flexButton" class="closeButton close"></div>
</aside>
<!--<aside id="sidebar" class="column open cloudindex">
    <div id="flexButton" class="closeButton close"></div>
</aside>-->
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/portal/sp/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/portal/sp/template_list.php">我的模板</a></h4>
    <div class="managerSearch">
        <?php $form->display ();?>

    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>
