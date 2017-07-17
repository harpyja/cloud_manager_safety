<?php
//session_start();
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ('../../main/exercice/exercise.class.php');

include_once ("inc/page_header.php");
$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
//$type = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : 3);

$id=(isset ( $_GET ['ass_id'] ) ? getgpc ( 'ass_id', 'G' ) : 1);$id=(int)$id;
$pro_id=getgpc ( 'pro_id' );$pro_id=(int)$pro_id;
$item_id=getgpc('item_id');$item_id=(int)$item_id;
//$pro_id=$_POST['pro_id'];
$assess_id=getgpc('assess_id');$assess_id=(int)$assess_id;
$check_box=getgpc('check_box');

$key=$_GET['key']==null?0:getgpc('key');
$key=(int)$key;
 

if($item_id!=null){
     
    $j=1;
    foreach ($item_id as $val){
    //$sql="INSERT INTO `assessment_result`(`pro_id`, `user_id`, `assess_id`, `check_id`, `result`) VALUES (".$_SESSION['pro'].",".Database::escape ( $user_id ).",$assess_id,$val,".$check_box[$j].")";
    $sql="UPDATE `assessment_result` SET `result`=".$check_box[$j]." WHERE check_id=$val";
    //echo $sql;
    api_sql_query ( $sql, __FILE__, __LINE__ );
    $j++;
    }
    
}

if($pro_id!=null){
    


$_SESSION['pro']=$pro_id;

$sql="select result from assessment_result where pro_id=".$_SESSION['pro']." and user_id=".Database::escape ( $user_id );
$re = api_sql_query ( $sql, __FILE__, __LINE__ );
$r = Database::fetch_array ( $re, 'ASSOC' );
        
if($r==null){

 $sql1="select * from assess where pro_id=".$_SESSION['pro'];
 $result = api_sql_query ( $sql1, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
//    $arr[]=$row;
    $sql2="select * from check_items where assess_id=".$row['id'];
     $result2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
    while ( $row2 = Database::fetch_array ( $result2, 'ASSOC' ) ) {
            $sql3="INSERT INTO `assessment_result`(`pro_id`, `user_id`, `assess_id`, `check_id`) VALUES (".$_SESSION['pro'].",".Database::escape ( $user_id ).",".$row['id'].",".$row2['id'].")";
            api_sql_query ( $sql3, __FILE__, __LINE__ );
    }
    
}

}

} 
//类数组
$sql="select class,id from assess where pro_id=".$_SESSION['pro']." group by class";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
           $class_arr[]=$row['class'];
   } 
   $ass_arr='';
   foreach ($class_arr as $val){
       
          $che_sql="select * from assess where pro_id=".$_SESSION['pro']." and class like '%".$val."%'";   
          $che_rel = api_sql_query ( $che_sql, __FILE__, __LINE__ );
          while ($row = Database::fetch_array ( $che_rel, 'ASSOC') ){
                  $ass_arr[$row['id']]=$row['check'];
           }
        
   }
   
   $id_arr=  array_keys($ass_arr);
    
 
?>
<script>
$(function(){
    $('#prevBtn').click(function(){
        $('form').attr("action","assess.php?key=<?php echo $key-1;?>");
        $('form').submit();
    })
    
     $('#submitbtn').click(function(){
        
        $('form').submit();
    })
    
})

 function showdiv(i,e){
    var a="showdiv"+i;
    e=e||window.event;
         var b= e.clientY;
         var ww=e.clientX;
         var temp = $(window).scrollTop();
         var rr =$("#"+a).width();
         $("#"+a).css("top",b+temp-425);
         $("#"+a).css("left",b+temp-280);
  $("#"+a).show();
  
 }
 
 function hidediv(i){
    var a="showdiv"+i;
  $("#"+a).hide();
  
 }


</script>
 
