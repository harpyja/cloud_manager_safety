<?php
/**
==============================================================================
 * 拓扑设备管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();  //保护平台管理员权限
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_devices'"))!=1){//添加设备
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_devices` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `lab_id` varchar(128) NOT NULL,
          `name` text NOT NULL,
          `ios` text NOT NULL,
          `ram` int(128) NOT NULL,
          `nvram` int(128) NOT NULL,
          `ethernet` int(128) NOT NULL,
          `serial` int(128) NOT NULL,
          `slot` varchar(128) NOT NULL,
          `picture` text NOT NULL,
          `conf_id` text NOT NULL,
          `top` int(20) NOT NULL,
          `left` int(20) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
//Interception of a fixed-length string  @changzf 2013/01/18
function g_substr($str, $len, $dot = true) {
    $i = 0;
    $l = 0;
    $c = 0;
    $a = array();
    while ($l < $len) {
        $t = substr($str, $i, 1);
        if (ord($t) >= 224) {
            $c = 3;
            $t = substr($str, $i, $c);
            $l += 2;
        } elseif (ord($t) >= 192) {
            $c = 2;
            $t = substr($str, $i, $c);
            $l += 2;
        } else {
            $c = 1;
            $l++;
        }
        $i += $c;
        if ($l > $len) break;
        $a[] = $t;
    }
    $re = implode('', $a);
    if (substr($str, $i, 1) !== false) {
        array_pop($a);
        ($c == 1) and array_pop($a);
        $re = implode('', $a);
        $dot and $re .= '...';
    }
    return $re;
}

function config_filter($conf_id){
    $result = "";
    $result.=g_substr($conf_id,150);
    return $result;
}
function locate_filter($id){
    $result = "";
    $sql="select `top`,`left` from `labs_devices` where `id`=".$id;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    while ( $arr = Database::fetch_row ( $res) ) {
        $arrs [] = $arr;
    }
    $top  =$arrs[0][0];
    $left =$arrs[0][1];
    $result.='('.$top.','.$left.')';
    return $result;
}
function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .=   link_button ( 'edit.gif', 'Edit', 'labs_device_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .=   confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'labs_device.php?action=delete&id=' . $id );
    }
    return $result;
}
$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=htmlspecialchars($_GET ['id']);
            if ( isset($delete_id)){
                $sql = "DELETE FROM `vslab`.`labs_devices` WHERE `labs_devices`.`id` = ".$delete_id;
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "labs_device.php";
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

                    $sql = "DELETE FROM `vslab`.`labs_devices` WHERE id='" . $id . "'";
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
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string ( getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR name LIKE '%" . trim ( $keyword ) . "%'
        OR lab_id LIKE '%" . trim ( $keyword ) . "%' OR ios LIKE '%" . trim ( $keyword ) . "%'
        OR ram LIKE '%" . trim ( $keyword ) . "%'
        OR picture LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank ( $_GET ['lab_name'] )) {
        $sql_where .= " AND `lab_id`=" . Database::escape ( getgpc ( 'lab_name', 'G' ) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_devices() {
    $labs_devices = Database::get_main_table (labs_devices);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_devices;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_devices_data($from, $number_of_items, $column, $direction) {
    $labs_devices = Database::get_main_table (labs_devices);
    $sql = "select `id`,`id`,`name`,`lab_id`,`ios`,`vmdisks`,`slot`,`picture`,`conf_id`,`id`,`id`,`id` FROM ".$labs_devices;

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
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );

$sql1 = "SELECT `name` FROM `vslab`.`labs_labs`";
$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
$device_show= array ();

while ( $device_show = Database::fetch_row ( $result1) ) {
    $device_shows [] = $device_show;
}
foreach ( $device_shows as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $labs_device[$v2]  = $v2;
    }
}
$labs_device[""] = "---所有拓扑---";
ksort($labs_device);
foreach ( $category_cnt as $item ) {
    $cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [$item ['id']] . ")" : "");
    $cate_options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$form->addElement ( 'select', 'lab_name', get_lang ( '拓扑' ), $labs_device, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['lab_name'] ) && is_not_blank ( $_GET ['lab_name'] )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'labs', 'get_number_of_labs_devices', 'get_labs_devices_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false, null);
$table->set_header ( $idx ++, '编号', false, null, array ('style' => 'width:10%;text-align:center' ));
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:15%; text-align:center' ) );
$table->set_header ( $idx ++, '实验拓扑', false, null, array ('style' => 'width:8%;text-align:center' ) );
$table->set_header ( $idx ++, '设备型号', false, null, array ('style' => 'width:5%; text-align:center' ) );
$table->set_header ( $idx ++, '虚拟模板', false, null, array ('style' => 'width:5%; text-align:center' ) );
$table->set_header ( $idx ++, '模块设置', false, null, array ('style' => 'width:20%;text-align:center' ) );
$table->set_header ( $idx ++, '设备类型', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '初始化配置', false, null, array ('style' => 'width:10%') );
$table->set_header ( $idx ++, '定位', false, null, array ('style' => 'width:5%') );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:5%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
$table->set_column_filter ( 8, 'config_filter' );
$table->set_column_filter ( 9, 'locate_filter' );
$table->set_column_filter ( 10, 'edit_filter' );
$table->set_column_filter ( 11, 'delete_filter' );


//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 网络设备管理</h4>
    <div class="managerSearch">
        <?php  $form->display (); //表单中的“查询”及显示部分?>
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'labs_device_add.php', '90%', '70%' );?>
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
