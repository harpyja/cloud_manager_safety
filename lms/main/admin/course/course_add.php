<?php
/**
 ==============================================================================

 ==============================================================================
 */

$language_file = array ('course_info', 'admin', 'create_course', 'course_description' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'add_course.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "admin/admin.lib.inc.php");

//课程默认过期时间
$firstExpirationDelay = 31536000; // <- 86400*365    // 60*60*24 = 1 jour = 86400


$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_REQUISITION );
$tool_name = get_lang ( 'AddCourse' );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		/*$("#upload_max_filesize").parent().append("<div class=\'onShow\'>' . get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( 0 ) . 'M</div>");*/
		$("#visual_code").parent().append("<div class=\'onShow\'>' . get_lang ( 'AddCourseCodeTip' ) . '</div>");
		$("#credit").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditTip' ) . '</div>");
		$("#credit_hours").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditHoursTip' ) . '</div>");
		
		$("#org_id").attr("disabled","true");
		//$("#payment").attr("disabled","true");
		//$("#payment").hide();
		
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

$htmlHeadXtra [] = '<script language="JavaScript" type="text/JavaScript">
function fee_switch_radio_button(form, input){
	var NodeList = document.getElementsByTagName("input");
	for(var i=0; i< NodeList.length; i++){
		if(NodeList.item(i).name=="fee[is_free]"  && NodeList.item(i).value=="0"){
			NodeList.item(i).checked=true;
			document.getElementById("is_audit_enabled").checked=false;
		}
	}
}
</script>';

function credit_range_check($inputValue) {
	return (intval ( $inputValue ) > 0);
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

function upload_max_filesize_check($inputValue) {
	return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= get_upload_max_filesize ( 0 ));
}

$deptObj = new DeptManager ();
$objCrsMng = new CourseManager ();

$form = new FormValidator ( 'update_course' );
//$form->addElement ( 'header', 'header', get_lang ( 'AddCourse' ) );
//编号
$form->add_textfield ( 'visual_code', get_lang ( 'CourseCode' ), true, array ('id' => 'visual_code', 'style' => "width:40%", 'class' => 'inputText', 'maxlength' => 20 ,"readonly" => "true" ) );
$form->applyFilter ( 'visual_code', 'strtoupper' );
$form->addRule ( 'visual_code', get_lang ( 'Max' ), 'maxlength', 30 );
$form->addRule ( 'visual_code', get_lang ( 'CourseCodeMustBeAlphanumeric' ), 'alphanumeric' ); //只允许字母和数字
$values ['visual_code'] = date ( 'Ym' ) . Database::get_scalar_value ( "SELECT FLOOR(100000+rand()*899999)" );

//标题
$form->add_textfield ( 'title', get_lang ( 'CourseTitle' ), true, array ('style' => "width:40%", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'Max' ), 'maxlength', 50 );

//自定义编号
$form->addElement ( 'text', 'nodeId', get_lang ( '自定义编号' ),array ('style' => "width:200px", 'class' => 'inputText' ) );
$form->addRule ( 'nodeId', get_lang ( 'Max' ), 'maxlength', 50 );


//TODO:liyu 课程管理员,应该可设置成多个
//$modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'COURSE_UPDATE', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/pop_frame.php?", 'form_name' => 'update_course', 'TO_NAME' => 'TO_NAME_ADMIN', 'TO_ID' => 'TO_ID_ADMIN' );
//$form->addElement ( 'modaldialog_select', 'courseTeachers', get_lang ( 'CourseTeachers' ), NULL, $modaldialog_select_options );
////$form->addRule ( 'courseTeachers', get_lang ( 'ThisFieldIsRequired' ), 'required' );  //设置课程管理员不能为空
//$values ['courseTeachers'] ['TO_NAME_ADMIN'] = $_user ['firstName'];
//$values ['courseTeachers'] ['TO_ID_ADMIN'] = api_get_user_id ();
$sql_admin="select `user_id`,`firstname` from `user` where `status`='10' or `status`='1'";
$result=Database::get_into_array2 ( $sql_admin, __FILE__, __LINE__ );
//var_dump($result);
$form->addElement ( 'select', 'course_admin', get_lang ( "课程管理员" ), $result, array ('id' => "user", 'style' => 'height:22px;' ) );

