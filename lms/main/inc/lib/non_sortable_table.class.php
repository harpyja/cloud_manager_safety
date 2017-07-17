<?php
/*
 ==============================================================================

 ==============================================================================
 */
require_once ("HTML/Table.php"); //See http://pear.php.net/package/HTML_Table
require_once ("Pager/Pager.php"); //See http://pear.php.net/package/Pager


/**
 * This class allows you to display a sortable data-table. It is possible to
 * split the data in several pages.
 * Using this class you can:
 * - automatically create checkboxes of the first table column
 * - a "select all" and "deselect all" link is added
 * - only if you provide a list of actions for the selected items
 * - click on the table header to sort the data
 * - choose how many items you see per page
 * - navigate through all data-pages
 */
class NonSortableTable extends HTML_Table {
	/**
	 * A name for this table
	 */
	var $table_name;
	
	/**
	 * 要显示的页面索引号
	 * The page to display
	 */
	var $page_nr;
	
	/**
	 * 每页显示记录数
	 * Number of items to display per page
	 */
	var $per_page;
	
	/**
	 * 默认每页显示记录数
	 * The default number of items to display per page
	 */
	var $default_items_per_page;
	
	/**
	 * URL参数
	 * A prefix for the URL-parameters, can be used on pages with multiple
	 * SortableTables
	 */
	var $param_prefix;
	
	/**
	 * PEAR 分页对象
	 * The pager object to split the data in several pages
	 */
	var $pager;
	
	/**
	 * 总记录数
	 * The total number of items in the table
	 */
	var $total_number_of_items;
	
	/**
	 * 获取总记录数的方法
	 * The function to get the total number of items
	 */
	var $get_total_number_function;
	
	/**
	 * 获取记录数据的方法
	 * The function to the the data to display
	 */
	var $get_data_function;
	
	/**
	 * An array with defined column-filters
	 */
	var $column_filters;
	
	/**
	 * 表单其它操作
	 * A list of actions which will be available through a select list
	 */
	var $form_actions;
	
	/**
	 * 链接额外参数
	 * Additional parameters to pass in the URL
	 */
	var $additional_parameters;
	
	/**
	 * Additional attributes for the th-tags
	 */
	var $th_attributes;
	
	/**
	 * Additional attributes for the td-tags
	 */
	var $td_attributes;
	
	/**
	 * Array with names of the other tables defined on the same page of this
	 * table
	 */
	var $other_tables;
	
	var $is_display_jump2page_html;
	var $is_display_pagesize_html;
	var $dispaly_style_navigation_bar;
	
	function __construct($table_name = 'table', $get_total_number_function = null, $get_data_function = null, $default_items_per_page = 20) {
		$this->NonSortableTable ( $table_name, $get_total_number_function, $get_data_function, $default_items_per_page );
	}
	
	/**
	 * Create a new SortableTable
	 * @param string $table_name A name for the table (default = 'table')
	 * @param string $get_total_number_function A user defined function to get
	 * the total number of items in the table
	 * @param string $get_data_function A function to get the data to display on
	 * the current page
	 * @param int $default_column The default column on which the data should be
	 * sorted
	 * @param int $default_items_per_page The default number of items to show
	 * on one page
	 * @param string $default_order_direction The default order direction;
	 * either the constant 'ASC' or 'DESC'
	 */
	function NonSortableTable($table_name = 'table', $get_total_number_function = null, $get_data_function = null, $default_items_per_page = 20) {
		parent::HTML_Table ( array ('class' => 'data_table' ) );
		$this->table_name = $table_name;
		$this->additional_parameters = array ();
		$this->param_prefix = $table_name . '_';
		
		$this->page_nr = isset ( $_SESSION [$this->param_prefix . 'page_nr'] ) ? $_SESSION [$this->param_prefix . 'page_nr'] : 1;
		$this->page_nr = isset ( $_GET [$this->param_prefix . 'page_nr'] ) ? $_GET [$this->param_prefix . 'page_nr'] : $this->page_nr;
		
		$this->per_page = isset ( $_SESSION [$this->param_prefix . 'per_page'] ) ? $_SESSION [$this->param_prefix . 'per_page'] : $default_items_per_page;
		$this->per_page = isset ( $_GET [$this->param_prefix . 'per_page'] ) ? $_GET [$this->param_prefix . 'per_page'] : $this->per_page;
		
		$_SESSION [$this->param_prefix . 'per_page'] = $this->per_page;
		
		$_SESSION [$this->param_prefix . 'page_nr'] = $this->page_nr;
		
		$this->pager = null;
		$this->default_items_per_page = $default_items_per_page;
		$this->total_number_of_items = - 1;
		$this->get_total_number_function = $get_total_number_function;
		$this->get_data_function = $get_data_function;
		$this->column_filters = array ();
		$this->form_actions = array ();
		$this->checkbox_name = null;
		$this->td_attributes = array ();
		$this->th_attributes = array ();
		$this->other_tables = array ();
		
		$this->is_display_jump2page_html = true;
		$this->is_display_pagesize_html = TRUE;
		$this->dispaly_style_navigation_bar = NAV_BAR_TOP;
	}
	
