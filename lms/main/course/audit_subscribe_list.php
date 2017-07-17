<?php
/**
 * 审核注册到该课程的用户
 */
$language_file = array('registration','admin','course_info');
require_once ("../../inc/global.inc.php");
$this_section = SECTION_COURSES;
api_block_anonymous_users();

require_once (api_get_path(LIBRARY_PATH)."course.lib.php");
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'dept.lib.inc.php');
require_once (api_get_path(LIBRARY_PATH).'course_class_manager.lib.php');

$table_course_subscribe_requisition=Database::get_main_table(TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION);

/*
 -----------------------------------------------------------
 Constants and variables
 -----------------------------------------------------------
 */
$currentCourseID = $_course['sysCode'];
$userId = intval(getgpc('user_id','G')); 
$code =getgpc('code','G');  
$course_code=($code);
$user_id=($userId);
$course_info=CourseManager::get_course_information($course_code);

if(isset(getgpc('action')))
{
	//是否有权限
	$sql="SELECT * FROM ".$table_course_subscribe_requisition." WHERE course_code=".Database::escape($course_code)
	." AND user_id=".Database::escape($user_id)." AND audit_user='".api_get_user_id()."'";
	if(Database::if_row_exists($sql)){
		switch(getgpc("action"))
		{
			case 'apply_audit_pass':
				$rs_no=CourseManager::apply_audit_pass($course_code,$user_id);
				if($rs_no==1) $message=get_lang('EnrollToCourseSuccessful');
				elseif($rs_no==0) $message=get_lang('ErrorContactPlatformAdmin');
				elseif($rs_no==2) $message=get_lang("UserExistInTheCourse");
				break;
			case 'apply_audit_not_pass':
				CourseManager::apply_audit_not_pass($course_code,$user_id);
				$message=get_lang('OperationSuccess');
				break;
			case 'apply_del':
				$result=CourseManager::apply_audit_del($course_code,$user_id);
				$message=($result?get_lang('RequisitionDeleted'):get_lang('CannotRequisitionDeleted'));
				break;
		}
	}
}


$htmlHeadXtra[]=Display::display_thickbox();

$nameTools=get_lang('AuditSubscribeToCourseUsers');
//$interbreadcrumb[] = array ("url" => "audit_subscribe_list.php", "name" => $nameTools);
Display::display_header(NULL);

if( isset($message))
{
	Display::display_normal_message($message);
}



//查询过滤
$form = new FormValidator('search_user', 'get','','',null,false);
$renderer = $form->defaultRenderer();
$renderer->setElementTemplate('<span>{element}</span> ');
$keyword_tip=get_lang('LoginName')."/".get_lang("FirstName")."/".get_lang("OfficialCode");
$form->addElement('text','keyword',get_lang('keyword'),array('style'=>"width:20%",'class'=>'inputText','title'=>$keyword_tip));

$form->addElement('submit', 'submit', get_lang('Filter'), 'class="inputSubmit"');

$form->setDefaults($defaults);
$form->display();

display_subscribe_course_audit_list(api_get_user_id());


