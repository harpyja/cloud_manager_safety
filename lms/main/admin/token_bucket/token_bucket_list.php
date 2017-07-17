<?php
/**
==============================================================================
 * token bucket
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();
//if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/admin/token_bucket/token_bucket_list.php';


if(mysql_num_rows(mysql_query("SHOW TABLES LIKE token_bucket"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `token_bucket` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `types` varchar(50) NOT NULL,
      `token_bucket_name` varchar(128) NOT NULL,
      `ranges` varchar(256) NOT NULL,
      `parameter` varchar(256) NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

function types_filter($types){
    $result="";
    if($types=='1'){
        $result='系统默认';
    }else{
        $result='自定义';
    }
    return $result;
}
function ranges_filter($ranges){
    $results = unserialize($ranges);
    $result=$results[0]." 至 ".$results[1];

    return $result; 
}

 function status_filter($token_bucket_name) {
    $result = "";
    $result .= '&nbsp;' . link_button ( 'chat.gif', '', 'token_bucket_status.php?id='.$token_bucket_name);
    return $result;
}    
function delete_filter($id) {
    $result = "";
    $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'token_bucket_list.php?action=delete_token_bucket&id=' . $id );
    return $result;
} 
function del_tb($id){  //删除令牌桶同时删除表  --zd
    $sql="select `token_bucket_name` from `token_bucket`  where id=".$id;    
    $token_bucket_name=  Database::getval($sql);   
    $sq="drop table `".$token_bucket_name."`";    
     $re=api_sql_query ( $sq, __FILE__, __LINE__ );      
}
function token_name_filter($name){  //当有该令牌桶却没有该令牌桶的数据表时要创建表---zd
    $sql="select ranges from token_bucket where token_bucket_name='".$name."'";
    $rangess =  Database::getval($sql);
    $ranges  =  unserialize($rangess);
    $ranges1=$ranges[0];
    $ranges2=$ranges[1];
        $ranges=$ranges1-1;

    //判断建表
    if(mysql_num_rows(mysql_query("SHOW TABLES LIKE `".$name."`"))!=1){
        $sql_insert="CREATE TABLE if not exists `".DB_NAME."`.`$name`(
            `Pid` INT NOT NULL AUTO_INCREMENT ,
            `status` smallint(6),
            `values` varchar(256) ,
            PRIMARY KEY ( `Pid` )) ENGINE =MyISAM  charset=utf8 auto_increment=".$ranges;
        api_sql_query ( $sql_insert,__FILE__, __LINE__ );
    }
    $count_sql=Database::getval("select count(*) from `".$name."`",__FILE__,__LINE__);

    //插入记录
    if($count_sql==0){
        for($pid=$ranges1;$pid<=$ranges2;$pid++){
            $sql1="insert into `$name` (`Pid`,`status`,`values`) values(".$pid.",'0','0');";
            api_sql_query ( $sql1, __FILE__, __LINE__ );
        }
    }
    return $name;
}

if (isset ( $_GET ['action'] )) {
    switch ($_GET ['action']) {
        case 'delete_token_bucket' :
            if ( $_GET ['action'] =='delete_token_bucket') {
                $id = intval(getgpc('id'));
                
                 del_tb($id);         
                $sql = "DELETE FROM `".DB_NAME."`.`token_bucket` WHERE `token_bucket`.`id` = $id";
                    $re=api_sql_query ( $sql, __FILE__, __LINE__ );   
                     if($re){
                            tb_close ( "token_bucket_list.php");
                            }else {
                                echo "false!!!";
                            } 
            }
            break; 
    }
} 
if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        // 批量删除课程
        case 'delete_token_buckets' :
            $deleted_vm_count = 0;
            $vm_id = $_POST['networkmap'];
            if (count ( $vm_id ) > 0) {
                foreach ( $vm_id as $index => $id ) {
                      del_tb($id);
                    $sql = "DELETE FROM `".DB_NAME."`.`token_bucket` WHERE id='" . $id . "'";
                   $re=api_sql_query ( $sql, __FILE__, __LINE__ ); 
                           
                    $log_msg = get_lang('删除所选') . "id=" . $id;
//                    api_logging ( $log_msg, 'networkmap', 'dfgdfgdfg' );
                } 
            tb_close ( "token_bucket_list.php"); 
            } 
    }
}

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc('keyword','G'), TRUE );
        $sql_where .= " AND (id LIKE '%" . intval( $keyword ) . "%'
                           OR token_bucket_name LIKE '%" . trim ( $keyword ) . "%'
                           OR ranges LIKE '%" . trim ( $keyword ) . "%' )";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_vmdisk() {
    $vmdisk = Database::get_main_table (token_bucket);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $vmdisk;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
//changzf
function get_vm_data($from, $number_of_items, $column, $direction) {
    $token_bucket = Database::get_main_table (token_bucket); 
    
    $sql = "select id,token_bucket_name,ranges,types,id,id FROM  $token_bucket ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
        
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
$keyword_tip = "序号/名称/范围" ;
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "", 'class' => 'inputText', 'title' => $keyword_tip,'value'=>'输入搜索关键词','id'=>'searchkey') );
        
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

$table = new SortableTable ( 'vmdisk', 'get_number_of_vmdisk', 'get_vm_data',2, NUMBER_PAGE  );
//$table->set_additional_parameters ( $parameters );

//$actions = array ('delete' => get_lang ( 'BatchDelete' ) );
//$table->set_form_actions ( $actions );

$idx = 0;
//$table->set_header ( $idx ++, '', false, null,array ('style' => 'width:100px;' ) );
$table->set_header ( $idx ++, '序号', false, null  );
$table->set_header ( $idx ++, '类别', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '令牌桶范围' , false, null, array ('style' => 'width:25%;text-align:center' ) ); 
$table->set_header ( $idx ++, '状态', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_form_actions ( array ('delete_token_buckets' => '删除所选项' ), 'networkmap' );

$table->set_column_filter (1, 'token_name_filter' );
$table->set_column_filter (2, 'ranges_filter' ); 
$table->set_column_filter (3, 'types_filter' );
$table->set_column_filter (4, 'status_filter' );
$table->set_column_filter ( 5, 'delete_filter' );
//Display::display_footer ( TRUE );
?>
<aside id="sidebar" class="column cloud open"> <div id="flexButton" class="closeButton close">  </div> </aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a>
        &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a>
        &gt; 令牌桶管理</h4>
    <div class="managerSearch">
      <?php  $form->display (); ?>
		<span class="searchtxt right">
                    <?php echo '&nbsp;&nbsp;' . link_button ( 'view_more_stats.gif', '新建令牌桶', 'token_bucket_add.php?action=add&edit_id='.$id, '70%', '80%' );?>
        </span>
    </div>
    <article class="module width_full hidden">
        <table cellpadding="0" cellspacing="0" class="p-table">
            <tbody>
          <?php
            $table->display ();
            ?>
            </tbody>
        </table>
    </article>
</section>
</body>
</html>
