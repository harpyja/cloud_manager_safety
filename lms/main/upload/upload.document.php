<?php

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
require_once ('../document/document.inc.php');

if (isset ( $_POST ['unzip'] ) && $_POST ['unzip'] == 1) {
	require_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
}

$courseDir = api_get_course_code () . "/document";
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir;
$max_filled_space = DocumentManager::get_course_quota ();

//what's the current path?
if (isset ( $_POST ['curdirpath'] )) {
	$path = $_POST ['curdirpath'];
} else {
	$path = '/';
}

// Check the path
// If the path is not found (no document id), set the path to /
if (! DocumentManager::get_document_id ( $_course, $path )) $path = '/';

$nameTools = get_lang ( 'UplUploadDocument' );

Display::display_header ( $nameTools, "Doc" );

if (isset ( $_FILES ['user_upload'] )) {
	$upload_ok = process_uploaded_file ( $_FILES ['user_upload'] );
	if ($upload_ok) {
		$new_path = handle_uploaded_document ( $_course, $_FILES ['user_upload'], $base_work_dir, $_POST ['curdirpath'], $_user ['user_id'], NULL, $_POST ['unzip'], $_POST ['title'], $_POST ['if_exists'] );
		$new_comment = isset ( $_POST ['comment'] ) ? trim ( $_POST ['comment'] ) : '';
		$new_title = isset ( $_POST ['title'] ) ? trim ( $_POST ['title'] ) : '';
		$docid = DocumentManager::get_document_id ( $_course, $new_path );
		if ($new_path && ($new_comment || $new_title) && $docid) {
			$table_document = Database::get_course_table ( TABLE_DOCUMENT );
			$ct = '';
			if ($new_comment) $ct .= ", comment='$new_comment'";
			if ($new_title) $ct .= ", title='$new_title'";
			api_sql_query ( "UPDATE $table_document SET" . substr ( $ct, 1 ) . " WHERE id = '$docid'", __FILE__, __LINE__ );
		}
	}
}
if (isset ( $_POST ['create_dir'] ) && $_POST ['dirname'] != '') {
	$added_slash = ($path == '/') ? '' : '/';
	$dir_name = $path . $added_slash . replace_dangerous_char ( $_POST ['dirname'] );
	$created_dir = create_unexisting_directory ( $_course, $_user ['user_id'], NULL, $base_work_dir, $_POST ['curdirpath'], $dir_name, $_POST ['dirname'] );
	if ($created_dir) {
		//Display::display_normal_message("<strong>".$created_dir."</strong> was created!");
		Display::display_normal_message ( get_lang ( 'DirCr' ) );
		$path = $created_dir;
	} else {
		Display::display_error_message ( get_lang ( 'CannotCreateDir' ) );
	}
}
?>

<div id="folderselector"></div>

<!-- start upload form -->
<form action="<?php
echo $_SERVER ['PHP_SELF'];
?>" method="POST"
	name="upload" enctype="multipart/form-data"><input type="hidden"
	name="curdirpath" value="<?=$path?>">
<table>
	<tr>
		<td valign="top">
<?php
echo get_lang ( 'File' );
?>
</td>
		<td><input type="file" name="user_upload" /></td>
	</tr>
<?php
if (get_setting ( 'use_document_title' ) == 'true') {
	?>
    <tr>
		<td><?php
	echo get_lang ( 'Title' );
	?></td>
		<td><input type="text" size="20" name="title" style="width: 300px;"></td>
	</tr>
	<?php
}
?>
    <tr>
		<td valign="top"><?php
		echo get_lang ( 'Comment' );
		?></td>
		<td><textarea rows="3" cols="20" name="comment" wrap="virtual"
			style="width: 300px;"></textarea></td>
	</tr>
	<tr>
		<td valign="top">
<?php
echo get_lang ( 'Options' );
?>
</td>
		<td>- <input type="checkbox" name="unzip" value="1"
			onclick="check_unzip()" /> <?php
			echo (get_lang ( 'Uncompress' ));
			?><br />
- <?php
echo (get_lang ( 'UplWhatIfFileExists' ));
?><br />
		&nbsp;&nbsp;&nbsp;<input type="radio" name="if_exists" value="nothing"
			title="<?php
			echo (get_lang ( 'UplDoNothingLong' ));
			?>"
			checked="checked" />  <?php
			echo (get_lang ( 'UplDoNothing' ));
			?><br />
		&nbsp;&nbsp;&nbsp;<input type="radio" name="if_exists"
			value="overwrite"
			title="<?php
			echo (get_lang ( 'UplOverwriteLong' ));
			?>" /> <?php
			echo (get_lang ( 'UplOverwrite' ));
			?><br />
		&nbsp;&nbsp;&nbsp;<input type="radio" name="if_exists" value="rename"
			title="<?php
			echo (get_lang ( 'UplRenameLong' ));
			?>" /> <?php
			echo (get_lang ( 'UplRename' ));
			?>

</td>
	</tr>
</table>

<input type="submit" value="<?php
echo (get_lang ( 'Ok' ));
?>"></form>
<!-- end upload form -->

<!-- so they can get back to the documents   -->
<p><?php
echo (get_lang ( 'Back' ));
?> <?php
echo (get_lang ( 'To' ));
?> <a href="document.php?curdirpath=<?php
echo $path;
?>"><?php
echo (get_lang ( 'DocumentsOverview' ));
?></a></p>

<?php

Display::display_footer ();
?>