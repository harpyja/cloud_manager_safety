<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );

$user_id = api_get_user_id ();
include_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
$is_course_user = (isset ( $_SESSION ["is_allowed_in_course"] ) && $_SESSION ["is_allowed_in_course"] ? TRUE : FALSE);
if (! $is_course_user) {
	api_not_allowed ();
}

$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );

$course_code = (isset ( $_GET ["code"] ) ? getgpc ( "code", "G" ) : $cidReq);
if (empty ( $course_code )) $course_code = api_get_course_code ();
?>
<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title">
    <h2>
        <img src="images/list10.jpg" style="float: left; margin: 6px 5px 0 5px;" />
        <div style="float: left;" class="de4">课程记录</div>
    </h2>
</div>
</div>

<!--<div class="tab_title">
<ul id="tab">

	<li class="de1 on" >课件学习</li>
	<?php
	if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true' && $have_exam) {
		?>
	<li class="de1" url="course_modules/progress_tab3.php">考试记录</li><?php
	}
	?>
</ul>
</div>-->
<div class="tab_content de1">
 <style>
     #tak_link li{
         list-style-type: none;
     }
 </style>
 

<div style="width: 660px;padding-left:5px">
	<table cellspacing="0" class="p-table"  border="1"  >
		<tr>
			<th class="dd2">课件名称</th>
			<th class="dd2">上次学习时间</th>
			<?php if($_configuration ['enable_display_courseware_track_info'] ){?>
			<th class="dd2">学习总时间</th>
			<th class="dd4">学习进度</th>
			<?php }?>
		</tr>
		<?php
		$sqlwhere = " visibility=1 AND t1.cc='" . escape ( $course_code ) . "' AND t2.user_id='" . escape ( $user_id ) . "'";
		$sql2 = "SELECT t1.*,t1.attribute AS lp_id,DATE_FORMAT(FROM_UNIXTIME(last_access_time),'%Y-%m-%d %H:%i') AS last_learn_time,t1.id AS cw_id,t1.title,t1.cw_type,t2.total_time,t2.progress FROM " . $tbl_courseware . " AS t1 LEFT JOIN " . $tbl_track_cw . " AS t2 ON t2.cw_id=t1.id WHERE " . $sqlwhere;
		//echo $sql2;
		$res2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
		while ( $data2 = Database::fetch_array ( $res2, "ASSOC" ) ) {
			$progress = ($data2 ['progress'] ? $data2 ['progress'] : '0');
 
			switch ($data2 ['cw_type']) {
				case 'scorm' :
					$url = api_get_path ( WEB_SCORM_PATH ) . 'lp_controller.php?cidReq=' . $course_code . '&action=read&lp_id=' . $data2 ["lp_id"] . '&cw_id=' . $data2 ["cw_id"];
					$icon = Display::return_icon ( 'scorm.gif', $data2 ['cw_type'], array ('style' => 'vertical-align: middle;' ) );
					break;
				case 'html' :
					$url = api_get_path ( WEB_CODE_PATH ) . 'courseware/link_goto.php?' . api_get_cidreq () . '&cw_id=' . $data2 ['cw_id'];
					$url = 'document_viewer.php?cw_id=' . $data2 ["cw_id"] . '&url=' . urlencode ( $url );
					$icon = Display::return_icon ( 'file_html.gif', $data2 ['cw_type'], array ('style' => 'vertical-align: middle;' ) );
					break;
				case 'link' :
					$url = api_get_path ( WEB_CODE_PATH ) . "courseware/link_goto.php?" . api_get_cidreq () . "&cw_id=" . $data2 ["cw_id"];
					$url = 'document_viewer.php?cw_id=' . $data2 ["cw_id"] . '&url=' . urlencode ( $url );
					$icon = Display::return_icon ( 'links.gif', $data2 ['cw_type'], array ('style' => 'vertical-align: middle;' ) );
					break;
				case 'media' :
					$url = api_get_path ( REL_PATH ) . "main/courseware/flv_player.php?cw_id=" . $data2 ["cw_id"] . "&target=_blank";
					$url = 'document_viewer.php?cw_id=' . $data2 ["cw_id"] . '&url=' . urlencode ( $url );
					$icon = Display::return_icon ( 'file_flash.gif', $data2 ['cw_type'], array ('style' => 'vertical-align: middle;' ) );
					break;
			}
			?>
		<tr>
			<td style="padding-left: 5px;">
				<?php
			echo '<a href="' . $url . '" target="_blank" style="float:left" title="' . $data2 ['title'] . '">' .$icon.'&nbsp;'. api_trunc_str2 ( $data2 ['title'] ) . '</a>';
			?></a></td>
			<td style="text-align: center;"><?php
			echo $data2 ['last_learn_time']?></td>
			<?php if($_configuration ['enable_display_courseware_track_info'] ){?>
			<td style="text-align: center;"><?php
			echo api_time_to_hms ( $data2 ['total_time'] );
			?></td>
			<td style="text-align: left; width: 160px; padding-left: 10px"
				class="percent">
			<div class="tiao"
				style="border: 1px solid black; width: 100px; float: left; margin-top: 5px">
			<div style="background: rgb(0,49,92); height: 14px; width: <?=$progress?>%;"></div>
			</div>
			<div style="float: left; width: 30px;"><?=$progress?>%</div>
			</td><?php }?>
		</tr>
		<?php
		} 
		?> 
	</table>
	</div>
	 

<?php
//if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true') {
//	?>
	<!--li>正在统计装载中，请稍候...</li--><?php
//}
?>

 
<div style="height: 0px; overflow: hidden; clear: both;"></div>
</div>
<script type="text/javascript">

$(document).ready(function(){
	$('#tab li').click(function(){
		$(this).addClass("on").siblings().removeClass("on");
		$("#tab_link > li").eq($('#tab li').index(this)).addClass("on").siblings().removeClass("on");
		if($(this).attr("url")!="undefined"){
			$("#tab_link > li").eq($('#tab li').index(this)).load($(this).attr("url"));
		}
		//$("#tab_link > li").slideUp('slow').eq($('#tab li').index(this)).slideDown('slow');
		$("#tab_link > li").fadeOut('normal').eq($('#tab li').index(this)).fadeIn('normal');
	});
});

</script>
