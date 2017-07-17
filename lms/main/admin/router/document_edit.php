<?php
/**
 * Created by JetBrains PhpStorm.
 * User:  Z-dan
 * Date: 13-5-22
 * Time: 下午4:56
 * To change this template use File | Settings | File Templates.
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_document = Database::get_main_table (labs_document);


$_SESSION['id'] =  intval($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){  //获取默认值
    $sql="select * from $table_labs_document where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
//print_r($default);
$form = new FormValidator ( 'labs_experimental_anual','POST','document_edit.php?id='.$id,'');    //???????????
$form->addElement ( 'html', '&nbsp;');
$form->addElement ( 'text', 'document_name', "名字", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'document_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$type=array('0'=>'实验指导书','1'=>'初始化配置');  //（0-->实验指导书 , 1-->初始化配置 ）--->存到数据库表中的是int型的0或1
$form->addElement ( 'select', 'type',"类型", $type, array ('id' => "type", 'style' => 'height:22px;width:20%' ) );

$form->addElement ( 'text', 'document_size',"大小",  array ('maxlength' => 8,'id' => "type", 'style' => 'width:30%','class' => 'inputText') );

//设一个数组形式的变量，存放拓扑名称(key值(拓扑id)存到数据库，其对应的值(name)为显示内容)-----难啊！！！
$sql='select  id,name from labs_labs';
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
     $toponame [] = $vm;   //二维数组,,,,,需要处理一下啊！！！
}
//print_r($toponame); //Array ( [0] => Array ( [0] => 1 [1] => base ) [1] => Array ( [0] => 2 [1] => rip ) [2] => Array ( [0] => 3 [1] => ospf ) [3] => Array ( [0] => 4 [1] => eigrp ) [4] => Array ( [0] => 5 [1] => is-is ) [5] => Array ( [0] => 6 [1] => bgp ) [6] => Array ( [0] => 7 [1] => ddd ) [7] => Array ( [0] => 22 [1] => 路由器的登陆 ) [8] => Array ( [0] => 23 [1] => tp ) )
$toponame1=array ();
foreach($toponame as $val){  // $val的下标0-->id,下标1-->name
    $toponame1[$val[0]]=$val[1];
}
$form->addElement ( 'select', 'labs_id',"实训拓扑", $toponame1, array ('id' => "type", 'style' => 'height:22px;width:20%' ) );  //拓扑id--->拓扑名称


$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
//使文本框不能编辑，值为默认值
$form->freeze ( array ("document_name" ) );
$labs['document_name'] = $default['document_name'];
$form->freeze ( array ("document_size" ) );
$labs['document_size'] = $default['document_size'];

$form->setDefaults ($default);   //设置默认值
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );  //设置模板边框
if ($form->validate ()) {

    $labs  = $form->getSubmitValues ();
// print_r($labs);
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $document_name    = $labs['document_name'];
    $type    = $labs['type'];    //值为0或1
    $document_size=$labs['document_size'];
    $labs_id=$labs['labs_id'];
// print_r($type);

    $sql="UPDATE `vslab`.`labs_document` SET document_name= '".$document_name."',type= '".$type."',document_size= '".$document_size."',labs_id= '".$labs_id."' WHERE id='".$id."'";
//echo $sql;
   $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ('labs_experimental_anual.php');
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();


