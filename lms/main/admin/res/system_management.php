<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-2
 * Time: 下午2:16
 * To change this template use File | Settings | File Templates.
 */
//$language_file = array ("registration", "admin" );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

$table_settings_current = Database::get_main_table ( TABLE_MAIN_SETTINGS_CURRENT );
//$resultcategories = array ('客户端设置', '服务端设置' ,'数据通道设置');
$resultcategories = array ("系统管理","DHCP服务","系统IP配置");

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$strCategory = isset ( $_GET ['category'] ) ? getgpc ( 'category', 'G' ) : 'Company';

$my_category = escape ( $strCategory );
$form = new FormValidator ( 'system_management', 'post', 'system_management.php?category=' . $strCategory );
Display::setTemplateSettings ( $form, '97%' );

//Get DHCP
$myFile=file("/etc/dhcp.conf");

for($index=0;$index<count($myFile);$index++){
    $dhcp_a.=$myFile[$index]."<br/>";
}
$conf=explode("<br/>",$dhcp_a);

$network=explode('=',$conf[0]);
$broadcast=explode('=',$conf[1]);
$netmask=explode('=',$conf[2]);
$gateway=explode('=',$conf[3]);
$dns=explode('=',$conf[4]);
$range1=explode('=',$conf[5]);
$range2=explode('=',$conf[6]);
$service=explode('=',$conf[7]);

$interface=file("/etc/network/interfaces");

for($index=0;$index<count($interface);$index++){
    $interface_a.=$interface[$index]."<br/>";
}
$interfaces=explode("<br/>",$interface_a);
//echo "<pre>";var_dump($interfaces);echo "</pre>";
$i_address = explode(' ',$interfaces[6]);
$i_netmask = explode(' ',$interfaces[7]);
$i_gateway = explode(' ',$interfaces[8]);


if($_GET['category'] == 'DHCP服务')
{

    $default_values['network'] = trim($network[1]);
    $default_values['broadcast'] = trim($broadcast[1]);
    $default_values['netmask'] = trim($netmask[1]);
    $default_values['gateway'] = trim($gateway[1]);
    $default_values['dns'] = trim($dns[1]);
    $default_values['range1'] = trim($range1[1]);
    $default_values['range2'] = trim($range2[1]);

    $service1= trim($service[1]);
    if($service1 == "stop"){
        $default_values['service']='0';
    }
    if($service1 =="start"){
        $default_values['service']='1';
    }
    $form->addElement ( 'text', 'network', '网络地址',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'broadcast', '广播地址',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'netmask', '掩码',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'gateway', '网关',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'dns', 'dns',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'range1', '地址范围开始',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'range2', '地址范围结束',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $group = array ();
    $group [] = $form->createElement ( 'radio', 'service', null, '启动', '1' ,array('id' => 'underlyingMirror'));
    $group [] = $form->createElement ( 'radio', 'service', null, '停止', '0',array('id' => 'incrementalMirror'));
    $form->addGroup ( $group, 'service', '服务状态', '&nbsp;&nbsp;&nbsp;&nbsp;', false );

}elseif($_GET['category'] == '系统IP配置'){
    $default_values['i_address'] = trim($i_address[1]);
    $default_values['i_gateway'] = trim($i_gateway[1]);
    $default_values['i_netmask'] = trim($i_netmask[1]);
    $form->addElement ( 'text', 'i_address', 'IP地址',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'i_netmask', '子网掩码',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'text', 'i_gateway', '网关',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
}else{
    $group = array ();
    $group [] = $form->createElement ( 'radio', 'Status', null, '关机', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
    $group [] = $form->createElement ( 'radio', 'Status', null, '重启', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
    $form->addGroup ( $group, 'Status', '系统关机/重启', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
}

$form->addElement ( 'style_submit_button', null, get_lang ( '确定' ), 'class="save"' );

$form->setDefaults ( $default_values );

if ($form->validate ()) { //处理保存
    $values = $form->exportValues ();
    //var_dump($values);
    if(htmlspecialchars($_GET['category']) == 'DHCP服务'){
      $network = $values['network'];
      $broadcast = $values['broadcast'];
      $netmask = $values['netmask'];
      $gateway = $values['gateway'];
      $dns = $values['dns'];
      $range1 = $values['range1'];
      $range2 = $values['range2'];
      $service= $values['service'];
      if($service=='1'){
          $services="start";
      }
      if($service=='0'){
            $services="stop";
       }
  exec("sudo -u root /sbin/cloudconfigdhcp.sh $network $broadcast $netmask $gateway $dns $range1 $range2 $services");
    }
    if(htmlspecialchars($_GET['category']) == '系统IP配置'){
      $i_address =  $values['i_address'];
      $i_netmask =  $values['i_netmask'];
      $i_gateway =  $values['i_gateway'];
     exec("sudo -u root /sbin/cloudconfigip.sh $i_address $i_netmask $i_gateway ");
    }else{
       $status=$values['Status'];
        if($status!=""){
           if($status=='1'){
//              echo "关闭系统！";
              exec("sudo -u root /sbin/cloudconfigreboot.sh shutdown");
           }
           if($status=='2'){
//               echo "重启系统！";
               exec("sudo -u root /sbin/cloudconfigreboot.sh reboot");
           }
        }

    }


}

Display::display_header ( NULL );


//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column systeminfo open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统管理</h4>
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
        <div class="manage-tab-content-list" >
            <div class="tabcontent boxPublic" style="background:#FFF;">
                <form action="#" method="post">
                    <table cellpadding="0" cellspacing="0" class="settingstable">

                        <?php $form->display ();?>

                    </table>
                </form>
            </div>
        </div>

    </div>
</section>
</body>
</html>
