<?php
/**----------------------------------------------------------------

liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");

$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());

$offset = getgpc ( "offset", "G" );$offset=(int)$offset;
$ids=  getgpc('id');$ids=(int)$ids;
//分页(因为也面试弹出狂的过,无法实现)
$sql1 = "SELECT count(id) FROM vmdisk where mod_type=1 and task_id=".$ids;
$rs= api_sql_query_array_assoc ( $sql1, __FILE__, __LINE__ );
$total_rows=$rs[0]["count(id)"];
$url = WEB_QH_PATH . "blue_template.php?id=".$ids;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url,NUMBER_PAGE);
$pagination = new Pagination ( $pagination_config );


$sql ="select blue_vm from task where id=".$ids;
$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );

$vm_arr=$data_list[0]['blue_vm'];
$vm_unser=  unserialize($vm_arr);
//task name
$vlanid=Database::getval("select name from task where id =".$ids,__FILE__,__LINE__);
$vlanid=$vlanid+1;
?>

<section id="main" class="column"  style="width: 100%;">

    <article class="module width_full hidden">
        <h4 class="page-mark">渗透模版</h4>
        <link rel="stylesheet" href="<?=WEB_QH_PATH?>css/layout.css" type="text/css" media="screen" />
        <?php
        $num=count($vm_unser);
        if($vm_unser !=''){
            ?>
            <div class="module_content">
                <table cellspacing="0" cellpadding="0" class="p-table" >
                    <tr>
                        <th>编号</th>
                        <th>模版名称</th>
                        <th>进入对抗</th>
                    </tr>
                    <?php


                    for($i=0;$i<$num;$i++){
                        $j=$i+1;
                        ?>
                        <tr>
                            <td width='20%'><?=$j?></td>
                            <td width='20%'><?=$vm_unser[$i]?></td>
                            <td width='20%'>
                                <a href="/lms/main/cloud/cloudvmstart.php?system=<?=$vm_unser[$i]?>_<?=$_SESSION['_user']['user_id']?>&nicnum=1&user_id=<?=$_SESSION['_user']['user_id']?>&cid=<?=$vm_unser[$i] ?>&vlanid=<?=$vlanid?>" target="_new" title="进入对抗">
                                    <img src="../../themes/img/visio_na.gif" alt="进入对抗" title="进入对抗"/></a>
                            </td>
                        </tr>
                        <?php  }

                    ?>


                </table>
                <div class="page">
                    <ul class="page-list">
                        <li class="page-num">总计<?=$num?>条记录</li>
                    </ul>
                </div>
            </div>

            <?php
        } else {
            ?>
            <div class="error" >没有相关记录</div>
            <?php
        }
        ?>

    </article>



</section>


</body>
</html>
