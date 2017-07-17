<?php
/**
 * This is a new virtual template page
 * @changzf
 * on 2012/06/15
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

 

$edit_id = intval(getgpc('edit_id','G'));
  
function check_name($element_name, $element_value) {
    $tbl_vmdisk = Database::get_main_table ( token_bucket );
    $sql="select token_bucket_name from $tbl_vmdisk";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}

$form = new FormValidator ( 'token_bucket_add','POST','token_bucket_add.php');
//名称
$form->addElement ( 'text', 'token_bucket_name', "名字", array ('maxlength' => 50, 'style' => "width:200px", 'class' => 'inputText' ) );
$form->addRule ( 'token_bucket_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'token_bucket_name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
 
$group = array ();
$group [] = $form->createElement ( 'radio', 'types', null, '系统默认', '1' ,array('id' => 'underlyingMirror'));
//$group [] = $form->createElement ( 'radio', 'types', null, '自定义', '2',array());
$group [] = $form->createElement ( 'radio', 'types', null, '自定义', '2',array('id' => 'incrementalMirror'));
$form->addGroup ( $group, 'types', '类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'types', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
 
$form->addElement ( 'html', "<tr class='containerBody'><td class='formLabel'><span class='form_required'></span>令牌桶范围</td><td class='formTableTd' align='left'><input type='text' name='ranges1' id='ranges1' size='10' value='".$value_ranges[0]."'/>至<input type='text' name='ranges2' id='ranges2' size='10' value='".$value_ranges[1]."'/></input></td></tr>" );
$form->addRule ( 'ranges1', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'ranges2', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'ranges1', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'ranges2', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

//$form->addElement ( 'text', 'parameter',"参数(例如:userid,lessionid,vmid)", array ('maxlength' => 30, 'style' => "width:200px", 'class' => 'inputText') );
//$form->addRule ( 'parameter', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//
//$group1 = array ();
//$group1 [] = $form->createElement ( 'radio', 'status', null, '开启', '1' ,array('id' => 'on')); 
//$group1 [] = $form->createElement ( 'radio', 'status', null, '关闭', '2',array('id' => 'off'));
//$form->addGroup ( $group1, 'status', '状态', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
//$form->addElement ( 'text', 'values',"内容", array ('maxlength' => 30, 'style' => "width:200px", 'class' => 'inputText') );
 
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
  
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
//var_dump($_SESSION);

function add($token_buckets){
//    var_dump($token_buckets);
     //types
    if($token_buckets['types']=='1'){
        $types    = $token_buckets['types'];
    }if($token_buckets['types']=='2'){
        $types    = $token_buckets['types'];
    } 
    //token bucket name
    $token_bucket_name   = $token_buckets['token_bucket_name']; 
    //ranges
    $ranges1 = getgpc('ranges1','P');
    $ranges2 = getgpc('ranges2','P');
    $rangess=array($ranges1,$ranges2);
    $ranges  = serialize($rangess); 
    //parameter
   // $parameter   = serialize($token_buckets['parameter']); 
//    $status    = $token_buckets['status'];
//    $values    = $token_buckets['values'];
    
      $sql="insert  into `token_bucket` (`types`,`token_bucket_name`,`ranges`)  values('".$types."','".$token_bucket_name."','".$ranges."')";
      $result = api_sql_query ( $sql, __FILE__, __LINE__ );
      
      $create_tb="CREATE TABLE if not exists `".DB_NAME."`.`$token_bucket_name` (`Pid` INT NOT NULL AUTO_INCREMENT ,`status` smallint(6),`values` varchar(256) , PRIMARY KEY ( `Pid` )) ENGINE = MyISAM auto_increment=1 charset=utf8;";
       api_sql_query ( $create_tb, __FILE__, __LINE__ );
   
        for($pid=$ranges1;$pid<=$ranges2;$pid++){
                  $sql1="insert into `$token_bucket_name` (`Pid`,`status`,`values`) values(".$pid.",'0','0');"; 
                    $result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
          }   
      return $result1;
}

if ($form->validate ()) { 
    
    $token_buckets  = $form->getSubmitValues (); 
     $re=add($token_buckets);
        if($re){
             tb_close ( 'token_bucket_list.php' );
       }else{
           echo "false!!!";
       }
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();