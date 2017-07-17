<?php
/**
 ==============================================================================
 * 虚拟模版管理
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/admin/vmdisk/vmdisk_list.php';

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
    if($CD_mirror==''){
        $result='基础镜像';
    }else{
        $result=$CD_mirror;
    }
    return $result;
}


function modify_filter($code) {
	$html = "";
	$html .=  link_button ( 'cd.gif', '设置光盘镜像启动', 'ISO_edit.php?code=' . $code, '60%', '70%', FALSE );
	//$html .= '&nbsp;' . link_button ( 'add_user_big.gif', 'CourseAdmin', 'course_admins.php?code=' . $code, '70%', '76%', FALSE );
	return $html;
}

function  console_filter($code) {
    //dengxin
    $sql = 'select name from vmdisk where id='.$code;
    $system  = Database::getval ( $sql, __FILE__, __LINE__ );
    $manage=Database::getval("select `manage` from `vmtotal` where `system`='".$system."'",__FILE__ , __LINE__);
    if($manage){
        $html = "";
        $html .='<a href="vmdisk_list.php?action=join&system='.$system.'" target=_blank >连接</a>';
        return $html;
    }else{
        $html = "";
        $html .="无法连接";
        return $html;
    }
}
function export_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
         $result .=   confirm_href ( 'down_na.gif', '你确定要导出该模板？', 'export', 'vmdisk_list.php?action=export&id=' . $id );
    }
    return $result;
}
function active_filter($active, $url_params, $row) {
    global $_user, $_configuration;
        $code = $row[0];
        $nicnum=intval($row[11]);
        if($nicnum<1){
            $nicnum=1;
        }
        $manage=Database::getval("select `manage` from `vmtotal` where `system`='".$row[3]."'",__FILE__ , __LINE__);
    if ($manage) { 
        $action = 'unlock';
        $image = 'right';
 	$title="已启动";
        $Path='vmdisk_list.php?action='.$action.'&manage=1&id='.$row[0].'&system=' . $row ['3'] . '&keyword='.$_GET ['keyword'];
    }else {
        $action = 'unlock';
        $image = 'wrong';
	$title="未启动";
        $Path='../../cloud/cloudvmstart.php?action=' . $action . '&manage=1&id='.$row[0].'&system=' . $row ['3'] . '&nicnum='.$nicnum.'&keyword='.$_GET ['keyword'];
    }
      
        $result = '<a   href="'.$Path.'">' . Display::return_icon ( $image . '.gif', get_lang ( ucfirst ( $title ) ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';

    return $result;
}
function lock_unlock_vmdisk($status, $id,$row) {
    $vmid = $id;
    $name = "select name,ISO,boot  FROM  vmdisk   WHERE id = '{$id}'";
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
        $manage= '1';
        $return_message = get_lang ( 'UserUnlocked' );
        if($out[1]){
            $ISO = $out[1];
            if($out[2]){
                $boot = $out[2];
                $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype $ISO $boot $manage");
            }else{
                $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype $ISO $manage");
            }
        }else{
            $output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype $manage");
        }
    }
    if (($status_db == '1' or $status_db == '0') and is_numeric ( $id )) {
        $sql = "UPDATE $user_table SET active='" . escape ( $status_db ) . "' WHERE id='" . escape ( $id )  .  "'";
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
    return $result;
}
function edit_filter($id, $url_params){
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .=   link_button ( 'edit.gif', 'Edit', 'vmdisk_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}
function delete_filter($id, $url_params) {
    $sql="select CD_mirror from vmdisk where id=".$id;
    $res=  Database::getval($sql);    
    if($res==""){  
	if(file_exists("/tmp/delvm")){
	        $result = "";
	        global $_configuration, $root_user_id;
	        if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
	            $result .=   confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'vmdisk_list.php?action=delete_vm&id=' . $id );
	        }
	        return $result;
	}else{
        	return "默认";
	}
    }else{
        $result = "";
        global $_configuration, $root_user_id;
        if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
            $result .=   confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'vmdisk_list.php?action=delete_vm&id=' . $id );
        }
        return $result;
    }
}
//close  vmdisk
if($_GET['action']=='unlock'  && $_GET['system']!=="" && $_GET['manage']=="1"){
    $system1=  getgpc("system","G");
    $id     =  getgpc("id","G");
    $keyword="?keyword=".$_GET['keyword'];
    $vm_datas=  api_sql_query_array_assoc("select * from  `vmtotal` where `system`='".$system1."' and `manage`='1' and `lesson_id`='111111111111' limit  0,1",__FILE__,__LINE__);
    $Addres=$vm_datas[0]['addres'];
    $Vmid  =$vm_datas[0]['vmid']; 
    $output = exec("sudo -u root /usr/bin/ssh root@$Addres /sbin/cloudvmstop.sh $Vmid");
    $delsql="delete from `vmtotal` where `system`='".$system1."' and `manage`='1' and `lesson_id`='111111111111'";
    $result = api_sql_query ( $delsql, __FILE__, __LINE__ );
    
    $redirect_url = "vmdisk_list.php".$keyword;
    tb_close ( $redirect_url ); 
}
//join vmdisk
if($_GET['action']=='join'  && $_GET['system']!==""){
    $system1=  getgpc("system","G");
    $id     =  getgpc("id","G");
    $lessonId ='111111111111';
    $manage='1'; 
    $keyword="?keyword=".$_GET['keyword'];
    $vm_datas  =  api_sql_query_array_assoc("select * from  `vmtotal` where `system`='".$system1."' and `manage`='1' and `lesson_id`='111111111111' limit  0,1",__FILE__,__LINE__);
    $Addres    =  $vm_datas[0]['addres'];
    $Vmid      =  $vm_datas[0]['vmid']; 
    $proxy_port=  $vm_datas[0]['proxy_port'];
    $local_ip=$_SERVER['HTTP_HOST'];
    header("Location: http://$local_ip/lms/main/html5/cloudauto.php?lessonId=$lessonId&&host=$local_ip&port=$proxy_port&system=$system1&manage=$manage");
}

if($_GET ['action']=="delete_vm" && $_GET ['id']!==""){ 
                $id = getgpc("id",G);
                $del = getgpc("del","G");
                $vm_name=Database::getval("select `name` from `vmdisk` where `id`=".$id,__FILE__,__LINE__ );
                $CD_mirror=Database::getval("select `CD_mirror` from `vmdisk` where `id`=".$id,__FILE__,__LINE__ );//是否是增量镜像，空为基础镜像
                $sql = "DELETE FROM `".DB_NAME."`.`vmdisk` WHERE `vmdisk`.`id` = {$id}";

		if($vm_name!==''){
		        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		        if($result){
			   //$raw_file='/var/www/'.$vm_name.".raw";
			   $raw_file='/tmp/mnt/vmdisk/images/99/'.$vm_name.".raw";
                                                                if($CD_mirror!=='' or $del=='ok'){//增量镜像
                                                                   if(file_exists($raw_file)){
                                                                        exec("rm -f $raw_file");//echo "rm -r $raw_file";
                                                                   }
                                                                }
                          $log_msg = '删除虚拟模板：id=' . $id;
                          api_logging ( $log_msg, 'NETMAP', 'DeleteVmdisk' );
			}
		}
                
                $redirect_url = "vmdisk_list.php";
                tb_close ( $redirect_url ); 
}
if (isset ( $_GET ['action'] )) {
      if($_GET['keyword']){
            $redirect_url.="?keyword=".$_GET['keyword']; 
        } 
/**    switch ($_GET ['action']) {
        case 'lock' :
            $id = getgpc("id",G);
            unlink(URL_ROOT."/www/$id.html");

            if( trim($_GET ['keyword'])){
                tb_close("vmdisk_list.php?keyword=".trim($_GET ['keyword']));
            }else{
                 tb_close('vmdisk_list.php'); 
            }
            break;
        case 'unlock' :
            $vmid = getgpc("id",G);
              if( trim($_GET ['keyword'])){
                tb_close("vmdisk_list.php?keyword=".trim($_GET ['keyword']));
            }else{
                 tb_close('vmdisk_list.php'); 
            }
            break; 
    }**/
}
if (isset ( $_GET ['action'] )  && $_GET ['action']=='export' && $_GET['id']!=='') {
            $id = getgpc("id",G);
            //数据表
            $tb_vmdisk=Database::get_main_table ( vmdisk );
            $sql='select id ,category ,name ,version ,size  ,memory ,CPU_number ,NIC_type ,type,platform,vlan,active,nodeId,ISO,boot from '.$tb_vmdisk.' where id='.$id;
            $result=api_sql_query_array_assoc($sql,__FILE__,__LINE__);
            
            $tgz_folder_name=$result[0]['name'];
            $tgz_name=$result[0]['name'].".tgz";
            $path=URL_ROOT.'/www'.URL_APPEDND.'/courses/'.$tgz_folder_name;
               if(!file_exists($path)){
                    exec("mkdir  $path");
                    exec("chmod -R 777 $path");
                }
            $data=serialize($result[0]);
            file_put_contents($path.'/'.$tgz_folder_name.'.txt', $data);
//            echo $path;
            
            //raw模版文件  复制raw文件到制定路径下
            exec("cp ".URL_ROOT."/mnt/vmdisk/images/99/".$tgz_name.".raw ".$path); 
            //打包
            exec("cd $path/ ; tar -zcvf ".$tgz_name." *");
            $tar_file=$path."/".$tgz_name;
            if (file_exists($tar_file)){
                header('Content-type: application/tgz');
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=".$tgz_name);
                header("Content-Transfer-Encoding: binary");
                readfile($tar_file);
                //unlink($path);
                exec("rm -rf $path");
                exit;
            }else{
                echo "<script>alert('操作失败,请重试！');</script>";
            }
            
            $redirect_url = 'vmdisk_list.php';
            tb_close ( $redirect_url );
}
//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'delete_vms' :
            $deleted_vm_count = 0;
            $vm_id = $_POST ['networkmap'];
            if (count ( $vm_id ) > 0) {
                foreach ( $vm_id as $index => $id ) {
                    $vm_name1=Database::getval("select `name` from `vmdisk` where `id`=".$id,__FILE__,__LINE__ );
                    $CD_mirror1=Database::getval("select `CD_mirror` from `vmdisk` where `id`=".$id,__FILE__,__LINE__ );//是否是增量镜像，空为基础镜像
        
                      $raw_file=URL_ROOT.'/mnt/vmdisk/images/99/'.$vm_name1.".raw";
                    if($CD_mirror1!==''){//增量镜像
                           $sql = "DELETE FROM `".DB_NAME."`.`vmdisk` WHERE `vmdisk`.`id` ='" . $id . "'";
                           $result =api_sql_query ( $sql, __FILE__, __LINE__ );
                           if($result){
                               if(file_exists($raw_file)){
                                       exec("rm -f $raw_file");//echo "rm -r $raw_file";
                               } 
                           }
                    }

                    $log_msg = '批量删除虚拟模板：id=' . $id;
                    api_logging ( $log_msg, 'NETMAP', 'DeleteVmdisk' );
                }
            }
    }
}

