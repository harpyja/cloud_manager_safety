<?php
/**----------------------------------------------------------------

 liyu: 2011-10-17
 *----------------------------------------------------------------*/
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );
require_once (api_get_path ( SYS_CODE_PATH ) . 'assignment/assignment.lib.php');
//Display::display_thickbox ( false, true );
?>
  

<div class="tab_content de1" style="border-top: #b9cde5 1px solid;margin-top:10px; min-height: 250px">
	<table cellspacing="0"  style="width: 100%" border="1" class="p-table">
		<tr>
			<th>序号</th>
			<th>作业名称</th>
			<th>发布时间</th>
			<th>截收时间</th>
			<th>状态</th>
			<!-- <th class="dd4">成绩</th> -->
		</tr>
	<?php
	$index = 1;
         $data = Database::fetch_array ( $result_assignment, 'ASSOC' );
	while($data) {
		$isSubmitted = is_submitted_assignment ( $user_id, $data ['id'] );
		$isFeedback = is_feedback_assignment ( $user_id, $data ['id'] );
		$isSubmitted ? $class = 'class="invisible"' : $class = '';
		$status_html = '<span ' . $class . '>';
		if ($isSubmitted) {
			$status_html .= '已提交';
			$status_html .= ($isFeedback ? ",已批改" : ",未批改");
		} else {
			$status_html .= '未提交';
		}
		
		$sql = "SELECT status FROM " . $tbl_assignment_submission . " WHERE student_id='" . escape ( $user_id ) . "' AND assignment_id='" . escape ( $data ['id'] ) . "'";
		$sql .= " AND cc='" . escape ( $course_code ) . "' ";
		$submission_status = Database::get_scalar_value ( $sql );
		if ($submission_status == 2) $status_html .= '&nbsp;(已退回)';
		$status_html .= '</span>'; 
		?>
		<tr>
			<td style="text-align: center;"><?=$index?></td>
			<td style="padding-left: 10px;"><a class="thickbox" title="<?=$data ['title']?>"
				href="<?=api_get_path ( WEB_CODE_PATH ) . "assignment/assignment_info_stud.php?id=" . $data ['id'] . "&cidReq=" . $course_code?>&KeepThis=true&TB_iframe=true&height=90%&width=960&modal=true"
				title="<?=$data ['title']?>"><?=api_trunc_str2 ( $data ['title'] )?></a></td>
			<td style="text-align: center;"><?=substr ( $data ['published_time'], 0, 10 )?></td>
			<td style="text-align: center;"><?=substr ( $data ['deadline'], 0, 10 )?></td>
			<td style="text-align: center;"><?=$status_html?></td>
		</tr>
	<?php
        $index ++; 
	}
        if($data==''){
            echo "<tr><td colspan='10'>没有相关课程作业</td></tr>";
        }
	?>
	</table>
</div>