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

$_SESSION['red_user'] =  htmlspecialchars($_GET ['id']);
$id = intval($_SESSION['red_user']);

$objDept = new DeptManager ();

$htmlHeadXtra [] = import_assets ( "select.js" );
$tool_name = get_lang ( 'ArrangeCourse' );
Display::display_header ( $tool_name, FALSE );

$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['code']));
            $group_id=  intval(htmlspecialchars($_GET ['group_id']));
            if ( isset($delete_id)){

                //delete mysql
//                $sql = "UPDATE  `vslab`.`user` SET  `type` =  '0',group_id='0' WHERE  `user`.`user_id` ={$delete_id}";
                $sql = "UPDATE  `vslab`.`user` SET  `type` =  '0' WHERE  `user`.`user_id` ={$delete_id}";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                $redirect_url = "red_user_control.php?id=".$group_id;
                api_redirect ( $redirect_url );
            }
            break;
    }
}


if(isset($_POST['formSent']) && getgpc("formSent","P")==1){
    $str=getgpc('str');
    $userid=  intval(getgpc('user'));
    $red_tem=explode(",",$str);
    // var_dump($red_tem);
    foreach($red_tem as $k){
        $vms=explode("(",$k);
        $username=$vms[0];
        $status=$vms[1];
        $status =str_replace(')','',$status);
        if($status=="学员"){
            $status=5;
        }else if($status=="超级管理员"){
            $status=10;
        }else if($status=="老师"){
            $status=1;
        }

        $sql_vm="UPDATE  `vslab`.`user` SET  `type` =  '1',`group_id` = '".$userid."' WHERE status='".$status."'  and  username='".$username."';";
        api_sql_query($sql_vm,__FILE__,__LINE__);
    }
    $redirect_url = "red_user_control.php?id=".$userid;
    api_redirect ( $redirect_url );
}
?>
<br>
<form name="theForm" method="post" action="red_user_control.php" onsubmit="validate()">
    <input type="hidden" name="formSent" value="1" />
    <input type="hidden" name="user" value="<?php echo $id?>" />
    <table border="0" cellpadding="5" cellspacing="0" align="center"
           width="98%">

        <tr class="containerBody">
            <td class="formLabel">选择红方小组用户</td>
            <td style="text-align: left;" class="formTableTd">
                <table id="linkgoods-table" align="left">

                    <tr>
                        <td>

                            <!--<select name="source_select[]" size="10" id="source_select[]" style="width: 250px;"
                                    ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                           </select>-->
                            <select name="source_select[]" size="10" id="source_select[]" style="width: 250px;" ondblclick="moveItem_l2r(G('source_select[]'),G('target_select[]'),false)" multiple="true">
                                <?php
//                                $sql="select user_id,username,status from user where type=0";
                                $sql="select user_id,username,status from user where type=0 and group_id=".$id;
                                $re=api_sql_query ( $sql, __FILE__, __LINE__ );

                                while($arr=mysql_fetch_row($re)){
                                    if($arr[2]==5){
                                        $status = "学员";
                                    }else if($arr[2]==10){
                                        $status = "超级管理员";
                                    }else if($arr[2]==1){
                                        $status = "老师";
                                    }
                                    echo "<option>".$arr[1]."(".$status.")"."</option>";
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
            window.location.href="red_user_control.php";
        }
    </script></form>
<?php

function get_user_data($condition = '') {

    /*$_SESSION['red_user'] =  htmlspecialchars($_GET ['id']);
    $id = $_SESSION['red_user'];*/
    $sql = "SELECT user_id,status,username,type,user_id FROM user where type=1 and group_id=".  intval(getgpc('id'));

    /*$keyword = escape ( getgpc ( 'keyword' ), TRUE );
    if ($keyword) {
        $sql .= " AND (name LIKE '%" . $keyword . "%' OR blue_template LIKE '%" . $keyword . "%')";
    }*/
    $all_tasks = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
    return $all_tasks;
}


$table_header [] = array (get_lang ( '编号' ), true );
$table_header [] = array (get_lang ( '用户身份' ), true );
$table_header [] = array (get_lang ( '用户名称' ), true );
$table_header [] = array (get_lang ( '用户类型' ), true );
//$table_header [] = array (get_lang ( 'OfficialCode' ), true );
$table_header [] = array (get_lang ( '操作' ) );

$all_users = get_user_data ();


foreach ( $all_users as $admin_id => $data ) {
    $row = array ();
    if($data ['type']==1){
        $data ['type']='红方';
    }else{
        $data ['type']='蓝方';
    }
    if($data ['status']==1){
       $data ['status']='教师';
    }elseif($data ['status']==10){
       $data ['status']='超级管理员';
    }else{
        $data ['status']='学生';
    }
    $row [] = $data ['user_id'];
    $row [] = $data ['status'];
    $row [] = $data ['username'];
    $row [] = $data ['type'];
    $href = 'red_user_control.php?action=delete&code=' . $data ['user_id'].'&group_id='.$id;
    $row [] = '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', '踢出用户', $href );
    $table_data [] = $row;
}
unset ( $data, $row );

echo '<br/>该红方小组的所有成员: ';
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();
