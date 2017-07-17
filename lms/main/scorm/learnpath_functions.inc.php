<?php // 


/*
==============================================================================
	
==============================================================================
*/

/**
==============================================================================
 * This is a function library for the learning path.
 *
 * Due to the face that the learning path has been built upon the resoucelinker,
 * naming conventions have changed at least 2 times. You can see here in order the :
 * 1. name used in the first version of the resourcelinker
 * 2. name used in the first version of the LP
 * 3. name used in the second (current) version of the LP
 *
 * 1.       2.        3.
 * Category = Chapter = Module
 * Item (?) = Item    = Step
 *
 * @author  Denes Nagy <darkden@evk.bke.hu>, main author
 * @author  Roan Embrechts, some code cleaning
 * @author	Yannick Warnier <yannick.warnier@ZLMS.com>, multi-level learnpath behaviour + new SCORM tool
 * @access  public
 * @package ZLMS.learnpath
 * @todo rename functions to coding conventions: not deleteitem but delete_item, etc
 * @todo rewrite functions to comply with phpDocumentor
 * @todo remove code duplication
============================================================================== 
 */

/**
 * This function returns the items belonging to the chapter that contains the given item (brother items)
 * @param	integer	Item id
 * @return	array		Table containing the items
 */
function learnpath_items($itemid) {
	global $xml_output;
	$tbl_learnpath_item = Database::get_course_table ( TABLE_LEARNPATH_ITEM );
	
	$sql_items = "SELECT parent_item_id FROM $tbl_lp_item WHERE id='$itemid'";
	$moduleid_sql = api_sql_query ( $sql_items );
	$moduleid_array = mysql_fetch_array ( $moduleid_sql ); //first row of the results
	$moduleid = $moduleid_array ["parent_item_id"];
	
	$sql_items = "SELECT * FROM $tbl_lp_item WHERE parent_item_id='$moduleid' ORDER BY display_order ASC";
	$result_items = api_sql_query ( $sql_items );
	$ar = mysql_fetch_array ( $result_items );
	while ( $ar != '' ) {
		$result [] = $ar;
		$ar = mysql_fetch_array ( $result_items );
	}
	return $result;

}


/**
 * This function tells if a learnpath contains items which are prerequisite to other items
 * @param	integer	Learnpath id
 * @return	boolean	True if this learnpath contains an item which is a prerequisite to something
 */
function is_prereq($learnpath_id) {
	global $xml_output;
	$tbl_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
	
	$prereq = false;
	
	$sql_items = "SELECT * FROM $tbl_lp_item WHERE lp_id='$learnpath_id' AND parent_item_id=0 ORDER BY display_order ASC";
	$result_items = api_sql_query ( $sql_items, __FILE__, __LINE__ );
	while ( $ar = Database::fetch_array ( $result_items ) ) {
		$c = $ar ['id'];
		$sql_items2 = "SELECT * FROM $tbl_lp_item WHERE lp_id = $learnpath_id AND parent_item_id='$c' ORDER BY display_order ASC";
		$result_items2 = api_sql_query ( $sql_items2, __FILE__, __LINE__ );
		while ( $ar2 = Database::fetch_array ( $result_items2 ) ) {
			if ($ar2 ['prerequisite'] != '') {
				$prereq = true;
			}
		}
	}
	return ($prereq);
}

/**
 * This function returns the prerequisite sentence
 * @param	integer	Item ID
 * @return	string 	Prerequisite warning text
 */
