<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );

Display::display_thickbox ( false, true );
?>
<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title"><img src="images/list10.jpg"
	style="float: left; margin: 6px 5px 0 10px;" />
<div style="float: left;" class="de4">课程公告消息</div>
<div style="float: right;"></div>
</div>
</div>
<div style="clear: both; height: 0px; overflow: hidden;"></div>

<div class="tab_content de1" style="border-top: #b9cde5 1px solid;min-height:250px">
<table cellspacing="0" class="directory">
	<?php
	
	$index = 1;
	while ( $data = Database::fetch_array ( $result_notice, 'ASSOC' ) ) {
		
		?>
	<tr>
		<td class="jie"><?=$index?>. <a class="thickbox"
			href="<?php
		echo api_get_path ( WEB_CODE_PATH ) . "announcements/show_all.php?todo=view&id=" . $data ['anno_id'] . "&cidReq=" . $course_code;
		?>&KeepThis=true&TB_iframe=true&height=90%&width=960"
			target="_blank" title="<?=$data ['title']?>"><?=api_trunc_str2($data ['title'])?></a></td>
		<td class="test"></td>
		<td class="percent"><?=$data ['end_date']?></td>
	</tr>
	<?php
		$index ++;
	}
	?>
	</table>
</div>
