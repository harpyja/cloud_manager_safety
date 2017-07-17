<?php
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_ios'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_ios` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(128) NOT NULL,
          `filename` varchar(128) NOT NULL,
          `idle` text NOT NULL,
          `type` text NOT NULL,
          `ram` int(128) NOT NULL,
          `nvram` int(128) NOT NULL,
          `slot_number` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
function edit_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = link_button ( 'edit.gif', 'Edit', 'labs_ios_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'labs_ios.php?action=delete&id=' . $id );
    }
    return $result;
}
$action=getgpc('action','G');


if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=intval(getgpc('id','G'));
            if ( isset($delete_id)){
                //delete file
                $filename_sql="SELECT `filename` from `labs_ios` where `id`=".$delete_id;
                $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);
                unlink(URL_ROOT."/www/lms/main/admin/router/file/".$get_filename);

                //delete mysql
                $sql = "DELETE FROM `vslab`.`labs_ios` WHERE `labs_ios`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "labs_ios.php";
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
                    //delete file
                    $filename_sql="SELECT `filename` from `labs_ios` where `id`=".$id;
                    $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);
                    unlink(URL_ROOT."/www/lms/main/admin/router/file/".$get_filename);

                    //delete mysql
                    $sql = "DELETE FROM `vslab`.`labs_ios` WHERE id='" . $id . "'";
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
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR name LIKE '%" . trim ( $keyword ) . "%' OR filename LIKE '%" . trim ( $keyword ) . "%' OR type LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )));
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_ios() {
    $labs_ios = Database::get_main_table (labs_ios);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $labs_ios;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_ios_data($from, $number_of_items, $column, $direction) {
    $labs_ios = Database::get_main_table (labs_ios);
    $sql = "select `id`,`id`,`name`,`filename`,`idle`,`type`,`slot_number`,`ram`,`nvram`,`id`,`id` FROM $labs_ios";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
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

$html = '<div id="demo" class="yui-navset">';
$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_ios', 'get_labs_ios_data',2, NUMBER_PAGE  );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '编号', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:18%;text-align:center' ) );
$table->set_header ( $idx ++, '文件名称', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'idle', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '类型' , false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'slot数量' , false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'ram', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'nvram', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:5%;text-align:center' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
$table->set_column_filter ( 9, 'edit_filter' );
$table->set_column_filter ( 10, 'delete_filter' );



//Display::display_footer ( TRUE );
?>


<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 路由交换管理</h4>
    <div class="managerSearch">
        <span class="searchtxt right">
            <?php echo '&nbsp;&nbsp;' . link_button ( 'add.gif', '添加', 'labs_ios_add.php', '90%', '70%' );?>
        </span>
        <?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>