function prereqcheck($id_in_path) {
	//1 Initialise and import working vars
	global $learnpath_id, $_user;
	global $langPrereqToEnter, $langPrereqTestLimit1, $langPrereqTestLimit2, $langPrereqTestLimitNow, $langPrereqFirstNeedTo, $langPrereqModuleMinimum1, $langPrereqModuleMinimum2;
	$tbl_learnpath_user = Database::get_course_table ( TABLE_LEARNPATH_USER );
	$tbl_learnpath_item = Database::get_course_table ( TABLE_LEARNPATH_ITEM );
	$tbl_learnpath_chapter = Database::get_course_table ( TABLE_LEARNPATH_CHAPTER );
	
	//2 Initialise return value
	$prereq = false;
	
	//3 Get item data from the database
	$sql_items = "SELECT * FROM $tbl_learnpath_item WHERE id='$id_in_path'";
	$result_items = api_sql_query ( $sql_items );
	$row = mysql_fetch_array ( $result_items );
	//4 Check prerequisite's type
	if ($row ['prereq_type'] == 'i') {
		//4.a If prerequisite is of type 'i' (item)
		//4.a.1 Get data ready for use
		$id_in_path3 = $row ['prereq_id'];
		$prereq_limit = $row ['prereq_completion_limit'];
		
		//4.a.2 Get data from the user-item relation
		if ($_user ['user_id'] == '') {
			$user_id = '0';
		} else {
			$user_id = $_user ['user_id'];
		}
		$sql_items3 = "SELECT * FROM $tbl_learnpath_user WHERE (learnpath_item_id='$id_in_path3' and user_id=$user_id)";
		$result_items3 = api_sql_query ( $sql_items3 );
		$row3 = mysql_fetch_array ( $result_items3 );
		
		//4.a.3 Get the link that needs to be shown for the current item (not the prereq)
		$stepname = display_addedresource_link_in_learnpath ( $row ['item_type'], $row ['ref'], '', $id_in_path, 'builder', 'nolink' );
		//this is the step we want to open
		$stepname = trim ( $stepname ); //to remove occasional line breaks and white spaces
		

		//4.a.4 Get the prerequisite item
		$sql6 = "SELECT * FROM $tbl_learnpath_item WHERE (id='$id_in_path3')";
		$result6 = api_sql_query ( $sql6 );
		$row6 = mysql_fetch_array ( $result6 );
		//4.a.5 Get a link to the prerequisite item
		$prereqname = display_addedresource_link_in_learnpath ( $row6 ['item_type'], $row6 ['ref'], '', $id_in_path3, 'builder', 'nolink' ); //this is the prereq of the step we want to open
		

		//4.a.5 Initialise limit value
		$limitok = true;
		//4.a.6 Get prerequisite limit
		if ($prereq_limit) {
			//4.a.6.a If the completion limit exists
			if ($row3 ['score'] < $prereq_limit) {
				//4.a.6.a.a If the completion limit hasn't been reached, then display the corresponding message
				$prereq = $langPrereqToEnter . $stepname . $langPrereqTestLimit1 . "$prereq_limit" . $langPrereqTestLimit2 . $prereqname . ". (" . $langPrereqTestLimitNow . $row3 ['score'] . ")";
			} else {
				//4.a.6.a.b The completion limit has been reached. Prepare to return false (no prereq hanging)
				$prereq = false;
			}
		} else {
			//4.a.6.b If the completion limit doesn't exist
			if ($row3 ['status'] == "completed" or $row3 ['status'] == 'passed') {
				//4.a.6.b.a If the prerequisite status is 'completed'
				$prereq = false;
			} else {
				//4.a.6.b.b The prerequisite status is not 'completed', return corresponding message
				$prereq = $langPrereqToEnter . $stepname . $langPrereqFirstNeedTo . $prereqname . '.';
			}
		}
	
	} elseif ($row ['prereq_type'] == 'c') {
		//4.b If prerequisite is of type 'c' (chapter)
		//4.b.1 Get data ready to use
		$id_in_path2 = $row ['prereq_id'];
		//4.b.2 Get all items in the prerequisite chapter
		$sql_items3 = "SELECT * FROM $tbl_lp_item WHERE parent_item_id='$id_in_path2'";
		$result_items3 = api_sql_query ( $sql_items3 );
		$allcompleted = true;
		while ( $row3 = mysql_fetch_array ( $result_items3 ) ) {
			//4.b.3 Cycle through items in the prerequisite chapter
			//4.b.3.1 Get data ready to use
			$id_in_path4 = $row3 ['id'];
			if ($_user ['user_id'] == '') {
				$user_id = '0';
			} else {
				$user_id = $_user ['user_id'];
			}
			//4.b.3.2 Get user-item relation
			$sql_items4 = "SELECT * FROM $tbl_learnpath_user WHERE (learnpath_item_id='$id_in_path4' and user_id=$user_id)";
			$result_items4 = api_sql_query ( $sql_items4 );
			$row4 = mysql_fetch_array ( $result_items4 );
			//4.b.3.3 If any of these elements is not 'completed', the overall completion status is false
			if ($row4 ['status'] != "completed" and $row4 ['status'] != 'passed') {
				$allcompleted = false;
			}
		}
		if ($allcompleted) {
			//4.b.4.a All items were completed, prepare to return that there is no prerequisite blocking the way
			$prereq = false;
		} else {
			//4.b.4.b Something was not completed. Return corresponding message
			$sql5 = "SELECT * FROM $tbl_learnpath_chapter WHERE (lp_id='$learnpath_id' and id='$id_in_path2')";
			$result5 = api_sql_query ( $sql5 );
			$row5 = mysql_fetch_array ( $result5 );
			$prereqmodulename = trim ( $row5 ['chapter_name'] );
			$prereq = $langPrereqModuleMinimum1 . $prereqmodulename . $langPrereqModuleMinimum2;
		}
	} else {
		//5 If prerequisite type undefined, no prereq
		$prereq = false;
	}
	//6 Return the message (or false if no prerequisite waiting)
	return ($prereq);
}

