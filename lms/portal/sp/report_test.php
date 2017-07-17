<?php
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("../../main/inc/global.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

//需要传递的参数 课程编号   需要获得用户名。
$submit=$_POST['submit'];
$action=$_GET['action'];
$b=$_GET['b'];
$code=$_GET['cidReq'];//cidReq
$user=api_get_user_name ();//用户名称
$url="report_test.php";
$old_url=basename($_SERVER['HTTP_REFERER']);
$url_old_u=$_SESSION['old_url'];
if($url_old_u==NULL){
    $_SESSION['old_url']=$old_url;
}else{
    $old_url=$_SESSION['old_url'];
}

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();

include_once ("inc/page_header_report.php");    //导航条

if($code){
    $sql_class="select `title` from `course` where `code` = '$code' ";
    $class=  Database::getval($sql_class,__FILE__, __LINE__ );  //课程名称   
    $sql="select `id` ,`purpose`,`equipment`,`content`,`result`,`analysis` from `report` where `user`='".$user."' and  `code`='".$class."'";
    $result=Database::fetch_one_row($sql,__FILE__,__LINE__);
    if($result){
        $id=$result['id'];
        $purpose=$result['purpose'];
        $equipment=$result['equipment'];
        $content=$result['content'];
        $res_result=$result['result'];
        $analysis=$result['analysis'];
    }
}


//返回实验报告管理
if($_POST['test_a']=='acb'){
    tb_close ( $old_url );
}

//报告的id
if(!empty($submit)&&isset($submit)){
    $user=$_POST['name'];
    $code=$_POST['class'];
    $report_name=$user.'_'.$code;
    $sql="select `id` from `report` where `user`='".$user."' and  `code`='".$code."'";
    $id=Database::getval($sql,__FILE__,__LINE__);
    if(empty($id)&&($_POST['aaaa']!='0000')){
        $sql="insert into `report`(`report_name`,`user`,`code`,`type`)value('$report_name','$user','$code','1')";
        api_sql_query ( $sql, __FILE__, __LINE__ );
        $sql="select `id` from `report` where `user`='".$user."' and  `code`='".$code."'";
        $id=Database::getval($sql,__FILE__,__LINE__);
    }
}

//写入数据表
if(!empty($submit)&&isset($submit)){
    $mark=$_POST['mark'];
    $cidReq=$_POST['code'];
    $cidReq_a=$_POST['cidReq_a'];
    $b=$_POST['b'];
    if($cidReq_a=='0000'){
        echo "<script>alert(\"没有选择课程名称！\");</script>";
        echo "<script>window.location.href='report_test.php'</script>";
        
    }
    if($cidReq_a!=NULL){ $cidReq=$cidReq_a; }
    
    $purpose=$_POST['purpose'];
    $equipment=$_POST['equipment'];
    $content=$_POST['content'];
    $result=$_POST['result'];
    $analysis=$_POST['analysis'];
    
    //写入实验目的及要求
    if($mark='purpose_a'&&!empty($purpose)){
        $sql="UPDATE `report` SET  `purpose`= '".$purpose."' ,`type`='1' WHERE `id`= '$id'" ;
        api_sql_query ( $sql, __FILE__, __LINE__ );
    }
    //实验设备环境及要求
    if($mark='equipment_a'&&!empty($equipment)){
               $sql="UPDATE `report` SET  `equipment`= '".$equipment."' ,`type`='1'  WHERE `id`= '$id'" ;
        api_sql_query ( $sql, __FILE__, __LINE__ );
    }
    //实验内容与步骤
    if($mark='content_a'&&!empty($content)){
        $sql="UPDATE `report` SET  `content`= '".$content."' ,`type`='1'  WHERE `id`= '$id'" ;
        api_sql_query ( $sql, __FILE__, __LINE__ );
    }
    //实验结果
    if($mark='result_a'&&!empty($result)){
        $sql="UPDATE `report` SET  `result`= '".$result."' ,`type`='1' WHERE `id`= '$id'" ;
        api_sql_query ( $sql, __FILE__, __LINE__ );
    }
    //实验分析与讨论
    if($mark='analysis_a'&&!empty($analysis)){
        $sql="UPDATE `report` SET  `analysis`= '".$analysis."' ,`type`='1' WHERE `id`= '$id'" ;
        api_sql_query ( $sql, __FILE__, __LINE__ );
    }
    
    
    echo "<script>window.location.href='report_test.php?cidReq=".$cidReq."&b=".$b."'</script>";
    
}

//编辑，查看获取参数
if(!empty($action)&&isset($action)){
    $action=$_GET['action'];
    $name=$_GET['name'];
    $class=$_GET['class'];
    $mark=$_GET['mark'];
}

if($_GET['key']!=NULL){
    $num=$_GET['key'];
    $d=null;
    $sql_content="select `content` from `report` where `user`='$user' and `code` ='$class' ";
    $content=  Database::getval($sql_content,__FILE__, __LINE__ );
    $a=explode(";",$content);
    array_splice($a,$num,1); 
    foreach($a as $key => $value){  
        if($d==NULL){
            $d=$value;
        }else{
            $d =$d.";".$value;
        }
    }
   
    $sql="UPDATE `report` SET  `content`= '".$d."' ,`type`='1'  WHERE `user`='$user' and `code` ='$class' " ;
    api_sql_query ( $sql, __FILE__, __LINE__ );
    echo "<script>window.location.href='report_test.php?cidReq=".$cidReq."&b=".$b."'</script>";
}

?>
<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
        
        <div class="g-mn1" > 
            <div class="g-mn1c m-cnt" style="display:block;margin-left:100px;">
                
                <div class="top f-cb j-top">
                     <?php if($b!=NULL){?>   
                    <span class="f-fc6 f-fs1" id="j-catTitle"><b>实验报告> <?= $class?></b></span>
                    <?php }elseif($class==NULL){  ?>
                       <span class="f-fc6 f-fs1" id="j-catTitle">实验报告  </span> 
                 <?php   }else{?>  
                     <span class="f-fc6 f-fs1" id="j-catTitle"><b>实验报告 > <?= $class?></b></span> 
                 <?php } ?>   
                    </h3>
                    <form action="<?= $url?>" method="post" style="display:inline-block;float:right;" >
                        <input type="hidden" name="test_a" value="acb">
                        <input type="hidden" name="old_url" value="<?= $old_url?>">
                        <input type="submit"  name="submit" value="返回" style="width:70px;" class="lab-return">
                    </form>
                    <div class="j-nav nav f-cb"> 
                        <div id="j-tab">  
                        </div>
                    </div>
                </div>
                
                 <div class="j-list lists" id="j-list"> 
                    <div class="u-content">
                        <h3 class="sub-simple u-course-title"></h3>
                        
                        <div class="lab-goal">   
                            <?php 
                                if($code==NULL){ ?>
                                    <div class="lab-goal-title">
                                        <div class="lab-left">选择课程名称：</div>
                                      <div class="lab-right">
                                
                                        <?php 
                                        $sql="SELECT `code` FROM `report` where `user`='".$user."'";
                                        $res= api_sql_query_array_assoc($sql);
                                        $sql_in=null;
                                        for($i=0;$i<count($res);$i++){
                                            $data=$res[$i]['code'];
                                            $sql_in .="'".$data."' , ";
                                        }
                                        $sql_in ='( '.rtrim(trim($sql_in),",").' )';
                                        $sql = "select `code`,`title` FROM  `course` ";
                                        if(!empty($sql_in)){
                                            $sql .= 'where  `title` not in '.$sql_in;
                                        }
                                            $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                                            $vm= array ();
                                            while ( $vm = Database::fetch_row ( $res) ) {
                                                $c=$vm[0];
                                                $lab [$c] = $vm[1];
                                            }
                                        ?>
                                            <form action="<?= $url?>" method="post">
                                            <select name ="cidReq_a" style="height:30px;width:400px;border:1px solid #13a654;margin:10px;margin-left:5px;">
                                                 <option value="0000" selected>----请先选择课程名称----</option>
                                                <?php foreach($lab as $key => $value){ ?>
                                                <option value="<?= $key?>"><?= $value?></option>
                                                <?php }?>
                                            </select>
                                            <input type="hidden" name="aaaa" value="0000">
                                            <input type="hidden" name="a" value="a">
                                            <input type="hidden" name="old_url" value="<?= $old_url?>">
                                           <input type="submit"  name="submit" value="保存" class="lab-save">
                                             </form>
                                  </div> 
                                    </div>
                                            <?PHP 
                                            }
                            ?>
                            
                        </div>
                        
                        <div class="lab-goal">   
                            <?php 
                                if($code!=NULL){ ?>
<!--                                    <div class="lab-goal-title">
                                       <div class="lab-left">课程名称：</div>
                                       <div class="lab-right">
                                         <h3 class="lab-f16"><?= $class?></h3>
                                       </div>
                                    </div>-->
                            <?PHP            
                                }
                            ?>
                            
                            
                            <div class="lab-goal-title">
                               <div class="lab-left">一、</div>
                               <div class="lab-right">
                                 <h3 class="lab-f16">实验目的及要求</h3>
                               </div>
                            </div>
                            <div class="lab-goal-con">
                                <div class="lab-left"></div>
                                <div class="lab-right lab-con-right">
                                   <form action="<?= $url?>" method="post">
                                       <textarea name="purpose" ><?= $purpose?></textarea>
                                       <input type="hidden" name="mark" value="purpose_a">
                                       <input type="hidden" name="name" value="<?= $user?>">
                                       <input type="hidden" name="code" value="<?= $code?>">
                                       <input type="hidden" name="class" value="<?= $class?>">
                                       <input type="hidden" name="b" value="<?= $b?>">
                                       <input type="submit"  name="submit" value="保存">
                                   </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="lab-goal">   
                            <div class="lab-goal-title">
                                <div class="lab-left">二、</div>
                                <div class="lab-right">
                                  <h3 class="lab-f16">实验设备环境及要求</h3>
                                </div>
                            </div>
                            <div class="lab-goal-con">
                                <div class="lab-left"></div>
                                <div class="lab-right lab-con-right">
                                    <form action="<?= $url?>" method="post">
                                        <textarea name="equipment" ><?= $equipment?></textarea>
                                        <input type="hidden" name="mark" value="equipment_a">
                                        <input type="hidden" name="name" value="<?= $user?>">
                                        <input type="hidden" name="class" value="<?= $class?>">
                                        <input type="hidden" name="code" value="<?= $code?>">
                                        <input type="hidden" name="b" value="<?= $b?>">
                                        <input type="submit"  name="submit" value="保存">
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="lab-goal">   
                            <div class="lab-goal-title">
                                <div class="lab-left">三、</div>
                                <div class="lab-right">
                                  <h3 class="lab-f16">试验内容与步骤 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                      <!--<a href="report_content_add.php" title='添加试验内容与步骤'>添加</a>-->
                                  <?php    echo link_button ( 'exercise22.png', '添加试验内容与步骤', 'report_content_add.php?cidReq='.$code.'&b='.$b, '50%', '50%', FALSE );  ?>
                                  </h3>
                                </div>
                            </div>
                            <div class="lab-goal-con">
                                <div class="lab-left"></div>
           <!-- 有实验步骤时 -->
                                <div class="lab-right lab-con-right">
                                    <?php 
                                        $sql_content="select `content` from `report` where `user`='$user' and `code` ='$class' ";
                                        $content=  Database::getval($sql_content,__FILE__, __LINE__ );
                                        if($content!=NULL){
                                            $a=explode(";",$content);
                                            $i=1;
                                            foreach($a as $key => $value){  
                                                $b=explode("_",$value);
                                                $c=$b['0'];
                                                //$c=$i++.".".$c;
                                                ?>
                                                <span class="no-step"><?= $c?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="report_test.php?cidReq=<?= $code?>&b=<?= $b?>&key=<?= $key?>">删除</a></span>
<!--                                                <span class="no-step"> <?= $c?>&nbsp;&nbsp;&nbsp;<img src="../../storage/snapshot/<?= $b['1']?>"></span>-->
                                        <?php } 
                                        }else{
                                            echo '<span class="no-step"><b>没有试验内容与步骤，请点击上方按钮添加。</b></span>';
                                        } ?>
                                </div> 
                            </div>
                        </div>
                        <div class="lab-goal">   
                            <div class="lab-goal-title">
                               <div class="lab-left">四、</div>
                               <div class="lab-right">
                                 <h3 class="lab-f16">实验结果</h3>
                               </div>
                            </div>
                            <div class="lab-goal-con">
                                <div class="lab-left"></div>
                                <div class="lab-right lab-con-right">
                                    <form action="<?= $url?>" method="post">
                                        <textarea name="result" ><?= $res_result?></textarea>
                                        <input type="hidden" name="mark" value="result_a">
                                        <input type="hidden" name="name" value="<?= $user?>">
                                        <input type="hidden" name="class" value="<?= $class?>">
                                        <input type="hidden" name="code" value="<?= $code?>">
                                        <input type="hidden" name="b" value="<?= $b?>">
                                        <input type="submit"  name="submit" value="保存">
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="lab-goal">   
                            <div class="lab-goal-title">
                                <div class="lab-left">五、</div>
                                <div class="lab-right">
                                  <h3 class="lab-f16">实验分析与讨论</h3>
                                </div>
                            </div>
                            <div class="lab-goal-con">
                                <div class="lab-left"></div>
                                <div class="lab-right lab-con-right">
                                    <form action="<?= $url?>" method="post">
                                        <textarea name="analysis" ><?= $analysis?></textarea>
                                        <input type="hidden" name="mark" value="analysis_a">
                                        <input type="hidden" name="name" value="<?= $user?>">
                                        <input type="hidden" name="class" value="<?= $class?>">
                                        <input type="hidden" name="code" value="<?= $code?>">
                                        <input type="hidden" name="b" value="<?= $b?>">
                                        <input type="submit"  name="submit" value="保存">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
        
    </div>
<!--    <form action="<?= $url?>" method="post" class="lab-return">
        <input type="hidden" name="test_a" value="acb">
        <input type="hidden" name="old_url" value="<?= $old_url?>">
        <input type="submit"  name="submit" value="返回" >
    </form>-->
</div>
<style>
    
</style>
</body>
</html>
