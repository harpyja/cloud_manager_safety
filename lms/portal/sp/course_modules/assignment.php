<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );
require_once (api_get_path ( SYS_CODE_PATH ) . 'assignment/assignment.lib.php'); 
?>
<style>
     .u-course-title{
	border-bottom:1px solid #DDDDDD;
        display:block;
        clear:both;
        content:"";
}

</style>
    <div class="j-list lists" id="j-list"> 
        <div class="u-content">
    <?php
    $index = 1;
    $sql="select * from  crs_assignment_main  where cc=".getgpc('cidReq');
    $result_assignment=  api_sql_query($sql);
    while ($res = Database::fetch_array ( $result_assignment, 'ASSOC' )){
        $datas[]=$res;
    }
    
    if($datas!=''){
    ?>

            <h3 class="sub-simple "></h3> 
                <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                    <tr>
                            <th width="10%" align="center">序号</th>
                            <th width="10%" align="center">作业名称</th>
                            <th width="10%" align="center">发布时间</th>
                            <th width="10%" align="center">截收时间</th>
                            <th width="10%" align="center">操作</th>
                            <th width="10%" align="center">状态</th>
                    </tr>
                    <tr>
                        <td colspan="10"><h3  class="sub-simple u-course-title"></h3> </td>
                    </tr>
    <?php
                }

        foreach ($datas as $data){
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
			<td style="text-align: center;"><?=$data ['title']?></td>
			<td style="text-align: center;"><?=substr ( $data ['published_time'], 0, 10 )?></td>
			<td style="text-align: center;"><?=substr ( $data ['deadline'], 0, 10 )?></td>
			<td style="text-align: center;"><?=link_button ( 'message_normal.gif', '提交作业', api_get_path ( WEB_CODE_PATH ) . "assignment/assignment_info_stud.php?id=" . $data ['id'] . "&cidReq=" . $course_code, '80%', '70%', FALSE );  ?></td>
                        <td style="text-align: center;"><?=$status_html?></td>
		</tr>
                <tr>
                        <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
                <?php
                $index ++;

        }
        if($data!=''){
        ?>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <span class="l">
                            总计：<strong><?=$index-1?></strong> 条记录
                        </span>
                    </td>
                </tr>
            </tfoot>    
        <?php
        }
        if($data==''){
            echo "<center><tr><td colspan='10'>没有相关课程作业</td></tr></center>";
        }
	?>
	</table>
    </div>
</div>