<style type="text/css">
  #showdiv1,#showdiv2,#showdiv3,#showdiv4,#showdiv5,#showdiv6,#showdiv7,#showdiv8,#showdiv9,#showdiv10,#showdiv11,#showdiv12
    {   
        padding:15px;
        font-size:18px;
        position: absolute;   
        visibility: block;   
        overflow: hidden;   
        border:1px solid #CCC;   
        background-color:#F9F9F9;   
        border:1px solid #333;   
         
        z-index:999;
        
    }
.box{
    text-align: center;
    margin-top: 70px;
}
.j-cateTitle{
    width:auto;
}
#auto-id-D1Xl5FNIN6cSHqo0{
    border-bottom: 1px solid #ddd;
}
</style>
<link href="css/assess.css" type="text/css" rel="stylesheet"/>
<script type="text/javascript" src="js/tooltip.js"></script>
<link href="css/tooltip.css" rel="stylesheet" type="text/css" />


 <div class="clear"></div> 
	<div class="m-moclist">
	    <div class="g-flow" id="j-find-main">
		     <div class="b-30"></div>
		<div class="g-container f-cb">	 
                <div class="g-sd1 nav">
                    <div class="m-sidebr" id="j-cates">
                        <ul class="u-categ f-cb">
                            <li class="navitm it f-f0 f-cb  first cur" style="" data-id="-1" data-name="安全评估" id="auto-id-D1Xl5FNIN6cSHqo0">
                                <a class="f-thide f-f1"  style="background-color:#13a654;color:#FFF" href="learning_center.php" title="安全评估">安全评估</a>
                            </li> 
                            <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="安全评估" id="auto-id-D1Xl5FNIN6cSHqo0">
                                <a class="f-thide f-f1" title=""  href="<?=URL_APPEND?>portal/sp/pro_index.php">安全评估</a>
                            </li> 
                        </ul>
                        </div>
 
                    </div> 
           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
                            <div class="top f-cb j-top">
                                <h3 class="left f-thide j-cateTitle title">
                                    <span class="f-fc6 f-fs1" id="j-catTitle">
                                          当前位置：<?php
                                                    $pro_name=Database::getval ("SELECT `name` FROM `project` WHERE id=".$_SESSION['pro'], __FILE__, __LINE__ );
                                                    $class=Database::getval ("SELECT `class` FROM `assess` WHERE id=".$id_arr[$key], __FILE__, __LINE__ );
                                                     echo $pro_name." &gt; ".$class;
                                                    ?> 
                                    </span>
                                </h3>
                            </div>
                            <div class="j-list lists" id="j-list"> 
                             <div class="u-content">   
                               <form id="answering_technology" name="answering_technology" action="assess.php?key=<?php echo $key+1;?>"     method="post">
                                            <input type="hidden" name="questionLibrary.libraryId" value="bc437af93c72ba78013c72f63e6500cb" id="answering_technology_questionLibrary_libraryId"/>
                                            <input type="hidden" name="questionOperation.operationId" value="bc437af93c72ba78013c72fd3d5900e3" id="answering_technology_questionOperation_operationId"/>
                                            <div id="answeringTop" style="text-align:left;width:920;">
                                                    <span style="font-size:24px;margin-right:8px;"><?php   echo $pro_name; ?> </span>
                                                    <img src="images/separate.png">
                                                    <span style="font-size:18px;margin-left:8px;"><?php echo $class;?></span>
                                            </div>
                                            <table border="0">
                                                    <tr>
                                                          <?php if($key!=  count($id_arr)){?>
                                                            <td id="answeringDiv">
                                                                            <div id="infotop" style="width:100%;position: relative;">
                                                                                    <div id="title1">
                                                                                                            检查方法：
                                                                                    </div>
                                                                                    <div id="btnPositionDiv">
                            <!--									<a id="submitbtn" href="javascript:void(0)"  class="ui-btn-normal">请选择</a>-->

                                                                        <a id="submitbtn" href="javascript:viod(0)" class="ui-btn-normal" style="color:white">下一个</a>
                                                                                    </div>

                                                                                            <div id="btnPrevDiv">
                            <!--										<a id="prevBtn" href="javascript:void(0)" class="ui-btn-normal-1">前一个</a>-->
                                                                                                    <?php if($key!=0){?>
                                                                        <a id="prevBtn" href="javascript:viod(0)" class="ui-btn-normal-1" style="color:white">上一个</a>
                                                                        <?php }?>
                                                                                            </div>

                                                                            </div>
                                                                            <div style="margin-top:20px;padding-left:10px;font-size:14px;">
                                                                   <?php
                                                                          $sql="SELECT `check` FROM `assess` WHERE id=".$id_arr[$key];
                                                                           $check_me=Database::getval ("SELECT `check` FROM `assess` WHERE id=".$id_arr[$key], __FILE__, __LINE__ );
                                                                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp".$check_me;

                                                                   ?>
                                                                         </div>
                                                                            <div id="check-content">
                                                                                    <div id="check-content-title">检查项</div>
                                                                                    <table border="0">
                                                                                            <col width="62px"><col width="*"><col width="60px"><col width="60px"><col width="60px">
                                                                                            <tr style="color:#919191;">
                                                                                                    <td></td>
                                                                                                      <td></td>
                                                                                                    <td class="checktd">
                                                                                                            符合
                                                                                                    </td>
                                                                                                    <td class="checktd">
                                                                                                            不符合
                                                                                                    </td>
                                                                                                    <td class="checktd">
                                                                                                            不适用
                                                                                                    </td>
                                                                                            </tr>
                                                                                       <?php
                                                                     $ch_sql="select * from check_items where assess_id=".$id_arr[$key]; 
                                                                     //echo $ch_sql;
                                                                     $ch_rel = api_sql_query ( $ch_sql, __FILE__, __LINE__ );
                                                                     $i=1;
                                                                     while ( $chrow = Database::fetch_array ( $ch_rel, 'ASSOC' ) ) {  
                                                                           $status=Database::getval ("SELECT `result` FROM `assessment_result` WHERE check_id=".$chrow['id'], __FILE__, __LINE__ ); 
                                                                         if($status==null){
                                                                         ?>     

                                                                         <tr>
                                                                            <td ><?php echo "&nbsp;&nbsp;".$i?> 、</td>
                                                                            <td>
                                                                                <input type="hidden" name="item_id[]" value="<?php echo $chrow['id'];?>"><?=$chrow["name"]?>
                                                                             <?php  if($chrow["des"] !=""){?>
                                                                                <abbr title="<?=$chrow["des"]?>"rel="tooltip" style="color:blue">&nbsp;&nbsp;详述</abbr>
                                                                               <?php } ?> 
                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="1"></td>
                                                                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="2"></td>
                                                                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="3"></td>
                                                                        </tr>
                                                                         <?php }else{?>

                                                                         <tr>
                                                                            <td class="serial"> <?php echo "&nbsp;&nbsp;".$i?> 、</td>

                                                                            <td>
                                                                                 <p><input type="hidden" name="item_id[]" value="<?php echo $chrow['id'];?>"><?php echo $chrow["name"]?>

                              <?php  if($chrow["des"] !=""){?>
                                                                                <abbr title="<?=$chrow["des"]?>"rel="tooltip" style="color:blue">&nbsp;&nbsp;详述</abbr>
                                                                               <?php } ?>
                            </td>
                                                                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="1" <?php echo ($status==2 or $status==3)?"":"checked=checked"?>></td>
                                                                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="2" <?php echo ($status==1 or $status==3)?"":"checked=checked"?>></td>
                                                                            <td><input type="radio" name="check_box[<?php echo $i;?>]" value="3" <?php echo ($status==1 or $status==2)?"":"checked=checked"?>></td>
                                                                        </tr>



                                                                         <?php }?>
                                                                     <?php 
                                                                     $i++;
                                                                     }?>

                                                                                    </table>
                                                                            </div>
                                                            </td>

                                                              <?php }else{?>
                                                            <?php
                                                            $sql="select result from assessment_result where pro_id=".$_SESSION['pro']." and user_id=".Database::escape ( $user_id );
                                                            $re = api_sql_query ( $sql, __FILE__, __LINE__ );
                                                            $nu='';
                                                            while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                                                                if($r['result']==null){
                                                                    $nu=1;
                                                                }
                                                            }
                                                            if($nu==''){
                                                            ?>
                                                             <td id="answeringDiv"><div class="box"><h3>您已完成了<?php echo $pro_name;?>问题库中的全部问答<br>单击这里查看评估报告<br></h3><a href="report.php?uid=<?php echo Database::escape ( $user_id ); ?>&pid=<?php echo $_SESSION['pro'];?>" target="_blank" id="load">查看报告</a></div></td>
                                                            <?php }else{?>
                                                            <td id="answeringDiv"><div class="box"><h3>您还没有完成<?php echo $pro_name;?>问题库中的全部问答<br>请单击右侧分类列表完成全部问答</h3></div></td>
                                                            <?php }?>

                                                        <?php }?>

                                                            <td valign="top">
                                                                            <div id="item-index">
                                                                                    <div id="item-index-title">
                                                                                            分类索引
                                                                                    </div>
                                                                                      <?php 
                                                                                    $sql="select class,id from assess where pro_id=".$_SESSION['pro']." group by class";
                                                                                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                                                                                    while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
                                                                                        //$class_arr[]=$row['class'];
                                                                                        ?>
                                                                                <a href='assess.php?key=<?php echo array_search($row["id"], $id_arr);?>' > <?php echo $row["class"];?> </a>
                                                                                    <span style="font-size:12px">
                                                                                      (
                                                                                      <?php
                                                                                      $t=0;
                                                                                      $sq="select id from assess where pro_id=".$_SESSION['pro']." and class='".$row["class"]."'";
                                                                                      //echo $sq;
                                                                                      $res = api_sql_query ( $sq, __FILE__, __LINE__ );
                                                                                      $cl='';
                                                                                       while ( $r2 = Database::fetch_array ( $res, 'ASSOC' ) ) {
                                                                                           $cl[]=$r2['id'];
                                                                                       }
                                                                                       //var_dump($cl);
                                                                                       foreach ($cl as $c){
                                                                                           $n=0;
                                                                                           $sq1="select result from assessment_result where assess_id=$c";
                                                                                           $res1 = api_sql_query ( $sq1, __FILE__, __LINE__ );
                                                                                           while ( $r1 = Database::fetch_array ( $res1, 'ASSOC' ) ) {

                                                                                               if($r1['result']==null){
                                                                                                   $n=1;
                                                                                               }



                                                                                           }
                                                                                           if($n==0){
                                                                                               $t++;
                                                                                           }

                                                                                       }
                                                                                      echo $t;

                                                                                      ?>

                                                                                      /<?php
                                                                                        $csql="select count(*) from assess where class='".$row['class']."' and pro_id=".$_SESSION['pro'];
                                                                                        $cresult= api_sql_query($csql);
                                                                                        $crow=  Database::fetch_row($cresult);
                                                                                       echo $crow[0];
                                                                                      ?>)<br/>
                                                                                    </span>
                                                                                <?php  }?>	 

                                                                            </div>

                                                            </td>
                                                    </tr>
                                            </table>
                                        <input type="hidden" value="<?php echo $_SESSION['pro'];?>" name="pro_id">
                                       <input type="hidden" value="<?php echo $_SESSION['ass'][$a][0];?>" name="assess_id">
                                       <input type="hidden" value="<?php echo Database::escape ( $user_id );?>" name="user_id">

                                    </form>
                           </div>
                            </div> 
	  </div>
	</div>
     </div> 
 </div>
</div>		 
	 

	<!-- 底部 -->
<?php
        include_once './inc/page_footer.php';
?>
 </body>
</html>