/**
 * Constructs the tree that will be used to build the learnpath structure
 * @params  integer     Learnpath_id
 * @return  array       Tree of the learnpath structure
 * @author  Yannick Warnier <yannick.warnier@ZLMS.com>
 * @comment This is a temporary function, which exists while the chapters and items
 * are still in separate tables in the database. This function gathers the data in a unique tree.
 **/
function get_learnpath_tree($learnpath_id) {
	$tbl_lp_item = Database::get_course_table ( TABLE_LP_ITEM );
	
	$tree = array ();
	$chapters = array ();
	$all_items_by_chapter = array ();
	$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id = " . $learnpath_id . " AND item_type='webcs_chapter' ORDER BY display_order";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	// format the $chapters_by_parent array so we have a suitable structure to work with
	while ( $row = Database::fetch_array ( $res ) ) {
		$chapters [] = $row;
		//shouldn't be necessary (check no null value)
		if (empty ( $row ['parent_item_id'] )) {
			$row ['parent_item_id'] = 0;
		}
		//$chapters_by_parent[$row['parent_item_id']][$row['previous_item_id']] = $row;
		$all_items_by_chapter [$row ['parent_item_id']] [$row ['display_order']] = $row;
		$all_items_by_chapter [$row ['parent_item_id']] [$row ['display_order']] ['type'] = 'ZLMS_chapter';
	}
	
	// now for every item in each chapter, get a suitable structure too
	foreach ( $chapters as $row ) {
		// select items from this chapter
		$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id = $learnpath_id AND parent_item_id = " . $row ['id'] . " ORDER BY display_order";
		//error_log('New LP - learnpath_functions - get_learnpath_tree: '.$sql,0);
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		//error_log('New LP - learnpath_functions - get_learnpath_tree: Found '.Database::num_rows($res).' results',0);
		while ( $myrow = mysql_fetch_array ( $res, MYSQL_ASSOC ) ) {
			//$items[] = $myrow;
			//$items_by_chapter[$myrow['parent_item_id']][$myrow['display_order']] = $myrow;
			$all_items_by_chapter [$row ['id']] [$myrow ['display_order']] = $myrow;
			$all_items_by_chapter [$row ['id']] [$myrow ['display_order']] ['type'] = 'item';
		}
	}
	//array_multisort($all_items_by_chapter[0], SORT_ASC, SORT_NUMERIC);
	foreach ( $all_items_by_chapter as $key => $subrow ) {
		ksort ( $all_items_by_chapter [$key] );
	}
	
	//all items should now be well-ordered
	//error_log('New LP - In get_learnpath_tree, returning '.print_r($all_items_by_chapter,true),0);
	return $all_items_by_chapter;
}

/**
 * Gives a list of sequencial elements IDs for next/previous actions
 * @param   array   The elements tree as returned by get_learnpath_tree()
 * @param   integer The chapter id to start from
 * @param   boolean Whether to include chapters or not
 * @return  array   List of elements in the first to last order
 * @author  Yannick Warnier <yannick.warnier@ZLMS.com>
 **/
