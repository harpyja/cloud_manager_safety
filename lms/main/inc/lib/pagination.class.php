<?php  if ( ! defined('SYS_ROOT')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/pagination.html
 */
class Pagination {

	var $base_url			= ''; // The page we are linking to
	var $total_rows  		= ''; // Total number of items (database results)
	var $per_page	 		= 10; // Max number of items you want shown per page
	var $num_links			=  2; // Number of "digit" links to show before/after the currently viewed page
	var $cur_page	 		=  0; // The current page being viewed
	var $first_link   		= '&lsaquo; First';
	var $next_link			= '&gt;';
	var $prev_link			= '&lt;';
	var $last_link			= 'Last &rsaquo;';
	var $uri_segment		= 3;
	var $full_tag_open		= '';
	var $full_tag_close		= '';
	var $first_tag_open		= '';
	var $first_tag_close	= '';
	var $last_tag_open		= '';
	var $last_tag_close		= '';
	var $cur_tag_open		= '<strong>';
	var $cur_tag_close		= '</strong>';
	var $next_tag_open		= '&nbsp;';
	var $next_tag_close		= '&nbsp;';
	var $prev_tag_open		= '&nbsp;';
	var $prev_tag_close		= '';
	var $num_tag_open		= '&nbsp;';
	var $num_tag_close		= '';
	var $page_query_string	= FALSE;
	var $query_string_segment = 'offset';

	var $subfix_url='';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	function Pagination($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}

		api_log("Pagination Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}

	static function get_defult_config($total_rows,$base_url,$subfix_url='',$page_size=NUMBER_PAGE,$num_links=5){
		$config['base_url'] =$base_url;
		$config['subfix_url']="/".$subfix_url;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $page_size;	
		$config['num_links'] = $num_links;
		$config['page_query_string'] = TRUE;
		$config['cur_tag_open']='<li class="la"><a href="#">';
		$config['cur_tag_close']='</a></li>';
		$config['prev_link']=get_lang("prev_link_page");
		$config['next_link']=get_lang("next_link_page");
		$config['first_link']=get_lang("first_link_page");
		$config['last_link']=get_lang("last_link_page");
		//$config['last_link']="Last";
		return $config;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links()
	{
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}



		if ($this->page_query_string === TRUE)
		{
			if (getgpc($this->query_string_segment,"G") != 0)
			{
				$this->cur_page = getgpc($this->query_string_segment,"G");

				$this->cur_page = (int) $this->cur_page;
			}
		}
		else
		{
				
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			//show_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = 0;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->cur_page > $this->total_rows)
		{
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}

		$uri_page_number = $this->cur_page;
		$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// Is pagination being used over GET or POST?  If get, add a per_page query string. If post, add a trailing slash to the base URL if needed
		if ($this->page_query_string === TRUE)
		{
			if(strpos($this->base_url,"?")){
				$this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
			}else{
				$this->base_url = rtrim($this->base_url).'?'.$this->query_string_segment.'=';
			}
		}
		else
		{
				
		}

		// 创建分页链接
		$output = '';

		// Render the "First" link  第1页
		if  ($this->cur_page > ($this->num_links + 1))
		{
			$output .= $this->first_tag_open.'<li class="next"><a href="'.$this->base_url.'">'.$this->first_link.'</a></li>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  ($this->cur_page != 1)
		{
			$i = $uri_page_number - $this->per_page;
			if ($i == 0) $i = ''; //liyu
			$output .= $this->prev_tag_open.'<li class="next"><a href="'.$this->base_url.$i.'">'.$this->prev_link.'</a></li>'.$this->prev_tag_close;
		}

		// 数字页码
		for ($loop = $start -1; $loop <= $end; $loop++)
		{
			$i = ($loop * $this->per_page) - $this->per_page;

			if ($i >= 0)
			{
				if ($this->cur_page == $loop)
				{
					$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
				}
				else
				{
					//$n = ($i == 0) ? '' : $i;
					//$n=$i;  //liyu
					//$output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.$this->subfix_url.'">'.$loop.'</a>'.$this->num_tag_close;
					$n = ($i == 0) ? '' : $i;
					$output .= $this->num_tag_open.'<li><a href="'.$this->base_url.$n.'">'.$loop.'</a></li>'.$this->num_tag_close;
				}
			}
		}

		// 下一页
		if ($this->cur_page < $num_pages)
		{
			//$output .= $this->next_tag_open.'<a href="'.$this->base_url.($this->cur_page * $this->per_page).$this->subfix_url.'">'.$this->next_link.'</a>'.$this->next_tag_close;
			$output .= $this->next_tag_open.'<li class="last"><a href="'.$this->base_url.($this->cur_page * $this->per_page).'">'.$this->next_link.'</a></li>'.$this->next_tag_close;
		}

		// 最后一页
		if (($this->cur_page + $this->num_links) < $num_pages)
		{
			$i = (($num_pages * $this->per_page) - $this->per_page);
			//$output .= $this->last_tag_open.'<a href="'.$this->base_url.$i.$this->subfix_url.'">'.$this->last_link.'</a>'.$this->last_tag_close;
			$output .= $this->last_tag_open.'<li class="colophon"><a href="'.$this->base_url.$i.'">'.$this->last_link.'</a></li>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}
