<?php
/**
 * This is an add category and switching page
 * @changzf
 * on 2013/01/20
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$form = new FormValidator ( 'labs_device','POST','labs_category_add.php','');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');

$categorys =array();
    $sql = "select `name` FROM  `labs_category` where `parent_id`=0 order by `id`";
    $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vms= array ();
    while ( $vms = Database::fetch_row ( $ress) ) {
        $vmss [] = $vms;
    }
    foreach ( $vmss as $k1 => $v1){
        foreach($v1 as $k2 => $v2){
            $categorys[$v2]  = $v2;
        }
    }
    array_push($categorys,'---请选择上级分类---');
    sort($categorys);


    $form->addElement ( 'select', 'parent_id',"上级分类", $categorys, array ( 'style' => 'height:25px;' ) );


$form->addElement ( 'text', 'name','分类名称', array ('style' => "width:30%;height:25px;", 'class' => 'inputText' ) );

$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $sql="select name from `labs_category`";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
$form->addElement ( 'text', 'tree_pos','显示顺序', array ('style' => "width:30%;height:25px;", 'class' => 'inputText' ) );
$form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'8','cols'=>'50' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
    $data = $form->getSubmitValues ();
    $parent_id = trim ( $data ['parent_id'] );
    $name = trim ( $data ['name'] );
    $description = trim ( $data ['description'] );
    $tree_pos = trim($data['tree_pos']);

    if($parent_id=='0' OR $parent_id==''){
        $p_id=0;
    }else{
        $pid_sql="select `id` from `labs_category` where `name`='".$categorys[$data['parent_id']]."'";
        $p_id=DATABASE::getval($pid_sql,__FILE__,__LINE__);
    }
    $sql_data = array (
        'parent_id' => $p_id,
        'name' => $name,
        'description' => $description,
        'tree_pos' => $tree_pos
    );
    $sql = Database::sql_insert ( 'labs_category', $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    $id_sql="select `name` from `labs_category` where `id`=".$p_id;
    $cate_name=DATABASE::getval($id_sql,__FILE__,__LINE__);
    tb_close ('labs_category_iframe.php?name='.$cate_name);

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();