<?php
require_once ("HTTP/Upload.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');

class AttachmentManager {

	function hanle_sys_upload($file_element, $attachment_type, $save_dir) {
		if (isset ( $file_element ) && is_not_blank ( $save_dir ) && is_object ( $file_element ) && is_a ( $file_element, "HTML_QuickForm_file" )) {
			$file = $file_element->getValue (); //数组name,type,tmp_name,error,size
			$file_new_name = '';
			$uniqid = uniqid ( '' );
			$uniq_id = md5 ( $uniqid );
			$table_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
			if (strlen ( $file ['name'] ) > 0 && $file ['error'] == 0) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_type = $file ['type'];
				$file_new_name = time () . '_' . $uniqid . "." . getFileExt ( $filename );
				$file_element->moveUploadedFile ( $save_dir, $file_new_name );
				$save_uri = $file ['save_uri'] = substr ( $save_dir, strlen ( api_get_path ( SYS_PATH ) ) ) . $file_new_name;
				
				$sql_data = array ('name' => $uniq_id, 'old_name' => $filename, 'new_name' => $file_new_name, 'ext_name' => getFileExt ( $filename ), 'url' => $save_uri, 'size' => $fsize, 'type' => $attachment_type, 'mime_type' => $file_type );
				$sql = Database::sql_insert ( $table_attachment, $sql_data );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$file ['attachment_uniqid'] = $uniq_id;
				$file ['new_name'] = $file_new_name;
			}
			return $file;
		}
		return false;
	}

	function hanle_upload($file_element, $attachment_type, $save_dir, $file_url_prefix, $db_name = '') {
		if (isset ( $file_element ) && is_not_blank ( $save_dir ) && is_object ( $file_element ) && is_a ( $file_element, "HTML_QuickForm_file" )) {
			$file = $file_element->getValue (); //数组name,type,tmp_name,error,size
			$file_new_name = '';
			$uniqid = get_unique_name ();
			$uniq_id = md5 ( $uniqid );
			$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT, $db_name );
			if (strlen ( $file ['name'] ) > 0 && $file ['error'] == 0) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_type = $file ['type'];
				$file_new_name = $uniqid . "." . getFileExt ( $filename );
				$file_element->moveUploadedFile ( $save_dir, $file_new_name );
				
				$sql_data = array ('name' => $uniqid, 'old_name' => $filename, 'new_name' => $file_new_name, 'ext_name' => getFileExt ( $filename ), 'url' => $file_url_prefix . $file_new_name, 'size' => $fsize, 'type' => $attachment_type, 'mime_type' => $file_type );
				$sql_data ['cc'] = api_get_course_code ();
				$sql = Database::sql_insert ( $table_attachment, $sql_data );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$file ['attachment_uniqid'] = $uniq_id;
				$file ['new_name'] = $file_new_name;
			}
			return $file;
		}
		return false;
	}

	/**
	 *
	 * @param $html_form_filename HTML表单域名
	 * @param $attachment_type 上传所属模块
	 * @param $dest_dir 上传到目标目录
	 * @param $file_url_prefix
	 * @param $ref_id
	 * @param $uniqid
	 * @param $deny_files
	 * @param $accept_files
	 * @param $db_name
	 * @return unknown_type
	 */
	function do_upload($html_form_filename, $attachment_type, $dest_dir, $file_url_prefix = '', $ref_id = '0', $uniqid = '', $deny_files = NULL, $accept_files = NULL, $db_name = '') {
		if (isset ( $_FILES [$html_form_filename] )) {
			$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT, $db_name );
			$upload = new HTTP_Upload ( "zh_CN" );
			$upload->setChmod ( 0664 );
			$file = $upload->getFiles ( $html_form_filename );
			if ($file->isValid ()) {
				//$file->setName('uniq');
				$file_ext = $file->getProp ( 'ext' );
				$file->setName ( get_unique_name () . "." . $file_ext );
				
				//if($deny_files==NULL)
				//$deny_files=array('php', 'phtm', 'phtml', 'php3', 'inc','exe','cmd','bat');
				

				if (isset ( $deny_files )) {
					$file->setValidExtensions ( $deny_files );
				}
				
				if (isset ( $accept_files )) {
					$file->setValidExtensions ( $accept_files, 'accept' );
				}
				
				if (! isset ( $deny_files ) && ! isset ( $accept_files )) {
				
				}
				$moved = $file->moveTo ( $dest_dir );
				if (! PEAR::isError ( $moved )) {
					$filename = $file->getProp ( 'real' );
					$file_uri = $file->getProp ( 'name' );
					$file_size = $file->getProp ( 'size' );
					$file_mimetype = $file->getProp ( 'type' );
					
					//$file_uri = $uniqid . '_' . time () . "." . $file_ext;
					$sql_data = array ('name' => $uniqid, 'old_name' => $filename, 'new_name' => $file_uri, 'ext_name' => $file_ext, 'url' => $file_url_prefix . $file_uri, 'size' => $file_size, 'type' => $attachment_type, 'ref_id' => $ref_id, 'mime_type' => $file_mimetype );
					$sql_data ['cc'] = api_get_course_code ();
					$sql = Database::sql_insert ( $table_attachment, $sql_data );
					api_sql_query ( $sql, __FILE__, __LINE__ );
					return get_lang ( "UploadSuccess" );
				} else {
					return $moved->getMessage ();
				}
			} elseif ($file->isMissing ()) {
				return "No file was provided.";
			} elseif ($file->isError ()) {
				return $file->errorMsg ();
			}
		}
		return "";
	}

	function del_all_sys_attachment($attachment_type, $ref_id, $file_path_prefix) {
		$table_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
		$sql = "SELECT * FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . Database::escape_string ( $ref_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($res && Database::num_rows ( $res ) > 0) {
			while ( $attachment = mysql_fetch_array ( $res ) ) {
				$attachment_uri = $file_path_prefix . $attachment ['url'];
				$del_res = unlink ( $attachment_uri );
			}
		}
		$sql = "DELETE FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . $ref_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}
        function update_sys_attachment($attachment_type, $ref_id, $file_path_prefix) {
		$table_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
		$sql = "SELECT * FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . Database::escape_string ( $ref_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($res && Database::num_rows ( $res ) > 0) {
			while ( $attachment = mysql_fetch_array ( $res ) ) {
				$attachment_uri = $file_path_prefix . $attachment ['url'];
				$del_res = unlink ( $attachment_uri );
			}
		}
		$sql = "DELETE FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . $ref_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function del_all_attachment($attachment_type, $ref_id, $file_path_prefix) {
		$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );
		$sql = "SELECT * FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . Database::escape_string ( $ref_id ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($res && Database::num_rows ( $res ) > 0) {
			while ( $attachment = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$attachment_uri = $file_path_prefix . $attachment ['url'];
				$del_res = unlink ( $attachment_uri );
			}
		}
		$sql = "DELETE FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . $ref_id . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function del_attachment($name, $file_path_prefix) {
		$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );
		$sql = "SELECT url FROM " . $table_attachment . " WHERE name='" . Database::escape_string ( $name ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		$attachment_uri = Database::get_scalar_value ( $sql );
		if (isset ( $attachment_uri ) and ! empty ( $attachment_uri )) {
			$del_res = unlink ( $file_path_prefix . $attachment_uri );
		}
		$sql = "DELETE FROM " . $table_attachment . " WHERE name='" . Database::escape_string ( $name ) . "'";
		 $sql .= " AND cc='" . api_get_course_code () . "' ";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function get_attachment($attachment_type, $ref_id) {
		if ($attachment_type && $ref_id) {
			$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );
			$sql = "SELECT * FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . Database::escape_string ( $ref_id ) . "'";
			$sql .= " AND cc='" . api_get_course_code () . "' ";
			//echo $sql;
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			return api_store_result_array ( $res );
		}
		return false;
	}

	function get_sys_attachment($attachment_type, $ref_id) {
		if ($attachment_type && $ref_id) {
			$table_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
			$sql = "SELECT * FROM " . $table_attachment . " WHERE type='" . $attachment_type . "' AND ref_id='" . Database::escape_string ( $ref_id ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			return api_store_result_array ( $res );
		}
		return false;
	}

	function get_sys_attachment2($attachment_url, $attachment_type) {
		if ($attachment_url) {
			$table_attachment = Database::get_main_table ( TABLE_MAIN_SYS_ATTACHMENT );
			$sql = "SELECT * FROM " . $table_attachment . " WHERE TRIM(url)='" . escape ( trim ( $attachment_url ) ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			return Database::fetch_one_row ( $sql, true, __FILE__, __LINE__ );
		}
		return false;
	}
}