function platform_filter($platform){
    $result = "";
    if($platform==1){
        $result .= "渗透";
    }elseif($platform==2){
        $result .= "靶机";
    }else{
        $result .= "其他";
    }

    return $result;
}
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if($_GET ['keyword']=='输入搜索关键词'){
        $_GET ['keyword']='';
    }
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%'
                        OR category LIKE '%" . trim ( $keyword ) . "%'
                        OR name LIKE '%" . trim ( $keyword ) . "%'
                        OR version LIKE '%" . trim ( $keyword ) . "%'
                        OR size LIKE '%" . trim ( $keyword ) . "%'
                        OR CPU_number LIKE '%" . trim ( $keyword ) . "%'
                        OR vlan LIKE '%" . trim ( $keyword ) . "%'
                        )";
    }

    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( getgpc ( 'id', 'G' ) );
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
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_vm_data($from, $number_of_items, $column, $direction) {
    $networkmap = Database::get_main_table ( vmdisk);
    $sql = "select id ,id ,category ,name ,version ,size  ,memory ,CPU_number ,NIC_type ,type,platform,vlan,CD_mirror,active,Ide,Display,id ,id,id ,id  FROM  $networkmap ";
    
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " ORDER BY name,id ";
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

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip ="序号/类别/名称/版本/大小/内存/CPU数量";
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
        
if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ["keyword"] )) { $parameters ['keyword'] =  trim($_GET ['keyword']);  }
if (is_not_blank ( $_GET ["keyword_org_id"] )) $parameters ['keyword_org_id'] = trim (getgpc("keyword_org_id","G") );
 if( $_GET['all_exp']){ $parameters ['all_exp']=  getgpc("all_exp","G"); }
 if( $_GET['all_model']){ $parameters ['all_model']=  getgpc("all_model","G"); }
 if( $_GET['all_status']){ $parameters ['all_status']=  getgpc("all_status","G"); }
  if( $_GET['keyword_status']){ $parameters ['keyword_status'] =  trim($_GET ['keyword_status']);}

