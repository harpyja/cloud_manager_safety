<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-2
 * Time: 下午11:40
 * To change this template use File | Settings | File Templates.
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE clouddesktopscan"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `clouddesktopscan` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `IP_address` varchar(256) NOT NULL,
      `physical_address` varchar(256) DEFAULT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE = MEMORY DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

function edit_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( intval($id), $root_user_id )) {
        $result .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'clouddesktopscan_edit.php?action=edit&id='.intval($id), '90%', '70%', FALSE );
    }
    return $result;
}

function addclouddesk_filter($id) {
    global $_user, $_configuration;
    $addclouddesk = '加入';
    $result = link_button ( '', $addclouddesk, 'clouddesktop_add.php?action=add&ids='.intval($id), '90%', '70%' );
    return $result;
}


function delete_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    //$result .= link_button ( 'edit.gif', 'Edit', 'vmdisk_edit.php?id='. $id, '90%', '90%', FALSE );
    //$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. $id, '90%', '80%', FALSE );
    if (api_is_platform_admin () && ! in_array ( intval($id), $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'clouddesktopscan.php?action=delete&id=' . intval($id) );
    }
    return $result;
}
if (isset ( $_GET ['action'] )) {
    switch (getgpc("action","G")) {
        case 'delete' :
            if ( $_GET ['action'] =='delete') {
                //$table = "vmdisk";
                $id = intval(getgpc('id'));
                $sql = "DELETE FROM `vslab`.`clouddesktopscan` WHERE `clouddesktopscan`.`id` = {$id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "clouddesktopscan.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}

//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        // 批量删除课程
        case 'delete_clouddesktopscan' :
            $deleted_clouddesktopscan_count = 0;
            $clouddesktopscan_id = $_POST['clouddesktopscan'];
            if (count ( $clouddesktopscan_id ) > 0) {
                foreach ( $clouddesktopscan_id as $index => $id ) {

                    $sql = "DELETE FROM `vslab`.`clouddesktopscan` WHERE id='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );
                }
            }
    }
}
        
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if($_POST ['keyword']=='输入搜索关键词'){  
        $_POST ['keyword']='';
    }
    if (is_not_blank ($_POST ['keyword'] )) {   
        $keyword = Database::escape_string ($_POST ['keyword'], TRUE );
        $sql_where .= " AND (
		id LIKE '%" .trim ( $keyword ). "%' 
		or IP_address  LIKE '%" . trim ( $keyword ) . "%')";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
        
function get_number_of_clouddesktop() {
    $clouddesktop = Database::get_main_table ( clouddesktopscan );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $clouddesktop;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_clouddesktop_data($from, $number_of_items, $column, $direction) {
    $networkmap = Database::get_main_table ( clouddesktopscan );
    //$sql = "select id as co5,id as co6,name as co7te,id as co8, id as co9 FROM  $networkmap ";
    $sql = "select id as co7,id as co8,IP_address as co9,physical_address as co10,id as co11,id as co12 FROM  $networkmap ";

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



$form = new FormValidator ( 'search_simple', 'post', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = "编号/IP地址";
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit" id="searchbutton"' );

$table = new SortableTable ( 'clouddesktop', 'get_number_of_clouddesktop', 'get_clouddesktop_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '编号', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, 'IP地址', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, 'MAC地址', false, null, array ('style' => ' text-align:center' ) );
$table->set_header ( $idx ++, '加入云桌面终端管理', false, null, array ('style' => ' text-align:center' ) );

$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'text-align:center' ) );
$table->set_form_actions ( array ('delete_clouddesktopscan' => get_lang ( '删除所选项' ) ), 'clouddesktopscan' );

$table->set_column_filter ( 4, 'addclouddesk_filter' );
$table->set_column_filter ( 5, 'delete_filter' );

?>


<aside id="sidebar" class="column cloud open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt; 云桌面扫描 </h4>
    <div class="managerSearch">
        <form action="#" method="post" id="searchform">
        	<span class="searchtxt right">
            <?php
            echo '&nbsp;&nbsp;' . link_button ( 'explorer.gif', '扫描', 'Scanning.php', '90%', '70%' );
            ?>
            </span>
            <?php  $form->display ();  ?>
        </form>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
              <?php $table->display ();?>
            </table>
        </form>
    </article>
</section>
</body>
</html>
