<?php
require_once 'HTML/QuickForm/group.php';
require_once 'HTML/QuickForm/hidden.php';
require_once 'HTML/QuickForm/textarea.php';
require_once 'HTML/QuickForm/text.php';
require_once 'HTML/QuickForm/link.php';

class HTML_QuickForm_modaldialog_select extends HTML_QuickForm_group {
	var $debug = FALSE;
	
	var $_options = array ('is_multiple_line' => TRUE,
			'is_display_clear_btn' => TRUE,
			'MODULE_ID' => '',
			'TO_NAME' => 'TO_NAME',
			'TO_ID' => 'TO_ID',
			'selected_names' => '',
			'selected_ids' => '',
			'open_url' => '',
			'form_name' => '',
			'textarea_rows' => '3',
			'textarea_cols' => '50',
			'js_select_func_name' => 'select_data',
			'js_clear_func_name' => 'clear_data'
	/*'js_module_id'=>'',*/
	);
	
	function HTML_QuickForm_modaldialog_select($elementName = null, $elementLabel = null, $attributes = null, $options = array()) {
		/*$this->selected_names = $attributes['selected_names'];
		 $this->selected_ids = $attributes['selected_ids'];
		 unset($attributes['selected_names']);
		 unset($attributes['selected_ids']);*/
		$this->HTML_QuickForm_element ( $elementName, $elementLabel, $attributes );
		$this->_persistantFreeze = true;
		$this->_appendName = true;
		$this->_type = 'modaldialog_select';
		
		if (is_array ( $options )) {
			foreach ( $options as $name => $value ) {
				/*if (isset($this->_options[$name])) {
					if (is_array($value) && is_array($this->_options[$name])) {
						$this->_options[$name] = @array_merge($this->_options[$name], $value);
					} else {*/
				$this->_options [$name] = $value;
				//}
			//}
			}
		}
		//var_dump($this->_options);
	}
	
	/**
	 * Create the form elements to build this element group
	 */
	function _createElements() {
		if ($this->_options ['is_multiple_line']) {
			$this->_elements [] = new HTML_QuickForm_textarea ( $this->_options ['TO_NAME'], '', array ('id' => $this->_options ['TO_NAME'], 'cols' => $this->_options ['textarea_cols'], 'rows' => $this->_options ['textarea_rows'], 'readonly' => 'true', 'class' => 'inputText' ) );
		} else {
			$this->_elements [] = new HTML_QuickForm_text ( $this->_options ['TO_NAME'], '', array ('id' => $this->_options ['TO_NAME'], 'style' => 'width:250px', 'class' => 'inputText', 'readonly' => 'true' ) );
		}
		$this->_elements [0]->setValue ( $this->_options ['selected_names'] );
		
		if (! $this->debug) {
			$this->_elements [] = new HTML_QuickForm_hidden ( $this->_options ['TO_ID'], $this->_options ['selected_ids'], array ('id' => $this->_options ['TO_ID'] ) );
		} else {
			$this->_elements [] = new HTML_QuickForm_text ( $this->_options ['TO_ID'], '', array ('id' => $this->_options ['TO_ID'], 'value' => $this->_options ['selected_ids'], 'style' => 'width:20%' ) );
		}
		
		$this->_elements [] = new HTML_QuickForm_link ( 'add', '', 'javascript:;', "&nbsp;" . Display::return_icon ( 'search.gif', get_lang ( 'ModalDialogSelect' ) ), array ('id' => 'link_select',
				'style' => 'font-size:12px',
				'onclick' => 'javascript:' . $this->_options ['js_select_func_name'] . '(\'' . $this->_options ["MODULE_ID"] . '\',\'' . $this->_options ["TO_ID"] . '\',\'' . $this->_options ["TO_NAME"] . '\',\'' . $this->_options ["form_name"] . '\',\'' . $this->_options ["open_url"] . '\');' ) );
		if ($this->_options ['is_display_clear_btn']) $this->_elements [] = new HTML_QuickForm_link ( 'clear', '', 'javascript:;', "&nbsp;" . Display::return_icon ( 'delete.gif', get_lang ( 'ModalDialogClear' ) ), array ('id' => 'link_clear',
				'style' => 'font-size:12px',
				'onclick' => 'javascript:' . $this->_options ['js_clear_func_name'] . '(\'' . $this->_options ["TO_NAME"] . '\',\'' . $this->_options ["TO_ID"] . '\');' ) );
	}
	
	function toHtml() {
		include_once ('HTML/QuickForm/Renderer/Default.php');
		$this->_separator = '<br/>';
		$renderer = & new HTML_QuickForm_Renderer_Default ();
		$renderer->setElementTemplate ( '{element}' );
		parent::accept ( $renderer );
		$js = $this->getElementJS ();
		return $renderer->toHtml () . $js;
	}
	/**
	 * Get the necessary javascript
	 */
	function getElementJS() {
		$js = "<script type=\"text/javascript\">
					/* <![CDATA[ */
					
					/* ]]> */
					</script>\n";
		return $js;
	}
}
?>