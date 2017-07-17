 <?php  
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id (); 
$category_id=  getgpc('category_id');
$pid=  getgpc('pid');
$tag=  getgpc('tag');
$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];
include_once './inc/page_header.php'; 

$sql='select * from course_category where id='.$category_id;
$category_res=  api_sql_query_array_assoc($sql);
$category_data=$category_res[0]; 

//Recently Study
$sql="SELECT `code`,`title` FROM  `course` INNER JOIN `course_rel_user` ON `course_rel_user`.`course_code`=`course`.`code` where `user_id`=".$user_id." ORDER BY  `course_rel_user`.`last_access_time` DESC limit  0,5";
$Recently_Study=  api_sql_query_array_assoc($sql,__FILE__,__LINE__);
$Recently_Study_count=intval(count($Recently_Study));


//learning center
$objStat = new ScormTrackStat ();
api_session_unregister ( 'oLP' );
api_session_unregister ( 'lpobject' ); 

if(is_not_blank($sel)){
    $sqlwhere.="  AND  (`course`.`title`  LIKE '%" .trim($sel). "%'  )";
}

 
$sql="SELECT `view_course_user`.*,`course`.`category_code`,`course`.`code`,`course_category`.`id`,`course_category`.`parent_id` ,`course`.`description` FROM (`course`INNER JOIN `view_course_user` ON `course`.`code` = `view_course_user`.`course_code`)INNER JOIN `course_category` ON `course`.`category_code` = `course_category`.`id` WHERE `view_course_user`.`user_id` = '".$user_id."' AND `view_course_user`.`visibility` != ''AND `view_course_user`.`is_valid_date` != '0'  ";

$sql .= $sqlwhere;
if($tag=='0'||$tag=='1'){
    $sql .=" AND `view_course_user`.`is_required_course` = '". $tag ."'";
}
if(!isset($_GET['pid']) && $category_id!=''){
    $sql .=" and `course`.`category_code`=".$category_id." ";
}elseif(isset($_GET['pid']) && isset($_GET['category_id']) && $category_id!='' && $pid!=''){
    $sql1="SELECT distinct `course_category`.`id`,`course_category`.`parent_id` FROM (`course`INNER JOIN `view_course_user` ON `course`.`code` = `view_course_user`.`course_code`)INNER JOIN `course_category` ON `course`.`category_code` = `course_category`.`id` WHERE `view_course_user`.`user_id` = '".$user_id."' AND `view_course_user`.`visibility` != ''AND `view_course_user`.`is_valid_date` != '0'  ";
    if($pid!=''){
        $sql1.=" and  `course_category`.`parent_id`= ".$category_id." ";
    }
    $cate_datas=api_sql_query_array_assoc ( $sql1, __FILE__, __LINE__ );
     $cc=" and ( 0  ";
    foreach($cate_datas as $v0){
        $cc.=" or `course`.`category_code`=".$v0['id']." ";
    }
    $cc.=")";
    $sql.=$cc;
}
$personal_course_list1 = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
$total_rows = intval(count($personal_course_list1));
 
$parms="category_id=".$_GET['category_id'];
if($pid){$parms.="&pid=".$pid;}
if($tag){$parms.="&tag=".$tag;}
$url = WEB_QH_PATH . "learning_center.php?".$parms;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$sql .= " ORDER BY `view_course_user`.`category_code`,`view_course_user`.`title`";
if( $_GET['offset']==''){
    $offset=0;
}else{
    $offset=getgpc ( "offset", "G" );
}
$sql .= " LIMIT " . $offset . "," . NUMBER_PAGE;
$personal_course_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );

//if ($param {0} == "&") $param = substr ( $param, 1 );
//$url = WEB_QH_PATH . "learning_center.php?tag=" .$tag."&". $param;

