<?php
$language_file = array ('admin', 'registration' );$cidReset = true;

include_once ('../../main/inc/global.inc.php');//is_admins();

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();

include_once('inc/page_header.php');



if(mysql_num_rows(mysql_query("SHOW TABLES LIKE group_user"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `group_user` (
          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
          `name` varchar(128) NOT NULL COMMENT '分组名称',
          `userId` varchar(128) NOT NULL COMMENT '用户编号',
          `is_leader` int(11) NOT NULL COMMENT '是否组长',
          `type` varchar(128) NOT NULL COMMENT '用户类型,1为红方，2为蓝方',
          `description` text NOT NULL COMMENT '分组描述',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='监控平台小组用户表' AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
function user_filter1($id) {
    $names=Database::getval('select name from group_user where id='.$id,__FILE__,__LINE__);
    $result =link_button ( '../../themes/img/crs_group.gif', $names, 'red_user_control.php?id='.$id, '50%', '60%' , FALSE);
    return $result;
}
function user_filter2($id) {
    $names=Database::getval('select name from group_user where id='.$id,__FILE__,__LINE__);
    $result =link_button ( '../../themes/img/crs_group_na.gif', $names, 'blue_user_control.php?id='.$id, '50%', '60%' , FALSE);
    return $result;
}
$action=htmlspecialchars($_GET ['action']);

function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (`id` LIKE '%" . trim ( $keyword ) . "%'
                        OR `name` LIKE '%" . trim ( $keyword ) . "%'
                        OR `description` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( getgpc ( 'id', 'G' ) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_group() {
    $group_id=Database::getval('select group_id from user where user_id='.$_SESSION['_user']['user_id'],__FILE__,__LINE__);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `group_user` where 1 ";
    if($_SESSION['_user']['user_id']!=1){
        $sql.=' AND id='.$group_id;
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_group_data($from, $number_of_items, $column, $direction) {
    $group_id=Database::getval('select group_id from user where user_id='.$_SESSION['_user']['user_id'],__FILE__,__LINE__);
    $sql = "select `id`,`name`,`description`,`id`,  `id` FROM `group_user` where 1 ";
    if($_SESSION['_user']['user_id']!=1){
        $sql.=' AND id='.$group_id;
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $arrs [] = $arr;
    }
    return $arrs;
}

echo '<style type="text/css">
    .redpic img{
    width:30px;
        height:30px;
    }
</style>';
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'value'=>'输入搜索关键词','id'=>'searchkey','title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


$table = new SortableTable ( 'labs', 'get_number_of_group', 'get_group_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
//$table->set_header ( $idx ++, '',  false, null, null);
$table->set_header ( $idx ++, get_lang ( '编号' ), false, null,null);
$table->set_header ( $idx ++, get_lang ( '分组名称' ), false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, get_lang ( '描述' ), false, null, array ('style' => 'width:20%' ));

$table->set_header ( $idx ++, get_lang ( '红方小组' ), false, null, array ('style' => 'width:25%' ,'class'=>'redpic'));
$table->set_header ( $idx ++, get_lang ( '蓝方小组' ), false, null, array ('style' => 'width:25%','class'=>'redpic'));

//$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
//$table->set_column_filter ( 3, 'des_filter' );
$table->set_column_filter ( 3, 'user_filter1' );
$table->set_column_filter ( 4, 'user_filter2' );
?>
<aside id="sidebar" class="column control open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/portal/sp/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/portal/sp/control/group_list.php">我的小组</a></h4>
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