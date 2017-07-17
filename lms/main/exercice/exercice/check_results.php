<?php
/*
 测验列表
 */

$language_file = array ('exercice', 'admin' );
$cidReset = TRUE;
include_once ('../inc/global.inc.php');
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');
include_once ('exercise.lib.php');
//api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');

$course_code = isset ( $_REQUEST ['course_code'] ) && $_REQUEST ['course_code'] ? getgpc ( 'course_code' ) : api_get_course_code ();
$action = getgpc ( 'action', 'G' );
$id = intval(getgpc ( 'id' ));
$user_id = intval(getgpc ( 'user_id') );
$objStat = new ScormTrackStat ();
$objDept = new DeptManager ();

if (is_equal ( $_REQUEST ['action'], 'modify_score' )) {
	$sql = "SELECT score FROM " . $tbl_exam_rel_user . " t1 WHERE exam_id=" . Database::escape ( $id )." AND user_id =".$user_id;
	$old_score = Database::getval ( $sql );
       // var_dump($sql);
	$form = new FormValidator ( 'modify_score', 'post' );
	$form->addElement ( 'hidden', 'id', $id );
        $form->addElement ( 'hidden', 'user_id', $user_id );
	$form->addElement ( 'hidden', 'action', 'modify_score_save' );
	$form->add_textfield ( 'score', get_lang ( 'NewScore' ), true, array ('style' => "width:250px", 'class' => 'inputText' ) );
	$form->addRule ( 'score', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	$form->addRule ( 'score', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
	$group = array ();
	$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
	$defaults ['score'] = $old_score;
	$form->setDefaults ( $defaults );
	Display::setTemplateBorder ( $form, '99%' );
	Display::display_header ( NULL, false );
	$form->display ();
	Display::display_footer ();
	exit ();
}
if (is_equal ( $_REQUEST ['action'], 'modify_score_save' )) {
        $user_id = intval(getgpc ( 'user_id' ));
	$new_score = getgpc ( 'score' );
	$sql = "UPDATE " . $tbl_exam_rel_user . " SET score=" . Database::escape ( $new_score ) . "  WHERE exam_id=" . Database::escape ( $id )." AND user_id =".$user_id;
	api_sql_query ( $sql );
          // var_dump($sql);
        	$sql = "UPDATE " . $tbl_exam_result . " SET score=" . Database::escape ( $new_score ) . "  WHERE exe_id=" . Database::escape ( $id );
	api_sql_query ( $sql );
     
	tb_close ();
	exit ();
}

if (isset ( $_GET ['action'] )) {
	switch ($action) {
		case 'export' :
			$data_header = array (get_lang ( 'ExamTitle' ), 
					get_lang ( 'Type' ), 
					get_lang ( 'LoginName' ), 
					get_lang ( 'FirstName' ), 
					get_lang ( 'OfficialCode' ), 
					get_lang ( 'UserInDept' ), 
					get_lang ( 'FinalScore' ), 
					get_lang ( 'MaxScore' ), 
					get_lang ( 'MinScore' ), 
					get_lang ( 'FirstAttemptScore' ), 
					get_lang ( 'LastAttemptScore' ), 
					get_lang ( 'FirstAttemptDate' ), 
					get_lang ( 'LastAttemptDate' ), 
					get_lang ( 'AttemptTimes' ), 
					get_lang ( 'IsPased' ) );
			$in_export = true;
			$export_data = get_export_data_list ();
			//var_dump($export_data);exit;
			//$filename = get_lang('Classes').get_lang('StatByUserDetails') .'_'. date ( 'Ymd' ); //导出文件名
			$filename = '在线考试_' . date ( 'Ymd' ); //导出文件名
			array_unshift ( $export_data, $data_header );
			Export::export_data ( $export_data, $filename, 'xls' );
			break;
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();

$htmlHeadXtra [] = '<script language="JavaScript" type="text/JavaScript">
	$(document).ready( function() {
		$("#org_id").change(function(){
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"options_get_all_sub_depts",org_id:$("#org_id").val()},
				function(data,textStatus){
					//alert(data);
					$("#dept_id").html(data);
				});
		});
	});
</script>';
$htmlHeadXtra [] = '<style>#center{margin:0}</style>';

Display::display_header ( NULL );

//课程的跟踪统计信息


function get_sqlwhere() {
	global $objDept;
	$sql_where = "";
	if (isset ( $_GET ['keyword'] ) && ! empty ( $_GET ['keyword'] )) {
		$keyword = escape ( $_GET ['keyword'], TRUE );
		$sql_where .= " AND (t3.title LIKE '%" . $keyword . "%') ";
	}
	if (isset ( $_GET ['keyword_user'] ) && ! empty ( $_GET ['keyword_user'] )) {
		$keyword = escape ( getgpc ( 'keyword_user' ), TRUE );
		$sql_where .= " AND (t2.firstname LIKE '%" . $keyword . "%'  OR t2.username LIKE '%" . $keyword . "%') ";
	}
	
	if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
		$dept_id = (escape ( intval(getgpc ( 'keyword_deptid', 'G' )) ));
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where .= " AND dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	if (isset ( $_GET ['course_code'] ) && ! empty ( $_GET ['course_code'] )) {
		$sql_where .= " AND t3.cc=" . Database::escape ( getgpc ( 'course_code', 'G' ) );
	}
	
	if (isset ( $_GET ['type'] ) && is_not_blank ( $_GET ['type'] )) {
		$sql_where .= " AND t3.type=" . Database::escape ( getgpc ( 'type', 'G' ) );
	}
	
	$sql_where = trim ( $sql_where );
	if ($sql_where)
		return substr ( ltrim ( $sql_where ), 3 );
	else return "";
}

function get_data_count() {
	global $TBL_EXERCICES, $tbl_exam_rel_user;
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT COUNT(*) FROM $tbl_exam_rel_user AS t1 , $TBL_EXERCICES AS t3 , $table_user AS t2 WHERE t1.exam_id=t3.id AND t1.user_id=t2.user_id ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	//echo $sql."<br/>";
	return Database::get_scalar_value ( $sql );
}

function get_data_list($from, $number_of_items, $column, $direction) {
	global $objStat, $in_export, $TBL_EXERCICES, $tbl_exam_rel_user;
	$table_course_user = Database::get_main_table ( VIEW_COURSE_USER );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$fields = array ('t3.title', 
			't3.cc', 
			't2.username', 
			't2.firstname', 
			't2.official_code', 
			"CONCAT(t2.org_name,'/',t2.dept_name)", 
			't1.score', 
			'ROUND(t1.best_attempt_score/t1.paper_score*100)', 
			'ROUND(t1.min_score/t1.paper_score*100)', 
			'ROUND(t1.first_attempt_score/t1.paper_score*100)', 
			'ROUND(t1.last_attempt_score/t1.paper_score*100)', 
			't1.first_attempt_date', 
			't1.last_attempt_date', 
			't1.attempt_times', 
			't1.paper_score', 
			'is_pass', 
			't3.type', 
			't1.id' );
	$sql = "SELECT " . sql_field_list ( $fields ) . " FROM $tbl_exam_rel_user AS t1 , $TBL_EXERCICES AS t3 , $table_user AS t2 WHERE t1.exam_id=t3.id AND t1.user_id=t2.user_id ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	$sql .= " ORDER BY col0 ASC ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res, 'NUM' ) ) {
		$type = $row [16];
		if ($type == 3)
			$nameTools = '自测练习';
		elseif ($type == 2)
			$nameTools = '课程毕业考试';
		elseif ($type == 1)
			$nameTools = '综合考试';
		$row [1] = $nameTools . ($type == 2 ? '(课程编号:' . $row [1] . ')' : '');
		$row [11] = substr ( $row [10], 0, 16 );
		$row [12] = substr ( $row [11], 0, 16 );
		$row [15] = ($row [15] == 1 ? get_lang ( 'Yes' ) : get_lang ( 'No' ));
		$row [16] = link_button ( 'edit.gif', 'ModifyScore', 'query_quiz.php?action=modify_score&id=' . $row [17], '30%', '40%', 0 );
		unset ( $row [17] );
		$rows [] = $row;
	}
	return $rows;
}

