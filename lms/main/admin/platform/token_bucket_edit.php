<?php
/**
 * This is a new virtual template page
 * @changzf
 * on 2012/06/15
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$token_bucket = Database::get_main_table (token_bucket);
//$htmlHeadXtra [] = '<script type="text/javascript">
//	$(document).ready( function() {
//		$("tr.containerBody:eq(2)").hide();
//		//$("tr.containerBody:eq(5)").hide();
//
//
//		$("#underlyingMirror").click(function(){
//			if($("#underlyingMirror").attr("checked")){
//				$("tr.containerBody:eq(2)").hide();
//			}
//		});
//
//        $("#incrementalMirror").click(function(){
//			if($("#incrementalMirror").attr("checked")){
//			 $("tr.containerBody:eq(2)").show();
//			}
//		});
//
//	});
//	</script>';

$edit_id = htmlspecialchars($_GET ['edit_id']);
if(isset($edit_id)){
    $sql="select * from token_bucket where id = '".$edit_id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $values = $ss;
    }
}
$value_ranges = unserialize($values['ranges']);
//var_dump($values);


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

$form = new FormValidator ( 'token_bucket_edit','POST','token_bucket_edit.php?id='.$edit_id,'');
//名称
$form->addElement ( 'text', 'token_bucket_name', "名字", array ('maxlength' => 50, 'style' => "width:200px", 'class' => 'inputText' ) );
$form->addRule ( 'token_bucket_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'token_bucket_name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
//changzf on 2012/07/20
//$form->registerRule('name_only','function','check_name');
//$form->addRule('token_bucket_name','您输入的内容已存在，请重新输入', 'name_only');
//镜像
$group = array ();
$group [] = $form->createElement ( 'radio', 'types', null, '系统默认', '1' ,array('id' => 'underlyingMirror'));
//$group [] = $form->createElement ( 'radio', 'types', null, '自定义', '2',array());
$group [] = $form->createElement ( 'radio', 'types', null, '自定义', '2',array('id' => 'incrementalMirror'));
$form->addGroup ( $group, 'types', '类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'types', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

//$form->addElement ( 'text', 'types_2',"自定义", array ( 'style' => 'height:22px;' ) );

//$group = array ();
//$group [] = $form->createElement ( 'text', 'ranges','-->>', array('style'=>'width:10%','maxlength' => '5'));
//$group [] = $form->createElement ( 'text', 'ranges', '-->>',array('style'=>'width:10%','maxlength' => '5'));
//$form->addGroup ( $group, 'ranges', '令牌桶范围', '&nbsp;&nbsp;&nbsp;&nbsp;', false );


$form->addElement ( 'html', "<tr class='containerBody'><td class='formLabel'><span class='form_required'></span>令牌桶范围</td><td class='formTableTd' align='left'><input type='text' name='ranges1' id='ranges1' size='10' value='".$value_ranges[0]."'/>至<input type='text' name='ranges2' id='ranges2' size='10' value='".$value_ranges[1]."'/></input></td></tr>" );
$form->addRule ( 'ranges1', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'ranges2', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'ranges1', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'ranges2', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

$form->addElement ( 'text', 'parameter',"参数(例如:userid,lessionid,vmid)", array ('maxlength' => 30, 'style' => "width:200px", 'class' => 'inputText') );
$form->addRule ( 'parameter', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$token_buckets['types'] = $values['types'];
//$token_buckets['parameter'] = unserialize($values['parameter']);

$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
//var_dump($_SESSION);
if ($form->validate ()) {

    $token_buckets  = $form->getSubmitValues ();
    $id = htmlspecialchars($_GET ['id']);
    //types
    if($token_buckets['types']=='1'){
        $types    = $token_buckets['types'];
    }if($token_buckets['types']=='2'){
        $types    = $token_buckets['types'];
    }

    //token bucket name
    $token_bucket_name   = $token_buckets['token_bucket_name'];

    //ranges
    $ranges1 = htmlspecialchars($_POST['ranges1']);
    $ranges2 = htmlspecialchars($_POST['ranges2']);
    $rangess=array($ranges1,$ranges2);
    $ranges  = serialize($rangess);

    //parameter
    $parameter   = serialize($token_buckets['parameter']);

    $sql_data = array (
        'types' => $types,
        'token_bucket_name' => $token_bucket_name,
        'ranges' => $ranges,
        'parameter' => $parameter,
    );


    $update_sql = Database::sql_update( $token_bucket, $sql_data ,"id='$id'");

    $result = api_sql_query ( $update_sql, __FILE__, __LINE__ );

    if($result){
        $token_buckets_parameter=explode(',',$parameter);
        for($i=0;$i<=count($token_buckets_parameter)-1;$i++){
            echo $token_buckets_parameter[$i];
        }
        $token_bucket_sql="CREATE TABLE if not exists `vslab`.`$token_bucket_name` (`id` INT NOT NULL AUTO_INCREMENT ,$hub_element PRIMARY KEY ( `id` )) ENGINE = MEMORY auto_increment=1 charset=utf8;";

       // $to =  api_sql_query($token_bucket_sql,__FILE__,__LINE__);

        if($to){
            echo "操作成功！";
        }
    }
        tb_close ( 'token_bucket_list.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();