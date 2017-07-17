<?php
/*
 ==============================================================================

 ==============================================================================
 */

/**
 * This (abstract?) class defines the parent attributes and methods for the ZLMS learnpaths and scorm
 * learnpaths. It is used by the scorm class as well as the ZLMS_lp class.
 * @package ZLMS.learnpath
 * @author	Yannick Warnier <ywarnier@beeznest.org>
 * @license	GNU/GPL - See ZLMS license directory for details
 */
/**
 * Defines the learnpath parent class
 * @package ZLMS.learnpath
 */
class learnpath {

	var $attempt = 0; //the number for the current ID view
	var $cc; //course (code) this learnpath is located in
	var $current; //id of the current item the user is viewing
	var $current_score; //the score of the current item
	var $current_time_start; //the time the user loaded this resource (this does not mean he can see it yet)
	var $current_time_stop; //the time the user closed this resource
	var $default_status = 'not attempted';
	var $encoding = SYSTEM_CHARSET;
	var $error = '';
	var $extra_information = ''; //this string can be used by proprietary SCORM contents to store data about the current learnpath
	var $force_commit = false; //for SCORM only - if set to true, will send a scorm LMSCommit() request on each LMSSetValue()
	var $index; //the index of the active learnpath_item in $ordered_items array
	var $items = array();
	var $last; //item_id of last item viewed in the learning path
	var $last_item_seen = 0; //in case we have already come in this learnpath, reuse the last item seen if authorized
	var $license; //which license this course has been given - not used yet on 20060522
	var $lp_id; //DB ID for this learnpath
	var $lp_view_id; //DB ID for lp_view
	var $log_file; //file where to log learnpath API msg
	var $maker; //which maker has conceived the content (ENI, Articulate, ...)
	var $message = '';
	var $mode = 'embedded'; //holds the video display mode (fullscreen or embedded)
	var $name; //learnpath name (they generally have one)
	var $ordered_items = array(); //list of the learnpath items in the order they are to be read
	var $path = ''; //path inside the scorm directory (if scorm)
	var $theme; // the current theme of the learning path
	var $preview_image; // the current image of the learning path

	// Tells if all the items of the learnpath can be tried again. Defaults to "no" (=1)
	var $prevent_reinit = 1;

	var $progress_bar_mode = '%';
	var $progress_db = '0';
	var $proximity; //wether the content is distant or local or unknown
	var $refs_list = array (); //list of items by ref => db_id. Used only for prerequisites match.
	//!!!This array (refs_list) is built differently depending on the nature of the LP.
	//If SCORM, uses ref, if ZLMS, uses id to keep a unique value
	var $type; //type of learnpath. Could be 'ZLMS', 'scorm', 'scorm2004', 'aicc', ...
	//TODO check if this type variable is useful here (instead of just in the controller script)
	var $user_id; //ID of the user that is viewing/using the course
	var $update_queue = array();
	var $scorm_debug = 0;


	var $arrMenu = array(); //array for the menu items

	var $debug = SCORM_DEBUG; //logging level

	var $course_code="";
	var $target='_self';
	var $learning_time=0;
	var $learning_order=0;

