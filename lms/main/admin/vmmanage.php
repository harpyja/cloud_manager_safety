<?php
/**
 * This is Virtualization management page
 * by changzf
 * on 2012/06/10
 */
$language_file = array ('admin' );
$cidReset = true;
include ('../../main/inc/global.inc.php');
api_protect_admin_script ();

$sql = "SELECT COUNT(user_id) FROM " . Database::get_main_table ( TABLE_MAIN_USER );
$user_count = Database::get_scalar_value ( $sql );

$sql = "SELECT COUNT(user_id) FROM " . Database::get_main_table ( TABLE_MAIN_USER_REGISTER ) . " WHERE reg_status=" . AUDIT_REGISTER_INIT;
$reg_user_count = Database::get_scalar_value ( $sql );

$sql = "SELECT COUNT(code) FROM " . Database::get_main_table ( TABLE_MAIN_COURSE );
$course_count = Database::get_scalar_value ( $sql );

$sql="SELECT(id) FROM ".Database::get_main_table(TABLE_QUIZ_TEST)." WHERE active=1";
$quiz_count= Database::get_scalar_value ( $sql );

$sql="SELECT(id) FROM ".Database::get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS)." WHERE visible=1";
$anno_count= Database::get_scalar_value ( $sql );

$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
$sql = "SELECT SUM(total_time) FROM " . $tbl_track_cw ;
$total_learning_time=api_time_to_hms(Database::get_scalar_value ( $sql ));

$sql2=$sql." WHERE MONTH(FROM_UNIXTIME(last_access_time))=MONTH(NOW())";
$total_learning_time2=api_time_to_hms(Database::get_scalar_value ( $sql2 ));

$this_year=date('Y');
$sql="SELECT COUNT(login_id) FROM ".Database::get_main_table(TABLE_STATISTIC_TRACK_E_LOGIN)." WHERE YEAR(login_date)=".$this_year;
$total_login_count= Database::get_scalar_value ( $sql );

$htmlHeadXtra [] ='<style type="text/css">
* {padding: 0px;margin: 0px auto;}

html,body {width: 100%;height: 100%;}

body {height: 100%;font-size: 12px;font-family: Verdana, Arial, Helvetica, sans-serif;overflow: hidden;z-index: 1;}

.allmenu {background: #FFF;_border: 2px solid #CCC;text-align: center;width: 80%;height: auto;margin: 10px auto;}

.allmenu .header {padding: 2px;background: #B31000;color: #FFF;line-height: 19px;font-weight: bold;margin-bottom: 3px;text-indent: 3px;text-align: left;padding:5px;font-size:14px;}

.allmenu .datalist {text-align: left;width: 100%;border-collapse: collapse;}

.allmenu .datalist td {text-align: left;border: 1px dotted #CCC;padding-top: 4px;padding-bottom: 4px;padding-left: 8px;}

.allmenu .datalist .td1 {background-color: #EEE;width: 20%;}

.allmenu .allmenu-box {width: 98%;margin: 5px auto;text-align: left;overflow: hidden;padding-left: 2px;}

.maptop {float: left;width: 125px;overflow: hidden;border-right: 1px solid #EEE;border-left: 1px solid #EEE;border-bottom: 1px solid #EEE;margin-left: -1px;width: 115px;}

.maptop dt.bigitem {padding: 2px;/*background: #455656;*/background: #B31000;color: #FFF;line-height: 19px;font-weight: bold;margin-bottom: 3px;text-indent: 3px;}

.mapitem dt {line-height: 21px;font-weight: bold;text-indent: 10px;background: #EFF1F1;}

.mapitem ul {margin-top: 2px;margin-bottom: 5px;}

.mapitem ul li {text-indent: 13px;line-height: 19px;background: url(arrr.gif) 4px 6px no-repeat;}

.allmenu a {color: #5C604F;text-decoration: none;}

.allmenu a:hover {color: #F63;}
.box{width:48%;float:left;border:2px solid #CCCCCC;min-height:250px;margin-left:10px}

</style>';
Display::display_header ();

function get_sqlwhere() {
    global $is_required_course;
    $sql_where = "";
    if (isset ( $_GET ['keyword'] ) && ! empty ( $_GET ['keyword'] )) {
        $keyword = Database::escape_str ( getgpc ( 'keyword' ), TRUE );
        $sql_where .= " AND (course.code LIKE '%" . $keyword . "%' OR course.title LIKE '%" . $keyword . "%') ";
    }

    if (isset ( $_GET ['id']) && is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND vmstartinfo.id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    return $sql_where;
}

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) {
    $parameters ['keyword'] = getgpc ( 'keyword', 'G' );
}

function get_number_of_vm() {
    $vm_info = Database::get_main_table ( vmstartinfo);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $vm_info;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function Status() {
    $result = '开启';
    return $result;
}

function Addres($addres) {
    $localhost='127.0.0.1';
    if(!$addres){
        $result= $localhost;
    }else{
        $result=$addres;
    }
    return $result;
}

function Vm_id($id) {
    if($id){
        $result= $id+100;
    }
    return $result;
}
function get_vm_data($from, $number_of_items, $column, $direction) {
    $vm_info = Database::get_main_table (vmstartinfo);

    $sql =  "select id as col10,addres as col11,id as col12,nicnum as col13,system as col14,user_id as col15,lesson_id as col16,stat_id as col17,group_id as col18 FROM  $vm_info  ";

    $sql_where = get_sqlwhere ();
   if ($sql_where) {$sql .= $sql_where;}

    $sql .= " LIMIT $from,$number_of_items";
    //echo $from.'<br/>'.$number_of_items;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }

    foreach($vms as $key=>$value)
    {
        foreach($value as $k=>$v)
        {
          '$arr['.$key.']['.$k.']='.$v;
        }
    }
    return $vms;
}

//$table = new SortableTable ( 'vmstartinfo', 'get_number_of_vm', 'get_vm_data',0,20, 'DESC' );
$table = new SortableTable ( 'vmstartinfo', 'get_number_of_vm', 'get_vm_data',0,10,NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );

//$idx = 0;
$table->set_header ( 0, '编号', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 1, 'hypervisor地址', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 2, '虚拟机编号',false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 3, '网络接口', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 4, '系统类型', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 5, '用户', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 6, '课程编号', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 7, '状态', false, null, array ('style' => 'text-align:center' ) );
$table->set_header ( 8, '用户组', false, null, array ('style' => 'text-align:center' ) );
$table->set_column_filter ( 1, 'Addres' );
$table->set_column_filter ( 2, 'Vm_id' );
$table->set_column_filter ( 7, 'Status' );

$table->display ();


Display::display_footer(TRUE);
