<?php
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );

api_sql_query ( "delete from  course where code ='' or  title=''", __FILE__, __LINE__ );
api_sql_query ( "DELETE FROM course_category  WHERE course_category.name=''", __FILE__, __LINE__ );
api_sql_query ( "DELETE FROM networkmap  WHERE  name=''", __FILE__, __LINE__ );
api_sql_query ( "DELETE FROM vmdisk  WHERE  name=''", __FILE__, __LINE__ );
api_sql_query ( "DELETE FROM crs_courseware  WHERE  cc='' or  title=''", __FILE__, __LINE__ );

$objCrsMng = new CourseManager ();

function get_sqlwhere() {
	global $restrict_org_id, $objCrsMng;
	$sql_where = "";
	if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
		$keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
		$sql_where .= " AND (title LIKE '%" . trim ( $keyword ) . "%' OR code LIKE '%" . trim ( $keyword ) . "%')";
	}
	
	if (is_not_blank ( $_GET ['category_id'] )) {
		$sql_where .= " AND category_code=" . Database::escape (intval(getgpc ( 'category_id', 'G' )) );
	}
	
	$sql_where = trim ( $sql_where );
	if ($sql_where)
		return substr ( ltrim ( $sql_where ), 3 );
	else return "";
}

function get_number_of_courses() {
	$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table AS t1 ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	//echo $sql;exit;
	return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_course_data($from, $number_of_items, $column, $direction) {
	$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$users_table = Database::get_main_table ( TABLE_MAIN_USER );
	$course_users_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
	global $tbl_courseware;
  
    //by changzf at 54 line on 2012/06/08
	//$sql = "SELECT code AS col0,title AS col1,code AS col2, tutor_name as col3,code AS col4,category_code AS col5,code as col6,code as col7,code as col8,code as col9,code as col10,code as col11,code as col12,code as col13,code as col14,code as col15,code as col16,code as col17 FROM $course_table AS t1";
        $sql = "SELECT code AS col0,title AS col1,code AS col2, nodeId as col3,tutor_name as col4,code AS col5,category_code AS col6,code as col7,code as col8,code as col9,code as col10,code as col11,code as col12,code as col13,code as col14,code as col15,code as col16,code as col17 FROM $course_table AS t1";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	
	$sql .= " ORDER BY title asc,col$column $direction ";

	$sql .= " LIMIT $from,$number_of_items";

	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$courses = array ();
	$objCourse = new CourseManager ();
	while ( $course = Database::fetch_array ( $res, 'NUM' ) ) {
		$objCourse->category_path = ''; 
		$course [1] = '<span style="float:left"> '.Display::return_icon ( "course.gif" ) . '&nbsp;' . api_trunc_str2 ( $course [1],45 ).'</span>';
		//$category = $objCourse->get_category_path ( $course [5], TRUE );

		//讲师V2.1
		//$tutor_info=CourseManager::get_course_tutor($course[0]);
		//$course[3]=$tutor_info['firstname']."(".$tutor_info['username'].")";

		//liyu 得到课程管理员
		$admin_info = CourseManager::get_course_admin ( $course [0] );
                $manager=$admin_info ['firstname']  ;
                if($admin_info ['username']!=''){
                  $manager.= "(". $admin_info ['username'].")";
                }
		$course [5] = $manager ;
		//注册人数
		$sql3 = "SELECT COUNT(user_id) FROM " . $course_users_table . " WHERE course_code='" . $course [0] . "'";
		$course [6] = Database::getval ( $sql3, __FILE__, __LINE__ );
		
		$sql3 = "SELECT COUNT(id) FROM " . $tbl_courseware . " WHERE cc='" . $course [0] . "'";
		$course [7] = Database::getval ( $sql3, __FILE__, __LINE__ );
		
		$courses [] = $course;
	}
	return $courses;
}

//by changzf at 90-146 line on 2012/06/08
function preview_filter($code) {
	$html = "";
	$html .=  icon_href ( 'course_home.gif', 'CourseHomePage', api_get_path ( WEB_PATH ) . PORTAL_LAYOUT . 'course_home.php?cidReq=' . $code.'&action=introduction', '_blank' );
	return $html;
}

function  describe_filter($code) {
    $html =  link_button ( 'synthese_view.gif', 'Info', 'course_information.php?code=' . $code, '70%', '80%', FALSE );
    return $html;
}
//function  Edit_filter($code) {
//    $html = "";
//    $html .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'course_edit.php?course_code=' . $code, '70%', '80%', FALSE );
//    return $html;
//}
function  Delete_filter($code) {

    $desc = 'select description12 from course where code='.$code;
    $description12  = Database::getval ( $desc, __FILE__, __LINE__ );
    $lessonedit="/etc/lessonedit";
    $lessonedit=file_get_contents($lessonedit);
    $lessonedit+=0;
    if($lessonedit == '1'){
        $html = "";
        $html .="&nbsp;" . confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'course_list.php?delete_course=' . $code );
        return $html;

    }else{
        if($description12 =='1'){
            $html = "";
            $html .="&nbsp;" . confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'course_list.php?delete_course=' . $code );
            return $html;
        }else{
            $html = "";
            $html .="默认";
            return $html;
        }
    }
