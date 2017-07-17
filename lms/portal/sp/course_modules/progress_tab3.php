<?php
include_once ("../inc/app.inc.php");
$user_id = api_get_user_id ();
include_once ('../../../main/exercice/exercise.class.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');

$is_course_user = (isset ( $_SESSION ["is_allowed_in_course"] ) && $_SESSION ["is_allowed_in_course"] ? TRUE : FALSE);
if (! $is_course_user) api_not_allowed ();

$course_code = (isset ( $_GET ["code"] ) ? getgpc ( "code", "G" ) : $cidReq);
if (empty ( $course_code )) $course_code = api_get_course_code ();

$objStat = new ScormTrackStat ();

$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
$exam_info = $objStat->get_course_exam_info ( $course_code );

$sql = "SELECT t1.* FROM " . $tbl_exam_rel_user . " AS t1 WHERE  t1.user_id=" . Database::escape ( $user_id ) . " AND t1.exam_id= " . Database::escape ( $exam_info ['id'] );
$resultExercices = api_sql_query ( $sql, __FILE__, __LINE__ );
$a_exercices = Database::fetch_array ( $resultExercices, "ASSOC" );
if ($a_exercices && $a_exercices ['attempt_times'] > 0) {
	//$attempt_times=Exercise::get_exam_user_attempts($v['id'], $user_id);
	?>
<div style="width: 480px;padding-left:100px">
<table cellspacing="0" class="tbl_course" style="width: 100%">
	<tr>
		<td style="text-align: right;padding-right:10px">考试总次数:</td>
		<td style="text-align: center;"><?=$a_exercices ['attempt_times']?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">首次考试成绩:</td>
		<td style="text-align: center;"><?=round($a_exercices ['first_attempt_score']/$a_exercices ['paper_score']*100)?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">首次考试时间:</td>
		<td style="text-align: center;"><?=$a_exercices ['first_attempt_date']?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">最后一次考试成绩:</td>
		<td style="text-align: center;"><?=round($a_exercices ['last_attempt_score']/$a_exercices ['paper_score']*100)?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">最后一次考试时间:</td>
		<td style="text-align: center;"><?=$a_exercices ['last_attempt_date']?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">最终成绩(最好成绩):</td>
		<td style="text-align: center;"><?=$a_exercices['score']?>
		</td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">通过分数:</td>
		<td style="text-align: center;"><?=$exam_info ['pass_score']?></td>
	</tr>
	<tr>
		<td style="text-align: right;padding-right:10px">是否通过:</td>
		<td style="text-align: center;"><?php
	echo $a_exercices ['score'] >= $exam_info ['pass_score'] ? '已通过' : '未通过';
	?></td>

	</tr>
</table>
<?php if($exam_info['feedback_type']==0){?><span style="float:right;padding-right:5px">
		<a href="<?=api_get_path(WEB_CODE_PATH)?>exam/exam_view.php?exam_id=<?=$exam_info ['id']?>&result_id=<?=$a_exercices['track_id']?>" title="点击查看答卷及标准答案" target="_blank">查看答卷及标准答案</a></span>	
	<?php }?>
</div>
<?php
} else {
	?><div class="empty_data_alert">没有相关记录</div>
<?php
}
?>
