<?php
$cidReset = true;
include_once ("inc/app.inc.php");

include_once (api_get_path ( LIBRARY_PATH ) . 'attachment.lib.php');
include_once ("inc/page_header.php");

$tbl_anno = Database::get_main_table ( TABLE_MAIN_SYSTEM_ANNOUNCEMENTS );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'bulletin.php', "name" => "公告中心" );

if (is_equal ( getgpc("todo"), "view" )) {
	$announcement = SystemAnnouncementManager::get_announcement ( intval(getgpc('id')) );
	$interbreadcrumb [] = array ("url" => 'bulletin.php?todo=view&id=' . $announcement->id, "name" => $announcement->title );
}

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
	width: 86%;
}
-->
</style>

<?php
display_tab ();
?>

<div class="body_banner_down">
	<!--by changzf add 41 line-->
	<div style="float: left; width: 300px;">&nbsp;</div>	
	<a href="bulletin.php" class="label dd2"
	style="margin-left: 55px; _margin-left: 25px;"><span
	style="font-weight: bold; font-size: 14px">系统公告</span></a>
<div style="float: left" class="dd2">|</div>
<a href="course_notice.php" class="label dd2">课程公告消息</a>
<div style="float: left" class="dd2">|</div>
<a href="user_profile.php" class="label dd2">个人信息及设置</a>
<div style="float: left" class="dd2">|</div>
<a href="user_center.php" class="label dd2">修改密码</a>
<div style="float: left" class="dd2">|</div>
<!-- <a href="learning_progress.php" class="label dd2">短消息</a> --></div>

<div class="index">

<div class="body_bread dd2" style="margin-top: 0px; margin-bottom: 8px;">
<?=display_interbreadcrumb ( $interbreadcrumb )?>
</div>

<?php
if (isset ( $_GET ['todo'] ) && $_GET ['todo'] == 'view') { //显示信息
	$created_user = $announcement->firstname;
	
	//是否有权限查看
	$canRead = false;
	//echo "user_id=".api_get_user_id();
	if (api_get_user_id ()) {
		if ($announcement->visible == 1) {
			$canRead = true;
		}
	}
	
	if ($canRead) {
		$http_www = api_get_path ( WEB_PATH ) . $_configuration ['attachment_folder'];
		
		$attachments = AttachmentManager::get_sys_attachment ( 'SYS_ANNOUNCEMENT', getgpc('id') );
		$has_attachment = (is_array ( $attachments ) && count ( $attachments ) > 0);
		if ($has_attachment) {
			if ($attachment = $attachments [0]) {
				$attachment_id = $attachment ['id'];
				$attachment_unique_name = $attachment ['name'];
				$attachment_name = $attachment ['old_name'];
				$attachment_uri = $attachment ['url'];
				$attachment_size = $attachment ['size'];
			}
		}
		?>
	<div class="register_title dc2" style="text-align: center;"><strong><?=$announcement->title?></strong></div>
<div class="emax_title de1"><span
	style="margin-right: 30px; float: right;">发布时间：<?=substr ( $announcement->date_start, 0, 16 )?>&nbsp;&nbsp; &nbsp; &nbsp;  发布人:<?=$created_user?></span></div>
<div style="width: 960px; margin: 0pt auto;">
<?=$announcement->content?>
</div>
<div style="margin-top: 40px">
<?php
		if ($has_attachment) echo get_lang ( "Attachment" ) . ': <a href="' . api_get_path ( WEB_CODE_PATH ) . 'course/download.php?doc_url=' . urlencode ( $attachment_uri ) . '">' . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)";
		?></div>
<?php
	} else {
		Display::display_warning_message ( get_lang ( 'NoPermission' ) );
	}
} else {
	
	?>
<div class="course_home">

<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title"><img src="images/list10.jpg"
	style="float: left; margin: 6px 5px 0 10px;" />
<div style="float: left;" class="de4">所有系统公告</div>
<div style="float: right;"></div>
</div>
</div>
<div style="clear: both; height: 0px; overflow: hidden;"></div>
<table cellspacing="0" class="anno" style="width: 100%">
	<tbody>
<?php
	$restrict_org_id = $_SESSION ["_user"] ["org_id"];
	$offset = (is_not_blank ( $_REQUEST ["offset"] ) ? getgpc ( "offset" ) : "0");
	
	$sqlwhere = "";
	$sql_cnt = "SELECT COUNT(*) FROM " . $tbl_anno . " WHERE visible=1 " . $sqlwhere;
	$total_rows = Database::get_scalar_value ( $sql_cnt );
	
	$sql = "SELECT *, DATE_FORMAT(date_start,'%Y-%m-%d') AS display_date FROM " . $tbl_anno . "	WHERE visible=1 " . $sqlwhere;
	$sql .= " ORDER BY date_start DESC";
	$sql .= " LIMIT " . $offset . "," . NUMBER_PAGE;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$url = "bulletin.php";
	
	$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUM_PAGE );
	$pagination = new Pagination ( $pagination_config );
	
	while ( $info = Database::fetch_array ( $res, 'ASSOC' ) ) {
		?>
<tr>
			<td><a href="?todo=view&id=<?=$info ['id']?>" target="_blank"><?php
		echo $info ['title'];
		?></td>
			<td><?php
		echo $info ['display_date']?></td>
		</tr>
<?php
	}
	?></tbody>
</table>

<div class="Pagination" style="float: right"><span class="f_l f6"
	style="margin-right: 10px;">总计 <b><?=$total_rows?></b> 条记录</span><?php
	echo $pagination->create_links ();
	?></div>
</div>

</div>
<?php
}
?>
</div>
<?php

include_once ("inc/page_footer.php");
?>
