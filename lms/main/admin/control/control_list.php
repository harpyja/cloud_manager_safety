<?php
$language_file = array ('admin', 'registration' );$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

//echo '<pre>';var_dump($GLOBALS['_user']);echo '</pre>';
//echo '<pre>';var_dump($_SESSION);echo '</pre>';
function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
        $result .=  link_button ( 'edit.gif', 'Edit', 'control_edit.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function group_filter($group) {
    $result  = "";
    $result .=Database::getval('select name from group_user where id='.intval($group),__FILE__,__LINE__);
    return $result;
}
function red_filter($id) {
    $result = "";
    $result .=  link_button ( 'calendar_month.gif', '靶机模板', 'red_template.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function blue_filter($id) {
    $result = "";
    $result .=  link_button ( 'calendar_personal.gif', '渗透模板', 'blue_template.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function status_filter($id){
    $status=Database::getval('select status from task where id='.intval($id),__FILE__,__LINE__);
    $result='';$action='';$title='';$img='';
    if($status==1){
        $action.='off';
        $title .='关闭';
        $img.='visible.gif';
    }else{
        $action.='on';
        $title .='发布';
        $img.='invisible.gif';
    }
    $result.="<a href='control_list.php?action=".$action."&id=".intval($id)."'>";
    $result.="<img src='../../../themes/img/".$img."' title='".$title."'>";
    $result.="</a>";
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
        $result .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'control_list.php?action=delete&id=' . intval($id) );
    return $result;
}
$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['id']));
            if ( isset($delete_id)){

                $sql = "DELETE FROM `vslab`.`task` WHERE `task`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                if($result){
                    $update_sql ="UPDATE  `vmdisk` SET  `mod_type` =  '0', `task_id` =  '0' WHERE  `vmdisk`. `task_id`=".$delete_id;
                    api_sql_query($update_sql,__FILE__,__LINE__);
                }
                $redirect_url = "control_list.php";
                api_redirect ($redirect_url);
            }
            break;
    }
}
if(isset($_GET['action'])){
    $ids=  intval(htmlspecialchars($_GET ['id']));
    if(getgpc("action","G")=='on' && isset($_GET['id'])){
        $sql="UPDATE  `vslab`.`task` SET  `status` =  '1' WHERE  `task`.`id` =".$ids;
        $res=api_sql_query($sql ,__FILE__,__LINE__);
        api_redirect ('control_list.php');
    }
    if(getgpc("action","G")=='off' && isset($_GET['id'])){
        $sql="UPDATE  `vslab`.`task` SET  `status` =  '0' WHERE  `task`.`id` =".$ids;
        $res=api_sql_query($sql ,__FILE__,__LINE__);
        api_redirect ('control_list.php');
    }
}
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $labs = getgpc('labs');
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {

                    $sql = "DELETE FROM `vslab`.`task` WHERE `task`.`id` =".intval($id);
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    if($result){
                        $update_sql ="UPDATE  `vmdisk` SET  `mod_type` =  '0', `task_id` =  '0' WHERE  `vmdisk`. `task_id`=".intval($id);
                        api_sql_query($update_sql,__FILE__,__LINE__);
                    }

                    $log_msg = get_lang('删除所选') . "id=" . intval($id);
                    api_logging ( $log_msg, 'labs', 'labs' );
                }
            }
            break;

        case 'visible' :
            $labs = getgpc('labs');
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {
                    $sql="UPDATE  `vslab`.`task` SET  `status` =  '1' WHERE  `task`.`id` =".intval($id);
                    $res=api_sql_query($sql ,__FILE__,__LINE__);
                }
            }
            break;
        case 'invisible' :
            $labs = getgpc('labs');
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {
                    $sql="UPDATE  `vslab`.`task` SET  `status` =  '0' WHERE  `task`.`id` =".intval($id);
                    $res=api_sql_query($sql ,__FILE__,__LINE__);
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
        $keyword = Database::escape_string ( getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (`id` LIKE '%" . intval(trim ( $keyword )) . "%' OR `name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_topo() {
    $labs_topo = Database::get_main_table (task);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_topo;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $labs_topo = Database::get_main_table (task);

    $sql = "select `id`,`id`,`name`,`group`,`id`,`id`,`id`,`id`,`id` FROM ".$labs_topo." WHERE 1 ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $arr[2]=Database::getval("select name from renwu where id =".intval($arr[2]),__FILE__,__LINE__);
        $arrs [] = $arr;
    }
    return $arrs;
}
$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include ('../../inc/header.inc.php');


$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'value'=>'输入搜索关键词','id'=>'searchkey','title' => '' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '序号',  false, null, null);
$table->set_header ( $idx ++, '编号', false, null,array ('style' => 'width:5%' ));
$table->set_header ( $idx ++, '任务名称', false, null, array ('style' => 'width:20%' ));
//$table->set_header ( $idx ++, '任务描述', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '用户组', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '靶机模板', false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, '渗透模板', false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, '状态', false, null, array ('style' => 'width:10%;text-align:center' ));
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:10%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ,'visible' => '发布所选项','invisible' => '关闭所选项'), 'labs' );
$table->set_column_filter ( 3, 'group_filter' );
$table->set_column_filter ( 4, 'red_filter' );
$table->set_column_filter ( 5, 'blue_filter' );
$table->set_column_filter ( 6, 'status_filter' );
$table->set_column_filter ( 7, 'edit_filter' );
$table->set_column_filter ( 8, 'delete_filter' );
?>
<aside id="sidebar" class="column control open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>
        &gt;  分组对抗模板分配 </h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
            echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'control_add.php', '90%', '70%' );
            ?>
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
