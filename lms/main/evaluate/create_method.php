<?php
$language_file = 'admin';
$cidReset = true;

include_once ("../inc/global.inc.php");
//api_protect_admin_script ();
//if (! isRoot ()) api_not_allowed ();
header("content-type:text/html;charset=utf-8");
$htmlHeadXtra [] = Display::display_kindeditor ( 'check', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'reinforcement_suggestions', 'normal' );
//$htmlHeadXtra [] = Display::display_kindeditor ( 'des[]', 'normal' );
$pro_id=intval(getgpc ( 'project_id' ));
$assess = Database::get_main_table (assess);
//$sql="select * from project where id=$pro_id";
$pro_name=Database::getval ("select name from project where id=$pro_id", __FILE__, __LINE__ );

$form = new FormValidator ( 'examtype_new','POST','create_method.php','');

//$form->addElement ( 'text', 'name', "竞赛名称", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
//$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
//$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$form->addRule('name_only','function','check_name');
//$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
$form->addElement ( 'static', '', get_lang ( '评估项目' ), get_lang ( $pro_name ) );
$form->addElement ( 'hidden', 'pro_id', $pro_id );
$form->addElement ( 'text', 'class', "类别", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'class', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addElement ( 'textarea', 'check', "检查方法", array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );
$form->addRule ( 'check', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$form->addElement ( 'text', 'num', "类别优先级", array ('maxlength' => 50, 'style' => "width:20px", 'class' => 'inputText' ) );
function check_name( $element_value,$assess) {
    $sql="select `name` from ".$assess;
    $Host_name=Database::get_into_array ( $sql );
    if (in_array($element_value,$Host_name)) {
        return false;
    } else {
        return true;
    }
}

$group = array ();
$group [] = $form->createElement ( 'radio', 'risk_level', null, get_lang ( '等级一' ), 1 );
$group [] = $form->createElement ( 'radio', 'risk_level', null, get_lang ( '等级二' ), 2 );
$group [] = $form->createElement ( 'radio', 'risk_level', null, get_lang ( '等级三' ), 3 );
$group [] = $form->createElement ( 'radio', 'risk_level', null, get_lang ( '等级四' ), 4 );
$form->addGroup ( $group, null, get_lang ( '风险等级' ), null, false );
$values['risk_level']='1';
//$form->addElement ( 'text', 'risk_level', get_lang ( '风险等级' ) );
$form->addElement ( 'textarea', 'reinforcement_suggestions', '加固建议', array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );



//显，隐
//$group = array ();
//$group [] = $form->createElement ( 'radio', 'enable', null, get_lang ( '开场' ), 1 );
//$group [] = $form->createElement ( 'radio', 'enable', null, get_lang ( '结束' ), 0 );
//$form->addGroup ( $group, null, get_lang ( '考场状态' ), null, false );
//$values['enable']='1';

$form->addElement ( 'html', "<tr class='containerBody'><td class='formLabel'>检查项</td><td class='formTableTd' align='left'>  名称<input type='text' name='name[]' id='size1' size='40' value=''/>&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' id='add_check' value='添加多个'><br>详述<textarea name='des[]' id='size2'  rows='10' cols='80'/></textarea></td></tr>" );

//$group = array ();
//
//$group [] = $form->createElement ( 'text', 'name[]', '名称',true );
//$group [] = $form->createElement ( 'text', 'des[]', '描述');
//$form->addGroup ( $group, null, get_lang ( '检查项' ), null, false );



$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
$values['num']='1';
$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
    $exam_list  = $form->getSubmitValues ();

    $pro_id           = $exam_list['pro_id'];
    $class            = $exam_list['class'];
    $check            = $exam_list['check'];
    $level            =$exam_list['risk_level'];
    $suggestions      =$exam_list['reinforcement_suggestions'];
    $num              = 1;

    $sql ="INSERT INTO `assess`(`pro_id`, `class`, `check`, `risk_level`, `reinforcement_suggestions`, `num`) VALUES ($pro_id,'$class','$check',$level,'$suggestions',$num)";
    //echo $sql;
   $resoult= api_sql_query ( $sql, __FILE__, __LINE__ );
   $ass_id=mysql_insert_id();
   //echo $ass_id;
   if($resoult==1){
       $name=$exam_list['name'];
       $des=$exam_list['des'];
       $j=1;
       $n=  count($name);
       for($i=0;$i<$n;$i++){
           if($name[$i]!=null){
                        
               $sql2="INSERT INTO `check_items`(`item_id`, `name`, `des`, `assess_id`) VALUES ($j,'$name[$i]','$des[$i]',$ass_id)";
        
               $re= api_sql_query ( $sql2, __FILE__, __LINE__ );
               $j++;
           }
       }
       
       
   }

    tb_close( 'method_list.php?project_id='.$pro_id );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
?>
<script>
$(function(){
    $('#add_check').click(function(){
      $('.add').parent().parent().before("<tr class='containerBody'><td class='formLabel'>检查项</td><td class='formTableTd' align='left'>  名称<input type='text' name='name[]' id='size1' size='40' value=''/>&nbsp;&nbsp;&nbsp;&nbsp;<br>详述<textarea name='des[]' id='size2'  rows='10' cols='80'/></textarea></td></tr>")
    })
    
})
</script>
