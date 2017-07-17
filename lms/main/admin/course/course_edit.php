<?php
/**
 ==============================================================================
 * 课程信息修改
 ==============================================================================
 */
$language_file = array ('course_info', 'admin', 'create_course' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "usermanager.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
echo "<style type='text/css'>
.yui-skin-sam .yui-navset .yui-nav #edit a,.yui-skin-sam .yui-navset .yui-nav #edit a:focus,.yui-skin-sam .yui-navset .yui-nav #edit a:hover
    </style>";

$firstExpirationDelay = 31536000; // 课程默认过期时间 <- 86400*365    // 60*60*24 = 1 jour = 86400

function credit_range_check($inputValue) {
	return (intval ( $inputValue ) > 0 );
}
function credit_hours_range_check($inputValue) {
	return (intval ( $inputValue ) > 0);
}
function fee_check($inputValue) {
	//var_dump($inputValue);
	if (isset ( $inputValue ) && is_array ( $inputValue )) {
		if ($inputValue ['is_free'] == '0') {
			return floatval ( $inputValue ['payment'] ) > 0;
		} else {
			return true;
		}
	}
	return false;
}
$lessonid=getgpc('cidReq');
$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_user = $course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$table_user = Database::get_main_table ( TABLE_MAIN_USER );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_setting = Database::get_course_table ( TABLE_COURSE_SETTING );

$course_code = isset ( $_GET ['course_code'] ) ? getgpc('course_code') : getgpc('code');
$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';


$description_id = isset ( $_REQUEST ['description_id'] ) ? intval ( getgpc ( 'description_id' ) ) :10;

$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li  id="edit" ' . ($description_id == 10 ? 'class="selected"' : '') . '><a href="../course/course_edit.php?cidReq='.$lessonid.'&description_id=' . 10 . '"><em>' . get_lang ( '课程设置' ) . '</em></a></li>';

$html .= '<li  ' . ($description_id == 0 ? 'class="selected"' : '') . '><a href="../../course_description/index.php?cidReq='.$lessonid.'&description_id=' . 0 . '"><em>' . get_lang ( '课程信息' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="../../course_description/index.php?cidReq='.$lessonid.'&description_id=' . 8 . '"><em>' . get_lang ( '实验步骤' ) . '</em></a></li>';
//$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="../../course_description/step.php?cidReq='.$lessonid.'"><em>' . get_lang ( '模拟仿真实验' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 7 ? 'class="selected"' : '') . '><a href="../../course_description/index.php?cidReq='.$lessonid.'&description_id=' . 7 . '"><em>' . '教学大纲' . '</em></a></li>';
$html .= '<li  ' . ($description_id == 14 ? 'class="selected"' : '') . '><a href="../../course_description/lessontop.php?cidReq='.$lessonid.'"><em>' . get_lang ( '网络拓扑') . '</em></a></li>';

$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

//machao 
$sql = "SELECT user.user_id,user.firstname FROM $table_user as user,$table_course_user as course_user WHERE course_user.status='1' AND course_user.is_course_admin='1'  AND course_user.user_id=user.user_id AND course_user.course_code='" . $lessonid . "' LIMIT 1";
$res0 = api_sql_query ( $sql, __FILE__, __LINE__ );
list ( $course_admin_id, $course_admin_name ) = Database::fetch_row ( $res0 );
$course ['course_teachers'] = $course_admin_id;


//var_dump($course_admin_name);
//获取课程信息

$course = CourseManager::get_course_information ( $lessonid );
$course['course_admin']=$course_admin_id;   //machao

if ($course == false) api_redirect ( "course_list.php" );

$deptObj = new DeptManager ();

$tool_name = get_lang ( 'ModifyCourseInfo' );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("#visual_code").parent().append("<div class=\'onShow\'>' . get_lang ( 'AddCourseCodeTip' ) . '</div>");
		$("#credit").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditTip' ) . '</div>");
		$("#credit_hours").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditHoursTip' ) . '</div>");
		' . ($course ['org_id'] < 0 ? '$("#org_id").attr("disabled","true")' : '') . ';
		
		$("#is_shared1").click(function(){
			$("#org_id").attr("disabled","true");
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:"-1",empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});
		
		$("#is_shared0").click(function(){
			$("#org_id").removeAttr("disabled");
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:$("#org_id").val(),empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});
		
		$("#org_id").change(function(){
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:$("#org_id").val(),empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});
	});
	</script>';

