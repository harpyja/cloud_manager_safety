<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

//部门数据
$objDept = new DeptManager ();
function course_filer($title){
	$result ='';
	$result.='<span style="float:left">&nbsp;'.$title.'</span>';
	return $result;
	}

$redirect_url='main/admin/course/course_user_manage.php';
//
////$sql = "SELECT  count(distinct(course_rel_user.user_id))  FROM  course_rel_user ,course ,sys_user_dept  "
//        . "WHERE course_rel_user.user_id=sys_user_dept.user_id AND "
//        . "course_rel_user.course_code=course.code AND is_course_admin=0 ";
////echo $sql;

if (isset ( $_REQUEST ['action'] )) {
	switch (getgpc("action")) {
		case 'unsubscribe' :
			if (CourseManager::is_course_admin (intval(getgpc('user_id')), getgpc('course_code') ) == false) {
				CourseManager::unsubscribe_user (intval(getgpc('user_id')), getgpc('course_code') );
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url);
                                tb_close('course_user_manage.php');
			} else {
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url,'error');
                               tb_close('course_user_manage.php');
			}
			break;
		case 'batch_unsubscribe' :
			$subid = $_POST['id'];
			if ($subid && is_array ( $subid )) {
				foreach ( $subid as $id ) {
					$tmp_id_arr = explode ( "###", $id );
					$user_id = intval($tmp_id_arr [0]);
					$course_code = $tmp_id_arr [1];
                                        global $tbl_course_user;
                                        $tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
                                        $main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
					if (CourseManager::is_course_admin ( $user_id, $course_code ) == false) {
					$sql="DELETE FROM  $tbl_course_user  WHERE $tbl_course_user.user_id=$user_id AND is_course_admin=0";
                                        Database::query ( $sql, __FILE__, __LINE__ );
                                        
                                        }
				}
//				Display::display_msgbox ( get_lang ( 'UserUnsubscribed' ), $redirect_url);
                                 tb_close('course_user_manage.php');
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
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT count(distinct(t1.`user_id`)) AS total_number_of_items
	FROM  ".$tbl_course_user." As t1,".$tbl_course." AS t2,
	".$main_user_table." AS t3 WHERE t1.user_id=t3.user_id AND t1.course_code=t2.code AND is_course_admin=0";
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
	t3.org_name		AS col3,
	t3.dept_name		AS col4,
	count(t2.title) 	AS col5,
        CONCAT(t1.user_id,'###',t3.firstname)	AS col6
	FROM  $tbl_course_user As t1,$tbl_course AS t2,
	$main_user_table AS t3 WHERE t1.user_id=t3.user_id AND t1.course_code=t2.code AND is_course_admin=0 ";

//        echo $sql;
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	
        $sql .="group by t1.user_id";
        $sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
//       echo $sql;
	$res = Database::query ( $sql, __FILE__, __LINE__, 0, 'NUM' );
	$data = array ();
	while ( $adata = Database::fetch_row ( $res ) ) {
//		if ($adata [6] == 1)
//			$adata [6] = get_lang ( 'RequiredCourse' );
//		elseif ($adata [6] == 0)
//			$adata [6] = get_lang ( 'OpticalCourse' );
		$data [] = $adata;
		unset ( $adata );
	}
//        var_dump($data);
	return $data;
}

function action_filter($str_course_user) {
	$cu = explode ( '###', $str_course_user );
	if ($cu && is_array ( $cu )) {
                
                
//		$action_html = link_button ( 'edit.gif', 'ArrangeCourse', 'course_user_manage_chlid.php?user_id=' . $cu [0] . '&code=' . $cu [1], 400, 900, FALSE );
		$href = 'course_user_manage_child.php?user_id=' . $cu [0];
//		$action_html .= '&nbsp;&nbsp;' . confirm_href ( 'file.gif', '查看用户的学习课程吗?', '', $href );
                $action_html .=icon_href ( 'file.gif', '查看用户的调度课程', $href  );
	}
	return $action_html;
}

$htmlHeadXtra [] = Display::display_thickbox ();

Display::display_header ();

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}{element}</span> ' );
$form->addElement ( 'text', 'keyword_username', get_lang ( 'FirstName' ), array ('style' => "width:120px", 'class' => 'inputText' ) );
//$form->addElement ( 'text', 'keyword_coursename', get_lang ( 'CourseTitle' ), array ('style' => "width:120px", 'class' => 'inputText' ) );

$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

//$courseType = array ("" => "", "1" => get_lang ( 'RequiredCourse' ), "0" => get_lang ( "OpticalCourse" ) );
//$form->addElement ( 'select', 'keyword_is_reqcrs', get_lang ( 'CourseStudyType' ), $courseType, array ('id' => "keyword_is_reqcrs", 'style' => 'height:22px;' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );




$table = new SortableTable ( 'course_user', 'get_number_of_data', 'get_data', 0, NUMBER_PAGE, 'DESC' );

$parameters ['keyword_username'] = getgpc('keyword_username');
$parameters ['keyword_coursename'] = getgpc('keyword_coursename');
$parameters ['keyword_deptid'] = intval (getgpc('keyword_deptid'));
$parameters ['keyword_is_reqcrs'] = getgpc('keyword_is_reqcrs');

$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'LoginName' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'InOrg' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( 'InDept' ), false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, get_lang ( '学习课程总数' ), false, null, array ('style' => 'width:15%' ) );
//$table->set_header ( $idx ++, get_lang ( 'CourseStudyType' ), false, null, array ('style' => 'width:5%' ) );
//$table->set_header ( $idx ++, get_lang ( 'CourseStudyDuration' ), false, null, array ('style' => 'width:16%' ) );
$table->set_header ( $idx ++, get_lang ( '查看' ), false ,null,array('style'=>'width:10%'));
//var_dump($a);
$table->set_column_filter ( $idx - 1, 'action_filter' );
$actions = array ('batch_unsubscribe' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );
//$table->set_column_filter ( 5, 'course_filer' );
//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column course open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a> &gt; 调度查看</h4>
    <div class="managerSearch">
            <?php $form->display();?>
    </div>
    <article class="module width_full hidden">
<?php $table->display ();?>
    </article>

</section>
</body>
</html>
