<?php
$cidReset = true;
include_once ("inc/app.inc.php");

if (api_get_setting ( 'open_course_center' ) == 'false') {
	api_redirect ( 'learning_center.php' );
}

 if(api_get_setting ( 'enable_modules', 'course_center' ) == 'false'){
      echo '<script language="javascript"> document.location = "./learning_center.php";</script>';
      exit ();
 }
$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );

function get_my_applied_course_list($user_id, $sql_where = "", $page_size = NULL, $offset = 0) {
	$personal_course_list = array ();
	
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
	$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	
	$sql = "SELECT COUNT(*) FROM " . $table_course_subscribe_requisition . " t1 left join " . $table_course . " t2 on t1.course_code=t2.code LEFT JOIN " . $tbl_user . " t3 on t3.user_id=t1.user_id WHERE t1.user_id=" . Database::escape ( $user_id );
	if ($sql_where) $sql .= $sql_where;
	$total_rows = Database::get_scalar_value ( $sql );
	
	$sql = "SELECT t3.firstname,t1.creation_date,t1.audit_date,t3.user_id,t1.audit_result,t2.code,t2.title,t1.status,t2.visibility,t2.tutor_name FROM " . $table_course_subscribe_requisition . " t1 left join " . $table_course . " t2 on t1.course_code=t2.code LEFT JOIN " . $tbl_user .
			 " t3 on t3.user_id=t1.user_id WHERE t1.user_id=" . Database::escape ( $user_id );
	if ($sql_where) $sql .= $sql_where;
	if (empty ( $offset )) $offset = 0;
	if (isset ( $page_size )) $sql .= " LIMIT " . $offset . "," . $page_size;
	//echo $sql;
	

	$course_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	return array ("data_list" => $course_list, "total_rows" => $total_rows );
}

if (is_equal ( $_GET ["action"], "apply_del" )) {
	if (is_equal ( intval($_GET ["user_id"]), api_get_user_id () ) && isset ( $_GET ["code"] )) {
		$sql = "DELETE FROM " . $table_course_subscribe_requisition . " WHERE course_code='" . escape ( getgpc ( "code", "G" ) ) . "' AND user_id='" . escape ( getgpc ( "user_id" ), "G" ) . "'";
		$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

$rtn_data = get_my_applied_course_list ( api_get_user_id (), $sql_where, NUMBER_PAGE, getgpc ( "offset", "G" ) );
$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];
$url = WEB_QH_PATH . "course_applied.php";

$pagination_config = Pagination::get_defult_config ( $total_rows, $url );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'course_catalog.php', "name" => "选课中心" );
$interbreadcrumb [] = array ("url" => 'course_applied.php', "name" => "选课记录" );

