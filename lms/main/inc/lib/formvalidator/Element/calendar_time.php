<?php
/**
 * 使用： http://code.google.com/p/kimsoft-jscalendar/
 */
require_once 'HTML/QuickForm/group.php';
require_once 'HTML/QuickForm/select.php';
require_once ('HTML/QuickForm/text.php');
require_once 'HTML/QuickForm/html.php';
require_once 'HTML/QuickForm/image.php';

class HTML_QuickForm_calendar_time extends HTML_QuickForm_group
{
	var $html_calendar_icon="";

	var $_options = array(
        'language'         => 'en',
	//'format'           => 'dMY', //liyu
        'format'           => 'YMd',
        'minYear'          => 2001,
        'maxYear'          => 2010,
        'addEmptyOption'   => false,
        'emptyOptionValue' => '',
        'emptyOptionText'  => '&nbsp;',
        'optionIncrement'  => array('i' => 1, 's' => 1)
	);

	/**
	 * Constructor
	 */
	function HTML_QuickForm_calendar_time($elementName = null, $elementLabel = null, $options = array(),$attributes = null)
	{
		global $language_interface;
		unset($js_form_name);
		$js_form_name = $attributes['form_name'];
		unset($attributes['form_name']);
		$attributes['readonly']='readonly';
		$attributes['size']='12';
		$attributes['onclick']='new Calendar().show(this);';
		HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
		$this->_persistantFreeze = true;
		$this->_appendName = true;
		$this->_type = 'calendar_time';
		@ $editor_lang = Database :: get_language_isocode($language_interface);
		if (empty ($editor_lang) )
		{
			$editor_lang = 'en';
		}
		$this->_options['language'] = $editor_lang;
		$this->html_calendar_icon = '&nbsp;'.Display::return_icon('calendar.gif', '', array('style'=>'vertical-align:bottom;','onclick'=>'javascript:new Calendar().show(document.'.$js_form_name.'.'.$elementName.',document.'.$js_form_name.'.'.$elementName.');'));

		unset($this->_elements);
		$this->_elements[] =& new HTML_QuickForm_text('Date', $elementLabel, $this->getAttributes());
		$this->_elements[] =& new HTML_QuickForm_html($this->getElementJS());
		//$this->_elements[] =& new HTML_QuickForm_html($this->html_calendar_icon);
		//$this->_elements[] =& new HTML_QuickForm_image(null,api_get_path(WEB_IMG_PATH).'calendar.gif',array('style'=>'vertical-align:bottom;','onclick'=>'javascript:new Calendar().show(document.all.'.$elementName.',document.all.'.$elementName.');return false;'));
		$this->_elements[] =& new HTML_QuickForm_select("Hour", null, $this->_createOptionList(0, 23), null);
		$this->_elements[] =& new HTML_QuickForm_select("Minute", null, $this->_createOptionList(0, 59,1), null);
	}

	function toHtml()
	{
		//$js = $this->getElementJS();
		//return $js.parent :: toHtml().$this->html_calendar_icon;
		include_once('HTML/QuickForm/Renderer/Default.php');
		$renderer =& new HTML_QuickForm_Renderer_Default();
		$renderer->setElementTemplate('{element}');
		parent::accept($renderer);
		return $renderer->toHtml();

	}

	function getElementJS()
	{
		$js = "\n";
		$js .= '<script src="';
		$js .= api_get_path(WEB_CODE_PATH).'inc/lib/formvalidator/Element/';
		$js .= 'calendar.js" type="text/javascript"></script>';
		$js .= "\n";

		return $js;
	}

	function exportValue()
	{
		$values = parent::getValue();
		$date=$values['Date'];
		$h = $values['Hour'][0];
		$i = $values['Minute'][0];
		$h = $h < 10 ? '0'.$h : $h;
		$i = $i < 10 ? '0'.$i : $i;
		$datetime = $date.' '.$h.':'.$i.':00';
		$result[$this->getName()]= $datetime;
		return $result;
	}

	function setValue($value)
	{
		if (empty($value)) {
			$value = array();
		} elseif (is_scalar($value)) {
			/*if (!is_numeric($value)) {
				$value = strtotime($value);
			}*/
			
			$arr = explode(' ', $value);
			$arr2=explode(":",$arr[0]);
			$value = array(
                'Date' => $arr[0],
                'Hour' => $arr2[0],
                'Minute' => $arr2[1]                            
			);
		}
		parent::setValue($value);
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