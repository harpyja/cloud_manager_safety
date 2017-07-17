<?php
/**
 ==============================================================================

 ==============================================================================
 */

$language_file = array ("registration", "admin" );
$cidReset = true;
include_once ('../../inc/global.inc.php');

$table_settings_current = Database::get_main_table ( TABLE_MAIN_SETTINGS_CURRENT );
$resultcategories = array ('Company', 'MailServer', 'Security', 'Platform', 'Exam' );

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$strCategory = isset ( $_GET ['category'] ) ? getgpc ( 'category', 'G' ) : 'Company';

$my_category = escape ( $strCategory );
$form = new FormValidator ( 'settings', 'post', 'settings.php?category=' . $strCategory ,'', 'enctype="multipart/form-data"' );
Display::setTemplateSettings ( $form, '95%' );

$sqlsettings = "SELECT DISTINCT * FROM $table_settings_current WHERE enabled=1 and category='$my_category' GROUP BY variable ORDER BY display_order ASC";
$resultsettings = api_sql_query ( $sqlsettings, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $resultsettings, 'ASSOC' ) ) {
	
	$title = '<span style="font-weight:bold;font-size:14px">' . get_lang ( $row ['title'] ) . "</span><br/>";
	$comment = '<span style="font-style:italic;font-size:11px">' . get_lang ( $row ['comment'] ) . "</span>";
	switch ($row ['type']) {
		case 'textfield' :
			$form->addElement ( 'text', $row ['variable'], $title . $comment, array ('style' => "width:500px", 'class' => 'inputText' ) );
			$default_values [$row ['variable']] = $row ['selected_value'];
			break;
		case 'password' :
			$form->addElement ( 'password', $row ['variable'], $title . $comment, array ('style' => "width:500px", 'class' => 'inputText' ) );
			$default_values [$row ['variable']] = $row ['selected_value'];
			break;
		case 'textarea' :
			$form->addElement ( 'textarea', $row ['variable'], $title . $comment );
			$default_values [$row ['variable']] = $row ['selected_value'];
			break;
		case 'radio' :
			$values = get_settings_options ( $row ['variable'] );
			$group = array ();
			foreach ( $values as $key => $value ) {
				$group [] = $form->createElement ( 'radio', $row ['variable'], '', get_lang ( $value ['display_text'] ), $value ['value'] );
			}
			$form->addGroup ( $group, $row ['variable'], $title . $comment, str_repeat ( '&nbsp;', 6 ), false );
			$default_values [$row ['variable']] = $row ['selected_value'];
			break;
		case 'checkbox' :
			$sql = "SELECT * FROM $table_settings_current WHERE enabled=1 and variable='" . $row ['variable'] . "'";
			//echo $sql."<BR>";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			$group = array ();
			while ( $rowkeys = Database::fetch_array ( $result ) ) {
				$element = $form->createElement ( 'checkbox', $rowkeys ['subkey'], '', get_lang ( $rowkeys ['subkeytext'] ) );
				if ($rowkeys ['selected_value'] == 'true' && ! $form->isSubmitted ()) {
					$element->setChecked ( true );
				}
				$group [] = $element;
			}
			$form->addGroup ( $group, $row ['variable'], $title . $comment, str_repeat ( '&nbsp;', 4 ) . "\n" );
			break;
		case "link" :
			$form->addElement ( 'static', null, $title . $comment, get_lang ( 'CurrentValue' ) . ' : ' . $row ['selected_value'] );
	}
}
if($_GET['category']=='Company' || $_GET['category']==''){
    $form->addElement ( 'file', 'user_upload', '上传logo图片', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );
    $form->addElement ( 'file', 'logo_upload', '登陆页logo上传', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );
//$form->addElement ( 'text', 'color2', '背景颜色',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
//$form->addRule ( 'user_upload', get_lang ( '背景颜色不能为空！' ), 'required' );
}
$form->addElement ( 'style_submit_button', null, get_lang ( 'SaveSettings' ), 'class="save"' );
$form->setDefaults ( $default_values );
$color1 = $default_values['bgcolor'];
if ($form->validate ()) { //处理保存
	$values = $form->exportValues ();
	$sql = "UPDATE $table_settings_current SET selected_value='false' WHERE category='$my_category' AND type='checkbox'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	// Save the settings
	foreach ( $values as $key => $value ) {
		if (! is_array ( $value )) {
			$sql = "UPDATE $table_settings_current SET selected_value='" . Database::escape_string ( $value ) . "' WHERE variable='$key'";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			foreach ( $value as $subkey => $subvalue ) {
				$sql = "UPDATE $table_settings_current SET selected_value='true' WHERE variable='$key' AND subkey = '$subkey'";
				$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			}
		}
	}
    $dir = "/tmp";
/**index logo */
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间
    exec(" rm -rf /etc/oem/logo4.gif");

    $upfile = '/etc/oem/logo4.gif';
    //move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $upfile );
    $cp = "$dir/www/lms/panel/default/assets/images/logo4.gif";

    move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $cp );
    copy($cp,$upfile);

    $color2 = $values['bgcolor'];
    $des = $color2;
    $fh = fopen("/etc/oem/color","w");
    fwrite($fh,$des);
    fclose($fh);

