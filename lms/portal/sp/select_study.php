<?php  
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();  
include_once './inc/page_header.php';
 if(api_get_setting ( 'enable_modules', 'course_center' ) == 'false'){
      echo '<script language="javascript"> document.location = "./learning_center.php";</script>';
      exit ();
 }
$id=  getgpc('id');
if(!isset($_GET['id']) && $id==''){
    $sql =  "select id from setup order by id LIMIT 0,1";
    $courseId= DATABASE::getval ( $sql, __FILE__, __LINE__ );
    if($courseId!==''){
        echo '<script language="javascript"> document.location = "./select_study.php?id='.$courseId.'";</script>';
    }
}
$category_id=  getgpc("category");  
$subclass='';
if($category_id){   //选中某个一级分类
    $subclass[]=$category_id;
}else{  //某课程体系下的所有一级分类
$sql1="select subclass from setup where id=$id";
$re=  Database::getval($sql1);
$rews=explode(',',$re);
    foreach ($rews as $v) {
        if($v!==''){
           $subclass[]=$v; 
        }
    }
}

$tbl_course  = Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_course_openscore = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );

if (api_is_platform_admin () OR api_get_setting('course_center_open_scope')==1) {
    $sql = "SELECT category_code,count(*) FROM $tbl_course  GROUP BY category_code";
} else {
    $sql = "SELECT category_code,count(*) FROM $tbl_course WHERE code IN (SELECT course_code FROM " . $tbl_course_openscore . " WHERE user_id='" . api_get_user_id () . "')  GROUP BY category_code";
}
$category_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
 
$objCrsMng=new CourseManager();//课程分类  对象。
$objCrsMng->all_category_tree = array ();
$category_tree = $objCrsMng->get_all_categories_trees ( TRUE,$subclass);

//Recently Study
$sql="SELECT `code`,`title`  FROM  `course` INNER JOIN `course_rel_user` ON `course_rel_user`.`course_code`=`course`.`code` where `user_id`=".$user_id." ORDER BY  `course_rel_user`.`last_access_time` DESC  limit  0,8";
//echo $sql;
$Recently_Study=  api_sql_query_array_assoc($sql,__FILE__,__LINE__);
$Recently_Study_count=intval(count($Recently_Study));
    
?>  
    <!-- 导航结束 -->
     <div class="clear"></div> 
	<div class="m-moclist">
	    <div class="g-flow" id="j-find-main"> 
               <div class="b-30"></div>
     <div class="g-container f-cb">

            <div class="g-sd1 nav">
                    <div class="m-sidebr" id="j-cates">
                           <ul class="u-categ f-cb">
                               <li class="navitm it f-f0 f-cb first cur"  data-id="-1" data-name="选课中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                                   <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="选课中心">选课中心</a>
                               </li>
                               <?php  
                                $sql="select id,title from setup order by id";
                                  $res=  api_sql_query_array($sql);
                                  foreach ($res as $value) {
                                      ?>
                            <li class="navitm it f-f0 f-cb haschildren"  data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" <?=$value['id']==$id?'style="color:green;font-weight:bold"':''?> title="<?=$value['title']?>" href="<?=URL_APPEND."portal/sp/select_study.php?id=".$value['id']?>"><?=$value['title']?></a>
                                                <div class="i-mc">
                                                    <div class="subitem" clstag="homepage|keycount|home2013|0601b">
                                                            <?php    
                                                            $sql1="select subclass from setup where id=".$value['id'];
                                                              $re1=  Database::getval($sql1);
                                                              $rews1=explode(',',$re1);
                                                                  $subclass1='';
                                                                  foreach ($rews1 as $v1) {
                                                                      if($v1!==''){
                                                                         $subclass1[]=$v1; 
                                                                      }
                                                                  }
                                                              $objCrsMng1=new CourseManager();//课程分类  对象。
                                                              $objCrsMng1->all_category_tree = array (); 
                                                              $category_tree1 = $objCrsMng1->get_all_categories_trees ( TRUE,$subclass1);
                                                              $i = 0;   $j = 0;   $o = array(); //标记循环变量， 数组 ;
                                                              foreach ( $category_tree1 as $category ) { ///父类循环
                                                                $url = "select_study.php?id=".$value['id']."&category=" . $category ['id'];
                                                                  $cate_name = $category ['name'] . (($category_cnt [$category ['id']]) ? "&nbsp;(" . $category_cnt [$category ['id']] . ")" : "");
                                                                  if($category['parent_id']==0) {
                                                                  ?>
                                                                <a class="j-subit f-ib f-thide" href="<?=$url?>"><?=$cate_name?></a>
                                                                  <?php  if($i==3){$i=0;}
                                                                    }  
                                                                 }
                                                                  if(!$category_tree1){    
                                                                      echo "<p align='center'>没有相关课程分类，请联系课程管理员</p>";
                                                                  }
                                                                  ?>

                                                        </div>
                                                </div>

                                        </li>
                               <?php  }  ?>
                               </ul>
                        </div>
                
                     <div class="m-university" id="j-university" style="border:1px solid #13a654;">
                           
                                   <div class="bar f-cb">
                                          <a class="left f-fc3 safe-assess" href="./pro_index.php">安全评估</a>
                                   </div>
                    </div>
                        <!--下部-->

                        <div class="m-university" id="j-university">
                            <div>
                                   <div class="bar f-cb">
                                          <h3 class="left f-fc3 rece-h3">最近学习</h3>
                                   </div>
                                 <div class="us">
                                 <?php
                                    if($Recently_Study_count>0){
                                        foreach ($Recently_Study as $values1) { ?>
                                            <div class="Recently_Study">
                                               <a class="recently1" href="<?=URL_APPEND?>portal/sp/course_home.php?cidReq=<?=$values1['code']?>&action=introduction" class="logo" >
                                                 <?=api_trunc_str2($values1['title'],18)?> 
                                               </a>
                                           </div>
                                    <?php
                                        }
                                    }else{?>
                                        <div class="Recently_Study">
                                                 没有最近学习
                                           </div>
                                   <?php
                                   }
                                    ?>
                                </div> 
                        </div>
                </div>
