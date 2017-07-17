<?php
/**
==============================================================================
 * 网络拓扑设计
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_labs'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_labs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` text NOT NULL,
              `labs_category` int(11) NOT NULL,
              `description` text NOT NULL,
              `info` text NOT NULL,
              `netmap` text NOT NULL,
              `diagram` int(11) DEFAULT NULL,
              `folder` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

function description_filter($description){
    $result ='';
    $result.='<span style="float:left">&nbsp;'.$description.'</span>';
    return $result;
}

function info_filter($info){
    $result ='';
    $result.='<span style="float:left">&nbsp;'.$info.'</span>';
    return $result;
}

function device_filter($id){
    $addDevices = '添加设备';
    $result = link_button ( '', $addDevices, 'labs_device_add.php?action=device_add&id='.$id, '90%', '70%' );
    return $result;
}
function category_filter($labs_category){
    $result='';
    $labs_category_sql="select `name` from `vslab`.`labs_category` where `id`=".$labs_category;
    $result.=DATABASE::getval($labs_category_sql,__FILE__,__LINE__);
    return $result;
}
function net_filter($id){
    $sql="select `name` from `labs_labs` where `id`='".$id."'";
    $name=DATABASE::getval($sql,__FILE__,__LINE__);
    $result="";
    $topo_design="设计拓扑";
    $result .= link_button ( '', $topo_design, '../../../router/html/labs_netmap.php?action=design&name='.$name.'&id='.$id, '90%', '70%' );
    //$result .= link_button ( '', $topo_design, 'iou_web/labs_topo_design.php?action=design&name='.$name, '90%', '70%' );
    return $result;
}
function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .=  link_button ( 'edit.gif', 'Edit', 'labs_topo_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'labs_topo.php?action=delete&id=' . $id );
    }
    return $result;
}
$action=getgpc('action','G');

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id= intval(getgpc('id','G'));
            if ( isset($delete_id)){
                $name_sql="select `name` from `labs_labs` where id='".$delete_id."'";
                $labs_name=Database::getval($name_sql,__FILE__,__LINE__);

                $sql = "DELETE FROM `vslab`.`labs_labs` WHERE `labs_labs`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $devices_sql="DELETE FROM `vslab`.`labs_devices` WHERE `labs_devices`.`lab_id`='".$labs_name."'";
                $a=api_sql_query($devices_sql,__FILE__,__LINE__);

                $redirect_url = "labs_topo.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}
if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'deletes' :
            $labs =$_POST['labs']; 
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {

                    $name_sql="select `name` from `labs_labs` where id='".$id."'";
                    $labs_name=Database::getval($name_sql,__FILE__,__LINE__);

                    $sql = "DELETE FROM `vslab`.`labs_labs` WHERE `labs_labs`.`id` = {$id}";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    $devices_sql="DELETE FROM `vslab`.`labs_devices` WHERE `labs_devices`.`lab_id`='".$labs_name."'";
                    $a=api_sql_query($devices_sql,__FILE__,__LINE__);

                    $log_msg = get_lang('删除所选') . "id=" . $id;
                    api_logging ( $log_msg, 'labs', 'labs' );
                }
            }
            break;
    }
}

function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc("keyword");
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (`id` LIKE '%" . trim ( $keyword ) . "%' OR `name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%'
        OR `info` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_topo() {
    $labs_topo = Database::get_main_table (labs_labs);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_topo;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $labs_topo = Database::get_main_table (labs_labs);
    $sql = "select `id`,`id`,`name`,`labs_category`,`description`,`info`,`id`,`id`,`id`,`id` FROM ".$labs_topo;

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



$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '编号', false, null, array ('style' => ' text-align:center;width:5%' ));
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'text-align:center;width:18%' ) );
$table->set_header ( $idx ++, '拓扑分类', false, null, array ('style' => 'text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '描述' , false, null, array ('style' => 'width:20%;' ) );
$table->set_header ( $idx ++, '信息', false, null, array ('style' => 'width:20%; text-align:center;' ) );
$table->set_header ( $idx ++, '设备列表', false, null, array ('style' => 'width:8%; text-align:center;' ) );
$table->set_header ( $idx ++, '网络拓扑', false, null, array ('style' => 'width:8%; text-align:center;' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:5%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
$table->set_column_filter ( 3, 'category_filter' );
$table->set_column_filter ( 4, 'description_filter' );
$table->set_column_filter ( 5, 'info_filter' );
$table->set_column_filter ( 6, 'device_filter' );
$table->set_column_filter ( 7, 'net_filter' );
$table->set_column_filter ( 8, 'edit_filter' );
$table->set_column_filter ( 9, 'delete_filter' );


//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 网络拓扑设计</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'labs_topo_add.php', '90%', '70%' );?>
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