$htmlHeadXtra [] = '<script type="text/javascript">
function moveItem(origin , destination){
	
	for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			destination.options[destination.length] = new Option(origin.options[i].text,origin.options[i].value);
			origin.options[i]=null;
			i = i-1;
		}
	}
	destination.selectedIndex = -1;
	sortOptions(destination.options);
	
}

function sortOptions(options) {

	newOptions = new Array();
	for (i = 0 ; i<options.length ; i++)
		newOptions[i] = options[i];
		
	newOptions = newOptions.sort(mysort);
	options.length = 0;
	for(i = 0 ; i < newOptions.length ; i++)
		options[i] = newOptions[i];
	
}

function mysort(a, b){
	if(a.text.toLowerCase() > b.text.toLowerCase()){
		return 1;
	}
	if(a.text.toLowerCase() < b.text.toLowerCase()){
		return -1;
	}
	return 0;
}

function valide(){
	//var options = document.getElementById("course_teachers").options;
	//for (i = 0 ; i<options.length ; i++)
		//options[i].selected = true;
	document.update_course.submit();
}

function fee_switch_radio_button(form, input){
	var NodeList = document.getElementsByTagName("input");
	for(var i=0; i< NodeList.length; i++){
		if(NodeList.item(i).name==input  && NodeList.item(i).value=="0"){
			NodeList.item(i).checked=true;
			document.getElementById("is_audit_enabled").checked=false;
		}
	}
}
</script>';


//修改课程信息表单
$form = new FormValidator ( 'update_course' );
$form->addElement ( 'hidden', 'code', $course_code );
//$form->addElement('header', 'header', get_lang('ModifyCourseInfo'));


//编号
$form->add_textfield ( 'visual_code', get_lang ( 'CourseCode' ), true, array ('style' => "width:40%", 'class' => 'inputText', "readonly" => "true" ) );
$form->applyFilter ( 'visual_code', 'strtoupper' );

//标题
$form->add_textfield ( 'title', get_lang ( 'CourseTitle' ), true, array ('style' => "width:40%", 'class' => 'inputText' ) );
//自定义编号
$form->addElement ( 'text', 'nodeId', get_lang ( '自定义编号' ),array ('style' => "width:200px", 'class' => 'inputText' ) );
$form->addRule ( 'nodeId', get_lang ( 'Max' ), 'maxlength', 50 );

/*$form->addElement('select', 'course_teachers', get_lang('CourseTeachers'), $all_teachers, '');*/
//liyu:20090801
//    $modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'COURSE_UPDATE', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/pop_frame.php?", 'form_name' => 'update_course', 'TO_NAME' => 'TO_NAME_ADMIN', 'TO_ID' => 'TO_ID_ADMIN' );
//    $form->addElement ( 'modaldialog_select', 'courseTeachers', get_lang ( 'CourseTeachers' ), NULL, $modaldialog_select_options );
//    $form->addRule ( 'courseTeachers', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//    $sql = "SELECT GROUP_CONCAT( CONCAT(firstname,'(',username,')')),GROUP_CONCAT(t1.user_id) FROM $table_course_user AS t1,$table_user AS t2 WHERE t1.user_id=t2.user_id AND is_course_admin=1 AND tutor_id=1 AND course_code = " . Database::escape ( $course_code );
//    list ( $course_admin_name, $course_admin_id ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
//$course ['courseTeachers'] ['TO_NAME_ADMIN'] = $course_admin_name;
//$course ['courseTeachers'] ['TO_ID_ADMIN'] = $course_admin_id;

//$course ['courseTeachers'] ['TO_NAME_ADMIN'] = $_user ['firstName'];

// 课程管理员   machao$course_admin_id, $course_admin_name
$sql_admin="select `user_id`,`firstname` from `user` where `status`='10' or `status`='1'";
$result=Database::get_into_array2 ( $sql_admin, __FILE__, __LINE__ );
//var_dump($result);
$form->addElement ( 'select', 'course_admin', get_lang ( "课程管理员" ), $result, array ('id' => "user", 'style' => 'height:22px;' ) );

