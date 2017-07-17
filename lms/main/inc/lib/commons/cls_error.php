<?php

/**
 * ECSHOP 用户级错误处理类
 * ============================================================================
 * 版权所有 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: cls_error.php 15013 2008-10-23 09:31:42Z testyang $
*/

if (!defined('ROOT_PATH'))
{
    die('Hacking attempt');
}

class ecs_error
{
    var $_message   = array();
    var $_template  = '';
    var $error_no   = 0;

    /**
     * 构造函数
     *
     * @access  public
     * @param   string  $tpl
     * @return  void
     */
    function __construct($tpl)
    {
        $this->ecs_error($tpl);
    }

    /**
     * 构造函数
     *
     * @access  public
     * @param   string  $tpl
     * @return  void
     */
    function ecs_error($tpl)
    {
        $this->_template = $tpl;
    }

    /**
     * 添加一条错误信息
     *
     * @access  public
     * @param   string  $msg
     * @param   integer $errno
     * @return  void
     */
    function add($msg, $errno=1)
    {
        if (is_array($msg))
        {
            $this->_message = array_merge($this->_message, $msg);
        }
        else
        {
            $this->_message[] = $msg;
        }

        $this->error_no     = $errno;
    }

    /**
     * 清空错误信息
     *
     * @access  public
     * @return  void
     */
    function clean()
    {
        $this->_message = array();
        $this->error_no = 0;
    }

    /**
     * 返回所有的错误信息的数组
     *
     * @access  public
     * @return  array
     */
    function get_all()
    {
        return $this->_message;
    }

    /**
     * 返回最后一条错误信息
     *
     * @access  public
     * @return  void
     */
    function last_message()
    {
        return array_slice($this->_message, -1);
    }

    /**
     * 显示错误信息
     *
     * @access  public
     * @param   string  $link
     * @param   string  $href
     * @return  void
     */
    function show($link = '', $href = '')
    {
        if ($this->error_no > 0)
        {
            $message = array();

            $message['link'] = (empty($link)) ? $GLOBALS['_LANG']['back_up_page'] : $link;
            $message['href'] = (empty($href)) ? 'javascript:history.back();' : $href;

            foreach ($this->_message AS $msg)
            {
                $message['content'] = '<div>' . htmlspecialchars($msg) . '</div>';
            }

            if (isset($GLOBALS['smarty']))
            {
                assign_template();
                $GLOBALS['smarty']->assign('message', $message);
                $GLOBALS['smarty']->display($this->_template);
            }
            else
            {
                die($message['content']);
            }

            exit;
        }
    }
}

?>