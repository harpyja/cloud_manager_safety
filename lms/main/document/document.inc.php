<?php

function create_document_link($www, $title, $path, $filetype, $size, $visibility) {
	$url_path = urlencode ( $path );
	$forcedownload_link = $_SERVER ['PHP_SELF'] . '?action=download&amp;id=' . $url_path;
	$force_download_html = ($size == 0) ? '' : '<a href="' . $forcedownload_link . '" style="float:right"' . '>' . Display::return_icon ( 'filesave.gif', get_lang ( 'Download' ), array ('width' => '16', 'height' => '16' ) ) . '</a>';
	return '<b>' . $title . '</b>' . $force_download_html;
}

function build_document_icon_tag($type, $path) {
	$icon = 'folder_document.gif';
	$type_desc = "";
	if ($type == 'file') {
		$icon = choose_image ( basename ( $path ) );
		if (ereg ( '\.([[:alnum:]]+)$', basename ( $path ), $extension )) {
			$type_desc = $extension [1];
		}
	}
	
	return Display::return_icon ( $icon, $type_desc, array ('style' => 'vertical-align: middle' ) );
}

function build_edit_icons($id, $type, $path, $visibility, $title) {
	$sort_params = implode ( '&amp;', $sort_params );
	$visibility_icon = ($visibility == 0) ? 'invisible' : 'visible';
	$visibility_command = ($visibility == 0) ? 'set_visible' : 'set_invisible';
	
	$modify_icons=link_button('edit.gif', 'EditDocument', 'edit_document.php?id=' . $id, '50%', '70%',false);
	$modify_icons .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'document.php?action=delete&id=' . $id );
	//可见性 V2.1
	//$modify_icons .= '&nbsp;<a href="' . $_SERVER['PHP_SELF'] . '?curdirpath=' . $curdirpath . '&amp;' . $visibility_command . '=' . $id . $gid_req . '&amp;' . $sort_params . '">' . Display::return_icon($visibility_icon.'.gif', get_lang('Visible')) . '</a>';
	return $modify_icons;
}

