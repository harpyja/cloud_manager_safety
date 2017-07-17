<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xx
 * Date: 13-7-29
 * Time: 下午3:18
 * setting info
 */
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
//include_once ("inc/page_header.php");
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$report = Database::get_main_table (report);
$id= htmlspecialchars(getgpc('id'));
$group= htmlspecialchars(getgpc('group'));

//删除数组元素
function array_remove(&$arr, $offset){
    array_splice($arr, $offset, 1);
}

$form = new FormValidator ( 'labs_report','POST','setting.php','');
//$form = new FormValidator ( 'category_update' );
$form->addElement ( 'hidden', 'task_id',$id);

$t_sql = "select id,red_vm,blue_vm from task where id=".$id;
$t_data_list = api_sql_query_array_assoc( $t_sql, __FILE__, __LINE__ );

if($t_data_list[0]['red_vm']!==''){
    $red_vm=unserialize($t_data_list[0]['red_vm']);
}
if($t_data_list[0]['blue_vm']!==''){
    $blue_vm=unserialize($t_data_list[0]['blue_vm']);
}

$merge_result = array_merge($red_vm,$blue_vm);//合并




for($p=0;$p<count($merge_result);$p++){
    if($merge_result[$p]==''){
        array_remove($merge_result, $p);
    }
}
$type_data=array_unique($merge_result);
$type_arr=array();
foreach($type_data as $key){
    $vm_id=Database::getval("select `id` from `vmdisk` where `name`='".$key."'",__FILE__,__LINE__);
    $type_arr[$vm_id]=$key;
}
$form->addElement ( 'select', 'type', "选择模板" , $type_arr, array ('id' => "course", 'style' => 'height:22px;') );
$sql = "select user_id,username,firstname from user where group_id=".$group;
$data_list = api_sql_query_array_assoc( $sql, __FILE__, __LINE__ );
$group_user=array();
for($i=0;$i<count($data_list);$i++){
    $user_id = $data_list[$i]['user_id'];
    $username= $data_list[$i]['username'];
    $firstname= $data_list[$i]['firstname'];
    $group_user[$user_id]=$username.'('.$firstname.')';
}
$form->addElement ( 'select', 'user', "成员" , $group_user, array ('id' => "course", 'style' => 'height:22px;') );
$form->addElement ( 'text', 'ip', "IP", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );

$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {
    $setting = $form->getSubmitValues ();
//var_dump($setting);
    $template_id = $setting['type'];
    $user        = $setting['user'];
    $ip          = $setting['ip'];
    $task_id     = $setting['task_id'];


    $sql="INSERT INTO `deploy` (`template_id`,`user_id`, `task_id`, `ip`) VALUES ( '".$template_id."', '".$user."', '".$task_id."', '".$ip."')";

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'template_info.php?id='.$task_id );
}
Display::display_header ( null, FALSE );
echo '<br>';
$form->display ();
Display::display_footer ();