include_once ("inc/page_header.php");
display_tab ( TAB_COURSE_CENTER );
?>
<style type="text/css">
        body{height:100%;_height:100%;font-size:14px;}
        .m-moclist .u-content{
            padding-bottom:5px;
        }
	.tbl_course{width:100%;margin:0 auto;border-collapse:collapse; text-align:left;}
	.tbl_course tr th{height:40px;line-height:40px;padding:4px 3px 4px 10px;}
	.tbl_course td{padding:7px 4px 7px 9px;}
	.dd2{ text-align:left;}
	.tbl_course tfoot tr td{border:none;background:#F8F8F8;}
	.l{margin-top:15px;float:left;padding:0 10px;}
        .l strong{ color:#13a654; }
        .u-course-time td img{  margin-right:15px;  }
	.tbl_course tfoot tr td .num{float:right;margin-top:10px;}
	.error{height:30px; text-align:center; color:red; font-weight:bold;}
	.tbl_course tfoot tr td ul{list-style:none;margin:0;padding:0; overflow:hidden;}
	.tbl_course tfoot tr td ul li{ display:inline-block;float:left;}
	.tbl_course tfoot tr td ul li a{ display:block;float:left;border:1px solid #CCC; color:#3F3F41;margin:5px;padding:5px;}
	.tbl_course tfoot tr td ul li.la{margin:0 !important; }
        .tbl_course tfoot tr td ul li.la a{background:#13a654;}
	.tbl_course tfoot tr td ul li a:hover{background: #37BE5D;text-decoration: none;color: #FFFFFF}
        tfoot {  display: table-footer-group;  vertical-align: middle;  border-color: inherit;}
        tr {display: table-row;vertical-align: inherit;}
</style>
   
     <div class="clear"></div> 
	<div class="m-moclist">
	  <div class="g-flow" id="j-find-main">
               <div class="b-30"></div>
       <div class="g-container f-cb">
          <div class="g-sd1 nav">
               <div class="m-sidebr" id="j-cates">
                           <ul class="u-categ f-cb">
                               <li class="navitm it f-f0 f-cb first cur"  data-id="-1" data-name="学习中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                                   <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="学习中心">学习中心</a>
                               </li>
                               <?php  
                                $sql="select id,title from setup order by id";
                                  $res=  api_sql_query_array($sql);
                                  foreach ($res as $value) {
                                      ?>
                            <li class="navitm it f-f0 f-cb haschildren"  data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" <?=$value['id']==$id?' style="color:green;font-weight:bold"':''?> title="<?=$value['title']?>" href="<?=URL_APPEND."portal/sp/learning_before.php?id=".$value['id']?>"><?=$value['title']?></a>
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
                                                                $url = "learning_before.php?id=".$value['id']."&category=" . $category ['id'];
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
                                <li class="navitm it f-f0 f-cb haschildren course-mess"  data-id="-1" data-name="选课记录">
                                     <a class="f-thide f-f1" title="选课记录" href="./course_applied.php" style="color:green;font-weight:bold">选课记录</a>
                                </li>
                                <li class="navitm it f-f0 f-cb haschildren course-mess"  data-id="-1" data-name="课程表">
                                     <a class="f-thide f-f1" title="课程表" href="./syllabus.php">课程表</a>
                                </li>
                               </ul>
                        </div>

          </div>



           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
				    <div class="top f-cb j-top">
					   <h3 class="left f-thide j-cateTitle title">
					      <span class="f-fc6 f-fs1" id="j-catTitle">选课记录</span>
					   </h3>
					   <div class="j-nav nav f-cb"> 
					   </div>
					</div>

                     <div class="j-list lists" id="j-list"> 
                     <div class="u-content">
		         <h3 class="sub-simple u-course-title"></h3> 
                                 
                                <div class="module_content">
                                    <table cellspacing="0" cellpadding="0" class="tbl_course">



                                        <?php
                                        if (is_array ( $personal_course_list ) && $personal_course_list) {?>

                                            <tr>
                                            <th class="case-table-title">课程名称</th>
                                            <th>讲师</th>
                                            <th>申请时间</th>
                                            <th>审批时间</th>
                                            <th>审批状态</th>
                                            <th>操作</th>
                                        </tr><?php
                                            foreach ( $personal_course_list as $course ) {
                                                $title = $state = '';
                                                $course_system_code = $course ['code'];
                                                $course_title = $course ['title'];
                                                if ($course ["visibility"] != COURSE_VISIBILITY_CLOSED) {
                                                    ?>
                                        <tr>
                                            <td colspan="6">
                                                <h3 class="sub-simple u-course-title"></h3>
                                            </td>
                                        </tr>
                                                    <tr class="u-course-time">
                                                        <?php $ip = $_SERVER["SERVER_ADDR"];?>
                                                        <td class="line-title"><a  onClick='openWindow(900,300,"iframe:http://<?=$ip.WEB_QH_PATH;?>course_info.php?code=<?=$course_system_code?>", "<?=$course_title?>") '  title="<?=$$course_title?>">
                                                            <?=$course_title?></a></td>
                                                        <td><?=$course ["tutor_name"]?></td>

                                                        <td><?=substr ( $course ["creation_date"], 0, 16 )?></td>

                                                        <td><?=substr ( $course ["audit_date"], 0, 16 )?></td>

                                                        <td>

                                                            <?php
                                                            if ($course ["audit_result"] == 0)
                                                                echo "已提交,待批准中";
                                                            elseif ($course ["audit_result"] == 1)
                                                                echo "已通过审核";
                                                            elseif ($course ["audit_result"] == 2)
                                                                echo "已审核,未通过";
                                                            ?>
                                                        </td>
                                                        <td><?php
                                                            if ($course ["audit_result"] == 0) {
                                                                echo '<a href="' . $_SERVER ['PHP_SELF'] . '?action=apply_del&code=' . $course_system_code . '&user_id=' . $course ["user_id"] . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( get_lang ( 'ConfirmYourChoice' ) ) . "'" . ')) return false;">' . Display::return_icon (
                                                                    'delete.gif', get_lang ( 'Delete' ) ) . '</a>';
                                                            }
                                                            ?>

                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        }else{
                                        ?>
                                            <div class="error b">没有相关课程</div>

                        <?php }?>

                                    </table>
                                </div>
                                <div class="page">
                                    <ul class="page-list">
                                        <li class="page-num">
                                            <?php
                                            if($total_rows>0){
                                                echo '总计'.$total_rows.'个课程';
                                        }
                                            ?></li>

                                    <?php
                                    echo $pagination->create_links ();
                                    ?></div>

                                    </ul>
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
        include_once('./inc/page_footer.php');
?>
 </body>
</html>