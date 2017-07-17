<?php
$language_file = array ('courses', 'create_course', 'index' );
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("inc/page_header.php");

include_once ('inc/global.inc.php');
api_block_anonymous_users ();
if (! api_is_admin ()) api_not_allowed ();//权限


include_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'course/course.inc.php');

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );

$user_id = api_get_user_id ();
$is_required_course = (is_equal ( $_GET ["required_crs"], "false" ) ? "0" : "1");

$htmlHeadXtra [] = Display::display_thickbox ();

//Display::display_header ();

if (api_is_platform_admin ()) {
	$sql = "SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
} else {
	$sql = "SELECT category_code,count(*) FROM $view_course_user WHERE user_id='" . escape ( $user_id ) . "' AND training_class_id=0  GROUP BY category_code";
}
$category_cnt = Database::get_into_array2 ( $sql );
$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
$cate_options [""] = "---所有分类---";
foreach ( $category_tree as $item ) {
	$cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [$item ['id']] . ")" : "");
	$cate_options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$g_keyword=  getgpc('keyword');
if (isset ( $g_keyword ) && is_not_blank ( $g_keyword )) {
	$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
}

$g_category_id=  getgpc("category_id");
if (isset ( $g_category_id ) && is_not_blank ( $g_category_id )) {
	$parameters ['category_id'] = getgpc ( 'category_id', 'G' );
}
function course_filter($title){
    $result ="";
    $result .="<span style='float:left'>".$title."</span>";
    return $result;
}
$form = new FormValidator ( 'search_simple', 'get' );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '&nbsp;{element}' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'title' => get_lang ( "CourseTitleOrCode" ) ) );
$form->addElement ( 'select', 'category_id', get_lang ( 'CourseCategory' ), $cate_options, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );
$defaults ['category_id'] = getgpc ( 'category_id', 'G' );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'id="searchbutton" id="searchbutton"' );
$form->setDefaults ( $defaults );


$table = new SortableTable ( 'courses', 'get_data_count', 'get_data_table', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );
$idx = 0;
$table->set_header ( $idx ++, get_lang ( '课程名称' ), true, array('class'=>'case-table-title') ,null,array('class'=>'c'));
$table->set_header ( $idx ++, get_lang ( '课程编号' ), true, null);
$table->set_header ( $idx ++, get_lang ( '讲师' ), true, null);
$table->set_header ( $idx ++, get_lang ( '学分' ), true, null);

//by changzf at 62-71 line on 2012/06/09
//$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '210' ) );
$table->set_header ( $idx ++, get_lang ( '内容' ), false, null);
$table->set_header ( $idx ++, get_lang ( '课程公告' ), false, null);
$table->set_header ( $idx ++, get_lang ( '课程文档' ), false, null);
$table->set_header ( $idx ++, get_lang ( '学习课件' ), false, null);
$table->set_header ( $idx ++, get_lang ( '考试' ), false, null);
$table->set_header ( $idx ++, get_lang ( '作业' ), false, null);
$table->set_header ( $idx ++, get_lang ( '课程用户' ), false, null);
$table->set_header ( $idx ++, get_lang ( '学习进度' ), false, null);
$table->set_column_filter ( 0, 'course_filter' );
//课程名称	课程编号	讲师 ↑	学分	内容	课程公告	课程文档	学习课件	课程考试	课程作业	课程用户	学习进度
function get_sqlwhere() {
	global $is_required_course;
	$sql_where = "";
        $get_key=  getgpc('keyword');
	if (isset ( $get_key ) && ! empty ( $get_key )) {
		$keyword = Database::escape_str ( $_GET['keyword'], TRUE );
		$sql_where .= " AND (course.code LIKE '%" . $keyword . "%' OR course.title LIKE '%" . $keyword . "%') ";
	}
	
        $get_category=  getgpc('category_id');
	if (isset ( $get_category ) && is_not_blank ( $get_category )) {
		$sql_where .= " AND course.category_code=" . Database::escape ( getgpc ( 'category_id', 'G' ) );
	}
	
	$sql_where = trim ( $sql_where );
	return $sql_where;
}