function get_ordered_items_list($tree, $chapter = 0, $include_chapters = false) {
	$list = array ();
	foreach ( $tree [$chapter] as $order => $elem ) {
		if ($elem ['type'] == 'chapter') {
			if ($include_chapters === true) {
				$list [] = array ('id' => $elem ['id'], 'type' => $elem ['type'] );
			}
			$res = get_ordered_items_list ( $tree, $elem ['id'], $include_chapters );
			foreach ( $res as $elem ) {
				$list [] = $elem;
			}
		} elseif ($elem ['type'] == 'item') {
			$list [] = array ('id' => $elem ['id'], 'type' => $elem ['type'], 'item_type' => $elem ['item_type'], 'parent_item_id' => $elem ['parent_item_id'], 'item_id' => $elem ['item_id'] );
		}
	}
	return $list;
}

/**
 * Displays the structure of a chapter recursively. Takes the result of get_learnpath_tree as argument 
 * @param	array		Chapter structure
 * @param	integer	Chapter ID (start point in the tree)
 * @param	integer	Learnpath ID 
 * @param	integer	User ID
 * @param	boolean	Indicates if the style is wrapped (true) or extended (false) 
 * @param	integer	Level reached so far in the tree depth (enables recursive behaviour)
 * @return	array		Number of items, Number of items completed
 * @author	Many changes by Yannick Warnier <yannick.warnier@ZLMS.com> 
 **/
function display_toc_chapter_contents($tree, $parent_item_id = 0, $learnpath_id, $uid, $wrap, $level = 0) {
	#global $tbl_learnpath_user;
	$tbl_learnpath_user = Database::get_course_table ( TABLE_LEARNPATH_USER );
	$num = 0;
	$num_completed = 0;
	foreach ( $tree [$parent_item_id] as $order => $elem ) {
		
		$bold = false;
		if (! empty ( $_SESSION ['cur_open'] ) && ($elem ['id'] == $_SESSION ['cur_open'])) {
			$bold = true;
		}
		if ($elem ['type'] === 'chapter') {
			if ($wrap) {
				echo str_repeat ( "&nbsp;&nbsp;", $level ) . shorten ( strip_tags ( $elem ['chapter_name'] ), (35 - 3 * $level) ) . "<br />\n";
			} else {
				echo "<tr><td colspan='3'>" . str_repeat ( "&nbsp;&nbsp;", $level ) . shorten ( $elem ['chapter_name'], (35 - 3 * $level) ) . "</td></tr>\n";
			}
			
			if ($wrap) {
				if ($elem ['chapter_description'] != '') {
					echo "<div class='description'>" . str_repeat ( "&nbsp;&nbsp;", $level ) . "&nbsp;" . shorten ( $elem ['chapter_description'], (35 - 3 * $level) ) . "</div>\n";
				}
			} else {
				if ($elem ['chapter_description'] != '') {
					echo "<tr><td colspan='3'><div class='description'>" . str_repeat ( "&nbsp;&nbsp;", $level ) . "&nbsp;" . shorten ( $elem ['chapter_description'], (35 - 3 * $level) ) . "</div></td></tr>\n";
				}
			}
			list ( $a, $b ) = display_toc_chapter_contents ( $tree, $elem ['id'], $learnpath_id, $uid, $wrap, $level + 1 );
			$num += $a;
			$num_completed += $b;
		
		} elseif ($elem ['type'] === 'item') {
			// If this element is an item (understand: not a directory/module)
			$sql0 = "SELECT * FROM $tbl_learnpath_user WHERE (user_id='" . $uid . "' and learnpath_item_id='" . $elem ['id'] . "' and lp_id='" . $learnpath_id . "')";
			$result0 = api_sql_query ( $sql0, __FILE__, __LINE__ );
			$row0 = mysql_fetch_array ( $result0 );
			
			$completed = '';
			if (($row0 ['status'] == 'completed') or ($row0 ['status'] == 'passed')) {
				$completed = 'completed';
				$num_completed ++;
			}
			
			if ($wrap) {
				echo str_repeat ( "&nbsp;&nbsp;", $level ) . "<a name='{$elem['id']}' />\n";
			} else {
				echo "<tr><td>" . str_repeat ( "&nbsp;&nbsp;", $level - 1 ) . "<a name='{$elem['id']}' />\n";
			}
			
			if ($wrap) {
				$icon = 'wrap';
			}
			
			if ($bold) {
				echo "<b>";
			}
			display_addedresource_link_in_learnpath ( $elem ['item_type'], $elem ['ref'], $completed, $elem ['id'], 'player', $icon );
			if ($bold) {
				echo "</b>";
			}
			if ($wrap) {
				echo "<br />\n";
			} else {
				echo "</td></tr>\n";
			}
			
			$num ++;
		}
	}
	return array ($num, $num_completed );
}

