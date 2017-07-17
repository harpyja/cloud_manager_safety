<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;

include_once ('../../main/inc/global.inc.php');
include_once ('../../main/assignment/assignment.lib.php');
include_once ("inc/app.inc.php");

include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once('inc/page_header.php');
$id=htmlspecialchars(getgpc('id'));$id=(int)$id;
$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());
$offset = getgpc ( "offset", "G" );

$task= Database::get_main_table (task);
$sql="select `id`,`name`,`group` from ".$task." where id =".$id;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$arrs= array ();

while ( $arr = Database::fetch_row ( $res) ) {
    $arrs['task_name']=Database::getval("select name from renwu where id =".$arr[1],__FILE__,__LINE__);
    $arrs['task_desc']=Database::getval("select description from renwu where id =".$arr[1],__FILE__,__LINE__);
    $arrs['task_group']=Database::getval('select name from group_user where id='.$arr[2],__FILE__,__LINE__);
    $arrs['group_id']=$arr[2];
}
$group_type=Database::getval("select `type` from `group_user` where `name`='".$arrs['task_group']."'",__FILE__,__LINE__);
if($group_type==1){
    $type="红方";
}if($group_type==2){
    $type="蓝方";
}

//分页(因为也面试弹出狂的过,无法实现)
$get_id=  getgpc('id');$get_id=(int)$get_id;
$sql1 = "SELECT count(id) FROM vmdisk where mod_type=1 and task_id=".$get_id;
$rs= api_sql_query_array_assoc ( $sql1, __FILE__, __LINE__ );
$total_rows=$rs[0]["count(id)"];
$url = WEB_QH_PATH . "template_info.php?id=".$get_id;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url,NUMBER_PAGE);
$pagination = new Pagination ( $pagination_config );

$ids=  getgpc('id');
$ids=(int)$ids;
//red template
$red_sql ="select red_vm from task where id=".$ids;
$red_data_list = api_sql_query_array_assoc ( $red_sql, __FILE__, __LINE__ );
$red_vm_arr=$red_data_list[0]['red_vm'];
$red_vm_unser=  unserialize($red_vm_arr);

//blue template
$blue_sql ="select blue_vm from task where id=".$ids;
$blue_data_list = api_sql_query_array_assoc ( $blue_sql, __FILE__, __LINE__ );
$blue_vm_arr=$blue_data_list[0]['blue_vm'];
$blue_vm_unser=  unserialize($blue_vm_arr);

//user group info
$g_sql="select `user_id`,`username`,`firstname`,`dept_name` from `user` as t1 left join `sys_dept` as t2 on t2.id= t1.dept_id where t1.group_id=".$arrs['group_id'];
$group_data_list = api_sql_query_array_assoc ( $g_sql, __FILE__, __LINE__ );

//setting info
$s_sql="select `id`,`template_id`,`user_id`,`ip`  from `deploy` where `task_id`=".$id." order by id";
$setting_data_list = api_sql_query_array_assoc ( $s_sql, __FILE__, __LINE__ );

//task name
$vlanid=Database::getval("select name from task where id =".$ids,__FILE__,__LINE__);
$vlanid=$vlanid+1;

//delete setting
$a=intval(htmlspecialchars(getgpc('a')));
if(getgpc('action')=='delete' && is_not_blank($a)){
    $del_sql="delete from deploy where id=".$a;
    $result=api_sql_query($del_sql,__FILE__,__LINE__);
    //api_redirect ('template_info.php?id='.$id);
    tb_close('template_info.php?id='.$id);
}

?>
<aside id="sidebar" class="column open control">
    <div id="flexButton" class="closeButton close"></div>
</aside>
<link rel="stylesheet" href="<?=WEB_QH_PATH?>css/layout.css" type="text/css" media="screen" />
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/portal/sp/index.php">平台首页</a>
        &gt;<a href="<?=URL_APPEDND;?>/portal/sp/template_list.php">我的模板</a>
        <?php
        if(is_not_blank($id)){
            echo '&gt;'.$arrs['task_name'];
        }
        ?>
    </h4>
