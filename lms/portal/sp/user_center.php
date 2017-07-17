<?php
$cidReset = true;
include_once ("inc/app.inc.php");

include_once ("inc/page_header.php");
$interbreadcrumb[] = array ("url" => 'index.php', "name" => "首页");
$interbreadcrumb [] = array ("url" => 'index.php?learn_status=user', "name" => "用户中心" );
$interbreadcrumb [] = array ("url" => 'user_center.php', "name" => "修改密码" );

?>

<link type="text/css" rel="stylesheet" href="js/formValidator/style/validator.css"/>
<script src="js/formValidator/formValidator.js" type="text/javascript"
	charset="UTF-8"></script>
<script src="js/formValidator/formValidatorRegex.js"
	type="text/javascript" charset="UTF-8"></script>

<script type="text/javascript">
$(document).ready(function(){
	$.formValidator.initConfig({formid:"theForm",onerror:function(){alert("校验没有通过，具体错误请看错误提示")}});
	$("#old_pass").formValidator({onshow:"请输入旧密码",onfocus:"请输入旧密码",oncorrect:"旧密码正确"}).inputValidator({min:6,max:20,onerror:"你输入的旧密码非法,请确认"})
	    .ajaxValidator({
	    type : "get",
		url : "ajax_actions.php?action=check_old_pwd",
		datatype : "text",
		success : function(data){
            if( data == "1" ){ return true; }
            else{return false;}
		},
		buttons: $("#button"),
		error: function(){alert("服务器没有返回数据，可能服务器忙，请重试");},
		onerror : "你输入的旧密码不正确!",
		onwait : "正在对旧密码进行合法性校验，请稍候..."
	});

	$("#password1").formValidator({onshow:"请输入密码",onfocus:"密码不能为空,至少6个字符",oncorrect:"密码合法"})
		.inputValidator({min:6,empty:{leftempty:false,rightempty:false,emptyerror:"密码两边不能有空符号"},onerror:"密码长度不合要求,请确认"});

	$("#password2").formValidator({onshow:"请输入重复密码",onfocus:"两次密码必须一致哦",oncorrect:"密码一致"})
	.inputValidator({min:1,empty:{leftempty:false,rightempty:false,emptyerror:"重复密码两边不能有空符号"},onerror:"重复密码不能为空,请确认"})
	.compareValidator({desid:"password1",operateor:"=",onerror:"两次输入的密码不一致,请确认"});
});
</script>

<style>
    .la{color:#708090;}
  input{color:black;} 
</style>
<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
        <!--左侧-->
        <div class="b-30"></div>
	<div class="g-container f-cb">
        <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                    <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="用户中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                         <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="用户中心">用户中心</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="信息修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="信息修改" href="user_profile.php">信息修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="密码修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="密码修改" href="user_center.php" style="color:green;font-weight:bold">密码修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的考勤" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的考勤" href="work_attendance.php">我的考勤</a>
                    </li>
                    
                </ul>
            </div>
            
            <div class="m-university u-categ f-cb" id="j-university">
                <div>
                   <div class="bar f-cb">
                   <h3 class="left f-fc3">制作课件</h3>
                </div>
                <ul class="u-categ f-cb">
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="申请课程" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="申请课程" href="app_course.php" style="color:green;font-weight:bold">申请课程</a>
                        </li>
                        
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="查看审核结果" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="查看审核结果" href="app_result.php">审核结果</a>
                        </li>
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="提交课件" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="提交课件" href="app_submit.php">提交课件</a>
                        </li>
                </ul>
                               
               </div>
                            
           </div>
        </div>    
<div class="g-mn1" > 
         <div class="g-mn1c m-cnt" style="display:block;">
             <div class="top f-cb j-top">
                 <h3 class="left f-thide j-cateTitle title">
                 <span class="f-fc6 f-fs1" id="j-catTitle">密码修改</span>
                 </h3>
             </div>
    <div class="j-list lists" id="j-list"> 
        <div class="userContent">
            <div class="i-m">


                <div style="float: right; color: red; margin-right: 20px;"><?php
                    if (getgpc ( "msg" ) == "success")
                        echo "密码修改成功!";
                    ?></div>


                <form action="user_center.php" method="post" name="theForm" id="theForm">
                    <input type="hidden" name="action" value="save" />
                <div class="la usernum">
                    <label for="old_pass"><span style="color: #F00; ">*&nbsp;</span>旧密码：</label>
                    <input type="password" id="old_pass" name="old_pass" value="">
                    <div id="old_passTip" style="display: inline;"></div>
                </div>
                <div class="la chineseuser">
                    <label for="password1"><span style="color: #F00; ">*&nbsp;</span>新密码：</label>
                    <input type="password" id="password1" value="" name="password1">
                    <div id="password1Tip" style="display: inline;"></div>
                </div>
                <div class="la chineseuser">
                    <label for="password2"><span style="color: #F00; ">*&nbsp;</span>确认密码：</label>
                    <input type="password" id="password2" value="" name="password2">
                    <div id="password2Tip" style="display: inline;"></div>
                </div>
                <div class="save-button">
                    <label>&nbsp;</label>
                    <input type="submit" value="保存"  class="btn_querenbaocun" id="save-buttons" name="apply_change">
                    <span style="float: right">本项必须填写</span>
                    <span style="color: #F00; float: right">*</span>
                </div>
                    <?php
                    
                    $r_action=  getgpc("action");
                    if (is_equal ( $r_action, "save" )) {
                        $tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
                        $new_pwd = trim ( escape ( getgpc ( 'password1' ) ) );
                        $new_password = api_get_encrypted_password ( $new_pwd, SECURITY_SALT );
                        $sql = "update " . $tbl_user . " set password='" . $new_password . "' where user_id='" . api_get_user_id () . "'";
                        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                        if ($result) {
                            echo '<span style="color:red;margin-left:80px;">&nbsp;&nbsp;&nbsp;您的密码修改成功！ </span>';
                            header ( "Location:".URL_APPEDND."/portal/sp/index.php?learn_status=user" );
                        }
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
         </div>   
         </div>
    </div>    
</div>
</div>
<?php 
include './inc/page_footer.php';
?>
</body>

</html>