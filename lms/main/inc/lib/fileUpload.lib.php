<?php

/*
 ==============================================================================

 ==============================================================================
 */

/**
 * Replaces all accentuated characters by non-accentuated characters for filenames, as
 * well as special HTML characters by their HTML entity's first letter.
 *
 * Although this method is not absolute, it gives good results in general. It first
 * transforms the string to HTML entities (&ocirc;, @oslash;, etc) then removes the
 * HTML character part to result in simple characters (o, o, etc).
 * In the case of special characters (out of alphabetical value) like &nbsp; and &lt;,
 * it will still replace them by the first letter of the HTML entity (n, l, ...) but it
 * is still an acceptable method, knowing we're filtering filenames here...
 * @param	string	The accentuated string
 * @return	string	The escaped string, not absolutely correct but satisfying
 */
function replace_accents($string) {
	$string = api_htmlentities ( $string, ENT_QUOTES, SYSTEM_CHARSET );
	$res = preg_replace ( "/&([a-z])[a-z]+;/i", "$1", $string );
	return $res;
}

//------------------------------------------------------------------------------


/**
 * change the file name extension from .php to .phps
 * Useful to secure a site !!
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - the filenam phps'ized
 */

function php2phps($fileName) {
	$upload_extensions_replace_by = api_get_setting ( 'upload_extensions_replace_by' );
	$fileName = eregi_replace ( "\.(php.?|phtml)$", "." . $upload_extensions_replace_by, $fileName );
	$fileName = eregi_replace ( "\.(exe|bat|cmd|sh)$", "." . $upload_extensions_replace_by, $fileName );
	return $fileName;
}

//------------------------------------------------------------------------------


/**
 * Renames .htaccess & .HTACCESS tot htaccess.txt
 *
 * @param string $filename
 * @return string
 */
function htaccess2txt($filename) {
	$filename = str_replace ( '.htaccess', 'htaccess.txt', $filename );
	$filename = str_replace ( '.HTACCESS', 'htaccess.txt', $filename );
	return $filename;
}

//------------------------------------------------------------------------------


/**
 * this function executes our safety precautions
 * more functions can be added
 *
 * @param string $filename
 * @return string
 * @see php2phps()
 * @see htaccess2txt()
 */
function disable_dangerous_file($filename) {
	$filename = php2phps ( $filename );
	$filename = htaccess2txt ( $filename );
	return $filename;
}

//------------------------------------------------------------------------------


/**
 * this function generates a unique name for a file on a given location
 * filenames are changed to name_#.ext
 *
 * @param string $path
 * @param string $name
 * @return new unique name
 */
function unique_name($path, $name) {
	$ext = substr ( strrchr ( $name, "." ), 0 );
	$name_no_ext = substr ( $name, 0, strlen ( $name ) - strlen ( strstr ( $name, $ext ) ) );
	$n = 0;
	$unique = '';
	while ( file_exists ( $path . $name_no_ext . $unique . $ext ) ) {
		$unique = '_' . ++ $n;
	}
	
	return $name_no_ext . $unique . $ext;
}

//------------------------------------------------------------------------------


/**
 * Returns the unique file name
 *
 * @param string $path
 * @param string $ext
 * @return unique file name
 */
function get_file_name($path, $ext) {
	$fileName = md5 ( uniqid ( '' ) ) . '.' . $ext;
	$fileName = unique_name ( $path, $fileName );
	
	return $fileName;
}

//------------------------------------------------------------------------------


/**
 * Returns the file ext
 *
 * @param string $file
 * @return file ext
 */
function get_file_ext($file) {
	$ext = '';
	$pos = strrpos ( $file, '.' );
	if ($pos !== false) {
		$ext = strtolower ( substr ( $file, $pos + 1 ) );
	}
	
	return $ext;
}

//------------------------------------------------------------------------------


/**
 * Returns the unique file name
 *
 * @param string $path
 * @param string $ext
 * @return unique file name
 */
function get_dir_name($path) {
	//$dirName = uniqid('');
	$dirName = get_unique_name ();
	$i = 0;
	$unique = '';
	while ( is_dir ( $path . $dirName . $unique ) ) {
		$unique = '_' . ++ $i;
	}
	$dirName .= $unique;
	
	return $dirName;
}

//------------------------------------------------------------------------------


/**
 * Returns the name without extension, used for the title
 *
 * @param string $name
 * @return name without the extension
 */
function get_document_title0($name) {
	//if they upload .htaccess...
	$name = disable_dangerous_file ( $name );
	$ext = substr ( strrchr ( $name, "." ), 0 );
	$name_no_ext = substr ( $name, 0, strlen ( $name ) - strlen ( strstr ( $name, $ext ) ) );
	$filename = addslashes ( $name_no_ext );
	
	return $filename;
}

/**
 * 转化简体中文为可识别的字符
 * @since ZLMS 1.1.0
 * @param unknown_type $name
 * @return unknown
 */
function get_document_title_v11($name) {
	//if they upload .htaccess...
	$name = disable_dangerous_file ( $name );
	$encoding = api_get_system_encoding ();
	$len = mb_strlen ( $name, $encoding );
	$ext = mb_substr ( mb_strrchr ( $name, ".", FALSE, $encoding ), 0, $len, $encoding );
	$length = $len - mb_strlen ( mb_strstr ( $name, $ext, FALSE, $encoding ) );
	$name_no_ext = mb_substr ( $name, 0, $length, $encoding );
	//$name_no_ext = api_substr($name, 0, api_strlen($name) - api_strlen(api_strstr($name,$ext)));
	$filename = addslashes ( $name_no_ext );
	return @mb_convert_encoding ( $filename, SYSTEM_CHARSET, $encoding );
}

function get_document_title($name, $convert = TRUE) {
	//if they upload .htaccess...
	$name = disable_dangerous_file ( $name );
	if ($convert) {
		$encoding = api_get_system_encoding ();
		$name = api_to_system_encoding ( $name ); //转化成OS编码(Windows为GB2312): 从UTF-8到GB2312
		//api_log($name);
		$ext = api_substr ( api_strrchr ( $name, ".", false, $encoding ), 0 );
		$name_len = api_strlen ( $name, $encoding );
		$name_end = api_strlen ( api_strstr ( $name, $ext, false, $encoding ), $encoding );
		$name_no_ext = api_substr ( $name, 0, $name_len - $name_end, $encoding );
		$filename = addslashes ( $name_no_ext );
		return api_utf8_encode ( $filename, $encoding );
	} else {
		$ext = substr ( strrchr ( $name, "." ), 0 );
		$name_no_ext = substr ( $name, 0, strlen ( $name ) - strlen ( strstr ( $name, $ext ) ) );
		//api_log($name_no_ext);
		$filename = addslashes ( $name_no_ext );
		return $filename;
	}
}

//------------------------------------------------------------------------------


/**
 * 检查上传的一些相关状态
 * @param $uploaded_file
 * @return unknown_type
 */
function pre_check_uploaded_file($uploaded_file) {
	$rtn_msg = "";
	
	//上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值		
	if ($uploaded_file ['error'] == UPLOAD_ERR_INI_SIZE) {
		$rtn_msg = get_lang ( 'UplExceedMaxServerUpload' ) . get_upload_max_filesize ( 0 ) . "M";
	} 

	//上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值		
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_FORM_SIZE) {
		$rtn_msg = (get_lang ( 'UplExceedMaxPostSize' ) . round ( $_POST ['MAX_FILE_SIZE'] / 1024 ) . " KB");
	} 

	//文件只有部分被上传		
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_PARTIAL) {
		$rtn_msg = (get_lang ( 'UplPartialUpload' ) . " " . get_lang ( 'PleaseTryAgain' ));
	} 

	//没有文件被上传。	
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_NO_FILE) {
		$rtn_msg = (get_lang ( 'UplNoFileUploaded' ) . " " . get_lang ( 'UplSelectFileFirst' ));
	} else {
		$rtn_msg = "";
	}
	return $rtn_msg;
}

