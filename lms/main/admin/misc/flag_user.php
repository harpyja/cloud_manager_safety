<?php
/**
==============================================================================
==============================================================================
 */
header("content-type:text/html;charset=utf-8");
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$_SESSION['flag_id'] =  htmlspecialchars($_GET ['id']);
$id = intval($_SESSION['flag_id']);
$objDept = new DeptManager ();

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( $tool_name, FALSE );

$action=htmlspecialchars($_GET ['action']);
//all user
$all_users =unserialize(Database::getval("select `user` from `flag` where `id`=".$id,__FILE__,__LINE__));
////all user2
//$all_users2 =unserialize(Database::getval("select `user` from `flag` where `id`=".$task_id,__FILE__,__LINE__));

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=htmlspecialchars($_GET ['id']);
            $task_id=htmlspecialchars($_GET ['task_id']);
            if ( isset($delete_id)){
                $all_users2 =unserialize(Database::getval("select `user` from `flag` where `id`=".$task_id,__FILE__,__LINE__));


                function array_remove(&$arr, $offset){
                    array_splice($arr, $offset, 1);
                }

                array_remove($all_users2, $delete_id);
//                var_dump($all_users2);

                //delete mysql
                $sql = "UPDATE  `vslab`.`flag` SET  `user` =  '".serialize($all_users2)."' WHERE  `flag`.`id` ={$task_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "flag_user.php?id=".$task_id;
                api_redirect ( $redirect_url );
            }
            break;
    }
}

if(isset($_POST['formSent']) && $_POST['formSent']==1){
    $str=getgpc('str');
    $uid=  intval(getgpc('task'));
    $red_tem=explode(",",$str);
     //var_dump($red_tem);
    $user_list=array();
    if($str!=''){
        foreach($red_tem as $k){
            $vms=explode("(",$k);
            $username=$vms[0];
            $firstname =$vms[1];
            $firstname =str_replace(')','',$firstname);
            $sql="select `user_id` from `user` where `username`='".$username."' and `firstname` ='".$firstname."'";
            $user_id=Database::getval($sql,__FILE__,__LINE__);
            $user_list[]=$user_id;

        }
        $all_users =unserialize(Database::getval("select `user` from `flag` where `id`=".$uid,__FILE__,__LINE__));

        $intersection = array_diff($all_users,$user_list);
        $result= array_merge($intersection,$user_list);

        $user_var=serialize($result);
    //    $user_var=serialize($user_list);
        $user_sql="UPDATE  `vslab`.`flag` SET  `user` =  '".$user_var."' WHERE  `flag`.`id` =".$uid;
        api_sql_query($user_sql,__FILE__,__LINE__);
    }
    $redirect_url = "flag_user.php?id=".$uid;
    api_redirect ( $redirect_url );
}
?>
<br>
<form name="theForm" method="post" action="flag_user.php" onsubmit="validate()">
    <input type="hidden" name="formSent" value="1" />
    <input type="hidden" name="task" value="<?php echo $id?>" />
    <table border="0" cellpadding="5" cellspacing="0" align="center"
           width="98%">

        <tr class="containerBody">
            <td class="formLabel">选择用户</td>
            <td style="text-align: left;" class="formTableTd">
                <table id="linkgoods-table" align="left">

                    <tr>
                        <td>
                            <select name="source_select[]" size="10" id="source_select[]" style="width: 250px;" ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                                <?php
                                $sql="select user_id,username,firstname from user";
                                $re=api_sql_query ( $sql, __FILE__, __LINE__ );

                                while($arr=mysql_fetch_row($re)){
                                    echo "<option value='".$arr[0]."'>".$arr[1]."(".$arr[2].")</option>";
                                }
                                ?>
                            </select>
                        </td>

                        <td align="center">
                            <p><input type="button" value=">>"
                                      onclick="moveItem_l2r(this.form.elements['source_select[]'],this.form.elements['target_select[]'],true)" class="formbtn" /></p>
                            <p><input type="button" value=">"
                                      onclick="moveItem_l2r(this.form.elements['source_select[]'],this.form.elements['target_select[]'],false)"  class="formbtn" /></p>
                            <p><input type="button" value="<"
                                      onclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)" class="formbtn" /></p>
                            <p><input type="button" value="<<"
                                      onclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],true)" class="formbtn" /></p>
                        </td>

                        <td align="left"><select name="target_select[]" id="target_select[]"
                                                 size="10" style="width: 250px" multiple
                                                 ondblclick="moveItem_r2l(this.form.elements['target_select[]'],this.form.elements['source_select[]'],false)">
                        </select></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="3" align="center" class="formTableTd">
                <input type='hidden' id="options_values" name="option_values" value=""/>
                <input type="submit"  class="inputSubmit" value="<?=get_lang ( "Ok" )?>"  />&nbsp;&nbsp;
                <button class="cancel" type="button" onclick="javascript:self.parent.tb_remove();"><?=get_lang ( 'Cancel' )?></button>
            </td>
        </tr>
    </table>

    <script type="text/javascript">

        function validate(){
            /*select_items("target_select[]");
             return true;*/
            var option=document.getElementById('target_select[]');
            var str='';
            var i;
            for(i=0;i<option.length;i++){

                str+=option[i].innerText;
                if(i!==option.length-1){
                    str+=',';
                }
            }
            document.cookie="str="+str;
            document.cookie="taskid=<?php echo  $id?>";
            window.location.href="flag_user.php";
        }
    </script></form>
<?php
$table_header [] = array (get_lang ( '编号' ), true );
$table_header [] = array (get_lang ( '登录名' ), true );
$table_header [] = array (get_lang ( '姓名' ), true );
$table_header [] = array (get_lang ( '操作' ));

$count_user=count($all_users);

if($count_user!==0 && $count_user[0]==''){
    for($i=0;$i<$count_user;$i++){
        $row = array ();
        $fu_sql="select `username`,`firstname` from `sys_user_dept` where `user_id`=".$all_users[$i];
        $data_list = api_sql_query_array_assoc ( $fu_sql, __FILE__, __LINE__ );

        $row [] = $i+1;
        $row [] = $data_list[0]['username'];
        $row [] = $data_list[0]['firstname'];
        if(isset($all_users[$i])){
        $href = 'flag_user.php?action=delete&id=' . $i.'&task_id='.$id;
        $row [] = '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unreg', $href );
        }else{
            $row [] ='';
        }

        $table_data [] = $row;
    }
}

unset ( $data, $row );



//echo '<pre>';var_dump($table_data);echo '</pre>';

echo '<br/>所有的用户: ';
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();