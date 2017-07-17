<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;

include ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');


require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();
$code = getgpc('code');
$table_net = Database::get_main_table ( vmdisk );

$def = "SELECT boot,ISO  FROM " . $table_net . " WHERE id=" .$code;
list ( $boot,$ISO ) = api_sql_query_one_row ( $def, __FILE__, __LINE__ );


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
    $message = urldecode ( getgpc('message','G') );
}

$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );


function myreaddir($dir) {
    $handle=opendir($dir);
    $i=0;
    while($file=readdir($handle)) {
        if (($file!=".")and($file!="..")) {
            $list[$file]=$file;
            $i=$i+1;
        }
    }
    closedir($handle);
    //var_dump($list);
    return $list;
}

//$dir = "/var/www/";
$dir = "/tmp/mnt/vmdisk/template/iso/";
$form = new FormValidator ( 'ISO_edit','POST', 'ISO_edit.php?code=' . $code, '' );


$iso = myreaddir($dir);
//$form->addElement ( 'text', 'describe', "描述", array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'checkbox', 'boot', "光盘优先启动",'(不选默认系统将通过硬盘启动)',array ('style' => "width:70px", 'class' => 'inputText' ) );
$form->addElement ( 'select', 'ISO', "光盘镜像文件",$iso, array ('style' => "width:250px", 'class' => 'inputText' ) );

//提交
$form->addElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );


$defaults ['boot'] = $boot;
$defaults ['ISO'] = $ISO;

$days = api_get_setting ( 'account_valid_duration' );


$form->setDefaults ( $defaults );

$form->addFormRule ( "_license_user_count" );

Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {

$code = getgpc("code");

    $net = $form->getSubmitValues ();

    $boot = $net ['boot'];

    $ISO = $net['ISO'];

    $sql_data = array (
        'boot' => $boot,
        'ISO' => $ISO
    );


    $sql = Database::sql_update ( $table_net, $sql_data ,"id='$code'");

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );


    if (isset ( $user ['submit_plus'] )) {
        api_redirect ( 'topo_add.php?message=' . urlencode ( get_lang ( 'UserAdded' ) ) );
    } else {
        tb_close (   );
        //tb_close ( 'ISO_edit.php?' . urlencode ( get_lang ( 'UserAdded' ) ) );
    }
}

Display::display_header($tool_name,FALSE);

if (! empty ( $message )) {
    Display::display_normal_message ( stripslashes ( $message ), false );
}

$form->display ();

Display::display_footer ();