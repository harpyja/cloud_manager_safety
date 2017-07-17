<?php
$language_file = array ('announcements', 'admin' );
include_once ('../inc/global.inc.php');
include_once ('announcements.inc.php');
api_protect_course_script ();

$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
$tbl_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );

$cur_course_url = api_get_path ( WEB_COURSE_PATH ) . api_get_course_id () . "/";

$nameTools = get_lang ( 'AllAnnouncements' );
$objAnnouncement = new CourseAnnouncementManager ();
$htmlHeadXtra [] = get_table_style_ie6 ();
$interbreadcrumb [] = array ("url" => 'announcements.php', "name" => get_lang ( 'CourseAnnouncement' ) );
$interbreadcrumb [] = array ("url" => api_get_self (), "name" => $nameTools );

//if (isset ( getgpc('todo','G') ) && getgpc('todo','G') == 'view') {
if(isset($_GET['todo']) && $_GET['todo'] == 'view'){
 Display::display_header ( $tool_name, FALSE );
        
} else {
	//JQuery,Thickbox
	$htmlHeadXtra [] = Display::display_thickbox ();
	Display::display_header ( NULL, TRUE );
}

if (isset ( $_GET ['todo'] ) && getgpc('todo','G')== 'view') { //显示信息
	

	$announcement = $objAnnouncement->get_announcement (intval( getgpc ( 'id', 'G') ) );
	
	if ($announcement) {
		$title = $announcement ['title'];
		$content = $announcement ['content'];
		$content = make_clickable ( $content );
		$content = text_filter ( $content );
		$last_post_datetime = $announcement ['lastedit_date']; // post time format  datetime de mysql
		list ( $last_post_date, $last_post_time ) = split ( " ", $last_post_datetime );
		$sql = "SELECT * FROM " . $tbl_attachment . " WHERE TYPE='COURSE_ANNOUNCEMENT' AND ref_id='" . intval(getgpc ( 'id', 'G' )) . "'";
                $result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
		$has_attachment = (Database::num_rows ( $result1 ) > 0);
		
		//发送给
		$sql = "SELECT to_user_id,username,firstname FROM " . $tbl_item_property . " AS t1 LEFT JOIN " . $tbl_user . " AS t2 ON t1.to_user_id=t2.user_id WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . getgpc ( 'id', 'G' ) . "' AND visibility<>2 AND t2.user_id IS NOT NULL";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$num_to_user = Database::num_rows ( $res );
		while ( $toUser = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$to_user_ids .= $toUser ['to_user_id'] . ",";
			$to_user_names .= $toUser ['firstname'] . "(" . $toUser ['username'] . "),";
		}
		
		?>
<table width="98%" height="306" border="0" align="center"
	cellpadding="0" cellspacing="0">
	<tr>
		<td height="63" valign="top" bgcolor="#FFFFFF">
		<div align="center"><font color="#FF0000"><br>
		<br>
		<b><font size="5"><?=$title?></font></b></font></div>
		</td>
	</tr>
	<tr>
		<td height="16" valign="top" bgcolor="#FFFFFF" class=f2>
		<div align="right">&nbsp;&nbsp;<font color="#990000"></font> <?=get_lang ( 'AnnounceDate' )?>:<font
			color="#990000"><?=substr ( $last_post_datetime, 0, 16 )?></font> <font
			color="#990000"></font></div>
		</td>
	</tr>
	<tr>
		<td height="16" valign="top" bgcolor="#FFFFFF" class=f2>
		<div align="center">
		<hr size="2" color="#CC0000">
		</div>
		</td>
	</tr>
	<tr>
		<td valign="top">	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?=$content?>
		<p align="center">&nbsp;</p>
		</td>
	</tr>
	<tr>
		<td height="40" valign="bottom" bgcolor="#FFFFFF"><?=$num_to_user > 0 ? get_lang ( "SentTo" ) . ": " . $to_user_names : ""?> </td>
	</tr>
	<tr>
		<td height="40" valign="bottom" bgcolor="#FFFFFF"><?php
		if ($has_attachment) {
			if ($attachment = Database::fetch_array ( $result1, 'ASSOC' )) {
				$attachment_id = $attachment ['id'];
				$attachment_name = $attachment ['old_name'];
				$attachment_uri = $attachment ['url'];
				$attachment_size = $attachment ['size'];
			}
			if($attachment_uri) $attachment_uri=urlencode('storage/courses/'.api_get_course_code().'/'.$attachment_uri);
			//echo get_lang ( "Attachment" ) . ": <a href=\"" . $cur_course_url . $attachment_uri . "\">" . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)" ;round ( $row ['attachment_size'] / 1024,2 )
			echo get_lang ( "Attachment" ) . ': <a href="'.api_get_path(WEB_CODE_PATH).'course/download.php?doc_url=' .$attachment_uri . '">' . $attachment_name . "</a>(" .round($attachment_size/1024,2). "KB)";
		}
		?></td>
	</tr>

	<tr>
		<td height="12" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
	</tr>
</table>

<?php
	} else {
		Display::display_normal_message ( get_lang ( 'TheListIsEmpty' ) );
	}
}

Display::display_footer ();
?>