<?php
/**
==============================================================================
*	This script displays the ZLMS header.
*
*	@package zllms.include
==============================================================================
*/

/*----------------------------------------
              HEADERS SECTION
  --------------------------------------*/

/*
 * HTTP HEADER
 */
header('Content-Type: text/html; charset='. SYSTEM_CHARSET);
if ( isset($httpHeadXtra) && $httpHeadXtra )
{
	foreach($httpHeadXtra as $thisHttpHead)
	{
		header($thisHttpHead);
	}
}

// Get language iso-code for this page - ignore errors
// The error ignorance is due to the non compatibility of function_exists()
// with the object syntax of Database::get_language_isocode()

//@$document_language = Database::get_language_isocode($language_interface);
//if(empty($document_language)){
  $document_language = 'en';
//}
$my_code_path = api_get_path(WEB_CODE_PATH);
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $document_language; ?>" lang="<?php echo $document_language; ?>">
<head>
<title>
<?=get_setting('siteName'); ?>
</title>
<style type="text/css" media="screen, projection">
/*<![CDATA[*/
<?php
$my_style='default';
echo "@import '" . api_get_path(WEB_PATH) . 'themes/'.$my_style.'/default.css' . "';\n";
//echo "@import '" . api_get_path(WEB_CSS_PATH) . 'course.css' . "';\n";
?>
/*]]>*/
</style>
<!-- <link rel="top" href="<?php echo api_get_path(WEB_PATH); ?>index.php" title="" />
<link rel="courses" href="<?php echo api_get_path(WEB_CODE_PATH) ?>course/course_manage.php" title="<?php echo htmlentities(get_lang('OtherCourses'), ENT_NOQUOTES, SYSTEM_CHARSET); ?>" />
<link rel="profil" href="<?php echo api_get_path(WEB_CODE_PATH) ?>auth/profile.php" title="<?php echo htmlentities(get_lang('ModifyProfile'), ENT_NOQUOTES, SYSTEM_CHARSET); ?>" />
 -->
<link rel="shortcut icon" href="<?php echo api_get_path(WEB_PATH); ?>favicon.gif" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo SYSTEM_CHARSET ?>" />
<?php
if ( isset($htmlHeadXtra) && $htmlHeadXtra )
{
	foreach($htmlHeadXtra as $this_html_head)
	{
		echo($this_html_head);
	}
}

if ( isset($htmlIncHeadXtra) && $htmlIncHeadXtra )
{
	foreach($htmlIncHeadXtra as $this_html_head)
	{
		include($this_html_head);
	}
}
//the following include might be subject to a setting proper to the course or platform
//include(api_get_path(LIBRARY_PATH).'/javascript/email_links.lib.js.php');
?>

</head>
<body>

<!-- #outerframe container to control some general layout of all pages -->
<div id="outerframe">

<?php $html = "<div id='header'>\n"; ?>
<div id="header4">
<?php
/*
 */
$navigation = array();
// part 1: Course Homepage. If we are in a course then the first breadcrumb is a link to the course homepage
if (isset ($_cid))
{
	$navigation_item['url'] = api_get_path(WEB_PATH) . 'user_portal.php';
	
			$navigation_item['title'] =  $_course['name'];
	$navigation_item['target']=(isset($display_course_homepage_target) && !empty($display_course_homepage_target)?$display_course_homepage_target:'_self');
	//if($display_course_homepage_in_parent)
	//$navigation_item['target']='_parent';
	$navigation[] = $navigation_item;
}
// part 2: Interbreadcrumbs. If there is an array $interbreadcrumb defined then these have to appear before the last breadcrumb (which is the tool itself)
if (is_array($interbreadcrumb))
{
	foreach($interbreadcrumb as $breadcrumb_step)
	{
		$sep = (strrchr($breadcrumb_step['url'], '?') ? '&amp;' : '?');
		$navigation_item['url'] = $breadcrumb_step['url'].$sep.api_get_cidreq();
		$navigation_item['title'] = $breadcrumb_step['name'];
		$navigation_item['target']=((isset($breadcrumb_step['target'])&& !empty($breadcrumb_step['target']))?$breadcrumb_step['target']:'_self');
		$navigation[] = $navigation_item;
	}
}
// part 3: The tool itself. If we are on the course homepage we do not want to display the title of the course because this
// is the same as the first part of the breadcrumbs (see part 1)
if (isset ($nameTools) AND $language_file<>"course_home")
{
	$navigation_item['url'] = '#';
	$navigation_item['title'] = $nameTools;
	$navigation[] = $navigation_item;
}

foreach($navigation as $index => $navigation_info)
{
	$navigation[$index] = '<a href="'.$navigation_info['url'].'" target="'.$navigation_info['target'].'">'.$navigation_info['title'].'</a>';
}
echo implode(' &gt; ',$navigation);
if (isset ( $_cid ) && !empty($_course)) {
	echo '<div style="float:right;"><b>'.$_course ['name'].'-'.$_course ['official_code'].'</b><span style="padding-left:20px;">'
	.Display::display_course_tool_shortcuts($_cid).'</span></div>';
}
?>

</div><!-- end of header4 -->

</div> <!-- end of the whole #header section -->
<div class="clear">&nbsp;</div>
<?php
//to mask the main div, set $header_hide_main_div to true in any script just before calling Display::display_header();
global $header_hide_main_div;
if(!empty($header_hide_main_div) && $header_hide_main_div===true)
{
	//do nothing
}
else
{
?>
<div id="main"> <!-- start of #main wrapper for #content and #menu divs -->
<?php
}
?>
<!--   Begin Of script Output   -->
<body><div>