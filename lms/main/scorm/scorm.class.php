<?php
header ( "Content-Type: text/html;charset=UTF-8");
require_once ('scormItem.class.php');
require_once ('scormMetadata.class.php');
require_once ('scormOrganization.class.php');
require_once ('scormResource.class.php');

class scorm extends learnpath {
	
	var $manifest = array ();
	var $resources = array ();
	var $resources_att = array ();
	var $organizations = array ();
	var $organizations_att = array ();
	var $metadata = array ();
	var $idrefs = array (); //will hold the references to resources for each item ID found
	var $refurls = array (); //for each resource found, stores the file url/uri
	var $subdir = ''; //path between the scorm/ directory and the imsmanifest.xml e.g. maritime_nav/maritime_nav. This is the path that will be used in the lp_path when importing a package
	var $items = array ();
	var $zipname = ''; //keeps the zipfile safe for the object's life so that we can use it if no title avail
	var $lastzipnameindex = 0; //keeps an index of the number of uses of the zipname so far
	var $manifest_encoding = 'UTF-8';
	var $debug = SCORM_DEBUG;
	var $learning_time = 0;
	var $learning_order = 0;

	/**
	 * Class constructor. Based on the parent constructor.
	 * @param	string	Course code
	 * @param	integer	Learnpath ID in DB
	 * @param	integer	User ID
	 */
	function scorm($course_code = null, $resource_id = null, $user_id = null) {
		if ($this->debug > 0) {
			api_scorm_log ( '(' . __FILE__ . ',' . __LINE__ . ')- scorm::scorm(' . $course_code . ',' . $resource_id . ',' . $user_id . ') - In scorm constructor', __FILE__, __LINE__ );
		}
		if (! empty ( $course_code ) and ! empty ( $resource_id ) and ! empty ( $user_id )) {
			parent::learnpath ( $course_code, $resource_id, $user_id );
		}
	}

	/**
	 * Opens a resource
	 * @param	integer	Database ID of the resource
	 */
	function open($id) {
		if ($this->debug > 0) api_scorm_log ( '(' . __FILE__ . ',' . __LINE__ . ')- scorm::open() - In scorm::open method', __FILE__, __LINE__ );
	}

