<?php
$language_file = array ('admin', 'registration' );$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

function user_filter1($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .=  link_button ( 'crs_group.gif', 'Edit', 'red_user_control.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function user_filter2($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .=  link_button ( 'crs_group_na.gif', 'Edit', 'blue_user_control.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function group_fileter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .=  link_button ( 'group_view.gif', '成员查看/分配', 'blue_user_control.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}
function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .=  link_button ( 'edit.gif', 'Edit', 'user_group_edit.php?id='.intval($id), '90%', '70%', FALSE );
    return $result;
}

function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'control_user_group.php?action=delete&id=' . intval($id) );
    return $result;
}
function type_fileter($type) {
    $result = "";
    if($type==1){
        $result .= "红方";
    }if($type==2){
        $result .= "蓝方";
    }

    return $result;
}
function info_fileter($id) {
    $result = "";
    $result .=  link_button ( 'group_view.gif', '所有小组用户', 'user_group_info.php?ids='.intval($id), '70%', '60%', FALSE );
    return $result;
}

$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['id']));
            if ( isset($delete_id)){

                $sql = "DELETE FROM `vslab`.`group_user` WHERE `group_user`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                if($result=1){
                    $sql1 = "UPDATE  `vslab`.`user` SET  `type` =  '0',group_id='0' WHERE  `user`.`group_id` ={$delete_id}";
                    api_sql_query ( $sql1, __FILE__, __LINE__ );
                }


                $redirect_url = "control_user_group.php";
                tb_close ( $redirect_url );
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
                    $sql = "DELETE FROM `vslab`.`group_user` WHERE `group_user`.`id` =".intval($id);
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    if($result){
                        $sql1 = "UPDATE  `vslab`.`user` SET  `type` =  '0',group_id='0' WHERE  `user`.`group_id` =".intval($id);
                        api_sql_query ( $sql1, __FILE__, __LINE__ );
                    }
                    $log_msg = get_lang('删除所选') . "id=" .intval($id);
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
        $sql_where .= " AND (`id` LIKE '%" . intval(trim ( $keyword )) . "%'
                        OR `name` LIKE '%" . trim ( $keyword ) . "%'
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

function get_number_of_group() {
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `group_user`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_group_data($from, $number_of_items, $column, $direction) {
    $sql = "select `id`,`id`,`name`,`description`,`type`, `id`, `id`,`id` FROM `group_user`";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
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



$table = new SortableTable ( 'labs', 'get_number_of_group', 'get_group_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '',  false, null, null);
$table->set_header ( $idx ++, get_lang ( '编号' ), false, null,array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, get_lang ( '分组名称' ), false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, get_lang ( '描述' ), false, null, array ('style' => 'width:20%' ));
?>
<style type="text/css">
    .redpic img{
        width:30px;
        height:30px;
    }
</style>
<?php
$table->set_header ( $idx ++, get_lang ( '小组类型' ), false, null, array ('style' => 'width:10%','class'=>'redpic' ));
//$table->set_header ( $idx ++, get_lang ( '小组成员' ), false, null, array ('style' => 'width:10%','class'=>'redpic' ));
//$table->set_header ( $idx ++, get_lang ( '红方小组' ), false, null, array ('style' => 'width:15%','class'=>'redpic' ));
$table->set_header ( $idx ++, get_lang ( '成员查看/分配' ), false, null, array ('style' => 'width:10%','class'=>'redpic' ));
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:10%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
//$table->set_column_filter ( 3, 'des_filter' );
//$table->set_column_filter ( 4, 'user_filter1' );
//$table->set_column_filter ( 5, 'user_filter2' );
$table->set_column_filter ( 4, 'type_fileter' );
//$table->set_column_filter ( 5, 'info_fileter' );
$table->set_column_filter ( 5, 'group_fileter' );
$table->set_column_filter ( 6, 'edit_filter' );
$table->set_column_filter ( 7, 'delete_filter' );
?>
<aside id="sidebar" class="column control open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>
        &gt; <a href="<?=URL_APPEDND;?>/main/admin/control/control_user_group.php">用户分组</a></h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . link_button ( 'group_add.gif', '自动建组', 'auto_jz.php', '50%', '50%' );
            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . link_button ( 'edit_group.gif', '自动分组', 'auto_fz.php', '50%', '50%' );
            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . link_button ( 'add_user_big.gif', '添加分组', 'user_group_add.php', '90%', '70%' );
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