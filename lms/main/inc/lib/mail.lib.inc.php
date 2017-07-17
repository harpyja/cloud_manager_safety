<?php
require_once ('Mail.php');
require_once ('Mail/mime.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'conf/mail.conf.php');

$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

function is_mail_setting_valid() {

}

function api_email_wrapper($emailTo, $emailSubject, $emailBody) {
	if (get_setting ( 'notification_type', 'platform_email' ) == 'true') {
		$mail_type = get_setting ( 'platform_mail_type' );
		if ($mail_type == MAIL_TYPE_SMTP) {
			return api_mail_html ( '', $emailTo, $emailSubject, $emailBody );
		} elseif ($mail_type == MAIL_TYPE_GMAIL) {return api_customer_gmail ( '', $emailTo, $emailSubject, $emailBody );}
	}
}

/**
 * Sends email using the phpmailer class
 * Sender name and email can be specified, if not specified
 * name and email of the platform admin are used
 *
 * @param recipient_name $recipient_name 收件人名字，可以是数组
 * @param recipient_email $recipient_email 收件人地址，可以是数组
 * @param subject $subject 邮件主题
 * @param message $message 正文
 * @param string $sender_name 发件人名字
 * @param string $sender_email 发件人地址
 * @param string $extra_headers
 * @return                  returns true if mail was sent
 * @see                     class.phpmailer.php
 */
function api_mail($recipient_name, $recipient_email, $subject, $message, $sender_name = "", $sender_email = "", $extra_headers = "") {
	global $regexp;
	$params ['auth'] = true;
	$params ['host'] = api_get_setting ( 'smtp_mail_host' );
	$params ['port'] = api_get_setting ( 'smtp_mail_port' );
	$params ['username'] = api_get_setting ( 'smtp_mail_username' );
	$params ['password'] = api_get_setting ( 'smtp_mail_password' );
	$params ['timeout'] = 30;
	
	if (is_array ( $recipient_email )) {
		$i = 0;
		foreach ( $recipient_email as $address ) {
			if (eregi ( $regexp, $address )) {
				$recipients [] = $address;
			}
			$i ++;
		}
	} else {
		if (eregi ( $regexp, $recipient_email )) {
			$recipients = array ($recipient_email );
		}
	}
	
	$headers = array ('From' => api_get_setting ( 'smtp_mail_address' ), 'Reply-To' => api_get_setting ( 'smtp_mail_replyto' ), 'From' => api_get_setting ( 'smtp_mail_address' ), 'Subject' => 'ZLMS' . get_lang ( 'PlatformAdministrator' ) . ": " . $subject );
	
	$send = @$mail = & Mail::factory ( 'smtp', $params );
	$mail->send ( $recipients, $headers, $message );
	if (PEAR::isError ( $send )) {
		//echo($send->getMessage());
		return false;
	} else {
		return true;
	}
}

/**
 * Sends an HTML email using the phpmailer class (and multipart/alternative to downgrade gracefully)
 * Sender name and email can be specified, if not specified
 * name and email of the platform admin are used
 *
 * @author Bert Vanderkimpen ICT&O UGent
 * @author Yannick Warnier <yannick.warnier@ZLMS.com>
 *
 * @param string		   	name of recipient
 * @param string		  	email of recipient
 * @param string            email subject
 * @param string			email body
 * @param string			sender name
 * @param string			sender e-mail
 * @param array				extra headers in form $headers = array($name => $value) to allow parsing
 * @return                  returns true if mail was sent
 * @see                     class.phpmailer.php
 */
function api_mail_html($recipient_name, $recipient_email, $subject, $message, $sender_name = "", $sender_email = "", $text = "") {
	global $regexp;
	//$params['debug']=true;
	$params ['auth'] = true;
	$params ['host'] = api_get_setting ( 'smtp_mail_host' );
	$params ['port'] = api_get_setting ( 'smtp_mail_port' );
	$params ['username'] = api_get_setting ( 'smtp_mail_username' );
	$params ['password'] = api_get_setting ( 'smtp_mail_password' );
	$params ['timeout'] = 20;
	//$params['timeout']=(empty(api_get_setting('smtp_mail_timeout'))?api_get_setting('smtp_mail_timeout'):30);
	

	if (is_array ( $recipient_email )) {
		$i = 0;
		foreach ( $recipient_email as $address ) {
			if (eregi ( $regexp, $address )) {
				$recipients [] = $address;
			}
			$i ++;
		}
	} else {
		if (eregi ( $regexp, $recipient_email )) {
			$recipients = array ($recipient_email );
		}
	}
	
	$subject = "[" . api_get_setting ( 'siteName' ) . "] - " . $subject;
	if (function_exists ( 'iconv_mime_encode' )) {
		$imePrefs ['scheme'] = 'Q';
		$imePrefs ['input-charset'] = SYSTEM_CHARSET;
		$imePrefs ['output-charset'] = SYSTEM_CHARSET;
		$imePrefs ['line-length'] = 74;
		$imePrefs ['line-break-chars'] = "\r\n";
		$hdr_value = iconv_mime_encode ( "Subject", $subject, $imePrefs );
		if (! $hdr_value) $subject = get_lang ( 'DefaultEmailSubject' );
	}
	
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"      "http://www.w3.org/TR/html4/loose.dtd">
  <html><head> <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>' . $subject . '</title>
  </head> <body>' . $message . '</body> </html>';
	
	$param ['text_charset'] = SYSTEM_CHARSET;
	$param ['html_charset'] = SYSTEM_CHARSET;
	$param ['head_charset'] = SYSTEM_CHARSET;
	$headers = array ('Subject' => $subject, 'From' => 'e-Learning Platform <' . api_get_setting ( 'smtp_mail_address' ) . '>', 'Reply-To' => api_get_setting ( 'smtp_mail_replyto' )
	/*'From'=>api_get_setting('smtp_mail_address'),*/
	);
	$crlf = "\n";
	$mime = new Mail_mime ( $crlf );
	$mime->setTXTBody ( $text );
	$mime->setHTMLBody ( $html );
	$body = $mime->get ( $param );
	$hdrs = $mime->headers ( $headers );
	$send = @$mail = & Mail::factory ( 'smtp', $params );
	$mail->send ( $recipients, $hdrs, $body );
	if (PEAR::isError ( $send )) {
		//echo($send->getMessage());
		return false;
	} else {
		return true;
	}
}

// =============================== gmail ==============================
/**
 * 发送GMAIL 邮
 *
 * @param unknown_type $recipient_name 收件人名称,可以是数组
 * @param unknown_type $recipient_email 收件人email地址,可以是数组
 * @param unknown_type $subject 邮件标题
 * @param unknown_type $message 邮件HTML正方信息
 * @param unknown_type $sender_name 发件人名称
 * @param unknown_type $sender_email 发件人EMAIL地址
 * @param unknown_type $gmail_box_addr GMAIL 邮箱地址
 * @param unknown_type $gmail_box_pwd GMAIL 邮箱密码
 * @param unknown_type $gmail_box_reply_to 回复到的EMAIL地址
 * @param unknown_type $mai_reply_to_name 回复到的名字(显示)
 * @return unknown
 */
function api_gmail($recipient_name, $recipient_email, $subject, $message, $sender_name, $sender_email, $gmail_box_addr, $gmail_box_pwd, $gmail_box_reply_to, $mai_reply_to_name) {
	
	global $regexp;
	global $external_email;
	$mail = new PHPMailer ();
	
	$mail->IsSMTP ();
	$mail->SMTPAuth = true; // enable SMTP authentication
	$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
	$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
	$mail->Port = 465; // set the SMTP port for the GMAIL server
	$mail->CharSet = 'utf-8';
	
	$mail->Username = $gmail_box_addr; // GMAIL username
	$mail->Password = $gmail_box_pwd; // GMAIL password
	if ($gmail_box_reply_to != "" && $gmail_box_reply_to_name != "") {
		$mail->AddReplyTo ( $mai_reply_to, $gmail_box_reply_to_name ); //回复的地址,名字
	} else {
		if ($sender_email != "" && $sender_name != "") {
			$mail->AddReplyTo ( $sender_email, $sender_name );
		} else {
			return 0;
		}
	}
	
	if ($sender_email != "") { //发件人地址
		$mail->From = $sender_email;
		$mail->Sender = $sender_email;
	}
	
	if ($sender_name != "") { //发件人名字
		$mail->FromName = $sender_name;
	}
	
	$mail->IsHTML ( true );
	$mail->Subject = $subject;
	$body = '<html><head></head><body>' . $message . '</body></html>';
	$mail->Body = $body;
	
	if (is_array ( $recipient_email )) {
		$i = 0;
		foreach ( $recipient_email as $address ) {
			$strTemp = $recipient_name [$i];
			if (eregi ( $regexp, $address )) {
				$mail->AddAddress ( $address, $strTemp );
			}
			$i = $i + 1;
		}
	} else {
		if (eregi ( $regexp, $recipient_email )) {
			$recipient_name = $recipient_name;
			$mail->AddAddress ( $recipient_email, $recipient_name );
		}
	}
	
	//send mail
	if (! $mail->Send ()) {
		echo "ERROR: mail not sent to " . $recipient_name . " (" . $recipient_email . ") because of " . $mail->ErrorInfo . "<br>";
		return 0;
	}
	
	// Clear all addresses
	$mail->ClearAddresses ();
	return 1;

}

/**
 * 客户化的gmail配置来发送邮件
 *
 * @param unknown_type $recipient_name
 * @param unknown_type $recipient_email
 * @param unknown_type $subject
 * @param unknown_type $message
 * @return unknown
 */
function api_customer_gmail($recipient_name, $recipient_email, $subject, $message) {
	global $external_email;
	return api_gmail ( $recipient_name, $recipient_email, $subject, $message, $external_email ['gmail_box_from_name'], //发件人名字
$external_email ['gmail_box_from'], $external_email ['gmail_box'], //gmail邮箱用户名
$external_email ['gmail_pwd'], //gmail邮箱密码
$external_email ['gmail_reply_to'], //回复到
$external_email ['gmail_box_from_name'] ); //回复到名字
}

/**
 * 发送GMail 技术支持邮件
 *
 * @param unknown_type $recipient_name
 * @param unknown_type $recipient_email
 * @param unknown_type $subject
 * @param unknown_type $message
 * @return unknown
 */
function api_zlms_gmail($recipient_name, $recipient_email, $subject, $message) {
	$title = "ZLMS 技术支持: " . $subject;
	$body = '<html><head></head><body>';
	$body .= 'Hi,  您好! <P>&nbsp;&nbsp;&nbsp;&nbsp;这是zlms的技术支持邮件, 首先感谢您对
		<a href="http://www.zlms.org"><b>ZLMS学习管理系统产品及其我们公司</b></a> 的关注与支持!';
	$body .= $message . "</body></html>";
	return api_gmail ( $recipient_name, $recipient_email, $title, $body, get_lang ( 'CompanyName' ), "zlms.support@gmail.com", "zlms.support@gmail.com", "zlms321680", "zlms.support@gmail.com", get_lang ( 'CompanyName' ) );
}

?>