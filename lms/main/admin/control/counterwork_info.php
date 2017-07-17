<?php
$language_file = array ('admin', 'registration' );$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

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
    }
}
function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
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
    $labs_topo = Database::get_main_table (deploy);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_topo;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $labs_topo = Database::get_main_table (deploy);

    $sql = "select `id`,`task_id`,`template_id`,`user_id`,`id`,`ip` FROM ".$labs_topo." WHERE 1 ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $user1=Database::getval("select username from user where user_id=".$arr[3],__FILE__,__LINE__);
        $user2=Database::getval("select firstname from user where user_id=".$arr[3],__FILE__,__LINE__);

        $tem_name=Database::getval("select name from vmdisk where id=".intval($arr[2]),__FILE__,__LINE__);
        $platform_name=Database::getval("select platform from vmdisk where id=".intval($arr[2]),__FILE__,__LINE__);

        $task1=Database::getval("select name from task where id=".intval($arr[1]),__FILE__,__LINE__);
        $task_name=Database::getval("select name from renwu where id=".$task1,__FILE__,__LINE__);

        $group_id=Database::getval("select group_id from user where user_id=".intval($arr[3]),__FILE__,__LINE__);
        $group_name=Database::getval("select name from group_user where id=".$group_id,__FILE__,__LINE__);
        $group_type=Database::getval("select type from group_user where id=".$group_id,__FILE__,__LINE__);
        if($group_type==1){
            $group_type="(红方)";
        }else{
            $group_type="(蓝方)";
        }
        if($platform_name==1){
            $platform_name= "(渗透)";
        }if($platform_name==2){
            $platform_name= "(靶机)";
        }
        $arr[1]=$task_name;
        $arr[2]=$tem_name.$platform_name;
        $arr[3]=$user1."(".$user2.")";
        $arr[4]=$group_name.$group_type;
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
array_unshift($data,'---所有任务---');
$form->addElement ( 'select', 'renwu', get_lang ( '任务名称' ), $data, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

//$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '编号',  false, null, null);
$table->set_header ( $idx ++, '任务名称', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '模版名称', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '用户', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '用户组', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, 'IP', false, null, array ('style' => 'width:10%;text-align:center' ) );

//$table->set_form_actions ( array ('deletes' => '删除所选项' ,'visible' => '发布所选项','invisible' => '关闭所选项'), 'labs' );

?>
<aside id="sidebar" class="column control open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>
        &gt;  分组对抗信息 </h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
//            echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'control_add.php', '90%', '70%' );
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