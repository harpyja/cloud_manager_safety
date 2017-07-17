<?php
 if (!defined('IN_QH')) exit('Access Denied !');
	$TBL_EXERCICES = Database::get_main_table ( TABLE_QUIZ_TEST );
	$TBL_EXERCICE_QUESTION = Database::get_main_table ( TABLE_QUIZ_TEST_QUESTION );
	$sql = "SELECT id,title,type,description,max_attempt,max_duration FROM $TBL_EXERCICES AS ce WHERE active='1' ";
	$sql .= " AND ce.cc='" . api_get_course_code () . "'";
	$sql .= " ORDER BY display_order";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	?>

<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title"><img src="images/list10.jpg"
	style="float: left; margin: 6px 5px 0 10px;" />
<div style="float: left;" class="de4">测验练习</div>
</div>
</div>
<?php if(Database::num_rows($result)>0){?>
<table cellspacing="0" class="tbl_course" style="width: 960px">
	<tr>
		<th class="dd2">测验名称</th>
		<th class="dd2">测验类型</th>
		<th class="dd2">题目总数</th>
		<th class="dd4">答题时间</th>
		<th class="dd2">已做次数</th>
		<th class="dd2">成绩</th>
	</tr>

	<?php
	while ( $row = Database::fetch_array ( $result, "ASSOC" ) ) {
		$url = WEB_QH_PATH . 'quiz_intro.php?' . api_get_cidreq () . "&exerciseId=" . $row ['id'] ;
		
		$sqlquery = "SELECT count(*) FROM $TBL_EXERCICE_QUESTION WHERE `exercice_id` = '" . $row ['id'] . "'";
		$total_questions = Database::get_scalar_value ( $sqlquery );
		
		$objExercise = new Exercise ();
		$objExercise->read ( $row ["id"] );
		$attempts = $objExercise->get_user_attempts ( api_get_user_id (), api_get_course_code () );
		?>

	<tr class="de1">
		<td><a href="<?=$url?>" target="_blank" class="de1"><?=$row ["title"]?></a></td>
		<td align="center"><?php if($row['type']==2) echo '课后练习';if($row['type']==1) echo '模拟测验';  ?></td>
		<td align="center"><?php
		echo $total_questions;
		?></td>
		<td align="center"><?php
		echo $row ['max_duration'] == 0 ? "不限制" : ($row ['max_duration'] / 60) . "&nbsp;分钟";
		?></td>
		<td align="center"><?php
		echo $attempts == 0 ? "未测验" : $attempts;
		?></td>
		<td align="center"><?php $rtn=$objStat->get_quiz_score($user_id, $course_code, $row ["id"]);
		if($rtn['raw_score']>0) echo $rtn['raw_score'].' ('.$rtn['percent_score'].'%)';?></td>
	</tr>

	<?php
	}
	}else{
?>
<div class="empty_data_alert">没有相关测验练习</div>
<?php } ?>
</table>
