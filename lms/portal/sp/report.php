<?php
   $cidReset = true;
   include_once ("inc/app.inc.php");
   include_once ('../../main/exercice/exercise.class.php');
   $uid=  getgpc('uid');//$uid=(int)$uid;
   $pid=  getgpc('pid');$pid=(int)$pid;
  
   
   if($uid=='' || $pid==''){
       header("location: pro_index.php");
   }
   if(str_replace("'", '', stripslashes($uid))!= $user_id){
        header("location: pro_index.php");
   }
   
   $sql4="select result from assessment_result where pro_id=".$pid." and user_id=".Database::escape ( $user_id );
    $re4 = api_sql_query ( $sql4, __FILE__, __LINE__ );
    $ds=Database::fetch_array ( $re4, 'ASSOC' );
    $nu='';
    while ( $r4 = Database::fetch_array ( $re4, 'ASSOC' ) ) {
      if($r4['result']==null){
               $nu=1;
                       }
                       
                       
            }
            if($ds==''){
                 header("location: pro_index.php");
            }
            if($nu!=''){
                header("location: pro_index.php");
            }
           
            
            

   
   
   
   $sql="select class,id from assess where pro_id=".$pid." group by class";
   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
   while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
       $class_arr[]=$row['class'];
       
   }
   

   $sql="SELECT `id`, `pro_id`, `user_id`, `assess_id`, `check_id`, `result` FROM `assessment_result` WHERE pro_id=$pid and user_id=".Database::escape ( $user_id );
   $re = api_sql_query ( $sql, __FILE__, __LINE__ );
   while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
     $arr[]=$r;
     
    }
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>安全评估报告</title>
<style>
    .box{
         width:500px;
        margin:0 auto;
    }
    
</style>
</head>

<body>
<div class="box">
        <h1>LINUX安全评估在线评估报告</h1>
   <div>
     <div>
        <h3>一、评估说明</h3>
       <pre> LINUX安全评估库共包括系统及补丁情况、身份鉴别、密码控制等10大类21个
