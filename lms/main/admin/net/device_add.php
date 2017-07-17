<?php
/*
 ==============================================================================
 device edit
 ==============================================================================
 */
include_once ('../../inc/global.inc.php');
require_once (api_get_path(INCLUDE_PATH ).'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();//User rights  @chang_z_f 2013-07-27

header("content-type:text/html;charset=utf-8");

$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
Display::display_header(null,FALSE);

$form = new FormValidator ( 'course_step', 'POST', 'device_add.php', '' );
$renderer = $form->defaultRenderer ();

//名称
$form->addElement ( 'text', 'device_name', '类型名称',array('id' => 'device_name','type'=>'text','maxlength' => 20));

$form->addRule ( 'device_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$image [] = $form->createElement ( 'file', 'image_url', '图片',array('id' => 'image_url'));
$form -> addGroup ($image,'image','图片','',false);
$form->addRule ( 'image', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', '确认', 'class="add"' );
//$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', '确认并继续添加','class="plus"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );


//$default['course_id']=$_SESSION['lesson_id'];
//$lessonid = $default['course_id'];
//$form->setDefaults ( $default );

if ($form->validate ()) {
    $device = $form->getSubmitValues ();
    $device_name = $device['device_name'];
    $device_image = $device['image_url'];

    $url_1 = "/tmp/www/lms/storage/images";
    $url_2 = "/lms/storage/images";
    if(!file_exists($url_1)){
        exec("mkdir $url_1");
        exec("chmod 777 $url_1");
    }

    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间
    $image_url = $_FILES["image_url"]["tmp_name"];
    $image_name = $_FILES["image_url"]["name"];
    move_uploaded_file($image_url,"$url_1/$device_name.png");


    $sql_data = array (
        'device_name'=>$device_name,
        'image_url' => "$url_2/$device_name.png",

    );

    $sql = Database::sql_insert ( "device_type", $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );


    if(isset($step['submit_plus'])){
        $redirect_url = "step_add.php";
        echo "<script>document.location='{$redirect_url}'</script>";

    }
    $redirect_url = "device_type.php";
    tb_close ( $redirect_url );


}


$form->display ();

Display::display_footer ();
?>
