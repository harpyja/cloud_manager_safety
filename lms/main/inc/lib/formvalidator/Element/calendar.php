<?php
/**
 * liyu:20091229 V1.4.0 因为JS在thickbox中问题，暂不使用
 */
require_once ('HTML/QuickForm/text.php');
class HTML_QuickForm_calendar extends HTML_QuickForm_text
{
	var $html_calendar_icon="";

	var $_options = array(
        'lang'         => 'en',	
	//'dateFmt'           => 'yyyy-MM-dd HH:mm:ss',
        'dateFmt'           => 'yyyy-MM-dd',
        'startDate'          => '',
        'alwaysUseStartDate'  => 'false',
        'isShowWeek'   => 'true',
		'highLineWeekDay'=>'true',
        'readOnly' => 'true',
        'skin'  => 'blue'
        );

        /**
         * Constructor
         */
        function HTML_QuickForm_calendar($elementName = null, $elementLabel = null, $attributes = null,$options = array())
        {
        	global $language_interface;
        	$js_form_name = $attributes['form_name'];
        	unset($attributes['form_name']);
        	$attributes['readonly']='readonly';
        	$attributes['class']='inputText';
        	//$attributes['size']='12';
        	$attributes['id']=$elementName;
        	HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        	$this->_persistantFreeze = true;
        	$this->_appendName = true;
        	$this->_type = 'calendar';
        	@ $editor_lang = Database :: get_language_isocode($language_interface);
        	if (empty ($editor_lang) )
        	{
        		$editor_lang = 'en';
        	}
        	$this->_options['lang'] = $editor_lang;

        	if (is_array($options)) {
        		foreach ($options as $name => $value) {
        			if ('lang' == $name) {
        				$this->_options['language'] = isset($this->_locale[$value])? $value: 'en';
        			}
        			elseif (isset($this->_options[$name])) {
        				if (is_array($value) && is_array($this->_options[$name])) {
        					$this->_options[$name] = @array_merge($this->_options[$name], $value);
        				} else {
        					$this->_options[$name] = $value;
        				}
        			}
        		}
        	}

        	$calendar_attr="";
        	if(is_array($this->_options)){
        		foreach($this->_options as $key=>$val){
        			$calendar_attr.=",".$key.":"."'".$val."'";
        		}
        	}

        	$this->html_calendar_icon = '&nbsp;'.Display::return_icon('calendar.gif', '',
        	array('style'=>'vertical-align:middle;','onclick'=>'WdatePicker({el:$dp.$(\''
        	.$elementName.'\')'.$calendar_attr.'});'));
        }

        function toHtml()
        {
        	$js = $this->getElementJS();
        	return $js.parent :: toHtml().$this->html_calendar_icon;
        }

        function getElementJS()
        {
        	$js = "\n";
        	if(!defined('CALENDAR_JAVASCRIPT_INCLUDED'))
        	{
        		define('CALENDAR_JAVASCRIPT_INCLUDED',1);
        		$js .= '<script src="';
        		$js .= api_get_path(WEB_CODE_PATH).'inc/lib/My97DatePicker/';
        		$js .= 'WdatePicker.js" type="text/javascript"></script>';
        		$js .= "\n";
        	}
        	return $js;
        }

        function _createOptionList($start, $end, $step = 1)
        {
        	for ($i = $start, $options = array(); $start > $end? $i >= $end: $i <= $end; $i += $step) {
        		$options[$i] = sprintf('%02d', $i);
        	}
        	return $options;
        }

}
?>