/**
 * Returns a string to display in the tracking frame within the contents.php page (for example)
 * @param   integer     Learnpath id
 * @param   integer     Current user id
 * @param   integer     Starting chapter id
 * @param   array       Tree of elements as returned by get_learnpath_tree()
 * @param   integer     Level of recursivity we have reached
 * @param   integer     Counter of elements already displayed
 * @author  Yannick Warnier <yannick.warnier@ZLMS.com>
 * @note : forced display because of display_addedresource_link_in_learnpath behaviour (outputing a string would be better)
 **/
function get_tracking_table($learnpath_id, $user_id, $parent_item_id = 0, $tree = false, $level = 0, $counter = 0) {
	$tbl_learnpath_chapter = Database::get_course_learnpath_chapter_table ();
	$tbl_learnpath_item = Database::get_course_learnpath_item_table ();
	$tbl_learnpath_user = Database::get_course_learnpath_user_table ();
	//$mytable = '';
	$include_chapters = true;
	
	if (! is_array ( $tree )) {
		//get a tree of the current learnpath elements
		$tree = get_learnpath_tree ( $learnpath_id );
	}
	foreach ( $tree [$parent_item_id] as $order => $elem ) {
		if (($counter % 2) == 0) {
			$oddclass = 'row_odd';
		} else {
			$oddclass = 'row_even';
		}
		
		if ($elem ['type'] == 'chapter') {
			if ($include_chapters === true) {
				//$mytable .= "<tr class='$oddclass'><td colspan = '3'>".str_repeat('&nbsp;',$level*2+2).$elem['chapter_name']."</td></tr>\n";
				echo "<tr class='$oddclass'><td colspan = '3'>" . str_repeat ( '&nbsp;', $level * 2 + 2 ) . $elem ['chapter_name'] . "</td></tr>\n";
			}
			$counter ++;
			//$mytable .= get_tracking_table($learnpath_id, $user_id, $elem['id'], $tree, $level + 1, $counter );
			get_tracking_table ( $learnpath_id, $user_id, $elem ['id'], $tree, $level + 1, $counter );
		
		} elseif ($elem ['type'] == 'item') {
			
			$sql = "SELECT * FROM $tbl_learnpath_user " . "WHERE user_id = $user_id " . "AND lp_id = $learnpath_id " . "AND learnpath_item_id = " . $elem ['id'];
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$myrow = mysql_fetch_array ( $res );
			
			if (($myrow ['status'] == 'completed') || ($myrow ['status'] == 'passed')) {
				$color = 'blue';
				$statusmessage = get_lang ( 'Complete' );
			} else {
				$color = 'black';
				$statusmessage = get_lang ( 'Incomplete' );
			}
			
			$link = get_addedresource_link_in_learnpath ( $elem ['item_type'], $elem ['id'], $elem ['item_id'] );
			//$link = display_addedresource_link_in_learnpath($elem['item_type'], $elem['id'], $row['status'], $elem['item_id'], 'player', 'none');
			

			//$mytable .= "<tr class='$oddclass'>"
			echo "<tr class='$oddclass'>" . "<td class='mystatus'>" . str_repeat ( "&nbsp;", $level * 2 + 2 );
			//."<a href='$link?SQMSESSID=36812c2dea7d8d6e708d5e6a2f09b0b9' target='toc'>hop</a>"
			display_addedresource_link_in_learnpath ( $elem ['item_type'], $elem ['ref'], $myrow ['status'], $elem ['id'], 'player', 'wrap' );
			//we should also add the total score here
			echo "<td>" . "<font color='$color'><div class='mystatus'>" . $statusmessage . "</div></font>" . "</td>" . "<td>" . "<div class='mystatus' align='center'>" . ($myrow ['score'] == 0 ? '-' : $myrow ['score']) . "</div>" . "</td>" . "</tr>\n";
			$counter ++;
		}
	}
	//return $mytable;
	return true;
}