/**
 * 检查上传的一些相关状态
 * This checks if the upload succeeded
 *
 * @param array $uploaded_file ($_FILES)
 * @return true if upload succeeded
 */
function process_uploaded_file($uploaded_file) {
	//上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
	//0; There is no error, the file uploaded with success.
	//1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
	if ($uploaded_file ['error'] == UPLOAD_ERR_INI_SIZE) {
		api_error_log ( "Upload File Error: " . $uploaded_file ["name"] . ", 超过upload_max_filesize允许最大上传大小" );
		Display::display_error_message ( get_lang ( 'UplExceedMaxServerUpload' ) . get_upload_max_filesize ( 0 ) . "M" ); //server config
		return false;
	} 

	//上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
	//2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
	//not used at the moment, but could be handy if we want to limit the size of an upload (e.g. image upload in html editor).
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_FORM_SIZE) {
		api_error_log ( "Upload File Error: " . $uploaded_file ["name"] . ", 超过MAX_FILE_SIZE允许最大上传大小" );
		Display::display_error_message ( get_lang ( 'UplExceedMaxPostSize' ) . round ( $_POST ['MAX_FILE_SIZE'] / 1024 ) . " KB" );
		return false;
	} 

	//文件只有部分被上传
	//3; The uploaded file was only partially uploaded.
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_PARTIAL) {
		api_error_log ( "Upload File Error: " . $uploaded_file ["name"] . ", 文件只有部分被上传" );
		Display::display_error_message ( get_lang ( 'UplPartialUpload' ) . " " . get_lang ( 'PleaseTryAgain' ) );
		return false;
	} 

	//没有文件被上传。
	//4; No file was uploaded.
	elseif ($uploaded_file ['error'] == UPLOAD_ERR_NO_FILE) {
		api_error_log ( "Upload File Error: " . $uploaded_file ["name"] . ", 没有文件被上传。" );
		Display::display_error_message ( get_lang ( 'UplNoFileUploaded' ) . " " . get_lang ( 'UplSelectFileFirst' ) );
		return false;
	}
	api_error_log ( "Upload File OK: " . $uploaded_file ["name"] );
	return true;
}

//------------------------------------------------------------------------------


/**
 * this function does the save-work for the documents.
 * it handles the uploaded file and adds the properties to the database
 * if unzip=1 and the file is a zipfile, it is extracted
 * if we decide to save ALL kinds of documents in one database,
 * we could extend this with a $type='document', 'scormdocument',...
 *
 * @param array $_course
 * @param array $uploaded_file ($_FILES)
 * @param string $base_work_dir
 * @param string $upload_path
 * @param int $user_id
 * @param int $to_user_id, NULL for everybody
 * @param int $maxFilledSpace
 * @param int $unzip 1/0
 * @param string $what_if_file_exists overwrite, rename or warn if exists (default)
 * @param boolean Optional output parameter. So far only use for unzip_uploaded_document function. If no output wanted on success, set to false.
 * @return path of the saved file
 */
function handle_uploaded_document($_course, $uploaded_file, $base_work_dir, $upload_path, $user_id, $to_user_id = NULL, $unzip = 0, $title = null, $what_if_file_exists = '', $output = true) {
	if (! $user_id) die ( "Not a valid user." );
	$uploaded_file ['name'] = stripslashes ( $uploaded_file ['name'] );
	
	if (api_get_course_code () && api_get_setting ( "upload_max_filesize" )) {
		if ($uploaded_file ['size'] > intval ( api_get_setting ( "upload_max_filesize" ) ) * 1048576) {
			Display::display_error_message ( get_lang ( 'UplExceedMaxServerUpload' ) . intval ( api_get_setting ( "upload_max_filesize" ) ) . "M" );
			return false;
		}
	}
	
	//检查是否有足够空间存储文件
	/*if (! enough_space ( $uploaded_file ['size'], $maxFilledSpace )) {
		Display::display_error_message ( get_lang ( 'UplNotEnoughSpace' ) );
		return false;
	}*/
	//检查checkbox zip解压打上勾时的文件扩展名并处理上传的zip文件.   if the want to unzip, check if the file has a .zip (or ZIP,Zip,ZiP,...) extension
	if ($unzip == 1 && preg_match ( "/.zip$/", strtolower ( $uploaded_file ['name'] ) )) {
		$upload_result = unzip_uploaded_document ( $uploaded_file, $upload_path, $base_work_dir, $output );
		if ($upload_result) {
			Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ), false );
			return true;
		} else {
			return false;
		}
	} elseif ($unzip == 1 && ! preg_match ( "/.zip$/", strtolower ( $uploaded_file ['name'] ) )) { // //如果不是zip文件
		Display::display_error_message ( get_lang ( 'UplNotAZip' ) . " " . get_lang ( 'PleaseTryAgain' ) );
		return false;
	} else { //处理其它上传文件
		//clean up the name and prevent dangerous files remove strange characters
		$clean_name = replace_dangerous_chars ( $uploaded_file ['name'] ); //liyu:20091209: 原来使用replace_dangerous_char(...)
		$clean_name = disable_dangerous_file ( $clean_name );
		$clean_name = replace_accents ( $clean_name );
		//$clean_name=api_to_system_encoding($clean_name);//liyu:$clean_name 此时为GB2312文件
		

		//文件后缀检测
		if (! filter_extension ( $clean_name )) {
			Display::display_error_message ( get_lang ( 'UplUnableToSaveFileFilteredExtension' ) );
			return false;
		} else {
			if ($upload_path != '/') $upload_path = $upload_path . '/';
			$where_to_save = $base_work_dir . $upload_path;
			if (! is_dir ( $where_to_save )) { //目录不存在
				Display::display_error_message ( get_lang ( 'DestDirectoryDoesntExist' ) . ' (' . $where_to_save . ')' );
				return false;
			}
			
			//liyu: 修复文件名BUG
			$extension = get_file_ext ( $uploaded_file ['name'] );
			//$clean_name = get_file_name($where_to_save, $extension);
			$clean_name = get_unique_name () . "." . $extension;
			if (empty ( $title )) {
				$document_name = get_document_title ( $uploaded_file ['name'] );
			} else {
				//$clean_name = disable_dangerous_file(replace_dangerous_char($uploaded_file['name']));
				$document_name = $title;
			}
			
			//存储到DB中的文件相对路径
			$file_path = $upload_path . $clean_name;
			$store_path = $where_to_save . $clean_name; //存储的真实名字
			$file_size = $uploaded_file ['size'];
			
			//$files_perm = api_get_setting('permissions_for_new_files');
			//$files_perm = octdec(!empty($files_perm)?$files_perm:'0770');
			//$files_perm=CHMOD_NORMAL;
			switch ($what_if_file_exists) {
				//overwrite the file if it exists
				case 'overwrite' :
					if (@move_uploaded_file ( $uploaded_file ['tmp_name'], $store_path )) {
						if (file_exists ( $store_path )) {
							chmod ( $store_path, CHMOD_NORMAL );
							$document_id = DocumentManager::get_document_id ( $_course, $file_path );
							if ($document_id) {
								update_existing_document ( $_course, $document_id, $uploaded_file ['size'] );
								api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentUpdated', $user_id, 0, $to_user_id );
							}
							item_property_update_on_folder ( $_course, $upload_path, $user_id );
							if ($output) {
								Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ) . " " . $file_path . ' ' . get_lang ( 'UplFileOverwritten' ), false );
							}
							return $file_path;
						} else {
							$document_id = add_document ( $_course, $file_path, 'file', $file_size, $document_name );
							if ($document_id) {
								api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $user_id, 0, $to_user_id );
							}
							//if the file is in a folder, we need to update all parent folders
							item_property_update_on_folder ( $_course, $upload_path, $user_id );
							Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ) . " " . $file_path, false );
							return $file_path;
						}
					} else {
						Display::display_error_message ( get_lang ( 'UplUnableToSaveFile' ) );
						return false;
					}
					break;
				
				//rename the file if it exists
				case 'rename' :
					$new_name = unique_name ( $where_to_save, $clean_name );
					$store_path = $where_to_save . $new_name;
					$new_file_path = $upload_path . $new_name;
					
					if (@move_uploaded_file ( $uploaded_file ['tmp_name'], $store_path )) {
						chmod ( $store_path, CHMOD_NORMAL );
						$document_id = add_document ( $_course, $new_file_path, 'file', $file_size, $document_name );
						if ($document_id) api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $user_id, 0, $to_user_id );
						item_property_update_on_folder ( $_course, $upload_path, $user_id );
						if ($output) {
							Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ) . " " . get_lang ( 'UplFileSavedAs' ) . $new_file_path, false );
						}
						return $new_file_path;
					} else {
						Display::display_error_message ( get_lang ( 'UplUnableToSaveFile' ) );
						return false;
					}
					break;
				
				//only save the file if it doesn't exist or warn user if it does exist
				default :
					if (file_exists ( $store_path )) {
						Display::display_error_message ( $clean_name . ' ' . get_lang ( 'UplAlreadyExists' ) );
					} else {
						if (@move_uploaded_file ( $uploaded_file ['tmp_name'], $store_path )) {
							chmod ( $store_path, CHMOD_NORMAL );
							$document_id = add_document ( $_course, $file_path, 'file', $file_size, $document_name );
							if ($document_id) {
								api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $user_id, 0, $to_user_id );
							}
							item_property_update_on_folder ( $_course, $upload_path, $user_id );
							if ($output) {
								Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ) . "," . get_lang ( 'FileStoreName' ) . ":<br/>" . $file_path, false );
							}
							return $file_path;
						} else {
							Display::display_error_message ( get_lang ( 'UplUnableToSaveFile' ) );
							return false;
						}
					}
					break;
			}
		}
	}
}

