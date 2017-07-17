<?php
/**
==============================================================================
 * 路由交换模块管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_mod'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_mod` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mod_name` varchar(128) NOT NULL,
          `type` varchar(128) DEFAULT NULL,
          `slot0` int(11) NOT NULL,
          `size` varchar(128) DEFAULT NULL,
          `interface_type` varchar(128) NOT NULL,
          `description` text,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

function type_filter($type){
    $result  ='';
    $types=unserialize($type);
    for($i=0;$i<count($types);$i++){
        $result.=$types[$i]."; ";
    }
   return $result;
}
function slot0_filter($slot0){
    if($slot0==1){
        return "已定义";
    }else{
        return "未定义";
    }
}

function size_filter($size){
    $result  ='';
    $sizes=explode(',',$size);
    if($sizes[1]==0 OR $sizes[1]==''){
     }else{
        $result .=$sizes[0].'至'.$sizes[1];
    }
    return $result;
}

function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result =  link_button ( 'edit.gif', 'Edit', 'labs_mod_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'labs_mod.php?action=delete&id=' . $id );
    }
    return $result;
}
$action=getgpc('action','G');

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=intval(getgpc('id','G'));
            if ( isset($delete_id)){
                $sql = "DELETE FROM `vslab`.`labs_mod` WHERE `labs_mod`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "labs_mod.php";
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

                    $sql = "DELETE FROM `vslab`.`labs_mod` WHERE id='" . $id . "'";
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
        $sql_where .= " AND (`id` LIKE '%" . intval ( $keyword ) . "%' OR `mod_name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%' OR `interface_type` LIKE '%" . trim ( $keyword ) . "%'
        OR `type` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_mod() {
    $labs_mod = Database::get_main_table (labs_mod);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_mod;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_mod_data($from, $number_of_items, $column, $direction) {
    $labs_mod = Database::get_main_table (labs_mod);
    $sql = "select `id`,`id`,`mod_name`,`type`,`slot0`,`size`,`interface_type`,`description`,`id`,`id` FROM ".$labs_mod;

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

$html = '<div id="demo" class="yui-navset">';
$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_mod', 'get_labs_mod_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false);
$table->set_header ( $idx ++, '编号', false, null, array ('style' => ' text-align:center;width:5%' ));
$table->set_header ( $idx ++, '模块名称', false, null, array ('style' => 'text-align:center;width:18%' ) );
$table->set_header ( $idx ++, '匹配设备' , false, null, array ('style' => 'width:20%;' ) );
$table->set_header ( $idx ++, 'slot0模块', false, null, array ('style' => 'width:8%;text-align:center;' ) );
$table->set_header ( $idx ++, '范围', false, null, array ('style' => 'width:8%;text-align:center;' ) );
$table->set_header ( $idx ++, '网卡类型', false, null, array ('style' => 'width:8%; text-align:center;' ) );
$table->set_header ( $idx ++, '描述', false, null, array ('style' => 'width:20%;' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:5%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
$table->set_column_filter ( 3, 'type_filter' );
$table->set_column_filter ( 4, 'slot0_filter' );
$table->set_column_filter ( 5, 'size_filter' );
$table->set_column_filter ( 8, 'edit_filter' );
$table->set_column_filter ( 9, 'delete_filter' );


//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 路由交换模块管理</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'labs_mod_add.php', '90%', '70%' );?>
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
