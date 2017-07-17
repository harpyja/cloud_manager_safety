<?php
/*
 ==============================================================================

 ==============================================================================
 */
/**
 ==============================================================================
 * This is the document library for ZLMS.
 * It is / will be used to provide a service layer to all document-using tools.
 * and eliminate code duplication fro group documents, scorm documents, main documents.
 * Include/require it in your code to use its functionality.
 *
 * @version 1.1, January 2005
 * @package zllms.library
 ==============================================================================
 */

/*
 ==============================================================================
 DOCUMENTATION
 use the functions like this: DocumentManager::get_course_quota()
 ==============================================================================
 */

/*
 ==============================================================================
 CONSTANTS
 ==============================================================================
 */

define ( "DISK_QUOTA_FIELD", "disk_quota" ); //name of the database field


/*
 ==============================================================================
 VARIABLES
 ==============================================================================
 */

$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$baseServDir = api_get_path ( SYS_PATH );
$baseServUrl = $_configuration ['url_append'] . "/";
$baseWorkDir = $sys_course_path . (! empty ( $courseDir ) ? $courseDir : '');

/*
 ==============================================================================
 DocumentManager CLASS
 the class and its functions
 ==============================================================================
 */

/**
 * @package zllms.library
 */
class DocumentManager {

	/**
	 * @return the document folder quuta of the current course, in bytes
	 * @todo eliminate globals
	 */
	public static function get_course_quota() {
		global $_course, $maxFilledSpace;
		$course_code = $_course ['sysCode'];
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		
		$sql_query = "SELECT `" . DISK_QUOTA_FIELD . "` FROM $course_table WHERE `code` = '$course_code'";
		//$sql_result = api_sql_query($sql_query, __FILE__, __LINE__);
		//$result = mysql_fetch_array($sql_result);
		//$course_quota = $result[DISK_QUOTA_FIELD];
		$course_quota = Database::get_scalar_value ( $sql_query );
		
		if ($course_quota == NULL) {
			//course table entry for quota was null use default value
			$course_quota = DEFAULT_DOCUMENT_QUOTA;
		}
		
		return $course_quota;
	}