//------------------------------------------------------------------------------


/**
 * Check if there is enough place to add a file on a directory
 * on the base of a maximum directory size allowed
 * @deprecated use enough_space instead!
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileSize (int) - size of the file in byte
 * @param  - dir (string) - Path of the directory
 * whe the file should be added
 * @param  - maxDirSpace (int) - maximum size of the diretory in byte
 * @return - boolean true if there is enough space,
 * boolean false otherwise
 *
 * @see    - enough_size() uses  dir_total_space() function
 */

function enough_size($fileSize, $dir, $maxDirSpace) {
	if ($maxDirSpace) {
		$alreadyFilledSpace = dir_total_space ( $dir );
		if (($fileSize + $alreadyFilledSpace) > $maxDirSpace) {
			return false;
		}
	}
	
	return true;
}

//------------------------------------------------------------------------------


/**
 * Check if there is enough place to add a file on a directory
 * on the base of a maximum directory size allowed
 *
 * @author Bert Vanderkimpen
 * @param  int file_size size of the file in byte
 * @param array $_course
 * @param  int max_dir_space maximum size
 * @return boolean true if there is enough space, false otherwise
 *
 * @see enough_space() uses  documents_total_space() function
 */

function enough_space($file_size, $max_dir_space) {
	if ($max_dir_space) {
		$already_filled_space = documents_total_space ();
		if (($file_size + $already_filled_space) > $max_dir_space) {
			return false;
		}
	}
	
	return true;
}

//------------------------------------------------------------------------------


/**
 * Compute the size already occupied by a directory and is subdirectories
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - dirPath (string) - size of the file in byte
 * @return - int - return the directory size in bytes
 */

function dir_total_space($dirPath) {
	$save_dir = getcwd ();
	chdir ( $dirPath );
	$handle = opendir ( $dirPath );
	
	while ( $element = readdir ( $handle ) ) {
		if ($element == "." || $element == "..") {
			continue; // skip the current and parent directories
		}
		if (is_file ( $element )) {
			$sumSize += filesize ( $element );
		}
		if (is_dir ( $element )) {
			$dirList [] = $dirPath . "/" . $element;
		}
	}
	
	closedir ( $handle );
	
	if (sizeof ( $dirList ) > 0) {
		foreach ( $dirList as $j ) {
			$sizeDir = dir_total_space ( $j ); // recursivity
			$sumSize += $sizeDir;
		}
	}
	chdir ( $save_dir ); //return to initial position
	return $sumSize;
}

//------------------------------------------------------------------------------


/**
 * Calculate the total size of all documents in a course
 *
 * @author Bert vanderkimpen
 * @return int total size
 */

function documents_total_space() {
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$TABLE_DOCUMENT = Database::get_course_table ( TABLE_DOCUMENT );
	
	$sql = "SELECT SUM(size)
	FROM  " . $TABLE_ITEMPROPERTY . "  AS props, " . $TABLE_DOCUMENT . "  AS docs
	WHERE docs.id = props.ref
	AND props.tool = '" . TOOL_DOCUMENT . "'
	AND props.visibility <> 2";
	
	$document_total_size = Database::get_scalar_value ( $sql );
	
	return $document_total_size;
}

//------------------------------------------------------------------------------


/**
 * Try to add an extension to files without extension
 * Some applications on Macintosh computers don't add an extension to the files.
 * This subroutine try to fix this on the basis of the MIME type sent
 * by the browser.
 *
 * Note : some browsers don't send the MIME Type (e.g. Netscape 4).
 * We don't have solution for this kind of situation
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author - Bert Vanderkimpen
 * @param  - fileName (string) - Name of the file
 * @param  - fileType (string) - Type of the file
 * @return - fileName (string)
 *
 */

