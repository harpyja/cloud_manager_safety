<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-5
 * Time: 下午5:03
 * To change this template use File | Settings | File Templates.
 */
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
$id=htmlspecialchars($_GET ['id']);

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_clouddesktopscan = Database::get_main_table (clouddesktopscan);

if(isset($id)){
    $sql="select * from $table_clouddesktopscan where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $values = $ss;
    }
}

$form = new FormValidator ( 'clouddesktopscan','POST','clouddesktopscan_edit.php?id='.$id,'');

$form->addElement ( 'text', 'IP_address', "IP地址", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'MAC_address', "MAC地址", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
//$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( '确定并继续添加' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
//`id`, `host_name`, `physical_address`, `IP_address`, `cloud_mirror`, `storage_space_type`, `permissions` group_name user_name
    $clouddesktopscan  = $form->getSubmitValues ();

    $IP_address   = $clouddesktopscan['IP_address'];
    $MAC_address = $clouddesktopscan['MAC_address'];


    $sql_data = array (
        'IP_address'  => $IP_address,
        'MAC_address' => $MAC_address,
    );

    $sql = Database::sql_update( $table_clouddesktopscan, $sql_data ,"id='$id'");
    api_sql_query ( $sql, __FILE__, __LINE__ );

    if (isset ( $user ['submit_plus'] )) {
        api_redirect ( 'clouddesktopscan_edit.php');
    } else {
         tb_close( 'clouddesktopscan.php' );
    }

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();