</div>

           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
				    <div class="top f-cb j-top">
					   <h3 class="left f-thide j-cateTitle title">
					      <span class="f-fc6 f-fs1" id="j-catTitle">
                                              <?php  
                                              $catgory_name=DATABASE::getval("select  name  from  course_category  where  id=".$category_id);
                                              $setup_name=DATABASE::getval("select  title from setup where id=".$id);
                                              echo  ($category_id?$catgory_name:$setup_name);
                                              ?>
                                              </span>
					   </h3>
<!--					   <div class="j-nav nav f-cb"> 
					        <div class="u-btn-group right" id="j-tab">
							    <a class="u-btn u-btn-sm u-btn-left u-btn-active">全部</a>
								<a class="u-btn u-btn-sm u-btn-left">今日课程</a>
								<a class="u-btn u-btn-sm">选修</a>
								<a class="u-btn u-btn-sm">必修</a>
							</div>
					   </div>-->
					</div>

    <div class="j-list lists" id="j-list">
	<div class="m-allwrap f-cb">
	    <div class="cnt f-cb" id="auto-id-k6s3rv2cJswp3exB">
	 <input type="hidden" value="1" id="tol_input1">
         
     <?php    
     foreach ( $category_tree as $category ) {
         foreach ( $category_tree as $category2 ) {
             
             $category_tree_new[]=$category2;
         }
         
     }
    
        $i = 0;   $j = 0;   $o = array();  $k=0; $ii=1;$jj=1; //标记循环变量， 数组 ;
        foreach ( $category_tree as $category ) { ///父类循环   ?>
            <?php  
            if($category['parent_id']==0) { 
                $o[$j] = $category['id'];$j+=1;
                ?>
                    <?php  
                       foreach ( $category_tree as $key=>$category2 ) {//子类 循环  ； 
                                $url2 = "course_catalog.php?category_id=" . $category2 ['id'];
                                $cate_name2 = $category2 ['name'] . (($category_cnt [$category2 ['id']]) ? "&nbsp;(" . $category_cnt [$category2 ['id']] . ")" : "");
$sql="SELECT DISTINCT `username` FROM   `view_course_user`  WHERE `category_code` =".$category2['id'];
$count_use_data=  api_sql_query_array_assoc($sql);
$count_users=count($count_use_data);
                                ?>
                <?php if ($category2['parent_id'] == $o[$j-1]){?> 
                  <?php if(($k+12)%12==0){   if($k==0){?>
                     <div  class="to_ol_class"  id="to_ol_class<?php print $jj;?>"  style="display:block;">
                  <?php  }else{   ?>  
                     <div  class="to_ol_class"  id="to_ol_class<?php print $jj;?>"  style="display: none;">
                      <?php  }    }?>
                             <div class="g-cell1 u-card j-href ie6-style" data-href="#">
                                <div class="card">
                                     <div class="u-img f-pr">
                                          <a href="course_catalog.php?category_id=<?=$category2['id']?>">
                                                <img class="img" src="../../storage/category_pic/<?=$category2['code']?>" width="222" height="124" alt="<?=$cate_name2?>" title="<?=$cate_name2?>"> 
                                          </a>
                                     </div>
                                              <div class="f-pa over f-fcf"> 
                                                <span class="txt"><?=($category_cnt [$category2 ['id']] ? $category_cnt [$category2 ['id']]: "0")?>个课程</span>
                                             </div>  
                                             <div class="subject-study"> 
                                               <span   style=" font-size: 12px;"><b>学习:</b></span>
                                               <span class="sub-img"></span>  
                                              <span class="study-num" style="color:#501E1E; font-size: 12px;"><b><?=$count_users?>人</b></span>
                                            <span class="study-good"></span>
                                          </div>
                                          <p class="t2 f-thide">
                                                <a class="t21 f-fc6" href="#" target="_blank"><b><?=$category2 ['name']?></b></a>	
                                          </p>
                                          <div class="descd j-d">
                                                      <p class="dtit" ><b><a class="sun" style="color:#868D86" href="<?=$url2?>" > <?=$cate_name2?></a></b></p>
                                                          <!--p class="ddesc"><a  href="<?=$url2?>"><?=api_trunc_str2($category2["CourseDescription"],50);?></a></p-->
                                                          <span class="dbtn"><a  href="<?=$url2?>">查看课程</a></span>
                                          </div>
                             </div>
			</div> 
            <?php   if(($ii)%12==0){ $jj++;?>
              </div>
            <?php }   $ii++;?>
                    <?php  $k++; }?>
                    <?php    }?>
 
           <?php }?>

            <?php  if($i==3){$i=0;}
            
            }
                 
                if(!$category_tree){
                    echo "<p align='center'>没有相关课程分类，请联系课程管理员</p>";
                }
            ?>
                                                           
                           
                       <!-- 课程列表结束-->

	 
							 <!--   ::after -->
     </div>
                        <!-- 课程页码显示-->
                      
            </div>
              <?php   
                        if($k>12){ $allnum=ceil($k/12); ?>
                        <input type="hidden" value="<?=($allnum)?>" id="tol_input2">
                          <div class="ui-pager" id="auto_id123">
						      <div class="auto-1404702552486">
							     <a class="zbtn zprv" id="tool_left1">上一页</a> 
                                                 <?php for($i=1;$i<=$allnum;$i++){?>            
								  <a onclick='yema(<?php echo $i;?>,<?php echo $allnum;?>)' class="zpgi zpg1" id="auto_id<?php echo $i;?>" ><?php echo $i;?></a>
                                                 <?php }?>                 
								  <a class="zbtn znxt" id="tool_right1">下一页</a>
							  </div>
			 </div>
                          <?php  } ?>
						  <!-- <div class="nocnt f-fc9" style="none">暂无内容</div> -->
						<!-- ::after -->
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
        