function add_ext_on_mime($fileName, $fileType) {
	//Check if the file has an extension AND if the browser has sent a MIME Type
	if (! ereg ( "([[:alnum:]]|[[[:punct:]])+\.[[:alnum:]]+$", $fileName ) && $fileType) {
		//Build a "MIME-types / extensions" connection table
		static $mimeType = array ();
		
		$mimeType [] = "application/msword";
		$extension [] = ".doc";
		$mimeType [] = "application/rtf";
		$extension [] = ".rtf";
		$mimeType [] = "application/vnd.ms-powerpoint";
		$extension [] = ".ppt";
		$mimeType [] = "application/vnd.ms-excel";
		$extension [] = ".xls";
		$mimeType [] = "application/pdf";
		$extension [] = ".pdf";
		$mimeType [] = "application/postscript";
		$extension [] = ".ps";
		$mimeType [] = "application/mac-binhex40";
		$extension [] = ".hqx";
		$mimeType [] = "application/x-gzip";
		$extension [] = "tar.gz";
		$mimeType [] = "application/x-shockwave-flash";
		$extension [] = ".swf";
		$mimeType [] = "application/x-stuffit";
		$extension [] = ".sit";
		$mimeType [] = "application/x-tar";
		$extension [] = ".tar";
		$mimeType [] = "application/zip";
		$extension [] = ".zip";
		$mimeType [] = "application/x-tar";
		$extension [] = ".tar";
		$mimeType [] = "text/html";
		$extension [] = ".htm";
		$mimeType [] = "text/plain";
		$extension [] = ".txt";
		$mimeType [] = "text/rtf";
		$extension [] = ".rtf";
		$mimeType [] = "image/gif";
		$extension [] = ".gif";
		$mimeType [] = "image/jpeg";
		$extension [] = ".jpg";
		$mimeType [] = "image/pjpeg";
		$extension [] = ".jpg";
		$mimeType [] = "image/png";
		$extension [] = ".png";
		$mimeType [] = "audio/midi";
		$extension [] = ".mid";
		$mimeType [] = "audio/mpeg";
		$extension [] = ".mp3";
		$mimeType [] = "audio/x-aiff";
		$extension [] = ".aif";
		$mimeType [] = "audio/x-pn-realaudio";
		$extension [] = ".rm";
		$mimeType [] = "audio/x-pn-realaudio-plugin";
		$extension [] = ".rpm";
		$mimeType [] = "audio/x-wav";
		$extension [] = ".wav";
		$mimeType [] = "video/mpeg";
		$extension [] = ".mpg";
		$mimeType [] = "video/quicktime";
		$extension [] = ".mov";
		$mimeType [] = "video/x-msvideo";
		$extension [] = ".avi";
		//test on PC (files with no extension get application/octet-stream)
		//$mimeType[] = "application/octet-stream";      $extension[] =".ext";
		

		$mimeType [] = "video/x-ms-wmv";
		$extension [] = ".wmv";
		$mimeType [] = "video/x-flv";
		$extension [] = ".flv";
		
		$mimeType [] = "application/vnd.ms-word.document.macroEnabled.12";
		$extension [] = ".docm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		$extension [] = ".docx";
		$mimeType [] = "application/vnd.ms-word.template.macroEnabled.12";
		$extension [] = ".dotm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
		$extension [] = ".dotx";
		$mimeType [] = "application/vnd.ms-powerpoint.template.macroEnabled.12";
		$extension [] = ".potm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.presentationml.template";
		$extension [] = ".potx";
		$mimeType [] = "application/vnd.ms-powerpoint.addin.macroEnabled.12";
		$extension [] = ".ppam";
		$mimeType [] = "application/vnd.ms-powerpoint.slideshow.macroEnabled.12";
		$extension [] = ".ppsm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
		$extension [] = ".ppsx";
		$mimeType [] = "application/vnd.ms-powerpoint.presentation.macroEnabled.12";
		$extension [] = ".pptm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
		$extension [] = ".pptx";
		$mimeType [] = "application/vnd.ms-excel.addin.macroEnabled.12";
		$extension [] = ".xlam";
		$mimeType [] = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
		$extension [] = ".xlsb";
		$mimeType [] = "application/vnd.ms-excel.sheet.macroEnabled.12";
		$extension [] = ".xlsm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		$extension [] = ".xlsx";
		$mimeType [] = "application/vnd.ms-excel.template.macroEnabled.12";
		$extension [] = ".xltm";
		$mimeType [] = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
		$extension [] = ".xltx";
		
		/*
		 * Check if the MIME type sent by the browser is in the table
		 */
		
		foreach ( $mimeType as $key => $type ) {
			if ($type == $fileType) {
				$fileName .= $extension [$key];
				break;
			}
		}
		
		unset ( $mimeType, $extension, $type, $key ); // Delete to eschew possible collisions
	}
	
	return $fileName;
}

//------------------------------------------------------------------------------


/**
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param  array $uploadedFile - follows the $_FILES Structure
 * @param  string $baseWorkDir - base working directory of the module
 * @param  string $uploadPath  - destination of the upload.
 * This path is to append to $baseWorkDir
 * @param  int $maxFilledSpace - amount of bytes to not exceed in the base
 * working directory
 *
 * @return boolean true if it succeds, false otherwise
 */
function treat_uploaded_file($uploadedFile, $baseWorkDir, $uploadPath, $maxFilledSpace, $uncompress = '') {
	$uploadedFile ['name'] = stripslashes ( $uploadedFile ['name'] );
	
	if (! enough_size ( $uploadedFile ['size'], $baseWorkDir, $maxFilledSpace )) {
		return api_failure::set_failure ( 'not_enough_space' );
	}
	
	if ($uncompress == 'unzip' && preg_match ( "/.zip$/", strtolower ( $uploadedFile ['name'] ) )) {
		return unzip_uploaded_file ( $uploadedFile, $uploadPath, $baseWorkDir, $maxFilledSpace );
	} else {
		$fileName = trim ( $uploadedFile ['name'] );
		
		// CHECK FOR NO DESIRED CHARACTERS
		$fileName = replace_dangerous_char ( $fileName );
		
		// TRY TO ADD AN EXTENSION TO FILES WITOUT EXTENSION
		$fileName = add_ext_on_mime ( $fileName, $uploadedFile ['type'] );
		
		// HANDLE PHP FILES
		$fileName = php2phps ( $fileName );
		
		// COPY THE FILE TO THE DESIRED DESTINATION
		if (move_uploaded_file ( $uploadedFile ['tmp_name'], $baseWorkDir . $uploadPath . "/" . $fileName )) {
		}
		
		return true;
	}
}

/**
 * Manages all the unzipping process of an uploaded file
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param  array  $uploadedFile - follows the $_FILES Structure
 * @param  string $uploadPath   - destination of the upload.
 * This path is to append to $baseWorkDir
 * @param  string $baseWorkDir  - base working directory of the module
 * @param  int $maxFilledSpace  - amount of bytes to not exceed in the base
 * working directory
 *
 * @return boolean true if it succeeds false otherwise
 */
function unzip_uploaded_file($uploadedFile, $uploadPath, $baseWorkDir, $maxFilledSpace) {
	$zipFile = new pclZip ( $uploadedFile ['tmp_name'] );
	$zipContentArray = $zipFile->listContent ();
	$okScorm = false;
	foreach ( $zipContentArray as $thisContent ) {
		if (preg_match ( '~.(php.*|phtml)$~i', $thisContent ['filename'] )) {
			return api_failure::set_failure ( 'php_file_in_zip_file' );
		} elseif (stristr ( $thisContent ['filename'], 'imsmanifest.xml' )) {
			$okScorm = true;
		} elseif (stristr ( $thisContent ['filename'], 'LMS' )) {
			$okPlantynScorm1 = true;
		} elseif (stristr ( $thisContent ['filename'], 'REF' )) {
			$okPlantynScorm2 = true;
		} elseif (stristr ( $thisContent ['filename'], 'SCO' )) {
			$okPlantynScorm3 = true;
		} elseif (stristr ( $thisContent ['filename'], 'AICC' )) {
			$okAiccScorm = true;
		}
		
		$realFileSize += $thisContent ['size'];
	}
	
	if ((($okPlantynScorm1 == true) and ($okPlantynScorm2 == true) and ($okPlantynScorm3 == true)) or ($okAiccScorm == true)) {
		$okScorm = true;
	}
	
	if (! $okScorm && defined ( 'CHECK_FOR_SCORM' ) && CHECK_FOR_SCORM) {
		return api_failure::set_failure ( 'not_scorm_content' );
	}
	
	if (! enough_size ( $realFileSize, $baseWorkDir, $maxFilledSpace )) {
		return api_failure::set_failure ( 'not_enough_space' );
	}
	
	// it happens on Linux that $uploadPath sometimes doesn't start with '/'
	if ($uploadPath [0] != '/') {
		$uploadPath = '/' . $uploadPath;
	}
	
	if ($uploadPath [strlen ( $uploadPath ) - 1] == '/') {
		$uploadPath = substr ( $uploadPath, 0, - 1 );
	}
	
	/*
	 --------------------------------------
		Uncompressing phase
		--------------------------------------
		*/
	/*
		The first version, using OS unzip, is not used anymore
		because it does not return enough information.
		We need to process each individual file in the zip archive to
		- add it to the database
		- parse & change relative html links
		*/
	if (PHP_OS == 'Linux' && ! get_cfg_var ( 'safe_mode' ) && false) {
		// Shell Method - if this is possible, it gains some speed
		exec ( "unzip -d \"" . $baseWorkDir . $uploadPath . "/\"" . $uploadedFile ['name'] . " " . $uploadedFile ['tmp_name'] );
	} else {
		$save_dir = getcwd ();
		chdir ( $baseWorkDir . $uploadPath );
		$unzippingState = $zipFile->extract ();
		for($j = 0; $j < count ( $unzippingState ); $j ++) {
			$state = $unzippingState [$j];
			//fix relative links in html files
			$extension = strrchr ( $state ["stored_filename"], "." );
		}
		
		if ($dir = @opendir ( $baseWorkDir . $uploadPath )) {
			while ( $file = readdir ( $dir ) ) {
				if ($file != '.' && $file != '..') {
					$filetype = "file";
					if (is_dir ( $baseWorkDir . $uploadPath . '/' . $file )) $filetype = "folder";
					$safe_file = replace_dangerous_char ( $file, 'strict' );
					@rename ( $baseWorkDir . $uploadPath . '/' . $file, $baseWorkDir . $uploadPath . '/' . $safe_file );
				}
			}
			closedir ( $dir );
		}
		chdir ( $save_dir ); //back to previous dir position
	}
	
	return true;
}

