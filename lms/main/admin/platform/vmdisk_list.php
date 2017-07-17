<?php
/**
 ==============================================================================
 * 镜像管理
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/admin/platform/vmdisk_list.php';

function NIC_type($NIC_type){
    if($NIC_type=='1'){
        $result='Intel';
    }if($NIC_type=='2'){
        $result='Reltek';
    }
    return $result;
}

function type_flter($type){
    if($type=='1'){
        $result='操作系统';
    }else{
        $result='安全设备';
    }
    return $result;
}


function CD_mirror($CD_mirror){
    if($CD_mirror=='1'){
        $result='基础镜像';
    }else{
        $result=$CD_mirror;
    }
    return $result;
}


function modify_filter($code) {
	$html = "<a></a>";
	$html .= '&nbsp;' . link_button ( 'cd.gif', '设置光盘镜像启动', 'ISO_edit.php?code=' . $code, '60%', '70%', FALSE );
	//$html .= '&nbsp;' . link_button ( 'add_user_big.gif', 'CourseAdmin', 'course_admins.php?code=' . $code, '70%', '76%', FALSE );
	return $html;
}

function  console_filter($code) {
    //dengxin
    $desc = 'select active from vmdisk where id='.intval($code);
    $active  = Database::getval ( $desc, __FILE__, __LINE__ );

    if(file_exists("/tmp/www/$code.html")){
        $html = "";
        $html .="<a href=/$code.html target=_new >连接</a>";
        return $html;
    }else{
        $html = "";
        $html .="未启动无法连接";
        return $html;
    }
}
function edit_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'vmdisk_edit.php?id='.intval($id), '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    //$result .= link_button ( 'edit.gif', 'Edit', 'vmdisk_edit.php?id='. $id, '90%', '90%', FALSE );
    //$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. $id, '90%', '80%', FALSE );
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'vmdisk_list.php?action=delete_vm&id=' . intval($id) );
    }
    return $result;
}
if (isset ( $_GET ['action'] )) {


    switch (getgpc("action","G")) {
        case 'delete_vm' :
            if ( $_GET ['action'] =='delete_vm') {
                //$table = "vmdisk";
                $id = intval(getgpc('id'));
                //$where = "where id='{$id}'";


//                $sql="SELECT name FROM  `vmdisk` WHERE id= ".$id;
//                $imageName = Database::getval( $sql, __FILE__, __LINE__ );
//                exec("rm /tmp/mnt/vmdisk/images/99/$imageName.raw");

                $sql = "DELETE FROM `vslab`.`vmdisk` WHERE `vmdisk`.`id` = {$id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "vmdisk_list.php";
                tb_close ( $redirect_url );
            }
            break;
        case 'lock' :
            $id = getgpc('id');
            unlink("/tmp/www/$id.html");

            $message = lock_unlock_vmdisk ( 'lock', getgpc('id') );
            Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );


            break;
        case 'unlock' :
            $vmid = getgpc('id');
            $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype ");


            $message = lock_unlock_vmdisk ( 'unlock', getgpc('id') );
            Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );

            break;

    }
}


function active_filter($active, $url_params, $row) {
    global $_user, $_configuration;
        $code = $row['0'];
    if (file_exists("/tmp/www/$code.html")) {
        $action = 'lock';
        $image = 'right';

    }
    else {
        $action = 'unlock';
        $image = 'wrong';

    }
         $result = '<a href="vmdisk_list.php?action=' . $action . '&amp;id=' . $row ['0'] . '">' . Display::return_icon ( $image . '.gif', get_lang ( ucfirst ( $action ) ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';

    return $result;
}
function lock_unlock_vmdisk($status, $id,$row) {
    $vmid = intval($id);

    $name = "select name,ISO,boot  FROM  vmdisk   WHERE id =".  intval($id);

    $res = api_sql_query ( $name, __FILE__, __LINE__ );
    $out = Database::fetch_row ( $res);

    $hostname = $out[0];
    $disktype = 'raw';
    $user_table = Database::get_main_table ( vmdisk );

    if ($status == 'lock') { //锁定
        $status_db = '0';
        $return_message = get_lang ( 'UserLocked' );

        $vmid = $vmid+1024;
        //$output = exec("sudo -u root qm stop $vmid ");
        $output = exec("sudo -u root /sbin/cloudvmstop.sh $vmid ");
    }
    if ($status == 'unlock') { //解锁
        $status_db = '1';
        $return_message = get_lang ( 'UserUnlocked' );
        if($out[1]){
            $ISO = $out[1];
            if($out[2]){
                $boot = $out[2];
                $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype $ISO $boot");
            }else{
                $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype $ISO");
            }

        }else{
            $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype ");
        }

    }

    if (($status_db == '1' or $status_db == '0') and is_numeric ( $id )) {
        $sql = "UPDATE $user_table SET active='" . escape ( $status_db ) . "' WHERE id='" . escape ( intval($id) )  .  "'";
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    }

    if ($result > 0) {
        return $return_message;
    }
}

function batch_lock_unlock_vmdisk($action, $ids = array()) {
    $user_table = Database::get_main_table ( vmdisk );
    global $_configuration;
    if ($action == 'batchLock') { //锁定
        $status_db = '0';
    }
    if ($action == 'batchUnlock') { //解锁
        $status_db = '1';
    }

    if (is_array ( $ids ) && count ( $ids )) {
        $sql = "UPDATE $user_table SET active='" . $status_db . "' WHERE id IN (" . implode ( ",", $ids ) . ") '" ;
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    }

    //api_logging ( get_lang ( "BatchLockUnlockUser" ) . ": action=" . $action . ",user_id=" . implode ( ",", $ids ), 'USER' );

    return $result;

}
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (id LIKE '%" . intval(trim ( $keyword )) . "%')";
    }

    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_vmdisk() {
    $vmdisk = Database::get_main_table ( vmdisk);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $vmdisk;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
//changzf
function get_vm_data($from, $number_of_items, $column, $direction) {
    $networkmap = Database::get_main_table ( vmdisk);
    //$sql = "select id as co5,id as co6,name as co7te,id as co8, id as co9 FROM  $networkmap ";
    $sql = "select id as co8,category as co9,name as co10,version as co11,size as co12 ,memory as co13,CPU_number as co14,NIC_type as co15,type as co16,active as co17,id as co18,id as co19,id as co21,id as co22 FROM  $networkmap ";

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
//Display::display_header ( $tool_name );
include_once ('header.inc.php');

if($platform==1){
    $platform_n='渗透';
}
if($platform==2){
    $platform_n='靶机';
}
if($platform==3){
    $platform_n='网络';
}

$html = '<div id="demo" class="yui-navset">';
//$html .= '<ul class="yui-nav">';
//$html .= '<li class="selected"><a href="' . URL_APPEND . 'main/admin/vmdisk/vmdisk_list.php"><em>' . get_lang ( '虚拟化模板管理' ) . '</em></a></li>';
//$html .= '<li><a href="' . URL_APPEND . 'main/admin/vmdisk/vmdiskimg_list.php"><em> ' . get_lang ( '虚拟化镜像管理' ) . '</em></a></li>';
//$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

//by changzf


//echo '&nbsp;&nbsp;' . link_button ( 'excel.gif', '虚拟模板迁移', 'iso_list.php', '60%', '70%' );


$table = new SortableTable ( 'vmdisk', 'get_number_of_vmdisk', 'get_vm_data',2, NUMBER_PAGE  );


$table->set_header ( 0, '序号', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 1, '类别', false, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( 2, '名称', false, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( 3, '版本' , false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 4, '大小', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 5, '内存', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 6, 'CPU数量', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 7, '网卡类型', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 8, '类型', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 9, '状态', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 10, '连接控制', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( 11, 'ISO', false, null, array ('style' => 'width:80px;text-align:center' ) );
$table->set_header ( 12, '编辑', false, null, array ('style' => 'width:80px;text-align:center' ) );
$table->set_header ( 13, '删除', false, null, array ('style' => 'width:80px;text-align:center' ) );

$table->set_column_filter (1, 'CD_mirror' );
$table->set_column_filter (7, 'NIC_type' );
$table->set_column_filter (8, 'type_flter' );
$table->set_column_filter ( 9, 'active_filter' );
$table->set_column_filter ( 10, 'console_filter' );
$table->set_column_filter ( 11, 'modify_filter' );
$table->set_column_filter ( 12, 'edit_filter' );
$table->set_column_filter ( 13, 'delete_filter' );

//Display::display_footer ( TRUE );
?>


<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/penetration/index.php">平台首页</a> &gt; <?=$platform_n;?>虚拟模板管理</h4>
    <div class="managerTool">
<!--        	<span class="searchtxt right">-->
<!--            	<img src="images/excel.gif" align="absmiddle">-->
<!--               	<a href="#" title="新建虚拟模板">新建虚拟模板</a>-->
<!--            </span>-->
<!--            <span class="searchtxt right">-->
<!--            	<img src="images/excel.gif" align="absmiddle">-->
<!--               	<a href="#" title="新建虚拟模板">光盘镜像管理</a>-->
<!--            </span>-->
		<span class="searchtxt right">
        	 <?php
       			 echo '&nbsp;&nbsp;' . link_button ( 'excel.gif', '新建虚拟模板', 'vmdisk_new.php', '90%', '70%' );
			  ?>
        </span>
        <span class="searchtxt right">
        	<?php 
				echo '&nbsp;&nbsp;' . link_button ( 'excel.gif', '光盘镜像管理', 'iso_list.php', '80%', '70%' );
        	?>
        </span>
    </div>
    <article class="module width_full hidden">
        <table cellpadding="0" cellspacing="0" class="p-table">
            <tbody>
<?php
$table->display ();
?>
            </tbody>
        </table>
    </article>
</section>
</body>
</html>
