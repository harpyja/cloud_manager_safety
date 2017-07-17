<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-2
 * Time: 下午8:14
 * To change this template use File | Settings | File Templates.
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/admin/cloud/clouddesktop.php';


if(mysql_num_rows(mysql_query("SHOW TABLES LIKE clouddesktop"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `clouddesktop` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,`host_name` varchar(256) NOT NULL,
      `physical_address` varchar(256) NOT NULL,`IP_address` varchar(256) DEFAULT NULL,
      `cloud_mirror` varchar(256) DEFAULT NULL,`storage_space_type` int(11) DEFAULT NULL,
      `permissions` int(11) DEFAULT NULL,`group_name` varchar(256) DEFAULT NULL,
      `user_name` varchar(256) DEFAULT NULL,PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
function edit_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'clouddesktop_edit.php?action=edit&id='.intval($id), '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    //$result .= link_button ( 'edit.gif', 'Edit', 'vmdisk_edit.php?id='. $id, '90%', '90%', FALSE );
    //$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. $id, '90%', '80%', FALSE );
    if (api_is_platform_admin () && ! in_array ( intval($id), $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'clouddesktop.php?action=delete&id=' . intval($id) );
    }
    return $result;
}
if (isset ( $_GET ['action'] )) {


    switch ($_GET ['action']) {
        case 'delete' :
            if ( $_GET ['action'] =='delete') {
                //$table = "vmdisk";
                $id = intval(getgpc('id'));

                $sql = "SELECT host_name,cloud_mirror FROM `vslab`.`clouddesktop` WHERE id='" . $id . "'";
                $res=api_sql_query ( $sql, __FILE__, __LINE__ );
                while($ss = Database::fetch_array ( $res )){
                    $values = $ss;
                }
                $HostName = $values['host_name'];
                $CloudMirror=$values['cloud_mirror'];
                exec("sudo -u root /sbin/clouddesktopadd.sh $HostName $CloudMirror");
//                echo "sudo -u root /sbin/clouddesktopadd.sh $HostName $CloudMirror";

                $sql = "DELETE FROM `vslab`.`clouddesktop` WHERE `clouddesktop`.`id` = {$id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                tb_close ( "clouddesktop.php" );
            }
            break;
    }
}


function remote_wake_filter($id) {
    if ($id) {
        $action = 'lock';
        $wakening = '唤醒';
    }else {
        $action = 'unlock';
        $wakening = '沉睡';
    }
    $result = '<a href="clouddesktop.php?action=' . $action . '&amp;ids=' . intval($id) . '">'.$wakening.'</a>';
    return $result;
}
function storage_space_type_filter($storage_space_type){
    $result = '';
    if($storage_space_type=='1'){
        $result="共享";
    }if($storage_space_type=='2'){
        $result="独占";
    }
    return $result;
}
function permissions_filter($permissions){
    $result = '';
    if($permissions=='1'){
        $result="只读";
    }if($permissions=='2'){
        $result="读写";
    }
    return $result;
}
//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        // 批量删除课程
        case 'delete_clouddesktop' :
            $deleted_clouddesktop_count = 0;
            $clouddesktop_id = getgpc('clouddesktop');
            if (count ( $clouddesktop_id ) > 0) {
                foreach ( $clouddesktop_id as $index => $id ) {

                    $sql = "SELECT host_name,cloud_mirror FROM `vslab`.`clouddesktop` WHERE id='" . intval($id) . "'";
                    $res=api_sql_query ( $sql, __FILE__, __LINE__ );
                    while($ss = Database::fetch_array ( $res )){
                        $values = $ss;
                    }
                    $HostName = $values['host_name'];
                    $CloudMirror=$values['cloud_mirror'];

                exec("sudo -u root /sbin/clouddesktopadd.sh $HostName $CloudMirror");
//                    echo "sudo -u root /sbin/clouddesktopadd.sh $HostName $CloudMirror";

                    $sql = "DELETE FROM `vslab`.`clouddesktop` WHERE id='" . intval($id) . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );
                }
            }
    }
}
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if($_GET ['keyword']=='输入搜索关键词'){
        $_GET ['keyword']='';
    }
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (
		id LIKE '%" . intval(trim ( $keyword )) . "%' 
		or host_name  LIKE '%" . trim ( $keyword ) . "%' 
		or physical_address LIKE '%" . trim ( $keyword ) . "%' 
		or IP_address  LIKE '%" . trim ( $keyword ) . "%' 
		or cloud_mirror  LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_clouddesktop() {
    $clouddesktop = Database::get_main_table ( clouddesktop );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $clouddesktop;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_clouddesktop_data($from, $number_of_items, $column, $direction) {
    $networkmap = Database::get_main_table ( clouddesktop );
    //$sql = "select id as co5,id as co6,name as co7te,id as co8, id as co9 FROM  $networkmap ";
    $sql = "select id as co9,id as co10,host_name as co11,physical_address as co12,IP_address as co13,cloud_mirror as co14,storage_space_type as co15 ,permissions as co16,id as co17,id as co18,id as co19 FROM  $networkmap ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
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



$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip ="编号/主机名称/物理地址/IP地址/系统镜像" ;
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:60%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

//by changzf

$table = new SortableTable ( 'clouddesktop', 'get_number_of_clouddesktop', 'get_clouddesktop_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '编号', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '主机名称', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '物理地址', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '分配的IP地址', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '云桌面系统镜像' , false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '存储空间类型', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '读写权限', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '远程唤醒', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'text-align:center' ) );
$table->set_form_actions ( array ('delete_clouddesktop' => get_lang ( '删除所选项' ) ), 'clouddesktop' );

$table->set_column_filter ( 6, 'storage_space_type_filter' );
$table->set_column_filter ( 7, 'permissions_filter' );
$table->set_column_filter ( 8, 'remote_wake_filter' );
$table->set_column_filter ( 9, 'edit_filter' );
$table->set_column_filter ( 10, 'delete_filter' );



//Display::display_footer ( TRUE );
?>


<aside id="sidebar" class="column cloud open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt; 云桌面终端 </h4>
    <div class="managerSearch">
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'create.gif', '新增', 'clouddesktop_add.php', '90%', '70%' );?>
        </span>
        <?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
       <?php $table->display ();?>
    </article>
</section>
</body>
</html>