//------------------------------------------------------------------------------


/**
 * Manages all the unzipping process of an uploaded document
 * This uses the item_property table for properties of documents
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Bert Vanderkimpen
 *
 * @param  array  $uploadedFile - follows the $_FILES Structure
 * @param  string $uploadPath   - destination of the upload.
 * This path is to append to $baseWorkDir
 * @param  string $baseWorkDir  - base working directory of the module
 * @param  int $maxFilledSpace  - amount of bytes to not exceed in the base
 * working directory
 * @param		boolean	Output switch. Optional. If no output not wanted on success, set to false.
 *
 * @return boolean true if it succeeds false otherwise
 */

function unzip_uploaded_document($uploaded_file, $upload_path, $base_work_dir, $max_filled_space, $output = true) {
	global $_course;
	global $_user;
	global $to_user_id;
	
	$zip_file = new pclZip ( $uploaded_file ['tmp_name'] );
	$zip_content_array = $zip_file->listContent ();
	$folder_count = $file_count = 0;
	
	//计算总大小
	foreach ( ( array ) $zip_content_array as $this_content ) {
		$real_filesize += $this_content ['size'];
		
		//计算解压后包根目录中的文件或目录总数
		if (strpos ( $this_content ['filename'], "/" ) == FALSE && $this_content ['folder'] == FALSE) $file_count ++;
		if ($this_content ['folder'] && substr_count ( $this_content ['filename'], "/" ) == 1 && stripos ( $this_content ['filename'], "/" ) == strlen ( $this_content ['filename'] ) - 1) $folder_count ++;
	}
	
	if (! enough_space ( $real_filesize, $max_filled_space )) {
		Display::display_error_message ( get_lang ( 'UplNotEnoughSpace' ) );
		return false;
	}
	
	// it happens on Linux that $uploadPath sometimes doesn't start with '/'
	if ($upload_path [0] != '/') {
		$upload_path = '/' . $upload_path;
	}
	/*
	 --------------------------------------
		Uncompressing phase
		--------------------------------------
		*/
	//get into the right directory
	$save_dir = getcwd (); // D:\ZLMS\htdocs\zlms\main\document
	$new_dir_name = get_unique_name ();
	if ($file_count > 1 or $folder_count > 1) {
		if (stripos ( $upload_path, "/" ) != strlen ( $upload_path ) - 1) $upload_path .= "/";
		$upload_path .= $new_dir_name;
		if (! file_exists ( $base_work_dir . $upload_path )) mkdir ( $base_work_dir . $upload_path );
		$document_id = add_document ( $_course, $upload_path, 'folder', 0, get_lang ( 'NewFolder' ) );
		api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', api_get_user_id () );
	}
	chdir ( $base_work_dir . $upload_path );
	//we extract using a callback function that "cleans" the path
	$unzipping_state = $zip_file->extract ( PCLZIP_CB_PRE_EXTRACT, 'clean_up_files_in_zip' );
	// Add all documents in the unzipped folder to the database
	add_all_documents_in_folder_to_database ( $_course, $_user ['user_id'], $base_work_dir, $upload_path == '/' ? '' : $upload_path );
	
	return true;
}

//------------------------------------------------------------------------------


/**
 * this function is a callback function that is used while extracting a zipfile
 * http://www.phpconcept.net/pclzip/man/en/index.php?options-pclzip_cb_pre_extract
 *
 * @param $p_event
 * @param $p_header
 * @return 1 (If the function returns 1, then the extraction is resumed)
 */
function clean_up_files_in_zip($p_event, &$p_header) {
	clean_up_path ( $p_header ['filename'] );
	return 1;
}

//------------------------------------------------------------------------------


/**
 * this function cleans up a given path
 * by eliminating dangerous file names and cleaning them
 *
 * @param string $path
 * @return $path
 * @see disable_dangerous_file()
 * @see replace_dangerous_char()
 */
function clean_up_path(&$path) {
	//split the path in folders and files
	$path_array = explode ( '/', $path );
	//clean up every foler and filename in the path
	foreach ( $path_array as $key => $val ) {
		//we don't want to lose the dots in ././folder/file (cfr. zipfile)
		if ($path_array [$key] != '.') $path_array [$key] = disable_dangerous_file ( replace_dangerous_char ( $val ) );
	}
	//join the "cleaned" path
	$path = implode ( '/', $path_array );
	return $path;
}

//------------------------------------------------------------------------------


/**
 * Adds a new document to the database
 *
 * @param array $_course
 * @param string $path
 * @param string $filetype
 * @param int $filesize
 * @param string $title
 * @return id if inserted document
 */
function add_document($_course, $path, $filetype, $filesize, $title, $comment = NULL) {
	$table_document = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY, $_course ['dbName'] );
	
	$file_path = $path;
	//liyu: 20091208
	$path_parts = pathinfo ( $path );
	$path = $path_parts ['dirname'];
	$path = str_replace ( '_', '\_', $path );
	$visibility_bit = ' <> 2';
	$added_slash = ($path == '/') ? '' : '/';
	$sql = "SELECT MAX(display_order) FROM  " . $TABLE_ITEMPROPERTY . "  AS last, " . $table_document . "  AS docs
			WHERE docs.id = last.ref	AND docs.path LIKE '" . $path . $added_slash . "%' 
			AND docs.path NOT LIKE '" . $path . $added_slash . "%/%'	AND last.tool = '" . TOOL_DOCUMENT . "'  
			AND last.visibility" . $visibility_bit;
	$sql .= " AND docs.cc='" . $_course ["code"] . "' ";
	$max_order = Database::get_scalar_value ( $sql );
	$max_order ++;
	
	$sql_data = array ('path' => $file_path, 'filetype' => $filetype, 'size' => $filesize, 'title' => $title, 'comment' => $comment, 'display_order' => $max_order );
	$sql_data ['cc'] = $_course ['code'];
	$sql = Database::sql_insert ( $table_document, $sql_data );
	if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
		return Database::get_last_insert_id ();
	} else {
		return false;
	}
}

/**
 * Check if the file is dangerous, based on extension and/or mimetype.
 * The list of extensions accepted/rejected can be found from
 * api_get_setting('upload_extensions_exclude') and api_get_setting('upload_extensions_include')
 * @param	string 	filename passed by reference. The filename will be modified if filter rules say so! (you can include path but the filename should look like 'abc.html')
 * @return	int		0 to skip file, 1 to keep file
 */