<script  type="text/javascript">

     jQuery(function(){
 
         var   val=jQuery("#tol_input1").val();
         var   val2=jQuery("#tol_input2").val();
      $("#auto_id1").css('background-color','#13A654');
      $("#auto_id1").css('color','#FFFFFF'); 
     if(val===1){
         jQuery(".cnt f-cb").children("div").hide();
         jQuery("#to_ol_class1").show();
     }
     jQuery("#tool_right1").click(function(){
         
          var   val1=jQuery("#tol_input1").val();
          if(val1<val2){
        var addval=val1*1+1;
      $("#auto_id"+addval).css('background-color','#13A654');
      $("#auto_id"+addval).css('color','#FFFFFF');
        for(var i=1;i<=val2;i++){
          if(i!==addval){
          $("#auto_id"+i).css('background-color','#EEEEEE');
          $("#auto_id"+i).css('color','#999');  
          }
      }
        jQuery("#tol_input1").val(addval);
         var hide="#to_ol_class"+val1;
 
            jQuery(hide).fadeOut(500,function(){
                  var  show="#to_ol_class"+addval;
                  jQuery(".cnt f-cb").children("div").hide();
                 jQuery(show).show();
             });
         }
    });
    
      jQuery("#tool_left1").click(function(){
         
          var   val1=jQuery("#tol_input1").val();
          if(val1>1){
        var addval=val1*1-1;
        $("#auto_id"+addval).css('background-color','#13A654');
      $("#auto_id"+addval).css('color','#FFFFFF');
        for(var i=1;i<=val2;i++){
          if(i!==addval){
          $("#auto_id"+i).css('background-color','#EEEEEE');
          $("#auto_id"+i).css('color','#999');  
          }
      }
        jQuery("#tol_input1").val(addval);
         var hide="#to_ol_class"+val1;
 
            jQuery(hide).fadeOut(500,function(){
                  var  show="#to_ol_class"+addval;
                  jQuery(".cnt f-cb").children("div").hide();
                 jQuery(show).show();
             });
         }
    });
        
    });
    function yema(id,all){
      $("#tol_input1").val(id);
      $("#auto_id"+id).css('background-color','#13A654');
      $("#auto_id"+id).css('color','#FFFFFF');
      for(var i=1;i<=all;i++){
          if(i!==id){
          $("#auto_id"+i).css('background-color','#EEEEEE');
          $("#auto_id"+i).css('color','#999');  
          }
      }
      $("#to_ol_class"+id).fadeOut(500,function(){
                  var  show="#to_ol_class"+id;
                  $(".cnt").children("div").hide();
                  $("#tol_input2").css('display','block');
                  $("#auto_id123").css('display','block');
                 $(show).show();
             });  
    }
</script>
        
 </body>
</html>