<div class="main">
    <div class="lab-content-list">
      <ul class="tab">
                <li><a title="对抗信息">对抗信息</a></li>
                <li><a title="对抗部署">对抗部署</a></li>
      </ul>
      <div class="lab-cont">
        <div class="ptab tab1">
            <div class="content">
                <b>小组名称：</b><br><span class="block"><?=$arrs['task_group']?>(<span class="notice"><?=$type?></span>)</span><br>
                <b>小组成员：</b><br><span class="block">
                <table cellspacing="0" cellpadding="0" width="90%" border='1' class="t_table">
                    <?php
                    $count_user = count($group_data_list);
                    if($count_user >0){
                        ?>
                        <tr>
                            <th align="center">编号</th>
                            <th align="center">用户名</th>
                            <th align="center">所属部门</th>
                        </tr>
                        <?php
                        for($p=0;$p<$count_user;$p++){
                            $q=$p+1;
                            ?>
                            <tr>
                                <td align="center"><?=$q?></td>
                                <td align="center"><?=$group_data_list[$p]['username']."(".$group_data_list[$p]['firstname'].")"?></td>
                                <td align="center"><?=$group_data_list[$p]['dept_name']?></td>
                            </tr>
                            <?php } ?>
                        <tr>
                            <td colspan="3" align="right">总计<?=$count_user?>条记录&nbsp;&nbsp;&nbsp;</td>
                        </tr>
                        <?php }else{ ?>
                        <tr><td colspan="3" align="center">没有相关记录</td></tr>
                        <?php }?>
            </table>
          </span>
            </div>
            <div class="content">
                <b>任务指示：</b><br><span class="block"><?=$arrs['task_desc']?></span>
            </div>
            <div class="content">
                <b>模板：</b><br>
            <span class="block">
                            <table cellspacing="0" cellpadding="0" width="90%" border='1' class="t_table">
                                <tr>
                                    <td colspan="3"><span class="header">靶机</span><span class="notice">请保护好您的服务器!</span></td>
                                </tr>
                                <?php
                                $num1=count($red_vm_unser);
                                if($num1 >0){
                                    ?>
                                    <tr>
                                        <th>编号</th>
                                        <th>模版名称</th>
                                        <th>进入对抗</th>
                                    </tr>
                                    <?php
                                    for($i=0;$i<$num1;$i++){
                                        $j=$i+1;
                                        ?>
                                        <tr>
                                            <td align="center"><?=$j?></td>
                                            <td align="center"><?=$red_vm_unser[$i]?></td>
                                            <td align="center" valign="middle" class="comeIn">
                                                <a href="/lms/main/cloud/cloudvmstart.php?system=<?=$red_vm_unser[$i]?>_<?=$_SESSION['_user']['user_id']?>&nicnum=1&user_id=<?=$_SESSION['_user']['user_id']?>&cid=<?=$red_vm_unser[$i] ?>&vlanid=<?=$vlanid?>" target="_new" title="进入对抗">
                                                    <img src="../../themes/img/visio.gif" alt="进入对抗" title="进入对抗"/></a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    <tr>
                                        <td colspan="3" align="right">总计<?=$num1?>条记录&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                    <?php }else{ ?>
                                    <tr><td colspan="3" align="center">没有相关记录</td></tr>
                                    <?php }?>
                            </table>
                <table cellspacing="0" cellpadding="0" width="90%" border='1' class="t_table">
                    <tr>
                        <td colspan="3"><span class="header">渗透</span><span class="notice">请使用渗透模板进行攻击!</span></td>
                    </tr>
                    <?php
                    $num2=count($blue_vm_unser);
                    if($num2 >0){
                        ?>
                        <tr>
                            <th>编号</th>
                            <th>模版名称</th>
                            <th>进入对抗</th>
                        </tr>
                        <?php
                        for($a=0;$a<$num2;$a++){
                            $b=$a+1;
                            ?>
                            <tr>
                                <td align="center"><?=$b?></td>
                                <td align="center"><?=$blue_vm_unser[$a]?></td>
                                <td align="center" valign="middle" class="comeIn">
                                    <a href="/lms/main/cloud/cloudvmstart.php?system=<?=$blue_vm_unser[$a]?>_<?=$_SESSION['_user']['user_id']?>&nicnum=1&user_id=<?=$_SESSION['_user']['user_id']?>&cid=<?=$blue_vm_unser[$a] ?>&vlanid=<?=$vlanid?>" target="_new" title="进入对抗">
                                        <img src="../../themes/img/visio.gif" alt="进入对抗" title="进入对抗"/></a>
                                </td>
                            </tr>
                            <?php } ?>
                        <tr>
                            <td colspan="3" align="right">总计<?=$num?>条记录&nbsp;&nbsp;&nbsp;</td>
                        </tr>
                        <?php }else{ ?>
                        <tr><td colspan="3" align="center">没有相关记录</td></tr>
                        <?php }?>
                </table>
            </span>
            </div><br>
        </div>
        <div class="ptab tab2 hide">
            <div class="content">
                <b>设置：</b><br>
                <span class="block">
<!--                    <table cellspacing="0" cellpadding="0" width="90%" border='0' class="t_table">-->
<!--                        <td colspan="3" align="right">-->
                            <?php
                            echo link_button ( 'create.gif', '添加' , 'setting.php?id='.$id.'&group='.$arrs['group_id'], '60%', '50%' ,true);
                            ?>
<!--                        </td>-->
<!--                    </table>-->
                     <table cellspacing="0" cellpadding="0" width="90%" border='1' class="t_table">
                         <?php
                         $count_setting=count($setting_data_list);
                         if($count_setting >0){
                             ?>
                             <tr>
                                 <th>编号</th>
                                 <th>模版名称</th>
                                 <th>用户</th>
                                 <th>用户组</th>
                                 <th>IP</th>
                                 <th>操作</th>
                             </tr>
                             <?php
                             for($c=0;$c<$count_setting;$c++){
                                 $d=$c+1;
                                 $user1=Database::getval("select username from user where user_id=".$setting_data_list[$c]['user_id'],__FILE__,__LINE__);
                                 $user2=Database::getval("select firstname from user where user_id=".$setting_data_list[$c]['user_id'],__FILE__,__LINE__);
                                 $tem_name=Database::getval("select name from vmdisk where id=".$setting_data_list[$c]['template_id'],__FILE__,__LINE__);
                                 ?>
                                 <tr>
                                     <td align="center"><?=$d?></td>
                                     <td align="center"><?=$tem_name?></td>
                                     <td align="center"><?=$user1."(".$user2.")"?></td>
                                     <td align="center"><?=$arrs['task_group']."(".$type.")"?></td>
                                     <td align="center"><?=$setting_data_list[$c]['ip']?></td>
                                     <td align="center"><a href="template_info.php?action=delete&id=<?=$id?>&a=<?=$setting_data_list[$c]['id']?>"><img src="../../themes/img/delete.gif"></a></td>
                                 </tr>
                                 <?php } ?>
                             <tr>
                                 <td colspan="10" align="right">总计<?=$count_setting?>条记录&nbsp;&nbsp;&nbsp;</td>
                             </tr>
                             <?php }else{ ?>
                             <tr><td colspan="10" align="center">没有相关记录</td></tr>
                             <?php }?>
                     </table>
          </span>
            </div>
            <div class="content">
                <b>成绩提交单：</b><br><span  class="block">请<a href="counterwork_report.php">点击进入</a>报告提交界面!</span>
            </div>
        </div><br>
      </div>
    </div>
</div>
<script type="text/javascript">
        $(document).ready(function(){
            $('#tab li').click(function(){
                $(this).addClass("on").siblings().removeClass("on");
                $("#tab_link > li").eq($('#tab li').index(this)).addClass("on").siblings().removeClass("on");
                $("#tab_link > li").fadeOut('normal').eq($('#tab li').index(this)).fadeIn('normal');
            });
        });
</script>
</section>
