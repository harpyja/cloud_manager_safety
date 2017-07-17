<?php
/*
 ==============================================================================
 课程分类管理-新增+编辑
 ==============================================================================
 */

$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;

//api_protect_admin_script ();
Display::display_header ( $tool_name, FALSE );
$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once ('course_category.inc.php');

$action = getgpc ( 'action', 'G' );
$category = intval(getgpc ( 'category', 'G' ));

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
//$tbl_category = Database::get_main_table(TABLE_MAIN_CATEGORY);


$id = intval(Database::escape_string ( getgpc ( 'id' ) ));
$category = Database::escape_string ( getgpc ( 'category' ) );
$sql = "SELECT name,code,parent_id,org_id,CourseDescription,CurriculumStandards,AssessmentCriteria,TeachingProgress,StudyGuide,TeachingGuide,InstructionalDesignEvaluation FROM $tbl_category WHERE id='" . $category . "'";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
list ( $categoryName, $categoryCode, $parent_id, $org_id,$CourseDescription,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
$objCrsMng = new CourseManager (); 

if (isset ( $_GET ['action'] ) && getgpc("action","G") == 'edit') {
	if ($parent_id) {
		$sql = "SELECT name,code,parent_id FROM $tbl_category WHERE id='" . intval($parent_id) . "'";
		list ( $parent_categoryName, $parent_categoryCode, $pid ) = api_sql_query_one_row (  $sql, __FILE__, __LINE__ );
	}
}

$tool_name = get_lang ( 'AdminCategories' );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$interbreadcrumb [] = array ('url' => $_SERVER ['PHP_SELF'], "name" => $tool_name );

$strTitle = ($action == 'edit' ? get_lang ( 'EditNode' ) : get_lang ( 'AddACategory' ));

$form = new FormValidator ( 'category_update' );
$form->addElement ( 'hidden', 'action', is_equal ( getgpc("action","G"), 'add' ) ? 'add_save' : 'edit_save' );
$form->addElement ( 'hidden', 'category', urlencode ( $category ) );
$form->addElement ( 'hidden', 'id', urlencode ( $category ) );

if (is_equal ( getgpc("action","G"), 'edit' )) {
	$form->addElement ( 'hidden', 'parent_category', $parent_id );
	$parent_cate_option [$parent_id] = $parent_categoryName;
}

//if (is_equal ( $_GET ['action'], 'add' )) {
//	$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, - 1 );
//	$parent_cate_option ["0"] = "";
//	foreach ( $category_tree as $item ) {
//		$parent_cate_option [$item ['id']] = str_repeat ( "&nbsp;", 4 * (intval ( $item ['level'] ) + 1) ) . $item ['name'];
//	}
////	 var_dump($parent_cate_option); 
//	$form->addElement ( 'select', 'parent_category', get_lang ( "ParentCategory" ), $parent_cate_option, array ('id' => "parent_category", 'style' => 'height:22px;' ) );
//	$defaults ['parent_category'] = $category;
//}
if (is_equal ( getgpc("action","G"), 'add' )) {
            $sql="select `id`,`name` from `course_category` where `parent_id`='0'";
	$res=api_sql_query ( $sql, __FILE__, __LINE__ ); 
      $parent_cate_option[]="";
         while ( $row = Database::fetch_array ( $res ) ) {
                $parent_cate_option [$row["id"]] = $row["name"];
            }
         
//        var_dump($parent_cate_option); 
	$form->addElement ( 'select', 'parent_category', get_lang ( "ParentCategory" ), $parent_cate_option, array ('id' => "parent_category", 'style' => 'height:22px;' ) );
	$defaults ['parent_category'] = $category;
}

$form->addElement ( 'text', 'categoryName', get_lang ( 'CategoryName' ), array ('style' => "width:300px", 'class' => 'inputText' ) );
$form->addRule ( 'categoryName', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$form->freeze ( array ("categoryName" ) );

if (is_equal ( getgpc("action","G"), 'edit' )) {
	$defaults ['categoryName'] = $categoryName;
	$defaults ['CourseDescription'] = $CourseDescription;
	$defaults ['CurriculumStandards'] = $CurriculumStandards;
	$defaults ['AssessmentCriteria'] = $AssessmentCriteria;
	$defaults ['TeachingProgress'] = $TeachingProgress;
	$defaults ['StudyGuide'] = $StudyGuide;
	$defaults ['TeachingGuide'] = $TeachingGuide;
	$defaults ['InstructionalDesignEvaluation'] = $InstructionalDesignEvaluation;

}

$form->addElement ( 'file', 'categoryCode', "分类图片", array ('style' => "width:300px", 'class' => 'inputText', 'id' => 'upload_file_local'  ) );
//从本地文件中选取
$form->addRule ( 'categoryCode', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1024 * 1024 );
$form->addRule ( 'categoryCode', get_lang ( 'UploadFileNameAre' ) . '*.jpg,*.gif,*.jpeg,*.png', 'filename', '/\\.(jpg|gif|jpeg|png)$/' );
if (is_equal ( getgpc("action","G"), 'edit' )) {
	$defaults ['categoryCode'] = $categoryCode;
}
$form->addElement ( 'textarea', 'CourseDescription', '课程介绍', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'CurriculumStandards', '课程标准', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'AssessmentCriteria', '考核标准', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'TeachingProgress', '教学进度', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'StudyGuide', '学习指导', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'TeachingGuide', '教学指导', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );
$form->addElement ( 'textarea', 'InstructionalDesignEvaluation', '教学设计评价', array ('type'=>'textarea','rows'=>'5','cols'=>'80' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
if (! is_equal ( getgpc("action","G"), 'edit' )) {
	$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( 'SaveAndAdd' ), 'class="plus"' );
}

$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '100%' );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
        $times=date ( 'HiYmsd' );
        ini_set ( 'memory_limit', '256M' );
        ini_set ( 'max_execution_time', 1800 ); //设置执行时间
        
        $base_work_dir=URL_ROOT."/www".URL_APPEDND."/storage/category_pic";
        if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
        $tmp_name=$_FILES ['categoryCode']['tmp_name'];
        $file=$_FILES ['categoryCode']['name'];
        $file = replace_dangerous_char ( trim ( $file ), 'strict' );
        $ext= substr(strrchr($file, '.'), 1); 
        $categoryCode="category".$times.".".$ext;
        
	$categoryName = trim ( $data ['categoryName'] ); 
	$parent_id = intval ( $data ['parent_category'] );
	$CourseDescription = trim($data['CourseDescription']);
        $CurriculumStandards = trim($data['CurriculumStandards']);
        $AssessmentCriteria = trim($data['AssessmentCriteria']);
        $TeachingProgress  = trim($data['TeachingProgress']);
        $StudyGuide = trim($data['StudyGuide']);
        $TeachingGuide = trim($data['TeachingGuide']);
        $InstructionalDesignEvaluation = trim($data['InstructionalDesignEvaluation']);

	if (is_equal ( $data ['action'], 'add_save' )) { //新增
                //上传图片
                if($file!==''){
                    $file=$base_work_dir."/".$categoryCode;
                    move_uploaded_file($tmp_name, $file); 
                }
                exec("chmod -R 777 ".$file);
		$ret = addNode ( $categoryCode, $categoryName, $parent_id ,$CourseDescription,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation);
		
		$log_msg = get_lang ( 'AddCourseCateogry' ) . "code=" . getgpc('categoryCode') . ",name=" . getgpc('categoryName');
		api_logging ( $log_msg, 'EXTENSIONS', 'AddCourseCateogry' );
		
		if (isset ( $data ['submit_plus'] )) {
			$redirect_url = "course_category_add_edit.php?action=add&category=" . $parent_id . "&refresh=1";
			api_redirect ( $redirect_url );
		}
	}
	
	if (is_equal ( $data ['action'], 'edit_save' )) { //编辑
            
            
                $old_category_pic=DATABASE::getval("select code from course_category where id=".getgpc('id'),__FILE__,__LINE__);
                //echo "AAA".$old_category_pic."AAA";
                if($old_category_pic!==''){
                   if($file!==''){
                        $file=$base_work_dir."/".$old_category_pic;
                        move_uploaded_file($tmp_name, $file);
                        
                        exec("chmod -R 777 ".$file);
                        if(file_exists($file)){
                         // if($old_category_pic!==''){
                            //  $exec_temp="rm -rf ".$base_work_dir."/".$old_category_pic."*";
                            //  exec($exec_temp);
                          //}
                         $ret = editNode ( $old_category_pic, $categoryName, intval($data ['id']), $parent_id,$CourseDescription ,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation);

                         $log_msg = get_lang ( 'EditCourseCateogry' ) . "id=" . intval(getgpc('id')) . ",code=" . getgpc('categoryCode') . ",name=" . getgpc('categoryName');
                         api_logging ( $log_msg, 'EXTENSIONS', 'EditCourseCateogry' );
                        }
                    } 
                }else{
                    if($file!==''){
                        $file=$base_work_dir."/".$categoryCode;
                        move_uploaded_file($tmp_name, $file);

                        exec("chmod -R 777 ".$file);
                        if(file_exists($file)){
                         $ret = editNode ( $categoryCode, $categoryName, intval($data ['id']), $parent_id,$CourseDescription ,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation);

                         $log_msg = get_lang ( 'EditCourseCateogry' ) . "id=" . intval(getgpc('id')) . ",code=" . getgpc('categoryCode') . ",name=" . getgpc('categoryName');
                         api_logging ( $log_msg, 'EXTENSIONS', 'EditCourseCateogry' );
                        }
                    } 
                }
	}
	
	$redirect_url = "course_category_iframe.php?category=" . $parent_id . "&result=success";
	tb_close ( $redirect_url );

}


if (isset ( $_GET ['refresh'] )) {
	echo '<script>self.parent.refresh_tree();</script>';
}

$form->display ();

Display::display_footer ();
