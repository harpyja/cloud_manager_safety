<?php
$language_file = array ('exercice', 'admin' );
include_once ('../inc/global.inc.php');

api_protect_admin_script();
if (! isRoot ()) api_not_allowed ();

$pd=$_POST['pid'];
if(isset($pd)){
    $sql="UPDATE `project` SET `release`=1 WHERE id=$pd";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    echo $result;
    
    exit;
}

 
 

include ('cls.project.php');
$objProject = new project ();

$id = isset ( $_REQUEST ["id"] ) ?  intval(getgpc ( "id" )) : "0";
 
$htmlHeadXtra [] = import_assets ( "commons.js" );

//$interbreadcrumb[] = array("url"=> 'index.php', "name" => get_lang('Exam'));
$tool_name = get_lang ( 'project' );
 
$form = new FormValidator ( 'exam_ae' );

//$form->addElement ( 'header', 'header', $tool_name );
 
if (is_equal ( $_GET ['action'], 'add_project' )) {
	$form->addElement ( 'hidden', 'action', 'add_project_save' );
}  elseif (is_equal ( $_GET ["action"], "edit_project" )) {
    
	$item_info = $objProject->get_info ( $id );
        //var_dump($item_info);
	$form->addElement ( 'hidden', 'id', $id );
	$form->addElement ( 'hidden', 'action', 'edit_project_save' );
         
} 
 
$form->addElement ( 'text', 'name', get_lang ( '名称' ), array ('maxlength' => 40, 'style' => "width:70%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//
$form->addElement('hidden','upfile','图片');
//上传
$form->addElement('file','upfiles','上传图片');
$form->addRule ( 'upfile', get_lang ( 'UploadFileNameAre' ) . ' *.gif,*.jpg,*.png,*.jpeg', 'filename', '/\\.(gif|jpg|png|jpeg)$/' );

//描述
$form->addElement ( 'text', 'des', get_lang ( '描述' ), array ('maxlength' => 40, 'style' => "width:70%", 'class' => 'inputText' ) );

//显示顺序
//$form->add_textfield ( 'desc', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
//$form->addRule ( 'desc', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
//$form->addRule ( 'desc', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
//if (is_equal ( $_GET ['action'], 'add_project' )) {
//	$pool_pos = $objProject->get_next_display_order ( );
//	$item_info ["desc"] = $pool_pos;
//} 

//其它说明
//	$form->addElement('textarea', 'description', get_lang('Remark'),array('cols'=>50,'rows'=>5,'class'=>'inputText'));

//提交按钮
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="save"' );
//if (is_equal ( $_GET ['action'], 'add_project' )) {
//	$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( 'SaveAndAdd' ), 'class="plus"' );
//}
//$group[] = $form->createElement('style_button', 'button',get_lang('Back'), array('type'=>'button','value'=>get_lang("Back"),'class'=>"save",'onclick'=>'location.href=\'pool_list.php\';'));
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $item_info );


Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {
	$data = $form->getSubmitValues ();
        
//        if($_FILES['upfiles']['name']!=""){
//         // echo "hello world";
//        $files=$_FILES['upfiles'];
//        $dir=URL_ROOT.'/www'.URL_APPEDND."/storage/evaluate/";
//        $filename=explode(".",$files['name']);
//        $upfile=$dir.rand().".".$filename[1];
////        move_uploaded_file($files['tmp_name'],$upfile);
//        }else{
//            $upfile=$data['upfile'];
//        }
        $pro_name = ($data ['name']);
	$pro_desc = ($data ['des']);
        if (is_equal ( $_REQUEST ['action'], 'add_project_save' )) {
     
                $files=$_FILES['upfiles'];
              //  $dir=URL_ROOT.'/www'.URL_APPEDND."/storage/evaluate/";
                $dir='../../storage/evaluate/';
                $filename=explode(".",$files['name']);
                $upfile=$dir.rand().".".$filename[1];
                move_uploaded_file($files['tmp_name'],$upfile);
       
//		$sql_data = array ("name" => $pro_name, "desc" => $pro_desc, );
//                print_r($sql_data);
//		$objProject->add ( $sql_data );
//		if (isset ( $data ['submit_plus'] )) {
//			$redirect_url = "pool_update.php?action=add_pool&pid=" . $parent_id . "&refresh=1";
//			api_redirect ( $redirect_url );
//		}
                $sql ="INSERT INTO project (`name` ,`des`,`upfile`) VALUES ('".$pro_name."','".$pro_desc."','".$upfile."')";
                api_sql_query ( $sql, __FILE__, __LINE__ );
		$redirect_url = 'project.php';
		 tb_close($redirect_url);
	}
	
	if (is_equal ( $_REQUEST ['action'], 'edit_project_save' )) {
//		$sql_data = array ( "pool_name" => $pool_name, "display_order" => $pool_pos );
//		$objQuestionPool->edit ( $sql_data, "id='" . escape ( $data ["id"] ) . "'" );
//		
            if($_FILES['upfiles']['name']!=""){
                 //  echo "hello world";
               $files=$_FILES['upfiles'];
               $rmdir= $data['upfile'];
               unlink($rmdir);
             //  $dir=URL_ROOT.'/www'.URL_APPEDND."/storage/evaluate/";
               $dir='../../storage/evaluate/';
               $filename=explode(".",$files['name']);
               $upfile=$dir.rand().".".$filename[1];
               move_uploaded_file($files['tmp_name'],$upfile);
               }else{
                   $upfile=$data['upfile'];
               }
                $sql ="UPDATE   `project` SET  `name` =  '".$pro_name."',`des` =  '".$pro_desc."',`upfile`='".$upfile."' WHERE  `id` =".$id;
                  api_sql_query ( $sql, __FILE__, __LINE__ );
                $redirect_url = 'project.php?refresh=1';
		 tb_close($redirect_url);
	}

}

Display::display_header($tool_name,FALSE);

if (! empty ( $_GET ['message'] )) {
	$message = urldecode ( getgpc('message') );
	Display::display_normal_message ( urldecode ( stripslashes ( $message ) ) );
}

if (isset ( $_GET['refresh'] )) {
	echo '<script>self.parent.refresh_tree();</script>';
}

$form->display ();