//    $html = "";
//    $html .="&nbsp;&nbsp;&nbsp;" . confirm_href ( 'delete.gif', 'Are You Sure To Delete?', 'Delete', 'course_list.php?delete_course=' . $code );
//    return $html;
}
function  Reporting_filter($code) {  //进度
    $html = "";
    $html .=  link_button ( 'statistics.gif', 'Tracking', '../../reporting/stat_course_user.php?cidReq=' . $code, '70%', '80%', FALSE );
    return $html;
}

//function action_filter($code) {
//    return Display::display_course_tool_shortcuts ( $code, TRUE, '_self' );
//}
                
function Content($code) {
	return Display::display_course_content ( $code, TRUE, '_self' );
}
function Announcements($code) {
    return Display::display_course_announcements ( $code, TRUE, '_self' );
}
function Documents($code) {
    return Display::display_course_documents ( $code, TRUE, '_self' );//文档
}
function LearningDocument($code) {
    return Display::display_course_LearningDocument ( $code, TRUE, '_self' );
}
function CourseWork($code) {
    return Display::display_course_CourseWork ( $code, TRUE, '_self' );
}
function CourseExamination($code) {
    return Display::display_course_CourseExamination( $code, TRUE, '_self' );
}
function title_filter($code) {
	$html = "";
	return $html;
}

//导出课程
function ExportCourses($code) {
    $html = "";
    $html .= confirm_href ( 'enroll.gif', '你确定要导出该课程吗？', '导出课程', 'course_export.php?export_id=' . $code );
    return $html;
}


//处理批量操作
if (isset ( $_POST ['action'] )) {
	switch (getgpc("action","P")) {
		// 批量删除课程
		case 'delete_courses' :
			$deleted_course_count = 0;
			$course_codes = $_POST['courses'];    
			if (count( $course_codes ) > 0) {   
				foreach ( $course_codes as $index => $course_code ) {
                
                                    $course_code=trim($course_code);
                
					if (CourseManager::delete_course ( $course_code, false )) $deleted_course_count ++;
				}
			}
			if (count ( $course_codes ) == $deleted_course_count) {   
//				Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/course/course_list.php' );
                               tb_close('course_list.php' );
			} else {
//				Display::display_msgbox ( '某些课程没有成功删除,可能原因是你没有删除的权限!', 'main/admin/course/course_list.php', 'warning' );
			       tb_close('course_list.php' );
                          }
			break;
	}
}

//处理删除课程
if (isset ( $_GET ['delete_course'] )) {
	$course_info = CourseManager::get_course_information (getgpc("delete_course","G") );
	if (! can_do_my_bo ( $course_info ['created_user'] )) {
//		Display::display_msgbox ( '对不起,你没有操作的权限!', 'main/admin/course/course_list.php', 'warning' );
            tb_close('course_list.php' );
	}
	$delete_policy = get_setting ( 'permanently_remove_deleted_files' ); //永久删除文件
	$rtn = CourseManager::delete_course ( getgpc('delete_course'), $delete_policy == "true" ? FALSE : TRUE );
//	Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/course/course_list.php' );
        tb_close('course_list.php' );
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '{element} ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ( 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey' ) );
$sql = "SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
$category_cnt = Database::get_into_array2 ( $sql );