//学分
$form->addElement ( 'text', 'credit', get_lang ( "CourseCredit" ), array ('id' => 'credit', 'style' => "width:80px;text-align:right", 'class' => 'inputText', 'title' => get_lang ( 'CreditTip' ) ) );
$form->addRule ( 'credit', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'credit', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'credit', get_lang ( '' ), 'rangelength', array (1, 2 ) );
$form->addRule ( 'credit', get_lang ( 'CreditTip' ), 'callback', 'credit_range_check' );

//学时
$form->addElement ( 'text', 'credit_hours', get_lang ( "CourseCreditHours" ), array ('maxlength' => '4', 'style' => "width:80px;text-align:right", 'class' => 'inputText', 'title' => get_lang ( 'CreditHoursTip' ) ) );
$form->addRule ( 'credit_hours', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'credit_hours', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'credit_hours', get_lang ( '' ), 'rangelength', array (1, 4 ) );
$form->addRule ( 'credit_hours', get_lang ( 'CreditHoursTip' ), 'callback', 'credit_hours_range_check' );

//V1.4.0
$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE, $course ['org_id'] );
foreach ( $category_tree as $item ) {
	$parent_cate_option [intval($item ['id'])] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $item ['name'];
}
$form->addElement ( 'select', 'category_code', get_lang ( "CourseFaculty" ), $parent_cate_option, array ('id' => "category_code", 'style' => 'height:22px;' ) );