/**
 * This function deletes an entire directory
 * @param	string	The directory path
 * @return boolean	True on success, false on failure
 */
function deldir($dir) {
	$dh = opendir ( $dir );
	while ( $file = readdir ( $dh ) ) {
		if ($file != "." && $file != "..") {
			$fullpath = $dir . "/" . $file;
			if (! is_dir ( $fullpath )) {
				unlink ( $fullpath );
			} else {
				deldir ( $fullpath );
			}
		}
	}
	
	closedir ( $dh );
	
	if (rmdir ( $dir )) {
		return true;
	} else {
		return false;
	}
}

/**
 * This function returns an xml tag
 * $data behaves as the content in case of full tags 
 * $data is an array of attributes in case of returning an opening tag
 * @param	string
 * @param	string
 * @param	array
 * @param	string
 * @return string
 */
function xmltagwrite($tagname, $which, $data, $linebreak = "yes") {
	switch ($which) {
		case "open" :
			$tag = "<" . $tagname;
			$i = 0;
			while ( $data [0] [$i] ) {
				$tag .= " " . $data [0] [$i] . "=\"" . $data [1] [$i] . "\"";
				$i ++;
			}
			if ($tagname == 'file') {
				$closing = '/';
			}
			$tag .= $closing . ">";
			if ($linebreak != 'no_linebreak') {
				$tag .= "\n";
			}
			break;
		case "close" :
			$tag = "</" . $tagname . ">";
			if ($linebreak != 'no_linebreak') {
				$tag .= "\n";
			}
			break;
		case "full" :
			$tag = "<" . $tagname;
			$tag .= ">" . $data . "</" . $tagname . ">";
			if ($linebreak != 'no_linebreak') {
				$tag .= "\n";
			}
			break;
	}
	return $tag;
}

/**
 * Copy file and create directories in the path if needed.
 *
 * @param	string	$source Source path
 * @param	string	$dest Destination path 
 * @return boolean 	true on success, false on failure
 */
function CopyNCreate($source, $dest) {
	if (strcmp ( $source, $dest ) == 0) return false;
	
	$dir = "";
	$tdest = explode ( '/', $dest );
	for($i = 0; $i < sizeof ( $tdest ) - 1; $i ++) {
		$dir = $dir . $tdest [$i] . "/";
		if (! is_dir ( $dir )) if (! mkdir ( $dir )) return false;
	}
	
	if (! copy ( $source, $dest )) return false;
	
	return true;
}

function rcopy($source, $dest) {
	//error_log($source." -> ".$dest,0);
	if (! file_exists ( $source )) {
		//error_log($source." does not exist",0);
		return false;
	}
	
	if (is_dir ( $source )) {
		
		if (strrpos ( $source, '/' ) == sizeof ( $source ) - 1) {
			$source = substr ( $source, 0, size_of ( $source ) - 1 );
		}
		if (strrpos ( $dest, '/' ) == sizeof ( $dest ) - 1) {
			$dest = substr ( $dest, 0, size_of ( $dest ) - 1 );
		}
		
		if (! is_dir ( $dest )) {
			$res = @ mkdir ( $dest );
			if ($res === true) {
				return true;
			} else {
				//remove latest part of path and try creating that
				if (rcopy ( substr ( $source, 0, strrpos ( $source, '/' ) ), substr ( $dest, 0, strrpos ( $dest, '/' ) ) )) {
					return @ mkdir ( $dest );
				} else {
					return false;
				}
			}
		}
		return true;
	} else {
		//this is presumably a file
		//error_log($source." is a file",0);
		if (! @ copy ( $source, $dest )) {
			//error_log("Could not simple-copy $source",0);
			$res = rcopy ( dirname ( $source ), dirname ( $dest ) );
			if ($res === true) {
				//error_log("Welcome dir created",0);
				return @ copy ( $source, $dest );
			} else {
				return false;
			
		//error_log("Error creating path",0);
			}
		} else {
			//error_log("Could well simple-copy $source",0);
			return true;
		}
	}
}
?>