/**login logo */
    exec(" rm -rf /etc/oem/logo3.gif");
    $logo="/etc/oem/logo3.gif";
    $cplogo = "$dir/www/lms/panel/default/assets/images/logo3.gif";
    move_uploaded_file ( $_FILES ['logo_upload'] ['tmp_name'], $cplogo );
    copy($cplogo,$logo);


    $file_1="$dir/www/lms/portal/sp/index.css";
    $file_2="$dir/www/lms/themes/default/default.css";
    $file_3="$dir/www/lms/themes/js/yui/tabview/assets/skins/sam/tabview.css";
    $file_4="$dir/www/lms/main/admin/index.php";
    $file_5="$dir/www/lms/themes/js/jquery-plugins/thickbox/thickbox.css";
    $file_6="$dir/www/lms/main/admin/import_export/courses_import.php";
    $file_7="$dir/www/lms/main/inc/header.inc.php";
    $file_8="$dir/www/lms/main/admin/systeminfo.php";
//$file_9="set_themes.php";

$file_10="$dir/www/lms/portal/sp/login1.php";
$file_11="$dir/www/lms/portal/sp/login.css";
$file_12="$dir/www/lms/portal/sp/document_viewer.php";
    $file_10="$dir/www/lms/themes/js/yui/tabview/assets/skins/sam/tabview-skin.css";
    file_put_contents($file_1,str_replace($color1,$color2,file_get_contents($file_1)));
    file_put_contents($file_2,str_replace($color1,$color2,file_get_contents($file_2)));
    file_put_contents($file_3,str_replace($color1,$color2,file_get_contents($file_3)));
    file_put_contents($file_4,str_replace($color1,$color2,file_get_contents($file_4)));
    file_put_contents($file_5,str_replace($color1,$color2,file_get_contents($file_5)));
    file_put_contents($file_6,str_replace($color1,$color2,file_get_contents($file_6)));
    file_put_contents($file_7,str_replace($color1,$color2,file_get_contents($file_7)));
    file_put_contents($file_8,str_replace($color1,$color2,file_get_contents($file_8)));
    file_put_contents($file_9,str_replace($color1,$color2,file_get_contents($file_9)));

file_put_contents($file_10,str_replace($color1,$color2,file_get_contents($file_10)));
file_put_contents($file_11,str_replace($color1,$color2,file_get_contents($file_11)));
file_put_contents($file_12,str_replace($color1,$color2,file_get_contents($file_12)));

	$log_msg = get_lang ( 'UpdateSettings' );
	api_logging ( $log_msg, 'SYS_SETTINGS', 'UpdateSettings' );
	
	cache(CACHE_KEY_PLATFORM_SETTINGS,NULL);
	cache(CACHE_KEY_PLATFORM_SETTINGS,get_platform_settings ());
	
	//配置的相关处理
	hook_after_saving ();
	//var_dump($values);
	//api_redirect ( 'settings.php?action=stored&category=' . $strCategory );
}