//学员选修学习默认天数
$form->addElement ( 'text', 'default_learing_days', get_lang ( "DefaultLearningDays" ), array ('id' => 'default_learing_days', 'maxlength' => '4', 'style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$form->addRule ( 'default_learing_days', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'default_learing_days', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'default_learing_days', get_lang ( '' ), 'rangelength', array (1, 4 ) );

//通过条件
//$group = array();
//$group[] = $form->createElement('radio', 'pass_condition', null, get_lang('TeacherPassJudgment'), 1);
//$group[] = $form->createElement('radio', 'pass_condition', null, get_lang('PassAnExam'), 2);
//$form->addGroup($group,null,get_lang('PassCondition'),null,false);


//允许注册?
/*$group = array();
$group[] = $form->createElement('radio', 'subscribe', null, get_lang('Allowed'), 1);
$group[] = $form->createElement('radio', 'subscribe', null, get_lang('Denied'), 0);
$form->addGroup($group,'subscribe',get_lang('Subscription'),null,false);*/
$form->addElement ( 'hidden', 'subscribe' );
$values ['subscribe'] = 1;

//允许注销?
$form->addElement ( "hidden", "unsubscribe", "0" );
/*$group = array();
 $group[] = $form->createElement('radio', 'unsubscribe', null, get_lang('AllowedToUnsubscribe'), 1);
 $group[] = $form->createElement('radio', 'unsubscribe', null, get_lang('NotAllowedToUnsubscribe'), 0);
 $form->addGroup($group,'unsubscribe',get_lang('Unsubscription'),null,false);*/

//允许注册,注销课程
$form->addElement ( 'checkbox', 'is_subscribe_enabled', get_lang ( "SubscribePriv" ), get_lang ( 'AllowedCourseAdminSubscribeUser' ), array ("id" => "is_subscribe_enabled" ) );

//允许审批,有审批选课申请的
/*$group = array();
$group[] = $form->createElement('radio','is_audit_enabled',null,get_lang('AllowedOrgAdminAudit'),3);
$group[] = $form->createElement('radio','is_audit_enabled',null,get_lang('AllowedDeptAdminAudit'),2);
$group[] = $form->createElement('radio','is_audit_enabled',null,get_lang('AllowedCourseAdminAudit'),1);
$group[] = $form->createElement('radio','is_audit_enabled',null,get_lang('AllowedSubscribeCourseOpen'),0);
$form->addGroup($group,null,get_lang('AuditPriv'),'<br/>',false);*/
$form->addElement ( 'hidden', 'is_audit_enabled' );
$values ['is_audit_enabled'] = 0;

//TODO:liyu 访问权限
$group = array ();
$group [] = $form->createElement ( 'radio', 'visibility', null, get_lang ( 'Private' ), COURSE_VISIBILITY_REGISTERED );
$group [] = $form->createElement ( 'radio', 'visibility', null, get_lang ( 'CourseVisibilityClosed' ), COURSE_VISIBILITY_CLOSED );
$form->addGroup ( $group, 'visibility', get_lang ( "CourseAccess" ), '&nbsp;&nbsp;&nbsp;&nbsp;', false );

//语言
$form->addElement ( 'hidden', 'course_language', 'simpl_chinese' );
$form->addElement ( 'hidden', 'is_shown', '0' );
$form->addElement ( 'hidden', 'pass_condition', '2' );
$form->addElement ( 'hidden', 'org_id', '-1' );
$form->addElement ( 'hidden', 'old_is_audit_enabled', $course ['is_audit_enabled'] );

//V2.1 讲师
$form->add_textfield ( 'tutor_name', get_lang ( 'CourseTitular' ), FALSE, array ('style' => "width:50%", 'class' => 'inputText' ) );
$form->addRule ( 'tutor_name', get_lang ( 'Max' ), 'maxlength', 100 );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'onclick="valide()" class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;' );

// Set some default values
$course_db_name = $course ['db_name'];
$form->setDefaults ( $course );
//echo "<pre>";
//var_dump($course);
//liyu
$form->addRule ( 'visual_code', get_lang ( 'CourseCodeMustBeAlphanumeric' ), 'alphanumeric' );
$form->addRule ( 'visual_code', get_lang ( 'CourseCodeNopunctuation' ), 'nopunctuation' );

Display::setTemplateBorder ( $form, '100%' );

// Validate form
if ($form->validate ()) {
	//$course = $form->exportValues();
	$course = $form->getSubmitValues ();
	//var_dump($course);exit;
	$course_code = $course ['code'];
	$course_info = CourseManager::get_course_information ( $course_code );
	if (! can_do_my_bo ( $course_info ['created_user'] )) {
		Display::display_msgbox('对不起,你没有操作的权限!','main/admin/course/course_list.php','warning');
	}
	
	$visual_code = $course ['visual_code'];
	
	//讲师
	$tutor_id = $course ['tutorId'] ['TO_ID'];
	$tutor_name = $course ['tutorId'] ['TO_NAME'];
	$tutor_name = $course ['tutor_name'];
	
	//课程管理员
	$teachers = $course ['course_admin'];
        
	$course_managers = explode ( ',', $teachers );
	
	$nodeId=$course['nodeId'];  //自定义编号
	$title = $course ['title'];
	$category_code = $course ['category_code'];
	//$category_code=$course['categoryCode']['TO_ID_CRS_CATE'];
	

	$course_language = $course ['course_language'];
	$visibility = $course ['visibility'];
	$subscribe = $course ['subscribe'];
	$unsubscribe = $course ['unsubscribe'];
	
	$credit = $course ['credit']; //学分
	$credit_hours = $course ['credit_hours']; //学时
	$default_learing_days = $course ["default_learing_days"]; //选修学习默认天数
	$is_free = $course ['property'] ['is_free']; //是否免费课程
	$fee = $course ['property'] ['payment']; //价格
	if ($is_free) $fee = '0.00';
	if (floatval ( $fee ) == 0) $is_free = 1;
	$is_audit_enabled = $course ['is_audit_enabled'];
	$is_subscribe_enabled = $course ['is_subscribe_enabled'];
	$is_shown = $course ['is_shown'];
	$pass_condition = $course ['pass_condition'];
	$org_id = (empty ( $course ['is_shared'] ) ? $course ['org_id'] : - 1);
	
	$sql_data = array ('course_language' => $course_language, 
			'title' => $title,
			'nodeId'=>$nodeId,
			'category_code' => $category_code, 
			'tutor_name' => $tutor_name, 
			'visual_code' => $visual_code, 
			'visibility' => $visibility, 
			'subscribe' => $subscribe, 
			'unsubscribe' => $unsubscribe, 
			'credit' => $credit, 
			'credit_hours' => $credit_hours, 
			'is_free' => $is_free, 
			'fee' => $fee, 
			'is_audit_enabled' => $is_audit_enabled, 
			'is_subscribe_enabled' => $is_subscribe_enabled, 
			'is_shown' => $is_shown, 
			'pass_condition' => $pass_condition, 
			'org_id' => $org_id, 
			'default_learing_days' => $default_learing_days );
	$sql = Database::sql_update ( $course_table, $sql_data, "code=" . Database::escape ( $course_code ) );
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	/*	$sql_query = "SELECT t2.user_id FROM $course_user_table AS t1 t1.`course_code` = ".Database::escape($course_code)
	." AND t1.status=".COURSEMANAGER." AND (is_course_admin=1 OR tutor_id=1)";
	$tutor_and_admin=Database::get_into_array($sql_query);*/
	
	//V2.4 处理课程管理员
	//将课程管理员全部设置为普通用户
	$sql = "UPDATE $course_user_table SET is_course_admin=0,tutor_id=0  WHERE course_code = " . Database::escape ( $course_code ) . " AND is_course_admin =1 ";
	api_sql_query ( $sql, __FILE__, __LINE__ );
        var_dump($course_managers);
	foreach ( $course_managers as $admin_id ) {
		if ($admin_id) {
			if (CourseManager::is_user_subscribe ( $course_code, $admin_id )) {
				$sql_data = array ('status' => COURSEMANAGER, 'is_course_admin' => '1','tutor_id' => '1',  );
				$sqlwhere = "course_code = " . Database::escape ( $course_code ) . " AND user_id = " . Database::escape ( $admin_id );
				$sql = Database::sql_update ( $course_user_table, $sql_data, $sqlwhere );
                                echo '<hr>'.$sql;
				api_sql_query ( $sql, __FILE__, __LINE__ );
			} else {
				$sql_data = array ('course_code' => $course_code, 
						'user_id' => $admin_id, 
						'status' => COURSEMANAGER, 
						'role' => get_lang ( "CourseAdmin" ), 
						'is_course_admin' => '1', 
						'tutor_id' => '1', 
						'is_required_course' => 1, 
						'begin_date' => date ( "Y-m-d" ), 
						'finish_date' => date ( "Y-m-d", strtotime ( "+ $firstExpirationDelay seconds" ) ) );
				$sql = Database::sql_insert ( $course_user_table, $sql_data );
                                
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
		}
	}
	
	//处理课程选课审批
	if ($course ['old_is_audit_enabled'] != $course ['is_audit_enabled']) {
		$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		$sql = "SELECT user_id FROM " . $table_course_subscribe_requisition . " WHERE course_code='" . escape ( $course_code ) . "' AND audit_result<>1";
		//echo $sql;exit;
		$all_req_users = Database::get_into_array ( $sql );
		switch ($is_audit_enabled) {
			case 1 : //课程管理员审批
				$cousre_admin_info = CourseManager::get_course_admin ( $course_code );
				$course_admin_id = $cousre_admin_info ['user_id'];
				$sql = "UPDATE " . $table_course_subscribe_requisition . " SET audit_user='" . $course_admin_id . "' WHERE course_code='" . $course_code . "' AND " . Database::create_in ( $all_req_users, "user_id" );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				break;
			case 2 : //部门经理审批
				foreach ( $all_req_users as $req_user ) {
					$dept_admin = UserManager::get_user_dept_admin ( $req_user );
					$sql = "UPDATE " . $table_course_subscribe_requisition . " SET audit_user='" . $dept_admin . "' WHERE course_code='" . $course_code . "' AND  user_id='" . $req_user . "'";
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
				break;
			case 3 : //培训管理员审批
				$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
				foreach ( $all_req_users as $req_user ) {
					$sql = "SELECT org_admin FROM " . $view_user_dept . " WHERE user_id='" . escape ( $req_user ) . "'";
					$org_admin = Database::get_scalar_value ( $sql );
					$sql = "UPDATE " . $table_course_subscribe_requisition . " SET audit_user='" . $org_admin . "' WHERE course_code='" . $course_code . "' AND user_id='" . $req_user . "'";
					api_sql_query ( $sql, __FILE__, __LINE__ );
				}
				break;
			case 0 :
				
				break;
		}
	}
	
	$log_msg = get_lang ( 'EditCourseInfo' ) . "code=" . $course_code;
	api_logging ( $log_msg, 'COURSE', 'EditCourseInfo' );
	
	$redirect_url = 'course_list.php';
	tb_close ( $redirect_url );
}

//$htmlHeadXtra[]=$ams->getElementJs(false);
Display::display_header ( $tool_name, FALSE );

$form->display ();

Display::display_footer ();
