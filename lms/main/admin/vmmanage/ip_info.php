<?php
/**
==============================================================================
 * 路由交换模块管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(!isset($_GET['dhcp_page_nr']) && !isset($_GET ['keyword'])){
    exec("sudo -u root /sbin/clouddhcplease.sh;");
    exec("sudo -u root /sbin/cloudscanning.sh dhcp;");
}

function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc('keyword');
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
           $g_keyword='';
        }
        $keyword = Database::escape_string (  $g_keyword, TRUE );
        $sql_where .= " AND (`id` LIKE '%" . intval( $keyword ) . "%'
                        OR `IP_address` LIKE '%" . trim ( $keyword ) . "%'
                        OR `type` LIKE '%" . trim ( $keyword ) . "%'
                        OR `physical_address` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_dhcp() {
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `clouddesktopscan`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_dhcp_data($from, $number_of_items, $column, $direction) {
    $sql = "select `id`,`IP_address`,`physical_address`,`type` FROM `clouddesktopscan`";
    $sql_where = get_sqlwhere ();
    if ($sql_where){
        $sql .= " WHERE " . $sql_where;
    }

    $sql .= " order by `id` LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );

    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}
$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$html = '<div id="demo" class="yui-navset">';
$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'dhcp', 'get_number_of_dhcp', 'get_dhcp_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '编号', false, null,null);
$table->set_header ( $idx ++, 'IP地址', false, null, array ('style' => 'text-align:center;width:30%' ) );
$table->set_header ( $idx ++, '物理地址' , false, null, array ('style' => 'text-align:center;width:30%;' ) );
$table->set_header ( $idx ++, '类型' , false, null, array ('style' => 'text-align:center;width:30%;' ) );

?>

<aside id="sidebar" class="column cloud open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a>
        &gt; IP地址信息</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
<!--        --><?php //echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'dhcp_add.php', '90%', '70%' );?>
        </span>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">

                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>