	/**
	 * Possible SCO status: see CAM doc 2.3.2.5.1: passed, completed, browsed, failed, not attempted, incomplete
	 */
	/**
	 * Prerequisites: see CAM doc 2.3.2.5.1 for pseudo-code
	 */
	/**
	 * Parses an imsmanifest.xml file and puts everything into the $manifest array
	 * @param	string	Path to the imsmanifest.xml file on the system. If not defined, uses the base path of the course's scorm dir
	 * @return	array	Structured array representing the imsmanifest's contents
	 */
	function parse_manifest($file = '') {
		if ($this->debug > 0) api_scorm_log ( 'In scorm::parse_manifest(' . $file . ')', __FILE__, __LINE__ );
		
		if (empty ( $file ) or file_exists ( $file ) == false) {
			api_scorm_log ( 'ERROR - In scorm::parse_manifest() - No such file: ' . $file, __FILE__, __LINE__ );
			exit ( 'SCORM包解析文件imsmanifest.xml===>' . $file . '不存在, 解析失败!' );
			return null;
		}
		if (is_file ( $file ) and is_readable ( $file )) {
			$v = substr ( phpversion (), 0, 1 );
			//PHP V5.x版本使用
			if ($v == 5) {
				if ($this->debug > 0) {
					api_scorm_log ( 'In scorm::parse_manifest() - Parsing using PHP5 method', __FILE__, __LINE__ );
				}
				try {
					$doc = new DOMDocument ();
					$res = $doc->load ( $file );
				} catch ( Exception $ex ) {
					api_scorm_log ( "ERROR - In scorm.class.php::parse_manifest : " . $ex->getMessage (), __FILE__, __LINE__ );
				}
				
				if ($res === false) {
					api_scorm_log ( 'ERROR - In scorm::parse_manifest() - Exception thrown when loading ' . $file . ' in DOMDocument', __FILE__, __LINE__ );
					exit ( "加载文件imsmanifest.xml异常! 请检查该文件编码是否为UTF-8(若非UTF-8编码,请使用EditPlus/UltraEdit打开该文件,另存为UTF-8编码.) " );
					return null;
				}
				
				//文件编码
				if (! empty ( $doc->xmlEncoding )) {
					$this->manifest_encoding = strtoupper ( $doc->xmlEncoding );
				}
				if ($this->debug > 1) {
					api_scorm_log ( 'Called  (encoding:' . $doc->xmlEncoding . ' - saved: ' . $this->manifest_encoding . ')', __FILE__, __LINE__ );
				}
				
				$root = $doc->documentElement;
				if ($root->hasAttributes ()) {
					$attributes = $root->attributes;
					if ($attributes->length !== 0) {
						foreach ( $attributes as $attrib ) { //<manifest> element attributes
							$this->manifest [$attrib->name] = $attrib->value;
						}
					}
				}
				$this->manifest ['name'] = $root->tagName;
				if ($root->hasChildNodes ()) {
					$children = $root->childNodes;
					if ($children->length !== 0) {
						foreach ( $children as $child ) {
							//<manifest> element children (can be <metadata>, <organizations> or <resources> )
							if ($child->nodeType == XML_ELEMENT_NODE) {
								switch ($child->tagName) {
									case 'metadata' :
										//parse items from inside the <metadata> element
										$this->metadata = new scormMetadata ( 'manifest', $child );
										break;
									case 'organizations' :
										//contains the course structure - this element appears 1 and only 1 time in a package imsmanifest. It contains at least one 'organization' sub-element
										$orgs_attribs = $child->attributes;
										foreach ( $orgs_attribs as $orgs_attrib ) { //attributes of the <organizations> element
											if ($orgs_attrib->nodeType == XML_ATTRIBUTE_NODE) {
												$this->manifest ['organizations'] [$orgs_attrib->name] = $orgs_attrib->value;
											}
										}
										$orgs_nodes = $child->childNodes;
										$i = 0;
										$found_an_org = false;
										foreach ( $orgs_nodes as $orgnode ) {
											//<organization> elements - can contain <item>, <metadata> and <title>
											//Here we are at the 'organization' level. There might be several organization tags but
											//there is generally only one.
											//There are generally three children nodes we are looking for inside and organization:
											//-title
											//-item (may contain other item tags or may appear several times inside organization)
											//-metadata (relative to the organization)
											$found_an_org = false;
											switch ($orgnode->nodeType) {
												case XML_TEXT_NODE :
													//ignore here
													break;
												case XML_ATTRIBUTE_NODE :
													//just in case there would be interesting attributes inside the organization tag. There shouldn't
													//as this is a node-level, not a data level
													//$manifest['organizations'][$i][$orgnode->name] = $orgnode->value;
													//$found_an_org = true;
													break;
												case XML_ELEMENT_NODE :
													//<item>,<metadata> or <title> (or attributes)
													$organizations_attributes = $orgnode->attributes;
													foreach ( $organizations_attributes as $orgs_attr ) {
														$this->organizations_att [$orgs_attr->name] = $orgs_attr->value;
													}
													$oOrganization = new scormOrganization ( 'manifest', $orgnode, $this->manifest_encoding );
													if ($oOrganization->identifier != '') {
														$name = $oOrganization->get_name ();
														if (empty ( $name )) {
															//if the org title is empty, use zip file name
															$myname = $this->zipname;
															if ($this->lastzipnameindex != 0) {
																$myname = $myname + $this->lastzipnameindex;
																$this->lastzipnameindex ++;
															}
															$oOrganization->set_name ( $this->zipname );
														}
														$this->organizations [$oOrganization->identifier] = $oOrganization;
													}
													break;
											}
										}
										break;
									case 'resources' :
										if ($child->hasAttributes ()) {
											$resources_attribs = $child->attributes;
											foreach ( $resources_attribs as $res_attr ) {
												if ($res_attr->type == XML_ATTRIBUTE_NODE) {
													$this->manifest ['resources'] [$res_attr->name] = $res_attr->value;
												}
											}
										}
										if ($child->hasChildNodes ()) {
											$resources_nodes = $child->childNodes;
											$i = 0;
											foreach ( $resources_nodes as $res_node ) {
												$oResource = new scormResource ( 'manifest', $res_node );
												if ($oResource->identifier != '') {
													$this->resources [$oResource->identifier] = $oResource;
													$i ++;
												}
											}
										}
										//contains links to physical resources
										break;
									case 'manifest' :
										//only for sub-manifests
										break;
								}
							}
						}
					}
				}
				unset ( $doc );
			} else {
				if ($this->debug > 0) {
					api_scorm_log ( 'In scorm::parse_manifest() - PHP version is not 4 nor 5, cannot parse', __FILE__, __LINE__ );
				}
				$this->set_error_msg ( "Parsing impossible because PHP version is not 4 nor 5" );
				return null;
			}
		} else {
			if ($this->debug > 1) {
				api_scorm_log ( 'Could not open/read file ' . $file, __FILE__, __LINE__ );
			}
			$this->set_error_msg ( "File $file could not be read" );
			return null;
		}
		//TODO close the DOM handler
		return $this->manifest;
	}

	/**
	 * Import the scorm object (as a result from the parse_manifest function) into the database structure
	 * @param	string	Unique course code
	 * @return	bool	Returns -1 on error
	 */
	function import_manifest($course_code) {
		if ($this->debug > 0) api_scorm_log ( 'Entered import_manifest(' . $course_code . ')', __FILE__, __LINE__ );
		
		$sql = "SELECT * FROM " . Database::get_main_table ( TABLE_MAIN_COURSE ) . " WHERE code='$course_code'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $res ) < 1) {
			api_scorm_log ( 'Database for ' . $course_code . ' not found ' . __FILE__ . ' ' . __LINE__, __FILE__, __LINE__ );
			return - 1;
		}
		$row = Database::fetch_array ( $res );
		$dbname = $row ['db_name'];
		
