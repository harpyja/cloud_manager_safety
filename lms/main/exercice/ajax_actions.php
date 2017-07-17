<?php
include_once('exercise.class.php');
include_once('question.class.php');
include_once('answer.class.php');

$language_file = array ('exercice');
require_once ('../inc/global.inc.php');
header("Content-Type: text/html;charset=".SYSTEM_CHARSET);
require_once('exercise.lib.php');
api_block_anonymous_users();

require_once('exercise.lib.php');


$action=getgpc('action');
if(isset($_REQUEST['action'])){
	switch($action){
		case 'change_question_disp_order':
			$question_id=getgpc("id","P");
			$new_order=getgpc("value","P");
			$exerciseId=getgpc("exerciseId","P");
			$sql_data=array("question_order"=>intval($new_order));
			$sqlwhere=" cc='".api_get_course_code()."' AND exercice_id='"
			.escape($exerciseId)."' AND question_id='".$question_id."'";
			$sql=Database::sql_update($TBL_EXERCICE_QUESTION,$sql_data,$sqlwhere);
			api_sql_query($sql,__FILE__,__LINE__);
			if($exerciseId)	{
				api_session_unregister('objExercise');
				$objExercise=new Exercise();
				$objExercise->read($exerciseId);				
				api_session_register('objExercise');
			}
			break;

	}

}

?>