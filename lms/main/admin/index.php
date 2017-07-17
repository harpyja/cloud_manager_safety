<?php
/*
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-15
 * Time: 下午4:59
 * To change this template use File | Settings | File Templates.
 */
header("Content-Type:text/html;charset=utf-8");

include ('../../main/inc/global.inc.php');
require_once ("../../portal/sp/inc/commons.lib.php");
api_protect_admin_script ();
include ('../inc/header.inc.php');
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$objCrsMng = new CourseManager ();

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( getgpc("keyword","G"))) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (title LIKE '%" . trim ( $keyword ) . "%' OR code LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank (getgpc ( 'category_id', 'G' ) )) {
        $sql_where .= " AND category_code=" . Database::escape (intval(getgpc ( 'category_id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_courses() {
    $course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
    $sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table AS t1 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
        
    //return Database::getval ( $sql, __FILE__, __LINE__ );
    return 10;
}
//by changzf at 90-146 line on 2012/06/08
function preview_filter($code) {
    $html = "";
    $html .= icon_href ( 'course_home.gif', 'CourseHomePage', api_get_path ( WEB_PATH ) . PORTAL_LAYOUT . 'course_home.php?cidReq=' . $code, '_blank' );
    return $html;
}

function  describe_filter($code) {
    $html = "";
    $html .= link_button ( 'synthese_view.gif', 'Info', 'course/course_information.php?code=' . $code, '70%', '80%', FALSE );
    return $html;
}

function  Delete_filter($code) {

    $desc = 'select description12 from course where code='.$code;
    $description12  = Database::getval ( $desc, __FILE__, __LINE__ );

    $lessonedit="/etc/lessonedit";
    $lessonedit=file_get_contents($lessonedit);
    $lessonedit+=0;
    if($lessonedit == '1'){
        $html = "";
        $html .="&nbsp;" . confirm_href ( 'delete.gif', '你确定要执行此操作？', 'Delete', 'course_list.php?delete_course=' . $code );
        return $html;

    }else{
        if($description12 =='1'){
            $html = "";
            $html .="&nbsp;" . confirm_href ( 'delete.gif', '你确定要执行此操作？', 'Delete', 'course_list.php?delete_course=' . $code );
            return $html;
        }else{
            $html = "";
            $html .="默认";
            return $html;
        }
    }
}
function  Reporting_filter($code) {
    $html = link_button ( 'statistics.gif', 'Tracking', '../reporting/stat_course_user.php?cidReq=' . $code, '70%', '80%', FALSE );
    return $html;
}

function Content($code) {
    return Display::display_course_content ( $code, TRUE, '_self' );
}
function Announcements($code) {
    return Display::display_course_announcements ( $code, TRUE, '_self' );
}
function Documents($code) {
    return Display::display_course_documents ( $code, TRUE, '_self' );
}
function LearningDocument($code) {
    return Display::display_course_LearningDocument ( $code, TRUE, '_self' );
}
function CourseWork($code) {
    return Display::display_course_CourseWork ( $code, TRUE, '_self' );
}
function CourseExamination($code) {
    return Display::display_course_CourseExamination( $code, TRUE, '_self' );
}
function title_filter($title) {
    $html = '<div style="text-align:left">'.$title.'</div>';
    return $html;
}

//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        // 批量删除课程
        case 'delete_courses' :
            $deleted_course_count = 0;
            $course_codes = intval(getgpc('course'));
            if (count ( $course_codes ) > 0) {
                foreach ( $course_codes as $index => $course_code ) {
                    if (CourseManager::delete_course ( $course_code, false )) $deleted_course_count ++;
                }
            }
            if (count ( $course_codes ) == $deleted_course_count) {
                Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/course/course_list.php' );
            } else {
                Display::display_msgbox ( '某些课程没有成功删除,可能原因是你没有删除的权限!', 'main/admin/course/course_list.php', 'warning' );
            }
            break;
    }
}

//处理删除课程
if (isset ( $_GET ['delete_course'] )) {
    $course_info = CourseManager::get_course_information (getgpc("delete_course","G") );
    if (! can_do_my_bo ( $course_info ['created_user'] )) {
        Display::display_msgbox ( '对不起,你没有操作的权限!', 'main/admin/course/course_list.php', 'warning' );
    }
    $delete_policy = get_setting ( 'permanently_remove_deleted_files' ); //永久删除文件
    $rtn = CourseManager::delete_course ( getgpc("delete_course","G"), $delete_policy == "true" ? FALSE : TRUE );
    Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/course/course_list.php' );
}

$tool_name = get_lang ( 'CourseList' );

//Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '{element} ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ( 'class' => 'inputText' ) );

$sql = "SELECT category_code,count(*) FROM $tbl_course GROUP BY category_code";
$category_cnt = Database::get_into_array2 ( $sql );

$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
$cate_options [""] = "---所有分类---";
foreach ( $category_tree as $item ) {
    $cate_name = $item ['name'] . (($category_cnt [intval($item ['id'])]) ? "&nbsp;(" . $category_cnt [intval($item ['id'])] . ")" : "");
    $cate_options [intval($item ['id'])] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$form->addElement ( 'select', 'category_id', get_lang ( 'CourseCategory' ), $cate_options, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );

     ?>   
     <section id="main" class="column" style="width:96%;padding-left:2%;">
      <h4 class="page-mark">当前位置：平台首页</h4>
      <link href="../../themes/css/exerice.css" type="text/css" rel="stylesheet"/>      
    <div class="all">
      <div class="all-inner">
	   <div class="row-fluid">
<?php
    $filestr=file_get_contents('/tmp/statusinfo');
    $filearr=explode(' ',$filestr);
    $result5=mysql_query('select count(*) as num from vmtotal');
    $numarr=mysql_fetch_assoc($result5);
    $result6=mysql_query('select count(*) as linnum from track_e_online');
    $linearr=mysql_fetch_assoc($result6);    
?>               
	       <div class="span2 box-quick-link blue-background" style="margin-left:0px;">
		        <div class="user-top">
				   <i class=" user-list user-cpu-title"></i>
				    CPU使用率
				</div>
				<div class="user-img">
				   <img src="../../themes/images/cpu-img.png">
                                   <span class="user-font user-cpu"><?php if(empty($filearr[2])){echo "0%";}else{echo $filearr[2];}?></span>
				</div>
				
		   </div>

		   <div class="span2 box-quick-link blue-background">
		        <div class="user-top">
				  <i class=" user-list user-neicun-title"></i>
				内存使用率</div>
				<div class="user-img">
				   <img src="../../themes/images/neicun-img.png">
                                   <span class="user-font user-neicun"><?php if(empty($filearr[3])){echo "0%";}else{echo $filearr[3];}?></span>
				</div>
				
		   </div>

		    <div class="span2 box-quick-link blue-background">
		        <div class="user-top">
				    <i class=" user-list user-cipan1-title"></i>
				磁盘使用率1</div>
				<div class="user-img">
				   <img src="../../themes/images/cipan1-img.png">
                                   <span class="user-font user-cipan1"><?php echo empty($filearr[0]) ? "0%" : $filearr[0];?></span>
				</div>
				
		   </div>

		    <div class="span2 box-quick-link blue-background">
		        <div class="user-top">
				  <i class=" user-list user-cipan2-title"></i>
				磁盘使用率2</div>
				<div class="user-img">
				   <img src="../../themes/images/cipan2-img.png">
                                   <span class="user-font user-cipan2"><?php echo empty($filearr[1]) ? "0%" : $filearr[1];?></span>
				</div>
				
		   </div>
                    <div class="span2 box-quick-link blue-background">
		        <div class="user-top">
				   <i class=" user-list user-xuni-title"></i>
				在线虚拟机数量</div>
				<div class="user-img">
				   <img src="../../themes/images/xuni-img.png">
                                   <span class="user-font user-xuni"><a href="vmmanage/vmmanage_iframe.php"><?php echo empty($numarr['num']) ? "0" : $numarr['num'];?></a></span>
				</div>
				
		   </div>
		    <div class="span2 box-quick-link blue-background">
		        <div class="user-top">
				  <i class=" user-list user-zaixian-title"></i>
				在线用户数量</div>
				<div class="user-img">
				   <img src="../../themes/images/zaixian-img.png">
                                   <span class="user-font user-zaixian"><a href="user/user_online.php"><?php echo empty($linearr['linnum']) ? "0" : $linearr['linnum'];?></a></span>
				</div>
				
		   </div>
	   </div>
	</div>
  </div>
        <div id="function-main" class="function-main" style="width:96%;"> 
        <div class="maintool f0">
            <div style="border:1px solid #999999;height:280px;width:98%;border-radius:6px 6px 6px;">
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-desktop"></div>
                <dl class="cloudlab-map-list">
                    <dt>用户桌面</dt>
                    <dd><a href="<?=URL_APPEND?>user_portal.php">我管理的课程</a></dd>
                    <?php if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){?>
                    <dd><a href="<?=URL_APPEND?>main/exam/exam_corrected_list.php">我批改的考卷</a></dd>
                    <?php } ?> 
                </dl>
            </div>
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-course"></div>
                <dl class="cloudlab-map-list">
                    <dt>课程管理</dt>
                    <dd><a href="<?=URL_APPEND?>main/admin/course/course_list.php">课程管理</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/course/course_plan.php">课程调度</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/course/course_user_manage.php">调度查看</a></dd>


                    <?php if(api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' AND $_configuration ['enable_module_survey']){?>
                    <dd><a href="<?=URL_APPEND?>main/admin/course/course_category_iframe.php">课程分类</a></dd>
                    <?php } ?>
                </dl>
            </div>
            <?php if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){?>
             <div class="Coludlab-map">
                 <div class="Coludlab-map-icon map-exam"></div>
                 <dl class="cloudlab-map-list">
                    <dt>考试管理</dt>
                    <dd><a href="<?=URL_APPEND?>main/exam/pool_iframe.php">题库管理</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/exercice/question_base.php">所有考试</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/exercice/exercice.php">综合考试</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/exercice/exercice.php?type=2">课程考试</a></dd>

                </dl>
            </div>
                    <?php } ?> 
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-statistics"></div>
                <dl class="cloudlab-map-list">
                    <dt>查询统计</dt>
                    <dd><a href="<?=URL_APPEND?>main/reporting/learning_progress.php">学习情况</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/survey/index.php">调查问卷</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/user/user_online.php">在线用户</a></dd>
                    <?php if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){?>
                    <dd><a href="<?=URL_APPEND?>main/reporting/query_quiz.php">成绩查询</a></dd>
                    <?php } ?>
                </dl>
            </div>
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-user"></div>
                <dl class="cloudlab-map-list">
                    <dt>用户管理</dt>
                    <?php if(isRoot()){?>
                        <dd><a href="<?=URL_APPEND?>main/admin/user/user_list.php">用户管理</a></dd>
                    <?php } ?>
                    <dd><a href="<?=URL_APPEND?>main/admin/dept/dept_iframe.php">组织管理</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/user/user_list_audit.php">审核用户</a></dd>
                </dl>
            </div>
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-cloud"></div>
                <dl class="cloudlab-map-list">
                    <dt>云管理</dt>
                    <dd><a href="<?=URL_APPEND?>main/admin/vmmanage/vmmanage_iframe.php">虚拟化管理</a></dd>
                    <?php if(isRoot()){?>
                        <dd><a href="<?=URL_APPEND?>main/admin/net/vm_list_iframe.php">网络拓扑设计</a></dd>
                        <dd><a href="<?=URL_APPEND?>main/admin/vmmanage/centralized.php">集中管理设置</a></dd>
                        <dd><a href="<?=URL_APPEND?>main/admin/cloud/clouddesktop.php">云桌面终端</a></dd>
                    <?php } ?>
                </dl>
            </div>
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-log"></div>
                <dl class="cloudlab-map-list">
                    <dt>路由交换</dt>
                    <dd><a href="<?=URL_APPEND?>main/admin/router/labs_topo.php">网络拓扑设计</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/router/labs_device.php">网络设备管理</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/router/labs_mod.php">路由交换模块</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/router/labs_ios.php">路由交换管理</a></dd>
                </dl>
            </div>
            <?php if(isRoot()){?>
            <div class="Coludlab-map">
                <div class="Coludlab-map-icon map-setting"></div>
                <dl class="cloudlab-map-list">
                    <dt>系统管理</dt>
                    <dd><a href="<?=URL_APPEND?>main/admin/misc/settings.php">系统设置</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/misc/system_upgrade.php">系统升级</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/misc/system_management.php">系统管理</a></dd>
                    <dd><a href="<?=URL_APPEND?>main/admin/systeminfo.php">系统信息</a></dd>
                </dl>
            </div>
            </div>
            <div id="table_line" style="width:98%;height:930px;margin-top:10px;border:1px solid #999999;border-radius:6px 6px 6px;">

       <div style="width:98%;height:50px;margin:20px 1% 0 1%;">   
<?php 
        $allip=mysql_query('select id,ip_location from run_chart group by ip_location');
        $Usestr=1;
        while($ip_location=mysql_fetch_assoc($allip))
         {
            if($Usestr==1){
            $ip_location_1=$ip_location['ip_location'];
            $ip_location_id=$ip_location['id'];
                           }
             $all_id[]=$ip_location['id'];
?>                
             <div id="iplocation<?php echo $ip_location['id'];?>" style="width:20%;height:50px;text-align:center;line-height:50px;float:left;"><a href="javascript:void(0)" onclick="iplist('<?php echo $ip_location['ip_location'];?>',<?php echo $ip_location['id'];?>);"><?php echo $ip_location['ip_location'];?></a></div>
<?php
          $Usestr++;      
         }
         $js_allid=json_encode($all_id);
        $all_ip_line=ceil(($Usestr-1)/5);
        $table_line_h=880+50*$all_ip_line;
        $function_main_h=1200+50*$all_ip_line;
?>                      
       </div>  
   <script type="text/javascript" src="../../themes/js/jquery-1.5.2.min.js"></script>
   <script type="text/javascript" src="../../themes/js/highcharts.js"></script>
   <script type="text/javascript" src="../../themes/js/dark-green.js"></script>
   <script type="text/javascript" src="../../themes/js/exporting.js"></script>
   <script type="text/javascript">
        
        $(function(){ 
           $('#function-main').css('height','<?php echo $function_main_h;?>px');
           $('#table_line').css('height','<?php echo $table_line_h;?>px');
          $.ajax({
               type:'POST',
                url:'list.php',
               data:'ip_location=<?php echo $ip_location_1;?>',
           dateType:'html',
           success:function(er){
               $('#iplist').html(er);
               cycle(<?php echo $ip_location_id;?>);
               $('#iplocation<?php echo $ip_location_id;?>').css("background","#D8E9F1");
           }
            });
        });
        function iplist(ip_location,id){
            $.ajax({
               type:'POST',
                url:'list.php',
               data:'ip_location='+ip_location,
           dateType:'html',
           success:function(er){
               $('#iplist').html(er);
               cycle(id);
               $('#iplocation'+id).css('background','#D8E9F1');
           }
            });
        }
        function cycle(id)
        {   
            var js_arr=<?php echo $js_allid?>;
     
    for(var i=0;i<=js_arr.length;i++)
          {
              if(id!==js_arr[i]){
                $('#iplocation'+js_arr[i]).css('background','#F8F8F8');  
              }
          }
        }
   </script>   
<div id="iplist"> 
   </div>
                <div id="Coludlab-map-cpu" class="Coludlab-map-line" style="width:98%; height:400px;margin:0px 1% 0px 1%;"></div>
                <div id="Coludlab-map-online" class="Coludlab-map-line" style="width:98%; height:400px;margin:0px 1%;"></div>                
            </div>
         </div>  
     </div>
 </section

<?php } ?> 
</body>
</hrml>
