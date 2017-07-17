<?php
//$language_file = array ("registration", "admin" );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

$table_settings_current = Database::get_main_table ( TABLE_MAIN_SETTINGS_CURRENT );
//$resultcategories = array ('客户端设置', '服务端设置' ,'数据通道设置');
$resultcategories = array ('客户端设置', '服务端设置' ,"开启数据同步服务器","数据同步");

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$strCategory = isset ( $_GET ['category'] ) ? getgpc ( 'category', 'G' ) : 'Company';

$my_category = escape ( $strCategory );
$form = new FormValidator ( 'settings', 'post', 'centralized.php?category=' . $strCategory );
Display::setTemplateSettings ( $form, '97%' );

/**Data syncchronization；changzf 2012/11/24 13:35 start */
$sql="select addres  from vmsummary ";
$res = api_sql_query( $sql, __FILE__, __LINE__ );

$vmsummarys=array();
while($vmsummary = Database::fetch_row( $res )){
    $vmsummarys[] = $vmsummary;
}

$result = count($vmsummarys);//addres count
//var_dump($vmsummarys);
//echo $result;
$table='<table cellspacing="0" cellpadding="0" class="p-table">
                          <tr style="background-color: rgb(240, 240, 240); "><th>编号</th><th>节点地址</th><th>数据操作</th></tr>';
if($result>0){
    for($i=0;$i<$result;$i++){
        $j=$i+1;
        $address=$vmsummarys[$i][0];
       // $address=explode(";",$vmsummarys[$i][0]);
       // $addres_count=sizeof($address);
      //  if($addres_count=='2'){
         //   $add1=$address[0];
         //   $add2=$address[1];
//            $table.=' <tr class="row_even">
//	                <td style="width:100px;text-align:center"">'.$j.'</td>
//		            <td style="width:100px;text-align:center"">'.$add1.'</td>
//		            <td style="width:100px;text-align:center"">
//		                <a href=centralized.php?category=数据同步&actions='.$add2.'>数据同步</a>
//		            </td></tr>';
                $table.=' <tr class="row_even">
	                <td style="width:100px;text-align:center"">'.$j.'</td>
		            <td style="width:100px;text-align:center"">'.$address.'</td>
		            <td style="width:100px;text-align:center"">
		                <a href=centralized.php?category=数据同步&actions='.$address.'>数据同步</a>
		            </td></tr>';
      //  }
    }
}else{
    $table.='<tr><td colspan="10">没有相关数据</td></tr><table>';
}


function get_ini_file($file_name){
    $str=file_get_contents($file_name);//读取ini文件存到一个字符串中.
    $ini_list = explode("\n",$str);//按换行拆开,放到数组中.
    $ini_items = array();
    foreach($ini_list as $item){
        $one_item = explode(":",$item);
        if(isset($one_item[0])&&isset($one_item[1])) $ini_items[trim($one_item[0])] = trim($one_item[1]); //存成key=>value的形式.
    }
    return $ini_items;
}
function ireaddir($dir) {
    $handle=opendir($dir);
    $i=0;
    while($file=readdir($handle)) {
        if (($file!=".")and($file!="..")) {
            $list[$file]=$file;
            $i=$i+1;
        }
    }
    closedir($handle);
    //var_dump($list);
    return $list;
}
function get_file($file_name = '/etc/cloudschedule/enable'){
    $str1=file_get_contents($file_name);//读取ini文件存到一个字符串中.
    $ini_list = explode("\n",$str1);//按换行拆开,放到数组中.
    $ini_items = array();
    foreach($ini_list as $item){
        $one_item = explode("=",$item);
        if(isset($one_item[0])&&isset($one_item[1])) $ini_items[trim($one_item[0])] = trim($one_item[1]); //存成key=>value的形式.
    }
    return $ini_items;
}

//Get server address
   $myFile=file("/etc/cloudschedule/cloud_mysql.conf.client");

for($index=0;$index<count($myFile);$index++){
    $a.=$myFile[$index]."<br/>";
}
$conf=explode("<br/>",$a);
$server_address=explode(':',$conf[6]);
/**Data syncchronization；changzf 2012/11/24 13:35 end*/


