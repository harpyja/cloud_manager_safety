<?php
$language_file = 'admin';
$cidReset = true;
include_once ('../inc/global.inc.php');
//api_protect_admin_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'system_announcements.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");
        
//include ('../inc/header.inc.php');

//$user_list = WhoIsOnline ( api_get_user_id (), null, api_get_setting ( 'time_limit_whosonline' ) );
//$total = count ( $user_list );

$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];

$id = intval(getgpc ( 'id' ));

$tool_name = get_lang ( 'SystemAnnouncements' );
$htmlHeadXtra [] = Display::display_thickbox ();

$form_action = getgpc ( "action" );
       
$language_file = array ('admin', 'registration' );$cidReset = true;



//
include ('../inc/header.inc.php');

        
if($_user ["status"] == STUDENT){
        api_protect_admin_script ();
	return false;
}else{
        //return true;
}

function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    $result .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'control_list.php?action=delete&id=' . intval($id) );
    return $result;
}
$action=htmlspecialchars(getgpc ( "action","G" ));

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(getgpc ( "id","G" ));
            if ( isset($delete_id)){

                $sql = "DELETE FROM `vslab`.`task` WHERE `task`.`id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                if($result){
                    $update_sql ="UPDATE  `vmdisk` SET  `mod_type` =  '0', `task_id` =  '0' WHERE  `vmdisk`. `task_id`=".$delete_id;
                    api_sql_query($update_sql,__FILE__,__LINE__);
                }
                $redirect_url = "control_list.php";
                api_redirect ($redirect_url);
            }
            break;
    }
}

if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $labs = intval(getgpc('labs'));
            if (count ( $labs ) > 0) {
                foreach ( $labs as $index => $id ) {

                    $sql = "DELETE FROM `vslab`.`task` WHERE `task`.`id` =".  intval($id);
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    if($result){
                        $update_sql ="UPDATE  `vmdisk` SET  `mod_type` =  '0', `task_id` =  '0' WHERE  `vmdisk`. `task_id`=".$id;
                        api_sql_query($update_sql,__FILE__,__LINE__);
                    }

                    $log_msg = get_lang('删除所选') . "id=" . intval($id);
                    api_logging ( $log_msg, 'labs', 'labs' );
                }
            }
            break;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    $get_keyword=  getgpc('keyword');
    if (is_not_blank ( $get_keyword )) {
        if($get_keyword=='输入搜索关键词'){
           $get_keyword='';
        }
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (`id` LIKE '%" . intval(trim ( $keyword )) . "%' OR `name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_topo() {
    $labs_topo = Database::get_main_table (user);
    $sql = "SELECT COUNT(user_id) AS total_number_of_items FROM " . $labs_topo."  where  1 ";
    
    if(api_get_user_id ()){
        $sql.="  and user_id !=".api_get_user_id ();
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $labs_topo = Database::get_main_table (user);
        
    $sql = "select  `user_id`,`username`,`firstname`, `user_id`,`user_id` FROM user  where user_id !=".api_get_user_id ();
        
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `user_id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();
        
    while ( $arr = Database::fetch_row ( $res) ) {
        
         $arry[0]=$arr[0];
         $arry[1]=$arr[1];
         $arry[2]=$arr[2];

         $usid=  api_get_user_id();
         $sql="select count(*) from message where created_user=$arry[0] and recipient=$usid and status=0";
         $unread=Database::getval($sql);
	 if($unread>0){
	   $html="<span style='color:red'><b>".$unread."</b></span>";
	 }else{
	   $html="<span>".$unread."</span>";
	 }
	 $arry[3]=$html;

        $namesql="  select name  from group_user where userId=".$arr[4];
        $name=  Database::getval($namesql);
        $typesql="  select type  from group_user where userId=".$arr[4];
        $type=  Database::getval($typesql);
        if($type==1){
            $tys="(红方)";
        }elseif($type==2){
            $tys= "(蓝方)";
        }else{$tys='';}
      //  $arry[3]=$name.$tys;
         //  if($unread>0){
       // $arry[4]=  link_button ( 'announce_add.gif', '', 'message_single.php?action=add&created_user='.$arry[0], '90%', '60%' ).'未读消息';
        //   }else{
                     $arry[4]=  link_button ( 'announce_add.gif', '', 'message_single.php?action=add&created_user='.$arry[0], '90%', '60%' );
 
      //     }
        $arrs [] = $arry;
    }
    return $arrs;
}


$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
array_unshift($data,'---所有任务---');
$form->addElement ( 'select', 'renwu', get_lang ( '任务名称' ), $data, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

//$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '编号',  false, null, null);
$table->set_header ( $idx ++, '用户', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '姓名', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '未读', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '发送消息', false, null, array ('style' => 'width:20%;text-align:center' ) );

//$table->set_form_actions ( array ('deletes' => '删除所选项' ,'visible' => '发布所选项','invisible' => '关闭所选项'), 'labs' );

?>
<aside id="sidebar" class="column cloud1 open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/message_list.php"> 信息传递</a>  </h4>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                	<span class="searchtxt right">
                    <?php
                    echo link_button ( 'announce_add.gif', '发送全站消息', 'message_add.php?action=add', '70%', '60%' );
                  	?>
			</span>
                    <?php //$form->display ();?>
            </div>
            <article class="module width_full hidden">
                 <?php $table->display ();?>
            </article>
        </div>
    </div>
</section>
</body>
</html>
