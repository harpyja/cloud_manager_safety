<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');

api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
$objDept = new DeptManager ();

$redirect_url = 'main/admin/course/course_user_open_plan.php';
if (isset ( $_REQUEST ['action'] )) {
	switch (getgpc("action")) {
		case 'unsubscribe' :
			if (CourseManager::unsubscribe_openuser (intval(getgpc('user_id')), getgpc('course_code') )) {
				Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );
			} else {
				Display::display_msgbox ( get_lang ( 'DeleteCourseOpenUserFailed' ), $redirect_url, 'warning' );
			}
			break;
		case 'batch_unsubscribe' :
			$handle_item_cnt = $post_item_cnt = 0;
			$subid = getgpc ( "id", "P" );
			if ($subid && is_array ( $subid )) {
				foreach ( $subid as $id ) {
					$tmp_id_arr = explode ( "###", $id );
					$user_id = intval($tmp_id_arr [0]);
					$course_code = $tmp_id_arr [1];
					if (CourseManager::unsubscribe_openuser ( $user_id, $course_code )) {
						$handle_item_cnt ++;
					}
				}
				if ($handle_item_cnt == count ( $subid )) {
					Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );
				} else {
					Display::display_msgbox ( get_lang ( 'DeleteCourseOpenUserFailed' ), $redirect_url, 'warning' );
				}
			}
			break;
	}
}

function get_sqlwhere() {
	global $objDept;
	if (isset ( $_GET ['keyword_username'] ) && $_GET ['keyword_username']) {
		$keyword = trim ( Database::escape_str (getgpc("keyword_username",'G') ), TRUE );
		$sql_where .= " AND  (firstname LIKE '%" . $keyword . "%' OR username LIKE '%" . $keyword . "%')";
	}
	
	if (isset ( $_GET ['course_code'] ) && $_GET ['course_code']) {
		$keyword = trim ( Database::escape_str ( getgpc ( 'course_code' ) ), TRUE );
		$sql_where .= " AND  (course_code='" . $keyword . "')";
	}
	if (isset ( $_GET ['keyword_coursename'] ) && $_GET ['keyword_coursename']) {
		$keyword = trim ( Database::escape_str (getgpc("keyword_coursename","G") ), TRUE );
		$sql_where .= " AND  (title LIKE '%" . $keyword . "%')";
	}
	
	if (isset ( $_GET ['keyword_deptid'] ) and intval(getgpc ( 'keyword_deptid' )) != "0") {
		$dept_id = intval ( escape ( getgpc ( 'keyword_deptid', 'G' ) ) );
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where .= " AND dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	$sql_where = trim ( $sql_where );
	if ($sql_where)
		return substr ( $sql_where, 3 );
	else return "";
}

function get_number_of_data() {
	global $tbl_course_user;
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT COUNT(*) AS total_number_of_items
	FROM  $tbl_course_user As t1,$tbl_course AS t2,
	$main_user_table AS t3 WHERE t1.user_id=t3.user_id AND t1.course_code=t2.code";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	
	return Database::get_scalar_value ( $sql );
}

function get_data($from, $number_of_items, $column, $direction) {
	global $tbl_course_user;
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
	
	$sql = "SELECT
	CONCAT(t1.user_id,'###',t1.course_code)	AS col0,
	t3.username		AS col1,
	t3.firstname		AS col2,
	CONCAT(t3.org_name,'/',t3.dept_name)		AS col3,
	t2.title 			AS col4,
	t2.code 			AS col5,
	CONCAT(t1.user_id,'###',t1.course_code)	AS col6
	FROM  $tbl_course_user As t1,$tbl_course AS t2,
	$main_user_table AS t3 WHERE t1.user_id=t3.user_id AND t1.course_code=t2.code";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = Database::query ( $sql, __FILE__, __LINE__, 0, 'NUM' );
	$data = array ();
	while ( $adata = Database::fetch_row ( $res ) ) {
		$data [] = $adata;
	}
	return $data;
}

function action_filter($str_course_user) {
	$cu = explode ( '###', $str_course_user );
	if ($cu && is_array ( $cu )) {
		$href = 'course_user_open_plan.php?action=unsubscribe&amp;course_code=' . $cu [1] . '&amp;user_id=' . $cu [0];
		$action_html .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', '确认要执行这个操作吗?', 'Delete', $href );
	}
	return $action_html;
}

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();

Display::display_header ();
$html = '<div id="demo" class="yui-navset boxPublic">';
$html .= '<ul class="yui-nav">';
$html .= '<li><a href="' . URL_APPEND . 'main/admin/course/course_plan.php"><em>' . get_lang ( 'CourseAuthByCrs' ) . '</em></a></li>';
$html .= '<li><a href="' . URL_APPEND . 'main/admin/course/user_plan.php"><em> ' . get_lang ( 'CourseAuthByUser' ) . '</em></a></li>';
$html .= '<li  class="selected"><a href="' . URL_APPEND . 'main/admin/course/course_user_open_plan.php"><em> ' . get_lang ( 'CourseAuthByOpenUser' ) . '</em></a></li>';
$html .= '</ul>';
$html .= '</div>';
//$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}{element}</span> ' );
//$form->addElement ( 'text', 'keyword_coursename', get_lang ( 'CourseTitle' ), array ('style' => "width:120px", 'class' => 'inputText' ) );

$sql = "SELECT code,CONCAT(title,'-',code) FROM " . Database::get_main_table ( TABLE_MAIN_COURSE ) . "";
$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$all_courses = array_insert_first ( $all_courses, array ('' => '' ) );
$form->addElement ( 'select', 'course_code', get_lang ( "Courses" ), $all_courses );

$form->addElement ( 'text', 'keyword_username', get_lang ( 'FirstName' ) . '/' . get_lang ( 'LoginName' ), array ('style' => "width:120px", 'class' => 'inputText' ) );

$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



$table = new SortableTable ( 'course_user', 'get_number_of_data', 'get_data', 0, NUMBER_PAGE, 'DESC' );

$parameters ['keyword_username'] = getgpc('keyword_username');
$parameters ['keyword_coursename'] = getgpc('keyword_coursename');
$parameters ['keyword_deptid'] = intval(getgpc('keyword_deptid'));

$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'LoginName' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ) );
$table->set_header ( $idx ++, get_lang ( 'InOrg' ) . '/' . get_lang ( 'InDept' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseTitle' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseCode' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:40px' ) );
$table->set_column_filter ( $idx - 1, 'action_filter' );
$actions = array ('batch_unsubscribe' => get_lang ( 'BatchDeleteAuthorization' ) );
$table->set_form_actions ( $actions );
//$table->display ();
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
		<?php $form->display ();?>
      </div>
  	<!--数据模块-->
    <article class="module width_full hidden">
    	<?php $table->display ();?>
    </article>
  
</section>
</body>
</html>