function get_export_data_list() {
	global $objStat, $in_export, $TBL_EXERCICES, $tbl_exam_rel_user;
	$table_course_user = Database::get_main_table ( VIEW_COURSE_USER );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$fields = array ('t3.title', 
			't3.cc', 
			't2.username', 
			't2.firstname', 
			't2.official_code', 
			"CONCAT(t2.org_name,'/',t2.dept_name)", 
			't1.score', 
			'ROUND(t1.best_attempt_score/t1.paper_score*100)', 
			'ROUND(t1.min_score/t1.paper_score*100)', 
			'ROUND(t1.first_attempt_score/t1.paper_score*100)', 
			'ROUND(t1.last_attempt_score/t1.paper_score*100)', 
			't1.first_attempt_date', 
			't1.last_attempt_date', 
			't1.attempt_times', 
			't1.paper_score', 
			'is_pass', 
			't3.type' );
	$sql = "SELECT " . sql_field_list ( $fields ) . " FROM $tbl_exam_rel_user AS t1 , $TBL_EXERCICES AS t3 , $table_user AS t2 WHERE t1.exam_id=t3.id AND t1.user_id=t2.user_id ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	$sql .= " ORDER BY col1";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res, 'NUM' ) ) {
		$type = $row [16];
		if ($type == 3)
			$nameTools = '自测练习';
		elseif ($type == 2)
			$nameTools = '课程毕业考试';
		elseif ($type == 1)
			$nameTools = '综合考试';
		$row [1] = $nameTools . ($type == 2 ? '(课程编号:' . $row [1] . ')' : '');
		$row [11] = substr ( $row [11], 0, 16 );
		$row [12] = substr ( $row [12], 0, 16 );
		$row [14] = ($row [15] == 1 ? get_lang ( 'Yes' ) : get_lang ( 'No' ));
		unset($row[15]);
		unset ( $row [16] );
		$rows [] = $row;
	}
	return $rows;
}

