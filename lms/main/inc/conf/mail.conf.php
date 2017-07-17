<?php
define(MAIL_TYPE_CLOSE,'MAIL_CLOSE');
define(MAIL_TYPE_SMTP,'MAIL_SMTP'); // 使用平台配置的SMTP服务器
define(MAIL_TYPE_GMAIL,"MAIL_GMAIL"); //使用GMAIL

$_configuration['mail_type']=MAIL_TYPE_SMTP;
$config_use_platform_email=true; //true: 使用平台配置的SMTP服务器, false:使用GMAIL

// ============================== mail ================================= 

// smtp values for phpmailer Class 
$platform_email['SMTP_FROM_EMAIL']   = 'testly@uniebiz.com';  //发件人地址
$platform_email['SMTP_FROM_NAME']    = 'WebCS技术支持';   //发件人名字
$platform_email['SMTP_MAILER']       = 'smtp'; //mail, sendmail or smtp 
$platform_email['SMTP_HOST']         = 'mail.uniebiz.com';   //邮件主机
$platform_email['SMTP_PORT']         = 25; 			  //发送SMTP的端口
$platform_email['SMTP_AUTH']         = true; 		  //是否需要验证
$platform_email['SMTP_USER']         = 'testly@uniebiz.com'; //用户名
$platform_email['SMTP_PASS']         = '123456'; 		//密码

// =============================== gmail ==============================
//客户的gmail邮箱配置
$external_email['gmail_box']="zlms.support@gmail.com";
$external_email['gmail_pwd']="zlms321680";
$external_email['gmail_reply_to']="zlms.support@gmail.com";//回复的地址,名字
$external_email['gmail_box_from']="zlms.support@gmail.com";
$external_email['gmail_box_from_name']="ZLMS-www.zlms.org";
?>