风险点。这些风险按照漏洞的严重级别,分为了4大类,如下表所示。
</pre>
        <table border="1" cellspacing="0" cellpadding="0" width="500" height="150">
          <tr>
            <td colspan="2">风险严重级别定义</td> 
            
          </tr> 
          <tr>
            <td>等级</td>
            <td>说明</td>
          </tr>
        <tr>
            <td>I</td>
            <td>漏洞能够使攻击者直接获得系统控制权限,或者绕过防火墙。</td>
          </tr>
        <tr>
            <td>II</td>
            <td>漏洞会泄露使攻击者获得系统访问权的信息。</td>
          </tr>
        <tr>
            <td>III</td>
            <td>系统泄露的信息会使系统遭到攻击。</td>
          </tr>
        <tr>
            <td>IV</td>
            <td>漏洞如果被修补,会提高系统的安全性。</td>
          </tr>
   </table>
        
    </div>  
     <div>
      <h3>二、评估结果综述</h3>
        <p>各级别风险结果汇总如下:</p>
           <table border="1" cellspacing="0" cellpadding="0" width="500" height="60">
          <tr>
            <td></td> 
            <td>I</td>
             <td>II</td> 
            <td>III</td>
            <td>IV</td>
          </tr> 
         <?php foreach ($class_arr as $val){?>
         <tr>
            <td><?php echo $val;?></td> 
            <td>
                <?php
                   $n=0;
                   $sql="select id from assess where pro_id=".$pid." and class='$val' and risk_level=1";
                   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                   while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
                   $sql2="select result from assessment_result where pro_id=".$pid." and user_id=".Database::escape ( $user_id )." and assess_id=".$row['id'];
                     $re= api_sql_query ( $sql2, __FILE__, __LINE__ ); 
                     
                     while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                           if($r['result']==2){
                               $n++;
                           }
                                                  
                     }
                     
                       
                  }
                echo $n;
                
                ?>
            </td>
             <td>
                 <?php
                   $n=0;
                   $sql="select id from assess where pro_id=".$pid." and class='$val' and risk_level=2";
                   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                   while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
                   $sql2="select result from assessment_result where pro_id=".$pid." and user_id=".Database::escape ( $user_id )." and assess_id=".$row['id'];
                     $re= api_sql_query ( $sql2, __FILE__, __LINE__ ); 
                     
                     while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                           if($r['result']==2){
                               $n++;
                           }    
                     }
                       
                  }
                echo $n;
                
                ?>
                 
             </td> 
            <td>
                
                    <?php
                   $n=0;
                   $sql="select id from assess where pro_id=".$pid." and class='$val' and risk_level=3";
                   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                   while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
                   $sql2="select result from assessment_result where pro_id=".$pid." and user_id=".Database::escape ( $user_id )." and assess_id=".$row['id'];
                     $re= api_sql_query ( $sql2, __FILE__, __LINE__ ); 
                     
                     while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                           if($r['result']==2){
                               $n++;
                           }   
                     }   
                  }
                echo $n;
                
                ?>
            </td>
            <td>
                    <?php
                   $n=0;
                   $sql="select id from assess where pro_id=".$pid." and class='$val' and risk_level=4";
                   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                   while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
                   $sql2="select result from assessment_result where pro_id=".$pid." and user_id=".Database::escape ( $user_id )." and assess_id=".$row['id'];
                     $re= api_sql_query ( $sql2, __FILE__, __LINE__ ); 
                     
                     while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                           if($r['result']==2){
                               $n++;
                           }   
                     } 
                  }
                echo $n;
                
                ?>
                
            </td>
          </tr>
         <?php }?>     
   </table>
        
        
        
     </div> 
     <div>
       <h3>三、I级风险详述及加固建议</h3>
       <?php
                 $sql="select class from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=1 and r.result=2 group by a.class";
                  //$sql="select * from assessment_result r,assess a where a.id=r.assess_id and a.pro_id=$pid and a.risk_level=1 and r.result=2 group by a.class";
                 $re= api_sql_query ( $sql, __FILE__, __LINE__ ); 
                  while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                  ?>
            <div>
             <h4>▼<?php echo $r['class'];?></h4>
             <?php 
             $sql2="select a.reinforcement_suggestions rein,check_id from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=1 and r.result=2 and a.class='".$r['class']."'";
             //echo $sql2;
             $res=api_sql_query ( $sql2, __FILE__, __LINE__ );
             while ( $row2 = Database::fetch_array ( $res, 'ASSOC' ) ) {
             ?>
             <div>
             <div><span>检查项:</span><span>
                     <?php
                       $check_name=Database::getval ("SELECT `name` FROM `check_items` WHERE id=".$row2['check_id'], __FILE__, __LINE__ ); 
                       
                       echo $check_name;
                     ?>
                     
                 </span></div>
              <div><span>加固建议:</span>
              <pre><?php echo $row2['rein'];?></pre>
</div>
             </div>
             <?php }?>
            </div>
     
                  <?php }?>    
        
   
     </div> 
     <div>
       <h3>四、II级风险详述及加固建议</h3>
       <?php
                 $sql="select class from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=2 and r.result=2 group by a.class";
                  //$sql="select * from assessment_result r,assess a where a.id=r.assess_id and a.pro_id=$pid and a.risk_level=1 and r.result=2 group by a.class";
                 $re= api_sql_query ( $sql, __FILE__, __LINE__ ); 
                  while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                  ?>
            <div>
             <h4>▼<?php echo $r['class'];?></h4>
             <?php 
             $sql2="select a.reinforcement_suggestions rein,check_id from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=2 and r.result=2 and a.class='".$r['class']."'";
             //echo $sql2;
             $res=api_sql_query ( $sql2, __FILE__, __LINE__ );
             while ( $row2 = Database::fetch_array ( $res, 'ASSOC' ) ) {
             ?>
             <div>
             <div><span>检查项:</span><span>
                     <?php
                       $check_name=Database::getval ("SELECT `name` FROM `check_items` WHERE id=".$row2['check_id'], __FILE__, __LINE__ ); 
                       
                       echo $check_name;
                     ?>
                     
                 </span></div>
              <div><span>加固建议:</span>
              <pre><?php echo $row2['rein'];?></pre>
</div>
             </div>
             <?php }?>
            </div>
     
                  <?php }?> 

     </div>
  <div>
       <h3>五、III级风险详述及加固建议</h3>
       <?php
                 $sql="select class from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=3 and r.result=2 group by a.class";
                  //$sql="select * from assessment_result r,assess a where a.id=r.assess_id and a.pro_id=$pid and a.risk_level=1 and r.result=2 group by a.class";
                 $re= api_sql_query ( $sql, __FILE__, __LINE__ ); 
                  while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                  ?>
            <div>
             <h4>▼<?php echo $r['class'];?></h4>
             <?php 
             $sql2="select a.reinforcement_suggestions rein,check_id from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=3 and r.result=2 and a.class='".$r['class']."'";
             //echo $sql2;
             $res=api_sql_query ( $sql2, __FILE__, __LINE__ );
             while ( $row2 = Database::fetch_array ( $res, 'ASSOC' ) ) {
             ?>
             <div>
             <div><span>检查项:</span><span>
                     <?php
                       $check_name=Database::getval ("SELECT `name` FROM `check_items` WHERE id=".$row2['check_id'], __FILE__, __LINE__ ); 
                       
                       echo $check_name;
                     ?>
                     
                 </span></div>
              <div><span>加固建议:</span>
              <pre><?php echo $row2['rein'];?></pre>
</div>
             </div>
             <?php }?>
            </div>
     
                  <?php }?> 

     </div> 
     <div>
       <h3>六、IV级风险详述及加固建议</h3>
       <?php
                 $sql="select class from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=4 and r.result=2 group by a.class";
                  //$sql="select * from assessment_result r,assess a where a.id=r.assess_id and a.pro_id=$pid and a.risk_level=1 and r.result=2 group by a.class";
                 $re= api_sql_query ( $sql, __FILE__, __LINE__ ); 
                  while ( $r = Database::fetch_array ( $re, 'ASSOC' ) ) {
                  ?>
            <div>
             <h4>▼<?php echo $r['class'];?></h4>
             <?php 
             $sql2="select a.reinforcement_suggestions rein,check_id from assessment_result r left join assess a on r.assess_id=a.id where a.pro_id=$pid and a.risk_level=4 and r.result=2 and a.class='".$r['class']."'";
             //echo $sql2;
             $res=api_sql_query ( $sql2, __FILE__, __LINE__ );
             while ( $row2 = Database::fetch_array ( $res, 'ASSOC' ) ) {
             ?>
             <div>
             <div><span>检查项:</span><span>
                     <?php
                       $check_name=Database::getval ("SELECT `name` FROM `check_items` WHERE id=".$row2['check_id'], __FILE__, __LINE__ ); 
                       
                       echo $check_name;
                     ?>
                     
                 </span></div>
              <div><span>加固建议:</span>
              <pre><?php echo $row2['rein'];?></pre>
</div>
             </div>
             <?php }?>
            </div>
     
                  <?php }?> 

     </div>
   
   </div>
        
    </div>    
        </body>
</html>
