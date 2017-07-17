<?php
$language_file = 'admin';
$cidReset = true;
include_once ('../inc/global.inc.php');
//api_protect_admin_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'system_announcements.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");
 
$tbl_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
$sys_attachment_path = api_get_path ( SYS_ATTACHMENT_PATH );
$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];
$id = intval(getgpc ( 'id' ));

$tool_name = get_lang ( 'SystemAnnouncements' );
$htmlHeadXtra [] = Display::display_thickbox ();
$form_action = getgpc ( "action" );
$language_file = array ('admin', 'registration' );$cidReset = true;

include ('../inc/header.inc.php');
        

$action=htmlspecialchars(getgpc ( "action","G" ));
if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(getgpc ( "licenseid","G" ));
            if ( isset($delete_id)){
                $sql="select course_category from course_license where id=$delete_id";
                $course_category=  Database::getval($sql);
                $sql="select filename from course_license where id=$delete_id";
                $filename=  Database::getval($sql);
                $sql="select count(*) from course_license where filename='$filename' and course_category !='$course_category'";
                $result = Database::getval($sql);
                if(!$result){
                    $filename=URL_ROOT."/www/lms/storage/course_license/".$filename;
                    if(file_exists($filename)){
                                $exec_var="rm  -rf ".$filename;
                                exec($exec_var);
                            }
                }
                $sql = "DELETE FROM `course_license` WHERE  `id` = {$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                $redirect_url = "course_license.php";
                tb_close($redirect_url);
            }
            break;
    }
}
        
function get_sqlwhere() {
    $sql_where = "";
    $get_keyword=  getgpc('keyword');
    if (is_not_blank ( $get_keyword )) {
        if($get_keyword=='输入搜索关键词'){
           $get_keyword='';
        }
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (`id` LIKE '%" . intval(trim ( $keyword )) . "%' OR `name` LIKE '%" . trim ( $keyword ) . "%'
        OR `description` LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_topo() {
    $course_license = Database::get_main_table (course_license);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $course_license."  where  1 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_topo_data($from, $number_of_items, $column, $direction) {
    $course_license = Database::get_main_table (course_license);
        
    $sql = "select  `id`, `course_category`,`filename`,`time`, `id`  FROM $course_license where 1 ";
        
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();
        $i=1;
    while ( $arr = Database::fetch_row ( $res) ) {
        $arr[0]=$i;
        $arr[1]='<span style="float:center"> '.Display::return_icon ( "course.gif" ) . '&nbsp;' . api_trunc_str2 ( $arr[1],50 ).'</span>';
         
         $arr[4]= "&nbsp;" . confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'course_license.php?action=delete&licenseid=' . $arr[4] );;
        $arrs [] = $arr;
        $i++;
    }
    return $arrs;
}


$table = new SortableTable ( 'labs', 'get_number_of_labs_topo', 'get_labs_topo_data',2, NUMBER_PAGE  );

//$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '编号',  false, null, null);
//$table->set_header ( $idx ++, '描述', false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, '课程分类名称', false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, '课程lincense文件', false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, '时间', false, null, array ('style' => 'width:20%' ));
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:20%' ));

//$table->set_form_actions ( array ('deletes' => '删除所选项' ,'visible' => '发布所选项','invisible' => '关闭所选项'), 'labs' );

?>

<aside id="sidebar" class="column systeminfo open"><div id="flexButton" class="closeButton close"></div></aside>


<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/message_list.php"> 系统管理</a>  
     &gt;  课程license管理 </h4>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                	<span class="searchtxt right">
                    <?php
                    echo link_button ( 'announce_add.gif', '上传', 'courselicense_upload.php?action=add', '80%', '80%' );
                  	?>
	 </span> 
            </div>
            <article class="module width_full hidden">
                 <?php $table->display ();?>
            </article>
        </div>
    </div>
</section>
</body>
</html>