function get_data_count() {
	$tbl_main_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$tbl_main_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	if (api_is_platform_admin ()) {
		$sql = "SELECT  COUNT(*) FROM  " . $tbl_main_course . " AS course WHERE 1 ";
		$sql_where = get_sqlwhere ();
		if ($sql_where) $sql .= $sql_where;
	} else {
		$user_id = api_get_user_id ();
		$sql = "SELECT COUNT(*)  FROM    " . $tbl_main_course . " AS course," . $tbl_main_course_user . " AS course_rel_user
			 WHERE course.code = course_rel_user.course_code AND  course_rel_user.user_id = '" . $user_id . "'  AND course_rel_user.is_course_admin=1 ";
		$sql_where = get_sqlwhere ();
		if ($sql_where) $sql .= $sql_where;
	}
	return Database::get_scalar_value ( $sql );
    echo $sql;
}

function get_data_table($from, $number_of_items, $column, $direction) {
	$tbl_main_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$tbl_main_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	if (api_is_platform_admin ()) {
        //by changzf add 119-125 line on 2012/06/09
		$sql = "SELECT  course.title	AS	col0,
					course.code		AS	col1,
					course.tutor_name	AS	col2,
					course.credit	AS	col3,
					course.code		AS	col4,
					course.code		AS	col5,
					course.code		AS	col6,
					course.code		AS	col7,
					course.code		AS	col8,
					course.code		AS	col9,
					course.code		AS	col10,
					course.code		AS	col11
					FROM    " . $tbl_main_course . " AS course WHERE 1 ";
		$sql_where = get_sqlwhere ();
		if ($sql_where) $sql .= $sql_where;
	} else {
		$user_id = api_get_user_id ();
        //by changzf add 138-144 line on 2012/06/09
		$sql = "SELECT  course.title	AS	col0,
					course.code		AS	col1,
					course.tutor_name	AS	col2,
					course.credit	AS	col3,
					course.code		AS	col4,
					course.code		AS	col5,
					course.code		AS	col6,
					course.code		AS	col7,
					course.code		AS	col8,
					course.code		AS	col9,
					course.code		AS	col10,
					course.code		AS	col11
					FROM    " . $tbl_main_course . " AS course," . $tbl_main_course_user . " AS course_rel_user
					WHERE course.code = course_rel_user.course_code	AND  course_rel_user.user_id = '" . $user_id . "'  AND course_rel_user.is_course_admin=1 ";
		$sql_where = get_sqlwhere ();
		if ($sql_where) $sql .= $sql_where;
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";

	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$courses = array ();
	//$objCourse = new CourseManager ();
	while ( $course = Database::fetch_array ( $res, 'NUM' ) ) {
		$course [0] = Display::return_icon ( "course.gif" ) . "&nbsp;" . api_trunc_str2($course [0],50);

        //by changzf at 160-168 line on 2012/06/09
        //$course [4] = Display::display_course_tool_shortcuts( $course [4] );
        $course [4] = Display::display_course_content( $course [4] );
        $course [5] = Display::display_course_announcements ( $course [5] );
        $course [6] = Display::display_course_documents ( $course [6] );
        $course [7] = Display::display_course_LearningDocument ( $course [7] );
        $course [8] = Display::display_course_CourseExamination ( $course [8] );
        $course [9] = Display::display_course_CourseWork1 ( $course [9] );
        $course [10] = Display::display_course_CourseUsers ( $course [10] );
        $course [11] = Display::display_course_Reporting ( $course [11] );
		$courses [] = $course;
	}
	return $courses;
}

//Display::display_footer (TRUE);
?>
<aside id="sidebar" class="column mydesktop open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/portal/sp/index.php">首页</a> &gt; <a href="<?=URL_APPEDND;?>/portal/sp/teacher_portal.php">我的桌面</a> &gt; 我的课程管理</h4>

    <div class="managerSearch">
			<?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
			<?php $table->display ();?>
        </form>
    </article>
</section>
</body>
</html>