function filter_extension(&$filename) {
	if (substr ( $filename, - 1 ) == '/') {
		return 1;
	} //authorize directories
	$blacklist = api_get_setting ( 'upload_extensions_list_type' );
	if ($blacklist != 'whitelist') {
		$extensions = split ( ';', strtolower ( api_get_setting ( 'upload_extensions_blacklist' ) ) );
		
		$skip = api_get_setting ( 'upload_extensions_skip' );
		$ext = strrchr ( $filename, "." );
		$ext = substr ( $ext, 1 );
		if (empty ( $ext )) {
			return 1;
		}
		if (in_array ( strtolower ( $ext ), $extensions )) {
			if ($skip == 'true') { //移除
				return 0;
			} else { //重命名
				$new_ext = api_get_setting ( 'upload_extensions_replace_by' );
				$filename = str_replace ( "." . $ext, "." . $new_ext, $filename );
				return 1;
			}
		} else {
			return 1;
		}
	} else {
		$extensions = split ( ';', strtolower ( api_get_setting ( 'upload_extensions_whitelist' ) ) );
		$skip = api_get_setting ( 'upload_extensions_skip' );
		$ext = strrchr ( $filename, "." );
		$ext = substr ( $ext, 1 );
		if (empty ( $ext )) {
			return 1;
		} //accept empty extensions
		if (! in_array ( strtolower ( $ext ), $extensions )) {
			if ($skip == 'true') { //上传文件过滤的操作类型:移除
				return 0;
			} else { //重命名
				$new_ext = api_get_setting ( 'upload_extensions_replace_by' );
				$filename = str_replace ( "." . $ext, "." . $new_ext, $filename );
				return 1;
			}
		} else {
			return 1;
		}
	}
}

/**
 * Update an existing document in the database
 * as the file exists, we only need to change the size
 *
 * @param array $_course
 * @param int $document_id
 * @param int $filesize
 * @return boolean true /false
 */
function update_existing_document($_course, $document_id, $filesize, $display_order = 0) {
	$document_table = Database::get_course_table ( TABLE_DOCUMENT, $_course ['dbName'] );
	$sql = "UPDATE $document_table SET `size`='" . $filesize . "',display_order='" . $display_order . "' WHERE id='$document_id'";
	if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
		return true;
	} else {
		return false;
	}
}

/**
 * this function updates the last_edit_date, last edit user id on all folders in a given path
 *
 * @param array $_course
 * @param string $path
 * @param int $user_id
 */
function item_property_update_on_folder($_course, $path, $user_id) {
	//display_message("Start update_lastedit_on_folder");
	//if we are in the root, just return... no need to update anything
	if ($path == '/') return;
	
	//if the given path ends with a / we remove it
	$endchar = substr ( $path, strlen ( $path ) - 1, 1 );
	if ($endchar == '/') $path = substr ( $path, 0, strlen ( $path ) - 1 );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY, $_course ['dbName'] );
	
	//get the time
	$time = date ( "Y-m-d H:i:s", time () );
	
	//get all paths in the given path
	// /folder/subfolder/subsubfolder/file
	// if file is updated, subsubfolder, subfolder and folder are updated
	

	$exploded_path = explode ( '/', $path );
	
	foreach ( $exploded_path as $key => $value ) {
		//we don't want a slash before our first slash
		if ($key != 0) {
			$newpath .= "/" . $value;
			
			//echo "path= ".$newpath."<br>";
			//select ID of given folder
			$folder_id = DocumentManager::get_document_id ( $_course, $newpath );
			
			if ($folder_id) {
				$sql = "UPDATE $TABLE_ITEMPROPERTY SET `lastedit_date`='$time',`lastedit_type`='DocumentInFolderUpdated', `lastedit_user_id`='$user_id' WHERE tool='" . TOOL_DOCUMENT . "' AND ref='$folder_id'";
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
		}
	}
}

//------------------------------------------------------------------------------


/**
 * Returns the directory depth of the file.
 *
 * @author	Olivier Cauberghe <olivier.cauberghe@ugent.be>
 * @param	path+filename eg: /main/document/document.php
 * @return	The directory depth
 */
function get_levels($filename) {
	$levels = explode ( "/", $filename );
	if (empty ( $levels [count ( $levels ) - 1] )) unset ( $levels [count ( $levels ) - 1] );
	return count ( $levels );
}

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------


/**
 * retrieve the image path list in a html file
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $htmlFile
 * @return array -  images path list
 */
function search_img_from_html($htmlFile) {
	$imgFilePath = array ();
	$fp = fopen ( $htmlFile, "r" ) or die ( '<center>can not open file</center>' );
	// search and store occurences of the IMG tag in an array
	$buffer = fread ( $fp, filesize ( $htmlFile ) ) or die ( '<center>can not read file</center>' );
	$matches = array ();
	if (preg_match_all ( '~<[[:space:]]*img[^>]*>~i', $buffer, $matches )) {
		$imgTagList = $matches [0];
	}
	fclose ( $fp );
	unset ( $buffer );
	
	// Search the image file path from all the IMG tag detected
	if (sizeof ( $imgTagList ) > 0) {
		foreach ( $imgTagList as $thisImgTag ) {
			if (preg_match ( '~src[[:space:]]*=[[:space:]]*[\"]{1}([^\"]+)[\"]{1}~i', $thisImgTag, $matches )) {
				$imgPathList [] = $matches [1];
			}
		}
		$imgPathList = array_unique ( $imgPathList ); // remove duplicate entries
	}
	
	return $imgPathList;
}

//------------------------------------------------------------------------------


/**
 * creates a new directory trying to find a directory name
 * that doesn't already exist
 * (we could use unique_name() here...)
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Bert Vanderkimpen
 * @param array $_course 当前课程
 * @param int $user_id 当前用户
 * @param $desired_dir_name 要创建的目录名称
 * @param string $desiredDirName complete path of the desired name
 * @return string actual directory name if it succeeds,
 * boolean false otherwise
 */

function create_unexisting_directory($_course, $user_id, $to_user_id, $base_work_dir, $cur_path, $desired_dir_name, $title = null, $comment = NULL) {
	if ($title == null) {
		$title = basename ( $desired_dir_name );
		$desired_dir_name = $cur_path . ($cur_path == '/' ? '' : '/') . get_dir_name ( $base_work_dir . $cur_path );
	} else {
		$i = 0;
		$unique = '';
		while ( file_exists ( $base_work_dir . $cur_path . ($cur_path == '/' ? '' : '/') . $desired_dir_name . $unique ) ) {
			$unique = '_' . ++ $i;
		}
		$desired_dir_name = $cur_path . ($cur_path == '/' ? '' : '/') . $desired_dir_name . $unique;
	}
	
	if (mkdir ( $base_work_dir . $desired_dir_name, CHMOD_NORMAL )) {
		$document_id = add_document ( $_course, $desired_dir_name, 'folder', 0, $title, $comment );
		if ($document_id) {
			api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'FolderCreated', $user_id, 0, $to_user_id );
			return $desired_dir_name;
		}
	} else {
		return false;
	}
}

//------------------------------------------------------------------------------


/**
 * Handles uploaded missing images
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Bert Vanderkimpen
 * @param array $_course
 * @param array $uploaded_file_collection - follows the $_FILES Structure
 * @param string $base_work_dir
 * @param string $missing_files_dir
 * @param int $user_id
 * @param int $max_filled_space
 */

