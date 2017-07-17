<?php
include_once ("../../login.inc.php");
 $platform_path='portal/sp/index.php';
    $err=$_GET["error"];
   if($err=="user_password_incorrect"){
       $error_msg='密码错误！' ; 
   }elseif($err=="auth_user_locked"){ 
       $error_msg='用户被锁定！' ;
   }elseif($err=="auth_user_expiration"){ 
       $error_msg='帐号过期！' ;
   }elseif($err=="auth_user_not_exsist"){ 
           echo "<script ype='text/javascript'>";
      $error_msg='用户不存在！' ;
   }elseif($err=="auth_failed"){ 
       $error_msg='认证失败！' ;
    }elseif($err=="all_failed"){ 
    $error_msg='登录失败 - 用户名/密码错误！' ;
    }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?=api_get_setting ( 'siteName' )?>欢迎您</title>
    <link rel="stylesheet" media="all" type="text/css" href="./css/base.css"/> 
    <link type="text/css" rel="stylesheet" href="./css/login.css">
    <script type="text/javascript">
        function formatText(index, panel) {
            return index + "";
        }

        $(function () {
            $('.anythingSlider').anythingSlider({
                easing: "easeInOutExpo",        // Anything other than "linear" or "swing" requires the easing plugin
                autoPlay: true,                 // This turns off the entire FUNCTIONALY, not just if it starts running or not.
                delay: 3000,                    // How long between slide transitions in AutoPlay mode
                startStopped: false,            // If autoPlay is on, this can force it to start stopped
                animationTime: 600,             // How long the slide transition takes
                hashTags: true,                 // Should links change the hashtag in the URL?
                buildNavigation: false,          // If true, builds and list of anchor links to link to each slide
                pauseOnHover: true,             // If true, and autoPlay is enabled, the show will pause on hover
                startText: "Go",             // Start text
                stopText: "Stop",               // Stop text
                navigationFormatter: formatText       // Details at the top of the file on this use (advanced use)
            });

            $("#slide-jump").click(function(){
                $('.anythingSlider').anythingSlider(6);
            });
        });
        function LoadPage(){
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

        function userlogincheck() {
            var frm=document.form1;
            G("errorMsg").innerHTML="";
            if(frm.j_username.value==""){
                G("errorMsg").style.display="";
                G("errorMsg").innerHTML="<?=get_lang("userNameRequired")?>";
                frm.j_username.focus();
                return false;
            }
            if(frm.j_password.value==""){
                G("errorMsg").style.display="block";
                G("errorMsg").innerHTML="<?=get_lang("userPweRequired")?>";
                frm.j_password.focus();
                return false;
            }
        <?php if($is_needed_seccode){ ?>
            if(frm.seccode.value==""){
                G("errorMsg").style.display="block";
                G("errorMsg").innerHTML="<?=get_lang("VerifyCodeIsRequired")?>";
                frm.seccode.focus();
                return false;
            } <?php } ?>
            G("errorMsg").innerHTML="<?=get_lang("Logining")?>";
            return true;
        }

        $(document).ready( function() {
            imgLoader = new Image();
            imgLoader.src = "<?=api_get_path(WEB_JS_PATH)?>jquery-plugins/thickbox/loadingAnimation.gif";

        }); 
    </script>
    <style>
        html,body{
            height:100%;
        }
        .g-doc{
            height:75.3%;
        }
    </style>
</head>
<body id="login">
<!--    <div class="b-30"></div>  -->
<!--     <div class="b-30"></div>  -->

<div class="g-doc" >
    <div class="g-doc-title">
        <h3>
             <img src="<?="../../panel/default/assets/images/logo3.gif"?>" alt="51elab" width="205" height="97">
        </h3>
    </div>
    <div class="m-logform f-cb">
        <h3 class="user-login"> </h3>
            <div class="m-loginbox f-cb">
                <h3 class="user-login-title"></h3>

                <div class="login-content-input">
                    <form action="login.php" method="post"   onSubmit="return userlogincheck();" name="form1" id='login'>
                        <input type="hidden" name="testcookie" value="1" />
                        <?php
                        $sysidfile="/etc/lessonuser";
                        $num=file_get_contents($sysidfile);
                        if(!$num){
                            $num=10;
                        }
                        $user_list = WhoIsOnline ( api_get_user_id (), null, api_get_setting ( 'time_limit_whosonline' ) );
                        $count = count ( $user_list );
                        $sql = "select count(login_id) from track_e_online ;";
                        $count = Database::getval ( $sql, __FILE__, __LINE__ );
                        $count+=0;
                        $num+=0;
                        if($count<$num){
                            echo '<input type="hidden" name="indexPage" value="'.$platform_path.'" />';
                        }else{
                            echo '<script>
                            $.prompt("用户超限");
                        </script>';
                        }
                        ?>
                        <b class="buname">用户名：</b>
                         <div class="b-10">
                        </div>
                        
                        <input type="text"  class="l-input uname" name="login" value="<?=$_configuration ['enable_ukey_login']?'':$_COOKIE['lms_login_name'] ?>"  onkeypress="keyPressInUser()"
                        <?php if($_configuration ['enable_ukey_login']) echo 'readonly';?>autocomplete="on">
                        <div class="b-10">
                        </div>
                        <b class="buname">密&nbsp;&nbsp;&nbsp;码：</b>
                         <div class="b-10">  </div>
                        <input type="password" class="l-input passwd" value="" onkeypress="keyPressInPassword()" name="token">
                         <div class="b-10">  </div>
                        <span class="itm itm-1 f-vama">
                        <label class="lb">
                            <span class="atlg">
                                <input type="checkbox" value="" name="checkbox" class="f-check autologin j-autologin">自动登录
                            </span>
                        </label>
                            <?php
                        if (api_get_setting('allow_lostpassword') == 'true') { ?>
                            <a href="<?=api_get_path(WEB_PATH)?>lostPassword.php?KeepThis=true&TB_iframe=true&height=250&width=600&modal=true" >忘记密码</a>
                        <?php } ?>
                        </span>
                        <div class="b-10">
                        </div>
                        <input type="submit" value="登录" class="u-btn u-btn-primary j-submit" >
                        <div class="b-10">
                        </div> 
                        <div class="inputstyle forget" style="height: 10px;line-height: 8px;">
                            <span style="margin-top:8px;height: 10px;line-height: 10px;color:red;">
                                <div style="height: 10px;line-height: 10px;"  ><?=$error_msg?></div>
                                <?php if (api_get_setting('allow_registration') != 'false') { ?>
                                    <div class="reg" style="float:right">
                                    <a  title="注册"   onclick="location.href='user_register.php';">立即注册>></a>
                                    </div>
                               <?php }?>
                            </span>
                            
                        </div>
                        
                    </form>
                </div>
            </div>
    </div>
</div>
    <div class="b-40"></div>  
    <div class="b-40"></div>  
    <div class="b-40"></div>  
<div class="g-wrap m-foot" id="j-footer">
     
         <div class="m-ft2 f-cb">
 			      Copyright @2011-2014 51elab.All Rights Reserved.
				  <p></p>
				   北京易霖博信息技术有限公司  版权所有
 	 </div>
    
</div>
<?php if($_configuration ['enable_ukey_login']){?>
<script type="text/javascript">
    var obj=document.getElementById("FtRockey2");
    if(obj){
        //obj.uid=715400947;
        obj.OpenMode=0;
        var r2_num = obj.Ry2find();
        //alert("找到的加密锁个数：" + r2_num);
        var r2_handle = obj.Ry2open();
        if(r2_handle >= 0){
            obj.BlockIndex = 4;
            obj.Buffer = ""
            err = obj.Ry2Read();
            if(err == 0){
                if(document.getElementById("j_username")) document.getElementById("j_username").value=(obj.Buffer);
                obj.close();
            }else{
                alert("对不起,读取硬件信息失败,你不能登录使用本系统!");
            }
        }else{
            alert("不能打开加密锁,请插入硬件U棒后刷新当前页面!");
        }
    }else{
        alert('无法初始化硬件, 你不能使用本系统!');
    }
</script>
    <?php } ?>

 <?php
//        include_once './inc/page_footer.php';
?>
</body>
</html>
