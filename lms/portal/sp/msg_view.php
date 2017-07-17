<?php

include_once ("../../main/inc/global.inc.php");

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");

$user_list = WhoIsOnline ( api_get_user_id (), null, api_get_setting ( 'time_limit_whosonline' ) );
$total = count ( $user_list );
        

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$id = getgpc ( 'id' );

//$tool_name = get_lang ( 'SystemAnnouncements' );
//  $htmlHeadXtra [] = Display::display_thickbox ();
//Display::display_header ( $tool_name ); 



$form_action = getgpc ( "action" );

function edit_filter($id, $url_params) {
    global $_configuration, $root_user_id;
    $created_user=Database::getval("select created_user from message where id=$id");
    $result ='';
    $result.=link_button ( 'announce_add.gif', "查看消息", 'msg_show.php?created_user=' .$created_user , '90%', '60%',FALSE );
    return $result;
}
function action_filter($id, $url_params) {
    global $_configuration, $root_user_id;
//    $created_user=Database::getval("select created_user from message where id=$id");
    $result ='';
//    $result.=link_button ( 'announce_add.gif', "查看消息", 'msg_show.php?created_user=' .$created_user , '90%', '60%',FALSE );
    //当状态为隐藏时可以编辑，显示时不能编辑
    $sql1="select visible from sys_announcement where id=$id";
    $visible=Database::getval($sql1);
    if (! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', '清除会话', 'msg_view.php?action=delete&id=' .$id );
    }

    return $result;
}
//批量处理
if (isset ( $_POST ['action'] )) { 
    switch ($_POST ['action']) {
        case 'deletes' :
            $number_of_deleted_users = 0;
            foreach ($_POST['id']   as $index => $id ) {
//                $content= DATABASE::getval("select content from message where id =".$id,__FILE__,__LINE__);
                $sqlm=  "select  created_user,recipient  from message where  id='".intval($id)."'";
                $message_arrs=api_sql_query_array_assoc ( $sqlm,__FILE__,__LINE__);
                if($message_arrs[0]['created_user']!=='' && $message_arrs[0]['recipient']!=='' ){
                    $sql= "DELETE FROM `message` WHERE  `created_user`=".$message_arrs[0]['created_user']." and `recipient`=".$message_arrs[0]['recipient'];
                    //echo $sqld;
                    $res1=api_sql_query ( $sql, __FILE__, __LINE__ );
                    if($res1){
                        tb_close('msg_view.php');
                    }
                }
                
            }
            break;
    }
}
if ($_GET ['id']!=='' && $_GET ['action']=='delete') {
                $sql_select=  "select  created_user,recipient  from message where  id='".intval(getgpc('id','G'))."'";
                $message_arr=api_sql_query_array_assoc ( $sql_select,__FILE__,__LINE__);
//                var_dump($message_arr);
                if($message_arr[0]['created_user']!=='' && $message_arr[0]['recipient']!=='' ){
                    $sql_delete= "DELETE FROM `message` WHERE  `created_user`='".$message_arr[0]['created_user']."' and `recipient`='".$message_arr[0]['recipient']."'";
//                    echo $sql_delete;
                    $res=api_sql_query ( $sql_delete, __FILE__, __LINE__ );
//                    echo $res;
                    if($res){
                        tb_close('msg_view.php');
                    }
                }
}
      
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$form->addElement ( 'text', 'keyword', null, array ('style' => "width:200px", 'title' => '内容/时间','class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

//新增按钮

//列表
$sql_where = "";
if (isset ( $_GET ['keyword'] )) {
    if($_GET ['keyword']=='输入搜索关键词'){
        $_GET ['keyword']='';
    }
	$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
	if (! empty ( $keyword )) {
		$sql_where .= " date_start LIKE '%" . $keyword . "%'
                                or content LIKE '%" . $keyword . "%'
                                or id LIKE '%" . $keyword . "%'";
	}
}
       $usid=api_get_user_id ();  
       
       function read_filter($created_user){
           global $usid;
           $sql="select count(*) from message where created_user=$created_user and recipient=$usid and status=1";
           $count=Database::getval($sql);
           
           return $count;
       }
       
       function unread_filter($created_user){
           global $usid;
           $sql="select count(*) from message where created_user=$created_user and recipient=$usid and status=0";
           $count=Database::getval($sql);
           
           return $count;
       }
       
//获取系统公告数据 
    $sql = "SELECT `id`,`created_user`, `created_user`,`created_user`,  `id` ,  `id` 
        FROM `message` where recipient=$usid"; 
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    $sql.=' GROUP BY `created_user` ORDER BY `message`.`date_start` DESC' ;
    //echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $ress = array ();
    while ( $ress = Database::fetch_row ( $res ) ) {
        //获取公布者
        $cu=$ress[1];
        $firsname=Database::getval("select `firstname` from `user` where `user_id`=$ress[1]");
        $ress[1]=link_button ( '', "$firsname", 'msg_show.php?created_user=' .$cu , '90%', '60%', TRUE );
        $announcement_data [] = $ress;
    }

$table = new SortableTableFromArray ( $announcement_data, 2, NUMBER_PAGE, 'array_system_announcements' );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( '发信人' ), false, null, array ('style' => 'width:15%' ) );
$table->set_header ( $idx ++, get_lang ( '已读' ), false   , null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( '未读' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( '查看消息' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:20%' ) );
//$table->set_column_filter ( 1, 'look_filter' );
$table->set_column_filter ( 2, 'read_filter' );
$table->set_column_filter ( 3, 'unread_filter' );
$table->set_column_filter ( 4, 'edit_filter' );
$table->set_column_filter ( 5, 'action_filter' );

$actions = array ('deletes' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

if($platform==3){
    $nav='system';
}else{
    $nav='systeminfo';
}
?>
<!--<aside id="sidebar" class="column msg open">
    <div id="flexButton" class="closeButton close"></div>
</aside>
--><style>
    .p-table{
        width:99%;
        margin-left: 5px
    }
</style>

<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
    <div class="b-30"></div> 
        <div class="g-container f-cb">
            <div class="g-sd1 nav">
                <div class="m-sidebr" id="j-cates">
                    <ul class="u-categ f-cb">
                        <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="站内信" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1"  style="background-color:#13a654;color:#FFF" title="站内信" >站内信</a>
                        </li>
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="短消息" href="msg_view.php" style="color:green;font-weight:bold">短消息</a>
                        </li> 
                    </ul>
                </div>
            </div>
                  
            <div class="g-mn1" > 
                <div class="g-mn1c m-cnt" style="display:block;">
                    <div class="top f-cb j-top">
                        <h3 class="left f-thide j-cateTitle title">
                            <span class="f-fc6 f-fs1" id="j-catTitle">站内信</span>
                        </h3>
                    </div>
                    <div class="j-list lists" id="j-list"> 
                        <div class="u-content">
                            <h3 class="sub-simple u-course-title"></h3> 
                            <?php $table->display ();?>
                        </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include_once './inc/page_footer.php';
?>
</body>
</html>