//liyu: 审批申请注册的到某门课程的学生用户列表 
function display_subscribe_course_audit_list($user_id){
	global $_user;

	$tbl_user  = Database::get_main_table(VIEW_USER_DEPT);
	$table_course_subscribe_requisition=Database::get_main_table(TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION);
	$table_course = Database::get_main_table(TABLE_MAIN_COURSE);


	$sql="SELECT t3.firstname,t3.dept_id,t3.org_name,t1.creation_date,t1.audit_date,t3.user_id,t1.audit_result,
		t2.code,t1.status,t1.class_id,t2.title,t2.visibility FROM ".$table_course_subscribe_requisition." t1 , ".$table_course
	." t2 , ".$tbl_user." t3 WHERE  t1.course_code=t2.code AND t3.user_id=t1.user_id AND t1.audit_user="
	.Database::escape($user_id);
        $keyword= getgpc('keyword','G'); 
	if (is_not_blank ($keyword))
	{
		$sql .= " AND t3.firstname LIKE '%".escape($keyword,TRUE)."%' ";
	}

	$sql .=" ORDER BY creation_date DESC";

	//echo $sql;
	$sql_result = api_sql_query($sql,__FILE__,__LINE__);
	$table_header[] = array(get_lang('CourseTitle'),true);
	//$table_header[] = array(get_lang('Fac'),true);
	//$table_header[] = array(get_lang('Code'),true);
	//$table_header[] = array(get_lang('Professors'),true);
	$table_header[] = array(get_lang('AppliedUser'),true);
	$table_header[] = array(get_lang('InOrg'),true);
	//$table_header[] = array(get_lang('InDept'),true);
	//$table_header[] = array(get_lang('SubscribeCourseClass'),true);
	$table_header[] = array(get_lang('UserType'),true);
	$table_header[] = array(get_lang('CourseReqTime'),true);
	$table_header[] = array(get_lang('AuditReqTime'),true);
	//$table_header[] = array(get_lang('CourseReqStatus'),false);
	$table_header[] = array(get_lang('Actions'),false);
	$table_data = array();
	$objDept=new DeptManager();
	while( $data = Database::fetch_array($sql_result,"ASSOC")){
		$row = array();
			
		//$row[] = $work->title;
		$data['visibility'] == COURSE_VISIBILITY_CLOSED?	$class='class="invisible"':	$class='';
		if($data['visibility']!=COURSE_VISIBILITY_CLOSED){
			$row[] = "<span $class>".'<a class="thickbox" href="../subscribe.php?action=info&course_code='.$data['code'].'&KeepThis=true&TB_iframe=true&height=380&width=750&modal=">'.$data['title']."</a></span>";
		}else{
			$row[] = "<span $class>".$data['title']."(".get_lang("Closed").")</span>";
		}
		//$row[] = $work->name;
		//$row[] = $work->code;
		//$row[] = $work->tutor_name;
		//$row[] = $work->course_language;

		//$row[]='<a href="userInfo.php?origin=&uInfo='.$work->user_id.'">'.$work->firstname.'</a>';
		$row[]='<a class="thickbox" href="'.api_get_path(WEB_CODE_PATH).'user_info.php?uid='.$data['user_id'].'&height=320&width=700&TB_iframe=true&KeepThis=true&modal=">'.$data['firstname'].'</a>';
		$row[]=$data['org_name'];
		//$objDept->dept_path="";
		//$row[]=$objDept->get_dept_path($work->dept_id,FALSE);

		//$row[]=(($work->status==STUDENT)?get_lang('Student'):get_lang('Teacher'));
		$row[]=$data['creation_date'];
		$row[]=$data['audit_date'];
			
		if($data['audit_result']==AUDIT_CRS_SUBSCRITION_INIT){
			$req_status=get_lang('CourseStatus0');
			$row[] = get_lang('CourseStatus0');//.'&nbsp;'.Display::return_icon('wrong.gif', $req_status);
		}
		else if($data['audit_result']==AUDIT_CRS_SUBSCRITION_PASS) {
			$req_status=get_lang('CourseStatus1');
			$row[] = get_lang('CourseStatus1');//.'&nbsp;'.Display::return_icon('right.gif', $req_status);
		}
		else if($data['audit_result']==AUDIT_CRS_SUBSCRITION_REFUSE) {
			$req_status=get_lang('CourseStatus2');
			$row[] = get_lang('CourseStatus2');//.'&nbsp;'.Display::return_icon('wrong.gif', $req_status);
		}
			
			
		$action_html='';
		if($data['audit_result']==AUDIT_CRS_SUBSCRITION_INIT){
			$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_not_pass&code='.$data['code'].'&user_id='
			.$data['user_id'].'"  onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang('ConfirmYourChoice'), ENT_NOQUOTES, SYSTEM_CHARSET))."'"
			.')) return false;">'. Display::return_icon('wrong.gif', get_lang('AuditNotPassed')) . '</a>&nbsp;&nbsp;';

			$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_pass&code='.$data['code']
			.'&user_id='.$data['user_id'].'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang('ConfirmYourChoice'), ENT_NOQUOTES, SYSTEM_CHARSET))."'"
			.')) return false;">'. Display::return_icon('right.gif', get_lang('AuditPassed')) . '</a>&nbsp;&nbsp;';

		}else{
			/*if($work->audit_result==2){
				$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_pass&code='.$work->code.'&user_id='.$work->user_id.'">'
				.Display::return_icon('right.gif', get_lang('AuditPassed')) . '</a>&nbsp;&nbsp;';
				}
				if($work->audit_result==1){
				$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_not_pass&code='.$work->code.'&user_id='.$work->user_id.'">'
				.Display::return_icon('wrong.gif', get_lang('AuditNotPassed')) . '</a>&nbsp;&nbsp;';
				}*/

			//$action_html .=  Display::return_icon('delete_na.gif', get_lang('Delete'));
		}
		$action_html .= '<a href="audit_subscribe_list.php?action=apply_del&code='.$data['code']
		.'&user_id='.$data['user_id'].'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang('ConfirmYourChoice'), ENT_NOQUOTES, SYSTEM_CHARSET))."'"
		.')) return false;">' . Display::return_icon('delete.gif', get_lang('Delete')) . '</a>';
			
		$row[] =$action_html;
			
		//}
		$table_data[] = $row;
	}
	$parameters['keyword'] = getgpc('keyword','G');

	Display::display_sortable_table($table_header,$table_data,$sorting_options,null,$parameters,null,'bottom');
}


?>
