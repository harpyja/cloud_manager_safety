<?php

function _check_org_course_quota(){
	global $restrict_org_id,$table_course;
	if(!api_is_platform_admin()){
		$sql="SELECT course_quota FROM ".Database :: get_main_table(TABLE_MAIN_DEPT)." WHERE id=".Database::escape($restrict_org_id);
		$org_course_count=Database::get_scalar_value($sql);
		if($org_course_count==0) return TRUE;

		$sql="SELECT COUNT(*) FROM ".$table_course." WHERE org_id=".Database::escape($restrict_org_id);
		$course_count=Database::get_scalar_value($sql);

		return $course_count<=$org_course_count;
	}
	return true;
}

?>