if($category_id=='' && !isset($_GET['category_id']) && !isset($_GET['pid'])){
    $seelectd=" first cur";
}else{
    $seelectd=" haschildren";
}
//一级分类课程
if($_GET['pid'] && $_GET['pid']=='1'){
    $sql2="SELECT `id` FROM   `course_category`  WHERE `parent_id` =".$category_id;
    $ccid_data = api_sql_query_array_assoc( $sql2, __FILE__,__LINE__ );
    $ccid_data = array_filter($ccid_data);
    $countss=intval(count($ccid_data));
     
    $sql="SELECT DISTINCT `username` FROM   `view_course_user`  ";
    if($countss>0){
        $var2='';
        for($j=0;$j<$countss;$j++){
            if($j<1){
                $var2.=" WHERE `category_code` =".$ccid_data[$j]['id']." ";
            }else{
                $var2.=" OR `category_code` =".$ccid_data[$j]['id']." ";
            }
        }
        $sql.= $var2;
    }
}else{
    $sql="SELECT DISTINCT `username` FROM   `view_course_user`  WHERE `category_code`=".$category_id;
} 
    $count_use_data=  api_sql_query_array_assoc($sql);
    $count_users=count($count_use_data);  
?> 
     
     <div class="clear"></div> 
	<div class="m-moclist">
	    <div class="g-flow" id="j-find-main">
		     <div class="b-30"></div>
		<div class="g-container f-cb">	 
                <div class="g-sd1 nav">
                    <div class="m-sidebr" id="j-cates">
                        <ul class="u-categ f-cb">
                            <li class="navitm it f-f0 f-cb<?=$seelectd?>" style="" data-id="-1" data-name="我的学习课程" id="auto-id-D1Xl5FNIN6cSHqo0">
                                <a class="f-thide f-f1"  style="background-color:#13a654;color:#FFF" href="learning_center.php" title="我的学习课程">我的学习课程</a>
                            </li>
                            <?php  
                             $sql="SELECT distinct `parent_id` FROM (`course` INNER JOIN `course_rel_user` ON `course`.`code`=`course_rel_user`.`course_code`) INNER JOIN `course_category` ON `course`.`category_code`=`course_category`.`id` where `course_category`.`parent_id`!=0 and `course_rel_user`.`user_id`='".$user_id."' ";
                             $sql.=" order by `parent_id`";
                             $res=api_sql_query_array($sql);
                             $countS=count($res);
                             $seelectd="";
                             foreach ($res as $value) { 
                                    $category_cate=  Database::getval("select  `name` from `course_category` where `id`=".$value['parent_id'],__FILE__,__LINE__);
                                    if(isset($_GET['pid']) && $pid=='1'){
                                        if($category_id==$value['parent_id']){
                                            $seelectd=" first cur";
                                        }else{
                                            $seelectd=" haschildren";
                                        }
                                     }else{
                                         $category_pid=  Database::getval("select  `parent_id` from `course_category` where `id`=".$category_id,__FILE__,__LINE__);
                                        if($category_pid==$value['parent_id']){
                                            $seelectd=" first cur";
                                        }else{
                                            $seelectd=" haschildren";
                                        }
                                     }
                                    ?>
                                    <li class="navitm it f-f0 f-cb<?=$seelectd?>" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                                        <a class="f-thide f-f1" title="<?=$category_cate?>"  href="<?=URL_APPEND?>portal/sp/learning_center.php?category_id=<?=$value['parent_id']?>&pid=1"><?=$category_cate?></a>
                                        <div class="i-mc">
                                            <div class="subitem" clstag="homepage|keycount|home2013|0601b">
                                                <?php   
                                                $sql1="SELECT  distinct `id`,`name` FROM (`course` INNER JOIN `course_rel_user` ON `course`.`code`=`course_rel_user`.`course_code`) INNER JOIN `course_category` ON `course`.`category_code`=`course_category`.`id` where `course_category`.`parent_id`=".$value['parent_id']." and `course_rel_user`.`user_id`='".$user_id."'  ";
                                                $sql1.=" order by `parent_id`";
                                                $rews1=  api_sql_query_array_assoc($sql1);
                                                $counts=intval(count($rews1));
                                                if($counts>0){
                                                    foreach ($rews1 as $v1) {
                                                          if($v1['name']!=='' && $v1['id']!==''){ 
                                                             echo "<a class='j-subit f-ib f-thide' href='".URL_APPEND."portal/sp/learning_center.php?category_id=".$v1['id']."'>".$v1['name']."</a>";
                                                          }
                                                      } 
                                                }else{ 
                                                  echo "<p align='center'>没有相关课程分类</p>";
                                                }
                                                ?>
                                            </div>
                                        </div>

                                      </li>
                              <?php  }  ?>
                            </ul>
                        </div>

                        <!--下部-->

                        <div class="m-university" id="j-university">