	/**
	 * Get the Pager object to split the showed data in several pages
	 */
	function get_pager() {
		if (is_null ( $this->pager )) {
			$total_number_of_items = $this->get_total_number_of_items ();
			$params ['mode'] = 'Sliding';
			$params ['perPage'] = $this->per_page;
			$params ['totalItems'] = $total_number_of_items;
			$params ['urlVar'] = $this->param_prefix . 'page_nr';
			$params ['currentPage'] = $this->page_nr;
			$params ['prevImg'] = Display::return_icon ( 'prev.png', '', array ('style' => 'vertical-align: middle;' ) );
			$params ['nextImg'] = Display::return_icon ( 'next.png', '', array ('style' => 'vertical-align: middle;' ) );
			$params ['firstPageText'] = Display::return_icon ( 'first.png', '', array ('style' => 'vertical-align: middle;' ) );
			$params ['lastPageText'] = Display::return_icon ( 'last.png', '', array ('style' => 'vertical-align: middle;' ) );
			$params ['firstPagePre'] = '';
			$params ['lastPagePre'] = '';
			$params ['firstPagePost'] = '';
			$params ['lastPagePost'] = '';
			$params ['spacesBeforeSeparator'] = '';
			$params ['spacesAfterSeparator'] = '';
			$query_vars = array_keys ( $_GET );
			$query_vars_needed = array ($this->param_prefix . 'per_page' );
			if (count ( $this->additional_parameters ) > 0) {
				$query_vars_needed = array_merge ( $query_vars_needed, array_keys ( $this->additional_parameters ) );
			}
			$query_vars_exclude = array_diff ( $query_vars, $query_vars_needed );
			$params ['excludeVars'] = $query_vars_exclude;
			$this->pager = & Pager::factory ( $params );
		}
		return $this->pager;
	}
	
	function display() {
		if ($this->dispaly_style_navigation_bar == NAV_BAR_TOP) {
			$this->display_top ();
		} else {
			$this->display_bottom ();
		}
	}
	
