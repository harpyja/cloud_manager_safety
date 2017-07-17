<?php
$cidReset = true;
include_once ("inc/app.inc.php");

include_once (api_get_path ( LIBRARY_PATH ) . 'attachment.lib.php');
include_once ("inc/page_header.php");

$tbl_anno = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'bulletin.php', "name" => "我的课程公告" );

if (is_equal ( $_GET ["todo"], "view" )) {
	$announcement = SystemAnnouncementManager::get_announcement ( getgpc('id') );
	$interbreadcrumb [] = array ("url" => 'bulletin.php?todo=view&id=' . $announcement->id, "name" => $announcement->title );
}
Display::display_thickbox ( false, true );
?>
<style>
<!--
.course_home .anno tr {
	border: 1px solid #B9CDE5;
}

.course_home .anno td {
	color: #4D4D4D;
	font-size: 14px;
	line-height: 28px;
	text-indent: 15px;
}
-->
</style>

<?php
display_tab ();
?>

<div class="body_banner_down">
		<!--by changzf add 40 line-->
<div style="float: left; width:300px">&nbsp;</div>
	<a href="bulletin.php" class="label dd2"
	style="margin-left: 55px; _margin-left: 25px;">系统公告</a>
<div style="float: left" class="dd2">|</div>
<a href="course_notice.php" class="label dd2"><span
	style="font-weight: bold; font-size: 14px">课程公告</span></a>
<div style="float: left" class="dd2">|</div>
<a href="user_profile.php" class="label dd2">个人信息及设置</a>
<div style="float: left" class="dd2">|</div>
<a href="user_center.php" class="label dd2">修改密码</a>
<div style="float: left" class="dd2">|</div>
<!-- <a href="learning_progress.php" class="label dd2">短消息</a> --></div>

<div class="index">

<div class="body_bread dd2" style="margin-top: 0px; margin-bottom: 8px;">
<?=display_interbreadcrumb ( $interbreadcrumb, NULL )?>
</div>

<div class="course_home" style="width: 100%">

<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title"><img src="images/list10.jpg"
	style="float: left; margin: 6px 5px 0 10px;" />
<div style="float: left;" class="de4">我的课程公告</div>
<div style="float: right;"></div>
</div>
</div>
<div style="clear: both; height: 0px; overflow: hidden;"></div>
<table cellspacing="0" class="anno" style="width: 100%">
	<tbody>
<?php
$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$sqlwhere = " t2.visibility=1 ";
$offset = (is_not_blank ( $_REQUEST ["offset"] ) ? getgpc ( "offset" ) : "0");

$sql = "SELECT COUNT(*) FROM $tbl_announcement AS t1, $tbl_item_property AS t2 WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'";
$sql .= " AND t1.cc IN ( SELECT t3.course_code FROM " . $table_course_user . " AS t3 WHERE t3.user_id=" . Database::escape ( $user_id ) . ")";
if ($sqlwhere) $sql .= " AND " . $sqlwhere;
$total_rows = Database::get_scalar_value ( $sql );

$sql = "SELECT	t1.*, t2.*,t1.cc AS course_code,t4.title AS course_title, t1.id AS anno_id FROM $tbl_announcement AS t1, $tbl_item_property AS t2, $table_course AS t4 WHERE t1.id = t2.ref AND t2.tool='" . TOOL_ANNOUNCEMENT . "'";
$sql .= " AND t1.cc IN ( SELECT t3.course_code FROM " . $table_course_user . " AS t3 WHERE t3.user_id=" . Database::escape ( $user_id ) . ") ";
$sql .= " AND t1.cc=t4.code ";
if ($sqlwhere) $sql .= " AND " . $sqlwhere;
$sql .= " GROUP BY t2.ref ORDER BY end_date DESC ";
$sql .= " LIMIT " . $offset . "," . NUMBER_PAGE;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$url = "course_notice.php";
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUM_PAGE );
$pagination = new Pagination ( $pagination_config );
$index = 1;
while ( $info = Database::fetch_array ( $res, 'ASSOC' ) ) {
	?>
<tr>
			<td><?=$index?>. <a class="thickbox"
				href="<?php
	echo api_get_path ( WEB_CODE_PATH ) . "announcements/show_all.php?todo=view&id=" . $info ['anno_id'] . "&cidReq=" . $info ['course_code'];
	?>&KeepThis=true&TB_iframe=true&height=450&width=100%"
				target="_blank"><?=$info ['title']?></a></td>
			<td><a href="course_home.php?cidReq=<?=$info ['course_code']?>"><?php
	echo $info ['course_title'];
	?></a></td>
			<td><?php
	echo $info ['end_date']?></td>
		</tr>
<?php
	$index ++;
}
?></tbody>
</table>

<div class="Pagination" style="float: right"><span class="f_l f6"
	style="margin-right: 10px;">总计 <b><?=$total_rows?></b> 条记录</span><?php
	echo $pagination->create_links ();
	?></div>
</div>

</div>

</div>
<?php
include_once ("inc/page_footer.php");
?>