<?php
require_once("../../login.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo get_lang("SystemName"); ?></title>
<style type="text/css">
</style>
<link href="assets/css_login.css" rel="stylesheet" type="text/css" />
<SCRIPT language="JavaScript">

function LoadPage()
{ 
	if(document.getElementById("j_username").value.length>=6) {
		document.getElementById("j_password").focus();   
	} else {
		document.getElementById("j_username").focus();
	}

}		

function keyPressInUser() {
	var keyValue;
	keyValue=window.event.keyCode;
	if(keyValue==13) document.all.j_password.focus();
}

function keyPressInPassword() {
	var keyValue;
	keyValue=window.event.keyCode;
	if(keyValue==13) document.all.btnLogin.click();
	 // submitForm();
}

 function userlogincheck()	
 { 
		var frm=document.form1;
		document.getElementById("errorMsg").innerHTML="";
	   	if(frm.j_username.value==""){
		   	/*alert('<?=get_lang("userNameRequired")?>');*/
		   	document.getElementById("errorMsg").innerHTML="<?=get_lang("userNameRequired")?>";
		   	frm.j_username.focus();
		   	return false;
	   	}
	   	if(frm.j_password.value==""){
		   	/*alert('<?= get_lang("userPweRequired") ?>');*/
		   	document.getElementById("errorMsg").innerHTML="<?=get_lang("userPweRequired")?>";
		   	frm.j_password.focus();
		   	return false;
	   	}
	   	<?php if($is_needed_seccode){ ?>
	   	if(frm.seccode.value==""){		   	
		   	document.getElementById("errorMsg").innerHTML="<?=get_lang("VerifyCodeIsRequired")?>";
		   	frm.seccode.focus();
		   	return false;
	   	} <?php } ?>
	   	document.getElementById("errorMsg").innerHTML="<?=get_lang("Logining")?>";
	   	return true;		   
  }
	
</SCRIPT>
	   	<?php echo import_assets("commons.js");
	   	echo import_assets("jquery-latest.js");
	   	?>
</head>
<body onload="LoadPage()">


<div class="main">
<div class="title"><em><a
	href="<?=get_lang('CompanyWebSite')?>"
	target="_blank">联系我们</a></em> <em><a
	href="mailto:<?=get_lang("SupportMailBox")?>" target="_blank">技术支持</a></em>
<em><a href="<?=api_get_path(WEB_PATH)?>" target="_blank">网站首页</a></em>
</div>
<div class="login">
<FORM name="form1" onSubmit="return userlogincheck();" method="post"
	action="login.php"><input type="hidden" name="testcookie" value="1" />
<input type="hidden" name="indexPage" value="panel/default/index.php" />

<div class="inputbox">
<div class="msg" id="errorMsg"><?=$loginFailed?handle_login_failed():"" ?></div>
<dl>
	<dt>用户名：</dt>
	<dd><input type="text" id="j_username" name="login"
		style="WIDTH: 150px" onblur="this.style.borderColor='#dcdcdc'"
		onkeypress="keyPressInUser()" onMouseOver="this.focus()"
		onFocus="this.select();this.style.borderColor='#239fe3'"
		autocomplete="on" value="<?php echo $_COOKIE['lms_login_name']; ?>" /></dd>
</dl>
<dl>
	<dt>密 码：</dt>
	<dd><input type="password" id="j_password" name="token"
		style="WIDTH: 150px" onkeypress="keyPressInPassword()"
		onmouseover="this.focus()"
		onFocus="this.select();this.style.borderColor='#239fe3'"
		onblur="this.style.borderColor='#dcdcdc'" /></dd>
</dl>
	   	<?php if($is_needed_seccode){ ?>
<dl>
	<dt>验证码：</dt>
	<dd><input id="seccode" name="seccode" type="text"
		onfocus="this.style.borderColor='#239fe3'"
		onblur="this.style.borderColor='#dcdcdc'"
		style="text-transform: uppercase; WIDTH: 75px" maxlength="4" /> <img
		src="<?=api_get_path(WEB_PATH)?>seccode.php?m=seccode&seccodeauth=<?=$seccode ?>&<?=rand() ?>" />
	<input type="hidden" name="seccodehidden" value="<?=$seccode ?>" /></dd>
</dl>
	   	<?php } ?>
<dl>
	<dt>&nbsp;</dt>
	<dd><input name="btnLogin" type="submit" value="" class="input" /></dd>
</dl>

<div class="btnLink"><?php if (api_get_setting('allow_registration') != 'false') : ?>
<A
	href="<?=api_get_path(WEB_PATH)?>reg.php?KeepThis=true&TB_iframe=true&height=340&width=680&modal=true"
	class="thickbox"><?=get_lang("Reg") ?></A> <?php endif;
	if (api_get_setting('allow_lostpassword') == 'true') :
	?> <A
	href="<?=api_get_path(WEB_CODE_PATH)?>auth/lostPassword.php?KeepThis=true&TB_iframe=true&height=280&width=640&modal=true"
	class="thickbox"><?=get_lang("LostPassword") ?></A> <?php endif;
	if (api_get_setting('single_login') == 'true') : 
	?><A
	href="login.php?logout=clear"><?=get_lang("ClearTrace") ?></A>
	<?php endif; ?>
	</div>

</div>
<div class="butbox">
<dl>
	<dt></dt>
	<dd></dd>
</dl>
</div>
<div class="clear"></div>
</form>
</div>

<div class="copyright"><?=get_lang('LoginPageCopyright')?></div>
</div>

</body>
</html>

