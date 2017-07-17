<?php
// $Id: html_editor.php 11004 2007-01-30 10:20:01Z elixir_julian $
/*
==============================================================================
	ZLMS - elearning and course management software

	Copyright (c) 2004-2005 ZLMS S.A.
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Zhong
	Mail: poopsoft@163.com
==============================================================================
*/
require_once ('HTML/QuickForm/textarea.php');
require_once (api_get_path(LIBRARY_PATH).'fckeditor/fckeditor.php');
/**
* A html editor field to use with QuickForm
*/
class HTML_QuickForm_html_editor extends HTML_QuickForm_textarea
{
	/**
	 * Full page
	 */
	var $fullPage;
	var $fck_editor;
	/**
	 * Class constructor
	 * @param   string  HTML editor name/id
	 * @param   string  HTML editor  label
	 * @param   string  Attributes for the textarea
	 */
	function HTML_QuickForm_html_editor($elementName = null, $elementLabel = null, $attributes = null)
	{
		global $language_interface, $fck_attribute;
		if($attributes['fck_attribute']) $fck_attribute=$attributes['fck_attribute'];
		HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
		$this->_persistantFreeze = true;
		$this->_type = 'html_editor';
		$this->fullPage = false;
		
/*	Maybe it is unuseful.		Zhong 20070928			
		@ $editor_lang = Database :: get_language_isocode($language_interface);
		// file_exists is not used for remote file. 	Zhong 20070928
		//$language_file = api_get_path(WEB_PATH).'/main/inc/lib/fckeditor/editor/lang/'.$editor_lang.'.js';
		$language_file = api_get_path(SYS_PATH).'/main/inc/lib/fckeditor/editor/lang/'.$editor_lang.'.js';
		if (empty ($editor_lang) || !file_exists($language_file))
		{
			//if there was no valid iso-code, use the english one
			$editor_lang = 'en';
		}
*/
		
		$name = $this->getAttribute('name');
		
		$this -> fck_editor = new FCKeditor($name);
		$this -> fck_editor->BasePath = api_get_path(WEB_LIBRARY_PATH).'fckeditor/';

		$this -> fck_editor->Width = $fck_attribute['Width'] ? $fck_attribute['Width'] : '100%';
		$this -> fck_editor->Height = $fck_attribute['Height'] ? $fck_attribute['Height'] : '400';
		
		//We get the optionnals config parameters in $fck_attribute array
		$this -> fck_editor->Config = $fck_attribute['Config'] ? $fck_attribute['Config'] : array();
		
		$TBL_LANGUAGES = Database::get_main_table(TABLE_MAIN_LANGUAGE);
		//We are in a course
		if(isset($_SESSION["_course"]["language"])){
			$sql="SELECT isocode FROM ".$TBL_LANGUAGES." WHERE english_name='".$_SESSION["_course"]["language"]."'";
		}
		//Else, we get the current session language
		elseif(isset($_SESSION["_user"]["language"])){
			$sql="SELECT isocode FROM ".$TBL_LANGUAGES." WHERE english_name='".$_SESSION["_user"]["language"]."'";
		}
		//Else we get the default platform language
		else{
			$platform_language=api_get_setting("platformLanguage");
			$sql="SELECT isocode FROM ".$TBL_LANGUAGES." WHERE english_name='$platform_language'";
		}
		$result_sql = api_sql_query($sql);
		$isocode_language = mysql_result($result_sql,0,0);
		$this -> fck_editor->Config['DefaultLanguage'] = $isocode_language;
		
		if($_SESSION['_course']['path']!=''){
			$upload_path = api_get_path(REL_COURSE_PATH).$_SESSION['_course']['path'].'/document/';
		}else{
			$upload_path = api_get_path(REL_PATH)."storage/attachment/";
		}
		
		//$this -> fck_editor->Config['CustomConfigurationsPath'] = api_get_path(REL_PATH)."main/inc/lib/fckeditor/myconfig.js";
		$this -> fck_editor->Config['CustomConfigurationsPath'] = api_get_path(WEB_LIBRARY_PATH) . "fckeditor/myconfig.js";
		
		$this -> fck_editor->Config['EditorAreaCSS'] = api_get_path(WEB_CSS_PATH) . "course.css";
		
		//$this -> fck_editor->Config['FullPage'] = 'false';

		$this -> fck_editor->ToolbarSet = $fck_attribute['ToolbarSet'] ;
		
		$this -> fck_editor->Config['LinkBrowserURL'] = $this -> fck_editor->BasePath . "editor/filemanager/browser/default/browser.html?Connector=connectors/php/connector.php&ServerPath=$upload_path";
		
		// Maybe this is bug. Zhong 20070830
		//$this -> fck_editor->Config['EditorAreaCSS'] = api_get_path(SYS_PATH).'main/css/'.api_get_setting('stylesheets').'/course.css';
		$this -> fck_editor->Config['EditorAreaCSS'] = api_get_path(WEB_CSS_PATH).'course.css';
		
		//for image
		$this -> fck_editor->Config['ImageBrowserURL'] = $this -> fck_editor->BasePath . "editor/filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php&ServerPath=$upload_path";

		$this -> fck_editor->Config['ImageUploadURL'] = $this -> fck_editor->BasePath . "editor/filemanager/upload/php/upload.php?Type=Image&ServerPath=$upload_path" ;

		//for flash
		$this -> fck_editor->Config['FlashBrowserURL'] = $this -> fck_editor->BasePath . "editor/filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/php/connector.php&ServerPath=$upload_path";

		$this -> fck_editor->Config['FlashUploadURL'] = $this -> fck_editor->BasePath . "editor/filemanager/upload/php/upload.php?Type=Flash&ServerPath=$upload_path" ;

		//for MP3
		$this -> fck_editor->Config['MP3BrowserURL'] = $this -> fck_editor->BasePath . "editor/filemanager/browser/default/browser.html?Type=MP3&Connector=connectors/php/connector.php&ServerPath=$upload_path";

		$this -> fck_editor->Config['MP3UploadURL'] = $this -> fck_editor->BasePath . "editor/filemanager/upload/php/upload.php?Type=MP3&ServerPath=$upload_path" ;

		//for other media
		$this -> fck_editor->Config['VideoBrowserURL'] = $this -> fck_editor->BasePath . "editor/filemanager/browser/default/browser.html?Type=Video&Connector=connectors/php/connector.php&ServerPath=$upload_path";

		$this -> fck_editor->Config['VideoUploadURL'] = $this -> fck_editor->BasePath . "editor/filemanager/upload/php/upload.php?Type=Video&ServerPath=$upload_path" ;
		
		//liyu: 开启时Toolbar是否展开
		$this -> fck_editor->Config['ToolbarStartExpanded'] =$fck_attribute["ToolbarStartExpanded"];
		
		$templates_file = api_get_path(LIBRARY_PATH) . "fckeditor/fcktemplates_{$isocode_language}.xml.php";
		if (file_exists($templates_file))
		{
			$templates_file = $this -> fck_editor->BasePath . "fcktemplates_{$isocode_language}.xml.php";
		}
		else 
		{
			$templates_file = $this -> fck_editor->BasePath  . "fcktemplates.xml.php";
		}
		$this -> fck_editor->Config['TemplatesXmlPath'] = $templates_file;
		 
	}
	/**
	 * Check if the browser supports FCKeditor
	 *
	 * @access public
	 * @return boolean
	 */
	function browserSupported()
	{
		return FCKeditor :: IsCompatible();
	}
	/**
	 * Return the HTML editor in HTML
	 * @return string
	 */
	function toHtml()
	{
		$value = $this->getValue();
		if ($this->fullPage)
		{
			if (strlen(trim($value)) == 0)
			{
				$value = '<html><head><title></title><style type="text/css" media="screen, projection">/*<![CDATA[*/body{font-family: arial, verdana, helvetica, sans-serif;font-size: 12px;}/*]]>*/</style></head><body></body></html>';
				$this->setValue($value);
			}
		}
		if ($this->_flagFrozen)
		{
			return $this->getFrozenHtml();
		}
		else
		{
			return $this->build_FCKeditor();
		}
	}
	/**
	 * Returns the htmlarea content in HTML
	 *@return string
	 */
	function getFrozenHtml()
	{
		return $this->getValue();
	}
	/**
	 * Build this element using FCKeditor
	 */
	function build_FCKeditor()
	{
		if(! FCKeditor :: IsCompatible())
		{
			return parent::toHTML();
		}
		$this -> fck_editor->Value = $this->getValue();
		$result .=$this -> fck_editor->CreateHtml();

		//Add a link to open the allowed html tags window 
		//$result .= '<small><a href="#" onclick="MyWindow=window.open('."'".api_get_path(WEB_CODE_PATH)."help/allowed_html_tags.php?fullpage=". ($this->fullPage ? '1' : '0')."','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=500,height=600,left=200,top=20'".'); return false;">'.get_lang('AllowedHTMLTags').'</a></small>';
		return $result;
	}
}
?>