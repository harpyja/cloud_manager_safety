<?php
//header("Location: portal/qh/user_register.php");exit;
//$language_file = "registration";
$language_file = array ("registration", "admin","index" );
include_once (dirname(__FILE__)."/main/inc/global.inc.php");
//header("Location: main/auth/inscription.php");

require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Display.php';
require_once (api_get_path ( CONFIGURATION_PATH ) . 'profile.conf.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'smsmanager.lib.php');

//$htmlHeadXtra[]='<script type="text/javascript" src="'.api_get_path(WEB_JS_PATH).'commons.js"></script>';
$htmlHeadXtra[]='<script src="'.api_get_path ( WEB_JS_PATH ).'jquery-latest.js"
	type="text/javascript"></script>';
$htmlHeadXtra[] = '
<script language="JavaScript" type="text/JavaScript">
	$(document).ready( function() {
		var credential_type_val=$("#credential_type").val();
		//alert(credential_type_val);
		if(credential_type_val=="0")
			$("tr.containerBody:eq(5)").hide();
		
		if($("#credential_no") && $("#credential_type"))
			$("#credential_no").attr("disabled",($("#credential_type").val()=="0"));
		
		$("#credential_type").change(function(){
			if($("#credential_type").val()=="0") {
				$("tr.containerBody:eq(5)").hide();
				$("#credential_no").attr("disabled",true);
				$("#credential_no").attr("readonly",true);
				$("#credential_no").removeClass("inputText");
				$("#credential_no").val("");					
			}
			else {
				$("tr.containerBody:eq(5)").show();
				
				$("#credential_no").attr("disabled",false);
				$("#credential_no").attr("readonly",false);
				$("#credential_no").addClass("inputText");			
			}
		});
	});


function on_load(){
	if($("#credential_no") && $("#credential_type"))
		$("#credential_no").attr("disabled",($("#credential_type").value=="0"));
}
//-->
</script>';

Display::display_reduced_header(NULL);
//echo '<body onload="javascript: on_load();"><div>';
echo '<body><div>';

if (get_setting ( 'allow_registration' ) == 'false') {
	api_not_allowed ();
}

if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'show_message') {
	if (! empty ( $_GET ['message'] )) {
		$message = urldecode ( $_GET ['message'] );
		Display::display_normal_message ( stripslashes ( $message ) );
		//echo '<br/><center><button onclick="javascript:window.close();" class="cancel">' . get_lang ( 'Close' ) . '</button></center>';
		echo '<br/><center><button class="cancel" onclick="javascript:self.parent.tb_remove();">'.get_lang('Close').'</button></center>';
		Display::display_footer();
		exit ();
	}
}


/*
 HTML_QuickForm_Controller  控制器   
 HTML_QuickForm_Page:       显示器   
 HTML_QuickForm_Action      模型器   
 */

function username_check($inputValue){
	return ereg("^[a-zA-Z0-9\-_]+$",$inputValue);
}

function reg_username_available($inputValue){
	$user_table = Database::get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT * FROM $user_table WHERE username = '".escape($inputValue)."'";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$number0 = Database::num_rows($res);
	Database::free_result($res);

	$reg_user_table = Database::get_main_table(TABLE_MAIN_USER_REGISTER);
	//$sql = "SELECT * FROM $reg_user_table WHERE reg_status=2 and username = '$username'";
	$sql = "SELECT * FROM $reg_user_table WHERE username = '".escape($inputValue)."'";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$number = Database::num_rows($res);
	
	$result=($number0==0 && $number == 0);
	return $result;

}

function reg_email_available($inputValue){
	$user_table = Database::get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT * FROM $user_table WHERE email = '$inputValue'";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$number0 = Database::num_rows($res);
	Database::free_result($res);

	$reg_user_table = Database::get_main_table(TABLE_MAIN_USER_REGISTER);
	$sql = "SELECT * FROM $reg_user_table WHERE reg_status=2 AND email = '$inputValue'";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$number1 =Database::num_rows($res);
	$result=($number0 == 0 && $number1==0);
	return $result;
}