function move_uploaded_file_collection_into_directory($_course, $uploaded_file_collection, $base_work_dir, $missing_files_dir, $user_id, $to_user_id, $max_filled_space) {
	$number_of_uploaded_images = count ( $uploaded_file_collection ['name'] );
	for($i = 0; $i < $number_of_uploaded_images; $i ++) {
		$missing_file ['name'] = $uploaded_file_collection ['name'] [$i];
		$missing_file ['type'] = $uploaded_file_collection ['type'] [$i];
		$missing_file ['tmp_name'] = $uploaded_file_collection ['tmp_name'] [$i];
		$missing_file ['error'] = $uploaded_file_collection ['error'] [$i];
		$missing_file ['size'] = $uploaded_file_collection ['size'] [$i];
		
		$upload_ok = process_uploaded_file ( $missing_file );
		if ($upload_ok) {
			$new_file_list [] = handle_uploaded_document ( $_course, $missing_file, $base_work_dir, $missing_files_dir, $user_id, $to_user_id, $max_filled_space, 0, null, 'overwrite' );
		}
		unset ( $missing_file );
	}
	
	return $new_file_list;
}

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------


/*
 * Open the old html file and replace the src path into the img tag
 * This also works for files in subdirectories.
 * @param $originalImgPath is an array
 * @param $newImgPath is an array
 */
function replace_img_path_in_html_file($originalImgPath, $newImgPath, $htmlFile) {
	global $_course;
	
	/*
	 * Open the file
	 */
	$fp = fopen ( $htmlFile, "r" );
	$buffer = fread ( $fp, filesize ( $htmlFile ) );
	
	/*
	 * Fix the image tags
	 */
	for($i = 0, $fileNb = count ( $originalImgPath ); $i < $fileNb; $i ++) {
		$replace_what = $originalImgPath [$i];
		/*
		 we only need the directory and the filename
		 /path/to/file_html_files/missing_file.gif -> file_html_files/missing_file.gif
		 */
		$exploded_file_path = explode ( '/', $newImgPath [$i] );
		$replace_by = $exploded_file_path [count ( $exploded_file_path ) - 2] . '/' . $exploded_file_path [count ( $exploded_file_path ) - 1];
		//$message .= "Element [$i] <b>" . $replace_what . "</b> replaced by <b>" . $replace_by . "</b><br>"; //debug
		//api_display_debug_info($message);
		

		$buffer = str_replace ( $replace_what, $replace_by, $buffer );
	}
	
	$new_html_content .= $buffer;
	
	fclose ( $fp ) or die ( '<center>cannot close file</center>' );
	
	/*
	 * Write the resulted new file
	 */
	$fp = fopen ( $htmlFile, 'w' ) or die ( '<center>cannot open file</center>' );
	fwrite ( $fp, $new_html_content ) or die ( '<center>cannot write in file</center>' );
}

//------------------------------------------------------------------------------


/**
 * Creates a file containing an html redirection to a given url
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $filePath
 * @param string $url
 * @return void
 */

function create_link_file($filePath, $url) {
	$fileContent = '<html>' . '<head>' . '<meta http-equiv="refresh" content="1;url=' . $url . '">' . '</head>' . '<body>' . '</body>' . '</html>';
	$fp = fopen ( $filePath, 'w' ) or die ( 'can not create file' );
	fwrite ( $fp, $fileContent );
}

//------------------------------------------------------------------------------


/**
 Open html file $full_file_name;
 Parse the hyperlinks; and
 Write the result back in the html file.

 @author Roan Embrechts
 @version 0.1
 */
function api_replace_links_in_html($upload_path, $full_file_name) {
	$fp = fopen ( $full_file_name, "r" );
	$buffer = fread ( $fp, filesize ( $full_file_name ) );
	$new_html_content = api_replace_links_in_string ( $upload_path, $buffer );
	$fp = fopen ( $full_file_name, "w" );
	fwrite ( $fp, $new_html_content );
}

//------------------------------------------------------------------------------


/**
@deprecated, use api_replace_parameter instead

Parse the buffer string provided as parameter
Replace the a href tags so they are displayed correctly.
- works for files in root and subdirectories
- replace relative hyperlinks to use showinframes.php?file= ...
- add target="_top" to all absolute hyperlinks
- leave local anchors untouched (e.g. #CHAPTER1)
- leave links with download.php and showinframes.php untouched

@author Roan Embrechts
@version 0.6
 */
function api_replace_links_in_string($upload_path, $buffer) {
	// Search for hyperlinks
	$matches = array ();
	if (preg_match_all ( "/<a[\s]*href[^<]*>/i", $buffer, $matches )) {
		$tag_list = $matches [0];
	}
	
	// Search the filepath of all detected <a href> tags
	if (sizeof ( $tag_list ) > 0) {
		$file_path_list = array ();
		$href_list = array ();
		
		foreach ( $tag_list as $this_tag ) {
			/* Match case insensitive, the stuff between the two ~ :
				a href = <exactly one quote><one or more non-quotes><exactly one ">
				e.g. a href="www.google.be", A HREF =   "info.html"
				to match ["] escape the " or else PHP interprets it
				[\"]{1} --> matches exactly one "
				+	1 or more (like * is 0 or more)
				[\s]* matches whitespace
				$matches contains captured subpatterns
				the only one here is ([^\"]+) --> matches[1]
				*/
			if (preg_match ( "~a href[\s]*=[\s]*[\"]{1}([^\"]+)[\"]{1}~i", $this_tag, $matches )) {
				$file_path_list [] = $matches [1]; //older
				$href_list [] = $matches [0]; //to also add target="_top"
			}
		}
	}
	
	// replace the original hyperlinks
	// by the correct ones
	for($count = 0; $count < sizeof ( $href_list ); $count ++) {
		$replaceWhat [$count] = $href_list [$count];
		
		$is_absolute_hyperlink = strpos ( $replaceWhat [$count], "http" );
		$is_local_anchor = strpos ( $replaceWhat [$count], "#" );
		if ($is_absolute_hyperlink == false && $is_local_anchor == false) {
			//this is a relative hyperlink
			if ((strpos ( $replaceWhat [$count], "showinframes.php" ) == false) && (strpos ( $replaceWhat [$count], "download.php" ) == false)) {
				//fix the link to use showinframes.php
				$replaceBy [$count] = "a href = \"showinframes.php?file=" . $upload_path . "/" . $file_path_list [$count] . "\" target=\"_top\"";
			} else {
				//url already fixed, leave as is
				$replaceBy [$count] = $replaceWhat [$count];
			}
		} else if ($is_absolute_hyperlink) {
			$replaceBy [$count] = "a href=\"" . $file_path_list [$count] . "\" target =\"_top\"";
		} else {
			//don't change anything
			$replaceBy [$count] = $replaceWhat [$count];
		}
	}
	
	$buffer = str_replace ( $replaceWhat, $replaceBy, $buffer );
	return $buffer;
}

//------------------------------------------------------------------------------


/**
 EXPERIMENTAL - function seems to work, needs more testing

 @param $upload_path is the path where the document is stored, like "/archive/"
 if it is the root level, the function expects "/"
 otherwise "/path/"

 This function parses all tags with $param_name parameters.
 so the tags are displayed correctly.

 --------------
 Algorithm v1.0
 --------------
 given a string and a parameter,
 * OK find all tags in that string with the specified parameter (like href or src)
 * OK for every one of these tags, find the src|href|... part to edit it
 * OK change the src|href|... part to use download.php (or showinframes.php)
 * OK do some special stuff for hyperlinks

 Exceptions
 * OK if download.php or showinframes.php is already in the tag, leave it alone
 * OK if mailto is in the tag, leave it alone
 * OK if the src|href param contains http://, it's absolute --> leave it alone

 Special for hyperlinks (a href...)
 * OK add target="_top"
 * OK use showinframes.php instead of download.php

 @author Roan Embrechts
 @version 1.1
 */
