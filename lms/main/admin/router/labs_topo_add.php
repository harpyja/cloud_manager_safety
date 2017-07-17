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
$table_labs = Database::get_main_table (labs_labs);

$form = new FormValidator ( 'labs_ios','POST','labs_topo_add.php','');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 50, 'style' => "width:50%;height:25px", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $table_labs_ios = Database::get_main_table ( labs_ios );
    $sql="select name from $table_labs_ios";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
$labs_category_sql = "SELECT `name` FROM  `labs_category`";
$res = api_sql_query ( $labs_category_sql, __FILE__, __LINE__ );
$category= array ();
while ( $category = Database::fetch_row ( $res) ) {
    $categorys [] = $category;
}
foreach ( $categorys as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $labs_categorys[$v2]  = $v2;
    }
}
//array_push($labs_categorys,'请选择分类');
arsort($labs_categorys);

$form->addElement ( 'select', 'labs_category', "拓扑分类", $labs_categorys,array ('maxlength' => 50, 'style' => "width:30%;height:30px;" ) );
$form->addElement ( 'textarea', 'description', "描述", array ('id' => 'description','type'=>'textarea','style' => 'width:80%;height:150px','class' => 'inputText') );
$form->addElement ( 'textarea', 'info', "信息", array (  'id' => 'info','type'=>'textarea','style' => 'width:80%;height:150px','class' => 'inputText') );
$form->addElement ( 'textarea', 'netmap', "网络拓扑", array (  'style' => "width:80%;height:100px", 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs  = $form->getSubmitValues ();
    $name    = $labs['name'];
    $description    = $labs['description'];
    $info  = $labs['info'];
    $netmap    = $labs['netmap'];

    $cate_id_sql="select `id`from `labs_category` where `name`='".$labs['labs_category']."'";
    $labs_category=DATABASE::getval($cate_id_sql,__FILE__,__LINE__);

    $sql_data = array (
        'name' => $name,
        'labs_category' => $labs_category,
        'description' => $description,
        'info' => $info,
        'netmap' => $netmap
    );
    $sql = Database::sql_insert ( $table_labs, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'labs_topo.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();