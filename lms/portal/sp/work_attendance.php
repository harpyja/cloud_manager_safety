<?php
/**----------------------------------------------------------------

liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'work_attendance'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `work_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL COMMENT '用户名称',
  `name` varchar(128) NOT NULL COMMENT '姓名',
  `dept_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '部门名称',
  `sign_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '签到时间',
  `sign_return_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '签退时间',
  `mode` int(128) NOT NULL COMMENT '出勤状态',
  `status` int(11) NOT NULL COMMENT '状态',
  `range` int(11) NOT NULL COMMENT '上课时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='考勤表' AUTO_INCREMENT=0 ;";
    $result= api_sql_query ( $sql_insert,__FILE__, __LINE__ );
//    if($result){
//         echo '考勤表不存在，已经新建完毕！';
//    }
}

function mode_filter($mode){
    $result = "";
    if($mode==1){
        $result.='签到成功';
    }elseif($mode==2){
        $result.='签退成功';
    }else{
        $result.='旷课';
    }
    return $result;
}

function time_filter($id){
    $sql="select sign_date,sign_return_date from work_attendance where id =".$id;
    //echo $sql;

    $res=api_sql_query($sql,__FILE__,__LINE__);
    $dates=Database::fetch_row($res);
    $startdate= $dates[0];
    $enddate= $dates[1];
    if($enddate!='0000-00-00 00:00:00'){
        $minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
        return $minute;
    }else{
        return '0';
    }
}
/**select sign_date from work_attendance where id =
SELECT DATEDIFF('2008-8-21,'2009-3-21');

 **/
function status_filter($status){
    $s='';
    if($status==1){
        $s.='迟到';
    }elseif($status==2){
        $s.='旷课';
    }else{
        $s.='完成考勤';
    }
    return $s;
}
$p_action=  getgpc("action");
$p_id=  getgpc('id');
if (isset ( $p_action )) {
    switch ($p_action) {
        case 'deletes' :
            $number_of_selected_users = count ( $p_id );
            $number_of_deleted_users = 0;
            foreach ( $p_id as $index => $id ) {
                $sql = "DELETE FROM `vslab`.`work_attendance` WHERE id='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );

                $log_msg = get_lang('删除所选') . "id=" . $id;
                api_logging ( $log_msg, 'labs', 'labs' );
            }
            break;
        case 'Belate' :
            $number_of_selected_users = count ( $p_id );
            $number_of_deleted_users = 0;
            foreach ( $p_id as $index => $id ) {

                $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '1' WHERE  `work_attendance`.`id` ='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );
            }
            break;
        case 'Truancy' :
            $number_of_selected_users = count ( $p_id );
            $number_of_deleted_users = 0;
            foreach ($p_id as $index => $id ) {

                $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '2' WHERE  `work_attendance`.`id` ='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );
            }
            break;
        case 'normal_attendance' :
            $number_of_selected_users = count ( $p_id );
            $number_of_deleted_users = 0;
            foreach ( $p_id as $index => $id ) {

                $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '0' WHERE  `work_attendance`.`id` ='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );
            }
            break;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    $keywords=  getgpc("keyword");
    if (is_not_blank ($keywords )) {
        if($keywords=='输入搜索关键词'){
            $keywords='';
        }
        $keyword = Database::escape_string ( $_GET['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%'
                             OR username LIKE '%" . trim ( $keyword ) . "%'
                             OR sign_date LIKE '%" . trim ( $keyword ) . "%'
                             OR sign_return_date LIKE '%" . trim ( $keyword ) . "%'
                             OR mode LIKE '%" . trim ( $keyword ) . "%'
                             OR status LIKE '%" . trim ( $keyword ). "%')";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}




function get_number_of_work_attendance() {
    $work_attendance = Database::get_main_table ( work_attendance );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $work_attendance." WHERE `username`='".$_SESSION['_user']['username']."'";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_work_attendance_data($from, $number_of_items, $column, $direction) {
    $work_attendance = Database::get_main_table ( work_attendance );
    $sql = "select  `id`, `username`, `sign_date`, `sign_return_date`, `mode`,`id`,`status` FROM  $work_attendance WHERE `username`='".$_SESSION['_user']['username']."'";


    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND  " . $sql_where;



    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
 //   echo $sql;

    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'user_profile.php', "name" => "用户中心" );
$interbreadcrumb [] = array ("url" => 'work_attendance.php', "name" => "我的考勤" );
//$nameTools="我的课程";
display_tab ( TAB_LEARNING_CENTER );

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();


$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


if (isset ( $keywords ) && is_not_blank ( $keywords )) $parameters ['keyword'] = getgpc ( 'keyword' ); 

$table = new SortableTable ( 'work_attendance', 'get_number_of_work_attendance', 'get_work_attendance_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
//$table->set_header ( $idx ++, '序号', false );
$table->set_header ( $idx ++, '编号', false  ,null);
$table->set_header ( $idx ++, '用户名', false, null, array ('style' => ' text-align:center;width:15%' ));
$table->set_header ( $idx ++, '签到时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '签退时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '状态', false, null, array ('style' => ' text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '上课时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '状态', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_column_filter ( 4, 'mode_filter' );
$table->set_column_filter ( 5, 'time_filter' );
$table->set_column_filter ( 6, 'status_filter' );
//$actions = array ('deletes' => '删除所选项','Belate' => '更改状态为迟到','Truancy' => '更改状态为旷课','normal_attendance' => '更改状态为正常考勤');
//$table->set_form_actions ( $actions );
?>
<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
  <!--左侧-->
  <div class="b-30"></div>
	<div class="g-container f-cb">
        <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                    <li class="navitm it f-f0 f-cb haschildren cur" data-id="-1" data-name="用户中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                         <a class="f-thide f-f1" title="用户中心" style="background: #13a654;color:#FFF">用户中心</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="信息修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="信息修改" href="user_profile.php">信息修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="密码修改" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="密码修改" href="user_center.php">密码修改</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的考勤" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的考勤" href="work_attendance.php"style="color:green;font-weight:bold">我的考勤</a>
                    </li>
                    
                </ul>
            </div>
        </div>


<div class="g-mn1" > 
         <div class="g-mn1c m-cnt" style="display:block;">
             <div class="top f-cb j-top">
                 <h3 class="left f-thide j-cateTitle title">
                 <span class="f-fc6 f-fs1" id="j-catTitle">我的考勤</span>
                 </h3>
             </div>
    <div class="j-list lists" id="j-list"> 
    <div class="managerSearch" style="width:98.8%;margin-left:0px;">
        <?php $form->display ();?>

    </div>
   
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">

                <?php $table->display ();?>
            </table>
        </form>
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
    body{
        min-height:80%;
    }
    #searchkey{
        height:30px;
    }
    
    th{
        text-align:center;
    }
</style>
</html>