if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
	$all_sub_depts = $objDept->get_sub_dept_ddl ( $_GET ['keyword_deptid'] );
	foreach ( $all_sub_depts as $item ) {
		$depts [$item ['id']] = str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'];
	}
}

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span class="searchtxt">{label}&nbsp;{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'ExamTitle' ), array ('style' => "width:100px", 'class' => 'inputText', 'title' => get_lang ( 'ExamTitle' ) ) );

$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
$form->addElement ( 'text', 'keyword_user', $keyword_tip, array ('style' => "width:100px", 'class' => 'inputText', 'title' => $keyword_tip ) );

//$form->addElement ( 'select', 'keyword_org', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;min-width:100px', 'title' => get_lang ( 'InOrg' ) ) );
$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;min-width:100px' ) );

$all_types = array ('0' => '', '1' => '综合考试', '3' => '自测练习', '2' => '课程毕业考试' );
$form->addElement ( 'select', 'type', get_lang ( "Type" ), $all_types );

$sql = "SELECT code,CONCAT(title,'-',code) FROM " . Database::get_main_table ( TABLE_MAIN_COURSE ) . "";
$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$all_courses = array_insert_first ( $all_courses, array ('' => '' ) );
$form->addElement ( 'select', 'course_code', get_lang ( "Courses" ), $all_courses );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit" id="searchbutton"' );

$parameters = array ();
if (isset ( $_GET ['action'] ) && is_not_blank ( $_GET ['action'] )) $parameters ['action'] = getgpc ( 'action', 'G' );
if (isset ( $_GET ['type'] ) && is_not_blank ( $_GET ['type'] )) $parameters ['type'] = getgpc ( 'type', 'G' );
if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword', 'G' );
if (isset ( $_GET ['keyword_deptid'] ) && is_not_blank ( $_GET ['keyword_deptid'] )) $parameters ['keyword_deptid'] = intval(getgpc ( 'keyword_deptid', 'G' ));
if (isset ( $_GET ['keyword_user'] ) && is_not_blank ( $_GET ['keyword_user'] )) $parameters ['keyword_user'] = getgpc ( 'keyword_user', 'G' );
$url = api_add_url_querystring ( 'query_quiz.php?action=export', $parameters );



$table = new SortableTable ( 'query_quiz', 'get_data_count', 'get_data_list', 0, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, get_lang ( 'ExamTitle' ) );
$table->set_header ( $idx ++, get_lang ( 'Type' ) );
$table->set_header ( $idx ++, get_lang ( 'LoginName' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ) );
$table->set_header ( $idx ++, get_lang ( 'OfficialCode' ) );
$table->set_header ( $idx ++, get_lang ( 'UserInDept' ) );
$table->set_header ( $idx ++, get_lang ( 'FinalScore' ) );
$table->set_header ( $idx ++, get_lang ( 'MaxScore' ) );
$table->set_header ( $idx ++, get_lang ( 'MinScore' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstAttemptScore' ) );
$table->set_header ( $idx ++, get_lang ( 'LastAttemptScore' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstAttemptDate' ) );
$table->set_header ( $idx ++, get_lang ( 'LastAttemptDate' ) );
$table->set_header ( $idx ++, get_lang ( 'AttemptTimes' ) );
$table->set_header ( $idx ++, get_lang ( 'ExamPaperScore' ) );
$table->set_header ( $idx ++, get_lang ( 'IsPased' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ) );

//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column exercice open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>  &gt; <a href="<?=URL_APPEDND;?>/main/exercice/exercice.php" title="考试管理">考试管理</a>  &gt; 考试成绩查询</h4>
    <div class="managerTool">
        <?php echo '<span style="float:right; padding-top:5px;">', link_button ( 'excel.gif', 'Export', $url ), '</span>';?>
    </div>
    <div class="managerSearch">
        <form action="#" method="post" id="searchform">
<!--            <span class="searchtxt">考试名称：</span><input type="text" id="searchtext" value="">-->
<!--            <span class="searchtxt">登陆名/姓名：</span><input type="text" id="searchtext" value="">-->
<!--            <span class="searchtxt">所属部门：</span>-->
<!--            <select>-->
<!--                <option>---所有分类---</option>-->
<!--                <option>AAAAA</option>-->
<!--            </select>-->
<!--            <span class="searchtxt">类型</span>-->
<!--            <select>-->
<!--                <option>---所有分类---</option>-->
<!--                <option>AAAAA</option>-->
<!--            </select>-->
<!--            <span class="searchtxt">课程</span>-->
<!--            <select>-->
<!--                <option>---所有分类---</option>-->
<!--                <option>AAAAA</option>-->
<!--            </select>-->
<!--            <input type="button" id="searchbutton" value="查询">-->

            <?php $form->display (); ?>
        </form>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
<?php $table->display ();  ?>
            </table>
        </form>
    </article>

</section>
</body>
</html>
