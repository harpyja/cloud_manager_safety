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
$redirect_url = 'main/admin/misc/flag.php';
if (isset ( $_GET ['action'] )) {    //set_visibility ( $id, 1 ); 更改状态值
    switch ($form_action) {
        case "make_visible" : //显示
            $sql="UPDATE  `flag` SET  `visible` =  '1' WHERE  `id` ='" . $id . "'";
            api_sql_query($sql ,__FILE__,__LINE__);
            api_redirect ('flag.php');
            break;
        case "make_invisible" : //隐藏
            $sql="UPDATE  `flag` SET  `visible` =  '0' WHERE `id` ='" . $id . "'";
        
            api_sql_query($sql ,__FILE__,__LINE__);
            api_redirect('flag.php');
            break;
        case "delete" : // 删除
            $sql = "DELETE FROM `vslab`.`flag` WHERE id='" . $id . "'";
            api_sql_query ( $sql, __FILE__, __LINE__ );
            api_redirect('flag.php');
            break;
    }
}

if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $number_of_deleted_users = 0;
            foreach ( getgpc('id') as $index => $id ) {
                $sql = "DELETE FROM `vslab`.`flag` WHERE id='" . intval($id) . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );

                $log_msg = get_lang('删除所选') . "id=" . intval($id);
                api_logging ( $log_msg, 'flag', 'flag' );

            }
            break;
            tb_close($redirect_url);
    }
}
function content_filter($content) {
    $result="<pre style='text-align:left' >".$content."</pre>";
    return $result;
}
function user_filter($id) {
    $result='';
    $result .= '&nbsp;' . link_button ( 'group_add_big.gif', '查看/调度用户', 'flag_user.php?id=' . intval($id), '80%', '70%', FALSE );
    return $result;
}
function status_filter($active, $url_params, $row) {
    $result="<a href=\"?id=" . $row ['0'] . "&amp;action=" . ($row ['5']==1 ? 'make_invisible' : 'make_visible') . "\">" .
        Display::return_icon (  $row ['5']==1? 'visible.gif' : 'invisible.gif' ) . "</a>";

    return $result;
}

function modify_filter($id, $url_params) {
    global $_configuration, $root_user_id;
    $result ='';
    $result .= '&nbsp;' . link_button ( 'edit.gif', '编辑', 'flag_edit.php?id=' . intval($id), '80%', '70%', FALSE );
    if (! in_array ( intval($id), $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'flag.php?action=delete&id=' .intval($id) );
    }

    return $result;
}


include ('../../inc/header.inc.php');
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

//获取
$sql = "SELECT  id,title,content,created_user,date_start,visible,id,id FROM  flag";
if ($sql_where) $sql .= " WHERE " . $sql_where;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$ress = array ();
while ( $ress = Database::fetch_row ( $res ) ) {
    //获取公布者
    $ress[3]=Database::getval("select `firstname` from `user` where `user_id`=$ress[3]");
    $announcement_data [] = $ress;
}

$table = new SortableTableFromArray ( $announcement_data, 2, NUMBER_PAGE, 'array_flag' );
$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'Title' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( '旗子位置描述' ), false, null, array ('style' => 'width:40%' ) );
$table->set_header ( $idx ++, get_lang ( '发布者' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'PublishTime' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Status' ), false, null, array ('style' => 'width:5%' ) );
$table->set_header ( $idx ++, get_lang ( '用户' ), false, null, array ('style' => 'width:5%' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:5%' ) );

$table->set_column_filter ( 2, 'content_filter' );//旗子位置描述
$table->set_column_filter ( 5, 'status_filter' );//
$table->set_column_filter ( 6, 'user_filter' );//用户
$table->set_column_filter ( 7, 'modify_filter' );//操作

$actions = array ('deletes' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

?>

<aside id="sidebar" class="column flag open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;夺旗管理 &gt; 旗子位置 </h4>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                	<span class="searchtxt right">
                    <?php
                        echo link_button ( 'create.gif','发布', 'flag_add.php?action=add', '80%', '70%' );
                        ?>
					</span>

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