		//liyu: 20091102 没有结构图时
		if (empty ( $this->organizations ) or count ( $this->organizations ) == 0) {
			api_scorm_log ( "---: imsmanifest.xml has no organizations, Convert failed!", __FILE__, __LINE__ );
			exit ( "imsmanifest.xml has no child node of organizations node , Import failed!" );
		}
		
		//get table names
		$new_lp = Database::get_course_table ( TABLE_LP_MAIN, $dbname );
		$new_lp_item = Database::get_course_table ( TABLE_LP_ITEM, $dbname );
		
		foreach ( $this->organizations as $id => $dummy ) {
			$lp_type = 2;
			$oOrganization = & $this->organizations [$id];
			$get_max = "SELECT MAX(display_order) FROM $new_lp WHERE cc='" . escape ( $course_code ) . "' ";
			$res_max = Database::get_scalar_value ( $get_max );
			$dsp = (empty ( $res_max ) ? 1 : $res_max + 1);
			
			$myname = $oOrganization->get_name ();
			//$this->manifest_encoding = 'UTF-8';
			global $charset;
			if ($charset) $charset = 'UTF-8';
			if (! empty ( $charset ) && ! empty ( $this->manifest_encoding ) && $this->manifest_encoding != $charset) {
				$myname = api_convert_encoding ( $myname, $charset, $this->manifest_encoding );
			}
			
			$sql_data = array (
					'lp_type' => 2, 
						'name' => $myname, 
						'ref' => $oOrganization->get_ref (), 
						'description' => '', 
						'path' => $this->subdir, 
						'force_commit' => 0, 
						'default_view_mod' => 'embedded', 
						'default_encoding' => $this->manifest_encoding, 
						'js_lib' => 'scorm_api.php', 
						'display_order' => $dsp );
			$sql_data ['cc'] = api_get_course_code ();
			$sql = Database::sql_insert ( $new_lp, $sql_data );
			if ($this->debug > 1) {
				api_scorm_log ( 'In import_manifest(), inserting path: ' . $sql, __FILE__, __LINE__ );
			}
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$lp_id = Database::get_last_insert_id ();
			$this->lp_id = $lp_id;
			api_item_property_update ( api_get_course_info ( $course_code ), TOOL_LEARNPATH, $this->lp_id, 'LearnpathAdded', api_get_user_id () );
			api_item_property_update ( api_get_course_info ( $course_code ), TOOL_LEARNPATH, $this->lp_id, 'visible', api_get_user_id () );
			
			//now insert all elements from inside that learning path
			//make sure we also get the href and sco/asset from the resources
			$list = $oOrganization->get_flat_items_list ();
			$parents_stack = array (0 );
			$parent = 0;
			$previous = 0;
			$level = 0;
			foreach ( $list as $item ) {
				if ($item ['level'] > $level) {
					array_push ( $parents_stack, $previous );
					$parent = $previous;
				} elseif ($item ['level'] < $level) {
					$diff = $level - $item ['level'];
					for($j = 1; $j <= $diff; $j ++) {
						$outdated_parent = array_pop ( $parents_stack );
					}
					$parent = array_pop ( $parents_stack ); //just save that value, then add it back
					array_push ( $parents_stack, $parent );
				}
				$path = '';
				$type = 'dir';
				if (isset ( $this->resources [$item ['identifierref']] )) {
					$oRes = & $this->resources [$item ['identifierref']];
					$path = @$oRes->get_path ();
					if (! empty ( $path )) {
						$temptype = $oRes->get_scorm_type ();
						if (! empty ( $temptype )) {
							$type = $temptype;
						}
					}
				}
				$level = $item ['level'];
				$field_add = '';
				$value_add = '';
				$field_arr_data = array ();
				if (! empty ( $item ['masteryscore'] )) {
					$field_add .= 'mastery_score';
					$value_add .= $item ['masteryscore'];
					$field_arr_data ['mastery_score'] = $item ['masteryscore'];
				}
				if (! empty ( $item ['maxtimeallowed'] )) {
					$field_add .= 'max_time_allowed';
					$value_add .= "'" . $item ['maxtimeallowed'] . "'";
					$field_arr_data ['max_time_allowed'] = $item ['maxtimeallowed'];
				}
				
				$title = Database::escape_string ( $item ['title'] );
				$max_score = Database::escape_string ( $item ['max_score'] );
				if ($max_score == 0 || is_null ( $max_score ) || $max_score == '') {
					$max_score = 100;
				}
				//DOM in PHP5 is always recovering data as UTF-8, somehow, no matter what
				//the XML document encoding is. This means that we have to convert
				//the data to the declared encoding when it is not UTF-8
				if ($this->manifest_encoding != 'UTF-8') {
					$title = api_convert_encoding ( $title, $this->manifest_encoding, 'UTF-8' );
				}
				//if($this->manifest_encoding != $charset){
				//	$title = api_convert_encoding($title,$charset,$this->manifest_encoding);
				//}
				$identifier = Database::escape_string ( $item ['identifier'] );
				$prereq = Database::escape_string ( $item ['prerequisites'] );
				$sql_data = array (
						'lp_id' => $lp_id, 
							'item_type' => $type, 
							'ref' => $identifier, 
							'title' => $title, 
							'path' => $path, 
							'min_score' => '0', 
							'max_score' => $max_score, 
							'parent_item_id' => $parent, 
							'previous_item_id' => $previous, 
							'next_item_id' => 0, 
							'prerequisite' => $prereq, 
							'display_order' => $item ['rel_order'], 
							'launch_data' => $item ['datafromlms'], 
							'parameters' => $item ['parameters'] );
				$sql_data = array_merge ( $sql_data, $field_arr_data );
				$sql_data ['cc'] = api_get_course_code ();
				$sql_item = Database::sql_insert ( $new_lp_item, $sql_data );
				$res_item = api_sql_query ( $sql_item, __FILE__, __LINE__ );
				if ($this->debug > 1) {
					api_scorm_log ( 'In import_manifest(), inserting item : ' . $sql_item . ' : ' . mysql_error (), __FILE__, __LINE__ );
				}
				$item_id = Database::get_last_insert_id ();
				//now update previous item to change next_item_id
				$upd = "UPDATE $new_lp_item SET next_item_id = $item_id WHERE id = $previous";
				$upd_res = api_sql_query ( $upd );
				$previous = $item_id;
			}
		}
	}

	/**
	 * Intermediate to import_package only to allow import from local zip files
	 * @param	string	Path to the zip file, from the ZLMS sys root
	 * @param	string	Current path (optional)
	 * @return string	Absolute path to the imsmanifest.xml file or empty string on error
	 */
	function import_local_package($file_path, $current_dir = '') {
		//todo prepare info as given by the $_FILES[''] vector
		$file_info = array ();
		$file_info ['tmp_name'] = $file_path;
		$file_info ['name'] = basename ( $file_path );
		//call the normal import_package function
		return $this->import_package ( $file_info, $current_dir );
	}

	/**
	 * 导入
	 * Imports a zip file into the ZLMS structure
	 * @param	string	Zip file info as given by $_FILES['userFile']
	 * @return	string	Absolute path to the imsmanifest.xml file or empty string on error
	 */
	function import_package($zip_file_info, $current_dir = '') {
		if ($this->debug) api_scorm_log ( 'In scorm::import_package(' . print_r ( $zip_file_info, true ) . ',"' . $current_dir . '") method', __FILE__, __LINE__ );
		require_once (api_get_path ( LIBRARY_PATH ) . "document.lib.php");
		$maxFilledSpace = DocumentManager::get_course_quota ();
		if ($maxFilledSpace) $maxFilledSpace = 1000000000;
		$zip_file_path = $zip_file_info ['tmp_name'];
		$zip_file_name = $zip_file_info ['name'];
		if ($this->debug > 1) api_scorm_log ( 'import_package() - zip file path = ' . $zip_file_path . ', zip file name = ' . $zip_file_name, __FILE__, __LINE__ );
		
		$course_rel_dir = api_get_course_path () . '/scorm'; //scorm dir web path starting from /courses
		$course_sys_dir = api_get_path ( SYS_COURSE_PATH ) . $course_rel_dir; //absolute system path for this course
		$current_dir = replace_dangerous_char ( trim ( $current_dir ), 'strict' ); //current dir we are in, inside scorm/
		if ($this->debug > 1) api_scorm_log ( 'import_package() - current_dir = ' . $current_dir, __FILE__, __LINE__ );
		
		//$uploaded_filename = $_FILES['userFile']['name']; get name of the zip file without the extension
		if ($this->debug > 1) api_scorm_log ( 'Received zip file name: ' . $zip_file_path, __FILE__, __LINE__ );
		$file_info = pathinfo ( $zip_file_name );
		$filename = $file_info ['basename'];
		$extension = $file_info ['extension'];
		$file_base_name = str_replace ( '.' . $extension, '', $filename ); //filename without its extension
		$this->zipname = $file_base_name; //save for later in case we don't have a title
		

		if ($this->debug > 1) api_scorm_log ( "base file name is : " . $file_base_name, __FILE__, __LINE__ );
		//$new_dir = replace_dangerous_char(trim($file_base_name),'strict');
		//$this->subdir = $new_dir;
		$new_dir = get_unique_name ();
		$this->subdir = $new_dir;
		if ($this->debug > 1) api_scorm_log ( "subdir is first set to : " . $this->subdir, __FILE__, __LINE__ );
		
		$zipFile = new pclZip ( $zip_file_path );
		$zipContentArray = $zipFile->listContent ();
		$package_type = '';
		$at_root = false;
		$manifest = '';
		$manifest_list = array ();
		//the following loop should be stopped as soon as we found the right imsmanifest.xml (how to recognize it?)
		foreach ( $zipContentArray as $thisContent ) {
			//api_scorm_log('Looking at  '.$thisContent['filename'],__FILE__,__LINE__);
			if (preg_match ( '~.(php.*|phtml)$~i', $thisContent ['filename'] )) {
				$this->set_error_msg ( "File $file contains a PHP script" );
			} elseif (stristr ( $thisContent ['filename'], 'imsmanifest.xml' )) {
				if ($thisContent ['filename'] == basename ( $thisContent ['filename'] )) {
					$at_root = true;
				} else {
					if ($this->debug > 2) api_scorm_log ( "subdir is now " . $this->subdir, __FILE__, __LINE__ );
				}
				$package_type = 'scorm';
				$manifest_list [] = $thisContent ['filename'];
				$manifest = $thisContent ['filename']; //just the relative directory inside scorm/
			} else {
				//do nothing, if it has not been set as scorm somewhere else, it stays as '' default
			}
			$realFileSize += $thisContent ['size'];
		}
		//now get the shortest path (basically, the imsmanifest that is the closest to the root)
		$shortest_path = $manifest_list [0];
		$slash_count = substr_count ( $shortest_path, '/' );
		foreach ( $manifest_list as $manifest_path ) {
			$tmp_slash_count = substr_count ( $manifest_path, '/' );
			if ($tmp_slash_count < $slash_count) {
				$shortest_path = $manifest_path;
				$slash_count = $tmp_slash_count;
			}
		}
		$this->subdir .= '/' . dirname ( $shortest_path ); //do not concatenate because already done above
		$manifest = $shortest_path;
		
		if ($this->debug > 1) api_scorm_log ( 'Package type is now ' . $package_type, __FILE__, __LINE__ );
		if ($package_type == '') {return api_failure::set_failure ( 'not_scorm_content' );}
		if (! enough_size ( $realFileSize, $course_sys_dir, $maxFilledSpace )) {return api_failure::set_failure ( 'not_enough_space' );}
		if ($new_dir [0] != '/') $new_dir = '/' . $new_dir;
		
		if ($new_dir [strlen ( $new_dir ) - 1] == '/') $new_dir = substr ( $new_dir, 0, - 1 );
		
		/*	解压*/
		if (! is_dir ( $course_sys_dir . $new_dir )) @mkdir ( $course_sys_dir . $new_dir );
		
		if (get_cfg_var ( 'safe_mode' ) == false && PHP_OS == 'Linux' && file_exists ( '/usr/bin/unzip' )) {
			$cmd = "unzip -l " . $zip_file_path;
			if ($this->debug >= 1) api_scorm_log ( 'Linux system, using CMD=' . $cmd, __FILE__, __LINE__ );
			exec ( $cmd, $cmd_output_zipFileList );
			
			$cmd = "unzip -d \"" . $course_sys_dir . $new_dir . "\" " . $zip_file_path;
			exec ( $cmd, $out_msg );
			if ($this->debug >= 1) api_scorm_log ( 'Linux system, using CMD=' . $cmd, ",Result=" . $out_msg, __FILE__, __LINE__ );
		} elseif (get_cfg_var ( 'safe_mode' ) == false && PHP_OS == "WINNT" && file_exists ( BIN_PATH . "unzip.exe" )) {
			$cmd = BIN_PATH . "unzip.exe -l " . $zip_file_path;
			if ($this->debug >= 1) api_scorm_log ( 'Windows system, using CMD=' . $cmd, __FILE__, __LINE__ );
			$rtn = exec ( $cmd, $cmd_output_zipFileList );
			api_scorm_log ( 'unzip -l exe result=' . $rtn, __FILE__, __LINE__ );
			
			$cmd = BIN_PATH . "unzip.exe -d \"" . $course_sys_dir . "" . $new_dir . "\" " . $zip_file_path;
			exec ( $cmd, $out_msg );
			api_scorm_log ( 'Windows system, using CMD=' . $cmd, __FILE__, __LINE__ );
			if ($this->debug >= 1) api_scorm_log ( 'Windows system, using CMD=' . $cmd, ",Result=" . $out_msg, __FILE__, __LINE__ );
		} else {
		
		}
		
		if (! is_dir ( $course_sys_dir . $new_dir ) or ! file_exists ( $course_sys_dir . $new_dir )) {
			if ($this->debug >= 1) api_scorm_log ( 'Changing dir to ' . $course_sys_dir . $new_dir, __FILE__, __LINE__ );
			$saved_dir = getcwd ();
			chdir ( $course_sys_dir . $new_dir );
			$unzippingState = $zipFile->extract ();
			if (! $unzippingState) {
				api_error_log ( "ERROR: Unzip SCORM zip file Failed", __FILE__, __LINE__ );
				exit ( "解压SCORM文件" . $zip_file_path . '失败!' );
			}
			for($j = 0; $j < count ( $unzippingState ); $j ++) {
				$state = $unzippingState [$j];
				$extension = strrchr ( $state ["stored_filename"], "." );
				if ($this->debug >= 1) {
					api_scorm_log ( 'found extension ' . $extension . ' in ' . $state ['stored_filename'], __FILE__, __LINE__ );
				}
			}
		}
		
		if (! is_dir ( $course_sys_dir . $new_dir ) or ! file_exists ( $course_sys_dir . $new_dir )) {
			api_scorm_log ( 'Extract SCORM zip file=' . $zip_file_path . " Failed! check php.ini setting", __FILE__, __LINE__ );
			exit ( '解压SCORM文件:' . basename ( $zip_file_path ) . " 失败! 请检查 php.ini 中设置: safe_mode=off或者解压命令unzip 路径=" . BIN_PATH );
		}
		
		if (! empty ( $new_dir )) $new_dir = $new_dir . '/';
		
		//rename files, for example with \\ in it
		if ($dir = @opendir ( $course_sys_dir . $new_dir )) {
			if ($this->debug >= 1) api_scorm_log ( ' Opened dir ' . $course_sys_dir . $new_dir, __FILE__, __LINE__ );
			
			while ( $file = readdir ( $dir ) ) {
				if ($file != '.' && $file != '..') {
					$filetype = "file";
					if (is_dir ( $course_sys_dir . $new_dir . $file )) $filetype = "folder";
					$find_str = array ('\\', '.php', '.phtml' );
					$repl_str = array ('/', '.txt', '.txt' );
					$safe_file = str_replace ( $find_str, $repl_str, $file );
					
					if ($safe_file != $file) {
						//@rename($course_sys_dir.$new_dir,$course_sys_dir.'/'.$safe_file);
						$mydir = dirname ( $course_sys_dir . $new_dir . $safe_file );
						if (! is_dir ( $mydir )) {
							$mysubdirs = split ( '/', $mydir );
							$mybasedir = '/';
							foreach ( $mysubdirs as $mysubdir ) {
								if (! empty ( $mysubdir )) {
									$mybasedir = $mybasedir . $mysubdir . '/';
									if (! is_dir ( $mybasedir )) {
										@mkdir ( $mybasedir, CHMOD_NORMAL );
										if ($this->debug == 1) {
											api_scorm_log ( 'Dir ' . $mybasedir . ' doesnt exist. Creating.', __FILE__, __LINE__ );
										}
									}
								}
							}
						}
						@rename ( $course_sys_dir . $new_dir . $file, $course_sys_dir . $new_dir . $safe_file );
						if ($this->debug == 1) {
							api_scorm_log ( 'Renaming ' . $course_sys_dir . $new_dir . $file . ' to ' . $course_sys_dir . $new_dir . $safe_file, __FILE__, __LINE__ );
						}
					}
				}
			}
			
			closedir ( $dir );
			chdir ( $saved_dir );
			
			$perm = api_get_setting ( 'permissions_for_new_directories' );
			$perm = octdec ( ! empty ( $perm ) ? $perm : '0770' );
			
			api_chmod_R ( $course_sys_dir . $new_dir, $perm );
		}
		
		return $course_sys_dir . $new_dir . $manifest;
	}

	/**
	 * Sets the proximity setting in the database
	 * @param	string	Proximity setting
	 */
	function set_proximity($proxy = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_proximity(' . $proxy . ') method', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET content_local = '$proxy' WHERE id = " . $lp;
			$res = api_sql_query ( $sql );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Sets the theme setting in the database
	 * @param	string	theme setting
	 */
	function set_theme($theme = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_theme(' . $theme . ') method', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET theme = '$theme' WHERE id = " . $lp;
			$res = api_sql_query ( $sql );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Sets the image setting in the database
	 * @param	string preview_image setting
	 */
	function set_preview_image($preview_image = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_theme(' . $preview_image . ') method', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET preview_image = '$preview_image' WHERE id = " . $lp;
			$res = api_sql_query ( $sql );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Sets the author  setting in the database
	 * @param	string preview_image setting
	 */
	function set_author($author = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_author(' . $author . ') method', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET author = '$author' WHERE id = " . $lp;
			$res = api_sql_query ( $sql );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Sets the content maker setting in the database
	 * @param	string	Proximity setting
	 */
	function set_maker($maker = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_maker method(' . $maker . ')', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET content_maker = '$maker' WHERE id = " . $lp;
			$res = api_sql_query ( $sql );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * V2.1
	 * @param unknown_type $time
	 */
	function set_learning_time($time = 0) {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_learning_time method(' . $time . ')', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET learning_time = '$time' WHERE id = " . $lp;
			return api_sql_query ( $sql );
		} else {
			return false;
		}
	}

	/**
	 * V2.1
	 * @param $time
	 */
	function set_learning_order($order = 0) {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::set_learning_order method(' . $order . ')', __FILE__, __LINE__ );
		}
		$lp = $this->get_id ();
		if ($lp != 0) {
			$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
			$sql = "UPDATE $tbl_lp SET learning_order = '$order' WHERE id = " . $lp;
			return api_sql_query ( $sql );
		} else {
			return false;
		}
	}

	/**
	 * Exports the current SCORM object's files as a zip. Excerpts taken from learnpath_functions.inc.php::exportpath()
	 * @param	integer	Learnpath ID (optional, taken from object context if not defined)
	 */
	function export_zip($lp_id = null) {
		if ($this->debug > 0) api_scorm_log ( 'In scorm::export_zip method(' . $lp_id . ')', __FILE__, __LINE__ );
		if (empty ( $lp_id )) {
			if (! is_object ( $this )) {
				return false;
			} else {
				$id = $this->get_id ();
				if (empty ( $id )) {
					return false;
				} else {
					$lp_id = $this->get_id ();
				}
			}
		}
		//error_log('in export_zip()',0);
		//zip everything that is in the corresponding scorm dir
		//write the zip file somewhere (might be too big to return)
		require_once (api_get_path ( LIBRARY_PATH ) . "fileManage.lib.php");
		require_once (api_get_path ( LIBRARY_PATH ) . "document.lib.php");
		require_once (api_get_path ( LIB_PATH ) . "pclzip/pclzip.lib.php");
		require_once ("learnpath_functions.inc.php");
		$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
		$_course = Database::get_course_info ( api_get_course_id () );
		$sql = "SELECT * FROM $tbl_lp WHERE id=" . $lp_id;
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$row = mysql_fetch_array ( $result );
		$LPname = $row ['path'];
		$list = split ( '/', $LPname );
		$LPnamesafe = $list [0];
		//$zipfoldername = '/tmp';
		//$zipfoldername = '../../courses/'.$_course['directory']."/temp/".$LPnamesafe;
		$zipfoldername = api_get_path ( SYS_COURSE_PATH ) . $_course ['directory'] . "/temp/" . $LPnamesafe;
		$scormfoldername = api_get_path ( SYS_COURSE_PATH ) . $_course ['directory'] . "/scorm/" . $LPnamesafe;
		$zipfilename = $zipfoldername . "/" . $LPnamesafe . ".zip";
		
		//Get a temporary dir for creating the zip file
		

		//error_log('cleaning dir '.$zipfoldername,0);
		deldir ( $zipfoldername ); //make sure the temp dir is cleared
		$res = mkdir ( $zipfoldername, CHMOD_NORMAL );
		//error_log('made dir '.$zipfoldername,0);
		

		//create zipfile of given directory
		$zip_folder = new PclZip ( $zipfilename );
		$zip_folder->create ( $scormfoldername . '/', PCLZIP_OPT_REMOVE_PATH, $scormfoldername . '/' );
		
		//$zipfilename = '/var/www/ZLMS-comp/courses/TEST2/scorm/example_document.html';
		//this file sending implies removing the default mime-type from php.ini
		//DocumentManager :: file_send_for_download($zipfilename, true, $LPnamesafe.".zip");
		DocumentManager::file_send_for_download ( $zipfilename, true );
		
		// Delete the temporary zip file and directory in fileManage.lib.php
		my_delete ( $zipfilename );
		my_delete ( $zipfoldername );
		
		return true;
	}

	/**
	 * Gets a resource's path if available, otherwise return empty string
	 * @param	string	Resource ID as used in resource array
	 * @return string	The resource's path as declared in imsmanifest.xml
	 */
	function get_res_path($id) {
		if ($this->debug > 0) api_scorm_log ( 'In scorm::get_res_path(' . $id . ') method', __FILE__, __LINE__ );
		$path = '';
		if (isset ( $this->resources [$id] )) {
			$oRes = & $this->resources [$id];
			$path = @$oRes->get_path ();
		}
		return $path;
	}

	/**
	 * Gets a resource's type if available, otherwise return empty string
	 * @param	string	Resource ID as used in resource array
	 * @return string	The resource's type as declared in imsmanifest.xml
	 */
	function get_res_type($id) {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::get_res_type(' . $id . ') method', __FILE__, __LINE__ );
		}
		$type = '';
		if (isset ( $this->resources [$id] )) {
			$oRes = & $this->resources [$id];
			$temptype = $oRes->get_scorm_type ();
			if (! empty ( $temptype )) {
				$type = $temptype;
			}
		}
		return $type;
	}

	/**
	 * Gets the default organisation's title
	 * @return	string	The organization's title
	 */
	function get_title() {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::get_title() method', __FILE__, __LINE__ );
		}
		$title = '';
		if (isset ( $this->manifest ['organizations'] ['default'] )) {
			$title = $this->organizations [$this->manifest ['organizations'] ['default']]->get_name ();
		} elseif (count ( $this->organizations ) == 1) {
			//this will only get one title but so we don't need to know the index
			foreach ( $this->organizations as $id => $value ) {
				$title = $this->organizations [$id]->get_name ();
				break;
			}
		}
		return $title;
	}

	/**
	 * //TODO @TODO implement this function to restore items data from an imsmanifest,
	 * updating the existing table... This will prove very useful in case initial data
	 * from imsmanifest were not imported well enough
	 * @param	string	course Code
	 * @param string	LP ID (in database)
	 * @param string	Manifest file path (optional if lp_id defined)
	 * @return	integer	ID or false on failure
	 * TODO @TODO Implement imsmanifest_path parameter
	 */
	function reimport_manifest($course, $lp_id = null, $imsmanifest_path = '') {
		if ($this->debug > 0) {
			api_scorm_log ( 'In scorm::reimport_manifest() method', __FILE__, __LINE__ );
		}
		global $_course;
		//RECOVERING PATH FROM DB
		$main_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		//$course = Database::escape_string($course);
		$course = $this->escape_string ( $course );
		$sql = "SELECT * FROM $main_table WHERE code = '$course'";
		if ($this->debug > 2) {
			api_scorm_log ( 'scorm::reimport_manifest() ' . __LINE__ . ' - Querying course: ' . $sql, __FILE__, __LINE__ );
		}
		//$res = Database::query($sql);
		$res = api_sql_query ( $sql );
		if (Database::num_rows ( $res ) > 0) {
			$this->cc = $course;
		} else {
			$this->error = 'Course code does not exist in database (' . $sql . ')';
			return false;
		}
		
		//TODO make it flexible to use any course_code (still using env course code here)
		//$lp_table = Database::get_course_table(LEARNPATH_TABLE);
		$lp_table = Database::get_course_table ( TABLE_LP_MAIN );
		
		//$id = Database::escape_integer($id);
		$lp_id = $this->escape_string ( $lp_id );
		$sql = "SELECT * FROM $lp_table WHERE id = '$lp_id'";
		if ($this->debug > 2) {
			api_scorm_log ( 'scorm::reimport_manifest() ' . __LINE__ . ' - Querying lp: ' . $sql, __FILE__, __LINE__ );
		}
		//$res = Database::query($sql);
		$res = api_sql_query ( $sql );
		if (Database::num_rows ( $res ) > 0) {
			$this->lp_id = $lp_id;
			$row = Database::fetch_array ( $res );
			$this->type = $row ['lp_type'];
			$this->name = stripslashes ( $row ['name'] );
			$this->encoding = $row ['default_encoding'];
			$this->proximity = $row ['content_local'];
			$this->maker = $row ['content_maker'];
			$this->prevent_reinit = $row ['prevent_reinit'];
			$this->license = $row ['content_license'];
			$this->scorm_debug = $row ['debug'];
			$this->js_lib = $row ['js_lib'];
			$this->path = $row ['path'];
			if ($this->type == 2) {
				if ($row ['force_commit'] == 1) {
					$this->force_commit = true;
				}
			}
			$this->mode = $row ['default_view_mod'];
			$this->subdir = $row ['path'];
		}
		//parse the manifest (it is already in this lp's details)
		$manifest_file = api_get_path ( 'SYS_COURSE_PATH' ) . $_course ['directory'] . '/scorm/' . $this->subdir . '/imsmanifest.xml';
		if ($this->subdir == '') {
			$manifest_file = api_get_path ( 'SYS_COURSE_PATH' ) . $_course ['directory'] . '/scorm/imsmanifest.xml';
		}
		echo $manifest_file;
		if (is_file ( $manifest_file ) && is_readable ( $manifest_file )) {
			//re-parse the manifest file
			if ($this->debug > 1) {
				api_scorm_log ( 'In scorm::reimport_manifest() - Parsing manifest ' . $manifest_file, __FILE__, __LINE__ );
			}
			$manifest = $this->parse_manifest ( $manifest_file );
			//import new LP in DB (ignore the current one)
			if ($this->debug > 1) {
				api_scorm_log ( 'In scorm::reimport_manifest() - Importing manifest ' . $manifest_file, __FILE__, __LINE__ );
			}
			$this->import_manifest ( api_get_course_id () );
		} else {
			if ($this->debug > 0) {
				api_scorm_log ( 'In scorm::reimport_manifest() - Could not find manifest file at ' . $manifest_file, __FILE__, __LINE__ );
			}
		}
		return false;
	}
}
?>