/**
 * 基本信息页面: V显示器 
 */
class Page_Register_First extends HTML_QuickForm_Page
{
	function buildForm()
	{
		$this->_formBuilt = true;

		// 注册表单
		//$form = new FormValidator ( 'registration' );get_lang ( 'PlatformRegistration' ).
		$this->addElement ( 'header', 'header', "&nbsp;".get_lang("RegistratinStep1") ); //Registration

		//	登录名
		$this->addElement ( 'text', 'username', get_lang ( 'UserName' ), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		$this->addRule ( 'username', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$this->addRule('username', get_lang('UsernameWrong'), 'callback', 'username_check');
		$this->addRule('username', get_lang('UserTaken'), 'callback', 'reg_username_available');
		//$this->addRule ( 'username', get_lang ( 'UsernameWrong' ), 'username' );
		//$this->addRule ( 'username', get_lang ( 'UserTaken' ), 'reg_username_available' );

		//	密码
		$this->addElement ( 'password', 'pass1', get_lang ( 'Pass' ), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		$this->addElement ( 'password', 'pass2', get_lang ( 'Confirmation' ), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		$this->addRule ( 'pass1', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$this->addRule ( 'pass2', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$this->addRule ( array ('pass1', 'pass2' ), get_lang ( 'PassTwo' ), 'compare' );


		//	EMAIL
		$this->addElement ( 'text', 'email', get_lang ( 'Email' ), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		$this->applyFilter('email', 'trim');
		$this->addRule ( 'email', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$this->addRule ( 'email', get_lang ( 'EmailWrong' ), 'email' );
		$this->addRule ('email', get_lang('EmailTaken'), 'callback', 'reg_email_available');


		//	用户类型
		/*if (get_setting ( 'allow_registration_as_teacher' ) == 'true') {
			$group = array ();
			$group [] = $this->createElement ( 'radio', 'status', null, get_lang ( 'RegStudent' ), STUDENT );
			$group [] = $this->createElement ( 'radio', 'status', null, get_lang ( 'RegAdmin' ), COURSEMANAGER );
			$this->addGroup ( $group, 'status', get_lang ( 'UserType' ), null, false );
			}*/

		//$this->addElement ( 'submit', 'submit', get_lang ( 'Next' ), 'class="inputSubmit"' );
		$this->addElement ( 'html', '<tr class="containerBody"><td class="formLabel"></td><td class="formTableTd"><input type="submit" class="inputSubmit"  value="'.get_lang('Next').'" />' );
		$this->addElement('html','<button class="cancel" onclick="javascript:self.parent.tb_remove();">'.get_lang('Cancel').'</button></td></tr>');

		$this->addFormRule('checkUser');

		$this->setDefaultAction('next');
	}
}



/**
 * 详细资料页面 
 */

class Page_Register_Second extends HTML_QuickForm_Page
{
	function buildForm()
	{
		$this->_formBuilt = true;
			
		$form=& $this;

		$this->addElement ( 'header', 'header', "&nbsp;".get_lang("RegistratinStep2") ); //Registration


		// 姓名
		$form->addElement ( 'text', 'firstname', get_lang ( 'RealName' ), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		if (get_setting ( 'registration', 'firstname' ) == 'true') {
			$form->addRule ( 'firstname', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		}

		//	用户类型
		if (get_setting ( 'allow_registration_as_teacher' ) == 'true') {
			$group = array ();
			$group [] = $this->createElement ( 'radio', 'status', null, get_lang ( 'RegStudent' ), STUDENT );
			$group [] = $this->createElement ( 'radio', 'status', null, get_lang ( 'RegAdmin' ), COURSEMANAGER );
			$this->addGroup ( $group, 'status', get_lang ( 'UserType' ), null, false );
		}

		//所属部门
		if (get_setting ( 'registration', 'dept' ) == 'true') {
			$deptObj = new DeptManager ( );
			/*$dept_tree = $deptObj->get_all_dept_tree ();
			 $top_dept = $deptObj->get_top_dept ();
			 if ($top_dept) {
				if($top_dept['enabled'])
				$dept_options [$top_dept ['id']] = $top_dept ['dept_name'] . ' - ' . $top_dept ['dept_no'];
				foreach ( $dept_tree as $dept_info ) {
				if($dept_info['enabled'])
				$dept_options [$dept_info ['id']] = str_repeat ( '&nbsp;', 8 * ($dept_info ['level']) ) . $dept_info ['dept_name'] . ' - ' . $dept_info ['dept_no'];
				}
				}
				$form->addElement ( 'select', 'dept_id', get_lang ( 'UserInDept' ), $dept_options );
				*/
			$all_org=$deptObj->get_all_org();
			$orgs['']="";
			foreach($all_org as $org){
				$orgs[$org['id']]=$org['dept_name'];
			}
			$form->addElement('select','dept_id',get_lang('InOrg'),$orgs,array('id'=>"org_id",'style'=>'height:22px;width:25%'));
			$form->addRule('dept_id', get_lang('ThisFieldIsRequired'), 'required');
		}


		//性别
		$group = array ();
		$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Male' ), 1 );
		$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Female' ), 2 );
		$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Secrect' ), 0 );
		$form->addGroup ( $group, 'sex', get_lang ( 'Sex' ), "&nbsp;&nbsp;&nbsp;&nbsp;", false );


		//证件
		if (api_get_setting ( 'registration', 'credentials' ) == 'false'){
			$credentials_options[0]=get_lang ( 'None' );
		}
		$credentials_options[1]=get_lang ( 'IDCard' );
		$credentials_options[2]=get_lang ( 'WorkCard' );
		$credentials_options[3]=get_lang ( 'StudentCard' );
		$form->addElement('select','credential_type',get_lang('CredentialType'),$credentials_options,array('style'=>"width:20%","id"=>"credential_type"));//,"onchange"=>"change_credeential_state(this.value)"
		$form->addElement('text','credential_no',get_lang('CredentialNo'),array('style'=>"width:40%",'class'=>'inputText','id'=>'credential_no'));
		if (api_get_setting ( 'registration', 'credentials' ) == 'true'){
			$form->addRule ( 'credential_no', get_lang ( 'ThisFieldIsRequired' ), 'required' );

		}

		//电话号码
		$form->addElement ( 'text', 'mobile',  get_lang ( 'MobilePhone' ), array ('style' => 'width:40%', 'class' => 'inputText', 'title' => get_lang ( 'MobilePhoneTip' ) ) );
		if (api_get_setting ( 'registration', 'mobile' ) == 'true')
		$form->addRule ( 'mobile', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'mobile', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
		$form->addRule ( 'mobile', get_lang ( 'ThisFieldMinLengthIs' ) . ':11', 'minlength', 11 );

		//固定电话号码（带区号） liyu
		$form->addElement ( "text", "phone",   get_lang ( "PhoneWithAreaCode" ) , array ('style' => 'width:40%', 'class' => 'inputText', 'title' => get_lang ( 'PhoneWithAreaCodeTip' ) ) );
		if (api_get_setting ( 'registration', 'phone' ) == 'true')
		$form->addRule ( 'phone', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'phone', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

		//QQ
		$form->addElement ( "text", "qq", "QQ", array ('style' => 'width:40%', 'class' => 'inputText' ) );
		if (api_get_setting ( 'registration', 'qq' ) == 'true')
		$form->addRule ( 'qq', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'qq', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

		//MSN
		$form->addElement ( "text", "msn", "MSN", array ('style' => 'width:40%', 'class' => 'inputText' ) );
		if (api_get_setting ( 'registration', 'msn' ) == 'true')
		$form->addRule ( 'msn', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'msn', get_lang ( 'MSNWrong' ), 'email' );

		//邮编
		$form->addElement ( "text", "zip_code", get_lang("ZipCode"), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		if (api_get_setting ( 'registration', 'zip_code' ) == 'true')
		$form->addRule ( 'zip_code', get_lang ( 'ThisFieldIsRequired' ), 'required' );
		$form->addRule ( 'zip_code', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

		$form->addElement ( "text", "address", get_lang("Address"), array ('style' => 'width:40%', 'class' => 'inputText' ) );
		if (api_get_setting ( 'registration', 'address' ) == 'true')
		$form->addRule ( 'address', get_lang ( 'ThisFieldIsRequired' ), 'required' );

		//QUESTION
		/*$form->addElement('text', 'question', get_lang('RegQuestion'), array('size' => 40));
		 $form->addRule('question', get_lang('ThisFieldIsRequired'), 'required');

		 //ANSWER
		 $form->addElement('text', 'answer', get_lang('ReqAnswer'), array('size' => 40));
		 $form->addRule('answer', get_lang('ThisFieldIsRequired'), 'required');*/

		if (CHECK_PASS_EASY_TO_FIND)
		$form->addRule ( 'password1', get_lang ( 'PassTooEasy' ) . ': ' . api_generate_password (), 'callback', 'api_check_password' );

		//liyu 备注说明
		//$form->addElement('textarea', 'description', get_lang('OtherDescription'),array('cols'=>40,'rows'=>5,'class'=>'inputText'));
		/* liyu: 20091111
		 if (api_get_setting ( 'html_editor' ) == 'simple') {
		 $form->addElement ( 'textarea', 'description', get_lang ( 'OtherDescription' ), array ('cols' => 40, 'rows' => 5, 'class' => 'inputText' ) );
		 } elseif(api_get_setting ( 'html_editor' ) == 'ckeditor') {
		 $fck_attribute ['Width'] = '90%';
		 $fck_attribute ['Height'] = '180';
		 $fck_attribute ['ToolbarSet'] = 'Comment';
		 $fck_attribute["ToolbarStartExpanded"]="false";
		 $form->add_html_editor ( 'description', get_lang ( 'OtherDescription' ), false );
		 }*/


		$group = array ();
		$group [] = $form->createElement ( 'submit', $this->getButtonName('back'), get_lang ( 'Previous' ), 'class="inputSubmit"' );
		$group[] =$form->createElement('submit', $this->getButtonName('next'),get_lang ( 'Registration' ), array( 'class'=>"inputSubmit"));
		//$form->addGroup ( $group, null, '&nbsp;', null, false );
		$form->addGroup($group, 'buttons', '', '&nbsp;', false);




		$form->setDefaultAction('next');
	}
}

/**
 * 模型器
 * @author Administrator
 *
 */
class Action_Display extends HTML_QuickForm_Action_Display
{
	function _renderForm(& $page)
	{
		$renderer =& $page->defaultRenderer();

		$page->setRequiredNote('<span class="form_required">*</span> <small>'.get_lang('ThisFieldIsRequired').'</small>');
		$required_note_template = <<<EOT
	<div class="row">
		<div class="label"></div>
		<div class="formw">{requiredNote}</div>
	</div>
EOT;
		$renderer->setRequiredNoteTemplate($required_note_template);

		Display::setTemplateBorder ( $page, '98%' );


		$page->accept($renderer);
		echo $renderer->toHtml();

	}
}

class Action_Process extends HTML_QuickForm_Action
{
	function perform(&$page, $actionName)
	{
		$values = $page->controller->exportValues();
		//var_dump($values);exit;

		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		if (get_setting ( 'allow_registration_as_teacher' ) == 'false') {
			$values ['status'] = STUDENT;
		}

		if (! isset ( $values ['language'] )) {
			$values ['language'] = api_get_setting ( 'platformLanguage' );
		}

		if (get_setting ( 'registration', 'dept' ) == 'false') {
			$values['dept_id']='0';
		}

		$credential_type=$values['credential_type'];
		$credential_no=($credential_type=='0'?'':$values['credential_no']);

		// creating a new user
		$user_id = UserManager::register_user ( $values ['firstname'], $values ['status'],
		$values ['email'], $values ['username'], $values ['pass1'],
		$values ['official_code'],$values ['language'], $values ['phone'],
		$values ['mobile'], $values ['question'], $values ['answer'],
		$values ['description'], 0, $values ['qq'], $values ['msn'],
		$values['dept_id'],$values['sex'],$credential_type,
		$credential_no,$values['zip_code'],$values['address']);

		if ($user_id) {

			//如果不要审核注册用户
			if (get_setting ( 'allow_registration' ) == 'true'){
					
				//审核直接通过
				$result=UserManager::audit_reg_user_passed($user_id);
					
				//邮件提醒注册的用户
				if ($values ['email'] && is_email($values ['email']))
				{
					$emailTo = $values ['email'];
					//$emailToName = $values['firstname'] . ' ' . $values['lastname'];
					$emailToName = $values ['firstname'];
					$emailFrom = api_get_setting ( 'emailAdministrator' );
					$emailFromName = addslashes ( get_setting ( 'administratorSurname' ) . ' ' . get_setting ( 'administratorName' ) );
					$emailSubject = get_lang ( 'YourReg' );
					$emailBody = get_lang ( 'Dear' ) . ' ' . stripslashes ( $values ['firstname'] ) . "<p>" . get_lang ( 'YouAreReg' ) . ' ' . get_setting ( 'siteName' ) . ' ' . get_lang ( 'Settings' ) . "<br/>" . get_lang ( 'TheU' ) . ' : ' . $values ['username'] . "<br/>" . get_lang ( 'Pass' ) . ' : ' . stripslashes ( $values ['pass1'] ) . "<br/>" . get_setting ( 'siteName' ) . ' : ' . $_configuration ['root_web'] . "<br/><br/>" . get_lang ( 'Problem' ) . "<br/>" . get_lang ( 'Manager' ) . ' : ' . get_setting ( 'administratorSurname' ) . ' ' . get_setting ( 'administratorName' ) . "<br/>" . get_lang ( 'Phone' ) . ' : ' . get_setting ( 'administratorTelephone' ) . "<br/>" . get_lang ( 'Email' ) . ' : ' . get_setting ( 'emailAdministrator' );

					if (api_get_mail_type () != MAIL_TYPE_CLOSE) {
						if(get_setting ( 'notification_type', 'platform_email' ) == 'true'){
							if (get_setting ( 'platform_mail_type' ) == MAIL_TYPE_SMTP) {
								api_mail_html ( $emailToName, $emailTo, $emailSubject, $emailBody, $emailFromName, $emailFrom, $emailHeader );
							} elseif (get_setting ( 'platform_mail_type' ) == MAIL_TYPE_GMAIL) {
								api_customer_gmail ( $emailToName, $emailTo, $emailSubject, $emailBody );
							}
						}
					}
				}
					
				unset ( $user_id );
				$message=urlencode ( get_lang ( 'YourAccountHasRegSuccess' ) );
				api_redirect ( api_get_path ( WEB_PATH ) . "reg.php?action=show_message&message=" .$message );
			}


			// if the account has to be approved then we set the account to inactive, sent a mail to the platform admin and exit the page.
			if (get_setting ( 'allow_registration' ) == 'approval') //需要审核,则发送管理员审核用户邮件,SMS提醒
			{
				// send mail to the platform admin
				$emailTo = api_get_setting ( 'emailAdministrator' );
				$emailToName = addslashes ( get_setting ( 'administratorSurname' ) . ' ' . get_setting ( 'administratorName' ) );
				$emailFrom = api_get_setting ( 'emailAdministrator' );
				$emailFromName = addslashes ( get_setting ( 'administratorSurname' ) . ' ' . get_setting ( 'administratorName' ) );
				$emailSubject = get_lang ( 'ApprovalForNewAccount' ) . ' : ' . $values ['username'];
					
				$approval_url = api_get_path ( WEB_CODE_PATH ) . 'admin/user_list_audit.php';
				$emailBody = get_lang ( 'ApprovalForNewAccount' ) . "<br><br><table><tr><td>" . get_lang ( 'UserName' ) . ' :</td><td> ' . $values ['username'] . "</td></tr><tr><td>" . //get_lang('LastName') . ' : ' . $values['lastname'] . "\n" .
				get_lang ( 'FirstName' ) . ' : </td><td>' . $values ['firstname'] . "</td></tr><tr><td>" . //get_lang('Pass').' : '. $values['pass1']."<br>".
				get_lang ( 'Email' ) . ' : </td><td>' . $values ['email'] . "</td></tr><tr><td>" . get_lang ( 'UserType' ) . ' : </td><td>' . ($values ['status'] == "1" ? get_lang ( "Tutor" ) : get_lang ( "Student" )) . "</td></tr><tr><td>" . get_lang ( 'ManageUser' ) . ' :</td><td> <a href="' . $approval_url . '" target="_blank">' . $approval_url . '</a></td></tr></table>';

				if (api_get_mail_type () != MAIL_TYPE_CLOSE) {
					if(get_setting ( 'notification_type', 'platform_email' ) == 'true'){
						if (get_setting ( 'platform_mail_type' ) == MAIL_TYPE_SMTP) {
							api_mail_html ( $emailToName, $emailTo, $emailSubject, $emailBody, $emailFromName, $emailFrom, $emailHeader );
						} elseif (get_setting ( 'platform_mail_type' ) == MAIL_TYPE_GMAIL) {
							api_customer_gmail ( $emailToName, $emailTo, $emailSubject, $emailBody );
						}
					}
				}
					
				if (get_setting ( 'notification_type', 'platform_sms' ) == 'true') {
					$sql="SELECT user_id FROM ".$table_user." WHERE is_admin=1 ORDER BY user_id LIMIT 1";
					$admin_id=Database::get_scalar_value($sql);
					$receivers = array ($admin_id);
					$send_time = date ( 'Y-m-d H:i:s', strtotime ( "+ 1 min" ) );
					SMSManager::create_sms ( $user_id, Database::escape_string ( $emailBody ), $send_time, $receivers, false, SMS_TYPE_PLATFORM );
				}
					
				// 3. exit the page
				unset ( $user_id );
				$message=urlencode ( get_lang ( 'YourAccountHasToBeApproved2' ) ) ;
				api_redirect ( api_get_path ( WEB_PATH ) . "reg.php?action=show_message&message=" .$message );
			}
		}
	}
}

Display::display_normal_message(get_lang("RegistrationNotes"),false);

//控制器 
$register = & new HTML_QuickForm_Controller('register', true);
$register->addPage(new Page_Register_First('reg1'));
$register->addPage(new Page_Register_Second('reg2'));

$defaults ['status'] = STUDENT;
$defaults ['sex'] =0;
/*if (api_get_setting ( 'registration', 'credentials' ) == 'true'){
 $defaults['credential_type']=1;
 }*/
if (isset ( $_SESSION ["user_language_choice"] ) && $_SESSION ["user_language_choice"] != "") {
	$defaults ['language'] = $_SESSION ["user_language_choice"];
} else {
	$defaults ['language'] = api_get_setting ( 'platformLanguage' );
}
$register->setDefaults($defaults);

$register->addAction('display', new Action_Display());
$register->addAction('process', new Action_Process());
$register->run();

//var_dump($_SESSION);

Display::display_footer ();
?>