	/**
	 * Get the content type of a file by checking the extension
	 * We could use mime_content_type() with php-versions > 4.3,
	 * but this doesn't work as it should on Windows installations
	 *
	 * @param string $filename or boolean TRUE to return complete array
	 * @author ? first version
	 * @author Bert Vanderkimpen
	 *
	 */
	function file_get_mime_type($filename) {
		//all mime types in an array (from 1.6, this is the authorative source)
		//please keep this alphabetical if you add something to this list!!!
		$mime_types = array (
				"ai" => "application/postscript", 
					"aif" => "audio/x-aiff", 
					"aifc" => "audio/x-aiff", 
					"aiff" => "audio/x-aiff", 
					"asf" => "video/x-ms-asf", 
					"asc" => "text/plain", 
					"au" => "audio/basic", 
					"avi" => "video/x-msvideo", 
					"bcpio" => "application/x-bcpio", 
					"bin" => "application/octet-stream", 
					"bmp" => "image/bmp", 
					"cdf" => "application/x-netcdf", 
					"class" => "application/octet-stream", 
					"cpio" => "application/x-cpio", 
					"cpt" => "application/mac-compactpro", 
					"csh" => "application/x-csh", 
					"css" => "text/css", 
					"dcr" => "application/x-director", 
					"dir" => "application/x-director", 
					"djv" => "image/vnd.djvu", 
					"djvu" => "image/vnd.djvu", 
					"dll" => "application/octet-stream", 
					"dmg" => "application/x-diskcopy", 
					"dms" => "application/octet-stream", 
					"doc" => "application/msword", 
					"dvi" => "application/x-dvi", 
					"dwg" => "application/vnd.dwg", 
					"dxf" => "application/vnd.dxf", 
					"dxr" => "application/x-director", 
					"eps" => "application/postscript", 
					"etx" => "text/x-setext", 
					"exe" => "application/octet-stream", 
					"ez" => "application/andrew-inset", 
					"gif" => "image/gif", 
					"gtar" => "application/x-gtar", 
					"gz" => "application/x-gzip", 
					"hdf" => "application/x-hdf", 
					"hqx" => "application/mac-binhex40", 
					"htm" => "text/html", 
					"html" => "text/html", 
					"ice" => "x-conference-xcooltalk", 
					"ief" => "image/ief", 
					"iges" => "model/iges", 
					"igs" => "model/iges", 
					"jar" => "application/java-archiver", 
					"jpe" => "image/jpeg", 
					"jpeg" => "image/jpeg", 
					"jpg" => "image/jpeg", 
					"js" => "application/x-javascript", 
					"kar" => "audio/midi", 
					"latex" => "application/x-latex", 
					"lha" => "application/octet-stream", 
					"lzh" => "application/octet-stream", 
					"m1a" => "audio/mpeg", 
					"m2a" => "audio/mpeg", 
					"m3u" => "audio/x-mpegurl", 
					"man" => "application/x-troff-man", 
					"me" => "application/x-troff-me", 
					"mesh" => "model/mesh", 
					"mid" => "audio/midi", 
					"midi" => "audio/midi", 
					"mov" => "video/quicktime", 
					"movie" => "video/x-sgi-movie", 
					"mp2" => "audio/mpeg", 
					"mp3" => "audio/mpeg", 
					"mp4" => "video/mpeg4-generic", 
					"mpa" => "audio/mpeg", 
					"mpe" => "video/mpeg", 
					"mpeg" => "video/mpeg", 
					"mpg" => "video/mpeg", 
					"mpga" => "audio/mpeg", 
					"ms" => "application/x-troff-ms", 
					"msh" => "model/mesh", 
					"mxu" => "video/vnd.mpegurl", 
					"nc" => "application/x-netcdf", 
					"oda" => "application/oda", 
					"pbm" => "image/x-portable-bitmap", 
					"pct" => "image/pict", 
					"pdb" => "chemical/x-pdb", 
					"pdf" => "application/pdf", 
					"pgm" => "image/x-portable-graymap", 
					"pgn" => "application/x-chess-pgn", 
					"pict" => "image/pict", 
					"png" => "image/png", 
					"pnm" => "image/x-portable-anymap", 
					"ppm" => "image/x-portable-pixmap", 
					"ppt" => "application/vnd.ms-powerpoint", 
					"pps" => "application/vnd.ms-powerpoint", 
					"ps" => "application/postscript", 
					"qt" => "video/quicktime", 
					"ra" => "audio/x-realaudio", 
					"ram" => "audio/x-pn-realaudio", 
					"rar" => "image/x-rar-compressed", 
					"ras" => "image/x-cmu-raster", 
					"rgb" => "image/x-rgb", 
					"rm" => "audio/x-pn-realaudio", 
					"roff" => "application/x-troff", 
					"rpm" => "audio/x-pn-realaudio-plugin", 
					"rtf" => "text/rtf", 
					"rtx" => "text/richtext", 
					"sgm" => "text/sgml", 
					"sgml" => "text/sgml", 
					"sh" => "application/x-sh", 
					"shar" => "application/x-shar", 
					"silo" => "model/mesh", 
					"sib" => "application/X-Sibelius-Score", 
					"sit" => "application/x-stuffit", 
					"skd" => "application/x-koan", 
					"skm" => "application/x-koan", 
					"skp" => "application/x-koan", 
					"skt" => "application/x-koan", 
					"smi" => "application/smil", 
					"smil" => "application/smil", 
					"snd" => "audio/basic", 
					"so" => "application/octet-stream", 
					"spl" => "application/x-futuresplash", 
					"src" => "application/x-wais-source", 
					"sv4cpio" => "application/x-sv4cpio", 
					"sv4crc" => "application/x-sv4crc", 
					"svf" => "application/vnd.svf", 
					"swf" => "application/x-shockwave-flash", 
					"sxc" => "application/vnd.sun.xml.calc", 
					"sxi" => "application/vnd.sun.xml.impress", 
					"sxw" => "application/vnd.sun.xml.writer", 
					"t" => "application/x-troff", 
					"tar" => "application/x-tar", 
					"tcl" => "application/x-tcl", 
					"tex" => "application/x-tex", 
					"texi" => "application/x-texinfo", 
					"texinfo" => "application/x-texinfo", 
					"tga" => "image/x-targa", 
					"tif" => "image/tif", 
					"tiff" => "image/tiff", 
					"tr" => "application/x-troff", 
					"tsv" => "text/tab-seperated-values", 
					"txt" => "text/plain", 
					"ustar" => "application/x-ustar", 
					"vcd" => "application/x-cdlink", 
					"vrml" => "model/vrml", 
					"wav" => "audio/x-wav", 
					"wbmp" => "image/vnd.wap.wbmp", 
					"wbxml" => "application/vnd.wap.wbxml", 
					"wml" => "text/vnd.wap.wml", 
					"wmlc" => "application/vnd.wap.wmlc", 
					"wmls" => "text/vnd.wap.wmlscript", 
					"wmlsc" => "application/vnd.wap.wmlscriptc", 
					"wma" => "video/x-ms-wma", 
					"wmv" => "video/x-ms-wmv", 
					"wrl" => "model/vrml", 
					"xbm" => "image/x-xbitmap", 
					"xht" => "application/xhtml+xml", 
					"xhtml" => "application/xhtml+xml", 
					"xls" => "application/vnd.ms-excel", 
					"xml" => "text/xml", 
					"xpm" => "image/x-xpixmap", 
					"xsl" => "text/xml", 
					"xwd" => "image/x-windowdump", 
					"xyz" => "chemical/x-xyz", 
					"zip" => "application/zip" );
		
		if ($filename === TRUE) {return $mime_types;}
		
		//get the extension of the file
		$extension = explode ( '.', $filename );
		
		//$filename will be an array if a . was found
		if (is_array ( $extension )) {
			$extension = (strtolower ( $extension [sizeof ( $extension ) - 1] ));
		} //file without extension
else {
			$extension = 'empty';
		}
		
		//if the extension is found, return the content type
		if (isset ( $mime_types [$extension] )) return $mime_types [$extension];
		//else return octet-stream
		return "application/octet-stream";
	}