//学分
$form->addElement ( 'text', 'credit', get_lang ( "CourseCredit" ), array ('id' => 'credit', 'maxlength' => '3', 'style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$form->addRule ( 'credit', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'credit', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'credit', get_lang ( '' ), 'rangelength', array (1, 3 ) );
$form->addRule ( 'credit', get_lang ( 'CreditTip' ), 'callback', 'credit_range_check' );

//学时
$form->addElement ( 'text', 'credit_hours', get_lang ( "CourseCreditHours" ), array ('id' => 'credit_hours', 'maxlength' => '4', 'style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$form->addRule ( 'credit_hours', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'credit_hours', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'credit_hours', get_lang ( '' ), 'rangelength', array (1, 4 ) );
$form->addRule ( 'credit_hours', get_lang ( 'CreditHoursTip' ), 'callback', 'credit_hours_range_check' );
$values ['credit_hours'] = 1;

$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
foreach ( $category_tree as $item ) {
	$parent_cate_option [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $item ['name'];
}
//课程分类
$form->addElement ( 'select', 'category_code', get_lang ( "CourseFaculty" ), $parent_cate_option, array ('id' => "category_code", 'style' => 'height:22px;' ) );

//是否允许选修课程
/*$group = array ();
$group [] = $form->createElement ( 'radio', 'subscribe', null, get_lang ( 'Allowed' ), 1 );
$group [] = $form->createElement ( 'radio', 'subscribe', null, get_lang ( 'Denied' ), 0 );
$form->addGroup ( $group, 'subscribe', get_lang ( 'Subscription' ), null, false );*/
$form->addElement ( 'hidden', 'subscribe' );
$values ['subscribe'] = 1;

//注销课程
$form->addElement ( "hidden", "unsubscribe", "0" );
$values ['unsubscribe'] = 0;

//学员选修学习默认天数 
$form->addElement ( 'text', 'default_learing_days', get_lang ( "DefaultLearningDays" ), array ('id' => 'default_learing_days', 'maxlength' => '4', 'style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$form->addRule ( 'default_learing_days', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'default_learing_days', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'default_learing_days', get_lang ( '' ), 'rangelength', array (1, 4 ) );

//允许课程管理员注册,注销课程用户
$form->addElement ( 'checkbox', 'is_subscribe_enabled', get_lang ( "SubscribePriv" ), get_lang ( 'AllowedCourseAdminSubscribeUser' ), array ("id" => "is_subscribe_enabled" ) );
$values ['is_subscribe_enabled'] = 1;

//课程访问权限
$group = array ();
$group [] = $form->createElement ( 'radio', 'visibility', null, get_lang ( 'Private' ), COURSE_VISIBILITY_REGISTERED );
$group [] = $form->createElement ( 'radio', 'visibility', null, get_lang ( 'CourseVisibilityClosed' ), COURSE_VISIBILITY_CLOSED );
$form->addGroup ( $group, 'visibility', get_lang ( 'CourseAccess' ), '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values ['visibility'] = COURSE_VISIBILITY_REGISTERED;

//允许审批,有审批选课申请的
//group = array ();
//$group [] = $form->createElement ( 'radio', 'is_audit_enabled', null, get_lang ( 'AllowedOrgAdminAudit' ), 3 ); //只允许 培训管理员/平台管理员 审批
//$group [] = $form->createElement ( 'radio', 'is_audit_enabled', null, get_lang ( 'AllowedDeptAdminAudit' ), 2 ); //只允许 部门经理 审批
//$group [] = $form->createElement ( 'radio', 'is_audit_enabled', null, get_lang ( 'AllowedCourseAdminAudit' ), 1 ); //只允许 课程管理员 审批
//$group [] = $form->createElement ( 'radio', 'is_audit_enabled', null, get_lang ( 'AllowedSubscribeCourseOpen' ), 0 ); //不需要审批
//$form->addGroup ( $group, null, get_lang ( 'AuditPriv' ), '<br/>', false );
$form->addElement ( 'hidden', 'is_audit_enabled', '0' );

$form->addElement ( 'hidden', 'course_language', 'simpl_chinese' );
$form->addElement ( 'hidden', 'is_shown', '0' );
$form->addElement ( 'hidden', 'pass_condition', '2' );
$form->addElement ( 'hidden', 'org_id', '-1' );

//讲师
$form->add_textfield ( 'tutor_name', get_lang ( 'CourseTitular' ), false, array ('style' => "width:50%", 'class' => 'inputText' ) );
$form->addRule ( 'tutor_name', get_lang ( 'Max' ), 'maxlength', 100 );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

reset ( $teachers );
$values ['course_teachers'] = key ( $teachers );
$values ['credit'] = 2;
//$values['credit_hours']=1;
$values ["default_learing_days"] = DEFAULT_LEARNING_DAYS;

$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );

if ($form->validate ()) {
	//设置内存及执行时间
	if (function_exists ( 'ini_set' )) {
		ini_set ( 'memory_limit', '256M' );
		ini_set ( 'max_execution_time', 1800 ); //设置执行时间
	}
	
	$course = $form->getSubmitValues (); //表单获得数据
	//var_dump($course);exit;
	$code = $course ['visual_code'];
	$title = trim ( $course ['title'] );
	$visibility = $course ['visibility'];
	
	//讲师
	$tutor_name = trim ( $course ['tutor_name'] );
	
	//liyu:课程管理员
	$course_admin = $course ['course_admin'];
	$category = $course ['category_code'];
	//$category = $course['categoryCode']['TO_ID_CRS_CATE'];


	$course_language = $course ['course_language'];
	$disk_quota = empty ( $course ['disk_quota'] ) ? (100 * 1048576) : (intval ( $course ['disk_quota'] )) * 1048576;
	
	$start_date = date ( 'Y-m-d H:i', time () + 300 );
	$expiration_date = date ( 'Y-m-d H:i', strtotime ( "+ " . ($firstExpirationDelay * 10) . " seconds" ) );
	
	$subscribe = $course ['subscribe'];
	//$unsubscribe=$course['unsubscribe'];
	$unsubscribe = 0; //不允许注销课程
	
  $nodeId=$course['nodeId']; //自定义编号
	$credit = $course ['credit']; //学分
	$credit_hours = $course ['credit_hours'];
	$default_learing_days = $course ["default_learing_days"]; //选修学习默认天数
	$is_free = $course ['fee'] ['is_free']; //是否免费
	$fee = floatval ( $course ['fee'] ['payment'] ); //
	if ($is_free) $fee = '0.00';
	if (floatval ( $fee ) == 0) $is_free = 1;
	$is_audit_enabled = $course ['is_audit_enabled'];
	$is_subscribe_enabled = $course ['is_subscribe_enabled'];
	$is_shown = $course ['is_shown'];
	$pass_condition = $course ['pass_condition'];
	$org_id = (empty ( $course ['is_shared'] ) ? $course ['org_id'] : - 1);
	
	//创建课程
	//$user_info=api_get_user_info($teacher_id);
	//$tutor_name =$user_info['firstName'];
	$course_data = array (
			'course_language' => $course_language, 
				'description' => "", 
				'category_code' => $category, 
				'visibility' => $visibility, 
				'tutor_name' => $tutor_name, 
				'visual_code' => $code, 
				'disk_quota' => 0, 
				'expiration_date' => $expiration_date, 
				'start_date' => $start_date, 
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
           			'nodeId'=>$nodeId,
				'default_learing_days' => $default_learing_days );
	
	$result = create_course ( $code, "", api_get_user_id (), $course_admin, $title, $course_data );
	
	if ($result) {
		$log_msg = get_lang ( 'AddCourseInfo' ) . "code=" . $code;
		api_logging ( $log_msg, 'COURSE', 'AddCourseInfo' );
		tb_close ( "course_list.php" );
	}
}

Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