	/**
	 * Displays the table, complete with navigation buttons to browse through
	 * the data-pages.
	 */
	function display_all() {
		
		$empty_table = false;
		
		//没有数据时
		if ($this->get_total_number_of_items () == 0) {
			$cols = $this->getColCount ();
			$this->setCellAttributes ( 1, 0, 'style="font-style: italic;text-align:center;" colspan=' . $cols );
			
			if (api_is_xml_http_request () === true) {
				$message_empty = api_utf8_encode ( get_lang ( 'TheListIsEmpty' ) );
			} else {
				$message_empty = get_lang ( 'TheListIsEmpty' );
			}
			$this->setCellContents ( 1, 0, $message_empty );
			
			$empty_table = true;
		}
		
		//有数据时
		$html = '';
		if (! $empty_table) {
			if (count ( $this->form_actions ) > 0) {
				$html .= '<script language="JavaScript" type="text/javascript">
						/*<![CDATA[*/
							function setCheckbox(value) {
				 				d = document.form_' . $this->table_name . ';
				 				for (i = 0; i < d.elements.length; i++) {
				   					if (d.elements[i].type == "checkbox") {
									     d.elements[i].checked = value;
				   					}
				 				}
							}
																
							function check_all(cb_name){  
  								var items0=document.getElementsByName(cb_name);  		
	 							for (var i=0;i<items0.length;i++) {		 	
	 								items0[i].checked=document.getElementById("allbox_for").checked;	  
	 							}
	 							if(i==0){		   
	      							items0.checked=document.getElementById("allbox_for").checked;		  
	 							} 	
							}
							
							function check_one(el){
   								if(!el.checked)
      								document.getElementById("allbox_for").checked=false;
							}
																
							/*]]>*/
				</script>';
				$params = $this->get_sortable_table_param_string . '&amp;' . $this->get_additional_url_paramstring ();
				
				$html .= '<form method="post" action="' . $_SERVER ['PHP_SELF'] . '?' . $params . '" name="form_' . $this->table_name . '">';
			}
		}
		$html .= $this->get_all_table_html ();
		if (! $empty_table) {
			$html .= '<table style="width:100%;">';
			$html .= '<tr>';
			$html .= '<td colspan="2">';
			if (count ( $this->form_actions ) > 0) //左下角操作
{
				$html .= '<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'' . $this->checkbox_name . '[]' . '\');"><label for="allbox_for">' . get_lang ( 'SelectAll' ) . '/' . get_lang ( 'UnSelectAll' ) . '</label>&nbsp;&nbsp;';
				
				$html .= '<select name="action">';
				foreach ( $this->form_actions as $action => $label ) {
					$html .= '<option value="' . $action . '">' . $label . '</option>';
				}
				$html .= '</select>';
				$html .= '&nbsp;&nbsp;<button type="submit" class="save" onclick="javascript:if(!confirm(' . "'" . addslashes ( api_htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_QUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . get_lang ( 'Ok' ) . '</button>';
			}
			
			$html .= '</td>';
			
			$html .= '</tr>';
			$html .= '</table>';
			
			if (count ( $this->form_actions ) > 0) {
				$html .= '</form>';
			}
		}
		echo $html;
	}
	
	function get_all_table_html() {
		$table_data = $this->get_table_data ( 0 ); //数据表主体（不含表头）
		foreach ( $table_data as $index => $row ) {
			$row = $this->filter_data ( $row );
			$this->addRow ( $row );
		}
		$this->altRowAttributes ( 0, array ('class' => 'row_odd' ), array ('class' => 'row_even' ), true );
		foreach ( $this->th_attributes as $column => $attributes ) {
			$this->setCellAttributes ( 0, $column, $attributes );
		}
		foreach ( $this->td_attributes as $column => $attributes ) {
			$this->setColAttributes ( $column, $attributes );
		}
		return $this->toHTML ();
	}
	
	/**
	 * Displays the table, complete with navigation buttons to browse through
	 * the data-pages.
	 */
	function display_top() {
		$is_display_header_nav = true;
		$is_display_footer_nav = false;
		$empty_table = false;
		
		//没有数据时
		if ($this->get_total_number_of_items () == 0) {
			$cols = $this->getColCount ();
			$this->setCellAttributes ( 1, 0, 'style="font-style: italic;text-align:center;" colspan=' . $cols );
			
			//liyu:20091210
			//$this->setCellContents(1, 0, get_lang('TheListIsEmpty'));
			if (api_is_xml_http_request () === true) {
				$message_empty = api_utf8_encode ( get_lang ( 'TheListIsEmpty' ) );
			} else {
				$message_empty = get_lang ( 'TheListIsEmpty' );
			}
			$this->setCellContents ( 1, 0, $message_empty );
			
			$empty_table = true;
		}
		
		//有数据时
		$html = '';
		if (! $empty_table) {
			$form = $this->get_page_select_form ();
			$nav = $this->get_navigation_html ();
			if ($is_display_header_nav) {
				$html = '<table style="width:100%;">';
				$html .= '<tr>';
				//$html .= '<td style="width:25%;">';
				//$html .= $form; //左上角每页显示记录数表单
				//$html .= '</td>';
				$html .= '<td style="text-align:left;">';
				$html .= $this->get_table_title ();
				$html .= '</td>';
				$html .= '<td style="text-align:right;">';
				$html .= $nav;
				$html .= $this->get_jump2page_html (); //liyu:直接跳转到某页
				$html .= str_repeat ( "&nbsp;", 2 ) . $form;
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '</table>';
			}
			if (count ( $this->form_actions ) > 0) {
				$html .= '<script language="JavaScript" type="text/javascript">
						/*<![CDATA[*/
							function setCheckbox(value) {
				 				d = document.form_' . $this->table_name . ';
				 				for (i = 0; i < d.elements.length; i++) {
				   					if (d.elements[i].type == "checkbox") {
									     d.elements[i].checked = value;
				   					}
				 				}
							}
																
							function check_all(cb_name){  
  								var items0=document.getElementsByName(cb_name);  		
	 							for (var i=0;i<items0.length;i++) {		 	
	 								items0[i].checked=document.getElementById("allbox_for").checked;	  
	 							}
	 							if(i==0){		   
	      							items0.checked=document.getElementById("allbox_for").checked;		  
	 							} 	
							}
							
							function check_one(el){
   								if(!el.checked)
      								document.getElementById("allbox_for").checked=false;
							}
																
							/*]]>*/
				</script>';
				$params = $this->get_sortable_table_param_string . '&amp;' . $this->get_additional_url_paramstring ();
				
				$html .= '<form method="post" action="' . $_SERVER ['PHP_SELF'] . '?' . $params . '" name="form_' . $this->table_name . '">';
			}
		}
		$html .= $this->get_table_html ();
		if (! $empty_table) {
			$html .= '<table style="width:100%;">';
			$html .= '<tr>';
			$html .= '<td colspan="2">';
			if (count ( $this->form_actions ) > 0) //左下角操作
{
				//$html .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$params.'" name="form_'.$this->table_name.'">';
				

				//liyu:
				//$html .= '<a href="?'.$params.'&amp;'.$this->param_prefix.'selectall=1" onclick="javascript:setCheckbox(true);return false;">'.get_lang('SelectAll').'</a> - ';
				//$html .= '<a href="?'.$params.'" onclick="javascript:setCheckbox(false);return false;">'.get_lang('UnSelectAll').'</a> ';
				

				$html .= '<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'' . $this->checkbox_name . '[]' . '\');"><label for="allbox_for">' . get_lang ( 'SelectAll' ) . '/' . get_lang ( 'UnSelectAll' ) . '</label>&nbsp;&nbsp;';
				
				$html .= '<select name="action">';
				foreach ( $this->form_actions as $action => $label ) {
					$html .= '<option value="' . $action . '">' . $label . '</option>';
				}
				$html .= '</select>';
				
				//$html .= '&nbsp;&nbsp;<input type="submit" class="inputSubmit" value="'.get_lang('Ok').'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"), ENT_NOQUOTES, SYSTEM_CHARSET))."'".')) return false;"/>';
				$html .= '&nbsp;&nbsp;<button type="submit" class="save" onclick="javascript:if(!confirm(' . "'" . addslashes ( api_htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_QUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . get_lang ( 'Ok' ) . '</button>';
				
			//$html .= '</form>';
			} else {
				//liyu: 屏蔽左下角的每页显示行设置
			//$html .= $form;
			}
			$html .= '</td>';
			
			if ($is_display_footer_nav) {
				$html .= '<td style="text-align:center;">';
				$html .= $this->get_table_title ();
				$html .= '</td>';
				
				$html .= '<td style="text-align:right;">';
				$html .= $nav;
				$html .= $this->get_jump2page_html (); //liyu:直接跳转到某页	
				$html .= '</td>';
			
			}
			$html .= '</tr>';
			$html .= '</table>';
			
			if (count ( $this->form_actions ) > 0) {
				$html .= '</form>';
			}
		}
		echo $html;
	}
	
	function display_bottom() {
		$is_display_header_nav = true;
		$is_display_footer_nav = false;
		$empty_table = false;
		
		//没有数据时
		if ($this->get_total_number_of_items () == 0) {
			$cols = $this->getColCount ();
			$this->setCellAttributes ( 1, 0, 'style="font-style: italic;text-align:center;" colspan=' . $cols );
			$this->setCellContents ( 1, 0, get_lang ( 'TheListIsEmpty' ) );
			$empty_table = true;
		}
		//有数据时
		if (! $empty_table) {
			$form = $this->get_page_select_form ();
			$nav = $this->get_navigation_html ();
			
			if (count ( $this->form_actions ) > 0) {
				$html .= '<script language="JavaScript" type="text/javascript">
						/*<![CDATA[*/
							function setCheckbox(value) {
				 				d = document.form_' . $this->table_name . ';
				 				for (i = 0; i < d.elements.length; i++) {
				   					if (d.elements[i].type == "checkbox") {
									     d.elements[i].checked = value;
				   					}
				 				}
							}
																
							function check_all(cb_name){  
  								var items0=document.getElementsByName(cb_name);  		
	 							for (var i=0;i<items0.length;i++) {		 	
	 								items0[i].checked=document.getElementById("allbox_for").checked;	  
	 							}
	 							if(i==0){		   
	      							items0.checked=document.getElementById("allbox_for").checked;		  
	 							} 	
							}
							
							function check_one(el){
   								if(!el.checked)
      								document.getElementById("allbox_for").checked=false;
							}
																
							/*]]>*/
				</script>';
				$params = $this->get_sortable_table_param_string . '&amp;' . $this->get_additional_url_paramstring ();
				
				$html .= '<form method="post" action="' . $_SERVER ['PHP_SELF'] . '?' . $params . '" name="form_' . $this->table_name . '">';
			}
		}
		
		if (! $empty_table) {
			$html .= '<table style="width:100%;">';
			$html .= '<tr>';
			$html .= '<td colspan="2">';
			if (count ( $this->form_actions ) > 0) //左上角操作
{
				$html .= '<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'' . $this->checkbox_name . '[]' . '\');"><label for="allbox_for">' . get_lang ( 'SelectAll' ) . '/' . get_lang ( 'UnSelectAll' ) . '</label>&nbsp;&nbsp;';
				
				$html .= '<select name="action">';
				foreach ( $this->form_actions as $action => $label ) {
					$html .= '<option value="' . $action . '">' . $label . '</option>';
				}
				$html .= '</select>';
				$html .= '&nbsp;&nbsp;<input type="submit" class="inputSubmit" value="' . get_lang ( 'Ok' ) . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;"/>';
				//$html .= '&nbsp;&nbsp;<button type="submit" class="add" value="'.get_lang('Ok').'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"), ENT_NOQUOTES, SYSTEM_CHARSET))."'".')) return false;">'.get_lang('Ok')."</button>";
			

			}
			
			$html .= '</td>';
			
			$html .= '</tr>';
			$html .= '</table>';
		
		}
		$html .= $this->get_table_html ();
		if (! $empty_table) {
			if (count ( $this->form_actions ) > 0) {
				$html .= '</form>';
			}
			if ($is_display_header_nav) {
				$html .= '<table style="width:100%;">';
				$html .= '<tr>';
				
				$html .= '<td style="text-align:left;">';
				$html .= $this->get_table_title ();
				$html .= '</td>';
				$html .= '<td style="text-align:right;">';
				$html .= $nav;
				if ($this->is_display_jump2page_html) $html .= $this->get_jump2page_html (); //liyu:直接跳转到某页
				if ($this->is_display_pagesize_html) $html .= str_repeat ( "&nbsp;", 2 ) . $form;
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '</table>';
			}
		}
		echo $html;
	}
	
	/**
	 * 右上角导航链接
	 * Get the HTML-code with the navigational buttons to browse through the
	 * data-pages.
	 */
	function get_navigation_html() {
		$pager = $this->get_pager ();
		$pager_links = $pager->getLinks ();
		$showed_items = $pager->getOffsetByPageId ();
		$nav = $pager_links ['first'] . ' ' . $pager_links ['back'];
		$nav .= ' ' . $pager->getCurrentPageId () . ' / ' . $pager->numPages () . ' ';
		$nav .= $pager_links ['next'] . ' ' . $pager_links ['last'];
		return $nav;
	}
	
	/**
	 * 表格主数据显示
	 * Get the HTML-code with the data-table.
	 */
	function get_table_html() {
		$pager = $this->get_pager ();
		$offset = $pager->getOffsetByPageId ();
		$from = $offset [0] - 1;
		$table_data = $this->get_table_data ( $from );
		foreach ( $table_data as $index => $row ) {
			$row = $this->filter_data ( $row );
			$this->addRow ( $row );
		}
		$this->altRowAttributes ( 0, array ('class' => 'row_odd' ), array ('class' => 'row_even' ), true );
		foreach ( $this->th_attributes as $column => $attributes ) {
			$this->setCellAttributes ( 0, $column, $attributes );
		}
		foreach ( $this->td_attributes as $column => $attributes ) {
			$this->setColAttributes ( $column, $attributes );
		}
		return $this->toHTML ();
	}
	
	/**
	 * liyu:直接跳转到某页
	 *
	 */
	function get_jump2page_html() {
		$total_number_of_items = $this->get_total_number_of_items ();
		if ($total_number_of_items <= $this->default_items_per_page) {
			return '';
		}
		$nav = '<form method="get" action="' . $_SERVER ['PHP_SELF'] . '" style="display:inline;">';
		$nav .= str_repeat ( "&nbsp;", 4 ) . '<input type="text" name="' . $this->param_prefix . 'page_nr' . '" style="text-align:right;width:35px" onfocus="this.select();"/>';
		//$nav .='<span><button type="submit" value="GO" class="simple">GO</button></span>';
		$nav .= '&nbsp;<input type="submit" value="GO" class="inputSubShort"/>';
		
		$param [$this->param_prefix . 'per_page'] = $this->per_page;
		if (is_array ( $this->additional_parameters )) $param = array_merge ( $param, $this->additional_parameters );
		foreach ( $param as $key => $value ) {
			$nav .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
		}
		$nav .= "</form>";
		return $nav;
	}
	
	/**
	 * 每页显示记录数下拉框表单 
	 * Get the HTML-code wich represents a form to select how many items a page
	 * should contain.
	 */
	function get_page_select_form() {
		$total_number_of_items = $this->get_total_number_of_items ();
		if ($total_number_of_items <= $this->default_items_per_page) {
			return '';
		}
		$result [] = '<form method="get" action="' . $_SERVER ['PHP_SELF'] . '" style="display:inline;">';
		
		$param [$this->param_prefix . 'page_nr'] = $this->page_nr;
		if (is_array ( $this->additional_parameters )) $param = array_merge ( $param, $this->additional_parameters );
		foreach ( $param as $key => $value ) {
			$result [] = '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
		}
		$result [] = '<select name="' . $this->param_prefix . 'per_page" onchange="javascript:this.form.submit();" style="width:80px">';
		//		for ($nr = 10; $nr <= min(50, $total_number_of_items); $nr += 10)
		//		{
		//			$result[] = '<option value="'.$nr.'" '. ($nr == $this->per_page ? 'selected="selected"' : '').'>'.$nr.'</option>';
		//		}
		//		if ($total_number_of_items < 500)
		//		{
		//			$result[] = '<option value="'.$total_number_of_items.'" '. ($total_number_of_items == $this->per_page ? 'selected="selected"' : '').'>ALL</option>';
		//		}
		
			$pagesize=array(5,10,20,30,50,90,100,200,500);
			foreach ( $pagesize as $nr ) {
				$result [] = '<option value="' . $nr . '" ' . ($nr == $this->per_page ? 'selected="selected"' : '') . '>' . $nr . '</option>';
			}
		
		if ($total_number_of_items > 500 && $total_number_of_items <= 4000) {
			$result [] = '<option value="' . $total_number_of_items . '" ' . ($total_number_of_items == $this->per_page ? 'selected="selected"' : '') . '>所有</option>';
		}
		$result [] = '</select>';
		$result [] = '<noscript>';
		//$result[] = '<input type="submit" value="ok"/>';
		$result [] = '<button class="save" type="submit">' . get_lang ( 'Save' ) . '</button>';
		$result [] = '</noscript>';
		$result [] = '</form>';
		$result = implode ( "\n", $result );
		return $result;
	}
	
	/**
	 * 表格相关信息：记录索引号范围/总记录数
	 * Get the table title.
	 */
	function get_table_title() {
		$pager = $this->get_pager ();
		$showed_items = $pager->getOffsetByPageId ();
		return '<span title="' . get_lang ( 'PagerIndexRange' ) . '/' . get_lang ( 'TotalRecords' ) . '">' . $showed_items [0] . ' - ' . $showed_items [1] . ' / ' . $this->get_total_number_of_items () . '</span>'; //liyu
	}
	
	/**
	 * Set the header-label
	 * @param int $column The column number
	 * @param string $label The label
	 * @param boolean $sortable Is the table sortable by this column? (defatult
	 * = true)
	 * @param string $th_attributes Additional attributes for the th-tag of the
	 * table header
	 * @param string $td_attributes Additional attributes for the td-tags of the
	 * column
	 */
	function set_header($column, $label, $th_attributes = null, $td_attributes = null) {
		
		$param ['page_nr'] = $this->page_nr;
		$param ['per_page'] = $this->per_page;
		
		$link = $label;
		
		$this->setHeaderContents ( 0, $column, $link );
		if (! is_null ( $td_attributes )) {
			$this->td_attributes [$column] = $td_attributes;
		}
		if (! is_null ( $th_attributes )) {
			$this->th_attributes [$column] = $th_attributes;
		}
	}
	
	/**
	 * Get the parameter-string with additional parameters to use in the URLs
	 * generated by this SortableTable
	 */
	function get_additional_url_paramstring() {
		$param_string_parts = array ();
		if (is_array ( $this->additional_parameters ) && count ( $this->additional_parameters ) > 0) {
			foreach ( $this->additional_parameters as $key => $value ) {
				$param_string_parts [] = urlencode ( $key ) . '=' . urlencode ( $value );
			}
		}
		$result = implode ( '&amp;', $param_string_parts );
		foreach ( $this->other_tables as $index => $tablename ) {
			
			if (isset ( $_GET [$tablename . '_page_nr'] )) $param [$tablename . '_page_nr'] = $_GET [$tablename . '_page_nr'];
			if (isset ( $_GET [$tablename . '_per_page'] )) $param [$tablename . '_per_page'] = $_GET [$tablename . '_per_page'];
			
			$param_string_parts = array ();
			foreach ( $param as $key => $value ) {
				$param_string_parts [] = urlencode ( $key ) . '=' . urlencode ( $value );
			}
			if (count ( $param_string_parts ) > 0) $result .= '&amp;' . implode ( '&amp;', $param_string_parts );
		}
		return $result;
	}
	
	/**
	 * Get the parameter-string with the SortableTable-related parameters to use
	 * in URLs
	 */
	function get_sortable_table_param_string() {
		
		$param [$this->param_prefix . 'page_nr'] = $this->page_nr;
		$param [$this->param_prefix . 'per_page'] = $this->per_page;
		
		$param_string_parts = array ();
		foreach ( $param as $key => $value ) {
			$param_string_parts [] = urlencode ( $key ) . '=' . urlencode ( $value );
		}
		$res = implode ( '&amp;', $param_string_parts );
		return $res;
	
	}
	
	/**
	 * Add a filter to a column. If another filter was allready defined for the
	 * given column, it will be overwritten.
	 * @param int $column The number of the column
	 * @param string $function The name of the filter-function. This should be a
	 * function wich requires 1 parameter and returns the filtered value.
	 */
	function set_column_filter($column, $function) {
		$this->column_filters [$column] = $function;
	}
	
	/**
	 * Define a list of actions which can be performed on the table-date.
	 * If you define a list of actions, the first column of the table will be
	 * converted into checkboxes.
	 * @param array $actions A list of actions. The key is the name of the
	 * action. The value is the label to show in the select-box
	 * @param string $checkbox_name The name of the generated checkboxes. The
	 * value of the checkbox will be the value of the first column.
	 */
	function set_form_actions($actions, $checkbox_name = 'id') {
		$this->form_actions = $actions;
		$this->checkbox_name = $checkbox_name;
	}
	
	/**
	 * Define a list of additional parameters to use in the generated URLs
	 * @param array $parameters
	 */
	function set_additional_parameters($parameters) {
		$this->additional_parameters = $parameters;
	}
	
	/**
	 * Set other tables on the same page.
	 * If you have other sortable tables on the page displaying this sortable
	 * tables, you can define those other tables with this function. If you
	 * don't define the other tables, there sorting and pagination will return
	 * to their default state when sorting this table.
	 * @param array $tablenames An array of table names.
	 */
	function set_other_tables($tablenames) {
		$this->other_tables = $tablenames;
	}
	
	/**
	 * Transform all data in a table-row, using the filters defined by the
	 * function set_column_filter(...) defined elsewhere in this class.
	 * If you've defined actions, the first element of the given row will be
	 * converted into a checkbox
	 * @param array $row A row from the table.
	 */
	function filter_data($row) {
		$url_params = $this->get_sortable_table_param_string () . '&amp;' . $this->get_additional_url_paramstring ();
		foreach ( $this->column_filters as $column => $function ) {
			$row [$column] = call_user_func ( $function, $row [$column], $url_params, $row );
		}
		if (count ( $this->form_actions ) > 0) {
			if (strlen ( $row [0] ) > 0) {
				$row [0] = '<input type="checkbox" name="' . $this->checkbox_name . '[]" value="' . $row [0] . '"';
				if (isset ( $_GET [$this->param_prefix . 'selectall'] )) {
					$row [0] .= ' checked="checked"';
				}
				$row [0] .= '  onClick="javascript:check_one(self);" />';
			}
		}
		foreach ( $row as $index => $value ) {
			if (strlen ( $row [$index] ) == 0) {
				// zhong 20070802
				$row [$index] = '';
				//$row[$index] = 'N/A';
			}
		}
		return $row;
	}
	
	/**
	 * Get the total number of items. This function calls the function given as
	 * 2nd argument in the constructor of a SortableTable. Make sure your
	 * function has the same parameters as defined here.
	 */
	function get_total_number_of_items() {
		if ($this->total_number_of_items == - 1 && ! is_null ( $this->get_total_number_function )) {
			$this->total_number_of_items = call_user_func ( $this->get_total_number_function );
		}
		return $this->total_number_of_items;
	}
	
	/**
	 * Get the data to display.  This function calls the function given as
	 * 2nd argument in the constructor of a SortableTable. Make sure your
	 * function has the same parameters as defined here.
	 * @param int $from Index of the first item to return.
	 * @param int $per_page The number of items to return
	 * @param int $column The number of the column on which the data should be
	 * sorted
	 * @param string $direction In which order should the data be sorted (ASC
	 * or DESC)
	 */
	function get_table_data($from = null, $per_page = null) {
		if (! is_null ( $this->get_data_function )) {
			return call_user_func ( $this->get_data_function, $from, $this->per_page );
		}
		return array ();
	}
	
	function set_display_jump2page_html($is_display_jump2page_html = "true") {
		if (isset ( $is_display_jump2page_html ) and (strtolower ( $is_display_jump2page_html ) == "false")) {
			$this->is_display_jump2page_html = FALSE;
		} else {
			$this->is_display_jump2page_html = TRUE;
		}
	}
	
	function set_display_pagesize_html($is_display_pagesize_html = TRUE) {
		if (isset ( $is_display_pagesize_html ) and strtolower ( $is_display_pagesize_html ) == "false") {
			$this->is_display_pagesize_html = FALSE;
		
		} else {
			$this->is_display_pagesize_html = TRUE;
		}
	}
	
	function set_dispaly_style_navigation_bar($style = NAV_BAR_TOP) {
		$this->dispaly_style_navigation_bar = $style;
	}
}

/**
 * Sortable table which can be used for data available in an array
 */
class NonSortableTableFromArray extends NonSortableTable {
	/**
	 * The array containing all data for this table
	 */
	var $table_data;
	
	function __construct($table_data, $default_items_per_page = 20, $tablename = 'tablename') //liyu
{
		$this->NonSortableTableFromArray ( $table_data, $default_items_per_page, $tablename );
	}
	
	/**
	 * Constructor
	 * @param array $table_data
	 * @param int $default_column
	 * @param int $default_items_per_page
	 */
	function NonSortableTableFromArray($table_data, $default_items_per_page = 20, $tablename = 'tablename') //liyu
{
		parent::NonSortableTable ( $tablename, null, null, $default_items_per_page ); //liyu
		$this->table_data = $table_data;
	}
	
	/**
	 * Get table data to show on current page
	 * @see SortableTable#get_table_data
	 */
	function get_table_data($from = 1) {
		return array_slice ( $this->table_data, $from, $this->per_page );
	}
	
	/**
	 * Get total number of items
	 * @see SortableTable#get_total_number_of_items
	 */
	function get_total_number_of_items() {
		return count ( $this->table_data );
	}
}

?>