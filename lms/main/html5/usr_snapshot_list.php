<?php
include_once ("../../portal/sp/inc/app.inc.php");
include_once ("../../portal/sp/inc/page_header.php");

$id=$_GET['id'];
if($id) {
    $desc = 'delete from snapshot where id='.$id;
    $res= api_sql_query ( $desc, __FILE__, __LINE__ );
    Header("Location:  usr_snapshot_list.php");
}

if (isset ( $_GET ["keyword"] ) && is_not_blank ( $_GET ["keyword"] )) {
	if($_GET ['keyword']=='输入搜索关键词'){
	    $_GET ['keyword']='';
	}
	$keyword = Database::escape_str ( urldecode ( $_GET ['keyword'] ), TRUE );
	$sql_where .= " AND (title LIKE '%" . $keyword . "%')";
	$param .= "&keyword=" . urlencode ( $keyword );
}

if ($param {0} == "&") $param = substr ( $param, 1 );

$userid=api_get_user_id();
$sql = "SELECT COUNT(*)	FROM snapshot where user_id=$userid";
$total_rows = Database::get_scalar_value ( $sql );
$sql="select * from snapshot where user_id=$userid";
$user_snapshot_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
$objStat = new ScormTrackStat ();
$pagination_config = Pagination::get_defult_config ( $total_rows,WEB_QH_PATH."usr_snapshot_list.php",'', NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );
$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'learning_center.php', "name" => "学习中心" );
$interbreadcrumb [] = array ("url" => 'learning_progress.php', "name" => "学习档案" );

display_tab ( TAB_LEARN_PROGRESS );
?>
<aside id="sidebar" class="column open study-Centre">
    <div id="flexButton" class="closeButton close"> </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?= display_interbreadcrumb ( $interbreadcrumb )?></h4>
    <div class="search">
        <form action="learning_progress.php" method="get">
            <input type="text" name="keyword"    value="输入搜索关键词" id="searchkey"  onfocus="this.select();" />
            <input type="submit" value="搜索"  id="searchbutton" class="submit" />
        </form>
    </div>
    <article class="module width_full hidden">
        <header><h3>学生截屏录屏资料</h3></header>    
				<?php
				if (is_array ( $user_snapshot_list ) && $user_snapshot_list) {
				?>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                   <th>文件名</th><th>系统</th><th>课程</th><th>虚拟机</th><th>MAC地址</th><th>代理端口</th><th>类型</th><th>时间</th><th>操作</th>
                </tr>
                <?php
                foreach ( $user_snapshot_list as $snapshot ) {
                           $id=$snapshot['id'];
                           $addres=$snapshot['addres'];
                           $system=$snapshot['system'];
                           $user_id=$snapshot['user_id'];
                           $lesson_id=$snapshot['lesson_id'];
                           $vmid=(int)$snapshot['vmid'];
                           $port=(int)$snapshot['port'];
                           $mac_id=(int)$snapshot['mac_id'];
                           $proxy_port=(int)$snapshot['proxy_port'];
                           $status=(int)$snapshot['status'];
                           $type=(int)$snapshot['type'];
                           $filename=$snapshot['filename'];
                           $time=$snapshot['time'];
                    $progress = $objStat->get_course_progress ( $course_code, $user_id ) . '%';
                    ?>
                    <tr>
                                <td><a onclick='openWindow(1024,768,"iframe:/lms/storage/snapshot/<?=$filename?>.jpg","" )' ><img alt="" src="/lms/storage/snapshot/<?php echo $filename;?>_s.jpg"></a></td>
                                <td><?php echo $system;?></td>
                                <td><?php echo $lesson_id;?></td>
                                <td><?php echo $vmid;?></td>
                                <td><?php echo $mac_id;?></td>
                                <td><?php echo $proxy_port;?></td>
                                <td><?php if($type==1){ echo '截屏文件'; }else{ echo'录屏文件'; } ?> </td>
                      <td><?php echo $time;?></td>
                      <td><a href=usr_snapshot_list.php?id=<?=$id?>><img src="../../themes/img/delete.gif" align="center"></a></td>
                    </tr>
                <?php  } 	?>
            </table>
            <div class="page">
                <ul class="page-list"><li class="page-num">总计<?=$total_rows?> 条记录</li><?php  echo $pagination->create_links (); ?> </ul>
            </div>
        </div>
    <?php
} else {
	echo '<div class="error"\>没有相关截屏录屏记录</div\>';
}
?>
