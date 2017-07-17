<?php //$id:$

/**
 * File containing the declaration of the learnpathList class.
 * @package	ZLMS.learnpath
 * @author	Yannick Warnier <ywarnier@beeznest.org>
 */
/**
 * This class is only a learning path list container with several practical methods for sorting the list and
 * provide links to specific paths
 * @uses	Database.lib.php to use the database
 * @uses	learnpath.class.php to generate learnpath objects to get in the list
 */
class learnpathList {
	var $list = array (); //holds a flat list of learnpaths data from the database
	var $ref_list = array (); //holds a list of references to the learnpaths objects (only filled by get_refs())
	var $alpha_list = array (); //holds a flat list of learnpaths sorted by alphabetical name order
	var $course_code;
	var $user_id;
	var $refs_active = false;

	/**
	 * This method is the constructor for the learnpathList. It gets a list of available learning paths from
	 * the database and creates the learnpath objects. This list depends on the user that is connected
	 * (only displays) items if he has enough permissions to view them.
	 * @param	integer		User ID
	 * @param	string		Optional course code (otherwise we use api_get_course_id())
	 * @return	void
	 */
	function learnpathList($user_id, $course_code = '') {
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		
		$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
		$lp_table = Database::get_course_table ( TABLE_LP_MAIN );
		$this->course_code = $course_code;
		$this->user_id = $user_id;
		$sql = "SELECT t1.* FROM " . $lp_table . " AS t1 WHERE t1.cc='" . $this->course_code . "' ";
		$sql .= " ORDER BY learning_order ASC, name ASC";
		//echo $sql."<br/>";
		$res = api_sql_query ( $sql );
		$names = array ();
		while ( $row = Database::fetch_array ( $res,'ASSOC' ) ) {
			//check if published
			$pub = '';
			$myname = domesticate ( $row ['name'] );
			$pub = 'i';
			
			//$vis = api_get_item_visibility ( api_get_course_info ( $course_code ), TOOL_LEARNPATH, $row ['id'] );
			$sql="SELECT visibility FROM $table_courseware WHERE cw_type='scorm' AND cc='" . $this->course_code . "' AND attribute=".Database::escape($row['id']);
			$vis=Database::get_scalar_value($sql);
			
			$this->list [$row ['id']] = array ('lp_type' => $row ['lp_type'], 
					'lp_name' => stripslashes ( $row ['name'] ), 
					'lp_desc' => stripslashes ( $row ['description'] ), 
					'lp_path' => $row ['path'], 
					'lp_view_mode' => $row ['default_view_mod'], 
					'lp_force_commit' => $row ['force_commit'], 
					'lp_maker' => stripslashes ( $row ['content_maker'] ), 
					'lp_proximity' => $row ['content_local'], 
					'lp_encoding' => $row ['default_encoding'], 
					'lp_visibility' => $vis, 
					'lp_published' => $pub, 
					'lp_prevent_reinit' => $row ['prevent_reinit'], 
					'lp_scorm_debug' => $row ['debug'], 
					'lp_display_order' => $row ['display_order'], 
					'lp_creation_date' => $row ['creation_date'], 
					'lp_preview_image' => stripslashes ( $row ['preview_image'] ), 
					'lp_open_target' => $row ['target'], 
					'lp_learning_order' => $row ["learning_order"], 
					'lp_learning_time' => $row ["learning_time"] );
			$names [$row ['name']] = $row ['id'];
		}
		$this->alpha_list = asort ( $names );
	}

	/**
	 * Gets references to learnpaths for all learnpaths IDs kept in the local list.
	 * This applies a transformation internally on list and ref_list and returns a copy of the refs list
	 * @return	array	List of references to learnpath objects
	 */
	function get_refs() {
		foreach ( $this->list as $id => $dummy ) {
			$this->ref_list [$id] = new learnpath ( $this->course_code, $id, $this->user_id );
		}
		$this->refs_active = true;
		return $this->ref_list;
	}

	/**
	 * Gets a table of the different learnpaths we have at the moment
	 * @return	array	Learnpath info as [lp_id] => ([lp_type]=> ..., [lp_name]=>...,[lp_desc]=>...,[lp_path]=>...)
	 */
	function get_flat_list() {
		return $this->list;
	}
}
?>