/**
 * 配置保存到setting表后的相关处理
 */
function hook_after_saving() {
    global $table_settings_current;

    //上传
    $upload_extensions_whitelist_default = array ("zip", "rar", "flv", "mp3", "ppt",'doc','xls','pptx','xlsx','pptx','pdf' );
    $upload_extensions_whitelist = api_get_setting ( 'upload_extensions_whitelist' );
    $upload_extensions_whitelist_arr = (empty ( $upload_extensions_whitelist ) ? $upload_extensions_whitelist_default : explode ( ";", $upload_extensions_whitelist ));
    $upload_extensions_whitelist_arr = array_merge ( $upload_extensions_whitelist_arr, $upload_extensions_whitelist_default );
    $sql_data = array ("selected_value" => implode ( ";", array_unique ( $upload_extensions_whitelist_arr ) ) );
    $sql = Database::sql_update ( $table_settings_current, $sql_data, "variable='upload_extensions_whitelist'" );
    api_sql_query ( $sql, __FILE__, __LINE__ );

    $upload_extensions_blacklist_default = array ("exe", "php", "com", "bat", "sh", 'dll', 'so', 'ocx' );
    $upload_extensions_blacklist = api_get_setting ( "upload_extensions_blacklist" );
    $upload_extensions_blacklist_arr = (empty ( $upload_extensions_blacklist ) ? $upload_extensions_blacklist_default : explode ( ";", $upload_extensions_blacklist ));
    $upload_extensions_blacklist_arr = array_merge ( $upload_extensions_blacklist_arr, $upload_extensions_blacklist_default );
    $sql_data = array ("selected_value" => implode ( ";", array_unique ( $upload_extensions_blacklist_arr ) ) );
    $sql = Database::sql_update ( $table_settings_current, $sql_data, "variable='upload_extensions_blacklist'" );
    api_sql_query ( $sql, __FILE__, __LINE__ );

    require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
    $objDept = new DeptManager ();
    $objDept->init ();

    cache(CACHE_KEY_PLATFORM_SETTINGS,NULL);
    cache(CACHE_KEY_PLATFORM_SETTINGS,get_platform_settings ());
}

function get_settings_options($var) {
    $table_settings_options = Database::get_main_table ( TABLE_MAIN_SETTINGS_OPTIONS );
    $sql = "SELECT * FROM $table_settings_options WHERE  variable='$var'"; //echo $sql."<BR>";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
        $temp_array = array ('value' => $row ['value'], 'display_text' => $row ['display_text'] );
        $settings_options_array [] = $temp_array;
    }
    return $settings_options_array;
}


Display::display_header ( NULL );

if ($_GET ['action'] == "stored") {
	Display::display_normal_message ( get_lang ( 'SettingsStored' ) );
}


//echo $html;



//Display::display_footer ( TRUE );
?>


<aside id="sidebar" class="column system open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
<h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统设置</h4>
<ul class="manage-tab boxPublic">
   <?php
    foreach ( $resultcategories as $value ) {
    $strClass = ($strCategory == $value ? 'class="selected"' : '');
    $html .= '<li  ' . $strClass . '><a href="' . $_SERVER ['PHP_SELF'] . '?category=' . $value . '">' . get_lang ( $value ) . '</a></li>';
        }
    echo $html;?>
</ul>
<div class="manage-tab-content">
<div class="manage-tab-content-list" >
    <div class="tabcontent boxPublic" style="background:#FFF;">
            <table cellpadding="0" cellspacing="0" class="settingstable">
                <tbody>

                <?php  $form->display (); ?>

                </tbody>
            </table>
    </div>
</div>





</div>
</section>
</body>
</html>
