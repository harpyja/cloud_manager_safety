<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
Display::display_thickbox ( false, true );
?>
<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title"><img src="images/list10.jpg"
	style="float: left; margin: 6px 5px 0 10px;" />
<div style="float: left;" class="de4">学习内容</div>
<div style="float: right;"></div>
</div>
</div>
<div style="clear: both; height: 0px; overflow: hidden;"></div>


<div class="tab_content de1"
	style="border-top: #b9cde5 1px solid; min-height: 250px">
<ul id="tab_link">
	<table cellspacing="0" class="directory">
<?php
$sql = "SELECT * FROM $table_courseware WHERE visibility=1 AND cc=" . Database::escape ( getgpc ( "cidReq", "G" ) ) . " ORDER BY display_order ASC,id DESC";
$all_courseware = api_sql_query_array ( $sql, __FILE__, __LINE__ );
$row_index = 0;
foreach ( $all_courseware as $cw_info ) {
	if ($cw_info ['cw_type'] == 'scorm') { //SCORM标准课件
		//$sql2 = "SELECT t2.id AS lp_id,t2.name AS lp_name,t2.content_maker FROM  " . $tbl_lp . " AS t2 WHERE t2.id=" . Database::escape ( $cw_info ['attribute'] );
		//$sql2 .= "  ORDER BY t2.learning_order ASC";
		//$data2 = api_sql_query_one_row ( $sql2, __FILE__, __LINE__ );
		$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $cw_info ['id'] );
		$lp_url = api_get_path ( WEB_SCORM_PATH ) . 'lp_controller.php?cidReq=' . $course_code . '&action=read&lp_id=' . $cw_info ['attribute'] . '&cw_id=' . $cw_info ["id"];
		?>
		<tr>
			<td class="jie">
			<?=Display::return_icon ( 'scorm.gif', 'SCORM标准课件', array ('style' => 'vertical-align: middle;' ) ) . '&nbsp;&nbsp;'?><a
				target="_blank" href="<?=$lp_url?>" title="<?=$cw_info ['title']?>"><?=api_trunc_str2 ( $cw_info ['title'] )?></a>
			</td>	<?php
		if ($_configuration ['enable_display_courseware_track_info']) {
			?>
				<td class="percent">
			<div class="tiao">
			<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
			</div>
			<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
			</td><?php
		}
		?>
			</tr>
		<?php
		unset ( $progress );
	} elseif ($cw_info ['cw_type'] == 'html') {
		$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . '/document';
		$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $cw_info ["id"] );
		$document_name = $cw_info ['title'];
		$path = $cw_info ['path'];
		$default_page = $cw_info ['default_page'];
		$url = api_get_path ( WEB_CODE_PATH ) . 'courseware/link_goto.php?' . api_get_cidreq () . '&cw_id=' . $cw_info ["id"];
		?>
		<tr>
			<td class="jie"><?php
		echo Display::return_icon ( 'file_html.gif', 'HTML课件包', array ('style' => 'vertical-align: middle;' ) ) . '';
		echo '&nbsp;&nbsp;<a href="document_viewer.php?cw_id=' . $cw_info ["id"] . '&url=' . urlencode ( $url ) . '" target="_blank" title="' . $document_name . '">' . api_trunc_str2 ( $document_name ) . '</a>';
		?></td><?php
		if ($_configuration ['enable_display_courseware_track_info']) {
			?>
				<td class="percent">
			<div class="tiao">
			<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
			</div>
			<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
			</td><?php
		}
		?>
			</tr>
		<?php
	} elseif ($cw_info ['cw_type'] == 'media') { //视频教程
		$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $cw_info ["id"] );
		$document_name = $cw_info ['title'];
		$url = api_get_path ( REL_PATH ) . "main/courseware/flv_player.php?cw_id=" . $cw_info ["id"] . "&target=_blank";
		$url = 'document_viewer.php?cw_id=' . $cw_info ["id"] . '&url=' . urlencode ( $url );
		?>
		<tr>
			<td class="jie"><?php
		echo Display::return_icon ( 'file_flash.gif', '视频点播课件', array ('style' => 'vertical-align: middle;' ) );
		echo '&nbsp;&nbsp;<a href="' . $url . '" target="_blank" title="' . $document_name . '">' . api_trunc_str2 ( $document_name ) . '</a>';
		?></td>
<?php
		if ($_configuration ['enable_display_courseware_track_info']) {
			?>
				<td class="percent">
			<div class="tiao">
			<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
			</div>
			<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
			</td><?php
		}
		?>
			</tr>
		<?php
	} elseif ($cw_info ['cw_type'] == 'link') { //网页链接
		$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $cw_info ["id"] );
		$url = api_get_path ( WEB_CODE_PATH ) . "courseware/link_goto.php?" . api_get_cidreq () . "&cw_id=" . $cw_info ["id"];
		$url = 'document_viewer.php?cw_id=' . $cw_info ["id"] . '&url=' . urlencode ( $url );
		?>
		<tr>
			<td class="jie"><?php
		echo "<a href=\"" . $url . "\" target=\"_blank\">", Display::return_icon ( 'links.gif', '网页链接课件', array ('style' => 'vertical-align: middle;' ) ), "</a>";
		echo "&nbsp;&nbsp;<a href=\"" . $url . "\" target=\"_blank\" title=\"" . $cw_info ['title'] . "\">", api_trunc_str2 ( $cw_info ['title'] ), "</a>\n";
		?></td>
		<?php
		if ($_configuration ['enable_display_courseware_track_info']) {
			?>
				<td class="percent">
			<div class="tiao">
			<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
			</div>
			<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
			</td><?php
		}
		?>
			</tr>
		<?php
	}
}
?>
	</table>

</ul>
<div style="height: 0px; overflow: hidden; clear: both;"></div>
</div>

