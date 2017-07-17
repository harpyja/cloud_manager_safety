<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
?>
<div class="course_title_frm" style="margin-top: 10px;">
	<div class="course_title">
		<img src="images/list10.jpg"
			style="float: left; margin: 6px 5px 0 10px;" />
		<div style="float: left;" class="de4">学习课件</div>
		<div style="float: right;"></div>
	</div>
</div>
<div style="clear: both; height: 0px; overflow: hidden;"></div>



<div class="tab_title">
	<ul id="tab">
		<li class="de1 on">课程主要内容</li>
		<li class="de1">在线学习网址</li>
		<li class="de1">视频教程</li>
	</ul>
</div>
<div class="tab_content de1">
	<ul id="tab_link">
		<li class="on">
			<table cellspacing="0" class="directory">
<?php
Display::display_thickbox ( false, true );

//SCORM标准课件
$row_index = 0;
$sql2 = "SELECT t2.id AS lp_id,t2.name AS lp_name,t2.content_maker,t1.id AS cw_id FROM  " . $table_courseware . " AS t1 LEFT JOIN " . $tbl_lp . " AS t2 ON t1.attribute=t2.id WHERE t1.visibility=1 AND t1.cw_type='scorm'  AND t1.cc='" . escape ( getgpc ( "cidReq", "G" ) ) . "'  ORDER BY t2.display_order";
//echo $sql2;
$res2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
while ( $data2 = Database::fetch_array ( $res2, "ASSOC" ) ) {
	//$url = WEB_QH_PATH . 'quiz_intro.php?' . api_get_cidreq () . "&exerciseId=" . $data2 ['quiz_id'];
	$vis = api_get_item_visibility ( api_get_course_info ( $course_code ), TOOL_LEARNPATH, $data2 ["lp_id"] );
	if ($vis == 1) {
		$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $data2 ['cw_id'] );
		$lp_url = api_get_path ( WEB_SCORM_PATH ) . 'lp_controller.php?cidReq=' . $course_code . '&action=read&lp_id=' . $data2 ["lp_id"] . '&cw_id=' . $data2 ["cw_id"];
		?>
	<tr>
					<td class="jie"><a target="_blank" href="<?=$lp_url?>"> <img
							src="../../themes/img/scorm.gif" />
		<?=$data2 ['lp_name']?></a></td>
					<td class="test"><?php
		//if (! empty ( $data2 ["quiz_id"] )) {
		?>
						<!-- <a href="<?=$url?>" target="_blank"><img src="images/test.png"
			style="float: left; margin: 5px 5px 0 0;" />课后练习</a>  -->
		<?php //}						?></td>
					<td class="percent">
						<div class="tiao">
							<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
						</div>
						<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
					</td>
				</tr>
	<?php
		unset ( $progress );
	}
}
?></table>
		</li>


		<li>
			<table cellspacing="0" class="directory">
	<?php
	//非标准zip课件包
	$pacakge_list = get_all_courseware_data ( $course_code, 'html', false );
	if (is_array ( $pacakge_list ) && count ( $pacakge_list ) > 0) {
		$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . '/document';
		while ( list ( $key, $id ) = each ( $pacakge_list ) ) {
			$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $key );
			$row = array ();
			$size = $id ["size"];
			$document_name = $id ['title'];
			$path = $id ['path'];
			$default_page = $id ['default_page'];
			$url = api_get_path ( WEB_CODE_PATH ) . 'courseware/link_goto.php?' . api_get_cidreq () . '&cw_id=' . $key;
			$vis = api_get_item_visibility ( api_get_course_info ( $course_code ), TOOL_COURSEWARE_PACKAGE, $key );
			if ($vis == 1) {
				?>
	<tr>
					<td class="jie"><?php
				echo '<div style="float:left;margin-right:10px">' . Display::return_icon ( 'file_html.gif', get_lang ( 'Links' ) ) . '</div>';
				echo '<a href="' . $url . '" target="_blank">' . $document_name . '</a>';
				?></td>
					<td class="test"></td>
					<td class="percent">
						<div class="tiao">
							<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
						</div>
						<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
					</td>
				</tr>
	<?php
			}
		}
	}
	
	//网页链接
	$TABLE_ITEM_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$sqlLinks = "SELECT * FROM " . $table_courseware . " WHERE cw_type='link' AND visibility=1 AND cc=" . Database::escape ( $course_code );
	$result = api_sql_query ( $sqlLinks, __FILE__, __LINE__ );
	$numberofzerocategory = Database::num_rows ( $result );
	if ($numberofzerocategory > 0) {
		while ( $myrow = Database::fetch_array ( $result, 'ASSOC' ) ) {
			$vis = api_get_item_visibility ( api_get_course_info ( $course_code ), TOOL_LINK, $myrow ['id'] );
			if ($vis == 1) {
				$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $myrow ['id'] );
				?>
	<tr>
					<td class="jie"><?php
				echo "<a href=\"" . api_get_path ( WEB_CODE_PATH ) . "courseware/link_goto.php?" . api_get_cidreq () . "&cw_id=", $myrow ['id'], "\" target=\"_blank\">", Display::return_icon ( 'file_html.gif', get_lang ( 'Links' ) ), "</a>&nbsp;&nbsp;";
				echo " <a href=\"" . api_get_path ( WEB_CODE_PATH ) . "courseware/link_goto.php?" . api_get_cidreq () . "&cw_id=", $myrow ['id'], "\" target=\"_blank\">", api_htmlentities ( $myrow ['title'], ENT_NOQUOTES, SYSTEM_CHARSET ), "</a>\n";
				?></td>
					<td class="test"></td>
					<td class="percent">
						<div class="tiao">
							<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
						</div>
						<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
					</td>
				</tr>
	<?php
			}
		}
	}
	?>
	</table>
		</li>

		<li>
			<table cellspacing="0" class="directory">
	<?php
	//视频教程
	$pacakge_list = get_all_courseware_data ( $course_code, 'media', false );
	if (is_array ( $pacakge_list ) && count ( $pacakge_list ) > 0) {
		$sortable_data = array ();
		while ( list ( $key, $data ) = each ( $pacakge_list ) ) {
			$progress = $objStat->get_courseware_progress ( $user_id, $course_code, $key );
			$row = array ();
			$size = $data ["size"];
			$document_name = $data ['title'];
			$path = $data ['path'];
			$open_target = '_self';
			?>
	<tr>
					<td class="jie"><?php
			echo '<div style="float:left;margin-right:10px">' . Display::return_icon ( 'file_flash.gif', get_lang ( 'FLV' ), array ('align' => 'middle', 'hspace' => '5' ) ) . '</div>';
			echo create_media_link ( $key, $http_www, $document_name, $path, $size, $open_target, $data ['visibility'] );
			?></td>
					<td class="test"></td>
					<td class="percent">
						<div class="tiao">
							<div style="background: rgb(0,49,92); height: 13px; width: <?=$progress?>%;"></div>
						</div>
						<div style="float: left; width: 30px;" class="de1"><?=$progress?>%</div>
					</td>
				</tr>
	<?php
		}
	}
	?>
	</table>
		</li>

	</ul>
	<div style="height: 0px; overflow: hidden; clear: both;"></div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#tab li').click(function(){
		$(this).addClass("on").siblings().removeClass("on");
		$("#tab_link > li").eq($('#tab li').index(this)).addClass("on").siblings().removeClass("on");
		$("#tab_link > li").fadeOut('normal').eq($('#tab li').index(this)).fadeIn('normal');
	});
});
</script>