	/**
	 * @return true if the user is allowed to see the document, false otherwise
	 * @author Sergio A Kessler, first version
	 * @author Roan Embrechts, bugfix
	 * @todo ??not only check if a file is visible, but also check if the user is allowed to see the file??
	 */
	function file_visible_to_user($this_course, $doc_url) {
		global $_course;
		if (api_is_allowed_to_edit ()) {
			return true;
		} else {
			$tbl_document = Database::get_course_table ( TABLE_DOCUMENT );
			$tbl_item_property = $this_course . 'item_property';
			//$doc_url = addslashes($doc_url);
			$query = "SELECT 1 FROM `$tbl_document` AS docs,`$tbl_item_property` AS props WHERE props.tool = '" . TOOL_DOCUMENT . "' AND docs.id=props.ref AND props.visibility <> '1' AND docs.path = '$doc_url'";
			$query .= " AND docs.cc='" . $_course ['code'] . "'";
			//echo $query;
			$result = api_sql_query ( $query, __FILE__, __LINE__ );
			
			return (mysql_num_rows ( $result ) == 0);
		}
	}

	/**
	 * 向浏览器发送一个文件流(下载)
	 *
	 * @param string $full_file_name 要下载的文件
	 * @param boolean $forced true强制浏览器下载文件,false: 由浏览器根据mimetype决定如何处理
	 * @param string $name 发送到浏览器的文件名
	 * @return false if file doesn't exist, true if stream succeeded
	 */
	function file_send_for_download($full_file_name, $forced = false, $name = '') {
		//$full_file_name=api_to_system_encoding($full_file_name);
		if (! is_file ( $full_file_name )) {return false;}
		$filename = ($name == '') ? basename ( $full_file_name ) : $name;
		$filename = api_to_system_encoding ( $filename ); //liyu:修改乱码
		$len = filesize ( $full_file_name );
		
		if ($forced) {
			//force the browser to save the file instead of opening it
			header ( 'Content-type: application/octet-stream' );
			//header('Content-Type: application/force-download');
			header ( 'Content-length: ' . $len );
			if (preg_match ( "/MSIE 5.5/", $_SERVER ['HTTP_USER_AGENT'] )) {
				header ( 'Content-Disposition: filename= ' . $filename );
			} else {
				header ( 'Content-Disposition: attachment; filename= ' . $filename );
			}
			if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' )) {
				header ( 'Pragma: ' );
				header ( 'Cache-Control: ' );
				// IE cannot download from sessions without a cache
				header ( 'Cache-Control: public' );
			}
			header ( 'Content-Description: ' . $filename );
			//header('Content-transfer-encoding: binary');
			

			$fp = fopen ( $full_file_name, 'r' );
			fpassthru ( $fp );
			return true;
		} else {
			//no forced download, just let the browser decide what to do according to the mimetype
			$content_type = DocumentManager::file_get_mime_type ( $filename );
			header ( 'Expires: Wed, 01 Jan 1990 00:00:00 GMT' );
			header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
			header ( 'Cache-Control: no-cache, must-revalidate' );
			header ( 'Pragma: no-cache' );
			header ( 'Content-type: ' . $content_type );
			header ( 'Content-Length: ' . $len );
			$user_agent = strtolower ( $_SERVER ['HTTP_USER_AGENT'] );
			if (strpos ( $user_agent, 'msie' )) {
				header ( 'Content-Disposition: ; filename= ' . $filename );
			} else {
				header ( 'Content-Disposition: inline; filename= ' . $filename );
			}
			readfile ( $full_file_name );
			return true;
		}
	}

	function get_all_document_data($_course, $can_see_invisible = false) {
		$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT );
		
		$visibility_bit = ' = 1';
		if ($can_see_invisible) {
			$visibility_bit = ' <> 2';
		}
		
		$sql = "SELECT * FROM  " . $TABLE_DOCUMENT . "  AS docs
		 WHERE docs.path!='/learnpath' AND filetype='file'
		 AND docs.cc=" . Database::escape ( api_get_course_code () ) . " ORDER BY docs.display_order ASC";
		//echo $sql;
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if ($result && Database::num_rows ( $result ) != 0) {
			while ( $row = Database::fetch_array ( $result, "ASSOC" ) ) {
				$document_data [$row ['id']] = $row;
			}
			//var_dump($document_data);
			return $document_data;
		} else {
			return false;
		}
	}

	/**
	 * Gets the paths of all folders in a course
	 * can show all folders (exept for the deleted ones) or only visible ones
	 * @param array $_course
	 * @param boolean $can_see_invisible
	 * @return array with paths
	 */
	function get_all_document_folders($_course, $can_see_invisible = false) {
		$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY, $_course ['dbName'] );
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
		
		if ($can_see_invisible) {
			$tbl = $TABLE_ITEMPROPERTY . "  AS last, " . $TABLE_DOCUMENT . "  AS docs ";
			$sql_where = " docs.id = last.ref	AND docs.filetype = 'folder' AND last.tool = '" . TOOL_DOCUMENT . "'  AND last.visibility <> 2 ";
			$sql = Database::select_from_course_table ( $tbl, $sql_where, " path, title ", " path asc ", 0, $_course ['code'], " docs.cc ", FALSE );
			//echo $sql."<Br/>";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			if ($result && Database::num_rows ( $result ) != 0) {
				while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
					$document_folders [$row ['path']] = $row ['title'];
				}
				
				return $document_folders;
			} else {
				return false;
			}
		} else {
			//get visible folders
			$visible_sql = "SELECT path, title
						FROM  " . $TABLE_ITEMPROPERTY . "  AS last, " . $TABLE_DOCUMENT . "  AS docs
						WHERE docs.id = last.ref
						AND docs.filetype = 'folder'
						AND last.tool = '" . TOOL_DOCUMENT . "'
						AND last.visibility = 1 ";
			$visible_sql .= " AND docs.cc='" . $_course ["code"] . "' ";
			$visible_sql .= " order by path asc ";
			$visibleresult = api_sql_query ( $visible_sql, __FILE__, __LINE__ );
			while ( $all_visible_folders = Database::fetch_array ( $visibleresult, 'ASSOC' ) ) {
				$visiblefolders [$all_visible_folders ['path']] = $all_visible_folders ['title'];
			}
			
			//get invisible folders
			$invisible_sql = "SELECT path
						FROM  " . $TABLE_ITEMPROPERTY . "  AS last, " . $TABLE_DOCUMENT . "  AS docs
						WHERE docs.id = last.ref
						AND docs.filetype = 'folder'
						AND last.tool = '" . TOOL_DOCUMENT . "'
						AND last.visibility = 0";
			$visible_sql .= " AND docs.cc='" . $_course ["code"] . "' ";
			$invisibleresult = api_sql_query ( $invisible_sql, __FILE__, __LINE__ );
			while ( $invisible_folders = Database::fetch_array ( $invisibleresult, "ASSOC" ) ) {
				//get visible folders in the invisible ones -> they are invisible too
				$folder_in_invisible_sql = "SELECT path, title
								FROM  " . $TABLE_ITEMPROPERTY . "  AS last, " . $TABLE_DOCUMENT . "  AS docs
								WHERE docs.id = last.ref
								AND docs.path LIKE '" . Database::escape_string ( $invisible_folders ['path'] ) . "/%'
								AND docs.filetype = 'folder'
								AND last.tool = '" . TOOL_DOCUMENT . "'
								AND last.visibility = 1 ";
				$folder_in_invisible_sql .= " AND docs.cc='" . $_course ["code"] . "' ";
				$folder_in_invisible_sql .= " order by path asc ";
				$folder_in_invisible_result = api_sql_query ( $folder_in_invisible_sql, __FILE__, __LINE__ );
				while ( $folders_in_invisible_folder = Database::fetch_array ( $folder_in_invisible_result, "ASSOC" ) ) {
					$invisiblefolders [$folder_in_invisible_result ['path']] = $folder_in_invisible_result ['title'];
				}
			}
			
			//if both results are arrays -> //calculate the difference between the 2 arrays -> only visible folders are left :)
			if (is_array ( $visiblefolders ) && is_array ( $invisiblefolders )) {
				foreach ( $visiblefolders as $path => $title ) {
					if (! array_key_exists ( $path, $invisiblefolders )) {
						$document_folders [$path] = $title;
					}
				}
				//$document_folders = array_diff($visiblefolders, $invisiblefolders);
				return $document_folders;
			} //only visible folders found
elseif (is_array ( $visiblefolders )) {
				return $visiblefolders;
			} //no visible folders found
else {
				return false;
			}
		}
	}

	/**
	 * This check if a document has the readonly property checked, then see if the user
	 * is the owner of this file, if all this is true then return true.
	 *
	 * @param array  $_course
	 * @param int    $user_id id of the current user
	 * @param string $file path stored in the database
	 * @param int    $document_id in case you dont have the file path ,insert the id of the file here and leave $file in blank ''
	 * @return boolean true/false
	 **/
	public static function check_readonly($_course, $user_id, $file, $document_id = '', $to_delete = false) {
		if (! (! empty ( $document_id ) && is_numeric ( $document_id ))) {
			$document_id = self::get_document_id ( $_course, $file );
		}
		
		$TABLE_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY, $_course ['dbName'] );
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
		
		if ($to_delete) {
			if (self::is_folder ( $_course, $document_id )) {
				if (! empty ( $file )) {
					$path = Database::escape_string ( $file );
					$what_to_check_sql = "SELECT td.id, readonly, tp.insert_user_id FROM " . $TABLE_DOCUMENT . " td , $TABLE_PROPERTY tp
									WHERE tp.ref= td.id and (path='" . $path . "' OR path LIKE BINARY '" . $path . "/%' ) ";
					$what_to_check_sql .= " AND td.cc='" . api_get_course_code () . "' ";
					//get all id's of documents that are deleted
					$what_to_check_result = api_sql_query ( $what_to_check_sql, __FILE__, __LINE__ );
					
					if ($what_to_check_result && Database::num_rows ( $what_to_check_result ) != 0) {
						// file with readonly set to 1 exist?
						$readonly_set = false;
						while ( $row = Database::fetch_array ( $what_to_check_result ) ) {
							//query to delete from item_property table
							//echo $row['id']; echo "<br>";
							if ($row ['readonly'] == 1) {
								if (! ($row ['insert_user_id'] == $user_id)) {
									$readonly_set = true;
									break;
								}
							
							}
						}
						
						if ($readonly_set) {return true;}
					}
				}
				return false;
			}
		}
		
		if (! empty ( $document_id )) {
			$sql = 'SELECT a.insert_user_id, b.readonly FROM ' . $TABLE_PROPERTY . ' a,' . $TABLE_DOCUMENT . ' b
				   WHERE a.ref = b.id and a.ref=' . $document_id . ' LIMIT 1';
			$sql .= " AND b.cc='" . api_get_course_code () . "' ";
			$resultans = api_sql_query ( $sql, __FILE__, __LINE__ );
			$doc_details = Database::fetch_array ( $resultans, 'ASSOC' );
			
			if ($doc_details ['readonly'] == 1) {
				if ($doc_details ['insert_user_id'] == $user_id || api_is_platform_admin ()) {
					return false;
				} else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * This check if a document is a folder or not
	 * @param array  $_course
	 * @param int    $document_id of the item
	 * @return boolean true/false
	 **/
	public static function is_folder($_course, $document_id) {
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
		//if (!empty($document_id))
		$document_id = Database::escape_string ( $document_id );
		$sql = "SELECT filetype FROM " . $TABLE_DOCUMENT . " WHERE id='" . $document_id . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		$resultans = api_sql_query ( $sql, __FILE__, __LINE__ );
		$result = Database::fetch_array ( $resultans, 'ASSOC' );
		if ($result ['filetype'] == 'folder') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This deletes a document by changing visibility to 2, renaming it to filename_DELETED_#id
	 * Files/folders that are inside a deleted folder get visibility 2
	 *
	 * @param array $_course
	 * @param string $path, path stored in the database
	 * @param string ,$base_work_dir, path to the documents folder
	 * @return boolean true/false
	 * @todo now only files/folders in a folder get visibility 2, we should rename them too.
	 */
	function delete_document($_course, $document_id, $base_work_dir) {
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT );
		$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
		$course_code = $_course ['code'];
		
		$dbTable = Database::get_course_table ( TABLE_DOCUMENT );
		$sql = "SELECT comment,title,path,display_order FROM $dbTable WHERE id=" . Database::escape ( $document_id );
		$document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
		$path = $document_info ['path'];
		
		if ($document_info) {
			$remove_from_item_property_sql = "DELETE FROM " . $TABLE_ITEMPROPERTY . " WHERE ref = " . Database::escape ( $document_id ) . " AND tool='" . TOOL_DOCUMENT . "'";
			$remove_from_item_property_sql .= " AND cc='" . $course_code . "' ";
			api_sql_query ( $remove_from_item_property_sql, __FILE__, __LINE__ );
			
			$remove_from_document_sql = "DELETE FROM " . $TABLE_DOCUMENT . " WHERE id = " . Database::escape ( $document_id ) . "";
			$remove_from_document_sql .= " AND cc='" . $course_code . "' ";
			api_sql_query ( $remove_from_document_sql, __FILE__, __LINE__ );
			
			my_delete ( $base_work_dir . $path );
			return true;
		}
		
		return false;
	}

	/**
	 * Gets the id of a document with a given path
	 *
	 * @param array $_course
	 * @param string $path
	 * @return int id of document / false if no doc found
	 */
	function get_document_id($_course, $path) {
		$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
		
		$sql = "SELECT id FROM $TABLE_DOCUMENT WHERE path = '$path'";
		$sql .= " AND cc='" . $_course ["code"] . "' ";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if ($result && mysql_num_rows ( $result ) == 1) {
			$row = mysql_fetch_row ( $result );
			return $row [0];
		} else {
			return false;
		}
	}

	/**
	 * return true if the documentpath have visibility=1 as item_property
	 *
	 * @param string $document_path the relative complete path of the document
	 * @param array  $course the _course array info of the document's course
	 */
	public static function is_visible($doc_path, $course) {
		$docTable = Database::get_course_table ( TABLE_DOCUMENT, $course ['dbName'] );
		$propTable = Database::get_course_table ( TABLE_ITEM_PROPERTY, $course ['dbName'] );
		//note the extra / at the end of doc_path to match every path in the
		// document table that is part of the document path
		$doc_path = Database::escape_string ( $doc_path );
		
		$sql = "SELECT path FROM $docTable d, $propTable ip " . "WHERE d.id=ip.ref AND ip.tool='" . TOOL_DOCUMENT . "' AND d.filetype='file' AND visibility=0 AND " . "locate(concat(path,'/'),'" . $doc_path . "/')=1";
		$sql .= " AND d.cc='" . api_get_course_code () . "' ";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $result ) > 0) {
			$row = Database::fetch_array ( $result );
			//echo "$row[0] not visible";
			return false;
		}
		
		//improved protection of documents viewable directly through the url: incorporates the same protections of the course at the url of documents:	access allowed for the whole world Open, access allowed for users registered on the platform Private access, document accessible only to course members (see the Users list), Completely closed; the document is only accessible to the course admin and teaching assistants.
		if ($_SESSION ['is_allowed_in_course'] or api_is_platform_admin ()) {
			return true; // ok, document is visible
		} else {
			return false;
		}
	}

}
//end class DocumentManager
?>