<?php
//$language_file = array ("registration", "admin" );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

$table_settings_current = Database::get_main_table ( TABLE_MAIN_SETTINGS_CURRENT );
//$resultcategories = array ('客户端设置', '服务端设置' );
$sqlsettings = "SELECT DISTINCT variable,selected_value FROM $table_settings_current WHERE variable='bgcolor'";
$resultsettings = api_sql_query ( $sqlsettings, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_array ( $resultsettings ) ) {
//$resultsettings = $resultsettings;
    $vms [] = $vm;

}


$color2 = $vms[0]['selected_value'];
$default_values['color2'] = $color2;
$color1 = $color2;

//var_dump();
//$vm= array ();
//while ( $vm = Database::fetch_row ( $res) ) {
//    $vms [] = $vm;
//}
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$strCategory = isset ( $_GET ['category'] ) ? getgpc ( 'category', 'G' ) : 'Company';




$my_category = escape ( $strCategory );
//$form = new FormValidator ( 'set_themes', 'post', 'set_themes.php?category=' . $strCategory );
//Display::setTemplateSettings ( $form, '98%' );
$form = new FormValidator ( 'upload', 'POST', "set_themes.php?color1=$color1", '', 'enctype="multipart/form-data"' );
$renderer = $form->defaultRenderer ();
//$renderer->setElementTemplate ( '<span>&nbsp;{element}</span> ' );
$form->addElement ( 'text', 'color2', '颜色',array('type'=>'textarea','rows'=>'5','cols'=>'80'));

$form->addElement ( 'file', 'user_upload', '上传logo图片', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );

//$form->addElement ( 'submit', 'submit', get_lang ( '提交' ), 'class="inputSubmit"' );








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

//function get_file($file_name = '/var/www/lms/storage/DATA/config.inc.php'){
//    $str1=file_get_contents($file_name);//读取ini文件存到一个字符串中.
//    $ini_list = explode("\n",$str1);//按换行拆开,放到数组中.
//    $ini_items = array();
//    foreach($ini_list as $item){
//        $one_item = explode("=",$item);
//        if(isset($one_item[0])&&isset($one_item[1])) $ini_items[trim($one_item[0])] = trim($one_item[1]); //存成key=>value的形式.
//    }
//    return $ini_items;
//}
//$ini_ite = get_file('/var/www/lms/portal/sp/login.php');
//
//
//    $file_name = '/var/www/lms/portal/sp/login.php';
//    $ini_items = get_ini_file($file_name);
//    $default_values['remoteaddr'] = $ini_items['remoteaddr'];
//    $default_values['client_send_rate'] = $ini_items['client_send_rate'];
////    if($ini_ite['client']=='enable'){
////        $active = true;
////    }else{$active = false;}
//    $default_values['active']  = $active;
    //$form->addElement ( 'checkbox', 'active','启动客户端', $title . $comment );

$form->addElement ( 'style_submit_button', null, get_lang ( '保存' ), 'class="save"' );
$form->setDefaults ( $default_values );
//$default_values['color1'] = $color1;
//var_dump($default_values);

if ($form->validate ()) { //处理保存
    $values = $form->exportValues ();

    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间
    $upfile = '/etc/oem/logo3.gif';
    //move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $upfile );
    $cp = "/tmp/www/lms/panel/default/assets/images/logo3.gif";
    move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $cp );
    //exec("sudo root /bin/cp -f /etc/oem/logo3.gif  /tmp/www/lms/panel/default/assets/images/logo3.gif");
copy($cp,$upfile);
    $color2 = $values['color2'];
    $sql_data = array (
        'selected_value' => $color2,

    );


    $sql = Database::sql_update ( $table_settings_current, $sql_data ,"variable='bgcolor'");

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//    $color2 = $values['color2'];
//
//    $color1=;  //原来的color
//    $color2=BGCOLOR;  //要替换的color
    $des = $color2;
    $fh = fopen("/etc/oem/color","w");
    fwrite($fh,$des);
    fclose($fh);

}



   // var_dump($color1);
   // var_dump($color2);

    $dir = "/tmp";
    $file_1="$dir/www/lms/portal/sp/index.css";
    $file_2="$dir/www/lms/themes/default/default.css";
    $file_3="$dir/www/lms/themes/js/yui/tabview/assets/skins/sam/tabview.css";
    $file_4="$dir/www/lms/main/admin/index.php";
    $file_5="$dir/www/lms/themes/js/jquery-plugins/thickbox/thickbox.css";
    $file_6="$dir/www/lms/main/admin/import_export/courses_import.php";
    $file_7="$dir/www/lms/main/inc/header.inc.php";
    $file_8="$dir/www/lms/main/admin/systeminfo.php";
//$file_9="set_themes.php";
    $file_10="$dir/www/lms/themes/js/yui/tabview/assets/skins/sam/tabview-skin.css";
    file_put_contents($file_1,str_replace($color1,$color2,file_get_contents($file_1)));
    file_put_contents($file_2,str_replace($color1,$color2,file_get_contents($file_2)));
    file_put_contents($file_3,str_replace($color1,$color2,file_get_contents($file_3)));
    file_put_contents($file_4,str_replace($color1,$color2,file_get_contents($file_4)));
    file_put_contents($file_5,str_replace($color1,$color2,file_get_contents($file_5)));
    file_put_contents($file_6,str_replace($color1,$color2,file_get_contents($file_6)));
    file_put_contents($file_7,str_replace($color1,$color2,file_get_contents($file_7)));
    file_put_contents($file_8,str_replace($color1,$color2,file_get_contents($file_8)));
    file_put_contents($file_9,str_replace($color1,$color2,file_get_contents($file_9)));
    file_put_contents($file_10,str_replace($color1,$color2,file_get_contents($file_10)));


Display::display_header ( NULL );

if ($_GET ['action'] == "stored") {
    Display::display_normal_message ( get_lang ( 'SettingsStored' ) );
}

$html = '<div id="demo" class="yui-navset" style="margin:10px;padding-bottom:1px;overflow:auto">';
$html .= '<ul class="yui-nav">';

$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

$form->display ();
echo '</div></div></div>';



Display::display_footer ( TRUE );
//var_dump();
?>


