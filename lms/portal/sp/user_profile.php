<?php
$cidReset = true;
include_once ("inc/app.inc.php");

include_once ("inc/page_header.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once ('../../main/inc/conf/user.conf.php');
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$user_id = api_get_user_id ();
$objDept = new DeptManager ();
$r_action=  getgpc("action");
if (is_equal ( $r_action, "save" )) {
    $user_data = array (
        "lastname" => getgpc ( "lastname" ),
        "en_name" => getgpc ( "en_name" ),
        "birthday" => getgpc ( "birthday" ),
        //'work_type' => getgpc ( 'work_type' ),
        'sex' => getgpc ( 'sex' ),
        'email' => getgpc ( 'email' ),
        'phone' => getgpc ( 'phone' ),
        'mobile' => getgpc ( 'mobile' ),
         
        //'academic' => getgpc ( 'academic' ),
        //'nation' => getgpc ( 'nation' ),
        //'company' => getgpc ( 'company' ),
        //'is_sign_contract' => getgpc ( 'is_sign_contract' ) ? 1 : 0,
        //'is_insurance1' => getgpc ( 'is_insurance1' ) ? 1 : 0,
        //'is_insurance2' => getgpc ( 'is_insurance2' ) ? 1 : 0,
        //'is_insurance3' => getgpc ( 'is_insurance3' ) ? 1 : 0,
        //'age' => intval ( getgpc ( 'age' ) ),
        //'avoid_exam' => intval ( getgpc ( 'avoid_exam' ) )
    );
    
 
    if (getgpc ( "firstname" ) && api_get_setting ( 'profile', 'name' ) == 'true') $user_data ['firstname'] = getgpc ( "firstname" );
 //   if (getgpc ( "email" ) && api_get_setting ( 'profile', 'email' ) == 'true') $user_data ['email'] = getgpc ( "email" );
    if (getgpc ( "dept_id" ) && api_get_setting ( 'profile', 'dept' ) == 'true') $user_data ['dept_id'] = getgpc ( "dept_id" );
    if (getgpc ( "official_code" ) && api_get_setting ( 'profile', 'officialcode' ) == 'true') $user_data ['official_code'] = getgpc ( "official_code" );
    if (getgpc ( "credential_no" ) && api_get_setting ( 'profile', 'credential_no' ) == 'true') $user_data ['credential_no'] = getgpc ( "credential_no" );
   /// if (getgpc ( "phone" ) && api_get_setting ( 'profile', 'phone' ) == 'true') $user_data ['phone'] = getgpc ( "phone" );
   // if (getgpc ( "mobile" ) && api_get_setting ( 'profile', 'mobile' ) == 'true') $user_data ['mobile'] = getgpc ( "mobile" );
    if (getgpc ( "qq" ) && api_get_setting ( 'profile', 'qq' ) == 'true') $user_data ['qq'] = getgpc ( "qq" );

    $dept_in_org = $objDept->get_dept_in_org ( getgpc ( "dept_id" ), TRUE );
    $dept_org = array_pop ( $dept_in_org );
    $user_data ['org_id'] = $dept_org ['id'];
    $user_data ['last_updated_date'] = date ( 'Y-m-d H:i:s' );

    if ($_FILES ['picture'] ['size'] > 0 && is_uploaded_file ( $_FILES ['picture'] ['tmp_name'] )) {
        $new_picture = upload_user_image ( $user_id );
        if ($new_picture) $user_data ['picture_uri'] = $new_picture;
    } elseif (getgpc ( 'remove_picture' )) {
        remove_user_image ( $user_id );
        $user_data ['picture_uri'] = '';
    }

    $sql = Database::sql_update ( $tbl_user, $user_data, "user_id=" . Database::escape ( $user_id ) );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    if ($result) {
         header ( "Location:".URL_APPEDND."/portal/sp/user_profile.php?msg=success" );
    }
}

$user_data = UserManager::get_user_information ( $user_id );
if ($user_data) {
    $user_dept_path = get_dept_path ( $user_data ["dept_id"], false, TRUE );
} else {
    api_redirect ( 'user_center.php' );
}

$dept_options = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
unset ( $dept_options [1] );

$img_attributes = get_user_picture ( $user_id );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'index.php?learn_status=user', "name" => "用户中心" );
$interbreadcrumb [] = array ("url" => 'user_profile.php', "name" => "信息修改" );
echo import_assets ( 'js/formValidator/style/validator.css', WEB_QH_PATH );
echo import_assets ( 'js/formValidator/formValidator.js', WEB_QH_PATH );
echo import_assets ( 'js/formValidator/formValidatorRegex.js', WEB_QH_PATH );
echo import_assets ( 'js_calendar.js', WEB_JS_PATH );
?>
<style>
    body{
        color:#444;
    }
    .la{color:#444;}
  input{color:#444;}
</style>
<script type="text/javascript">
    $(document).ready(function(){
        //$.formValidator.initConfig({formid:"theForm",onerror:function(){alert("校验没有通过，具体错误请看错误提示")}});
        $.formValidator.initConfig({formid:"theForm",onerror:function(msg){$.prompt(msg)}});

        $("#firstname").formValidator({onshow:"请输入真实姓名",onfocus:"真实姓名不能为空",oncorrect:"真实姓名输入合法"})
                .inputValidator({min:1,empty:{leftempty:false,rightempty:false,emptyerror:"真实姓名两边不能有空符号"},onerror:"真实姓名长度不合要求,请确认"});

        $("#credential_no").formValidator({onshow:"请输入身份证号",onfocus:"请输入18位身份证号",oncorrect:"身份证号输入合法"})
                .inputValidator({min:18,empty:{leftempty:false,rightempty:false,emptyerror:"身份证号两边不能有空符号"},onerror:"身份证号长度不合要求,请确认"})
                .regexValidator({regexp:"username",datatype:"enum",onerror:"身份证号格式不正确"});

        $("#mobile").formValidator({onshow:"请输入手机号",onfocus:"请输入11位手机号",oncorrect:"手机号输入合法"})
                .inputValidator({min:11,empty:{leftempty:false,rightempty:false,emptyerror:"手机号两边不能有空符号"},onerror:"手机号长度不合要求,请确认"})
                .regexValidator({regexp:"num",datatype:"enum",onerror:"手机号格式不正确"});

        $("#phone").formValidator({empty:true,onshow:"请输入固定电话号",onfocus:"请输入固定电话号",oncorrect:"固定电话号输入合法"})
                .inputValidator({min:11,onerror:"固定电话号长度不合要求,请确认"})
                .regexValidator({regexp:"num",datatype:"enum",onerror:"固定电话号格式不正确"});

        $("#qq").formValidator({onshow:"请输入QQ号",onfocus:"请输入QQ号",oncorrect:"QQ号输入合法"})
                .inputValidator({min:5,empty:{leftempty:false,rightempty:false,emptyerror:"QQ号两边不能有空符号"},onerror:"QQ号长度不合要求,请确认"})
                .regexValidator({regexp:"num",datatype:"enum",onerror:"QQ号格式不正确"});

        $("#age").formValidator({onshow:"请输入年龄",onfocus:"请输入年龄",oncorrect:"年龄输入合法"})
                .inputValidator({min:2,empty:{leftempty:false,rightempty:false,emptyerror:"年龄两边不能有空符号"},onerror:"年龄长度不合要求,请确认"})
                .regexValidator({regexp:"num",datatype:"enum",onerror:"年龄格式不正确"});

        $("#credential_type").change(function(){
            if($("#credential_type").val()=="0"){
                $("#credential_no").attr("disabled","true");
                $("#credential_no").removeAttr("class");
            }else{
                $("#credential_no").removeAttr("disabled");
            }
        });
    });
</script>

<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
           <div class="b-30"></div>
          <!--左侧-->
   <div class="g-container f-cb">
        <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                    <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="用户中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                         <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="用户中心">用户中心</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="信息修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="信息修改" href="user_profile.php" style="color:green;font-weight:bold">信息修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="密码修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="密码修改" href="user_center.php">密码修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的考勤" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的考勤" href="work_attendance.php">我的考勤</a>
                    </li>
                    
                </ul>
            </div>
                        <div class="m-university" id="j-university">
                            <div>
                                   <div class="bar f-cb">
                                          <h3 class="left f-fc3">制作课件</h3>
                                   </div>
                                 <div class="us">
                                 <?php
                                    if($Recently_Study_count>0){
                                        foreach ($Recently_Study as $values1) { ?>
                                            <div class="Recently_Study">
                                               <a class="recently1" href="<?=URL_APPEND?>portal/sp/course_home.php?cidReq=<?=$values1['code']?>&action=introduction" class="logo" >
                                                 <?=api_trunc_str2($values1['title'],18)?> 
                                               </a>
                                           </div>
                                    <?php
                                        }
                                    }else{?>
                                        <div class="Recently_Study">
                                                 没有最近学习
                                           </div>
                                   <?php
                                   }
                                    ?>
                                </div> 
                        </div>
                </div>
        </div>
    <div class="g-mn1" > 
         <div class="g-mn1c m-cnt" style="display:block;">
             <div class="top f-cb j-top">
                 <h3 class="left f-thide j-cateTitle title">
                 <span class="f-fc6 f-fs1" id="j-catTitle">信息修改</span>
                 </h3>
             </div>
    <div class="j-list lists" id="j-list"> 
        <div class="userContent">
            <form action="user_profile.php" method="post" name="theForm" enctype="multipart/form-data" id="theForm">
                <input type="hidden" name="action" value="save" />
                <input name="MAX_FILE_SIZE" type="hidden" value="1048576" />
                <input name="language" type="hidden" value="simpl_chinese" />
                <div class="i-m">
                    <div class="la username">
                        <span class="as usera">用户名：</span><?=$user_data ['username']?>
                    </div>
                    <div class="la email">
                        <span class="as umail">邮&nbsp;&nbsp;&nbsp;件：</span><?php
                            echo form_input ( 'email', $user_data ['email'], 'id="email" class="inputText" style="width:180px"' );
                        ?><div id="emailTip" style="display: inline;"></div>
                    </div>
                    <div class="la sex">
                        <span class="as usex">性&nbsp;&nbsp;&nbsp;别：</span> <?php
                            echo form_radio ( 'sex', 1, $user_data ["sex"] == 1 ) . ' 男&nbsp;&nbsp;';
                            echo form_radio ( 'sex', 2, $user_data ["sex"] == 2 ) . ' 女&nbsp;&nbsp;';
                            echo form_radio ( 'sex', 0, $user_data ["sex"] == 0 ) . ' 保密';
                        ?>
                    </div>
                    <div class="la usernum">
                        <label for="usernum">编&nbsp;&nbsp;&nbsp;号：</label>
                        <?php
                        if (api_get_setting ( 'profile', 'officialcode' ) == 'true') {
                            echo form_input ( 'official_code', $user_data ['official_code'], 'id="email" class="inputText" style="width:180px"' );
                        } else {
                            echo $user_data ['official_code'];
                            echo form_hidden ( 'official_code', $user_data ['official_code'] );
                        }
                        ?>
                        <div id="emailTip" style="display: inline;"></div>
                    </div>
                    <div class="la chineseuser">
                        <label for="chineseuser"><span style="color: #F00; ">*&nbsp;</span>中文名：</label>
                        <?php
                        if (api_get_setting ( 'profile', 'name' ) == 'true') {
                            echo form_input ( 'firstname', $user_data ['firstname'], 'id="firstname" class="inputText" style="width:180px"' );
                        } else {
                            echo $user_data ['firstname'];
                            echo form_hidden ( 'firstname', $user_data ['firstname'] );
                        }
                        ?>
                        <div id="firstnameTip" style="display: inline;"></div>
                    </div>
                    <div class="la phone">
                        <label for="phone"><span style="color: #F00; ">*&nbsp;</span>手&nbsp;&nbsp;&nbsp;机：</label>
                        <?php
                            echo form_input ( 'mobile', $user_data ['mobile'], 'id="mobile" class="inputText" style="width:180px"' );
                        ?>
                        <div id="firstnameTip" style="display: inline;"></div>
                    </div>
                    <!--div class="la phone">
                        <label for="phone">固&nbsp;&nbsp;&nbsp;话：</label>
                        <?php 
                            //echo form_input ( 'phone', $user_data ['phone'], 'id="phone" class="inputText" style="width:180px"' ); 
                        ?>
                        <div id="firstnameTip" style="display: inline;"></div>
                    </div-->
                    <div class="save-button">
                        <input class="btn_querenbaocun" name="apply_change" id="save-buttons" type="submit"/>
                    </div>
                </div>
                <div class="i-mc">
                    <div class="imgcontent">
                        <div class="userimg">

                            <img <?=$img_attributes?> />
                            <div class="dopost">
                            <input class="inputText borderleft"  name="picture"   type="file" /><br />
                            <span class="borderleft"><input name="remove_picture"  type="checkbox" value="1" id="remove_picture" />
            				<label  for="remove_picture">移除图片</label></span><br />
                            <span class="hightred borderleft">（注意: 上传图片大小不要超过1M,格式仅限jpg,gif,png）</span>
							
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
<style type="text/css">
/*    body{
        min-height:80%;
    }*/
</style>
</html>
