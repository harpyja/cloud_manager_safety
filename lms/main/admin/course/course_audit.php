<?php
//待审核的课程
$language_file =array("create_course",'admin','course_description');

$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;

//api_protect_admin_script ();
$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];
//$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);

$interbreadcrumb[] = array ("url" => api_get_path(WEB_ADMIN_PATH).'index.php', "name" => get_lang('PlatformAdmin'));
//$interbreadcrumb[] = array ("url" => "course_audit_applied_courses.php?action=list", "name" => get_lang('CourseRequistionListAll'));
$tool_name=get_lang('CourseRequistionAudit');

//课程默认过期时间
$firstExpirationDelay   = 31536000; // <- 86400*365    // 60*60*24 = 1 jour = 86400

include_once (api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'debug.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
require_once (api_get_path(SYS_CODE_PATH)."admin/admin.lib.inc.php");

$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$table_course_requisition = Database :: get_main_table(TABLE_MAIN_COURSE_REQUISITION);

$htmlHeadXtra[]=Display::display_thickbox();

$code = $_REQUEST['code'];
$id= intval(getgpc("id"));
$action=(isset($_REQUEST['action'])?  getgpc("action"):'');
$user_id = intval(api_get_user_id());



$sql="SELECT t1.id,t1.code,t1.title,t1.category_code,t1.tutor_name,t1.course_language,
		t1.status,t1.creation_date,t1.description,t1.visual_code,t1.db_name,t1.directory,t1.req_user_id,
		t2.name FROM ".$table_course_requisition ." t1 left join "	.$table_course_category
." t2 on t1.category_code=t2.id where t1.id=".Database::escape(intval(getgpc('id')));
//echo $sql;

$sql_result = api_sql_query($sql,__FILE__,__LINE__);
if($data=Database::fetch_array($sql_result,'ASSOC')){
	if($data['status']==AUDIT_CRS_CREATION_APPLY_INIT) $req_status=get_lang('CourseStatus0');
	else if($data['status']==AUDIT_CRS_CREATION_APPLY_PASS) $req_status=get_lang('CourseStatus1');
	else if($data['status']==AUDIT_CRS_CREATION_APPLY_REFUSE) $req_status=get_lang('CourseStatus2');

	$form = new FormValidator('audit_course','POST',$_SERVER['PHP_SELF']);
	$form->addElement('hidden','action','apply_audit_save');
	$form->addElement('hidden','id',  intval($data['id']));
	$form->addElement('hidden','code',$data['code']);

	$form->addElement('header', 'header', get_lang('Audit'));

	$is_allowed_audit_pass=true;
	$sql="SELECT COUNT(*) FROM ".$table_course_requisition." t1 LEFT JOIN ".$table_course
	." t2 ON t1.id=t2.apply_id WHERE t2.apply_id>0 AND t2.code IS NOT NULL AND t2.code=".Database::escape($code);
	if(Database::get_scalar_value($sql)>0){
		$is_allowed_audit_pass=false;
	}

	//是否同意
	$group = array();
	if($is_allowed_audit_pass){
		$group[] =& HTML_QuickForm::createElement('radio', 'audit_result',null,get_lang('AgreementPassed'),1);
	}else{ //已存在了这门课程, 不能再选择同意
		$group[] =& HTML_QuickForm::createElement('radio', 'audit_result',null,get_lang('AgreementPassed'),1,array('disabled'=>'true'));
	}
	$group[] =& HTML_QuickForm::createElement('radio', 'audit_result',null,get_lang('AgreementNotPassed'),0);
	$form->addGroup($group, 'audit_rs', get_lang('AduitResult'), '&nbsp;');

	if (api_get_setting('html_editor')=='simple') {
		$form->addElement('textarea', 'description', get_lang('Description'),array('cols'=>50,'rows'=>8));
	} else {
		$fck_attribute['Width'] = '100%';
		$fck_attribute['Height'] = '230';
		$fck_attribute['ToolbarSet'] = 'Comment';
		$form->add_html_editor('audit_desc', get_lang('Description'),false);
	}

	$group = array ();
	$group[] = $form->createElement('submit', null, get_lang('Ok'), 'class="inputSubmit"');
	$group[] =$form->createElement('style_button', 'cancle',null,array('type'=>'button','class'=>"cancel",
						'value'=>get_lang('Cancel'),'onclick'=>'javascript:self.parent.tb_remove();'));
	$form->addGroup($group, 'submit', '&nbsp;', null, false);

	$defaults['audit_rs']['audit_result'] = 0;
	$form->setDefaults($defaults);

	$form->add_progress_bar();
	Display::setTemplateBorder($form, '98%');

	if($form->validate() && isset($_REQUEST['action']) && $action=='apply_audit_save'){
		$form->freeze();
		$form_data = $form->exportValues();
		//var_dump($form_data);exit;
		$audit_result=$form_data['audit_rs']['audit_result'];
		$audit_desc=$form_data['audit_desc'];
		$id=  intval($form_data['id']);
		$code=$form_data['code'];
		if($audit_result=="0"){ //不通过审核
			$sql_data=array("status"=>AUDIT_CRS_CREATION_APPLY_REFUSE,"agreement_date"=>date("Y-m-d H:i:s"),"audit_desc"=>$audit_desc);
			$sql=Database::sql_update($table_course_requisition,$sql_data,"id=".Database::escape($id));
			api_sql_query($sql,__FILE__,__LINE__);

			$log_msg=get_lang('AppliedCourseAuditNotPass')."applied_id=".  intval(getgpc('id'));
			api_logging($log_msg,'APPLIED_COURSE','AppliedCourseAuditNotPass');

			$url=api_get_path(WEB_CODE_PATH)."admin/course/course_audit_applied_courses.php?action=list&message=".urlencode(get_lang('OperationSuccess'));
			api_redirect($url);
		}
		if($audit_result=="1"){//通过审核	
			//更改审批状态
			$sql_data=array("status"=>AUDIT_CRS_CREATION_APPLY_PASS,"agreement_date"=>date("Y-m-d H:i:s"),"audit_desc"=>$audit_desc);
			$sql=Database::sql_update($table_course_requisition,$sql_data,"id=".Database::escape($id));
			api_sql_query($sql,__FILE__,__LINE__);

			//将其它教师相同课程编号的申请更新为拒绝状态
			$sql_data=array("status"=>AUDIT_CRS_CREATION_APPLY_REFUSE,"agreement_date"=>date("Y-m-d H:i:s"),"audit_desc"=>get_lang('CreateCrsRefuseDefaultComment'));
			$sqlwhere="id<>'".Database::escape_string($id)."' AND code=".Database::escape($form_data['code']);
			$sql=Database::sql_update($table_course_requisition,$sql_data,$sqlwhere);
			api_sql_query($sql,__FILE__,__LINE__);

			//如果
			$sql="SELECT * FROM ".$table_course." WHERE code='".$code."'";
			if(Database::if_row_exists($sql)){
				api_redirect("course_list.php");
			}else{
				if(_check_org_course_quota()==FALSE){
					api_redirect('course_list.php?action=show_note&note='.urlencode(get_lang('CourseCountExcess')));
				}

				$sql="SELECT * FROM ".$table_course_requisition ." t1 where t1.id=".Database::escape($id);
				$res=api_sql_query($sql,__FILE__,__LINE__);
				$req_info=Database::fetch_array($res,"ASSOC");

				if(empty($req_info["attr"])){
					$start_date=date('Y-m-d H:i:s',strtotime("+ 180 seconds"));
					$expiration_date = date('Y-m-d H:i:s',strtotime("+ $firstExpirationDelay seconds"));
					$credit=0;
					$is_free=1;
					$fee=0;
					$is_audit_enabled=1;
					$credit_hours=10;
					$disk_quota=0;
					$visibility=COURSE_VISIBILITY_REGISTERED;
					$subscribe=1;
					$unsubscribe=0;
					$is_subscribe_enabled=1;
					$pass_condition=1;
					$is_shown=0;
					$tutor_id=$course_admin=$req_info["req_user_id"];
				}else{
					$attr=unserialize($req_info["attr"]);
					$start_date=$attr["start_date"];
					$expiration_date = $attr["expiration_date"];
					$credit=$attr["credit"];
					$is_free=$attr["is_free"];
					$fee=$attr['fee'];
					$credit_hours=$attr["credit_hours"];
					$disk_quota=0;
					$visibility=$attr["visibility"];
					$subscribe=$attr["subscribe"];
					$unsubscribe=$attr["unsubscribe"];
					$is_audit_enabled=$attr["is_audit_enabled"];
					$is_subscribe_enabled=$attr["is_subscribe_enabled"];
					$pass_condition=$attr["pass_condition"];
					$is_shown=$attr["is_shown"];
					$tutor_id=$attr["tutor_id"];
					$course_admin=$attr["course_admin"];
				}
				$title=$req_info["title"];
				$org_id=$req_info["org_id"];
				$category_code=$req_info["category_code"];
				$tutor_name=$req_info["tutor_name"];

				$course_data=array('course_language'=>'simpl_chinese','description'=>"",
					'category_code'=>$category_code,	'visibility'=>$visibility,
				 	'disk_quota'=>$disk_quota,'tutor_name'=>$tutor_name,
					'expiration_date'=>$expiration_date,'start_date'=>$start_date,
					'subscribe'=>$subscribe,'unsubscribe'=>$unsubscribe,
					'credit'=>$credit,'credit_hours'=>$credit_hours,
					'is_free'=>$is_free, 'fee'=>$fee,'is_audit_enabled'=>$is_audit_enabled,
					'is_subscribe_enabled'=>$is_subscribe_enabled,'pass_condition'=>$pass_condition,
					'is_shown'=>$is_shown,"org_id"=>$org_id);
				$result=create_course($code,$code,$tutor_id,$course_admin,$title,$course_data);

				$sql = "UPDATE $table_course SET apply_id=".Database::escape($id)." WHERE code = '".$code."'";
				api_sql_query($sql,__FILE__,__LINE__);

				$log_msg=get_lang('AppliedCourseAuditPass')."applied_id=".getgpc('id').",course_code=".$code;
				api_logging($log_msg,'APPLIED_COURSE','AppliedCourseAuditPass');

				$redirect_url=api_get_path(WEB_CODE_PATH).'admin/course/course_list.php?message='.urlencode(get_lang('RequisitionSubmittingSuccess'));
				//api_redirect($url);
				echo '<script>self.parent.location.href="'.$redirect_url.'";self.parent.tb_remove();</script>';exit;
			}
		}

	}

	$interbreadcrumb[] = array ("url" => "course_audit_applied_courses.php?action=list", "name" => get_lang('CourseRequistionAuditList'));
	Display::display_header ( $tool_name ,FALSE);
	$form->display();
}

Display::display_footer();