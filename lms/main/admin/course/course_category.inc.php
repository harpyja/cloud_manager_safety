<?php

$tbl_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );

function addNode($code, $name, $parent_id ,$CourseDescription,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation) {
	global $tbl_category;
	if ($parent_id) {
		$sql = "SELECT sn FROM " . $tbl_category . " WHERE id=" . Database::escape ( $parent_id );
		$parent_sn = Database::get_scalar_value ( $sql );
		if (empty ( $parent_sn )) $parent_sn = "";
		
		$sql = "SELECT sn FROM " . $tbl_category . " WHERE sn LIKE '" . $parent_sn . "__' ORDER BY sn DESC LIMIT 1";
		$p_sn = Database::get_scalar_value ( $sql );
		if ($p_sn) {
			$tmp_sub_sn = intval ( substr ( $p_sn, - 2 ) );
			$sn = strval ( $tmp_sub_sn + 1 );
			$sn = substr ( $p_sn, 0, - 2 ) . strval ( $sn );
		} else {
			$sn = strval ( $parent_sn ) . "10";
		}
	} else {
		$sn = '';
	}
	
	$sql = "SELECT MAX(tree_pos) AS maxTreePos FROM " . $tbl_category . " WHERE parent_id=" . Database::escape ( $parent_id );
	$tree_pos = Database::get_scalar_value ( $sql );
	$tree_pos = (empty ( $tree_pos ) ? 1 : $tree_pos ++);
	
	//'last_updated_date'=>date('Y-m-d H:i:s'),
	$sql_row = array (
        'name' => trim ( $name ),
        'code' => trim ( $code ),
        'parent_id' => (empty ( $parent_id ) ? "0" : $parent_id),
        'tree_pos' => $tree_pos,
        'children_count' => '0',
        'sn' => $sn ,
        'CourseDescription' =>$CourseDescription,
        'CurriculumStandards' => $CurriculumStandards,
        'AssessmentCriteria' => $AssessmentCriteria,
        'TeachingProgress' => $TeachingProgress,
        'StudyGuide' => $StudyGuide,
        'TeachingGuide' => $TeachingGuide,
        'InstructionalDesignEvaluation' => $InstructionalDesignEvaluation
    );
	$sql = Database::sql_insert ( $tbl_category, $sql_row );
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	updateFils ( $parent_id );
	cache ( CACHE_KEY_COURSE_CATEGORIES, null );
	return true;
}

function editNode($code, $name, $id, $parent_id,$CourseDescription,$CurriculumStandards,$AssessmentCriteria,$TeachingProgress,$StudyGuide,$TeachingGuide,$InstructionalDesignEvaluation) {
	global $tbl_category;
	$pid = $parent_id ? Database::escape_string ( $parent_id ) : '0';
	
	$sql_row = array (
        'name' => trim ( $name ),
        'code' => trim ( $code ),
        'CourseDescription' =>$CourseDescription,
        'CurriculumStandards' => $CurriculumStandards,
        'AssessmentCriteria' => $AssessmentCriteria,
        'TeachingProgress' => $TeachingProgress,
        'StudyGuide' => $StudyGuide,
        'TeachingGuide' => $TeachingGuide,
        'InstructionalDesignEvaluation' => $InstructionalDesignEvaluation

    );
	$sql = Database::sql_update ( $tbl_category, $sql_row, "id=" . Database::escape ( $id ) );
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	cache ( CACHE_KEY_COURSE_CATEGORIES, null );
	return true;
}

/**
 * 更新子项数
 * @param $category
 * @return unknown_type
 */
function updateFils($category) {
	global $tbl_category;
	
	//找到最顶级
	$sql = "SELECT parent_id FROM $tbl_category WHERE id=" . Database::escape ( $category );
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($row = Database::fetch_array ( $result, 'ASSOC' )) {
		updateFils ( $row ['parent_id'] );
	}
	
	$children_count = compterFils ( $category, 0 ) - 1;
	
	$sql = "UPDATE $tbl_category SET children_count='$children_count' WHERE id=" . Database::escape ( $category );
	api_sql_query ( $sql, __FILE__, __LINE__ );
}

function compterFils($pere, $cpt) {
	global $tbl_category;
	$sql = "SELECT id FROM $tbl_category WHERE parent_id=" . Database::escape ( $pere );
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$cpt = compterFils ( $row ['id'], $cpt );
	}
	
	return ($cpt + 1);
}

