<?php
$language_file = array ('admin', 'registration' );$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

function status_filter($active, $url_params, $row) {  //显示，隐藏----图片更改
    global $_user, $_configuration;

    if ($active == '1') {
        $action = 'make_visible';
        $image = 'right';
    }
    if ($active == '0') {
        $action = 'make_invisible';
        $image = 'wrong';
    }

    if ($row [0] != $_user ['user_id']) {
         $result= Display::return_icon (  $row ['4']==1? 'visible.gif' : 'invisible.gif' );
    }
    return $result;
}
function active_filter($id, $url_params) {
    global $_configuration, $root_user_id;
    $result ='';
   // $result .= link_button ( 'synthese_view.gif', '查看', 'summary.php?action=info&id=' . $id, '90%', '70%', FALSE );

   
    $result .= '&nbsp;' . link_button ( 'edit.gif', '编辑', 'summary_edit.php?id=' . intval($id), '90%', '70%', FALSE );
  
    if (! in_array ( intval($id), $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'summary.php?action=delete&id=' .intval($id) );
    }

    return $result;
}


$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['id']));
            if ( isset($delete_id)){
                $sql = "DELETE FROM `vslab`.`summary` WHERE `summary`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );



                $redirect_url = "summary.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $labs = getgpc('summary');
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {
                    $sql = "DELETE FROM `vslab`.`summary` WHERE `summary`.`id` =".  intval($id);
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang('删除所选') . "id=" . intval($id);
                    api_logging ( $log_msg, 'summary', 'summary' );
                }
            }
            break;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (`id` LIKE '%" . intval(trim ( $keyword )) . "%' OR `title` LIKE '%" . trim ( $keyword ) . "%'
        OR `content` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_summary() {
    $summary = Database::get_main_table (summary);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM ".$summary;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_summary_data($from, $number_of_items, $column, $direction) {
    $summary = Database::get_main_table (summary);
    $sql = "select id,id,title,created_user,visible,id FROM ".$summary;

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $arr[3]=Database::getval("select `firstname` from `user` where `user_id`=$arr[3]");
        $arrs [] = $arr;
        
    }
    return $arrs;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );



$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'value'=>'输入搜索关键词','id'=>'searchkey','title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'summary', 'get_number_of_summary', 'get_summary_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '',  false, null, null);
$table->set_header ( $idx ++, get_lang ( '编号' ), false, null, array ('style' => 'width:15%' ));
$table->set_header ( $idx ++, get_lang ( '标题' ), false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, get_lang ( '创建者' ), false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, get_lang ( '状态' ), false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '操作', false, null, array ('style' => 'width:15%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'summary' );


$table->set_column_filter ( 4, 'status_filter' );
$table->set_column_filter ( 5, 'active_filter' );
?>
<aside id="sidebar" class="column system open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
         <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> 
        &gt; <a href="<?=URL_APPEDND;?>/main/admin/misc/settings.php">系统管理</a>&gt;大赛简介</h4>
    </h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'summary_add.php', '90%', '70%' );?>
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
