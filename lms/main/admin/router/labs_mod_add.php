<?php
/**
 * This is an add routing and switching page
 * @changzf
 * on 2013/01/10
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_mod = Database::get_main_table (labs_mod);

$form = new FormValidator ( 'labs_mod','POST','labs_mod_add.php','');
$form->addElement ( 'html', '<div style="margin-top:10px;"></div>');
$form->addElement ( 'text', 'name', "模块名字", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $table_labs_mod = Database::get_main_table ( labs_mod);
    $sql="select name from $table_labs_mod";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
$device = "select `name` from `labs_ios` ";
$result = api_sql_query($device, __FILE__, __LINE__ );
while ( $rst = Database::fetch_row ( $result) ) {
    $ste [] = $rst;
}
foreach ( $ste as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $dna[$k1][]  = $v2;
    }
}
$group = array ();
foreach($dna as $k1 => $v1){
    $deviceName = $dna[$k1][0];

    $group [] = $form->createElement ( 'checkbox', $deviceName, null, $deviceName, array ('id' => $deviceName ) );

}

$form->addGroup ( $group, 'type', '匹配设备','&nbsp;');

$form->addElement ( 'checkbox', 'slot0', "slot0模块", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'html', "<tr class='containerBody'><td class='formLabel'>范围</td><td class='formTableTd' align='left'><input type='text' name='size1' id='size1' size='10' value='0'/>至<input type='text' name='size2' id='size2' size='10'/></input></td></tr>" );

$group = array ();
$group [] = $form->createElement ( 'radio', 'interface_type', null, '串口', '串口' );
$group [] = $form->createElement ( 'radio', 'interface_type', null, '以太网口', '以太网口');
$form->addGroup ( $group, 'interface_type', '网卡类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values['interface_type'] = '串口';


$form->addElement ( 'textarea', 'description', "描述", array ('id' => 'description','type'=>'textarea','style' => 'width:50%;height:80px','class' => 'inputText') );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs  = $form->getSubmitValues ();

    $type = serialize(array_keys($labs['type']));
    $mod_name    = $labs['name'];
    if($labs['slot0']==''){
        $slot0    = 0;
    }
    $description    = $labs['description'];
    $interface_type  = $labs['interface_type'];
    $size    = $labs['size1'].','.$labs['size2'];

    $sql_data = array (
        'mod_name' => $mod_name,
        'description' => $description,
        'type' => $type,
        'slot0' => $slot0,
        'size' => $size,
        'interface_type' => $interface_type
    );
    $sql = Database::sql_insert ( $table_labs_mod, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'labs_mod.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