$ini_ite = get_file('/etc/cloudschedule/enable');
if(getgpc('category','G') == '服务端设置'){
    $file_name = '/etc/cloudschedule/cloud_mysql.conf';
    $ini_items = get_ini_file($file_name);
    $default_values['client_send_rate'] = $ini_items['client_send_rate'];
    if($ini_ite['server']=='enable'){
        $active = true;
    }else{$active = false;}
    $default_values['active']  = $active;
    $form->addElement ( 'checkbox', 'active','启动服务端', $title . $comment );
    $form->addElement ( 'text', 'client_send_rate', '保证周期',array('type'=>'textarea','rows'=>'5','cols'=>'80'));

}elseif(getgpc('category','G')== '数据通道设置')
{

    $dir = "/var/www/lms/main/admin/vmmanage/";
    $ssh = ireaddir($dir);
//    var_dump($ssh);
    foreach ($ssh as $k => $v){
        $form->addElement('checkbox','ddd',"$v");

    }
    //root/.ssh/
}elseif(getgpc('category','G')== '开启数据同步服务器'){
    $form->addElement ( 'checkbox', 'open_synchronization','开启数据同步服务器&nbsp;','', array ('id' => 'open_synchronization' ) );
}elseif(getgpc('category','G')== '数据同步')
{
    if(isset($_GET['actions'])){
        $addres=  getgpc("actions");
        exec("sudo -u root /usr/bin/ssh root@$addres  /sbin/cloudrsync.sh  $server_address[1]");
    }
    $form->addElement ( 'html', $table );
}else
{
    $file_name = '/etc/cloudschedule/cloud_mysql.conf.client';
    $ini_items = get_ini_file($file_name);
    $default_values['remoteaddr'] = $ini_items['remoteaddr'];
    $default_values['client_send_rate'] = $ini_items['client_send_rate'];
    if($ini_ite['client']=='enable'){
        $active = true;
    }else{$active = false;}
    $default_values['active']  = $active;
    $form->addElement ( 'checkbox', 'active','启动客户端', $title . $comment );
    $form->addElement ( 'text', 'remoteaddr', '集中服务器地址',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'client_send_rate', '保证周期',array('type'=>'textarea','rows'=>'5','cols'=>'80'));

}
if(getgpc('category','G') == '数据同步'){
    $form->addElement ( 'html', '' );
}else{
    $form->addElement ( 'style_submit_button', null, get_lang ( '确定' ), 'class="save"' );
}
if(file_exists("/etc/cloudschedule/rsyncstart")){
    $default_values['open_synchronization']=1;
}
$form->setDefaults ( $default_values );



if ($form->validate ()) { //处理保存
    $values = $form->exportValues ();

    $ini_items['remoteaddr'] = $values['remoteaddr'];
    $ini_items['client_send_rate'] = $values['client_send_rate'];
    //var_dump($ini_items);
   $str = '';
    foreach($ini_items as $keys=>$v){
        $str .=  $keys.":".$v."\n";
    }
    $open = fopen($file_name,'w');

    fwrite($open,$str);
    fclose($open);

    /**open data syncchronization ; changzf 2012/11/24 14:00 start*/
    if(getgpc('category','G') == '开启数据同步服务器'){
        if($values["open_synchronization"]=='1'){
               exec("sudo -u root /usr/bin/touch /etc/cloudschedule/rsyncstart");
               exec("sudo -u root /usr/bin/rsync --daemon --config=/etc/cloudschedule/rsyncd.config ");


            $form->addElement ( 'html', $table);
        }else{
		exec(" sudo -u root /bin/rm -rf /etc/cloudschedule/rsyncstart");
        }
    }
    /**open data syncchronization ; changzf 2012/11/24 14:00 end*/

//var_dump($ini_ite);
    if($values['active']){

        if(getgpc('category','G') == '服务端设置'){
            $ini_ite['server'] = "enable";

        }else{
            $ini_ite['client'] = "enable";
        }

        $str = '';
        foreach($ini_ite as $keys=>$values){
            $str .=  $keys."=".$values."\n";
        }
        $open = fopen('/etc/cloudschedule/enable','w');
        fwrite($open,$str);
        fclose($open);

    }else{

        if(getgpc('category','G') == '服务端设置'){
            $ini_ite['server'] = "disable";
        }else{
            $ini_ite['client'] = "disable";
        }

        $str = '';
        foreach($ini_ite as $keys=>$values){
            $str .=  $keys."=".$values."\n";
        }
        $open = fopen('/etc/cloudschedule/enable','w');
        //var_dump($ini_item);
        fwrite($open,$str);
        fclose($open);
    }
   // exec("sudo -u root /sbin/cloudvmweb.sh");
    //api_redirect ( 'centralized.php' );
}

Display::display_header ( NULL );

if (getgpc('action','G') == "stored") {
    Display::display_normal_message ( get_lang ( 'SettingsStored' ) );
}

//$html = '<div id="demo" class="yui-navset" style="margin:10px;padding-bottom:1px;overflow:auto">';
//$html .= '<ul class="yui-nav">';
//foreach ( $resultcategories as $value ) {
//    $strClass = ($strCategory == $value ? 'class="selected"' : '');
//    $html .= '<li  ' . $strClass . '><a href="' . $_SERVER ['PHP_SELF'] . '?category=' . $value . '"><em>' . get_lang ( $value ) . '</em></a></li>';
//}
//$html .= '</ul>';
//$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;


//echo '</div></div></div>';
//Display::display_footer ( TRUE );
?>
<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt; 集中管理设置</h4>
    <ul class="manage-tab boxPublic">
        <?php
        foreach ( $resultcategories as $value ) {
            $strClass = ($strCategory == $value ? 'class="selected"' : '');
            $html .= '<li  ' . $strClass . '><a href="' . $_SERVER ['PHP_SELF'] . '?category=' . $value . '"><em>' . get_lang ( $value ) . '</em></a></li>';
        }
            echo $html;
        ?>
    </ul>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list">
            <article class="synchro ip">
                <form action="#" method="post">
                    <table cellpadding="0" cellspacing="0" class="settingstable">
                        <tbody>
                       <?php $form->display ();?>
                        </tbody>
                    </table>
                </form>
            </article> <!--数据-->

        </div>
</section>
</body>
</html>



