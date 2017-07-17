<?php
/**
 ==============================================================================
 * 课程授权(安排)
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");

//api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$objCrsMng = new CourseManager ();

function get_sqlwhere() {
	global $restrict_org_id, $objCrsMng;
	
	$sql_where = "";
	if (is_not_blank ( $_GET ['keyword'] )) {
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
	
	$sql = "SELECT title AS col0, code AS col1,tutor_name as col2,category_code AS col3 FROM $course_table AS t1";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$courses = array ();
	$objCourse = new CourseManager ();
	while ( $course = Database::fetch_array ( $res, 'NUM' ) ) {
		$objCourse->category_path = '';
		$course [1] = api_trunc_str2 ( $course [1] );
		$course [3] = $objCourse->get_category_path ( $course [3], TRUE );
		
		//注册人数
		$sql3 = "SELECT COUNT(user_id) FROM " . $course_users_table . " WHERE course_code='" . $course [1] . "'";
		$course [4] = Database::get_scalar_value ( $sql3 );
		$course [5] = $course [1];
		$courses [] = $course;
	}
	return $courses;
}

function modify_filter($code) {
	$html = "";
	$html .= '&nbsp;' . link_button ( 'blog_user.gif', 'CourseSubscribeUserList', 'course_subscribe_user_list.php?code=' . $code, '90%', '96%', FALSE );
	$html .= '&nbsp;' . link_button ( 'add_user_big.gif', 'CourseAdmin', 'course_admins.php?code=' . $code, '70%', '76%', FALSE );
	return $html;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );
$html = '<div id="demo" class="yui-navset boxPublic">';
$html .= '<ul class="yui-nav">';
$html .= '<li  class="selected"><a href="' . URL_APPEND . 'main/admin/course/course_plan.php"><em>' . get_lang ( 'CourseAuthByCrs' ) . '</em></a></li>';
$html .= '<li><a href="' . URL_APPEND . 'main/admin/course/user_plan.php"><em> ' . get_lang ( 'CourseAuthByUser' ) . '</em></a></li>';
$html .= '<li><a href="' . URL_APPEND . 'main/admin/course/course_user_open_plan.php"><em> ' . get_lang ( 'CourseAuthByOpenUser' ) . '</em></a></li>';
$html .= '</ul>';
$html .= '</div>';
//$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}{element}</span> ' );
$form->addElement ( 'text', 'keyword',get_lang ( 'CourseCode' ).'/'.get_lang('CourseTitle'), array ('style' => "width:160px", 'class' => 'inputText' ) );
$sql = "SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
$category_cnt = Database::get_into_array2 ( $sql );
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
$cate_options [""] = "---所有分类---";
foreach ( $category_tree as $item ) {
	$cate_name = $item ['name'] . (($category_cnt [intval($item ['id'])]) ? "&nbsp;(" . $category_cnt [intval($item ['id'])] . ")" : "");
	$cate_options [intval($item ['id'])] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$form->addElement ( 'select', 'category_id', get_lang ( 'CourseCategories' ), $cate_options, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );

//by changzf
//echo '<div class="actions">';
//echo '<span style="float:left; padding-top:2px;">';
//echo '&nbsp;&nbsp;' . link_button ( 'excel.gif', 'AddUsersToACourse', 'course_user_import.php', '50%', '60%' );
//echo '</span>';
//$form->display ();
//echo '</div>';
//end

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['category_id'] ) && is_not_blank ( $_GET ['category_id'] )) $parameters ['category_id'] = intval(getgpc ( 'category_id' ));

$table = new SortableTable ( 'courses', 'get_number_of_courses', 'get_course_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );
$idx = 0;
$table->set_header ( $idx ++, get_lang ( 'CourseTitle' ), false, null, array ('style' => 'width:40%' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseCode' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseTitular' ), false, null, array ('style' => 'width:7%' ) );
$table->set_header ( $idx ++, get_lang ( 'Category' ) );
//$table->set_header(4, get_lang('Duration'));
//$table->set_header(5, get_lang('SubscriptionAllowed'));
//$table->set_header(6, get_lang('UnsubscriptionAllowed'));
$table->set_header ( $idx ++, get_lang ( 'SubscribeUserCount' ), false, null, array ('style' => 'width:7%' ) );
//$table->set_header ( $idx ++, get_lang ( 'AppliedUserCount' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:12%' ) );
$table->set_column_filter ( 5, 'modify_filter' );
//$table->display ();

//by changzf
//echo '<div class="actions">';
//$form->display ();
//echo '</div>';
//end

//echo '</div></div></div>';
//Display::display_footer ( TRUE );
?>
<aside id="sidebar" class="column course open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a> &gt;课程调度</h4>
	  <?php echo $html; ?>
      <div class="managerSearch">
      <?php 
            echo '<span class="searchtxt right">';
            echo '&nbsp;&nbsp;' . link_button ( 'excel.gif', 'AddUsersToACourse', 'course_user_import.php', '50%', '60%' );
            //$form->display ();
            echo '</span>';
       ?>  
    <!--搜索模块-->
		<?php $form->display ();?>
    </div>
  	<!--数据模块-->
    <article class="module width_full hidden">
    	<?php $table->display ();?>
    </article>
  
</section>
</body>
</html>
