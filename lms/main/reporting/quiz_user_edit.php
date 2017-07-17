<?php
/**
 * User: chang
 * Date: 13-4-14
 * Time: 下午5:27
 * To change this template use File | Settings | File Templates.
 */
$cidReset = true;
include_once ("../inc/global.inc.php");
//api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'comment', 'normal' );

//require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$uid=htmlspecialchars($_GET ['uid']);
$title=htmlspecialchars($_GET ['title']);


if($uid!=='' && $title!==''){
    $exam_id=Database::getval('select id from exam_main where title="'.$title.'"',__FILE__,__LINE__);
    $user_id=Database::getval('select user_id from user where username="'.$uid.'"',__FILE__,__LINE__);
    $sql="select * from  `vslab`.`exam_rel_user` where exam_id = '".$exam_id."' and user_id='".$user_id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}

$form = new FormValidator ( 'report_edit','POST','quiz_user_edit.php?exam_id='.$exam_id.'&user_id='.$user_id,'');
$form->addElement ( 'text', 'score', '得分', array ('type'=>'text'  ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'is_pass', null, '未通过', '0' );
$group [] = $form->createElement ( 'radio', 'is_pass', null, '通过', '1');
$form->addGroup ( $group, 'return', '结果', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'is_pass', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$id_html='<input type="hidden" name="ids" value="'.$default['id'].'"><input type="hidden" name="exam_id" value="'.$exam_id.'"><input type="hidden" name="user_id" value="'.$user_id.'">';
$form->addElement ( 'html', $id_html );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $report       = $form->getSubmitValues ();
    $score        = $report['score'];
    $is_pass      = $report['is_pass'];
    $ids      = $report['ids'];
    $examId      = $report['exam_id'];
    $userId      = $report['user_id'];

    $sql="UPDATE  `vslab`.`exam_rel_user` SET  `score` =  '".$score."',is_pass=".$is_pass." WHERE  `exam_rel_user`.`id` =".$ids;
    if($examId!==''){
       $sql.=" and `exam_id`='".$examId. "'";
    }if($userId!==''){
       $sql.=" and `user_id`='".$userId. "'";
    }
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    if($result){
        if($examId!=='' && $userId!==''){
        $sql="UPDATE `exam_track` SET `score`='".$score."' WHERE `exe_exo_id`=".$examId." and `exe_user_id`=".$userId;
        api_sql_query ( $sql, __FILE__, __LINE__ );
        }
    }
   tb_close ( 'quiz_user.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();