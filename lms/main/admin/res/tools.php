<?php
/**
==============================================================================
==============================================================================
 */

$language_file = 'admin';
$cidReset = true;
include_once ('../../inc/global.inc.php');
//api_protect_admin_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'system_announcements.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'tools'"))!=1){//添加设备
    $sql_insert ="CREATE TABLE IF NOT EXISTS `tools` (
      `id` int(10) NOT NULL AUTO_INCREMENT,
      `title` varchar(128) NOT NULL,
      `created_user` int(11) DEFAULT NULL,
      `date_start` datetime NOT NULL,
      `visible` tinyint(1) NOT NULL,
      `content` text NOT NULL,
      `file` varchar(128) DEFAULT NULL,
      `type` varchar(128) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$id = intval(getgpc ( 'id' ));

$tool_name = get_lang ( 'SystemAnnouncements' );
$htmlHeadXtra [] = Display::display_thickbox ();

$form_action = getgpc ( "action" );
$redirect_url = 'main/admin/misc/tools.php';
if (isset ( $_GET ['action'] )) {    //set_visibility ( $id, 1 ); 更改状态值
    switch ($form_action) {
        case "make_visible" : //显示
            $sql="UPDATE  `vslab`.`tools` SET  `visible` =  '1' WHERE  `tools`.`id` =".$id;
            api_sql_query($sql ,__FILE__,__LINE__);
           // tb_close('tools');
            break;
        case "make_invisible" : //隐藏
            $sql="UPDATE  `vslab`.`tools` SET  `visible` =  '0' WHERE  `tools`.`id` =".$id;
            api_sql_query($sql ,__FILE__,__LINE__);
            //tb_close('tools');
            break;
        case "delete" : // 删除
            $del_sql='select `tools`.`file` FROM `vslab`.`tools` WHERE `id`='.$id;
            $files=Database::getval($del_sql,__FILE__,__LINE__);

            //delete sql
            $sql="DELETE FROM `vslab`.`tools` WHERE `tools`.`id` = ".$id;
            $result=api_sql_query ( $sql, __FILE__, __LINE__ );

            $path= URL_ROOT.'/www'.URL_APPEDND.'/storage/tools/';
            chmod($path, 0777);

            if($result!==0){
                //delete file
                unlink($path.$files);
            }
            tb_close('tools.php');
            break;
    }
}

if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $number_of_deleted_users = 0;
            foreach ( $_POST ['id'] as $index => $id ) {
                $del_sql='select `tools`.`file` FROM `vslab`.`tools` WHERE `id`='.intval($id);
                $files=Database::getval($del_sql,__FILE__,__LINE__);

                //delete sql
                $sql="DELETE FROM `vslab`.`tools` WHERE `tools`.`id` = ".intval($id);
                $result=api_sql_query ( $sql, __FILE__, __LINE__ );

                $path= URL_ROOT.'/www'.URL_APPEDND.'/storage/tools/';
                chmod($path, 0777);

                if($result!==0){
                    //delete file
                    unlink($path.$files);
                }
                $log_msg = get_lang('删除所选') . "id=" . intval($id);
                api_logging ( $log_msg, 'tools', 'tools' );

            }
            tb_close('tools.php');
            break;
    }
}
function active_filter($active, $url_params, $row) {  //显示，隐藏----图片更改
    global $_user, $_configuration;
    if (isRoot ( $row [1] )) return '';

    if ($active == '1') {
        $action = 'make_visible';
        $image = 'right';
    }
    if ($active == '0') {
        $action = 'make_invisible';
        $image = 'wrong';
    }

    if ($row [0] != $_user ['user_id']) {
       $result="<a href=\"?id=" . $row ['0'] . "&amp;action=" . ($row ['4']==1 ? 'make_invisible' : 'make_visible') . "\">" . Display::return_icon (  $row ['4']==1? 'visible.gif' : 'invisible.gif' ) . "</a>";
    }
    return $result;
}
function modify_filter($id, $url_params) {
    global $_configuration, $root_user_id;
    $result ='';
    $result .= link_button ( 'synthese_view.gif', '查看工具信息', 'tools_info.php?action=info&id=' . intval($id), '90%', '70%', FALSE );

    //当状态为隐藏时可以编辑，显示时不能编辑
    $sql1="select visible from tools where id=".  intval($id);
    $visible=Database::getval($sql1);
    if($visible=="0"){
        $result .= '&nbsp;' . link_button ( 'edit.gif', '编辑', 'tools_update.php?id=' . intval($id), '90%', '70%', FALSE );
    }
    if (! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'tools.php?action=delete&id=' .intval($id) );
    }

    return $result;
}


Display::display_header ( $tool_name );
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
//$form->addElement ( 'text', 'keyword', null, array ('style' => "width:130px", 'class' => 'inputText', 'title' => '' ) );
$form->addElement ( 'text', 'keyword', null, array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

//新增按钮

//列表
$sql_where = "";
if (isset ( $_GET ['keyword'] )) {
    if($_GET ['keyword']=='输入搜索关键词'){
        $_GET ['keyword']='';
    }
    $keyword = trim ( Database::escape_str (  getgpc("keyword","G"), TRUE ) );
    if (! empty ( $keyword )) {
        $sql_where .= " title LIKE '%" . $keyword . "%'
                        or date_start LIKE '%" . $keyword . "%'
                        or content LIKE '%" . $keyword . "%'
                        or id LIKE '%" . intval($keyword) . "%'";
    }
}

//获取系统公告数据
$sql = "SELECT  id	AS col0,
                 	title	AS col1,
                 	created_user 	AS col2,
                 	date_start 	AS col3,
                 	visible 	AS col4,
                 	id 	AS col5
			FROM  tools";
//    $sql_where = get_sqlwhere ();
if ($sql_where) $sql .= " WHERE " . $sql_where;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$ress = array ();
while ( $ress = Database::fetch_row ( $res ) ) {
    //获取公布者
    $ress[2]=Database::getval("select `firstname` from `user` where `user_id`=$ress[2]");
    $announcement_data [] = $ress;
}

$table = new SortableTableFromArray ( $announcement_data, 2, NUMBER_PAGE, 'array_tools' );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( '工具名称' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'CreatedUser' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( '创建时间' ), true, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Status' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:10%' ) );

$table->set_column_filter ( 4, 'active_filter' );
$table->set_column_filter ( 5, 'modify_filter' );

$actions = array ('deletes' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column system open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>
        &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 导调工具 </h4>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                	<span class="searchtxt right">
                    <?php
                        echo link_button ( 'copy.gif', '新增工具', 'tools_add.php?action=add', '90%', '70%' );
                        ?>
					</span>
                <!--                    <span class="searchtxt right">-->
                <!--                    --><?php
//                      echo link_button ( 'new_folder.gif', 'CategoriesMgr', ' category_list.php?module=sys_announce', '90%', '80%' );
//                    ?>
                <!--                    </span>-->
                <?php $form->display ();?>
            </div>
            <article class="module width_full hidden">
                <?php $table->display ();?>
            </article>
        </div>
    </div>
</section>
</body>
</html>