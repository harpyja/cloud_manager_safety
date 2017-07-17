<?php
/**
 * This is router type list page
 * @changzf
 * on 2013/01/10
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = link_button ( 'edit.gif', 'Edit', 'router_type_edit.php?id='.$id, '50%', '60%', FALSE );
    }
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'router_type.php?action=delete&id=' . $id );
    }
    return $result;
}
$action=getgpc('action','G');


if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=intval(getgpc('id'));
            if ( isset($delete_id)){ 
                $sql = "DELETE FROM `vslab`.`labs_type` WHERE `labs_type`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "router_type.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}

if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'deletes' :
            $labs = $_POST['labs'];
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {  
                    $sql = "DELETE FROM `vslab`.`labs_type` WHERE id='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ ); 
                    $log_msg = get_lang('删除所选') . "id=" . $id;
                    api_logging ( $log_msg, 'labs', 'labs' );
                }
            }
            break;
    }
}

function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc('keyword');
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
        $keyword = Database::escape_string ($g_keyword, TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR name LIKE '%" . trim ( $keyword ) . "%')";
    }
    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_type() {
    $labs_type = Database::get_main_table (labs_type);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_type;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_type_data($from, $number_of_items, $column, $direction) {
    $labs_type = Database::get_main_table (labs_type);
    $sql = "select `id`,`id`,`name`,`desc`,`id`,`id` FROM $labs_type";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
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
$keyword_tip ="编号/名称";
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_type', 'get_labs_type_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '编号', false, null, array ('style' => 'width:15%;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '描述', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:15%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:15%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
$table->set_column_filter ( 4, 'edit_filter' );
$table->set_column_filter ( 5, 'delete_filter' );
        
//Display::display_footer ( TRUE );
?> 
<aside id="sidebar" class="column router open"> <div id="flexButton" class="closeButton close"> </div> </aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;  路由交换管理  &gt; 路由交换类型管理</h4>
    <div class="managerSearch">
        <span class="searchtxt right">
            <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'router_type_add.php', '50%', '60%' );?>
        </span>
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