function api_replace_parameter($upload_path, $buffer, $param_name = "src") {
	/*
	 *	Search for tags with $param_name as a parameter
	 */
	/*
	 // [\s]*	matches whitespace
	 // [\"=a-z] matches ", = and a-z
	 // ([\s]*[a-z]*)*	matches all whitespace and normal alphabet
	 //					characters a-z combinations but seems too slow
	 //	perhaps ([\s]*[a-z]*) a maximum number of times ?
	 // [\s]*[a-z]*[\s]*	matches many tags
	 // the ending "i" means to match case insensitive (a matches a and A)
	 */
	$matches = array ();
	if (preg_match_all ( "/<[a-z]+[^<]*" . $param_name . "[^<]*>/i", $buffer, $matches )) {
		$tag_list = $matches [0];
	}
	
	/*
	 *	Search the filepath of parameter $param_name in all detected tags
	 */
	if (sizeof ( $tag_list ) > 0) {
		$file_path_list = array ();
		$href_list = array ();
		
		foreach ( $tag_list as $this_tag ) {
			//Display::display_normal_message(htmlentities($this_tag)); //debug
			if (preg_match ( "~" . $param_name . "[\s]*=[\s]*[\"]{1}([^\"]+)[\"]{1}~i", $this_tag, $matches )) 

			{
				$file_path_list [] = $matches [1]; //older
				$href_list [] = $matches [0]; //to also add target="_top"
			}
		}
	}
	
	/*
	 *	Replace the original tags by the correct ones
	 */
	for($count = 0; $count < sizeof ( $href_list ); $count ++) {
		$replaceWhat [$count] = $href_list [$count];
		
		$is_absolute_hyperlink = strpos ( $replaceWhat [$count], 'http' );
		$is_local_anchor = strpos ( $replaceWhat [$count], '#' );
		if ($is_absolute_hyperlink == false && $is_local_anchor == false) {
			if ((strpos ( $replaceWhat [$count], 'showinframes.php' ) == false) && (strpos ( $replaceWhat [$count], 'download.php' ) == false) && (strpos ( $replaceWhat [$count], 'mailto' ) == false)) {
				//fix the link to use download.php or showinframes.php
				if (preg_match ( "/<a([\s]*[\"\/:'=a-z0-9]*){5}href[^<]*>/i", $tag_list [$count] )) {
					$replaceBy [$count] = " $param_name =\"showinframes.php?file=" . $upload_path . $file_path_list [$count] . "\" target=\"_top\" ";
				} else {
					$replaceBy [$count] = " $param_name =\"download.php?doc_url=" . $upload_path . $file_path_list [$count] . "\" ";
				}
			} else {
				//"mailto" or url already fixed, leave as is
				//$message .= "Already fixed or contains mailto: ";
				$replaceBy [$count] = $replaceWhat [$count];
			}
		} else if ($is_absolute_hyperlink) {
			//$message .= "Absolute hyperlink, don't change, add target=_top: ";
			$replaceBy [$count] = " $param_name=\"" . $file_path_list [$count] . "\" target =\"_top\"";
		} else {
			//don't change anything
			//$message .= "Local anchor, don't change: ";
			$replaceBy [$count] = $replaceWhat [$count];
		}
	
		//$message .= "In tag $count, <b>" . htmlentities($tag_list[$count])
	//	. "</b>, parameter <b>" . $replaceWhat[$count] . "</b> replaced by <b>" . $replaceBy[$count] . "</b><br>"; //debug
	}
	//if (isset($message) && $message == true) api_display_debug_info($message); //debug
	$buffer = str_replace ( $replaceWhat, $replaceBy, $buffer );
	return $buffer;
}

//------------------------------------------------------------------------------


/**
 * Checks the extension of a file, if it's .htm or .html
 * we use search_img_from_html to get all image paths in the file
 *
 * @param string $file
 * @return array paths
 * @see check_for_missing_files() uses search_img_from_html()
 */
function check_for_missing_files($file) {
	if (strrchr ( $file, '.' ) == '.htm' || strrchr ( $file, '.' ) == '.html') {
		$img_file_path = search_img_from_html ( $file );
		return $img_file_path;
	}
	return false;
}

//------------------------------------------------------------------------------


/**
 * This builds a form that asks for the missing images in a html file
 * maybe we should do this another way?
 *
 * @param array $missing_files
 * @param string $upload_path
 * @param string $file_name
 * @return string the form
 */
function build_missing_files_form($missing_files, $upload_path, $file_name) {
	//do we need a / or not?
	$added_slash = ($upload_path == '/') ? '' : '/';
	//build the form
	$form .= "<p><strong>" . get_lang ( 'MissingImagesDetected' ) . "</strong></p>\n" . "<form method=\"post\" action=\"" . $_SERVER ['PHP_SELF'] . "\" enctype=\"multipart/form-data\">\n" . //related_file is the path to the file that has missing images
"<input type=\"hidden\" name=\"related_file\" value=\"" . $upload_path . $added_slash . $file_name .
			 "\" />\n" . "<input type=\"hidden\" name=\"upload_path\" value=\"" . $upload_path . "\" />\n" . "<table border=\"0\">\n";
	foreach ( $missing_files as $this_img_file_path ) {
		$form .= "<tr>\n" . "<td>" . basename ( $this_img_file_path ) . " : </td>\n" . "<td>" . "<input type=\"file\" name=\"img_file[]\"/>" . "<input type=\"hidden\" name=\"img_file_path[]\" value=\"" . $this_img_file_path . "\" />" . "</td>\n" . "</tr>\n";
	}
	$form .= "</table>\n" . "<input type=\"submit\" name=\"cancel_submit_image\" value=\"" . get_lang ( 'Cancel' ) . "\"/>\n" . "<input type=\"submit\" name=\"submit_image\" value=\"" . get_lang ( 'Ok' ) . "\"/><br/>" . "</form>\n";
	return $form;
}

//------------------------------------------------------------------------------


/**
 * This recursive function can be used during the upgrade process form older versions of ZLMS
 * It crawls the given directory, checks if the file is in the DB and adds it if it's not
 *
 * @param string $base_work_dir
 * @param string $current_path, needed for recursivity
 */
function add_all_documents_in_folder_to_database($_course, $user_id, $base_work_dir, $current_path = '') {
	$path = $base_work_dir . $current_path;
	//open dir
	$handle = opendir ( $path );
	//run trough
	while ( $file = readdir ( $handle ) ) {
		if ($file == '.' || $file == '..') continue;
		
		//directory?
		if (! DocumentManager::get_document_id ( $_course, Database::escape_string ( $current_path . '/' . $file ) )) {
			$completepath = $path . '/' . $file;
			if (is_dir ( $completepath )) {
				$title = get_document_title ( $file );
				$safe_file = get_dir_name ( $path );
				@rename ( $path . '/' . $file, $path . '/' . $safe_file );
				$document_id = add_document ( $_course, $current_path . '/' . $safe_file, 'folder', 0, $title );
				api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $user_id );
				//递归处理子目录下文件
				add_all_documents_in_folder_to_database ( $_course, $user_id, $base_work_dir, $current_path . '/' . $safe_file );
			} else {
				$extension = get_file_ext ( $file );
				$safe_file = get_file_name ( $base_work_dir . $current_path . '/', $extension );
				@rename ( $base_work_dir . $current_path . '/' . $file, $base_work_dir . $current_path . '/' . $safe_file );
				$title = get_document_title1 ( $file );
				$size = filesize ( $base_work_dir . $current_path . '/' . $safe_file );
				$document_id = add_document ( $_course, $current_path . '/' . $safe_file, 'file', $size, $title );
				api_item_property_update ( $_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $user_id );
			}
		}
	}
}

// could be usefull in some cases...
function remove_accents($string) {
	$string = strtr ( $string, "�����������������������������������������������������", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn" );
	return $string;
}

/**
 * Delete a file.
 * @param string $file file to be deleted
 * @return boolean true if deleted, false otherwise.
 */
function delFile($file) {
	if (is_file ( $file ))
		Return unlink ( $file );
	else Return false;
}

?>