	/**
	 * Class constructor. Needs a database handler, a course code and a learnpath id from the database.
	 * Also builds the list of items into $this->items.
	 * @param	string		Course code
	 * @param	integer		Learnpath ID
	 * @param	integer		User ID
	 * @return	boolean		True on success, false on error
	 */
	function learnpath($course, $lp_id, $user_id) {
		//check params
		//check course code
		//获取课程信息
		$this->course_code=api_get_course_code();
		if($this->debug>0){api_scorm_log('New LP - In learnpath::learnpath('.$course.','.$lp_id.','.$user_id.')',__FILE__,__LINE__);}
		if(empty($course)){
			$this->error = 'Course code is empty';
			$this->cc=api_get_course_code();
			return false;
		}else{
			$main_table = Database::get_main_table(TABLE_MAIN_COURSE);
			//$course = Database::escape_string($course);
			$course = $this->escape_string($course);
			$sql = "SELECT * FROM $main_table WHERE code = '$course'";
			if($this->debug>2){api_scorm_log('New LP - learnpath::learnpath() '.__LINE__.' - Querying course: '.$sql,__FILE__,__LINE__);}
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if(Database::num_rows($res)>0)
			{
				$this->cc = $course;
			}
			else
			{
				$this->cc=api_get_course_code();
				$this->error = 'Course code does not exist in database ('.$sql.')';
				return false;
			}
		}
			
		//check learnpath ID 获取LP信息
		if(empty($lp_id))
		{
			$this->error = 'Learnpath ID is empty';
			return false;
		}
		else
		{
			//TODO make it flexible to use any course_code (still using env course code here)
			$lp_table = Database::get_course_table(TABLE_LP_MAIN);

			//$id = Database::escape_integer($id);
			$lp_id = $this->escape_string($lp_id);
			$sql = "SELECT * FROM $lp_table WHERE id = '$lp_id'";
			if($this->debug>2){api_scorm_log('New LP - learnpath::learnpath() '.__LINE__.' - Querying lp: '.$sql,__FILE__,__LINE__);}
			//$res = Database::query($sql);
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if(Database::num_rows($res)>0)
			{
				$this->lp_id = $lp_id;
				$row = Database::fetch_array($res);
				$this->type = $row['lp_type'];
				$this->name = stripslashes($row['name']);
				$this->encoding = $row['default_encoding'];
				$this->proximity = $row['content_local'];
				$this->theme = $row['theme'];
				$this->maker = $row['content_maker'];
				$this->prevent_reinit = $row['prevent_reinit'];
				$this->license = $row['content_license'];
				$this->scorm_debug = $row['debug'];
				$this->js_lib = $row['js_lib'];
				$this->path = $row['path'];
				$this->preview_image= $row['preview_image'];
				$this->author= $row['author'];
				$this->target=$row['target'];
				$this->learning_time=$row["learning_time"];
				$this->learning_order=$row["learning_order"];
					
				if($this->type == 2){
					if($row['force_commit'] == 1){
						$this->force_commit = true;
					}
				}
				$this->mode = $row['default_view_mod'];
			}
			else
			{
				$this->error = 'Learnpath ID does not exist in database ('.$sql.')';
				return false;
			}
		}
			
		//check user ID 获取用户信息
		if(empty($user_id)){
			$this->error = 'User ID is empty';
			return false;
		}
		else
		{
			//$main_table = Database::get_main_user_table();
			$main_table = Database::get_main_table(TABLE_MAIN_USER);
			//$user_id = Database::escape_integer($user_id);
			$user_id = $this->escape_string($user_id);
			$sql = "SELECT * FROM $main_table WHERE user_id = '$user_id'";
			if($this->debug>2){api_scorm_log('New LP - learnpath::learnpath() '.__LINE__.' - Querying user: '.$sql,__FILE__,__LINE__);}
			//$res = Database::query($sql);
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if(Database::num_rows($res)>0)
			{
				$this->user_id = $user_id;
			}
			else
			{
				$this->error = 'User ID does not exist in database ('.$sql.')';
				return false;
			}
		}
		//end of variables checking
			
		//查看学习信息
		//now get the latest attempt from this user on this LP, if available, otherwise create a new one
		$lp_table = Database::get_course_table(TABLE_LP_VIEW);
		//selecting by view_count descending allows to get the highest view_count first
		$sql = "SELECT * FROM $lp_table WHERE lp_id = '$lp_id' AND user_id = '$user_id'";
		 $sql .=" AND cc='".api_get_course_code()."' "; //liyu: V1.4
		$sql .=" ORDER BY view_count DESC";
		if ($this->debug > 2) {
			api_scorm_log('New LP - learnpath::learnpath() - querying lp_view: ' . $sql, __FILE__,__LINE__);
		}
		//$res = Database::query($sql);
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$view_id = 0; //used later to query lp_item_view
		if (Database :: num_rows($res) > 0) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - Found previous view', __FILE__,__LINE__);
			}
			$row = Database :: fetch_array($res);
			$this->attempt = $row['view_count'];
			$this->lp_view_id = $row['id'];
			$this->last_item_seen = $row['last_item'];
			$this->progress_db = $row['progress'];
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - NOT Found previous view', __FILE__,__LINE__);
			}
			$this->attempt = 1;
			$sql_data=array('lp_id'=>$lp_id,'user_id'=>$user_id,'view_count'=>1);
			 $sql_data['cc']=api_get_course_code();
			//$sql_ins = "INSERT INTO $lp_table (lp_id,user_id,view_count) VALUES ($lp_id,$user_id,1)";
			$sql_ins=Database::sql_insert($lp_table,$sql_data);
			$res_ins = api_sql_query($sql_ins, __FILE__, __LINE__);
			$this->lp_view_id = Database :: get_last_insert_id();
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - inserting new lp_view: ' . $sql_ins, __FILE__,__LINE__);
			}
		}

		//初始化学习内容项
		//initialise items
		$lp_item_table = Database::get_course_table(TABLE_LP_ITEM);
		$sql = "SELECT * FROM $lp_item_table WHERE lp_id = '".$this->lp_id."'";
		 $sql .=" AND cc='".api_get_course_code()."' "; //liyu: V1.4
		$sql .=" ORDER BY parent_item_id, display_order";
		$res = api_sql_query($sql, __FILE__, __LINE__);
			
		while($row = Database::fetch_array($res))
		{
			$oItem = '';
			//$this->ordered_items[] = $row['id'];
			switch ($this->type) {
				case 2 : //scorm
					require_once ('scorm.class.php');
					require_once ('scormItem.class.php');
					$oItem = new scormItem('db', $row['id']);
					if (is_object($oItem)) {
						$my_item_id = $oItem->get_id();
						$oItem->set_lp_view($this->lp_view_id);
						$oItem->set_prevent_reinit($this->prevent_reinit);
						// Don't use reference here as the next loop will make the pointed object change
						$this->items[$my_item_id] = $oItem;
						$this->refs_list[$oItem->ref] = $my_item_id;
						if ($this->debug > 2) {
							api_scorm_log('New LP - object with id ' . $my_item_id . ' set in items[]', __FILE__,__LINE__);
						}
					}
					break;

				case 1 :
				default :
					require_once ('learnpathItem.class.php');
					$oItem = new learnpathItem($row['id'], $user_id);
					if (is_object($oItem)) {
						$my_item_id = $oItem->get_id();
						//$oItem->set_lp_view($this->lp_view_id); moved down to when we are sure the item_view exists
						$oItem->set_prevent_reinit($this->prevent_reinit);
						// Don't use reference here as the next loop will make the pointed object change
						$this->items[$my_item_id] = $oItem;
						$this->refs_list[$my_item_id] = $my_item_id;
						if ($this->debug > 2) {
							api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - object with id ' . $my_item_id . ' set in items[]', __FILE__,__LINE__);
						}
					}
					break;
			}

			//items is a list of pointers to all items, classified by DB ID, not SCO id
			if ($row['parent_item_id'] == 0 OR empty ($this->items[$row['parent_item_id']])) {
				$this->items[$row['id']]->set_level(0);
			} else {
				$level = $this->items[$row['parent_item_id']]->get_level() + 1;
				$this->items[$row['id']]->set_level($level);
				if (is_object($this->items[$row['parent_item_id']])) {
					//items is a list of pointers from item DB ids to item objects
					$this->items[$row['parent_item_id']]->add_child($row['id']);
				} else {
					if ($this->debug > 2) {
						api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - The parent item (' . $row['parent_item_id'] . ') of item ' . $row['id'] . ' could not be found', __FILE__,__LINE__);
					}
				}
			}

			//get last viewing vars 获取学习内容项查看学习信息
			$lp_item_view_table = Database :: get_course_table(TABLE_LP_ITEM_VIEW);
			//this query should only return one or zero result
			$sql = "SELECT * FROM $lp_item_view_table  WHERE lp_view_id = " . $this->lp_view_id . " " .
			"AND lp_item_id = " . $row['id'];
			 $sql .=" AND cc='".api_get_course_code()."' "; //liyu: V1.4
			$sql .= " ORDER BY view_count DESC ";
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::learnpath() - Selecting item_views: ' . $sql, __FILE__,__LINE__);
			}
			//get the item status
			$res2 = api_sql_query($sql, __FILE__, __LINE__);
			if (Database :: num_rows($res2) > 0) {
				//if this learnpath has already been used by this user, get his last attempt count and
				//the last item seen back into this object
				//$max = 0;
				$row2 = Database :: fetch_array($res2,"ASSOC");
				if ($this->debug > 2) {
					api_scorm_log('New LP - learnpath::learnpath() - Got item_view: ' . print_r($row2, true), __FILE__,__LINE__);
				}
				$this->items[$row['id']]->set_status($row2['status']);
				if (empty ($row2['status'])) {
					$this->items[$row['id']]->set_status($this->default_status);
				}
				//$this->attempt = $row['view_count'];
				//$this->last_item = $row['id'];
			}
			else //no item found in lp_item_view for this view
			{
				//first attempt from this user. Set attempt to 1 and last_item to 0 (first item available)
				//TODO  if the learnpath has not got attempts activated, always use attempt '1'
				//$this->attempt = 1;
				//$this->last_item = 0;
				$this->items[$row['id']]->set_status($this->default_status);
				//Add that row to the lp_item_view table so that we have something to show in the stats page
				$sql_ins = "INSERT INTO $lp_item_view_table (lp_item_id, lp_view_id, view_count, status) VALUES " .
				"(" . $row['id'] . "," . $this->lp_view_id . ",1,'not attempted')";
				//V1.4
				$sql_data=array('lp_item_id'=>$row["id"],'lp_view_id'=>$this->lp_view_id,'view_count'=>1,'status'=>'not attempted');
				 $sql_data['cc']=api_get_course_code();
				$sql_ins=Database::sql_insert($lp_item_view_table,$sql_data);
				if ($this->debug > 2) {
					api_scorm_log('New LP - learnpath::learnpath() ' . __LINE__ . ' - Inserting blank item_view : ' . $sql_ins, __FILE__,__LINE__);
				}
				$res_ins = api_sql_query($sql_ins, __FILE__, __LINE__);
			}
			//setting the view in the item object
			$this->items[$row['id']]->set_lp_view($this->lp_view_id);
		}

		$this->ordered_items = $this->get_flat_ordered_items_list($this->get_id(), 0);
		$this->max_ordered_items = 0;
		foreach ($this->ordered_items as $index => $dummy) {
			if ($index > $this->max_ordered_items AND !empty ($dummy)) {
				$this->max_ordered_items = $index;
			}
		}
		//TODO define the current item better
		$this->first();
		if ($this->debug > 2) {
			api_scorm_log('New LP - learnpath::learnpath() - End of learnpath constructor for learnpath ' . $this->get_id(), __FILE__,__LINE__);
		}
	}


	/**
	 * 增加LP
	 * Static admin function allowing addition of a learnpath to a course.
	 * @param	string	Course code
	 * @param	string	Learnpath name
	 * @param	string	Learnpath description string, if provided
	 * @param	string	Type of learnpath (default = 'guess', others = 'ZLMS', 'aicc',...)
	 * @param	string	Type of files origin (default = 'zip', others = 'dir','web_dir',...)
	 * @param	string	Zip file containing the learnpath or directory containing the learnpath
	 * @return	integer	The new learnpath ID on success, 0 on failure
	 */
	function add_lp($course, $name, $description = '', $learnpath = 'guess', $origin = 'zip', $zipname = '') {
		global $charset;

		//if($this->debug>0){error_log('New LP - In learnpath::add_lp()',0);}
		//TODO
		$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
		//check course code exists
		//check lp_name doesn't exist, otherwise append something
		$i = 0;
		$name = learnpath :: escape_string($name);
			
		$check_name = "SELECT * FROM $tbl_lp WHERE name = '$name'";
		 $check_name .=" AND cc='".api_get_course_code()."' ";
		//if($this->debug>2){error_log('New LP - Checking the name for new LP: '.$check_name,0);}
		$res_name = api_sql_query($check_name, __FILE__, __LINE__);
		while (Database :: num_rows($res_name)) {
			//there is already one such name, update the current one a bit
			$i++;
			$name = $name . ' - ' . $i;
			$check_name = "SELECT * FROM $tbl_lp WHERE name = '$name'";
			 $check_name .=" AND cc='".api_get_course_code()."' ";
			//if($this->debug>2){error_log('New LP - Checking the name for new LP: '.$check_name,0);}
			$res_name = api_sql_query($check_name, __FILE__, __LINE__);
		}
		//new name does not exist yet; keep it
		//escape description
		$description = learnpath :: escape_string(api_htmlentities($description, ENT_QUOTES, $charset)); //Kevin: added htmlentities()
		$type = 1;
		switch ($learnpath) {
			case 'guess' :
				break;
			case 'webcs' :
				$type = 1;
				break;
			case 'aicc' :
				break;
		}
		switch ($origin) {
			case 'zip' :
				//check zipname string. If empty, we are currently creating a new ZLMS learnpath
				break;
			case 'manual' :
			default :
				$get_max = "SELECT MAX(display_order) FROM $tbl_lp";
				 $get_max .=" WHERE cc='".api_get_course_code()."' ";
				$res_max = api_sql_query($get_max, __FILE__, __LINE__);
				if (Database :: num_rows($res_max) < 1) {
					$dsp = 1;
				} else {
					$row = Database :: fetch_array($res_max);
					$dsp = $row[0] + 1;
				}

				$sql_data=array('lp_type'=>$type,'name'=>$name,'description'=>$description,'path'=>'','default_view_mod'=>'embedded',
				'default_encoding'=>'UTF-8','display_order'=>$dsp,'content_maker'=>'WebCS', 'content_local'=>'local','js_lib'=>'');
				 $sql_data['cc']=api_get_course_code();
				$sql_insert=Database::sql_insert($tbl_lp,$sql_data);
				$res_insert = api_sql_query($sql_insert, __FILE__, __LINE__);
				$id = Database :: get_last_insert_id();
				if ($id > 0) {
					//insert into item_property
					api_item_property_update(api_get_course_info(), TOOL_LEARNPATH, $id, 'LearnpathAdded', api_get_user_id());
					return $id;
				}
				break;
		}
	}

	/**
	 * Appends a message to the message attribute
	 * @param	string	Message to append.
	 */
	function append_message($string) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::append_message()', __FILE__,__LINE__);
		}
		$this->message .= $string;
	}

	/**
	 * 若一学习内容项被完成或通过，则自动完成其父项(递归）
	 * Autocompletes the parents of an item in case it's been completed or passed
	 * @param	integer	Optional ID of the item from which to look for parents
	 */
	function autocomplete_parents($item) {
		if ($this->debug > 0) {
			api_scorm_log('In learnpath::autocomplete_parents()', __FILE__,__LINE__);
		}
		if (empty ($item)) {
			$item = $this->current;
		}
		$parent_id = $this->items[$item]->get_parent();
		if ($this->debug > 2) {
			api_scorm_log('New LP - autocompleting parent of item ' . $item . ' (item ' . $parent_id . ')', __FILE__,__LINE__);
		}
		if (is_object($this->items[$item]) and !empty ($parent_id)) {
			//if $item points to an object and there is a parent
			if ($this->debug > 2) {
				api_scorm_log('New LP - ' . $item . ' is an item, proceed', __FILE__,__LINE__);
			}
			$current_item = & $this->items[$item];
			$parent = & $this->items[$parent_id]; //get the parent
			//new experiment including failed and browsed in completed status
			$current_status = $current_item->get_status();
			if ($current_item->is_done() || $current_status == 'browsed' || $current_status == 'failed') {
				//if the current item is completed or passes or succeeded
				$completed = true;
				if ($this->debug > 2) {
					api_scorm_log('New LP - Status of current item is alright', __FILE__,__LINE__);
				}
				foreach ($parent->get_children() as $child) {
					//check all his brothers (his parent's children) for completion status
					if ($child != $item) {
						if ($this->debug > 2) {
							api_scorm_log('New LP - Looking at brother with ID ' . $child . ', status is ' . $this->items[$child]->get_status(), __FILE__,__LINE__);
						}
						//if($this->items[$child]->status_is(array('completed','passed','succeeded')))
						//Trying completing parents of failed and browsed items as well
						if ($this->items[$child]->status_is(array ('completed','passed','succeeded','browsed',	'failed'))) {
							//keep completion status to true
						} else {
							if ($this->debug > 2) {
								api_scorm_log('New LP - Found one incomplete child of ' . $parent_id . ': ' . $child . ' is ' . $this->items[$child]->get_status(), __FILE__,__LINE__);
							}
							$completed = false;
						}
					}
				}
				if ($completed == true) { //if all the children were completed
					$parent->set_status('completed');
					$parent->save(false, $this->prerequisites_match($parent->get_id()));
					$this->update_queue[$parent->get_id()] = $parent->get_status();
					if ($this->debug > 2) {
						api_scorm_log('New LP - Added parent to update queue ' . print_r($this->update_queue, true), __FILE__,__LINE__);
					}
					$this->autocomplete_parents($parent->get_id()); //recursive call
				}
			} else {
				//error_log('New LP - status of current item is not enough to get bothered with it',0);
			}
		}
	}

	/**
	 * Autosaves the current results into the database for the whole learnpath
	 */
	function autosave() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::autosave()', __FILE__,__LINE__);
		}
		//TODO add aditionnal save operations for the learnpath itself
	}

	/**
	 * Clears the message attribute
	 */
	function clear_message() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::clear_message()', __FILE__,__LINE__);
		}
		$this->message = '';
	}
	/**
	 * Closes the current resource
	 *
	 * Stops the timer
	 * Saves into the database if required
	 * Clears the current resource data from this object
	 * @return	boolean	True on success, false on failure
	 */

	function close() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::close()', __FILE__,__LINE__);
		}
		if (empty ($this->lp_id)) {
			$this->error = 'Trying to close this learnpath but no ID is set';
			return false;
		}
		$this->current_time_stop = time();
		if ($this->save) {
			$learnpath_view_table = Database :: get_course_table(TABLE_LP_VIEW);
			
		}
		$this->ordered_items = array ();
		$this->index = 0;
		unset ($this->lp_id);
		//unset other stuff
		return true;
	}

	/**
	 * 移除LP
	 * Static admin function allowing removal of a learnpath
	 * @param	string	Course code
	 * @param	integer	Learnpath ID
	 * @param	string	Whether to delete data or keep it (default: 'keep', others: 'remove')
	 * @return	boolean	True on success, false on failure (might change that to return number of elements deleted)
	 */
	function delete($course = null, $id = null, $delete = 'keep') {
		if (!empty ($id) && ($id != $this->lp_id)) {
			return false;
		}
		$course_code=api_get_course_code();

		$lp = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_view = Database :: get_course_table(TABLE_LP_VIEW);
		$lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$lp_item_view = Database :: get_course_table(TABLE_LP_ITEM_VIEW);
		$lp_scoes_track=Database::get_course_table(TABLE_SCORM_SCOES_TRACK);
		$tbl_track_cw=Database::get_main_table(TABLE_STATISTIC_TRACK_E_CW);
		$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );

		//删除crs_lp_item_ivew
		foreach ($this->items as $id => $dummy) {
			//$this->items[$id]->delete();
			$sql_del_view = "DELETE FROM $lp_item_view WHERE lp_item_id = '" . $id . "' AND cc='".escape($course_code)."' ";
			api_sql_query($sql_del_view, __FILE__, __LINE__);
		}

		//删除crs_lp_view
		$sql_del_view = "DELETE FROM $lp_view WHERE lp_id = " . $this->lp_id." AND cc='".escape($course_code)."'";
		$res_del_view = api_sql_query($sql_del_view, __FILE__, __LINE__);
		$this->toggle_publish($this->lp_id, 'i');

		if ($this->type == 2) {
			//删除文件
			$sql = "SELECT path FROM $lp WHERE id = " . $this->lp_id;
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if (Database :: num_rows($res) > 0) {
				$row = Database :: fetch_array($res);
				$path = $row['path'];
				$sql = "SELECT id FROM $lp WHERE path = '".$path."' AND id != " . $this->lp_id." AND cc='".escape($course_code)."' ";
				$res = api_sql_query($sql, __FILE__, __LINE__);
				if (Database :: num_rows($res) > 0) { //another learning path uses this directory, so don't delete it
					if ($this->debug > 2) {
						api_scorm_log('New LP - In learnpath::delete(), found other LP using path ' . $path . ', keeping directory', __FILE__, __LINE__);
					}
				} else {
					//$course_rel_dir = api_get_course_path() . '/scorm/'; 
					$course_scorm_dir = api_get_path(SYS_COURSE_PATH) . api_get_course_path() . '/scorm/'; 
					if ($delete == 'remove' && is_dir($course_scorm_dir . $path) and !empty ($course_scorm_dir)) {
						if ($this->debug > 2) {
							api_scorm_log('New LP - In learnpath::delete(), found SCORM, deleting directory: ' . $course_scorm_dir . $path, __FILE__, __LINE__);
						}
						if(substr ( $path, - 1 ) == ".") $path=substr ( $path, 0, - 1 );
						remove_dir($course_scorm_dir . $path);
					}
				}
			}
		}
		
		//删除crs_lp_scoes_track
		$sql="DELETE FROM $lp_scoes_track WHERE scormid='".$this->lp_id."' AND cc='".escape($course_code)."' ";
		$res_del = api_sql_query($sql, __FILE__, __LINE__);
		
		//删除crs_lp_item
		$sql_del_item="DELETE FROM $lp_item WHERE lp_id = " . $this->lp_id." AND cc='".escape($course_code)."' ";
		$res_del_item = api_sql_query($sql_del_item, __FILE__, __LINE__);

		//删除crs_lp
		$sql_del_lp = "DELETE FROM $lp WHERE id = " . $this->lp_id;
		$res_del_lp = api_sql_query($sql_del_lp, __FILE__, __LINE__);
		$this->update_display_order(); 
		api_item_property_update(api_get_course_info(), TOOL_LEARNPATH, $this->lp_id, 'delete', api_get_user_id());
		
		$sql="SELECT id FROM $tbl_courseware WHERE cc='".escape($course_code)."' AND cw_type='scorm' AND attribute=".Database::escape($this->lp_id);
		$cw_id=Database::get_scalar_value($sql);
		
		$sql="DELETE FROM $tbl_track_cw WHERE cw_id='".escape($cw_id)."' AND cc='".escape($course_code)."' ";
		$res_del = api_sql_query($sql, __FILE__, __LINE__);
		
		$sql="DELETE FROM $tbl_courseware WHERE cc='".escape($course_code)."' AND cw_type='scorm' AND attribute=".Database::escape($this->lp_id);
		api_sql_query($sql, __FILE__, __LINE__);
	}

	/**
	 * Removes all the children of one item - dangerous!
	 * @param	integer	Element ID of which children have to be removed
	 * @return	integer	Total number of children removed
	 */
	function delete_children_items($id) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::delete_children_items(' . $id . ')', __FILE__,__LINE__);
		}
		$num = 0;
		if (empty ($id) || $id != strval(intval($id))) {
			return false;
		}
		$lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$sql = "SELECT * FROM $lp_item WHERE parent_item_id = $id";
		 $sql .=" AND cc='".api_get_course_code()."' ";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		while ($row = Database :: fetch_array($res)) {
			$num += $this->delete_children_items($row['id']);
			$sql_del = "DELETE FROM $lp_item WHERE id = " . $row['id'];
			$res_del = api_sql_query($sql_del, __FILE__, __LINE__);
			$num++;
		}
		return $num;
	}

	/**
	 * Removes an item from the current learnpath
	 * @param	integer	Elem ID (0 if first)
	 * @param	integer	Whether to remove the resource/data from the system or leave it (default: 'keep', others 'remove')
	 * @return	integer	Number of elements moved
	 * @todo implement resource removal
	 */
	function delete_item($id, $remove = 'keep') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::delete_item()', __FILE__,__LINE__);
		}
		//TODO - implement the resource removal
		if (empty ($id) || $id != strval(intval($id))) {
			return false;
		}
		//first select item to get previous, next, and display order
		$lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$sql_sel = "SELECT * FROM $lp_item WHERE id = $id";
		$res_sel = api_sql_query($sql_sel, __FILE__, __LINE__);
		if (Database :: num_rows($res_sel) < 1) {
			return false;
		}
		$row = Database :: fetch_array($res_sel);
		$previous = $row['previous_item_id'];
		$next = $row['next_item_id'];
		$display = $row['display_order'];
		$parent = $row['parent_item_id'];
		$lp = $row['lp_id'];

		//delete children items
		$num = $this->delete_children_items($id);
		if ($this->debug > 2) {
			api_scorm_log('New LP - learnpath::delete_item() - deleted ' . $num . ' children of element ' . $id, __FILE__,__LINE__);
		}

		//now delete the item
		$sql_del = "DELETE FROM $lp_item WHERE id = $id";
		if ($this->debug > 2) {
			api_scorm_log('New LP - Deleting item: ' . $sql_del, __FILE__,__LINE__);
		}
		$res_del = api_sql_query($sql_del, __FILE__, __LINE__);

		//now update surrounding items
		$sql_upd = "UPDATE $lp_item SET next_item_id = $next WHERE id = $previous";
		$res_upd = api_sql_query($sql_upd, __FILE__, __LINE__);
		$sql_upd = "UPDATE $lp_item SET previous_item_id = $previous WHERE id = $next";
		$res_upd = api_sql_query($sql_upd, __FILE__, __LINE__);

		//now update all following items with new display order
		$sql_all = "UPDATE $lp_item SET display_order = display_order-1 WHERE lp_id = $lp AND parent_item_id = $parent AND display_order > $display";
		 $sql_all .=" AND cc='".api_get_course_code()."' ";
		$res_all = api_sql_query($sql_all, __FILE__, __LINE__);

		// remove from search engine if enabled
		if (api_get_setting('search_enabled') == 'true') {
			$tbl_se_ref = Database :: get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
			$sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s AND ref_id_second_level=%d LIMIT 1';
			$sql = sprintf($sql, $tbl_se_ref, $this->cc, TOOL_LEARNPATH, $lp, $id);
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if (Database :: num_rows($res) > 0) {
				$row2 = Database :: fetch_array($res);
				require_once (api_get_path(LIBRARY_PATH) . 'search/ZLMSIndexer.class.php');
				$di = new ZLMSIndexer();
				$di->remove_document((int) $row2['search_did']);
			}
			$sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s AND ref_id_second_level=%d LIMIT 1';
			$sql = sprintf($sql, $tbl_se_ref, $this->cc, TOOL_LEARNPATH, $lp, $id);
			api_sql_query($sql, __FILE__, __LINE__);
		}
	}

	/**
	 * Escapes a string with the available database escape function
	 * @param	string	String to escape
	 * @return	string	String escaped
	 */
	function escape_string($string) {
		//if($this->debug>0){api_scorm_log('New LP - In learnpath::escape_string('.$string.')',__FILE__,__LINE__);}
		return Database :: escape_string($string);
	}


	/**
	 * 获取同属一父子学习内容项的所有章节
	 * Gets all the chapters belonging to the same parent as the item/chapter given
	 * Can also be called as abstract method
	 * @param	integer	Item ID
	 * @return	array	A list of all the "brother items" (or an empty array on failure)
	 */
	function get_brother_chapters($id) {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_brother_chapters()', __FILE__,__LINE__);
		}

		if (empty ($id) OR $id != strval(intval($id))) {
			return array ();
		}

		$lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$sql_parent = "SELECT * FROM $lp_item WHERE id = $id AND item_type='webcs_chapter'";
		 $sql_parent .=" AND cc='".api_get_course_code()."' ";
		$res_parent = api_sql_query($sql_parent, __FILE__, __LINE__);
		if (Database :: num_rows($res_parent) > 0) {
			$row_parent = Database :: fetch_array($res_parent);
			$parent = $row_parent['parent_item_id'];
			$sql_bros = "SELECT * FROM $lp_item WHERE parent_item_id = $parent AND id = $id AND item_type='webcs_chapter'";
			 $sql_bros .=" AND cc='".api_get_course_code()."' ";
			$sql_bros .=" ORDER BY display_order";
			$res_bros = api_sql_query($sql_bros, __FILE__, __LINE__);
			$list = array ();
			while ($row_bro = Database :: fetch_array($res_bros)) {
				$list[] = $row_bro;
			}
			return $list;
		}

		return array ();

	}

	/**

	* Gets all the items belonging to the same parent as the item given
	* Can also be called as abstract method
	* @param	integer	Item ID
	* @return	array	A list of all the "brother items" (or an empty array on failure)
	*/
	function get_brother_items($id) {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_brother_items(' . $id . ')', __FILE__,__LINE__);
		}

		if (empty ($id) OR $id != strval(intval($id))) {
			return array ();
		}

		$lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$sql_parent = "SELECT * FROM $lp_item WHERE id = $id";
		 $sql_parent .=" AND cc='".api_get_course_code()."' ";
		$res_parent = api_sql_query($sql_parent, __FILE__, __LINE__);
		if (Database :: num_rows($res_parent) > 0) {
			$row_parent = Database :: fetch_array($res_parent);
			$parent = $row_parent['parent_item_id'];
			$sql_bros = "SELECT * FROM $lp_item WHERE parent_item_id = ".$parent;
			 $sql_bros .=" AND cc='".api_get_course_code()."' ";
			$sql_bros .= "ORDER BY display_order";
			$res_bros = api_sql_query($sql_bros, __FILE__, __LINE__);
			$list = array ();
			while ($row_bro = Database :: fetch_array($res_bros)) {
				$list[] = $row_bro;
			}
			return $list;
		}

		return array ();

	}


	/**
	 * 获取完成的item项
	 * Gets the number of items currently completed
	 * @return integer The number of items currently completed
	 */
	function get_complete_items_count() {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_complete_items_count()', __FILE__,__LINE__);
		}

		$i = 0;

		foreach ($this->items as $id => $dummy) {
			//if($this->items[$id]->status_is(array('completed','passed','succeeded'))){
			//Trying failed and browsed considered "progressed" as well
			if ($this->items[$id]->status_is(array ('completed','passed','succeeded','browsed','failed'))
			&& $this->items[$id]->get_type() != 'webcs_chapter' && $this->items[$id]->get_type() != 'dir') {
				$i++;
			}
		}

		return $i;

	}

	/**
	 * Gets the current item ID
	 * @return	integer	The current learnpath item id
	 */
	function get_current_item_id() {
		$current = 0;
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_current_item_id()', __FILE__,__LINE__);
		}
		if (!empty ($this->current)) {
			$current = $this->current;
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_current_item_id() - Returning ' . $current, __FILE__,__LINE__);
		}
		return $current;
	}

	/** Force to get the first learnpath item id
	 * @return	integer	The current learnpath item id
	 */
	function get_first_item_id() {
		$current = 0;
		if (is_array($this->ordered_items)) {
			$current = $this->ordered_items[0];
		}
		return $current;
	}


	/**
	 * Gets the total number of items available for viewing in this SCORM
	 * @return	integer	The total number of items
	 */
	function get_total_items_count() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_total_items_count()', __FILE__,__LINE__);
		}
		return count($this->items);
	}


	/**
	 * Gets the total number of items available for viewing in this SCORM but without chapters
	 * @return	integer	The total no-chapters number of items
	 */
	function get_total_items_count_without_chapters() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_total_items_count_without_chapters()',__FILE__,__LINE__);
		}
		$total = 0;
		foreach ($this->items as $temp => $temp2) {
			if (!in_array($temp2->get_type(), array ('webcs_chapter','chapter','dir')))
			$total++;
		}
		return $total;
	}

	/**
	 * Gets the first element URL.
	 * @return	string	URL to load into the viewer
	 */
	function first() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::first()', __FILE__,__LINE__);
		}
		//test if the last_item_seen exists and is not a dir
		if (count($this->ordered_items) == 0) {
			$this->index = 0;
		}
		if (!empty ($this->last_item_seen) && !empty ($this->items[$this->last_item_seen]) && $this->items[$this->last_item_seen]->get_type() != 'dir' && $this->items[$this->last_item_seen]->get_type() != 'webcs_chapter' && $this->items[$this->last_item_seen]->is_done() != true) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::first() - Last item seen is ' . $this->last_item_seen . ' of type ' . $this->items[$this->last_item_seen]->get_type(), __FILE__,__LINE__);
			}
			$index = -1;
			foreach ($this->ordered_items as $myindex => $item_id) {
				if ($item_id == $this->last_item_seen) {
					$index = $myindex;
					break;
				}
			}
			if ($index == -1) {
				//index hasn't changed, so item not found - panic (this shouldn't happen)
				if ($this->debug > 2) {
					api_scorm_log('New LP - Last item (' . $this->last_item_seen . ') was found in items but not in ordered_items, panic!', __FILE__,__LINE__);
				}
				return false;
			} else {
				$this->last = $this->last_item_seen;
				$this->current = $this->last_item_seen;
				$this->index = $index;
			}
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::first() - No last item seen', __FILE__,__LINE__);
			}
			$index = 0;
			//loop through all ordered items and stop at the first item that is
			//not a directory *and* that has not been completed yet
			while (!empty ($this->ordered_items[$index]) AND is_a($this->items[$this->ordered_items[$index]], 'learnpathItem') AND ($this->items[$this->ordered_items[$index]]->get_type() == 'dir' OR $this->items[$this->ordered_items[$index]]->get_type() == 'webcs_chapter' OR $this->items[$this->ordered_items[$index]]->is_done() === true) AND $index < $this->max_ordered_items) {
				$index++;
			}
			$this->last = $this->current;
			//current is
			$this->current = $this->ordered_items[$index];
			$this->index = $index;
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::first() - No last item seen. New last = ' . $this->last . '(' . $this->ordered_items[$index] . ')', __FILE__,__LINE__);
			}
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::first() - First item is ' . $this->get_current_item_id(),__FILE__,__LINE__);
		}
	}

	/**

	* Gets the information about an item in a format usable as JavaScript to update
	* the JS API by just printing this content into the <head> section of the message frame
	* @param	integer		Item ID
	* @return	string
	*/
	function get_js_info($item_id = '') {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_js_info(' . $item_id . ')', __FILE__,__LINE__);
		}

		$info = '';

		$item_id = $this->escape_string($item_id);

		if (!empty ($item_id) && is_object($this->items[$item_id])) {

			//if item is defined, return values from DB

			$oItem = $this->items[$item_id];

			$info .= '<script language="javascript">';
			$info .= "top.set_score(" . $oItem->get_score() . ");\n";
			$info .= "top.set_max(" . $oItem->get_max() . ");\n";
			$info .= "top.set_min(" . $oItem->get_min() . ");\n";
			$info .= "top.set_lesson_status('" . $oItem->get_status() . "');";
			$info .= "top.set_session_time('" . $oItem->get_scorm_time('js') . "');";
			$info .= "top.set_suspend_data('" . $oItem->get_suspend_data() . "');";
			$info .= "top.set_saved_lesson_status('" . $oItem->get_status() . "');";
			$info .= "top.set_flag_synchronized();";
			$info .= '</script>';

			if ($this->debug > 2) {
				api_scorm_log('New LP - in learnpath::get_js_info(' . $item_id . ') - returning: ' . $info, __FILE__,__LINE__);
			}

			return $info;

		} else {

			//if item_id is empty, just update to default SCORM data

			$info .= '<script language="javascript">';
			$info .= "top.set_score(" . learnpathItem :: get_score() . ");\n";
			$info .= "top.set_max(" . learnpathItem :: get_max() . ");\n";
			$info .= "top.set_min(" . learnpathItem :: get_min() . ");\n";
			$info .= "top.set_lesson_status('" . learnpathItem :: get_status() . "');";
			$info .= "top.set_session_time('" . learnpathItem :: get_scorm_time('js') . "');";
			$info .= "top.set_suspend_data('" . learnpathItem :: get_suspend_data() . "');";
			$info .= "top.set_saved_lesson_status('" . learnpathItem :: get_status() . "');";
			$info .= "top.set_flag_synchronized();";

			$info .= '</script>';

			if ($this->debug > 2) {
				api_scorm_log('New LP - in learnpath::get_js_info(' . $item_id . ') - returning: ' . $info, __FILE__,__LINE__);
			}

			return $info;

		}

	}
	/**
	 * Gets the js library from the database
	 * @return	string	The name of the javascript library to be used
	 */
	function get_js_lib() {
		$lib = '';
		if (!empty ($this->js_lib)) {
			$lib = $this->js_lib;
		}
		return $lib;
	}


	/**
	 * Gets the learnpath database ID
	 * @return	integer	Learnpath ID in the lp table
	 */
	function get_id() {
		//if($this->debug>0){error_log('New LP - In learnpath::get_id()',0);}
		if (!empty ($this->lp_id)) {
			return $this->lp_id;
		} else {
			return 0;
		}
	}

	/**

	* Gets the last element URL.
	* @return string URL to load into the viewer
	*/
	function get_last() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_last()', __FILE__,__LINE__);
		}
		$this->index = count($this->ordered_items) - 1;
		return $this->ordered_items[$this->index];
	}


	/**
	 * 获取URL队列中的下一个resource
	 * Gets the next resource in queue (url).
	 * @return	string	URL to load into the viewer
	 */
	function get_next_index() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_next_index()', __FILE__,__LINE__);
		}
		//TODO
		$index = $this->index;
		$index++;
		if ($this->debug > 2) {
			api_scorm_log('New LP - Now looking at ordered_items[' . ($index) . '] - type is ' . $this->items[$this->ordered_items[$index]]->type, __FILE__,__LINE__);
		}
		while (!empty ($this->ordered_items[$index]) AND ($this->items[$this->ordered_items[$index]]->get_type() == 'dir' || $this->items[$this->ordered_items[$index]]->get_type() == 'webcs_chapter') AND $index < $this->max_ordered_items) {
			$index++;
			if ($index == $this->max_ordered_items) {
				return $this->index;
			}
		}
		if (empty ($this->ordered_items[$index])) {
			return $this->index;
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - index is now ' . $index, __FILE__,__LINE__);
		}
		return $index;
	}

	/**
	 * Gets item_id for the next element
	 * @return	integer	Next item (DB) ID
	 */
	function get_next_item_id() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_next_item_id()', __FILE__,__LINE__);
		}
		$new_index = $this->get_next_index();
		if (!empty ($new_index)) {
			if (isset ($this->ordered_items[$new_index])) {
				if ($this->debug > 2) {
					api_scorm_log('New LP - In learnpath::get_next_index() - Returning ' . $this->ordered_items[$new_index], __FILE__,__LINE__);
				}
				return $this->ordered_items[$new_index];
			}
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_next_index() - Problem - Returning 0', __FILE__,__LINE__);
		}
		return 0;
	}

	/**
	 * 确定上传导入包的类型（scorm,scorm2004,aicc,ppt)
	 * Returns the package type ('scorm','aicc','scorm2004','ZLMS','ppt'...)
	 *
	 * Generally, the package provided is in the form of a zip file, so the function
	 * has been written to test a zip file. If not a zip, the function will return the
	 * default return value: ''
	 * @param	string	the path to the file
	 * @param	string 	the original name of the file
	 * @return	string	'scorm','aicc','scorm2004','ZLMS' or '' if the package cannot be recognized
	 */

	function get_package_type($file_path, $file_name) {

		//get name of the zip file without the extension
		$file_info = pathinfo($file_path);
		$filename = $file_info['basename']; //name including extension
		$extension = $file_info['extension']; //extension only
		$package_type = '';
                $p_ppt2lp=  getgpc('ppt2lp');
		if (!empty ($p_ppt2lp) && !in_array(strtolower($extension), array (	'dll',	'exe'))) {
			return 'ppt';
			/*if(OOGIE_CONVERT_METHOD==PPT_CONVERT_METHOD_OPENOFFICE){
			 return 'oogie';
			 }
			 if(OOGIE_CONVERT_METHOD==PPT_CONVERT_METHOD_MSOFFICE){
			 return 'ppt';
			 }*/
		}
                $p_woogie=  getgpc('woogie');
		if (!empty ($p_woogie) && !in_array(strtolower($extension), array ('dll',	'exe'	))) {
			return 'woogie';
		}

		$file_base_name = str_replace('.' . $extension, '', $filename); //filename without its extension
			
		//api_error_log("Import SCORM: begun unzip...",__FILE__,__LINE__,"scorm.log");
		$zipFile = new pclZip($file_path);
		//api_error_log("Import SCORM: initial pclzip=".(is_object($zipFile)?"is object":"is not object"),__FILE__,__LINE__,"scorm.log");
		$zipContentArray = $zipFile->listContent(); // Check the zip content (real size and file extension)
		//api_error_log($zipContentArray,__FILE__,__LINE__,"scorm.log");
		$at_root = false;
		$manifest = '';
		//echo $file_path."::";var_dump($zipContentArray); var_dump($file_info);exit;

		//the following loop should be stopped as soon as we found the right imsmanifest.xml (how to recognize it?)
		if (is_array($zipContentArray) && count($zipContentArray) > 0) {
			foreach ($zipContentArray as $thisContent) {
				if (preg_match('~.(php.*|phtml)$~i', $thisContent['filename'])) {
					//New behaviour: Don't do anything. These files will be removed in scorm::import_package
				}
				elseif (stristr($thisContent['filename'], 'imsmanifest.xml') !== FALSE) {
					$manifest = $thisContent['filename']; //just the relative directory inside scorm/
					$package_type = 'scorm';
					break; //exit the foreach loop
				}
				elseif (preg_match('/aicc\//i', $thisContent['filename']) != false) {
					//if found an aicc directory... (!= false means it cannot be false (error) or 0 (no match))
					$package_type = 'aicc';
					//break;//don't exit the loop, because if we find an imsmanifest afterwards, we want it, not the AICC
				} else {
					$package_type = '';
				}
			}
		}
		return $package_type;
	}

	/**
	 * Gets the previous resource in queue (url). Also initialises time values for this viewing
	 * @return string URL to load into the viewer
	 */
	function get_previous_index() {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_previous_index()', __FILE__,__LINE__);
		}

		$index = $this->index;
		if (isset ($this->ordered_items[$index -1])) {

			$index--;
			while (isset ($this->ordered_items[$index]) AND ($this->items[$this->ordered_items[$index]]->get_type() == 'dir' || $this->items[$this->ordered_items[$index]]->get_type() == 'webcs_chapter')) {

				$index--;
				if ($index < 0) {
					return $this->index;
				}
			}
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - get_previous_index() - there was no previous index available, reusing ' . $index, __FILE__,__LINE__);
			}
			//no previous item
		}
		return $index;
	}

	/**
	 * Gets item_id for the next element
	 * @return	integer	Previous item (DB) ID
	 */
	function get_previous_item_id() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_previous_item_id()', __FILE__,__LINE__);
		}
		$new_index = $this->get_previous_index();
		return $this->ordered_items[$new_index];
	}

	/**
	 * Gets the progress value from the progress_db attribute
	 * @return	integer	Current progress value
	 */
	function get_progress() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_progress()', __FILE__,__LINE__);
		}
		if (!empty ($this->progress_db)) {
			return $this->progress_db;
		}
		return 0;
	}

	/**
	 * Gets the progress bar mode
	 * @return	string	The progress bar mode attribute
	 */
	function get_progress_bar_mode() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_progress_bar_mode()', __FILE__,__LINE__);
		}
		if (!empty ($this->progress_bar_mode)) {
			return $this->progress_bar_mode;
		} else {
			return '%';
		}
	}

	/**
	 * Gets the learnpath proximity (remote or local)
	 * @return	string	Learnpath proximity
	 */
	function get_proximity() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_proximity()', __FILE__,__LINE__);
		}
		if (!empty ($this->proximity)) {
			return $this->proximity;
		} else {
			return '';
		}
	}


	/**
	 * Gets the learnpath image
	 * @return	string	Web URL of the LP image
	 */
	function get_preview_image() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_preview_image()', __FILE__,__LINE__);
		}
		if (!empty ($this->preview_image)) {
			return $this->preview_image;
		} else {
			return '';
		}
	}

	/**
	 * Gets the learnpath author
	 * @return	string	LP's author
	 */
	function get_author() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_author()', __FILE__,__LINE__);
		}
		if (!empty ($this->author)) {
			return $this->author;
		} else {
			return '';
		}
	}

	function get_learning_time() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_learning_time()', __FILE__,__LINE__);
		}
		if (!empty ($this->learning_time)) {
			return $this->learning_time;
		} else {
			return 0;
		}
	}

	function get_learning_order() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_learning_order()', __FILE__,__LINE__);
		}
		if (!empty ($this->learning_order)) {
			return $this->learning_order;
		} else {
			return 0;
		}
	}

	/**
	 * 生成指定item的新prerequisites(先决条件,前提 )字符串
	 * Generate a new prerequisites string for a given item. If this item was a sco and
	 * its prerequisites were strings (instead of IDs), then transform those strings into
	 * IDs, knowing that SCORM IDs are kept in the "ref" field of the lp_item table.
	 * Prefix all item IDs that end-up in the prerequisites string by "ITEM_" to use the
	 * same rule as the scorm_export() method
	 * @param	integer		Item ID
	 * @return	string		Prerequisites string ready for the export as SCORM
	 */
	function get_scorm_prereq_string($item_id) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_scorm_prereq_string()', __FILE__,__LINE__);
		}
		if (!is_object($this->items[$item_id])) {
			return false;
		}
		$oItem = $this->items[$item_id];
		$prereq = $oItem->get_prereq_string();
		if (empty ($prereq)) {
			return '';
		}
		if (preg_match('/^\d+$/', $prereq) && is_object($this->items[$prereq])) { //if the prerequisite is a simple integer ID and this ID exists as an item ID,
			//then simply return it (with the ITEM_ prefix)
			return 'ITEM_' . $prereq;
		} else {
			if (isset ($this->refs_list[$prereq])) {
				//it's a simple string item from which the ID can be found in the refs list
				//so we can transform it directly to an ID for export
				return 'ITEM_' . $this->refs_list[$prereq];
			} else {
				//last case, if it's a complex form, then find all the IDs (SCORM strings)
				//and replace them, one by one, by the internal IDs (ZLMS db)
				//TODO modify the '*' replacement to replace the multiplier in front of it
				//by a space as well
				$find = array ( '&', '|', '~', '=', '<>', '{', '}', '*', '(', ')' );
				$replace = array ( ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' );
				$prereq_mod = str_replace($find, $replace, $prereq);
				$ids = split(' ', $prereq_mod);
				foreach ($ids as $id) {
					$id = trim($id);
					if (isset ($this->refs_list[$id])) {
						$prereq = preg_replace('/[^a-zA-Z_0-9](' . $id . ')[^a-zA-Z_0-9]/', 'ITEM_' . $this->refs_list[$id], $prereq);
					}
				}
				api_scorm_log('New LP - In learnpath::get_scorm_prereq_string(): returning modified string: ' . $prereq,__FILE__,__LINE__);
				return $prereq;
			}
		}
	}

	/**
	 * Returns the XML DOM document's node
	 * @param	resource	Reference to a list of objects to search for the given ITEM_*
	 * @param	string		The identifier to look for
	 * @return	mixed		The reference to the element found with that identifier. False if not found
	 */
	function get_scorm_xml_node(& $children, $id) {
		for ($i = 0; $i < $children->length; $i++) {
			$item_temp = $children->item($i);
			if ($item_temp->nodeName == 'item') {
				if ($item_temp->getAttribute('identifier') == $id) {
					return $item_temp;
				}
			}
			$subchildren = $item_temp->childNodes;
			if ($subchildren->length > 0) {
				$val = $this->get_scorm_xml_node($subchildren, $id);
				if (is_object($val)) {
					return $val;
				}
			}
		}
		return false;
	}


	/**
	 * 获取所有LP项的状态列表
	 * Gets the status list for all LP's items
	 * @return	array	Array of [index] => [item ID => current status]
	 */
	function get_items_status_list() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_items_status_list()', __FILE__,__LINE__);
		}
		$list = array ();
		foreach ($this->ordered_items as $item_id) {
			$list[] = array (
			$item_id => $this->items[$item_id]->get_status()
			);
		}
		return $list;
	}


	/**
	 * 生成并返回LP内容的表格, 平面(没有级别)的列表内容
	 * Generate and return the table of contents for this learnpath. The (flat) table returned can be
	 * used by get_html_toc() to be ready to display
	 * @return	array	TOC as a table with 4 elements per row: title, link, status and level
	 */
	function get_toc() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_toc()', __FILE__,__LINE__);
		}
		$toc = array ();
		//echo "<pre>".print_r($this->items,true)."</pre>";
		foreach ($this->ordered_items as $item_id) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::get_toc(): getting info for item ' . $item_id, __FILE__,__LINE__);
			}
			//TODO change this link generation and use new function instead
			$toc[] = array (
				'id' => $item_id,
				'title' => $this->items[$item_id]->get_title(),
			//'link'=>get_addedresource_link_in_learnpath('document',$item_id,1),
				'status' => $this->items[$item_id]->get_status(),
				'level' => $this->items[$item_id]->get_level(),
				'type' => $this->items[$item_id]->get_type(),
				'description' => $this->items[$item_id]->get_description(),
				'path' => $this->items[$item_id]->get_path(),

			);
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_toc() - TOC array: ' . print_r($toc, true), __FILE__,__LINE__);
		}
		return $toc;
	}

	/**
	 * Gets the learning path type
	 * @param	boolean		Return the name? If false, return the ID. Default is false.
	 * @return	mixed		Type ID or name, depending on the parameter
	 */
	function get_type($get_name = false) {
		$res = false;
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_type()', __FILE__,__LINE__);
		}
		if (!empty ($this->type)) {
			if ($get_name) {
				//get it from the lp_type table in main db
			} else {
				$res = $this->type;
			}
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_type() - Returning ' . ($res == false ? 'false' : $res), __FILE__,__LINE__);
		}
		return $res;
	}

	/**
	 * Gets the learning path type as static method
	 * @param	boolean		Return the name? If false, return the ID. Default is false.
	 * @return	mixed		Type ID or name, depending on the parameter
	 */
	function get_type_static($lp_id = 0) {
		$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
		$sql = "SELECT lp_type FROM $tbl_lp WHERE id = '" . $lp_id . "'";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		if ($res === false) {
			return null;
		}
		if (Database :: num_rows($res) <= 0) {
			return null;
		}
		$row = Database :: fetch_array($res);
		return $row['lp_type'];
	}

	/**
	 * 递归: 获取有序的item ID 列表
	 * Gets a flat list of item IDs ordered for display (level by level ordered by order_display)
	 * This method can be used as abstract and is recursive
	 * @param	integer	Learnpath ID
	 * @param	integer	Parent ID of the items to look for
	 * @return	mixed	Ordered list of item IDs or false on error
	 */
	function get_flat_ordered_items_list($lp, $parent = 0) {
		//if($this->debug>0){error_log('New LP - In learnpath::get_flat_ordered_items_list('.$lp.','.$parent.')',0);}
		$list = array ();
		if (empty ($lp)) {
			return false;
		}
		$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
		$sql = "SELECT * FROM $tbl_lp_item WHERE lp_id ='". $lp."' AND parent_item_id ='". $parent."'";
		 $sql .=" AND cc='".api_get_course_code()."' ";
		$sql .=" ORDER BY display_order";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		while ($row = Database :: fetch_array($res)) {
			$sublist = learnpath :: get_flat_ordered_items_list($lp, $row['id']);
			$list[] = $row['id'];
			foreach ($sublist as $item) {
				$list[] = $item;
			}
		}
		return $list;
	}


	/**
	 * 调用 get_toc(), 返回HTML格式的LP显示内容
	 * Uses the table generated by get_toc() and returns an HTML-formatted string ready to display
	 * @return	string	HTML TOC ready to display
	 */
	function get_html_toc() {
		$charset = api_get_setting('platform_charset');
		$display_action_links_with_icons = true; //显示图标

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_html_toc()', __FILE__,__LINE__);
		}
		$list = $this->get_toc();
		//echo $this->current;
		//$parent = $this->items[$this->current]->get_parent();
		//if(empty($parent)){$parent = $this->ordered_items[$this->items[$this->current]->get_previous_index()];}
		$html="";

		// build, display
		if (api_is_allowed_to_edit()) {
			$gradebook = Security :: remove_XSS(getgpc('gradebook','G'));
			$html .= '<div class="actions_lp">';
			if ($display_action_links_with_icons) {
				$html .= "<a href='".api_get_path(WEB_PATH)."main.php?url=".htmlspecialchars(urlencode("main/".SCORM_PATH."lp_controller.php?" . api_get_cidreq() . "&gradebook=$gradebook&action=build&lp_id=" . $this->lp_id)) . "' target='_self'>" . Display :: return_icon('learnpath_build.gif', get_lang('BuildView'))  . get_lang('BuildView') . "</a>";
				$html .= "&nbsp;<a href='".api_get_path(WEB_PATH)."main.php?url=".htmlspecialchars(urlencode("main/".SCORM_PATH."lp_controller.php?" . api_get_cidreq() . "&amp;action=admin_view&amp;lp_id=" . $this->lp_id)) . "' target='_self'>" . Display :: return_icon('learnpath_organize.gif', get_lang('BasicOverviewView')) . get_lang('BasicOverviewView') . "</a>";
				$html .= '&nbsp;<span>' . Display :: return_icon('learnpath_view_na.gif', get_lang("DisplayView")) . ' <b>' . get_lang("DisplayView") . '</b></span>';
			} else {
				$html .= "<a href='lp_controller.php?" . api_get_cidreq() . "&amp;gradebook=$gradebook&amp;action=build&amp;lp_id=" . $this->lp_id . "' target='_self'>" . get_lang('Build') . "</a>";
				$html .= "&nbsp;<a href='lp_controller.php?" . api_get_cidreq() . "&amp;action=admin_view&amp;lp_id=" . $this->lp_id . "' target='_self'>" . get_lang('BasicOverview') . "</a>";
				$html .= '&nbsp;<span><b>' . get_lang('Display') . '</b></span>';
			}
			$html .= '</div>';
		}
		$html .="\n\n";
		$html .= "\n".'<div class="scorm_title"><div class="scorm_title_text">' . Security::remove_XSS($this->get_name()) . '</div></div>'."\n";;
		$html .= '<div id="inner_lp_toc" class="inner_lp_toc">' . "\n";
		require_once ('resourcelinker.inc.php');

		//temp variables
		$mycurrentitemid = $this->get_current_item_id();
		$color_counter = 0;
		$i = 0;
		foreach ($list as $item) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - learnpath::get_html_toc(): using item ' . $item['id'], __FILE__,__LINE__);
			}
			//TODO complete this
			$icon_name = array (
				'not attempted' => '../../themes/img/notattempted.gif',
				'incomplete' => '../../themes/img/incomplete.gif',
				'failed' => '../../themes/img/failed.gif',
				'completed' => '../../themes/img/completed.gif',
				'passed' => '../../themes/img/passed.gif',
				'succeeded' => '../../themes/img/succeeded.gif',
				'browsed' => '../../themes/img/completed.gif');

			$style = 'scorm_item';
			$scorm_color_background = 'scorm_item';
			$style_item = 'scorm_item';
			$current = false;

			if ($item['id'] == $this->current) {
				$style = 'scorm_item_highlight';
				$scorm_color_background = 'scorm_item_highlight';
			} else
			if ($color_counter % 2 == 0) {
				$scorm_color_background = 'scorm_item_1';
			} else {
				$scorm_color_background = 'scorm_item_2';
			}

			if ($scorm_color_background != '') {
				$html .= '<div id="toc_' . $item['id'] . '" class="' . $scorm_color_background . '">';
			}
			$html .="\n";
			//the anchor will let us center the TOC on the currently viewed item &^D
			if ($item['type'] != 'webcs_module' AND $item['type'] != 'webcs_chapter') {
				$html .= '<a name="atoc_' . $item['id'] . '" />';
				$html .= '<div class="' . $style_item . '" style="padding-left: ' . ($item['level'] * 1.5) . 'em; padding-right:' . ($item['level'] / 2) . 'em"  title="' . $item['description'] . '" >';
			} else {
				$html .= '<div class="' . $style_item . '" style="padding-left: ' . ($item['level'] * 2) . 'em; padding-right:' . ($item['level'] * 1.5) . 'em"  title="' . $item['description'] . '" >';
			}
			$html .="\n\n";
			$title = $item['title'];
			if (empty ($title)) {
				$title = rl_get_resource_name(api_get_course_id(), $this->get_id(), $item['id']);
			}
			//$title = api_htmlentities($title, ENT_QUOTES, $this->encoding);
			$title = Security::remove_XSS($title);
			if ($item['type'] != 'webcs_chapter' and $item['type'] != 'dir' AND $item['type'] != 'webcs_module') {
				//$html .= "<a href='lp_controller.php?".api_get_cidreq()."&action=content&lp_id=".$this->get_id()."&item_id=".$item['id']."' target='lp_content_frame_name'>".$title."</a>" ;
				$url = $this->get_link('http', $item['id']);
				//$html .= '<a href="'.$url.'" target="content_name" onclick="top.load_item('.$item['id'].',\''.$url.'\');">'.$title.'</a>' ;
				//$html .= '<a href="" onclick="top.load_item('.$item['id'].',\''.$url.'\');return false;">'.$title.'</a>' ;

				//<img align="absbottom" width="13" height="13" src="../img/lp_document.png">&nbsp;background:#aaa;
				$html .= '<a href="" onclick="ZLMS_xajax_handler.switch_item(' .$mycurrentitemid . ',' .$item['id'] . ');' .'return false;" >' . stripslashes($title) . '</a>'."\n";
			} elseif ($item['type'] == 'webcs_module' || $item['type'] == 'webcs_chapter') {
				$html .= "<img align='absbottom' width='13' height='13' src='../../themes/img/lp_ZLMS_module.png'>&nbsp;" . stripslashes($title);
			} elseif ($item['type'] == 'dir') {
				$html .= stripslashes($title);
			}

			$tbl_track_e_exercises = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
			$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
			$user_id = api_get_user_id();
			$course_id = api_get_course_id();
			$sql = "SELECT path  FROM $tbl_track_e_exercises, $tbl_lp_item
						WHERE path =   '" . $item['path'] . "' AND exe_user_id =  '$user_id' AND exe_cours_id = '$course_id' AND path = exe_exo_id AND status <> 'incomplete'";
			 $sql .=" AND cc='".api_get_course_code()."' ";
			$result = api_sql_query($sql, __FILE__, __LINE__);
			$count = Database :: num_rows($result);
			if ($item['type'] == 'quiz') {
				if ($item['status'] == 'completed') {
					$html .= "&nbsp;<img id='toc_img_" . $item['id'] . "' src='" . $icon_name[$item['status']] . "' alt='" . substr($item['status'], 0, 1) . "' width='12' height='12' />";
				}
			} else {
				if ($item['type'] != 'webcs_chapter' && $item['type'] != 'webcs_module' && $item['type'] != 'dir') {
					$html .= "&nbsp;<img id='toc_img_" . $item['id'] . "' src='" . $icon_name[$item['status']] . "' alt='" . substr($item['status'], 0, 1) . "' width='12' height='12' />";
				}
			}

			$html .= "</div>";

			if ($scorm_color_background != '') {
				$html .= '</div>';
			}

			$color_counter++;
		}
		$html .= "</div>\n\n";
		return $html;
	}


	/**
	 * Gets the learnpath maker name - generally the editor's name
	 * @return	string	Learnpath maker name
	 */
	function get_maker() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_maker()', __FILE__,__LINE__);
		}
		if (!empty ($this->maker)) {
			return $this->maker;
		} else {
			return '';
		}
	}

	/**
	 * Gets the user-friendly message stored in $this->message
	 * @return	string	Message
	 */
	function get_message() {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_message()', __FILE__,__LINE__);
		}
		return $this->message;
	}

	/**
	 * Gets the learnpath name/title
	 * @return	string	Learnpath name/title
	 */
	function get_name() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_name()', __FILE__,__LINE__);
		}
		if (!empty ($this->name)) {
			return $this->name;
		} else {
			return 'N/A';
		}
	}

	/**
	 * 从当前位置获取资源的链接
	 * Gets a link to the resource from the present location, depending on item ID.
	 * @param	string	Type of link expected
	 * @param	integer	Learnpath item ID
	 * @return	string	Link to the lp_item resource
	 */
	function get_link($type = 'http', $item_id = null) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_link(' . $type . ',' . $item_id . ')', __FILE__,__LINE__);
		}
		if (empty($item_id)) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::get_link() - no item id given in learnpath::get_link(), using current: ' . $this->get_current_item_id(), __FILE__,__LINE__);
			}
			$item_id = $this->get_current_item_id();
		}

		if (empty ($item_id)) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::get_link() - no current item id found in learnpath object', __FILE__,__LINE__);
			}
			//still empty, this means there was no item_id given and we are not in an object context or
			//the object property is empty, return empty link
			$item_id = $this->first();
			return '';
		}

		$file = '';
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_item_table = Database :: get_course_table(TABLE_LP_ITEM);
		$lp_item_view_table = Database :: get_course_table(TABLE_LP_ITEM_VIEW);
		$item_id = Database::escape_string($item_id);

		$sel = "SELECT l.lp_type as ltype, l.path as lpath, li.item_type as litype, li.path as lipath, li.parameters as liparams " .
			   "FROM $lp_table l, $lp_item_table li WHERE li.id = $item_id AND li.lp_id = l.id";
		 $sql .=" AND l.cc='".api_get_course_code()."' ";
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_link() - selecting item ' . $sel, __FILE__,__LINE__);
		}
		$res = api_sql_query($sel, __FILE__, __LINE__);
		if (Database :: num_rows($res) > 0) {
			$row = Database :: fetch_array($res);
			//var_dump($row);
			$lp_type = $row['ltype'];
			$lp_path = $row['lpath'];
			$lp_item_type = $row['litype'];
			$lp_item_path = $row['lipath'];
			$lp_item_params = $row['liparams'];
			if (empty ($lp_item_params) && strpos($lp_item_path, '?') !== false) {
				list ($lp_item_path, $lp_item_params) = explode('?', $lp_item_path);
			}
			//$lp_item_params = '?'.$lp_item_params;

			//add ? if none - left commented to give freedom to scorm implementation
			//if(substr($lp_item_params,0,1)!='?'){
			//	$lp_item_params = '?'.$lp_item_params;
			//}
			$sys_course_path = api_get_path(SYS_COURSE_PATH) . api_get_course_path();
			if ($type == 'http') {
				$course_path = api_get_path(WEB_COURSE_PATH) . api_get_course_path(); //web path
			} else {
				$course_path = $sys_course_path; //system path
			}
			//now go through the specific cases to get the end of the path
			switch ($lp_type) {
				
				case 2 :
					if ($this->debug > 2) {
						api_scorm_log('New LP - In learnpath::get_link() ' . __LINE__ . ' - Item type: ' . $lp_item_type, __FILE__,__LINE__);
					}

					if ($lp_item_type != 'dir') {
						//Quite complex here:
						//we want to make sure 'http://' (and similar) links can
						//be loaded as is (withouth the ZLMS path in front) but
						//some contents use this form: resource.htm?resource=http://blablabla
						//which means we have to find a protocol at the path's start, otherwise
						//it should not be considered as an external URL

						//if($this->prerequisites_match($item_id)){
						if (preg_match('#^[a-zA-Z]{2,5}://#', $lp_item_path) != 0) {
							if ($this->debug > 2) {
								api_scorm_log('New LP - In learnpath::get_link() ' . __LINE__ . ' - Found match for protocol in ' . $lp_item_path, __FILE__,__LINE__);
							}
							//distant url, return as is
							$file = $lp_item_path;
						} else {
							if ($this->debug > 2) {
								api_scorm_log('New LP - In learnpath::get_link() ' . __LINE__ . ' - No starting protocol in ' . $lp_item_path, __FILE__,__LINE__);
							}
							//prevent getting untranslatable urls
							$lp_item_path = preg_replace('/%2F/', '/', $lp_item_path);
							$lp_item_path = preg_replace('/%3A/', ':', $lp_item_path);
							//prepare the path
							$file = $course_path . '/scorm/' . $lp_path . '/' . $lp_item_path;
							//TODO fix this for urls with protocol header
							$file = str_replace('//', '/', $file);
							$file = str_replace(':/', '://', $file);
							if (substr($lp_path, -1) == '/') {
								$lp_path = substr($lp_path, 0, -1);
							}

							if (!is_file(realpath($sys_course_path . '/scorm/' . $lp_path . '/' . $lp_item_path))) {
								//if file not found
								$decoded = html_entity_decode($lp_item_path);
								list ($decoded) = explode('?', $decoded);
								if (!is_file(realpath($sys_course_path . '/scorm/' . $lp_path . '/' . $decoded))) {
									//require_once ('resourcelinker.inc.php');
									//$file = rl_get_resource_link_for_learnpath(api_get_course_id(), $this->get_id(), $item_id);
									$tmp_array = explode("/", $file);
									$document_name = $tmp_array[count($tmp_array) - 1];
									if (strpos($document_name, '_DELETED_')) {
										$file = 'blank.php?error=document_deleted';
									}

								} else {
									$file = $course_path . '/scorm/' . $lp_path . '/' . $decoded;
								}
							}
						}
						//}else{
						//prerequisites did not match
						//$file = 'blank.php';
						//}
						//We want to use parameters if they were defined in the imsmanifest
						if ($file != 'blank.php') {
							$file .= (strstr($file, '?') === false ? '?' : '') . $lp_item_params;
						}
					} else {
						$file = 'lp_content.php?type=dir';
					}
					break;
				
				default :
					break;
			}
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - In learnpath::get_link() - returning "' . $file . '" from get_link', __FILE__,__LINE__);
		}
		return $file;
	}

	/**
	 * 返回最后使用的lp_view.id,或新建一个
	 * Gets the latest usable view or generate a new one
	 * @param	integer	Optional attempt number. If none given, takes the highest from the lp_view table
	 * @return	integer	DB lp_view id
	 */
	function get_view($attempt_num = 0) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_view()', __FILE__,__LINE__);
		}
		$search = '';
		//use $attempt_num to enable multi-views management (disabled so far)
		if ($attempt_num != 0 AND intval(strval($attempt_num)) == $attempt_num) {
			$search = 'AND view_count = ' . $attempt_num;
		}
		//when missing $attempt_num, search for a unique lp_view record for this lp and user
		$lp_view_table = Database :: get_course_table(TABLE_LP_VIEW);
		$sql = "SELECT id, view_count FROM $lp_view_table " .
		"WHERE lp_id = " . $this->get_id() . " " .
		"AND user_id = " . $this->get_user_id() . " " .$search ;
		 $sql .=" AND cc='".api_get_course_code()."' ";
		$sql .= " ORDER BY view_count DESC";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		if (Database :: num_rows($res) > 0) {
			$row = Database :: fetch_array($res);
			$this->lp_view_id = $row['id'];
		} else {
			//no database record, create one
			/*$sql = "INSERT INTO $lp_view_table(lp_id,user_id,view_count)" .
			"VALUES (" . $this->get_id() . "," . $this->get_user_id() . ",1)";*/
			//V1.4
			$sql=array('lp_id'=>$this->get_id(),'user_id'=>$this->get_user_id(),'view_count'=>1);
			 $sql_data['cc']=$this->cc;
			$sql=Database::sql_insert($lp_view_table,$sql_data);
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$id = Database :: get_last_insert_id();
			$this->lp_view_id = $id;
		}
		return $this->lp_view_id;
	}

	/**
	 * Gets the current view id
	 * @return	integer	View ID (from lp_view)
	 */
	function get_view_id() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_view_id()', __FILE__,__LINE__);
		}
		if (!empty ($this->lp_view_id)) {
			return $this->lp_view_id;
		} else {
			return 0;
		}
	}

	/**
	 * Gets the update queue
	 * @return	array	Array containing IDs of items to be updated by JavaScript
	 */
	function get_update_queue() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_update_queue()', __FILE__,__LINE__);
		}
		return $this->update_queue;
	}

	/**
	 * Gets the user ID
	 * @return	integer	User ID
	 */
	function get_user_id() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::get_user_id()', __FILE__,__LINE__);
		}
		if (!empty ($this->user_id)) {
			return $this->user_id;
		} else {
			return false;
		}
	}

	/**
	 * Checks if any of the items has an audio element attached
	 * @return  bool    True or false
	 */
	function has_audio() {
		if ($this->debug > 1) {
			api_scorm_log('New LP - In learnpath::has_audio()', __FILE__,__LINE__);
		}
		$has = false;
		foreach ($this->items as $i => $item) {
			if (!empty ($this->items[$i]->audio)) {
				$has = true;
				break;
			}
		}
		return $has;
	}

	function is_single_sco(){
		if ($this->debug > 1) {
			api_scorm_log('New LP - In learnpath::is_single_sco()', __FILE__,__LINE__);
		}
		return (is_array($this->items) && count($this->items)==1);
	}

	/**
	 * Logs a message into a file
	 * @param	string 	Message to log
	 * @return	boolean	True on success, false on error or if msg empty
	 */
	function log($msg) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::log()', __FILE__,__LINE__);
		}
		//TODO
		$this->error .= $msg . "\n";
		return true;
	}

	/**
	 * Updates learnpath attributes to point to the next element
	 * The last part is similar to set_current_item but processing the other way around
	 */
	function next() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::next()',  __FILE__,__LINE__);
		}
		$this->last = $this->get_current_item_id();
		$this->items[$this->last]->save(false, $this->prerequisites_match($this->last));
		$this->autocomplete_parents($this->last);
		$new_index = $this->get_next_index();
		if ($this->debug > 2) {
			api_scorm_log('New LP - New index: ' . $new_index,  __FILE__,__LINE__);
		}
		$this->index = $new_index;
		if ($this->debug > 2) {
			api_scorm_log('New LP - Now having orderedlist[' . $new_index . '] = ' . $this->ordered_items[$new_index],  __FILE__,__LINE__);
		}
		$this->current = $this->ordered_items[$new_index];
		if ($this->debug > 2) {
			api_scorm_log('New LP - new item id is ' . $this->current . '-' . $this->get_current_item_id(),  __FILE__,__LINE__);
		}
	}

	/**
	 * Open a resource = initialise all local variables relative to this resource. Depending on the child
	 * class, this might be redefined to allow several behaviours depending on the document type.
	 * @param integer Resource ID
	 * @return boolean True on success, false otherwise
	 */
	function open($id) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::open()', __FILE__,__LINE__);
		}
		$this->index = 0; //or = the last item seen (see $this->last)
	}

	/**
	 * 检查所有的前置都满足. 成功返回TRUE或空字符串(没有前置时);失败返回FALSE或前提错误字符串
	 * Check that all prerequisites are fulfilled. Returns true and an empty string on succes, returns false
	 * and the prerequisite string on error.
	 * This function is based on the rules for aicc_script language as described in the SCORM 1.2 CAM documentation page 108.
	 * @param	integer	Optional item ID. If none given, uses the current open item.
	 * @return	boolean	True if prerequisites are matched, false otherwise
	 * @return	string	Empty string if true returned, prerequisites string otherwise.
	 */
	function prerequisites_match($item = null) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::prerequisites_match()', __FILE__,__LINE__);
		}
		if (empty ($item)) {
			$item = $this->current;
		}
		if (is_object($this->items[$item])) {
			$prereq_string = $this->items[$item]->get_prereq_string();
			if (empty ($prereq_string)) {
				return true;
			}
			//clean spaces
			$prereq_string = str_replace(' ', '', $prereq_string);
			if ($this->debug > 0) {
				api_scorm_log('Found prereq_string: ' . $prereq_string,__FILE__,__LINE__);
			}

			//now send to the parse_prereq() function that will check this component's prerequisites
			$result = $this->items[$item]->parse_prereq($prereq_string, $this->items, $this->refs_list, $this->get_user_id());

			if ($result === false) {
				$this->set_error_msg($this->items[$item]->prereq_alert);
			}
		} else {
			$result = true;
			if ($this->debug > 1) {
				api_scorm_log('New LP - $this->items[' . $item . '] was not an object', __FILE__,__LINE__);
			}
		}

		if ($this->debug > 1) {
			api_scorm_log('New LP - End of prerequisites_match(). Error message is now ' . $this->error, __FILE__,__LINE__);
		}
		return $result;
	}


	/**
	 * 更新LP属性使其指向前一元素
	 * Updates learnpath attributes to point to the previous element
	 * The last part is similar to set_current_item but processing the other way around
	 */
	function previous() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::previous()', __FILE__,__LINE__);
		}
		$this->last = $this->get_current_item_id();
		$this->items[$this->last]->save(false, $this->prerequisites_match($this->last));
		$this->autocomplete_parents($this->last);
		$new_index = $this->get_previous_index();
		$this->index = $new_index;
		$this->current = $this->ordered_items[$new_index];
	}


	/**
	 * 发布LP:显示或隐藏
	 * Publishes a learnpath. This basically means show or hide the learnpath
	 * to normal users.
	 * Can be used as abstract
	 * @param	integer	Learnpath ID
	 * @param	string	New visibility
	 */

	function toggle_visibility($lp_id, $set_visibility = 1) {
		//if($this->debug>0){api_scorm_log('New LP - In learnpath::toggle_visibility()',0);}
		$action = 'visible';
		if ($set_visibility != 1) {
			$action = 'invisible';
		}
		$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
		$sql="UPDATE $table_courseware SET visibility='".$set_visibility."' WHERE cc='".api_get_course_code()."' AND  cw_type='scorm' AND attribute=".Database::escape($lp_id);
		api_sql_query($sql, __FILE__, __LINE__);
		
		return api_item_property_update(api_get_course_info(), TOOL_LEARNPATH, $lp_id, $action, api_get_user_id());
	}

	/**
	 * 发布LP
	 * Publishes a learnpath. This basically means show or hide the learnpath
	 * on the course homepage
	 * Can be used as abstract
	 * @param	integer	Learnpath ID
	 * @param	string	New visibility
	 */
	function toggle_publish($lp_id, $set_visibility = 'v') {
		global $_course;
		if($this->debug>0){api_scorm_log('New LP - In learnpath::toggle_publish()',__FILE__,__LINE__);}
		$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
		$sql = "SELECT * FROM $tbl_lp where id=$lp_id";
		$result = api_sql_query($sql, __FILE__, __LINE__);
		$row = Database :: fetch_array($result);
		$name = domesticate($row['name']);
		if ($set_visibility == 'i') {
			$s = $name . " " . get_lang('_no_published');
			$dialogBox = $s;
			$v = 0;
		}
		if ($set_visibility == 'v') {
			$s = $name . " " . get_lang('_published');
			$dialogBox = $s;
			$v = 1;
		}

		$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
		$sql="UPDATE $table_courseware SET visibility='".$v."' WHERE cc='".api_get_course_code()."' AND  cw_type='scorm' AND attribute=".Database::escape($lp_id);
		api_sql_query($sql, __FILE__, __LINE__);
		
		if ($set_visibility == 'i') {
			api_item_property_update($_course,TOOL_LEARNPATH,$lp_id,"invisible");
		}
		elseif ($set_visibility == 'v') {
			api_item_property_update($_course,TOOL_LEARNPATH,$lp_id,"visible",api_get_user_id(),0,NULL);
		} else {
			//parameter and database incompatible, do nothing
		}
	}

	/**
	 * 重新启动整个LP. 返回第一个元素的URL.
	 * Restart the whole learnpath. Return the URL of the first element.
	 * Make sure the results are saved with anoter method. This method should probably be
	 * redefined in children classes.
	 * To use a similar method  statically, use the create_new_attempt() method
	 * @return string URL to load in the viewer
	 */
	function restart() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::restart()', __FILE__,__LINE__);
		}
		//TODO
		//call autosave method to save the current progress
		//$this->index = 0;
		/*$lp_view_table = Database :: get_course_table(TABLE_LP_VIEW);
		$sql = "INSERT INTO $lp_view_table (lp_id, user_id, view_count) " .
		"VALUES (" . $this->lp_id . "," . $this->get_user_id() . "," . ($this->attempt + 1) . ")";*/
		$sql_data=array('lp_id'=>$this->lp_id, 'user_id'=>$this->get_user_id(), 'view_count'=>($this->attempt + 1));
		 $sql_data['cc']=api_get_course_code();
		$sql=Database::sql_insert($lp_view_table,$sql_data);
		if ($this->debug > 2) {
			api_scorm_log('New LP - Inserting new lp_view for restart: ' . $sql, __FILE__,__LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		if ($view_id = Database :: get_last_insert_id($res)) {
			$this->lp_view_id = $view_id;
			$this->attempt = $this->attempt + 1;
		} else {
			$this->error = 'Could not insert into item_view table...';
			return false;
		}
		$this->autocomplete_parents($this->current);
		foreach ($this->items as $index => $dummy) {
			$this->items[$index]->restart();
			$this->items[$index]->set_lp_view($this->lp_view_id);
		}
		$this->first();
		return true;
	}


	/**
	 * 保存当前item
	 * Saves the current item
	 * @return	boolean
	 */
	function save_current() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::save_current()', __FILE__,__LINE__);
		}
		//TODO do a better check on the index pointing to the right item (it is supposed to be working
		// on $ordered_items[] but not sure it's always safe to use with $items[])
		if ($this->debug > 2) {
			api_scorm_log('New LP - save_current() saving item ' . $this->current, __FILE__,__LINE__);
		}
		if ($this->debug > 2) {
			api_scorm_log('' . print_r($this->items, true), __FILE__,__LINE__);
		}
		if (is_object($this->items[$this->current])) {
			//$res = $this->items[$this->current]->save(false);
			$res = $this->items[$this->current]->save(false, $this->prerequisites_match($this->current));
			$this->autocomplete_parents($this->current);
			$status = $this->items[$this->current]->get_status();
			$this->append_message('new_item_status: ' . $status);
			$this->update_queue[$this->current] = $status;
			return $res;
		}
		return false;
	}


	/**
	 * 保存给定item
	 * Saves the given item
	 * @param	integer	Item ID. Optional (will take from $_REQUEST if null)
	 * @param	boolean	Save from url params (true) or from current attributes (false). Optional. Defaults to true
	 * @return	boolean
	 */
	function save_item($item_id = null, $from_outside = true) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::save_item(' . $item_id . ',' . $from_outside . ')', __FILE__,__LINE__);
		}
		//TODO do a better check on the index pointing to the right item (it is supposed to be working
		// on $ordered_items[] but not sure it's always safe to use with $items[])
		if (empty ($item_id)) {
			$item_id = $this->escape_string(intval($_REQUEST['id']));
		}
		if (empty ($item_id)) {
			$item_id = $this->get_current_item_id();
		}
		if ($this->debug > 2) {
			api_scorm_log('New LP - save_current() saving item ' . $item_id, __FILE__,__LINE__);
		}
		if (is_object($this->items[$item_id])) {
			$res = $this->items[$item_id]->save($from_outside, $this->prerequisites_match($item_id));
			//$res = $this->items[$item_id]->save($from_outside);
			$this->autocomplete_parents($item_id);
			$status = $this->items[$item_id]->get_status();
			$this->append_message('new_item_status: ' . $status);
			$this->update_queue[$item_id] = $status;
			return $res;
		}
		return false;
	}


	/**
	 * 保存上次查看item ID
	 * Saves the last item seen's ID only in case
	 */
	function save_last()
	{
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::save_last()', __FILE__, __LINE__);
		}
		$table = Database :: get_course_table(TABLE_LP_VIEW);
		if (isset ($this->current)) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - Saving current item (' . $this->current . ') for later review', __FILE__, __LINE__);
			}
			$sql = "UPDATE $table SET last_item = " . Database::escape($this->get_current_item_id()). " " .
					"WHERE lp_id = " . $this->get_id() . " AND user_id = " . $this->get_user_id();
			 $sql .=" AND cc='".api_get_course_code()."' ";
			if ($this->debug > 2) {
				api_scorm_log('New LP - Saving last item seen : ' . $sql, __FILE__, __LINE__);
			}
			$res = api_sql_query($sql, __FILE__, __LINE__);
		}

		//save progress
		list ($progress, $text) = $this->get_progress();
		if ($progress >= 0 AND $progress <= 100) {
			$progress = (int) $progress;
			$sql = "UPDATE $table SET progress = $progress " .
					"WHERE lp_id = " . $this->get_id() . " AND  user_id = " . $this->get_user_id();
			 $sql .=" AND cc='".api_get_course_code()."' ";
			$res = api_sql_query($sql, __FILE__, __LINE__); //ignore errors as some tables might not have the progress field just yet
			$this->progress_db = $progress;
		}
	}


	/**
	 * 检查当前 item ID 是否可
	 * Sets the current item ID (checks if valid and authorized first)
	 * @param	integer	New item ID. If not given or not authorized, defaults to current
	 */
	function set_current_item($item_id = null) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_current_item(' . $item_id . ')', __FILE__,__LINE__);
		}
		if (empty ($item_id)) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - No new current item given, ignore...',  __FILE__,__LINE__);
			}
			//do nothing
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - New current item given is ' . $item_id . '...',  __FILE__,__LINE__);
			}
			if (is_numeric($item_id)) {
				$item_id = $this->escape_string($item_id);
				//TODO check in database here
				$this->last = $this->current;
				$this->current = $item_id;
				//TODO update $this->index as well
				foreach ($this->ordered_items as $index => $item) {
					if ($item == $this->current) {
						$this->index = $index;
						break;
					}
				}
				if ($this->debug > 2) {
					api_scorm_log('New LP - set_current_item(' . $item_id . ') done. Index is now : ' . $this->index,  __FILE__,__LINE__);
				}
			} else {
				api_scorm_log('New LP - set_current_item(' . $item_id . ') failed. Not a numeric value: ', __FILE__,__LINE__);
			}
		}
	}


	/**
	 * liyu: 20091103 打开窗口
	 * @param $target
	 * @return unknown_type
	 * @since V1.4.0
	 */
	function set_open_target($target='_self'){
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_oepn_target()', __FILE__,__LINE__);
		}
		if (empty ($target))		return false;

		$this->target = $this->escape_string($target);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET target = '" . $this->target . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log(' lp updated with new open target : ' . $this->target, __FILE__,__LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);

		return $res;
	}

	/**
	 * Sets the encoding
	 * @param	string	New encoding
	 */
	function set_encoding($enc = 'ISO-8859-15') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_encoding()', __FILE__,__LINE__);
		}
		$enc = strtoupper($enc);
		$encodings = array ( 'UTF-8', 'GB2312', 'ISO-8859-1', 'ISO-8859-15', 'cp1251', 'cp1252', 'KOI8-R',
			'BIG5', 'Shift_JIS', 'EUC-JP', '' );
		if (in_array($enc, $encodings)) {
			$lp = $this->get_id();
			if ($lp != 0) {
				$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
				$sql = "UPDATE $tbl_lp SET default_encoding = '$enc' WHERE id = " . $lp;
				$res = api_sql_query($sql, __FILE__, __LINE__);
				return $res;
			}
		}
		return false;
	}


	/**
	 * Sets the JS lib setting in the database directly.
	 * This is the JavaScript library file this lp needs to load on startup
	 * @param	string	Proximity setting
	 */
	function set_jslib($lib = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_jslib()', __FILE__, __LINE__);
		}
		$lp = $this->get_id();
		if ($lp != 0) {
			$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
			$sql = "UPDATE $tbl_lp SET js_lib = '$lib' WHERE id = " . $lp;
			$res = api_sql_query($sql, __FILE__, __LINE__);
			return $res;
		} else {
			return false;
		}
	}


	/**
	 * Sets the name of the LP maker (publisher) (and save)
	 * @param	string	Optional string giving the new content_maker of this learnpath
	 */
	function set_maker($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_maker()', __FILE__, __LINE__);
		}
		if (empty ($name))		return false;

		$this->maker = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET content_maker = '" . $this->maker . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new content_maker : ' . $this->maker, __FILE__, __LINE__);
		}
		//$res = Database::query($sql);
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}


	/**
	 * Sets the name of the current learnpath (and save)
	 * @param	string	Optional string giving the new name of this learnpath
	 */
	function set_name($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_name()', __FILE__, __LINE__);
		}
		if (empty ($name)) return false;

		$this->name = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET name = '" . $this->name . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new name : ' . $this->name, __FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}

	function set_theme($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_theme()', __FILE__, __LINE__);
		}
		$this->theme = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET theme = '" . $this->theme . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new theme : ' . $this->theme, __FILE__, __LINE__);
		}
		//$res = Database::query($sql);
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}


	/**
	 * 设置预览图片
	 * Sets the image of an LP (and save)
	 * @param	string	Optional string giving the new image of this learnpath
	 * @return bool returns true if theme name is not empty
	 */
	function set_preview_image($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_preview_image()', __FILE__, __LINE__);
		}
		$this->preview_image = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET preview_image = '" . $this->preview_image . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new preview image : ' . $this->preview_image, __FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}


	/**
	 * Sets the author of a LP (and save)
	 * @param	string	Optional string giving the new author of this learnpath
	 * @return bool returns true if author's name is not empty
	 */
	function set_author($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_author()', __FILE__, __LINE__);
		}
		$this->author = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET author = '" . $this->author . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new preview author : ' . $this->author,__FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}


	/**
	 * 设置location(local/remote)
	 * Sets the location/proximity of the LP (local/remote) (and save)
	 * @param	string	Optional string giving the new location of this learnpath
	 */
	function set_proximity($name = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_proximity()', __FILE__, __LINE__);
		}
		if (empty ($name)) return false;

		$this->proximity = $this->escape_string($name);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET content_local = '" . $this->proximity . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new proximity : ' . $this->proximity, __FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}

	/**
	 * V2.1
	 * @param unknown_type $time
	 */
	function set_learning_time($time=0){
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_learning_time()', __FILE__, __LINE__);
		}
		$this->learning_time = $this->escape_string($time);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET learning_time = '" . $this->learning_time . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new learning_time : ' . $this->learning_time,__FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}

	function set_learning_order($order=0){
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_learning_order()', __FILE__, __LINE__);
		}
		$this->learning_order = $this->escape_string($order);
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$lp_id = $this->get_id();
		$sql = "UPDATE $lp_table SET learning_order = '" . $this->learning_order . "' WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log('New LP - lp updated with new learning_order : ' . $this->learning_order,__FILE__, __LINE__);
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}

	/**
	 * Sets the previous item ID to a given ID. Generally, this should be set to the previous 'current' item
	 * @param	integer	DB ID of the item
	 */
	function set_previous_item($id) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_previous_item()', __FILE__, __LINE__);
		}
		$this->last = $id;
	}


	/**
	 * Sets the object's error message
	 * @param	string	Error message. If empty, reinits the error string
	 * @return 	void
	 */
	function set_error_msg($error = '') {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::set_error_msg()', __FILE__, __LINE__);
		}
		if (empty ($error)) {
			$this->error = '';
		} else {
			$this->error .= $error;
		}
	}


	/**
	 * 若不是sco,则启动当前item
	 * Launches the current item if not 'sco' (starts timer and make sure there is a record ready in the DB)
	 * @param unknown_type $allow_new_attempt
	 */
	function start_current_item($allow_new_attempt = false) {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::start_current_item()', __FILE__,__LINE__);
		}
		if ($this->current != 0 AND is_object($this->items[$this->current])) {
			$type = $this->get_type();
			$item_type = $this->items[$this->current]->get_type();
			if (($type == 2 && $item_type != 'sco') OR ($type == 3 && $item_type != 'au')
			OR ($type == 1 && $item_type != TOOL_QUIZ && $item_type != TOOL_HOTPOTATOES)) {
				$this->items[$this->current]->open($allow_new_attempt);
				$this->autocomplete_parents($this->current);
				$prereq_check = $this->prerequisites_match($this->current);
				$this->items[$this->current]->save(false, $prereq_check);
				//$this->update_queue[$this->last] = $this->items[$this->last]->get_status();
			} else {
				//if sco, then it is supposed to have been updated by some other call
			}
			//V2.1 只有一个SCO时不重启
			if ($item_type == 'sco' && count($this->items)>1) {
				$this->items[$this->current]->restart();
			}
		}
		if ($this->debug > 0) {
			api_scorm_log('New LP - End of learnpath::start_current_item()', __FILE__,__LINE__);
		}
		return true;
	}


	/**
	 * 停止处理前一item
	 * Stops the processing and counters for the old item (as held in $this->last)
	 * @param
	 */
	function stop_previous_item() {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::stop_previous_item()', __FILE__,__LINE__);
		}

		if ($this->last != 0 AND $this->last != $this->current AND is_object($this->items[$this->last])) {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::stop_previous_item() - ' . $this->last . ' is object', __FILE__,__LINE__);
			}
			switch ($this->get_type()) {
				case '3' :
					if ($this->items[$this->last]->get_type() != 'au') {
						if ($this->debug > 2) {
							api_scorm_log('New LP - In learnpath::stop_previous_item() - ' . $this->last . ' in lp_type 3 is <> au',  __FILE__,__LINE__);
						}
						$this->items[$this->last]->close();
						//$this->autocomplete_parents($this->last);
						//$this->update_queue[$this->last] = $this->items[$this->last]->get_status();
					} else {
						if ($this->debug > 2) {
							api_scorm_log('New LP - In learnpath::stop_previous_item() - Item is an AU, saving is managed by AICC signals',  __FILE__,__LINE__);
						}
					}
				case '2' :
					if ($this->items[$this->last]->get_type() != 'sco') {
						if ($this->debug > 2) {
							api_scorm_log('New LP - In learnpath::stop_previous_item() - ' . $this->last . ' in lp_type 2 is <> sco', __FILE__,__LINE__);
						}
						$this->items[$this->last]->close();
						//$this->autocomplete_parents($this->last);
						//$this->update_queue[$this->last] = $this->items[$this->last]->get_status();
					} else {
						if ($this->debug > 2) {
							api_scorm_log('New LP - In learnpath::stop_previous_item() - Item is a SCO, saving is managed by SCO signals',  __FILE__,__LINE__);
						}
					}
					break;
				case '1' :
				default :
					if ($this->debug > 2) {
						api_scorm_log('New LP - In learnpath::stop_previous_item() - ' . $this->last . ' in lp_type 1 is asset',  __FILE__,__LINE__);
					}
					$this->items[$this->last]->close();
					break;
			}
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - In learnpath::stop_previous_item() - No previous element found, ignoring...',  __FILE__,__LINE__);
			}
			return false;
		}
		return true;
	}


	



	/**
	 * 更新显示顺序
	 * Updates the order of learning paths (goes through all of them by order and fills the gaps)
	 * @return	bool	True on success, false on failure
	 */
	function update_display_order() {
		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$sql = "SELECT * FROM $lp_table  WHERE cc='".api_get_course_code()."'  ORDER BY display_order";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		if ($res === false) return false;
		$lps = array ();
		$lp_order = array ();
		$num = Database :: num_rows($res);
		if ($num > 0) {
			$i = 1;
			while ($row = Database :: fetch_array($res,'ASSOC')) {
				if ($row['display_order'] != $i) { //if we find a gap in the order, we need to fix it
					$need_fix = true;
					$sql_u = "UPDATE $lp_table SET display_order = $i WHERE id = " . $row['id'];
					$res_u = api_sql_query($sql_u, __FILE__, __LINE__);
				}
				$i++;
			}
		}
		return true;
	}


	/**
	 *
	 * Updates the "prevent_reinit" value that enables control on reinitialising items on second view
	 * @return	boolean	True if prevent_reinit has been set to 'on', false otherwise (or 1 or 0 in this case)
	 */
	function update_reinit() {

		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::update_reinit()',  __FILE__,__LINE__);
		}

		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$sql = "SELECT * FROM $lp_table WHERE id = " . $this->get_id();
		$res = api_sql_query($sql, __FILE__, __LINE__);

		if (Database :: num_rows($res) > 0) {
			$row = Database :: fetch_array($res);
			$force = $row['prevent_reinit'];
			if ($force == 1) {
				$force = 0;
			}
			elseif ($force == 0) {
				$force = 1;
			}

			$sql = "UPDATE $lp_table SET prevent_reinit = $force WHERE id = " . $this->get_id();
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$this->prevent_reinit = $force;
			return $force;
		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - Problem in update_reinit() - could not find LP ' . $this->get_id() . ' in DB',  __FILE__,__LINE__);
			}
		}

		return -1;
	}


	/**
	 * 更新"scorm_debug"值,显示或隐藏debug窗口
	 * Updates the "scorm_debug" value that shows or hide the debug window
	 * @return	boolean	True if scorm_debug has been set to 'on', false otherwise (or 1 or 0 in this case)
	 */
	function update_scorm_debug() {
		if ($this->debug > 0) {
			api_scorm_log('New LP - In learnpath::update_scorm_debug()', __FILE__,__LINE__);
		}

		$lp_table = Database :: get_course_table(TABLE_LP_MAIN);
		$sql = "SELECT * FROM $lp_table WHERE id = " . $this->get_id();
		$res = api_sql_query($sql, __FILE__, __LINE__);
		if (Database :: num_rows($res) > 0) {
			$row = Database :: fetch_array($res);
			$force = $row['debug'];
			if ($force == 1) {
				$force = 0;
			}
			elseif ($force == 0) {
				$force = 1;
			}

			$sql = "UPDATE $lp_table SET debug = $force WHERE id = " . $this->get_id();
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$this->scorm_debug = $force;
			return $force;

		} else {
			if ($this->debug > 2) {
				api_scorm_log('New LP - Problem in update_scorm_debug() - could not find LP ' . $this->get_id() . ' in DB',  __FILE__,__LINE__);
			}
		}

		return -1;
	}


	/**
	 * 调用 sort_tree_array()和create_tree_array()
	 * Function that makes a call to the function sort_tree_array and create_tree_array		*
	 * @author Kevin Van Den Haute		*
	 * @param unknown_type $array
	 */
	function tree_array($array) {
		if ($this->debug > 1) {
			api_scorm_log('New LP - In learnpath::tree_array()',  __FILE__,__LINE__);
		}
		$array = $this->sort_tree_array($array);
		$this->create_tree_array($array);
	}


	/**
	 *
	 * Creates an array with the elements of the learning path tree in it
	 *
	 * @author
	 *
	 * @param array $array
	 * @param int $parent
	 * @param int $depth
	 * @param array $tmp
	 */
	function create_tree_array($array, $parent = 0, $depth = -1, $tmp = array ()) {
		if ($this->debug > 1) {
			api_scorm_log('New LP - In learnpath::create_tree_array())',  __FILE__,__LINE__);
		}
		if (is_array($array)) {
			for ($i = 0; $i < count($array); $i++) {
				if ($array[$i]['parent_item_id'] == $parent) {
					if (!in_array($array[$i]['parent_item_id'], $tmp)) {
						$tmp[] = $array[$i]['parent_item_id'];
						$depth++;
					}
					$preq = (empty ($array[$i]['prerequisite']) ? '' : $array[$i]['prerequisite']);
					$this->arrMenu[] = array (
						'id' => $array[$i]['id'],
						'item_type' => $array[$i]['item_type'],
						'title' => $array[$i]['title'],
						'path' => $array[$i]['path'],
						'description' => $array[$i]['description'],
						'parent_item_id' => $array[$i]['parent_item_id'],
						'previous_item_id' => $array[$i]['previous_item_id'],
						'next_item_id' => $array[$i]['next_item_id'],
						'min_score' => $array[$i]['min_score'],
						'max_score' => $array[$i]['max_score'],
						'mastery_score' => $array[$i]['mastery_score'],
						'display_order' => $array[$i]['display_order'],
						'prerequisite' => $preq,
						'depth' => $depth,
						'audio' => $array[$i]['audio'],
						'audio_total_play_time' => $array[$i]['audio_total_play_time']
					);

					$this->create_tree_array($array, $array[$i]['id'], $depth, $tmp);
				}
			}
		}
	}


	/**
	 *
	 * Sorts a multi dimensional array by parent id and display order
	 * @author
	 *
	 * @param array $array (array with al the learning path items in it)
	 *
	 * @return array
	 */
	function sort_tree_array($array) {
		foreach ($array as $key => $row) {
			$parent[$key] = $row['parent_item_id'];
			$position[$key] = $row['display_order'];
		}

		if (count($array) > 0)
		array_multisort($parent, SORT_ASC, $position, SORT_ASC, $array);

		return $array;
	}


	/**
	 * This functions builds the LP tree based on data from the database.
	 *
	 * @return string
	 * @uses dtree.js :: necessary javascript for building this tree
	 */
	function build_tree() {
		$platform_charset = api_get_system_encoding();
		$return = "<script type=\"text/javascript\">\n";
		$return .= "\tm = new dTree('m');\n\n";
		$return .= "\tm.config.folderLinks		= true;\n";
		$return .= "\tm.config.useCookies		= true;\n";
		$return .= "\tm.config.useIcons			= true;\n";
		$return .= "\tm.config.useLines			= true;\n";
		$return .= "\tm.config.useSelection		= true;\n";
		$return .= "\tm.config.useStatustext	= false;\n\n";

		$menu = 0;
		$parent = '';
		$return .= "\tm.add(" . $menu . ", -1, '" . addslashes(Security::remove_XSS(($this->name))) . "');\n";
		$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);

		$sql = " SELECT * FROM " . $tbl_lp_item . " WHERE lp_id = " . Database :: escape_string($this->lp_id);
		 $sql .=" AND cc='".api_get_course_code()."' ";
		//echo $sql;
		$result = api_sql_query($sql, __FILE__, __LINE__);
		$arrLP = array ();

		while ($row = Database :: fetch_array($result)) {
			//liyu: 20091025
			//$row['title'] = Security :: remove_XSS(api_convert_encoding($row['title'], $platform_charset, $this->encoding));

			$row['description'] = Security :: remove_XSS(api_convert_encoding($row['description'], $platform_charset, $this->encoding));
			//$row['title'] = Security :: remove_XSS($row['title']);
			$arrLP[] = array (
				'id' => $row['id'],
				'item_type' => $row['item_type'],
				'title' => $row['title'],
				'path' => $row['path'],
				'description' => $row['description'],
				'parent_item_id' => $row['parent_item_id'],
				'previous_item_id' => $row['previous_item_id'],
				'next_item_id' => $row['next_item_id'],
				'max_score' => $row['max_score'],
				'min_score' => $row['min_score'],
				'mastery_score' => $row['mastery_score'],
				'display_order' => $row['display_order']
			);
		}

		$this->tree_array($arrLP);
		$arrLP = $this->arrMenu;
		unset ($this->arrMenu);
		$title = '';
		for ($i = 0; $i < count($arrLP); $i++) {
			$title = addslashes($arrLP[$i]['title']);
			$menu_page = api_get_self() . '?cidReq=' . Security :: remove_XSS( getgpc('cidReq','G')) . '&amp;action=view_item&amp;id=' . $arrLP[$i]['id'] . '&amp;lp_id=' . $_SESSION['oLP']->lp_id;
			$icon_name = str_replace(' ', '', $arrLP[$i]['item_type']);
			if (file_exists(api_get_path(SYS_IMG_PATH)."lp_" . $icon_name . ".png")) {
				$return .= "\tm.add(" . $arrLP[$i]['id'] . ", " . $arrLP[$i]['parent_item_id'] . ", '" . $title . "', '" . $menu_page . "', '', '', '../../themes/img/lp_" . $icon_name . ".png', '../../themes/img/lp_" . $icon_name . ".png');\n";
			} else if (file_exists(api_get_path(SYS_IMG_PATH)."lp_" . $icon_name . ".gif")) {
				$return .= "\tm.add(" . $arrLP[$i]['id'] . ", " . $arrLP[$i]['parent_item_id'] . ", '" . $title . "', '" . $menu_page . "', '', '', '../../themes/img/lp_" . $icon_name . ".gif', '../../themes/img/lp_" . $icon_name . ".gif');\n";
			} else {
				$return .= "\tm.add(" . $arrLP[$i]['id'] . ", " . $arrLP[$i]['parent_item_id'] . ", '" . $title . "', '" . $menu_page . "', '', '', '../../themes/img/folder_document.gif', '../../themes/img/folder_document.gif');\n";
			}
			if ($menu < $arrLP[$i]['id'])
			$menu = $arrLP[$i]['id'];
		}

		$return .= "\n\tdocument.write(m);\n";
		$return .= "\t if(!m.selectedNode) m.s(1);";
		$return .= "</script>\n";

		return $return;
	}



	/**
	 * 返回文件后缀
	 * Returns the extension of a document
	 *
	 * @param unknown_type $filename
	 * @return unknown
	 */
	function get_extension($filename) {
		$explode = explode('.', $filename);
		return $explode[count($explode) - 1];
	}



	function create_path($path) {
		$path_bits = split('/', dirname($path));
		$path_built = IS_WINDOWS_OS ? '' : '/';
		foreach ($path_bits as $bit) {
			if (!empty ($bit)) {
				$new_path = $path_built . $bit;
				if (is_dir($new_path)) {
					$path_built = $new_path . '/';
				} else {
					mkdir($new_path);
					$path_built = $new_path . '/';
				}
			}
		}
	}

}

if (!function_exists('trim_value')) {
	function trim_value(& $value) {
		$value = trim($value);
	}
}
