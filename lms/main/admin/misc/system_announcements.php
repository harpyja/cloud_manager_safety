<?php
/**
 ==============================================================================
 ==============================================================================
 */

$language_file = 'admin';
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'system_announcements.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$id = intval(getgpc ( 'id' ));

$tool_name = get_lang ( 'SystemAnnouncements' );
$htmlHeadXtra [] = Display::display_thickbox ();

$form_action = getgpc ( "action" );
$redirect_url = 'main/admin/misc/system_announcements.php';
if (isset ( $_GET ['action'] )) {    //set_visibility ( $id, 1 ); 更改状态值
	switch ($form_action) {
		case "make_visible" : //显示
//			SystemAnnouncementManager::set_visibility ( $id, 1 );
            $sql = "UPDATE `sys_announcement` SET `visible` = 1 ,`date_start`    ='".date ( 'Y-m-d H:i' )."' WHERE id=" .$id;
            api_sql_query ( $sql, __FILE__, __LINE__ );echo $sql;
            api_redirect('system_announcements.php');
			break;
		case "make_invisible" : //隐藏
            $sql = "UPDATE `sys_announcement` SET `visible` = 0,`date_start`    ='".date ( 'Y-m-d H:i' )."' WHERE id=" .$id;
            api_sql_query ( $sql, __FILE__, __LINE__ );echo $sql;
            api_redirect('system_announcements.php');
            break;
		case "delete" : // 删除
		//	SystemAnnouncementManager::delete_announcement ($id);   
                        $sql = "DELETE FROM `sys_announcement` WHERE id='" . $id . "'";
                        api_sql_query ( $sql, __FILE__, __LINE__ );
			$log_msg = get_lang ( 'DelAnnouncement' ) . "id=" . $id;
			api_logging ( $log_msg, 'ANNOUNCEMENT', 'DelAnnouncement' );
                        api_redirect('system_announcements.php');
			//Display::display_msgbox ( get_lang ( 'AnnouncementDeleted' ), $redirect_url );
			break;
	}
}

if (isset ( $_POST ['action'] )) { 
    switch (getgpc("action","P")) {
        case 'deletes' :
            $number_of_deleted_users = 0;
            foreach ( $_POST['id'] as $index => $id ) {
                $sql = "DELETE FROM `sys_announcement` WHERE id='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );

                $log_msg = get_lang('删除所选') . "id=" . $id;
                api_logging ( $log_msg, 'announcement', 'announcement' );
                
            }
            break;
            tb_close($redirect_url);
	}
}
function active_filter($active, $url_params, $row) {  //显示，隐藏----图片更改
    global $_user, $_configuration;
    if (isRoot ( $row [1] )) return '';

    $result="<a href=\"?id=" . $row ['0'] . "&amp;action=" . ($row ['4']==1 ? 'make_invisible' : 'make_visible') . "\">" . Display::return_icon (  $row ['4']==1? 'visible.gif' : 'invisible.gif' ) . "</a>";

    return $result;
}
function modify_filter($id, $url_params) {
    global $_configuration, $root_user_id;
    $result ='';
    $result .= link_button ( 'synthese_view.gif', '查看', 'system_announcement.php?action=info&id=' . intval($id), '80%', '70%', FALSE );

    //当状态为隐藏时可以编辑，显示时不能编辑
    $sql1="select visible from sys_announcement where id=".intval($id);
    $visible=Database::getval($sql1);
    if($visible=="0"){
        $result .= '&nbsp;' . link_button ( 'edit.gif', '编辑', 'system_announcement_update.php?id=' . intval($id), '80%', '70%', FALSE );
    }
    if (! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'system_announcements.php?action=delete&id=' .intval($id) );
    }

    return $result;
}
include_once ('../../inc/header.inc.php');
//Display::display_header ( $tool_name );
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
	$keyword = trim ( Database::escape_str (getgpc("keyword","G"), TRUE ) );
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
			FROM  sys_announcement";
//    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $ress = array ();
    while ( $ress = Database::fetch_row ( $res ) ) {
        //获取公布者
        $ress[2]=Database::getval("select `firstname` from `user` where `user_id`=$ress[2]");
        $announcement_data [] = $ress;
    }

$table = new SortableTableFromArray ( $announcement_data, 2, NUMBER_PAGE, 'array_system_announcements' );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'Title' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'CreatedUser' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'PublishTime' ), true, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Status' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:10%' ) );

$table->set_column_filter ( 4, 'active_filter' );
$table->set_column_filter ( 5, 'modify_filter' );

$actions = array ('deletes' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

if($platform==3){
    $nav='system';
}else{
    $nav='systeminfo';
}
?>
<aside id="sidebar" class="column <?=$nav?> open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统公告 </h4>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                	<span class="searchtxt right">
                    <?php
                    echo link_button ( 'announce_add.gif', 'AddAnnouncement', 'system_announcement_add.php?action=add', '80%', '70%' );
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