$table = new SortableTable ( 'vmdisk', 'get_number_of_vmdisk', 'get_vm_data',2, NUMBER_PAGE  );

$actions = array ('delete' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false, null,null );
$table->set_header ( $idx ++, '序号', false, null,null );
$table->set_header ( $idx ++, '类别', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:15%;text-align:center' ) );
$table->set_header ( $idx ++, '版本' , false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '大小', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( $idx ++, '内存', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( $idx ++, 'CPU数量', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '网卡类型', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '类型', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '平台类型', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '网卡接口数量', false, null, array ('style' => 'width:8%;text-align:center' ) );

$table->set_header ( $idx ++, '镜像', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '状态', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( $idx ++, '磁盘数量', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '显卡类型', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, '连接控制', false, null, array ('style' => 'width:5%;text-align:center' ) );
$table->set_header ( $idx ++, 'ISO', false, null, array ('style' => 'width:3%;text-align:center' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:3%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:3%;text-align:center' ) );
$actions = array ('deletes' => '删除所选项','Belate' => '更改状态为迟到','Truancy' => '更改状态为旷课','normal_attendance' => '更改状态为正常考勤');
$table->set_form_actions ( $actions );
$table->set_form_actions ( array ('delete_vms' => '删除所选项' ), 'networkmap' );
$table->set_column_filter (8, 'NIC_type' );
$table->set_column_filter (9, 'type_flter' );
$table->set_column_filter ( 10, 'platform_filter' );
$table->set_column_filter (12, 'CD_mirror' );
$table->set_column_filter ( 13, 'active_filter' );
$table->set_column_filter ( 16, 'console_filter' );
$table->set_column_filter ( 17, 'modify_filter' );
$table->set_column_filter ( 18, 'edit_filter' );
$table->set_column_filter ( 19, 'delete_filter' );
?>


<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt; 虚拟模板管理</h4>
    <div class="managerSearch">
        <?php  $form->display ();?>
		<span class="searchtxt right">
        	 <?php
       echo '&nbsp;&nbsp;' . link_button ( 'settings.gif', '磁盘镜像管理', 'vmdisk_import.php', '90%', '70%' );		
	 echo '&nbsp;&nbsp;' . link_button ( 'lp_webcs_chapter_add.gif', '新建虚拟模板', 'vmdisk_new.php', '90%', '70%' );
			echo '&nbsp;&nbsp;' . link_button ( 'cd.gif', '光盘镜像管理', 'iso_list.php', '80%', '70%' );
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
