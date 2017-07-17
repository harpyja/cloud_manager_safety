<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$form = new FormValidator ( 'task','POST','auto_fz.php','');
$form->addElement ( 'text', 'how', "每组几人", array ('maxlength' => 50, 'style' => "width:30%; ", 'class' => 'inputText' ) );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

if ($form->validate ()) {
    $group           = $form->getSubmitValues ();
    $how        =$group['how'];
    $sql_up="update user set group_id=0 where status=5";
    api_sql_query($sql_up);

    function get_user(){

        $sql = "select `user_id`  FROM user  where group_id=0 and status =5";
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $arr= array ();
        while ( $arr = Database::fetch_row ( $res) ) {
            $arrs [] = $arr[0];
        }
        shuffle($arrs);
        return $arrs;
    }
    $a=  get_user();
//获取所有的分组
    $sql_gr = "select `id`  FROM group_user  ";
    $res_gr = api_sql_query ( $sql_gr, __FILE__, __LINE__ );
    $arr_gr= array ();
    while ( $arr_gr = Database::fetch_row ( $res_gr) ) {
        $arrs_gr [] = $arr_gr[0];
    }
    //print_r($arrs_gr);
    $num_gr=count($arrs_gr);

    for($i=1;$i<=$num_gr;$i++){
        $a=  get_user();
        for($j=0;$j<$how;$j++){
            $sql1="update user set group_id=".$i." where user_id=".$a[$j];
            $result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
        }
    }
    tb_close ( 'control_user_group.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();