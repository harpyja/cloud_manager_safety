<?php
/**
==============================================================================
 * 用户考核管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'work_attendance'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `work_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL COMMENT '用户名称',
  `name` varchar(128) NOT NULL COMMENT '姓名',
  `dept_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '部门名称',
  `sign_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '签到时间',
  `sign_return_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '签退时间',
  `mode` int(128) NOT NULL COMMENT '出勤状态',
  `status` int(11) NOT NULL COMMENT '结果',
  `range` int(11) NOT NULL COMMENT '上课时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='考勤表' AUTO_INCREMENT=0 ;";
    $result= api_sql_query ( $sql_insert,__FILE__, __LINE__ );
    if($result){
        // echo '考勤表不存在，已经新建完毕！';
    }
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
        $s.='完成考勤';
    }elseif($status==2){
        $s.='迟到';
    }else{
        $s.='旷课';
    }
    return $s;
}
if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'deletes' :
                $number_of_selected_users = count ( $_POST ['id'] );
                $number_of_deleted_users = 0;
                foreach ( $_POST ['id'] as $index => $id ) {
                    $sql = "DELETE FROM `vslab`.`work_attendance` WHERE id='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang('删除所选') . "id=" . $id;
                    api_logging ( $log_msg, 'labs', 'labs' );
                }
            break;
        //迟到
        case 'Belate' :
                $number_of_selected_users = count ( $_POST ['id'] );
                $number_of_deleted_users = 0;
                foreach ( $_POST ['id'] as $index => $id ) {
                    $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '2' WHERE  `work_attendance`.`id` ='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );
                }
            break;
        case 'Truancy' :
                $number_of_selected_users = count ( $_POST ['id'] );
                $number_of_deleted_users = 0;
                foreach ( $_POST ['id'] as $index => $id ) {

                    $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '0' WHERE  `work_attendance`.`id` ='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );
                }
            break;
        case 'normal_attendance' :
            $number_of_selected_users = count ( $_POST ['id'] );
            $number_of_deleted_users = 0;
            foreach ( $_POST ['id'] as $index => $id ) {

                $sql = "UPDATE  `vslab`.`work_attendance` SET  `status` =  '1' WHERE  `work_attendance`.`id` ='" . $id . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );
            }
            break;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    $get_keyword=  getgpc("keyword","G");
    if (is_not_blank ( $get_keyword )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $get_keyword='';
        }
        $keyword = Database::escape_string ( $get_keyword, TRUE );
        $sql_where .= " AND (username LIKE '%" . trim ( $keyword ) . "%' OR sign_date LIKE '%" . trim ( $keyword ) . "%' OR sign_return_date LIKE '%" . trim ( $keyword ) . "%')";
//        $sql_where .=' AND `range` <'.  strtotime ( $keyword );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_work_attendance() {
    $work_attendance = Database::get_main_table ( work_attendance );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $work_attendance;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_work_attendance_data($from, $number_of_items, $column, $direction) {
    $work_attendance = Database::get_main_table ( work_attendance );
    $sql = "select `id`, `id`, `username`, `sign_date`, `sign_return_date`, `mode`,`id`,`status` FROM  $work_attendance ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
        
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['lab_name'] ) && is_not_blank ( $_GET ['lab_name'] )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'work_attendance', 'get_number_of_work_attendance', 'get_work_attendance_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '序号', false );
$table->set_header ( $idx ++, '编号', false  ,null, array ('style' => ' text-align:center;width:10%' ));
$table->set_header ( $idx ++, '用户名', false, null, array ('style' => ' text-align:center;width:15%' ));
$table->set_header ( $idx ++, '签到时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '签退时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '状态', false, null, array ('style' => ' text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '上课时间', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '结果', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_column_filter ( 5, 'mode_filter' );
$table->set_column_filter ( 6, 'time_filter' );
$table->set_column_filter ( 7, 'status_filter' );
$actions = array ('deletes' => '删除所选项','Belate' => '更改状态为迟到','Truancy' => '更改状态为旷课','normal_attendance' => '更改状态为正常考勤');
$table->set_form_actions ( $actions );
?>

<aside id="sidebar" class="column users open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/main/admin/user/user_online.php">用户管理</a> &gt; 考勤管理</h4>

    <div class="managerSearch">
        <?php $form->display ();?><span style="color:red;margin-left:10px;">搜索小于输入的上课时间，单位为分钟</span>
        <span class="searchtxt right">
            <?php
            $report_count=Database::getval('SELECT COUNT(id) FROM `work_attendance`',__FILE__,__LINE__);
            if($report_count > 0){
                echo '&nbsp;&nbsp;' . link_button ( 'return.gif', '导出考勤', 'attendance_export.php', '60%', '50%' );
            }
            ?>
        </span>
        </span>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">

                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>
