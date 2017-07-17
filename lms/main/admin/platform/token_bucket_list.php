<?php
/**
==============================================================================
 * token bucket
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/admin/token_bucket/token_bucket_list.php';


if(mysql_num_rows(mysql_query("SHOW TABLES LIKE token_bucket"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `token_bucket` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `types` varchar(50) NOT NULL,
      `token_bucket_name` varchar(128) NOT NULL,
      `ranges` varchar(256) NOT NULL,
      `parameter` varchar(256) NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

function types_filter($types){
    $result="";
    if($types=='1'){
        $result='系统默认';
    }else{
        $result='自定义';
    }
    return $result;
}
function ranges_filter($ranges){
    $results = unserialize($ranges);
    $result=$results[0]." 至 ".$results[1];

    return $result;

}


function edit_filter($id, $url_params) {
    $result = "";
    $result .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'token_bucket_edit.php?action=edit&edit_id='.intval($id), '70%', '80%', FALSE );
    return $result;
}


function delete_filter($id) {
    $result = "";
    $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'token_bucket_list.php?action=delete_token_bucket&id=' . intval($id) );
    return $result;
}

if (isset ( $_GET ['action'] )) {
    switch (getgpc("action","G")) {
        case 'delete_token_bucket' :
            if ( $_GET ['action'] =='delete_token_bucket') {
                $id = intval(htmlspecialchars($_GET['id']));
                $sql = "DELETE FROM `vslab`.`token_bucket` WHERE `token_bucket`.`id` = $id";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                tb_close ( "token_bucket_list.php");
            }
            break;

    }
}

if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        // 批量删除课程
        case 'delete_token_buckets' :
            $deleted_vm_count = 0;
            $vm_id = (getgpc('networkmap'));
            if (count ( $vm_id ) > 0) {
                foreach ( $vm_id as $index => $id ) {

                    $sql = "DELETE FROM `vslab`.`token_bucket` WHERE id='" . intval($id) . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang('删除所选') . "id=" . intval($id);
//                    api_logging ( $log_msg, 'networkmap', 'dfgdfgdfg' );
                }
            }

    }
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
    $vmdisk = Database::get_main_table (token_bucket);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $vmdisk;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
//changzf
function get_vm_data($from, $number_of_items, $column, $direction) {
    $token_bucket = Database::get_main_table (token_bucket);
//    $sql = "select id as co6,id as co7,types as co8,token_bucket_name as co9,ranges as co10,  id as co11,id as co12 FROM  $token_bucket ";
//    $sql = "select id as co6,id as co7,types as co8,token_bucket_name as co9,ranges as co10,  id as co11 FROM  $token_bucket ";
    $sql = "select id as co4,types as co5,token_bucket_name as co6,ranges as co7 FROM  $token_bucket ";

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


//by changzf

//echo '&nbsp;&nbsp;' . link_button ( 'links_ad.gif', '新增令牌桶', 'token_bucket_new.php', '80%', '70%' );



$table = new SortableTable ( 'vmdisk', 'get_number_of_vmdisk', 'get_vm_data',2, NUMBER_PAGE  );
//$table->set_additional_parameters ( $parameters );

//$actions = array ('delete' => get_lang ( 'BatchDelete' ) );
//$table->set_form_actions ( $actions );

$idx = 0;
//$table->set_header ( $idx ++, '', false, null,array ('style' => 'width:100px;' ) );
$table->set_header ( $idx ++, '序号', false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( $idx ++, '类别', false, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( $idx ++, '令牌桶范围' , false, null, array ('style' => 'width:100px;text-align:center' ) );
//$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:80px;text-align:center' ) );
//$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:80px;text-align:center' ) );
//$table->set_form_actions ( array ('delete_token_buckets' => '删除所选项' ), 'networkmap' );

$table->set_column_filter (1, 'types_filter' );
$table->set_column_filter (3, 'ranges_filter' );
//$table->set_column_filter ( 5, 'edit_filter' );
//$table->set_column_filter ( 5, 'delete_filter' );
//Display::display_footer ( TRUE );
?>
<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/penetration/index.php">平台首页</a> &gt; 令牌桶管理</h4>
    <div class="managerTool">
		<span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'links_ad.gif', '令牌桶状态', 'token_bucket_iframe.php',null,null);?>
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