<!--                            <div class="bar f-cb">
                                <h3 class="left f-fc3">最近学习</h3>
                            </div>-->
<!--                            <div class="us">
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
                            </div>-->
                         
                        </div>
                    </div>



           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
                            <div class="top f-cb j-top">
                                <h3 class="left f-thide j-cateTitle title">
                                    <span class="f-fc6 f-fs1" id="j-catTitle">
                                        <?php
                                        if(isset($_GET['category_id']) && $category_id!=''){
                                            echo $category_data["name"];
                                        }else{
                                            echo "全部课程";
                                        }
                                        ?>
                                    </span>
                                </h3>
                                <div class="j-nav nav f-cb"> 
                                    <div class="u-btn-group right" id="j-tab">
                                        <a class="u-btn u-btn-sm<?=$_GET['tag']==''?' u-btn-left u-btn-active':''?>" title="显示全部课程" href="learning_center.php">全部</a>
<!--                                        <a class="u-btn u-btn-sm u-btn-left">今日课程</a>-->
                                        <a class="u-btn u-btn-sm<?=$_GET['tag']=='0'?' u-btn-left u-btn-active':''?>" title="显示选修课程" href="learning_center.php?tag=0&category_id=<?=$_GET['category_id']?>&pid=<?=$pid?>">选修</a>
                                        <a class="u-btn u-btn-sm<?=$_GET['tag']=='1'?' u-btn-left u-btn-active':''?>" title="显示必修课程" href="learning_center.php?tag=1&category_id=<?=$_GET['category_id']?>&pid=<?=$pid?>">必修</a>
                                    </div>
                                </div>
                            </div>

                            <div class="j-list lists" id="j-list"> 
                                 <div class="m-allwrap f-cb">

						   <div class="cnt f-cb" id="auto-id-k6s3rv2cJswp3exB">
						       <div class="course-top">
							        <div class="g-cell1 u-card j-href ie6-style no-common" data-href="#">
									   <div class="card">
									        <div class="u-img f-pr">
                                                                                        <a href="course_catalog_info.php?action=view&category_id=<?=$category_id?>">
                                                                                            <img  class="img" src="../../storage/category_pic/<?=$category_data['code']?>" width="222" height="124" alt="<?=$category_data["name"]?>" title="<?=$category_data["name"]?>"> 
                                                                                        </a>
											</div>
											 <div class="f-pa over f-fcf">
											    <!-- <div class="f-icon clock left">*</div>  -->
												<span class="txt"><?=$total_rows?>个课程</span>
											</div> 
											<!-- <p class="f-f0 t1">高级语言程序设计</p> -->
											<div class="subject-study">
											  <span>学习:</span>
											  <span class="sub-img"></span>
											  <!-- <img src="images/sub-study.png">  -->
                                              <span class="study-num"><?=$count_users?>人</span>
											  <span class="study-good"></span>
											</div>
											<p class="t2 f-thide">
											      <!--<a class="t21 f-fc6" href="#" target="_blank" title="来源：北京邮电大学">来源：北京邮电大学</a>-->	
											</p>
											
									   </div>
									</div>

									<div class="course-simple right1">
									    <h3 class="sub-simple">简介</h3>
										<p class="sub-con">
                                                                                <?php
                                                                                    if($category_data["CourseDescription"]!==''){
                                                                                        echo  api_trunc_str2($category_data["CourseDescription"],225);
                                                                                    }else{
                                                                                        echo "没有课程简介！！！";
                                                                                    }
                                                                                    ?>
                                                                                </p>
                                                                                <?php
                                                                                if($category_id!==''){
                                                                                    echo '<div class="f-pa over f-fcf u-intro">
												<span class="txt">
                                                                                                    <a href="course_catalog_info.php?action=view&category_id='.$category_id.'">更多</a>
                                                                                                </span>
										</div>';
                                                                                }
                                                                                ?>
										 

									</div> 
                      </div> 
	      </div> 
    </div>
     <div class="b-20"></div>
                                <div class="u-content">
                                    <h3 class="sub-simple u-course-title">
                                        <span class="u-title-next">目录</span>
                                        <ul class="u-statues">
                                             <li> <span class="finishe-statues no-finish"></span>  未完成 </li>
                                             <li> <span class="finishe-statues continue"></span>  进行中  </li>
                                             <li> <span class="finishe-statues finished"></span>   已完成 </li>
                                        </ul> 
                                    </h3>
                                    <div class="u-content-bottom">
               <?php 
           $i=1;
        if (is_array ( $personal_course_list ) && $personal_course_list) {
            foreach ( $personal_course_list as $course ) {
                $title = $state = '';
                $course_system_code = $course ['code'];
                $course_title = $course ['title'];
                $course_visibility = $course ['visibility'];
                $is_course_admin = CourseManager::is_course_admin ( $user_id, $course_system_code );
                if ($course_visibility != COURSE_VISIBILITY_CLOSED or $is_course_admin) {
                    if (($course ['is_valid_date'] == 1) or api_is_platform_admin ()) {
                        $title .= '<a  href="' . WEB_QH_PATH . 'course_home.php?cidReq=' . $course_system_code . '&action=introduction" title="' . $course_title . '">' . api_trunc_str2 ( $course_title ,45) . '</a>';
                    } else {
                        $title .= '<a  href="javascript:void(0);" disabled="true">' . $course_title . '</a>';
                    }

                    if ($course ['is_valid_date'] == 0) {
                        $state .= '<span style="padding-right:4px;padding-left:20px;font-size:12px;font-style:italic;">(已过有效期)</span>';
                    }
                } else {
                    $title .= '<a href="javascript:void(0);" disabled="true">' . $course_title . '</a>';
                    $state .= "已关闭";
                }

                if ($course_visibility == COURSE_VISIBILITY_CLOSED) {
                    $state .= "已关闭";
                }
                //$icon = Display::return_icon ( "course.gif", get_lang ( "Student" ),array ('style' => 'vertical-align: middle;') );
                ?>
                    <ul class="u-course-time">
                        <li class="title-time p-514 ">课程<?=$i?></li>
                        <li class="title-name"><?=$icon . "&nbsp;" . $title . " " . $state?></li>
                        <li class="add-lab p-514 ">
                        <span class="finishe-statues finished f-13"></span>
                        加载试验</li>
                        <li class="lab-time f-13">
                           <span class=" finishe-statues video"></span>
                        14:02</li>
                        <!--<li class="lab-write ">-->
                        <li class="lab-write ">
                            <ul class="u-course-write f-13">
                                <li class="course-first">
                                        <?php
                                           if(api_trunc_str2($course ['description'])=='0'){
                                               echo '初级';
                                           }
                                           if(api_trunc_str2($course ['description'])=='1'){
                                               echo '中级';
                                           }
                                           if(api_trunc_str2($course ['description'])=='2'){
                                               echo '高级';
                                           }
                                           if(api_trunc_str2($course ['description'])!=='0' && api_trunc_str2($course ['description'])!=='1' && api_trunc_str2($course ['description'])!=='2'){
                                               echo '初级';
                                           }
                                           ?> 
                                </li>
                                <li>
                                       <?php		   
                                       if ($course ["is_required_course"]=='1' ) {
                                           ?><span  style="color:#FF0000">必修课</span><?php
                                       } else {
                                          echo "选修课"; 
                                           }?>
                                </li>
                                <li><?=$course ['credit_hours']."学时"?></li>
                                <!--li> <?=CourseManager::get_course_user_count($course ["code"])?> </li-->
                                <li><?=$course ['tutor_name']?></li>
                            </ul> 
                        </li>
                    </ul>
                             
                <?php
                $i++;
            } 
        ?>
    <div class="page">
        <ul class="page-list">
            <li class="page-num">总计<?=$total_rows?>个课程</li>
            <?php
            echo $pagination->create_links ();
            ?>
        </ul>
    </div>

        <?php }else{?>
        <div class="error">没有相关的课程</div>

        <?php }?>
	        </div>
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