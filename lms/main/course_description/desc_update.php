<?php
$language_file = array ('course_description' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );

$description_id = isset ( $_REQUEST ['description_id'] ) ? intval ( getgpc ( 'description_id' ) ) : 0;
$strActionType =intval(getgpc ( 'desc_id', 'G' ));

$sql = "SELECT description, description1, description2,description3,description4,description5,description6,description7,description8,description9,description10,description11,description12,description13 FROM " . $tbl_course . " WHERE code=" . Database::escape ( api_get_course_code () );
list ( $description, $description1, $description2,$description3,$description4,$description5,$description6,$description7,$description8,$description9,$description10,$description11,$description12,$description13 ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
if ($description_id == 0)
	$description_content = $description;
	$description_content1 = $description1;
	$description_content2 = $description2;
//	$description_content3 = $description3;
	$description_content4 = $description4;
	$description_content5 = $description5;
	$description_content6 = $description6;
	$description_content7 = $description7;
	$description_content8 = $description8;
	$description_content9 = $description9;
	$description_content10 = $description10;
	$description_content11 = $description11;
	$description_content12 = $description12;
	//echo $description_content;
	/**
elseif ($description_id == 1)
	$description_content = $description1;
elseif ($description_id == 2)
	$description_content = $description2;
elseif ($description_id == 3)
	$description_content = $description3;*/

	//处理相关逻辑:删除,编辑
if ($allowed_to_edit && isset ( $description_id )) {

	$fck_attribute ['Width'] = '64%';
	$fck_attribute ['Height'] = '200';
	$fck_attribute ["ToolbarStartExpanded"] = TRUE;
	$fck_attribute ['ToolbarSet'] = 'Middle';
	//var_dump($fck_attribute);
 ?>
	



<?php

 


	$form = new FormValidator ( 'course_description1', 'POST', 'desc_update.php?id=' . $description_id, '' );
	$renderer = $form->defaultRenderer ();
	 
//	$renderer->setElementTemplate ( '<span>{element}</span><br/> ' ); 
	// var_dump($renderer);
if ($description_id == 0){	 
		$group = array ();
		$group [] = $form->createElement ( 'radio', 'description', null,'初级', '0' );
		$group [] = $form->createElement ( 'radio', 'description', null, '中级', '1' );
		$group [] = $form->createElement ( 'radio', 'description', null, '高级', '2');
		$form->addGroup ( $group, 'description', '实验等级', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
    $form->addElement ( 'textarea', 'description1', '实验目的',array('id' => 'description1','type'=>'textarea','style' => 'width:70%;height:200px'));
		$form->addElement ('textarea', 'description2', '预备知识',array('id' => 'description2','type'=>'textarea','style' => 'width:70%;height:200px') );
 //   $form->addElement ( 'textarea', 'description3', '需求分析',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
 //   $form->addElement ( 'textarea', 'newContent', get_lang ( 'Content' ), array ('id' => 'descript', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:70%;height:200px' ) );
    $form->addElement ( 'textarea', 'description4', '实验内容',array('id' => 'description4','type'=>'textarea','style' => 'width:70%;height:200px'));
    $form->addElement ( 'textarea', 'description5', '实验原理',array('id' => 'description5','type'=>'textarea','style' => 'width:70%;height:200px'));
    $form->addElement ( 'textarea', 'description6', '实验环境描述',array('id' => 'description6','type'=>'textarea','style' => 'width:70%;height:200px'));
$form->addElement ( 'hidden', 'description7', '教学大纲',array('type'=>'textarea'));
     $form->addElement ( 'hidden', 'description13', '选择网络拓扑',array('type'=>'textarea','style' => 'width:70%;height:200px'));
    $form->addElement ( 'hidden','description8', '实验步骤');
}

			
	if ($description_id == 8){
		$form->addElement ( 'hidden', 'description' );
    $form->addElement ( 'hidden', 'description1', '实验目的',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
		$form->addElement ( 'hidden', 'description2', '预备知识' );
  //  $form->addElement ( 'hidden', 'description3', '需求分析',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'hidden', 'description4', '实验内容',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'hidden', 'description5', '实验原理',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    $form->addElement ( 'hidden', 'description6', '实验环境描述',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
$form->addElement ( 'hidden', 'description7', '教学大纲',array('type'=>'textarea','rows'=>'25','cols'=>'50'));
   // $form->addElement ( 'textarea', 'description13', '选择网络拓扑',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
    
		
    $networkmap = Database::get_main_table ( networkmap);
                $sql = "select name FROM  $networkmap ";
                $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                $vm= array ();
                while ( $vm = Database::fetch_row ( $res) ) {
                        $vms [] = $vm;
                }
                foreach ( $vms as $k1 => $v1){
                   foreach($v1 as $k2 => $v2){
                      $arr[$v2]  = $v2;
                     }
                }
                $op="";
               foreach ( $arr as $v1){ 
                    $op.="<option value='$v1'>$v1</option>";
                     }
              $arr_topo="<span style= 'width:100px;padding-left:120px;'>网络拓扑类型 </span><select id='description13' name='description13' onChange='getarea()' > ".$op." </select>";
     
     $form->addElement ( 'text', 'keys', "请输入网络拓扑关键字：", array ( 'id' => 'keys' ,onkeyup=>'descCheck()' ) );
//    $form->addElement ( 'select', 'description13', '选择网络拓扑', $arr, array ( 'style' => 'height:22px;width:200px;','type'=>'select' ) );
    $form->addElement ( 'html', '<div id="Flags">'.$arr_topo.'</div>');
    $form->addElement ( 'textarea' , 'description8', '实验步骤' , array('id' => 'description8','type'=>'textarea','style' => 'width:80%;height:400px'));
 
}	
    if ($description_id == 7){
        $form->addElement ( 'hidden', 'description' );
        $form->addElement ( 'hidden', 'description1', '实验目的',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden', 'description2', '预备知识' );
        //$form->addElement ( 'hidden', 'description3', '需求分析',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden', 'description4', '实验内容',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden', 'description5', '实验原理',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden', 'description6', '实验环境描述',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden', 'description13', '选择网络拓扑',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
        $form->addElement ( 'hidden','description8', '实验步骤' );

        $form->addElement ( 'textarea' , 'description7', '教学大纲' , array('id' => 'description7','type'=>'textarea','style' => 'width:80%;height:400px'));

    }
	$group = array ();
	$group [] = $form->createElement ( 'submit', null, get_lang ( 'Ok' ), 'class="inputSubmit"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
	
	$default ['contentDescription'] = $description_content;
	$default ['description'] = $description;
	$default ['description1'] = $description1;
	$default ['description2'] = $description2;
//	$default ['description3'] = $description3;
	$default ['description4'] = $description4;
	$default ['description5'] = $description5;
	$default ['description6'] = $description6;
	$default ['description7'] = $description7;
	$default ['description8'] = $description8;
	$default ['description13'] = $description13;


	$form->setDefaults ( $default );
	
	if ($form->validate ()) {
		$description = $form->getSubmitValues ();		
		$content = $description ['description'];
		$content1 = $description ['description1'];
		$content2 = $description ['description2'];
//		$content3 = $description ['description3'];
		$content4 = $description ['description4'];
		$content5 = $description ['description5'];
		$content6 = $description ['description6'];
		$content7 = $description ['description7'];
		$content8 = $description ['description8'];
		$content13 = $description ['description13'];
 		$content1=str_replace("\\\\","\\",$content1);
 		$content2=str_replace("\\\\","\\",$content2);
 		$content3=str_replace("\\\\","\\",$content3);
 		$content4=str_replace("\\\\","\\",$content4);
 		$content5=str_replace("\\\\","\\",$content5);
 		$content6=str_replace("\\\\","\\",$content6);
 		$content7=str_replace("\\\\","\\",$content7);
 		$content8=str_replace("\\\\","\\",$content8);
 		$content13=str_replace("\\\\","\\",$content13);

		if (empty ( $description_id )) $description_id = intval ( getgpc ( "description_id" ) );
		if ($description_id == 0){
			$sql_data = array (
			'description' => $content,
			'description1' => $content1,
			'description2' => $content2,
//			'description3' => $content3,
			'description4' => $content4,
			'description5' => $content5,
			'description6' => $content6,
			'description7' => $content7,
			'description8' => $content8,
			'description13' => $content13,

			);
}
	
		if ($description_id == 8 ){
			$sql_data = array (
			'description' => $content,
			'description1' => $content1,
			'description2' => $content2,
//			'description3' => $content3,
			'description4' => $content4,
			'description5' => $content5,
			'description6' => $content6,
			'description7' => $content7,
			'description8' => $content8,
			'description13' => $content13,

			);
		}

 if ($description_id == 7 ){
            $sql_data = array (
                'description' => $content,
                'description1' => $content1,
                'description2' => $content2,
                //'description3' => $content3,
                'description4' => $content4,
                'description5' => $content5,
                'description6' => $content6,
                'description7' => $content7,
                'description8' => $content8,
                'description13' => $content13,

            );

        }
		/**	
		elseif ($description_id == 2)
			$sql_data = array ('description2' => $content );
		elseif ($description_id == 3)
			$sql_data = array ('description3' => $content );*/
	
		$sql = Database::sql_update ( $tbl_course, $sql_data, "code=" . Database::escape ( api_get_course_code () ) );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		api_item_property_update ( $_course, TOOL_COURSE_DESCRIPTION, $description_id, "CourseDescriptionUpdated", api_get_user_id () );
//		Display::display_confirmation_message ( get_lang ( 'CourseDescriptionUpdated' ) );
		
		tb_close ();
	}
}
$htmlHeadXtra [] = Display::display_kindeditor ( 'description2', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description4', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description5', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description6', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description7', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description8', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'description1', 'normal' );
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
Display::display_header ( null, FALSE );

$form->display ();


Display::display_footer ();


?>


<script type="text/javascript" src="../../themes/js/jquery.js"></script>
 <script type="text/javascript">
  function descCheck(){         
           var values=$("#keys").val();  
          $.ajax({
              type: "post",
              url: "desc_check.php",
              data:"topo_type="+values,  
              cache:false,
              beforeSend: function(XMLHttpRequest){
              },
              success: function(data){
                        $("#Flags").empty(); 
                        $("#Flags").append(data);   //给下拉框添加option 
                 },
              complete: function(XMLHttpRequest, textStatus){
              },
              error: function(){

              }
             });
  } 
</script>