function deleteNode($node) {
	global $tbl_category, $tbl_course;
	
	$sql = "SELECT COUNT(*) FROM $tbl_category WHERE parent_id='" . Database::escape_string ( $node ) . "'";
	if (Database::get_scalar_value ( $sql ) > 0) return 101;
	
	$sql = "SELECT parent_id,tree_pos FROM $tbl_category WHERE id='" . Database::escape_string ( $node ) . "'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($row = Database::fetch_array ( $result, 'ASSOC' )) {
		if ($row ['parent_id']) {
			$sql = "UPDATE $tbl_course SET category_code='" . $row ['parent_id'] . "' WHERE  category_code='" . Database::escape_string ( $node ) . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$sql = "UPDATE $tbl_category SET parent_id='" . $row ['parent_id'] . "' WHERE parent_id='" . Database::escape_string ( $node ) . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			api_sql_query ( "UPDATE $tbl_course SET category_code='' WHERE category_code='" . Database::escape_string ( $node ) . "'", __FILE__, __LINE__ );
			api_sql_query ( "UPDATE $tbl_category SET parent_id=NULL WHERE parent_id='" . Database::escape_string ( $node ) . "'", __FILE__, __LINE__ );
		}
		
		api_sql_query ( "UPDATE $tbl_category SET tree_pos=tree_pos-1 WHERE tree_pos > '" . $row ['tree_pos'] . "'", __FILE__, __LINE__ );
                
                $old_category_pic=DATABASE::getval("select code from course_category where id=".Database::escape_string ( $node ),__FILE__,__LINE__);
                
                if($old_category_pic!==''){
                    $base_work_dir=URL_ROOT."/www".URL_APPEDND."/storage/category_pic";
                    exec("chmod -R 777 ".URL_ROOT."/www".URL_APPEDND."/storage/category_pic/".$old_category_pic);
                    $exec_temp="rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/category_pic/".$old_category_pic."*";
                //echo $exec_temp;    
                exec($exec_temp);
                }
                $ress=api_sql_query ( "DELETE FROM $tbl_category WHERE id='" . Database::escape_string ( $node ) . "'", __FILE__, __LINE__ );
               
		if (! empty ( $row ['parent_id'] )) {
			updateFils ( $row ['parent_id'] );
		}
		
		$log_msg = get_lang ( 'DelCourseCateogry' ) . "id=" . $node;
		api_logging ( $log_msg, 'COURSE', 'DelCourseCateogry' );
		
		global $_objCache;
		if ($_objCache && is_object ( $_objCache )) {
			$_objCache->remove ( CACHE_KEY_COURSE_CATEGORIES );
		}
		return SUCCESS;
	}
}

function moveNodeUp($id, $tree_pos, $parent_id) {
	global $tbl_category;
	
	$sql = "SELECT id,tree_pos FROM $tbl_category WHERE parent_id " . (empty ( $parent_id ) ? "IS NULL" : "='" . Database::escape_string ( $parent_id ) . "'") . " AND tree_pos<'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ ); //上一个
	

	if (! $row = mysql_fetch_array ( $result )) { //如果为最顶
		$sql = "SELECT id,tree_pos FROM $tbl_category WHERE parent_id " . (empty ( $parent_id ) ? "IS NULL" : "='" . Database::escape_string ( $parent_id ) . "'") . " AND tree_pos>'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ ); //下一个
		

		if (! $row = mysql_fetch_array ( $result )) {
			return false;
		}
	}
	
	$sql = "UPDATE $tbl_category SET tree_pos='" . $row ['tree_pos'] . "' WHERE id='" . Database::escape_string ( $id ) . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$sql = "UPDATE $tbl_category SET tree_pos='$tree_pos' WHERE id='$row[id]'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	global $_objCache;
	if ($_objCache && is_object ( $_objCache )) {
		$_objCache->remove ( CACHE_KEY_COURSE_CATEGORIES );
	}
}

//TO-TEST
function moveNodeDown($id, $tree_pos, $parent_id) {
	global $tbl_category;
	
	$sql = "SELECT id,tree_pos FROM $tbl_category WHERE parent_id " . (empty ( $parent_id ) ? "IS NULL" : "='" . Database::escape_string ( $parent_id ) . "'") . " AND tree_pos>'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ ); //上一个
	

	if (! $row = mysql_fetch_array ( $result )) { //如果为最底
		$sql = "SELECT id,tree_pos FROM $tbl_category WHERE parent_id " . (empty ( $parent_id ) ? "IS NULL" : "='" . Database::escape_string ( $parent_id ) . "'") . " AND tree_pos<'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ ); //下一个
		

		if (! $row = mysql_fetch_array ( $result )) {
			return false;
		}
	}
	
	$sql = "UPDATE $tbl_category SET tree_pos='" . $row ['tree_pos'] . "' WHERE id='" . Database::escape_string ( $id ) . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$sql = "UPDATE $tbl_category SET tree_pos='$tree_pos' WHERE id='$row[id]'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	global $_objCache;
	if ($_objCache && is_object ( $_objCache )) {
		$_objCache->remove ( CACHE_KEY_COURSE_CATEGORIES );
	}
}