$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
$cate_options [""] = "---所有分类---";
foreach ( $category_tree as $item ) {
	$cate_name = $item ['name'] . (($category_cnt [intval($item ['id'])]) ? "&nbsp;(" . $category_cnt [intval($item ['id'])] . ")" : "");
	$cate_options [intval($item ['id'])] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$form->addElement ( 'select', 'category_id', get_lang ( 'CourseCategory' ), $cate_options, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );

//by changzf

?>
<aside id="sidebar" class="column course open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
<h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 课程管理</h4>
<div class="manageinfo boxPublic">
    <dl class="inlet">
        <dt><?php echo '&nbsp;&nbsp;' . link_button ( '', '新建课程', 'course_add.php', '70%', '80%' );?></dt>
        <dl>功能描述：新建一个课程</dl>
        <span class="access"> <?php echo '&nbsp;&nbsp;' . link_button ( '', '进入', 'course_add.php', '70%', '80%' );?></span>
    </dl>
    <dl class="inlet">
        <dt><?php echo '&nbsp;&nbsp;' . link_button ( '', '导入课程', '../import_export/courses_import.php', '80%', '70%' );?></dt>
        <dl>功能描述：导入一个课程</dl>
        <span class="access"><?php echo '&nbsp;&nbsp;' . link_button ( '', '进入', '../import_export/courses_import.php', '80%', '70%' );?></span>
    </dl>
    <dl class="inlet">
        <dt><a href="<?=URL_APPEDND;?>/main/admin/course/course_category_iframe.php" title="进入课程分类管理">课程分类管理</a></dt>
        <dl>功能描述：进入课程分类管理</dl>
        <span class="access"><a href="<?=URL_APPEDND;?>/main/admin/course/course_category_iframe.php" title="进入">进入</a></span>
    </dl>
    <dl class="inlet">
        <dt><a href="<?=URL_APPEDND;?>/main/admin/course/course_plan.php" title="进入课程调度">课程调度</a></dt>
        <dl>功能描述：进入课程调度</dl>
        <span class="access"><a href="<?=URL_APPEDND;?>/main/admin/course/course_plan.php" title="进入">进入</a></span>
    </dl>
</div>


<div class="managerSearch">
    <div class="seart">
	<?php $form->display ();?>
       
    </div>
</div>



<article class="module width_full hidden">
<?php

 
$action = '&nbsp;' . link_button ( 'quiz_22.png', 'CourseExam', $web_code_path . 'exercice/course_exam_edit.php?cidReq=' . $course_system_code, '90%', '90%', false );

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['category_id'] ) && is_not_blank ( $_GET ['category_id'] )) $parameters ['category_id'] = intval(getgpc ( 'category_id' ));

$table = new SortableTable ( 'courses', 'get_number_of_courses', 'get_course_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );

$table->set_header ( 0, '', false);
$table->set_header ( 1, get_lang ( 'CourseTitle' ), false, null ,array('width'=>'35%') );
$table->set_header ( 2, get_lang ( 'CourseCode' ), false, null ,array('width'=>'8%') );
$table->set_header ( 3, get_lang ( '自定义编号' ), false, null ,array('width'=>'6%') );
$table->set_header ( 4, get_lang ( '讲师' ),false, null ,array('width'=>'7%')  );
$table->set_header ( 5, get_lang ( '管理员' ), false, null ,array('width'=>'5%'));
$table->set_header ( 6, get_lang ( '学员人数' ), true, null ,array('width'=>'5%') );
$table->set_header ( 7, get_lang ( '课件总数' ), true, null ,array('width'=>'5%') );

//by changzf at 235-256 line on 2012/06/08
$table->set_header ( 8,  get_lang ( 'preview' ), false, null ,array('width'=>'3%') );
$table->set_header ( 9,  get_lang ( 'describe' ), false, null ,array('width'=>'3%') );
$table->set_header ( 10, get_lang ( '进度' ), false, null ,array('width'=>'3%') );
$table->set_header ( 11, get_lang ( '公告' ), false, null ,array('width'=>'3%') );
$table->set_header ( 12, get_lang ( '文档' ), false, null ,array('width'=>'3%') );
$table->set_header ( 13, get_lang ( '课件' ), false, null ,array('width'=>'3%') );
//$table->set_header ( 14, get_lang ( '考试' ), false, null ,array('width'=>'4%') );
$table->set_header ( 14, get_lang ( '作业' ), false, null ,array('width'=>'3%') );
$table->set_header ( 15, get_lang ( '导出' ), false, null ,array('width'=>'3%') );
$table->set_header ( 16, get_lang ( '编辑' ), false, null ,array('width'=>'4%') );
$table->set_header ( 17, get_lang ( 'Delete' ), false, null ,array('width'=>'4%') );

$table->set_column_filter ( 8, 'preview_filter' );
$table->set_column_filter ( 9, 'describe_filter' );
$table->set_column_filter ( 10, 'Reporting_filter' );
$table->set_column_filter ( 11, 'Announcements' );
$table->set_column_filter ( 12, 'Documents' );
$table->set_column_filter ( 13, 'LearningDocument' );
//$table->set_column_filter ( 14, 'CourseExamination' );
$table->set_column_filter ( 14, 'CourseWork' );
$table->set_column_filter ( 15, 'ExportCourses' );
$table->set_column_filter ( 16, 'Content' );
$table->set_column_filter ( 17, 'Delete_filter' );
$table->set_form_actions ( array ('delete_courses' => get_lang ( 'DeleteCourse' ) ), 'courses' );
$table->display ();

echo '</article>'

//Display::display_footer ( TRUE );
                
?>
</section>
</body>
        </html>
