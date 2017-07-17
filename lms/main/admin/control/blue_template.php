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

$_SESSION['blue_task'] =  htmlspecialchars($_GET ['id']);
$id = intval($_SESSION['blue_task']);
$objDept = new DeptManager ();

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( $tool_name, FALSE );

$action=htmlspecialchars($_GET ['action']);

//删除数组元素
function array_remove(&$arr, $offset){
    array_splice($arr, $offset, 1);
}

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['code']));
            $task_id=  intval(htmlspecialchars($_GET ['task_id']));
            if ( isset($delete_id)){
                $all_users2 =unserialize(Database::getval("select `blue_vm` from `task` where `id`=".$task_id,__FILE__,__LINE__));

                array_remove($all_users2, $delete_id);
              //  var_dump($all_users2);

                $results=serialize($all_users2);
                $sql="UPDATE  `vslab`.`task` SET  `blue_vm` ='".$results."' WHERE  `task`.`id` =".$task_id;

                api_sql_query ( $sql, __FILE__, __LINE__ );


                $redirect_url = "blue_template.php?id=".$task_id;
                api_redirect ( $redirect_url );
            }
            break;
    }
}

if(isset($_POST['formSent']) && getgpc("formSent","P")==1){
    $str=getgpc('str');
    $taskid=  intval(getgpc('task'));
    $red_tem=explode(",",$str);
    // var_dump($red_tem);
    $vm_list=array();
//    if($str!=''){
        foreach($red_tem as $k){
            $vms=explode("(",$k);
            $category=$vms[0];
            $vm_name =$vms[1];
            $vm_name =str_replace(')','',$vm_name);
            $vm_list[]=$vm_name;
        }
        $all_users =unserialize(Database::getval("select `blue_vm` from `task` where `id`=".$taskid,__FILE__,__LINE__));

        if($all_users[0]==''){
            $result= $vm_list;
        }else{
            $intersection = array_diff($all_users,$vm_list);//比较
            $result= array_merge($intersection,$vm_list);//合并
        }
    for($p=0;$p<count($result);$p++){
        if($result[$p]==''){
            array_remove($result, $p);
        }
    }

        $result_var=serialize($result);
        $sql_vm="UPDATE  `vslab`.`task` SET  `blue_vm` =  '".$result_var."' WHERE  `task`.`id` =".$taskid;
        api_sql_query($sql_vm,__FILE__,__LINE__);
//    }
    $redirect_url = "blue_template.php?id=".$taskid;
    api_redirect ( $redirect_url );
}
?>
<br>
<form name="theForm" method="post" action="blue_template.php" onsubmit="validate()">
    <input type="hidden" name="formSent" value="1" />
    <input type="hidden" name="task" value="<?php echo $id?>" />
    <table border="0" cellpadding="5" cellspacing="0" align="center"
           width="98%">

        <tr class="containerBody">
            <td class="formLabel">选择设置的渗透模板</td>
            <td style="text-align: left;" class="formTableTd">
                <table id="linkgoods-table" align="left">

                    <tr>
                        <td>

                            <!--<select name="source_select[]" size="10" id="source_select[]" style="width: 250px;"
                                    ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                           </select>-->
                            <select name="source_select[]" size="10" id="source_select[]" style="width: 250px;" ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                                <?php
                                $sql="select id,category,name from vmdisk where  platform!=3 AND platform!=0";
                                $re=api_sql_query ( $sql, __FILE__, __LINE__ );

                                while($arr=mysql_fetch_row($re)){
                                    echo "<option>".$arr[1]."(".$arr[2].")". "</option>";
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
                <!--                <input type="submit"  class="inputSubmit" value="--><?//=get_lang ( "Ok" )?><!--" />&nbsp;&nbsp;-->
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
            window.location.href="blue_template.php";
        }
    </script></form>
<?php

$table_header [] = array (get_lang ( '编号' ), true );
$table_header [] = array (get_lang ( '模板名称' ), true );
$table_header [] = array (get_lang ( '操作' ) );

$user_data =unserialize(Database::getval("select `blue_vm` from `task` where `id`=".  intval(getgpc('id')),__FILE__,__LINE__));

$count_users_data=count($user_data);
if($count_users_data!=0 ){
    for($i=0;$i<$count_users_data;$i++){
        $row = array ();
        $row [] = $i+1;
        $row [] = $user_data[$i];
        $href = 'blue_template.php?action=delete&code=' .$i.'&task_id='.$id;
        $row [] = '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unreg', $href );
        $table_data [] = $row;
    }
}
unset ( $data, $row );

echo '<br/>该任务所有的渗透模板: ';
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();
