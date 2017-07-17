<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
$user_id=$_GET['user_id'];
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

function name(){
	$user_id=$_GET['user_id'];
	$sql="select firstname from user where user_id= $user_id";
        $result =Database::get_scalar_value ( $sql );
	return $result;
	}
$name=name();
//部门数据
$objDept = new DeptManager ();
function course_filer($title){
	$result ='';
	$result.='<span style="float:left">&nbsp;'.$title.'</span>';
	return $result;
	}

$redirect_url='main/admin/course/course_user_manage.php';
if (isset ( $_REQUEST ['action'] )) {
	switch (getgpc("action")) {
		case 'unsubscribe' :
			if (CourseManager::is_course_admin (intval(getgpc('user_id')), getgpc('course_code') ) == false) {
				CourseManager::unsubscribe_user (intval(getgpc('user_id')), getgpc('course_code') );
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url);
                                tb_close("course_user_manage_child.php?user_id=$user_id");
			} else {
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url,'error');
                               tb_close("course_user_manage_child.php?user_id=$user_id");
			}
			break;
		case 'batch_unsubscribe' :
			$subid = $_POST['id'];
			if ($subid && is_array ( $subid )) {
				foreach ( $subid as $id ) {
					$tmp_id_arr = explode ( "###", $id );
					$user_id = intval($tmp_id_arr [0]);
					$course_code = $tmp_id_arr [1];
					if (CourseManager::is_course_admin ( $user_id, $course_code ) == false) {
						CourseManager::unsubscribe_user ( $user_id, $course_code );
					}
				}
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url);
                                 tb_close("course_user_manage_child.php?user_id=$user_id");
			}
			break;
	}
}


function get_sqlwhere() {
	global $objDept;
	if (isset ( $_GET ['keyword_username'] ) && $_GET ['keyword_username']) {
		$keyword = trim ( Database::escape_str (getgpc("keyword_username","G") ), TRUE );
		$sql_where .= " AND  (firstname LIKE '%" . $keyword . "%')";
	}
	
	if (isset ( $_GET ['keyword_coursename'] ) && $_GET ['keyword_coursename']) {
		$keyword = trim ( Database::escape_str (getgpc("keyword_coursename","G") ), TRUE );
		$sql_where .= " AND  (title LIKE '%" . $keyword . "%')";
	}
	
	if (isset ( $_GET ['keyword_is_reqcrs'] ) && $_GET ['keyword_is_reqcrs'] != '') {
		$sql_where .= " AND is_required_course=" . Database::escape (getgpc("keyword_is_reqcrs","G") );
	}
	
	if (isset ( $_GET ['keyword_deptid'] ) and intval (getgpc ( 'keyword_deptid' )) != "0") {
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
        $user_id=$_GET['user_id'];
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT COUNT(*) AS total_number_of_items
	FROM  $tbl_course_user As t1,$tbl_course AS t2,
	$main_user_table AS t3 WHERE t1.user_id=$user_id AND t3.user_id=$user_id AND t1.course_code=t2.code AND is_course_admin=0";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	
	return Database::get_scalar_value ( $sql );
}

function get_data($from, $number_of_items, $column, $direction) {
	global $tbl_course_user;
        $user_id=$_GET['user_id'];
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
	
	$sql = "SELECT
	CONCAT(t1.user_id,'###',t1.course_code)	AS col0,

	t3.firstname		AS col2,


	t2.title 			AS col5,
	t1.is_required_course		AS col6,
	CONCAT(t1.begin_date,'~',t1.finish_date) AS col7,
	CONCAT(t1.user_id,'###',t1.course_code)	AS col8
	FROM  $tbl_course_user As t1,$tbl_course AS t2,
	$main_user_table AS t3 WHERE t1.user_id=$user_id AND t3.user_id=$user_id AND t1.course_code=t2.code AND is_course_admin=0";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
//	echo $sql;
	$res = Database::query ( $sql, __FILE__, __LINE__, 0, 'NUM' );
	$data = array ();
	while ( $adata = Database::fetch_row ( $res ) ) {
		if ($adata [3] == 1)
			$adata [3] = get_lang ( 'RequiredCourse' );
		elseif ($adata [3] == 0)
			$adata [3] = get_lang ( 'OpticalCourse' );
		$data [] = $adata;
		unset ( $adata );
	}
	return $data;
}

function action_filter($str_course_user) {
	$cu = explode ( '###', $str_course_user );
        $user_id=$_GET['user_id'];
	if ($cu && is_array ( $cu )) {
		$action_html = link_button ( 'edit.gif', 'ArrangeCourse', 'edit_user2course.php?user_id=' . $cu [0] . '&code=' . $cu [1], 300, 660, FALSE );
		$href = 'course_user_manage_child.php?action=unsubscribe&amp;course_code=' . $cu [1] . '&amp;user_id=' . $cu [0];
		$action_html .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', '删除该记录同时会删除该用户在学习这门课程时产生的所有相关数据且不可恢复,确认要执行这个操作吗?', 'Delete', $href );
	}
	return $action_html;
}

$htmlHeadXtra [] = Display::display_thickbox ();

Display::display_header ();

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}{element}</span> ' );
//$form->addElement ( 'text', 'keyword_username', get_lang ( 'FirstName' ), array ('style' => "width:120px", 'class' => 'inputText' ) );
//$form->addElement ( 'text', 'keyword_coursename', get_lang ( 'CourseTitle' ), array ('style' => "width:120px", 'class' => 'inputText' ) );

$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
//$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

$courseType = array ("" => "", "1" => get_lang ( 'RequiredCourse' ), "0" => get_lang ( "OpticalCourse" ) );
//$form->addElement ( 'select', 'keyword_is_reqcrs', get_lang ( 'CourseStudyType' ), $courseType, array ('id' => "keyword_is_reqcrs", 'style' => 'height:22px;' ) );
//$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );




$table = new SortableTable ( 'course_user', 'get_number_of_data', 'get_data', 0, NUMBER_PAGE, 'DESC' );

$parameters ['keyword_username'] = getgpc('keyword_username');
$parameters ['keyword_coursename'] = getgpc('keyword_coursename');
$parameters ['keyword_deptid'] = intval (getgpc('keyword_deptid'));
$parameters ['keyword_is_reqcrs'] = getgpc('keyword_is_reqcrs');
$parameters ['user_id'] = getgpc('user_id');
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
//$table->set_header ( $idx ++, get_lang ( 'LoginName' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ), false, null, array ('style' => 'width:20%' ) );
//$table->set_header ( $idx ++, get_lang ( 'InOrg' ), false, null, array ('style' => 'width:12%' ) );
//$table->set_header ( $idx ++, get_lang ( 'InDept' ), false, null, array ('style' => 'width:16%' ) );
$table->set_header ( $idx ++, get_lang ( 'StudyCourses' ), false, null, array ('style' => 'width:30%' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseStudyType' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseStudyDuration' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false ,null,array('style'=>'width:20%'));
$table->set_column_filter ( $idx - 1, 'action_filter' );
$actions = array ('batch_unsubscribe' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );
//$table->set_column_filter ( 2, 'course_filer' );
//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column course open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a>  &gt;<a href="<?=URL_APPEDND;?>/main/admin/course/course_user_manage.php"> 调度查看 </a> > <?= $name?></h4>
<!--    <div class="managerSearch">
            <?php // $form->display();?>
    </div>-->
    <article class="module width_full hidden">
<?php $table->display ();?>
    </article>

</section>
</body>
</html>
