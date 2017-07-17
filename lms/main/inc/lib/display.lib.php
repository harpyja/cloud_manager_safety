<?php
/*
 ==============================================================================

 ==============================================================================
 */
define ( 'HTML_WHITE', 'white' );
define ( 'ZLMSLIGHTGREY', '#fafafa' );
require_once (api_get_path ( LIBRARY_PATH ) . 'sortabletable.class.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'non_sortable_table.class.php');

class Display {

	function display_table($table_header, $table_data) {
		$html = '<table class="p-table"><tbody>';
		foreach ( $table_header as $table_element ) {
			$attr = self::_array_to_str ( $table_element [3] );
			$html .= "<th " . $attr . ">" . (is_array ( $table_element ) ? $table_element [0] : $table_element) . "</th>";
		}
		$html .= "</tr>\n";
		$idx = 0;
		if ($table_data && is_array ( $table_data )) {
			foreach ( $table_data as $table_row ) {
				$className = ($idx % 2 == 0 ? 'row_odd' : 'row_even');
				$html .= '<tr class="' . $className . '">';
				$col_idx = 0;
				foreach ( $table_row as $table_element ) {
					$attr = self::_array_to_str ( $table_header [$col_idx] [3] );
					$html .= "<td " . $attr . ">" . $table_element . "</td>";
					$col_idx ++;
				}
				$html .= "</tr>\n";
				$idx ++;
			}
		} else {
			$html .= '<tr><td colspan="' . (count ( $table_header )) . '" style="font-style: italic;text-align:center;">' . get_lang ( 'TheListIsEmpty' ) . '</td></tr>';
		}
		$html .= "</tbody></table><br>";
		return $html;
	}

    function display_category_table($table_header, $table_data) {
        $html = '<tr>';
        foreach ( $table_header as $table_element ) {
            $attr = self::_array_to_str ( $table_element [3] );
            $html .= "<th " . $attr . ">" . (is_array ( $table_element ) ? $table_element [0] : $table_element) . "</th>";
        }
        $html .= "</tr>\n";
        $idx = 0;
        if ($table_data && is_array ( $table_data )) {
            foreach ( $table_data as $table_row ) {
                $className = ($idx % 2 == 0 ? 'row_odd' : 'row_even');
                $html .= '<tr class="' . $className . '">';
               // $html .=' <tr> <td><table cellpadding="0" cellspacing="0" class="course-list"><tr class="' . $className . '">';
//
//
//                            <td class="course-win3">课程总数:339</td>
//                            <td class="course-win4"><a href="#" title="打开">打开</a> &nbsp;/&nbsp; <a href="#" title="修改">修改</a> &nbsp;/&nbsp; <a href="#" title="删除">删除</a></td>
//                        </tr>
//               ';

                $col_idx = 0;
                foreach ( $table_row as $table_element ) {
                    $attr = self::_array_to_str ( $table_header [$col_idx] [3] );
                    $html .= "<td " . $attr . ">" . $table_element . "</td>";


                    //dx
//                    $html .= '<td class="course-win1"><input name="" type="checkbox" value=""></td>';
//                    $html .= '<td class="course-win2"' . $attr . '>' . $table_element . '</td>';


                    $col_idx ++;
                }
                $html .= "</tr>\n";
                $idx ++;
            }
        } else {
            $html .= '<tr><td colspan="' . (count ( $table_header )) . '" style="font-style: italic;text-align:center;">' . get_lang ( 'TheListIsEmpty' ) . '</td></tr>';
        }
        $html .= "</tbody></table>";
        return $html;
    }

	function _array_to_str($attr) {
		$att = "";
		if (is_array ( $attr )) {
			foreach ( $attr as $key => $val )
				$att .= $key . '="' . $val . '" ';
		} else {
			$att = $attr;
		}
		return $att;
	}

	/**
	 * 显示消息
	 * @param string $message
	 * @param string $redirect 目标跳转URL
	 * @param int $box_type 	行为状态. 1手动关闭thickbox窗口,跳转父窗口页面; 
	 * 2手动关闭thickbox窗口,刷新父窗口页面; 
	 * 3手动/自动跳转目标页; 
	 * 4只显示信息;
	 * 0:手动关闭thickbox窗口,不刷新
	 * @param string $box_style 显示样式. normal, warning, confirm, error 
	 */
	public static function display_msgbox($message, $redirect = '', $box_style = 'success') {
		if (substr ( strtolower ( $redirect ), 0, 7 ) != 'http://') $redirect = URL_APPEND . $redirect;
		$data = array ('message' => urlencode ( $message ) );
		//$data = array ('redirect' => urlencode ( $redirect ), 'message' => urlencode ( $message ) );
		switch ($box_style) {
			case 'warning' :
				$data ['class'] = 'warning-message';
				$data ['icon'] = 'message_warning.png';
				break;
			case 'success' :
				$data ['class'] = 'confirmation-message';
				$data ['icon'] = 'message_confirmation.gif';
				break;
			case 'error' :
				$data ['class'] = 'error-message';
				$data ['icon'] = 'message_error.png';
				break;
			case 'normal' :
			default :
				$data ['class'] = 'normal-message';
				$data ['icon'] = 'message_normal.gif';
		}
		$redirect_url = api_add_url_querystring ( URL_APPEND . 'panel/default/msgbox.php', $data );
		api_redirect ( $redirect_url );
	}

	function display_progress_bar($percent = 0, $width = '98%', $disp_txt = TRUE) {
		$style = 'border: 1px solid #000000;float: left;height: 13px;margin: 2px;width: ' . $width . ';';
		$html = '<div style="' . $style . '"><div style="background: none repeat scroll 0% 0% rgb(0, 49, 92); height: 13px; width: ' . $percent . '%;"></div></div>';
		if ($disp_txt) $html .= '&nbsp;' . $percent . '%';
		return $html;
	}

	/**
	 * Displays the tool introduction of a tool.
	 *
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 * @param string $tool These are the constants that are used for indicating the tools
	 * @return html code for adding an introduction
	 */
	function display_introduction_section($tool) {
		$is_allowed_to_edit = api_is_allowed_to_edit ();
		$moduleId = $tool;
		if (api_get_setting ( 'enable_tool_introduction' ) == 'true' || $tool == TOOL_COURSE_HOMEPAGE) {
			include (api_get_path ( INCLUDE_PATH ) . "introductionSection.inc.php");
		}
	}

	/*
	 *	Displays a localised html file
	 *
	 *	tries to show the file "$full_file_name"."_".$language_interface.".html"
	 *	and if this does not exist, shows the file "$full_file_name".".html"
	 *
	 *	warning this function defines a global
	 *
	 *	@param $full_file_name, the (path) name of the file, without .html
	 */
	function display_localised_html_file($full_file_name) {
		global $language_interface;
		$localised_file_name = $full_file_name . "_" . $language_interface . ".html";
		$default_file_name = $full_file_name . ".html";
		if (file_exists ( $localised_file_name )) {
			include ($localised_file_name);
		} else {
			include ($default_file_name); //default
		}
	}

	/**
	 * Display simple html header of table.
	 */
	function display_table_header($column_header, $properties) {
		$width = (isset ( $properties ["width"] ) ? $properties ["width"] : "85%");
		$class = (isset ( $properties ["class"] ) ? $properties ["class"] : "class=\"data_table\"");
		$cellpadding = (isset ( $properties ["cellpadding"] ) ? $properties ["cellpadding"] : "4");
		$cellspacing = (isset ( $properties ["cellspacing"] ) ? $properties ["cellspacing"] : "0");
		$id = (isset ( $properties ['id'] ) ? 'id="' . $properties ['id'] . '"' : "");
		
		echo "<table $class border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\" width=\"$width\">\n";
		echo "<thead><tr $id $bgcolor>";
		foreach ( $column_header as $table_element ) {
			echo '<th ' . ($table_element [1] ? 'style="' . $table_element [1] . '"' : "") . '>' . $table_element [0] . "</th>";
		}
		echo "</tr></thead>\n";
		echo "<tbody>\n";
	}

	/**
	 * Display html header of table with several options.
	 *
	 * @param $properties, array with elements, all of which have defaults
	 * "width" - the table width, e.g. "100%", default is 85%
	 * "class"	 - the class to use for the table, e.g. "class=\"data_table\""
	 * "cellpadding"  - the extra border in each cell, e.g. "8",default is 4
	 * "cellspacing"  - the extra space between cells, default = 0
	 *
	 * @param column_header, array with the header elements.
	 * @author Roan Embrechts
	 * @version 1.01
	 */
	function display_complex_table_header($properties, $column_header) {
		$width = (isset ( $properties ["width"] ) ? $properties ["width"] : "85%");
		$class = (isset ( $properties ["class"] ) ? $properties ["class"] : "class=\"data_table\"");
		$cellpadding = (isset ( $properties ["cellpadding"] ) ? $properties ["cellpadding"] : "4");
		$cellspacing = (isset ( $properties ["cellspacing"] ) ? $properties ["cellspacing"] : "0");
		$id = (isset ( $properties ['id'] ) ? 'id="' . $properties ['id'] . '"' : "");
		
		//... add more properties as you see fit
		//api_display_debug_info("ZLMS light grey is " . ZLMSLIGHTGREY);
		$bgcolor = "bgcolor='" . ZLMSLIGHTGREY . "'";
		echo "<table $class border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\" width=\"$width\">\n";
		echo "<thead><tr $id $bgcolor>";
		foreach ( $column_header as $table_element ) {
			echo "<th>" . (is_array ( $table_element ) ? $table_element [0] : $table_element) . "</th>";
		}
		echo "</tr></thead>\n";
		echo "<tbody>\n";
	
	}

	/**
	 * Displays a table row.
	 *
	 * @param $bgcolor the background colour for the table
	 * @param $table_row an array with the row elements
	 * @param $is_alternating true: the row colours alternate, false otherwise
	 */
	function display_table_row($bgcolor, $table_row, $is_alternating = true) {
		echo "<tr $bgcolor>";
		foreach ( $table_row as $table_element ) {
			echo "<td>" . $table_element . "</td>";
		}
		echo "</tr>\n";
		if ($is_alternating) {
			if ($bgcolor == "bgcolor='" . HTML_WHITE . "'") {
				$bgcolor = "bgcolor='" . ZLMSLIGHTGREY . "'";
			} else {
				if ($bgcolor == "bgcolor='" . ZLMSLIGHTGREY . "'") {
					$bgcolor = "bgcolor='" . HTML_WHITE . "'";
				}
			}
		}
		return $bgcolor;
	}

	function disp_table_row($table_row = array(), $odd_or_even = 0, $properties = array()) {
		if ($table_row) {
			$id = $properties ['id'];
			$id_prefix = $properties ['id_prefix'];
			if ($properties ['is_alternating']) {
				if ($odd_or_even == 1) {
					$className = "row_odd";
				}
				if ($odd_or_even == 0) {
					$className = "row_even";
				}
			} else {
				$className = $properties ['class'];
			}
			$class = ($className ? ' class="' . $className . '"' : "");
			echo '<tr ' . ($id ? 'id="' . $id_prefix . $id . '"' : "") . $class . ">";
			foreach ( $table_row as $table_element ) {
				echo "<td>" . $table_element . "</td>";
			}
			echo "</tr>\n";
		}
	}

	function display_alternating_table_row($table_row, $odd_or_even = 0, $id = "", $id_prefix = "") {
		if ($odd_or_even == 1) {
			$className = "row_odd";
		}
		if ($odd_or_even == 0) {
			$className = "row_even";
		}
		echo '<tr class="' . $className . '" ' . ($id ? 'id="' . $id_prefix . $id . '"' : "") . ">";
		foreach ( $table_row as $table_element ) {
			echo "<td>" . $table_element . "</td>";
		}
		echo "</tr>\n";
	}

	/**
	 * Displays a table row.
	 * This function has more options and is easier to extend than display_table_row()
	 *
	 * @param $properties, array with properties:
	 * ["bgcolor"] - the background colour for the table
	 * ["is_alternating"] - true: the row colours alternate, false otherwise
	 * ["align_row"] - an array with, per cell, left|center|right
	 * @todo add valign property
	 */
	function display_complex_table_row($properties, $table_row) {
		$bgcolor = $properties ["bgcolor"];
		$is_alternating = $properties ["is_alternating"];
		$align_row = $properties ["align_row"];
		echo "<tr $bgcolor>";
		$number_cells = count ( $table_row );
		for($i = 0; $i < $number_cells; $i ++) {
			$cell_data = $table_row [$i];
			$cell_align = $align_row [$i];
			echo "<td align=\"$cell_align\">" . $cell_data . "</td>";
		}
		echo "</tr>\n";
		if ($is_alternating) {
			if ($bgcolor == "bgcolor='" . HTML_WHITE . "'")
				$bgcolor = "bgcolor='" . ZLMSLIGHTGREY . "'";
			else if ($bgcolor == "bgcolor='" . ZLMSLIGHTGREY . "'") $bgcolor = "bgcolor='" . HTML_WHITE . "'";
		}
		return $bgcolor;
	}

	/**
	 * display html footer of table
	 */
	function display_table_footer() {
		echo "</tbody></table>";
	}

	function table_tr($label, $element_html, $element_id, $is_required = false) {
		$html = '<tr class="containerBody"><td class="formLabel">';
		if ($is_required) $html .= '<span class="form_required">*</span>';
		$html .= $label . '</td>
		<td class="formTableTd" align="left">' . $element_html;
		if ($element_id) $html .= '<div id="' . $element_id . 'Tip"></div>';
		$html .= '</td></tr>' . "\r\n";
		return $html;
	}

	/**
	 * Display a table
	 * @param array $header Titles for the table header
	 * each item in this array can contain 3 values
	 * - 1st element: the column title
	 * - 2nd element: true or false (column sortable?)
	 * - 3th element: additional attributes for
	 * th-tag (eg for column-width)
	 * - 4the element: additional attributes for the td-tags
	 * @param array $content 2D-array with the tables content
	 * @param array $sorting_options Keys are:
	 * 'column' = The column to use as sort-key
	 * 'direction' = SORT_ASC or SORT_DESC
	 * @param array $paging_options Keys are:
	 * 'per_page_default' = items per page when switching from
	 * full-	list to per-page-view
	 * 'per_page' = number of items to show per page
	 * 'page_nr' = The page to display
	 * @param array $query_vars Additional variables to add in the query-string
	 * @author bart.mollet@hogent.be
	 */
	function display_sortable_table($header, $content, $sorting_options = array (), $paging_options = array (), $query_vars = null, $form_actions = array(), $disp_nav_bar_style = NAV_BAR_BOTTOM) {
		global $origin;
		$column = isset ( $sorting_options ['column'] ) ? $sorting_options ['column'] : 0; //排序的列
		$default_items_per_page = isset ( $paging_options ['per_page'] ) ? $paging_options ['per_page'] : 10; //每页记录数
		$default_order_direction = isset ( $sorting_options ['default_order_direction'] ) ? $sorting_options ['default_order_direction'] : 'DESC'; //liyu: 排序方向
		$tablename = isset ( $paging_options ['tablename'] ) ? $paging_options ['tablename'] : 'tablename'; //表格名
		$table = new SortableTableFromArray ( $content, $column, $default_items_per_page, $default_order_direction, $tablename ); //liyu
		if (is_array ( $query_vars )) {
			$table->set_additional_parameters ( $query_vars );
		}
		foreach ( $header as $index => $header_item ) {
			$table->set_header ( $index, $header_item [0], $header_item [1], $header_item [2], $header_item [3] );
		}
		$table->set_form_actions ( $form_actions );
		if (isset ( $paging_options ['is_display_jump2page_html'] )) $table->set_display_jump2page_html ( $paging_options ['is_display_jump2page_html'] );
		if (isset ( $paging_options ['is_display_pagesize_html'] )) $table->set_display_pagesize_html ( $paging_options ['is_display_pagesize_html'] );
		$table->set_dispaly_style_navigation_bar ( $disp_nav_bar_style );
		$table->display ();
	}

	/**
	 * 不排序的分页表格
	 * @param $header
	 * @param $content
	 * @param $paging_options
	 * @param $query_vars
	 * @param $form_actions
	 * @return unknown_type
	 */
	function display_non_sortable_table($header, $content, $paging_options = array (), $query_vars = null, $form_actions = array(), $disp_nav_bar_style = NAV_BAR_BOTTOM) {
		global $origin;
		$default_items_per_page = isset ( $paging_options ['per_page'] ) ? $paging_options ['per_page'] : 10; //每页记录数
		$tablename = isset ( $paging_options ['tablename'] ) ? $paging_options ['tablename'] : 'tablename'; //表格名
		$table = new NonSortableTableFromArray ( $content, $default_items_per_page, $tablename ); //liyu
		if (is_array ( $query_vars )) {
			$table->set_additional_parameters ( $query_vars );
		}
		foreach ( $header as $index => $header_item ) {
			$table->set_header ( $index, $header_item [0], $header_item [1], $header_item [2] );
		}
		$table->set_form_actions ( $form_actions );
		if (isset ( $paging_options ['is_display_jump2page_html'] )) $table->set_display_jump2page_html ( $paging_options ['is_display_jump2page_html'] );
		if (isset ( $paging_options ['is_display_pagesize_html'] )) $table->set_display_pagesize_html ( $paging_options ['is_display_pagesize_html'] );
		$table->set_dispaly_style_navigation_bar ( $disp_nav_bar_style );
		//if($display_all){
		//$table->display_all();
		//}else{
		$table->display ();
	
		//}
	}

	function display_sortable_table_array($header, $table_data, $sorting_options = array (), $paging_options = array (), $query_vars = null, $form_actions = array(), $disp_nav_bar_style = NAV_BAR_TOP) {
		//$column = isset ( $sorting_options ['column'] ) ? $sorting_options ['column'] : 0; //排序的列
		

		$default_items_per_page = isset ( $paging_options ['per_page'] ) ? $paging_options ['per_page'] : 10; //每页记录数
		$default_order_direction = isset ( $sorting_options ['default_order_direction'] ) ? $sorting_options ['default_order_direction'] : 'DESC'; //liyu: 排序方向
		$tablename = isset ( $paging_options ['tablename'] ) ? $paging_options ['tablename'] : 'tablename'; //表格名
		

		$table = new SortableTableFromArrayConfig ( $table_data, $default_column, $default_items_per_page, $tablename, null, null, $default_order_direction ); //liyu
		

		if (is_array ( $query_vars )) {
			$table->set_additional_parameters ( $query_vars );
		}
		foreach ( $header as $index => $header_item ) {
			$table->set_header ( $index, $header_item [0], $header_item [1], $header_item [2], $header_item [3] );
		}
		$table->set_form_actions ( $form_actions );
		if (isset ( $paging_options ['is_display_jump2page_html'] )) $table->set_display_jump2page_html ( $paging_options ['is_display_jump2page_html'] );
		if (isset ( $paging_options ['is_display_pagesize_html'] )) $table->set_display_pagesize_html ( $paging_options ['is_display_pagesize_html'] );
		$table->set_dispaly_style_navigation_bar ( $disp_nav_bar_style );
		$table->display ();
	}

	/**
	 * Display a table with a special configuration
	 * @param array $header Titles for the table header
	 * each item in this array can contain 3 values
	 * - 1st element: the column title
	 * - 2nd element: true or false (column sortable?)
	 * - 3th element: additional attributes for
	 * th-tag (eg for column-width)
	 * - 4the element: additional attributes for the td-tags
	 * @param array $content 2D-array with the tables content
	 * @param array $sorting_options Keys are:
	 * 'column' = The column to use as sort-key
	 * 'direction' = SORT_ASC or SORT_DESC
	 * @param array $paging_options Keys are:
	 * 'per_page_default' = items per page when switching from
	 * full-	list to per-page-view
	 * 'per_page' = number of items to show per page
	 * 'page_nr' = The page to display
	 * @param array $query_vars Additional variables to add in the query-string
	 * @param array $column_show Array of binaries 1= show columns 0. hide a column
	 * @param array $column_order An array of integers that let us decide how the columns are going to be sort.
	 * i.e:  $column_order=array('1''4','3','4'); The 2nd column will be order like the 4th column
	 * @param array $form_actions Set optional forms actions
	 *
	 * @author Julio Montoya
	 */
	
	public function display_sortable_config_table($header, $content, $sorting_options = array (), $paging_options = array (), $query_vars = null, $column_show = array(), $column_order = array(), $form_actions = array()) {
		global $origin;
		$column = isset ( $sorting_options ['column'] ) ? $sorting_options ['column'] : 0;
		$default_items_per_page = isset ( $paging_options ['per_page'] ) ? $paging_options ['per_page'] : 20;
		
		$table = new SortableTableFromArrayConfig ( $content, $column, $default_items_per_page, 'tablename', $column_show, $column_order );
		
		if (is_array ( $query_vars )) {
			$table->set_additional_parameters ( $query_vars );
		}
		// show or hide the columns header
		if (is_array ( $column_show )) {
			for($i = 0; $i < count ( $column_show ); $i ++) {
				if (! empty ( $column_show [$i] )) {
					isset ( $header [$i] [0] ) ? $val0 = $header [$i] [0] : $val0 = null;
					isset ( $header [$i] [1] ) ? $val1 = $header [$i] [1] : $val1 = null;
					isset ( $header [$i] [2] ) ? $val2 = $header [$i] [2] : $val2 = null;
					isset ( $header [$i] [3] ) ? $val3 = $header [$i] [3] : $val3 = null;
					$table->set_header ( $i, $val0, $val1, $val2, $val3 );
				}
			}
		}
		$table->set_form_actions ( $form_actions );
		$table->display ();
	}

	/**
	 * Displays a normal message. It is recommended to use this function
	 * to display any normal information messages.
	 *
	 * @author Roan Embrechts
	 * @param string $message - include any additional html
	 * tags if you need them
	 * @param bool	Filter (true) or not (false)
	 * @return void
	 */
	function display_normal_message($message, $filter = true) {
		if ($filter) {
			//filter message
			$message = htmlentities ( $message, ENT_NOQUOTES, SYSTEM_CHARSET );
		}
		if (! headers_sent ()) {
			echo '<style type="text/css" media="screen, projection">
				/*<![CDATA[*/
				@import "' . api_get_path ( WEB_CSS_PATH ) . 'default.css";
				/*]]>*/
				</style>';
		}
		echo '<div style="margin-left:10px;margin-bottom:15px" class="onFocus">' . $message . '</div><div style="clear:both;margin-bottom:10px"></div>';
		/*echo '<div class="normal-message">';
		Display::display_icon ( 'message_normal.gif', '', array ('style' => 'float:left; margin-right:10px;' ) );
		echo "<div style='margin-left: 43px'>" . $message . '</div></div>';*/
	}

	/**
	 * Displays a normal message without Div. It is recommended to use this function
	 * to display any normal information messages without Div.
	 *
	 * @author guozhong
	 * @param string $message - include any additional html
	 * tags if you need them
	 * @param bool	Filter (true) or not (false)
	 * @return void
	 */
	function display_normal_message_table($message, $filter = true) {
		if ($filter) {
			//filter message
			$message = htmlentities ( $message, ENT_NOQUOTES, SYSTEM_CHARSET );
		}
		if (! headers_sent ()) {
			echo '<style type="text/css" media="screen, projection">
					/*<![CDATA[*/
					@import "' . api_get_path ( WEB_CSS_PATH ) . 'default.css";
					/*]]>*/
				</style>';
		}
		echo '<table border="0"><tr><td class="normal-message-table" style="width:12px">';
		Display::display_icon ( 'message_normal.gif', '', array ('style' => 'float:left; margin-right:10px;' ) );
		echo '</td><td class="normal-message-table">' . "$message</td></tr></table>";
	}

	/**
	 * Displays an warning message. Use this if you want to draw attention to something
	 * This can also be used for instance with the hint in the exercises
	 *
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 * @param string $message
	 * @param bool	Filter (true) or not (false)
	 * @return void
	 */
	function display_warning_message($message, $filter = true) {
		if ($filter) {
			//filter message
			$message = htmlentities ( $message, ENT_NOQUOTES, SYSTEM_CHARSET );
		}
		if (! headers_sent ()) {
			echo '<style type="text/css" media="screen, projection">
						/*<![CDATA[*/
						@import "' . api_get_path ( WEB_CSS_PATH ) . 'default.css";
						/*]]>*/
						</style>';
		}
		echo '<div class="warning-message">';
		Display::display_icon ( 'message_warning.png', '', array ('style' => 'float:left; margin-right:10px;' ) );
		echo $message . '</div>';
	}

	/**
	 * Displays an confirmation message. Use this if something has been done successfully
	 *
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 * @param string $message
	 * @param bool	Filter (true) or not (false)
	 * @return void
	 */
	function display_confirmation_message($message, $filter = true) {
		if ($filter) $message = htmlentities ( $message, ENT_NOQUOTES, SYSTEM_CHARSET );
		echo '<div style="margin-left:10px;margin-bottom:15px" class="onSuccess">' . $message . '</div><div style="clear:both;margin-bottom:5px"></div>';
	}

	/**
	 * Displays an error message. It is recommended to use this function if an error occurs
	 *
	 * @author Hugues Peeters
	 * @author Roan Embrechts
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 * @param string $message - include any additional html
	 * tags if you need them
	 * @param bool	Filter (true) or not (false)
	 * @return void
	 */
	function display_error_message($message, $filter = true) {
		if ($filter) {
			$message = htmlentities ( $message, ENT_NOQUOTES, SYSTEM_CHARSET );
		}
		
		if (! headers_sent ()) {
			echo '<style type="text/css" media="screen, projection">
						/*<![CDATA[*/
						@import "' . api_get_path ( WEB_CSS_PATH ) . 'default.css";
						/*]]>*/
						</style>';
		}
		echo '<div style="margin-left:10px;margin-bottom:15px" class="onError2">' . $message . '</div><div style="clear:both;margin-bottom:10px"></div>';
		/*echo '<div class="error-message">';
		Display::display_icon ( 'message_error.png', '', array ('style' => 'float:left; margin-right:10px;' ) );
		echo $message . '</div>';*/
	}

	/**
	 * Return an encrypted mailto hyperlink
	 *
	 * @param - $email (string) - e-mail
	 * @param - $text (string) - clickable text
	 * @param - $style_class (string) - optional, class from stylesheet
	 * @return - encrypted mailto hyperlink
	 */
	function encrypted_mailto_link($email, $clickable_text = null, $style_class = '') {
		if (is_null ( $clickable_text )) {
			$clickable_text = $email;
		}
		//mailto already present?
		if (substr ( $email, 0, 7 ) != 'mailto:') $email = 'mailto:' . $email;
		
		//class (stylesheet) defined?
		if ($style_class != '') $style_class = ' class="' . $style_class . '"';
		
		//encrypt email
		$hmail = '';
		for($i = 0; $i < strlen ( $email ); $i ++)
			$hmail .= '&#' . ord ( $email {$i} ) . ';';
		
		//encrypt clickable text if @ is present
		if (strpos ( $clickable_text, '@' )) {
			for($i = 0; $i < strlen ( $clickable_text ); $i ++)
				$hclickable_text .= '&#' . ord ( $clickable_text {$i} ) . ';';
		} else {
			$hclickable_text = htmlspecialchars ( $clickable_text );
		}
		
		//return encrypted mailto hyperlink
		return '<a href="' . $hmail . '"' . $style_class . '>' . $hclickable_text . '</a>';
	}

	/**
	 * Create a hyperlink to the platform homepage.
	 * @param string $name, the visible name of the hyperlink, default is sitename
	 * @return string with html code for hyperlink
	 */
	function get_platform_home_link_html($name = '') {
		if ($name == '') {
			$name = api_get_setting ( 'siteName' );
		}
		return "<a href=\"" . api_get_path ( WEB_PATH ) . "index.php\">$name</a>";
	}

	/**
	 * Display the page header
	 * @param string $tool_name The name of the page (will be showed in the
	 * page title)
	 * @param string $help
	 */
	function display_header($tool_name = '', $is_full = TRUE) {
		$nameTools = $tool_name;
		global $_plugins;
		global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $text_dir, $plugins, $_user, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $display_course_homepage_target;
		global $display_admin_menushortcuts;
		if ($is_full) {
			include (api_get_path ( INCLUDE_PATH ) . "header.inc.php");
		} else {
			include (api_get_path ( INCLUDE_PATH ) . "reduced_header.inc.php");
		}
	}

	function display_reduced_header($tool_name = '', $help = NULL) {
		$nameTools = $tool_name;
		global $_plugins;
		global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $text_dir, $plugins, $_user, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $display_course_homepage_target;
		global $display_admin_menushortcuts;
		include (api_get_path ( INCLUDE_PATH ) . "reduced_header.inc.php");
	
		//include_once (api_get_path ( INCLUDE_PATH ) . "header.inc.php");
	}

	/**
	 * Display the page footer
	 */
	function display_footer($is_full = FALSE) {
		global $ZLMS_version; //necessary to have the value accessible in the footer
		global $_plugins;
		global $no_swap_menu, $starttime;
		if ($is_full) {
			include (api_get_path ( INCLUDE_PATH ) . "footer.inc.php");
		} else {
			echo '</div></body></html>';
		}
	}

	function display_thickbox($inc_jquery = true, $echo = false) {
		/*if ($inc_jquery) {
			$js = import_assets ( "commons.js" );
			$js .= import_assets ( "jquery-latest.js" );
		}*/
        $js= '';
		$js .= import_assets ( "jquery-plugins/thickbox/thickbox.js" );
		$js .= import_assets ( "jquery-plugins/thickbox/thickbox.css", api_get_path ( WEB_JS_PATH ) );
		$js .= '<script type="text/javascript">
	$(document).ready( function() {
		imgLoader = new Image();// preload image
		imgLoader.src = "' . api_get_path ( WEB_JS_PATH ) . 'jquery-plugins/thickbox/loadingAnimation.gif";
	});</script>';
		if ($echo)
			echo $js;
		else return $js;
	}

	function display_kindeditor($element = 'description', $control_style = 'basic', $echo = false) {
		$js = '';
		if (! defined ( 'INCLUDE_KINDEDITOR_JS' )) {
			$js = import_assets ( 'kindeditor/kindeditor.js' );
			define ( 'INCLUDE_KINDEDITOR_JS', 1 );
		}
		$js .= '<script type="text/javascript">';
		if ($control_style == 'basic') {
			$js .= 'KE.show({
				id : "' . $element . '",
				resizeMode : 1,
				allowPreviewEmoticons : false,
				allowUpload : false,
				items : ["fontname", "fontsize", "|", "textcolor", "bgcolor", "bold", "italic", "underline",
				"removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist",
				"insertunorderedlist", "|", "emoticons", "image", "link"]
			});';
		} elseif ($control_style == 'normal') {
			$js .= 'KE.show({
				id : "' . $element . '",
				afterCreate : function(id) {
					KE.util.focus(id);
				}
			});';
		}
		$js .= '</script>';
		
		if ($echo)
			echo $js;
		else return $js;
	}

	function display_message_header() {
		include (api_get_path ( INCLUDE_PATH ) . "message_header.inc.php");
	}

	/**
	 * Print an <option>-list with all letters (A-Z).
	 * @param char $selected_letter The letter that should be selected
	 */
	function get_alphabet_options($selected_letter = '') {
		$result = '';
		for($i = 65; $i <= 90; $i ++) {
			$letter = chr ( $i );
			$result .= '<option value="' . $letter . '"';
			if ($selected_letter == $letter) {
				$result .= ' selected="selected"';
			}
			$result .= '>' . $letter . '</option>';
		}
		return $result;
	}

	function display_course_tool_shortcuts0($course_system_code, $only_content = FALSE, $target = '_self') {
		$web_code_path = api_get_path ( WEB_CODE_PATH );
		$action = '&nbsp;' . icon_href ( 'info3.gif', 'Course_description', $web_code_path . 'course_description/?cidReq=' . $course_system_code, $target );
		$action .= '&nbsp;' . icon_href ( 'lp_announcement.png', 'Announcement', $web_code_path . 'announcements/index.php?cidReq=' . $course_system_code, $target );
		$action .= '&nbsp;' . icon_href ( 'folder_document.gif', 'Document', $web_code_path . 'document/document.php?cidReq=' . $course_system_code, $target );
		$action .= '&nbsp;' . icon_href ( 'scorm.gif', 'Courseware', $web_code_path . 'scorm/lp_controller.php?tabAction=lp&cidReq=' . $course_system_code, $target );
		//$action .= '&nbsp;' . icon_href ( 'quiz.gif', 'Quiz', $web_code_path . 'exercice/exercice.php?cidReq=' . $course_system_code );
		if (! $only_content) {
			$action .= '&nbsp;' . icon_href ( 'members.gif', 'CourseUsers', $web_code_path . 'user/user.php?cidReq=' . $course_system_code, '', '_parent', $target );
			//$action .= '&nbsp;' . icon_href ( 'group.gif', 'Class_of_course', $web_code_path . 'course_class/class_list.php?cidReq=' . $course_system_code, '', '_parent' );
			//$action .= '&nbsp;&nbsp;' . icon_href ( 'forum.gif', 'Forum', '#' );
			$action .= '&nbsp;' . icon_href ( 'stats_access.gif', 'MyProgress', $web_code_path . 'reporting/stat_course_user.php?cidReq=' . $course_system_code, $target );
		}
		return $action;
	}

    //by changzf at 742-777 line on 2012/06/08
	function display_course_content($course_system_code, $only_content = FALSE) {
		$web_code_path = api_get_path ( WEB_CODE_PATH );
	    $action = link_button ( 'exercise22.png', '内容', $web_code_path . 'admin/course/course_edit.php?cidReq=' . $course_system_code, '100%', '98%', false );
		return $action;
	}
    function display_course_announcements($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action =  link_button ( 'announce_add.gif', 'CourseAnnouncement', $web_code_path . 'announcements/index.php?cidReq=' . $course_system_code, '90%', '90%', false );

        return $action;
    }
    function display_course_documents($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action =  link_button ( 'folder_document.gif', 'Document', $web_code_path . 'document/document.php?cidReq=' . $course_system_code, '90%', '90%', false );
        return $action;
    }
    function display_course_LearningDocument($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action =  link_button ( 'scorm.gif', 'Courseware', $web_code_path . 'courseware/cw_list.php?cidReq=' . $course_system_code, '90%', '94%', false );
        return $action;
    }
    function display_course_CourseExamination($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action =  link_button ( 'quiz_22.png', 'CourseExam', $web_code_path . 'exercice/course_exam_edit.php?cidReq=' . $course_system_code, '90%', '90%', false );
        return $action;
    }
    function display_course_CourseWork ($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action =  link_button ( 'works_22.png', 'Assignment', $web_code_path . 'assignment/index.php?cidReq=' . $course_system_code, '90%', '90%', false );
        if (! $only_content) {
            $action .=  link_button ( 'blog_user.gif', 'CourseUsers', $web_code_path . 'user/user.php?cidReq=' . $course_system_code, '90%', '90%', false );
            $action .=  link_button ( 'statistics.gif', 'MyProgress', $web_code_path . 'reporting/stat_course_user.php?cidReq=' . $course_system_code, '94%', '90%', false );
        }
        return $action;
    }

    //by changzf at 779-816 line on 2012/06/09
    function display_course_tool_shortcuts($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action = '&nbsp;' . link_button ( 'info3.gif', 'Course_description', $web_code_path . 'course_description/?cidReq=' . $course_system_code, '90%', '90%', false );
        $action .= '&nbsp;' . link_button ( 'announce_add.gif', 'CourseAnnouncement', $web_code_path . 'announcements/index.php?cidReq=' . $course_system_code, '90%', '90%', false );
        $action .= '&nbsp;' . link_button ( 'folder_document.gif', 'Document', $web_code_path . 'document/document.php?cidReq=' . $course_system_code, '90%', '90%', false );
        //$action .= '&nbsp;' . link_button ( 'scorm.gif', 'Courseware', $web_code_path . 'scorm/lp_controller.php?tabAction=lp&cidReq=' . $course_system_code, '90%', '90%', false );
        $action .= '&nbsp;' . link_button ( 'scorm.gif', 'Courseware', $web_code_path . 'courseware/cw_list.php?cidReq=' . $course_system_code, '90%', '94%', false );
        $action .= '&nbsp;' . link_button ( 'quiz_22.png', 'CourseExam', $web_code_path . 'exercice/course_exam_edit.php?cidReq=' . $course_system_code, '90%', '90%', false );
        $action .= '&nbsp;' . link_button ( 'works_22.png', 'Assignment', $web_code_path . 'assignment/index.php?cidReq=' . $course_system_code, '90%', '90%', false );
        //$action .= '&nbsp;' . icon_href ( 'quiz.gif', 'Quiz', $web_code_path . 'exercice/exercice.php?cidReq=' . $course_system_code );
        if (! $only_content) {
            $action .= '&nbsp;' . link_button ( 'blog_user.gif', 'CourseUsers', $web_code_path . 'user/user.php?cidReq=' . $course_system_code, '90%', '90%', false );
            //$action .= '&nbsp;' . icon_href ( 'group.gif', 'Class_of_course', $web_code_path . 'course_class/class_list.php?cidReq=' . $course_system_code, '', '_parent' );
            //$action .= '&nbsp;&nbsp;' . icon_href ( 'forum.gif', 'Forum', '#' );
            $action .= '&nbsp;' . link_button ( 'statistics.gif', 'MyProgress', $web_code_path . 'reporting/stat_course_user.php?cidReq=' . $course_system_code, '94%', '90%', false );
        }
        return $action;
    }
    function display_course_CourseWork1 ($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        $action = '&nbsp;' . link_button ( 'works_22.png', 'Assignment', $web_code_path . 'assignment/index.php?cidReq=' . $course_system_code, '90%', '90%', false );
        return $action;
    }
    function display_course_CourseUsers ($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        if (! $only_content) {
            $action = '&nbsp;' . link_button ( 'blog_user.gif', 'CourseUsers', $web_code_path . 'user/user.php?cidReq=' . $course_system_code, '90%', '90%', false );
        }
        return $action;
    }
    function display_course_Reporting ($course_system_code, $only_content = FALSE) {
        $web_code_path = api_get_path ( WEB_CODE_PATH );
        if (! $only_content) {
            $action = '&nbsp;' . link_button ( 'statistics.gif', 'MyProgress', $web_code_path . 'reporting/stat_course_user.php?cidReq=' . $course_system_code, '94%', '90%', false );
        }
        return $action;
    }

	/**
	 * This function displays an icon
	 * @param string $image the filename of the file (in the themes/ folder
	 * @param string $alt_text the alt text (probably a language variable)
	 * @param array additional attributes (for instance height, width, onclick, ...)
	 */
	function display_icon($image, $alt_text = '', $additional_attributes = array ()) {
		echo Display::return_icon ( $image, $alt_text, $additional_attributes );
	}

	/**
	 * This function returns the htmlcode for an icon
	 *
	 * @param string $image the filename of the file (in the themes/ folder
	 * @param string $alt_text the alt text (probably a language variable)
	 * @param array additional attributes (for instance height, width, onclick, ...)
	 *
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 * @version October 2006
	 */
	function return_icon($image, $alt_text = '', $additional_attributes = array()) {
		if (! empty ( $additional_attributes ) and is_array ( $additional_attributes )) {
			$attribute_list = '';
			foreach ( $additional_attributes as $key => $value ) {
				$attribute_list .= $key . '="' . $value . '" ';
			}
		}
		return '<img src="' . api_get_path ( WEB_IMAGE_PATH ) . $image . '" alt="' . $alt_text . '"  title="' . $alt_text . '" ' . $attribute_list . '  />';
	}

	/**
	 * Display name and lastname in a specific order
	 * @param string Firstname
	 * @param string Lastname
	 * @param string Title in the destination language (Dr, Mr, Miss, Sr, Sra, etc)
	 * @param string Optional format string (e.g. '%t. %l, %f')
	 * @author Carlos Vargas <carlos.vargas@ZLMS.com>
	 */
	public function user_name($fname, $lname, $title = '', $format = null) {
		if (empty ( $format )) {
			if (empty ( $fname ) or empty ( $lname )) {
				$user_name = $fname . $lname;
			} else {
				$user_name = $fname . ' ' . $lname;
			}
		} else {
			$find = array ('%t', '%f', '%l' );
			$repl = array ($title, $fname, $lname );
			$user_name = str_replace ( $find, $repl, $format );
		}
		return $user_name;
	}

	/**
	 * This function set the template with no border for Form
	 *
	 * @param string $image the filename of the file (in the themes/ folder
	 *
	 * @author sshgz
	 * @version May 2007
	 */
	function setTemplateNoBorder(& $form, $width) {
		$renderer = $form->defaultRenderer ();
		
		$form_template = <<<EOT
		<form {attributes}>
		<table align=center cellpadding="4" cellspacing="0" width="$width">
				    {content}
				  </table>
			</form>
			
EOT;
		$renderer->setFormTemplate ( $form_template );
		
		$element_template = <<<EOT
			<tr>
				<td align=right><!-- BEGIN required --><span class="form_required">*</span> <!-- END required --> {label}</td>
				<td align="left"><!-- BEGIN error --><span class="onError">{error}</span><br /><!-- END error --> {element}</td>
			</tr>
			
EOT;
		$renderer->setElementTemplate ( $element_template );
		
		$header_template = <<<EOT
			<tr><th colspan="2">{header}</th></tr>
EOT;
		$renderer->setHeaderTemplate ( $header_template );
		
		$required_note_template = <<<EOT
			<tr>
				<td>&nbsp;</td>
				<tdalign="right">{requiredNote}</td>
			</tr>
EOT;
		$renderer->setRequiredNoteTemplate ( $required_note_template );
	}

	/**
	 *
	 * @param $form
	 * @param $width
	 * @return unknown_type
	 * @since WebCS V1.4.0
	 */
	function setTemplateSettings(& $form, $width) {
		$renderer = $form->defaultRenderer ();
		
		$form_template = <<<EOT
		<form {attributes}>
		<table align="center" width="$width" cellpadding="4" cellspacing="0">
				    {content}
				  </table>
			</form>
			
EOT;
		$renderer->setFormTemplate ( $form_template );
		
		$element_template = <<<EOT
			<tr>
				<td class="settingcomment" align="left" style="width:45%;border-bottom:1px dotted #666;border-right:1px dotted #666">{label}</td>
				<td class="settingvalue" align="left" style="border-bottom:1px dotted #666">{element}</td>
			</tr>
			
			
EOT;
		$renderer->setElementTemplate ( $element_template );
		
		$header_template = <<<EOT
			<tr><th class="settingtitle" align="left" colspan="2">{header}</th></tr>
EOT;
		$renderer->setHeaderTemplate ( $header_template );
	}

	/**
	 * This function set the template with border for Form
	 *
	 * @param string $image the filename of the file (in the themes/ folder
	 *
	 * @author sshgz
	 * @version May 2007
	 */
	function setTemplateBorder(& $form, $width) {
		$renderer = $form->defaultRenderer ();
		
		$form_template = <<<EOT
			<form {attributes}>
			    <table cellpadding="4" cellspacing="0" width="100%" >
				     {content}
			    </table>
			</form>
EOT;
		
		if (isset ( $width )) $form_template = <<<EOT
		<form {attributes}>
		<table align="center" width="$width" cellpadding="4" cellspacing="0" id="systeminfo">
				     {content}
				    </table>
			</form>
EOT;
		
		$renderer->setFormTemplate ( $form_template );
		
		$element_template = <<<EOT
			<tr class="containerBody">
				<td class="formLabel"><!-- BEGIN required --><span class="form_required">*</span> <!-- END required --> {label}</td>
				<td class="formTableTd" align="left"> {element}&nbsp;&nbsp;<!-- BEGIN error --><span class="onError">{error}</span><!-- END error --></td>
			</tr>
			
EOT;
		$renderer->setElementTemplate ( $element_template );
		
		$header_template = <<<EOT
			<tr>
				<th class="formTableTh" colspan="2">{header}</th>
			</tr>
EOT;
		$renderer->setHeaderTemplate ( $header_template );
		
		$required_note_template = <<<EOT
			<tr class="containerBody">
				<!--<td class="formLabel">&nbsp;</td>-->
				<td class="formTableTd" style="border:0px">&nbsp;</td>
				<td class="formTableTd" align="right" style="border:0px">{requiredNote}</td>
			</tr>
EOT;
		$renderer->setRequiredNoteTemplate ( $required_note_template );
	}

	/**
	 * This function set the template with border for Form
	 *
	 * @param string $image the filename of the file (in the themes/ folder
	 *
	 * @author sshgz
	 * @version May 2007
	 */
	function setTemplateNoForm(& $form, $width) {
		$renderer = $form->defaultRenderer ();
		
		$form_template = <<<EOT
			    <table cellpadding="4" cellspacing="0" width="100%">
				     {content}
			    </table>
EOT;
		
		if (isset ( $width )) $form_template = <<<EOT
		<table align=center cellpadding="4" cellspacing="0" width="$width">
				     {content}
				    </table>
EOT;
		
		$renderer->setFormTemplate ( $form_template );
		
		$element_template = <<<EOT
			<tr class="containerBody">
				<td class="formLabel"><!-- BEGIN required --><span class="form_required">*</span> <!-- END required --> {label}</td>
				<td class="formTableTd" align="left"> {element}&nbsp;&nbsp;<!-- BEGIN error --><span class="onError">{error}</span><!-- END error --></td>
			</tr>
			
EOT;
		$renderer->setElementTemplate ( $element_template );
		
		$header_template = <<<EOT
			<tr>
				<th class="formTableTh" colspan="2">{header}</th>
			</tr>
EOT;
		$renderer->setHeaderTemplate ( $header_template );
		
		$required_note_template = <<<EOT
			<tr class="containerBody">
				<td class="formLabel">&nbsp;</td>
				<td class="formTableTd" align="right">{requiredNote}</td>
			</tr>
EOT;
		$renderer->setRequiredNoteTemplate ( $required_note_template );
	}

} //end class Display
?>
