<?php
/**
==============================================================================
==============================================================================
 */
header("content-type:text/html;charset=utf-8");
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$_SESSION['red_renwu'] =  htmlspecialchars($_GET ['id']);
$id = intval($_SESSION['red_renwu']);
$objDept = new DeptManager ();

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( $tool_name, FALSE );

$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['code']));
            $task_id=  intval(htmlspecialchars($_GET ['task_id']));
            if ( isset($delete_id)){

                //delete mysql
                $sql = "UPDATE  `vslab`.`group_user` SET  `tasks_id` =  '0' WHERE  `group_user`.`id` ={$delete_id}";
//                $sql_vm="UPDATE  `vslab`.`group_user` SET  `tasks_id` = 0 '".$taskid."',`type` =  2  WHERE  `group_user`.`name` ='".$key."'";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "red_renwu.php?id=".$task_id;
                api_redirect ( $redirect_url );
            }
            break;
    }
}

if(isset($_POST['formSent']) && getgpc("formSent","P")==1){
    $str=getgpc('str');
    $taskid=  intval(getgpc('task'));
    $red_tem=explode(",",$str);
//     var_dump($red_tem);
    foreach($red_tem as $key){
        $vms=explode("(",$k);
        $sql_vm="UPDATE  `vslab`.`group_user` SET  `tasks_id` =  '".$taskid."',`type` = 1 WHERE  `group_user`.`name` ='".$key."'";
//        echo $sql_vm."<br>";
        api_sql_query($sql_vm,__FILE__,__LINE__);

    }
    $redirect_url = "red_renwu.php?id=".$taskid;
    api_redirect ( $redirect_url );
}
?>
<br>
<form name="theForm" method="post" action="red_renwu.php" onsubmit="validate()">
    <input type="hidden" name="formSent" value="1" />
    <input type="hidden" name="task" value="<?php echo $id?>" />
    <table border="0" cellpadding="5" cellspacing="0" align="center"
           width="98%">

        <tr class="containerBody">
            <td class="formLabel">选择用户组</td>
            <td style="text-align: left;" class="formTableTd">
                <table id="linkgoods-table" align="left">

                    <tr>
                        <td>
                            <select name="source_select[]" size="10" id="source_select[]" style="width: 250px;" ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                                <?php
                                $sql="select `id`,`name` from `group_user` where `tasks_id`=0 AND `type`=1";
                                $re=api_sql_query ( $sql, __FILE__, __LINE__ );

                                while($arr=mysql_fetch_row($re)){
                                    echo "<option>".$arr[1] . "</option>";
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
                            <!--                            <p><input type="button" value="--><?//=get_lang ( "Empty" )?><!--" onclick="clearOptions(this.form.elements['source_select[]'])" class="formbtn" /></p>-->
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
            window.location.href="red_renwu.php";
        }
    </script></form>
<?php

function get_user_data($condition = '') {
    $sql = "SELECT `id`,`name`,`id` FROM `group_user` where `type`=1 and `tasks_id`=".  intval(getgpc('id'));

    $all_tasks = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
    return $all_tasks;
}


$table_header [] = array (get_lang ( '编号' ), true );
$table_header [] = array (get_lang ( '用户组名称' ), true );
$table_header [] = array (get_lang ( '操作' ) );

$all_users = get_user_data ();


foreach ( $all_users as $admin_id => $data ) {
    $row = array ();
    $row [] = $data ['id'];
    $row [] = $data ['name'];
    $href = 'red_renwu.php?action=delete&code=' . intval($data ['id']).'&task_id='.$id;
    $row [] = '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unreg', $href );
    $table_data [] = $row;
}
unset ( $data, $row );

echo '<br/>该任务所有的红方用户组: ';
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();
