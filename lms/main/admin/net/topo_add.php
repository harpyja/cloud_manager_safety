<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;

include ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');


require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

$table_net = Database::get_main_table ( TABLE_MAIN_NET );

//部门数据
//$deptObj = new DeptManager ();
//if (isset ( $_GET ['dept_id'] )) {
//    $one_dept_info = $deptObj->get_dept_info ( getgpc ( 'dept_id' ) );
//}


$htmlHeadXtra [] = '<script type="text/javascript">
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

$htmlHeadXtra [] = '
<script language="JavaScript" type="text/JavaScript">
function enable_expiration_date() { //v2.0
	document.user_add.radio_expiration_date[0].checked=false;
	document.user_add.radio_expiration_date[1].checked=true;
}

//function password_switch_radio_button(form, input){
//	var NodeList = document.getElementsByTagName("input");
//	for(var i=0; i< NodeList.length; i++){
//		if(NodeList.item(i).name=="password[password_auto]" && NodeList.item(i).value=="0"){
//			NodeList.item(i).checked=true;
//		}
//	}
//}

function showadv() {
		if(document.user_add.advshow.checked == true) {
			G("adv").style.display = "";
		} else {
			G("adv").style.display = "none";
		}
}

function change_credeential_state(v){
		if(v!="0") {
			G("credential_no").disabled=false;
			G("credential_no").className="inputText";
			G("credential_no").style.display = "";
		}
		else {
			G("credential_no").value="";
			G("credential_no").className="";
			G("credential_no").style.display = "none";
			G("credential_no").disabled=true;
		}
}
</script>';

if (! empty ( $_GET ['message'] )) {
    $message = urldecode ( getgpc('message') );
}

$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$tool_name = get_lang ( 'AddUsers' );

$form = new FormValidator ( 'user_add' );

//$form->addElement ( 'header', 'header', $tool_name );

// Username 登录名
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '您输入的内容只能为字母或者数字'), 'alphanumeric' );
//$form->addRule ( 'username', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$form->addRule ( 'username', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'username' );
//$form->addRule ( 'username', '', 'maxlength', 20 );
//$form->addRule ( 'username', get_lang ( 'UserTaken' ), 'username_available', $user_data ['username'] );

// Password 密码

// Firstname 姓名
//$form->addElement ( 'text', 'describe', "描述", array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'content', "自定义编号", array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'content', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//提交
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
//$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', get_lang ( 'SaveAdd' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

// Set default values 默认值
$defaults ['admin'] ['platform_admin'] = 0;
$defaults ['mail'] ['send_mail'] = 0;

$defaults ['password'] ['password_auto'] = 2;

$defaults ['active'] = 1;
$defaults ['sex'] = 0;

$defaults ['expiration_date'] = array ();
$days = api_get_setting ( 'account_valid_duration' );
/*$defaults['expiration_date']['d']=date('d',$time);
 $defaults['expiration_date']['F']=date('m',$time);
 $defaults['expiration_date']['Y']=date('Y',$time);*/
$defaults ['expiration_date'] = date ( 'Y-m-d H:i', strtotime ( "+ $days day" ) );
$defaults ['radio_expiration_date'] = 0;


$form->setDefaults ( $defaults );

$form->addFormRule ( "_license_user_count" );

Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {
//    if (_license_user_count () == FALSE or _check_org_user_quota () == FALSE) {
//        api_redirect ( 'topo_add.php?message=' . urlencode ( get_lang ( 'UserCountExcess' ) ) );
//    }

    $net = $form->getSubmitValues ();

    $name = $net ['name'];
    //$describe = $net ['describe'];
    $content = $net['content'];
    //$firstname = $net ['firstname'];


    $sql_data = array ('name' => $name,
        //'describe' => $describe,
        'content' => $content
    );
    $sql = Database::sql_insert ( $table_net, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );


    if (isset ( $user ['submit_plus'] )) {
        api_redirect ( 'topo_add.php?message=' . urlencode ( get_lang ( 'UserAdded' ) ) );
    } else {
        tb_close ( 'vm_list_iframe.php?action=show_message&message=' . urlencode ( get_lang ( 'UserAdded' ) ) );
    }
}

Display::display_header($tool_name,FALSE);

if (! empty ( $message )) {
    Display::display_normal_message ( stripslashes ( $message ), false );
}

$form->display ();

Display::display_footer ();
