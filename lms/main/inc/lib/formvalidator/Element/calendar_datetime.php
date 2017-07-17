<?php
//V1.4.0 引入
require_once ('HTML/QuickForm/text.php');
class HTML_QuickForm_calendar_datetime extends HTML_QuickForm_text
{
	var $html_calendar_icon="";

	var $_options = array(
        'default_after_days' => 30,
		'show_time'=>TRUE
	);


	function HTML_QuickForm_calendar_datetime($elementName = null, $elementLabel = null, $attributes = null,$options = array())
	{

		if (is_array($options)) {
			foreach ($options as $name => $value) {
				if (isset($this->_options[$name])) {
					if (is_array($value) && is_array($this->_options[$name])) {
						$this->_options[$name] = @array_merge($this->_options[$name], $value);
					} else {
						$this->_options[$name] = $value;
					}
				}
			}
		}
		
		$attributes['readonly']='readonly';
		$attributes['class']='inputText';
		$attributes['style']='width:120px;text-align:right';
		$attributes['id']=$elementName;
		if($this->_options['show_time']){
			$attributes['onclick']='showcalendar(event,this,true,\''.date('Y-m-d H:i').'\', \''.date('Y-m-d H:i',strtotime('+'.$this->_options['default_after_days'].' day')).'\')';
		}else{
			$attributes['onclick']='showcalendar(event,this,false,\''.date('Y-m-d H:i').'\', \''.date('Y-m-d H:i',strtotime('+'.$this->_options['default_after_days'].' day')).'\')';
		}
		HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
		$this->_persistantFreeze = true;
		$this->_appendName = true;
		$this->_type = 'calendar_datetime';


	}

	function toHtml()
	{
		$js = $this->getElementJS();
		return $js.parent :: toHtml();
	}

	function getElementJS()
	{
		$js = "\n";
		if(!defined('CALENDAR_DATETIME_JS_INCLUDED'))
		{
			define('CALENDAR_DATETIME_JS_INCLUDED',1);
			$js .="<div id=\"append_parent\"></div>";
			$js .= '<script src="';
			$js .= api_get_path(WEB_JS_PATH).'js_calendar.js"  type="text/javascript"></script>';
			$js .= "\n";			
		}
		return $js;
	}
	
	function exportValue(&$submitValues, $assoc = false)
    {
    	$value=parent::exportValue($submitValues,$assoc);
    	return $this->_options['show_time']?$value.":00":$value;    	
    }

}
?>