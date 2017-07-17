 <?php  
//page_header();
$cidReset = true;
include_once ("./inc/app.inc.php");
include_once ("./inc/page_header.php");

$labs_category=trim(getgpc("labs_category","G"));

$sql='select * from labs_category where id='.$labs_category;
$category_res=  api_sql_query_array_assoc($sql);
$category_data=$category_res[0]; 
$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];



if (is_not_blank ( $sel )) {
	$keyword = Database::escape_str ( urldecode ($sel ), TRUE );   
	$sql_where.= "  ( `name`  LIKE '%" . trim($keyword) . "%'  or `description` LIKE '%" . trim($keyword) . "%')";
	$param .= "&keyword=" . urlencode ( $keyword );
}

if ($param {0} == "&") $param = substr ($param,1);
$exam_type = Database::get_main_table ( exam_type );
$sql1 = "SELECT COUNT(id) from $exam_type  where  enable=1  ";
if ($sql_where){
	$sql1 .=' and '.$sql_where;
}
$total_rows = Database::get_scalar_value ( $sql1 );

$sql = "select id,name,description FROM  $exam_type where enable=1 ";
if ($sql_where) {$sql .= " and " . $sql_where;}
$offset = $_GET['offset'];
$sql .= " order by `id`";
$sql .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . ",10";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$arr= array ();

while ( $arr = Database::fetch_row ( $res) ) {
	$arrs [] = $arr;
}
$rtn_data=array ("data_list" => $arrs, "total_rows" => $total_rows );
$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];
$url ="exam_list.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );
?> 
<style type="text/css">
        body{height:100%;_height:100%;font-size:14px;}
	.tbl_course{width:100%;margin:0 auto;border-collapse:collapse; text-align:left;}
	.tbl_course tr th{height:40px;line-height:40px;padding:4px 5px;}
	.tbl_course td{padding:4px 5px;}
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
                    <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="考试中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                         <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="考试中心">考试中心</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的考试" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的考试" href="exam_list.php"  style="color:green;font-weight:bold">我的考试</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="考试成绩查询" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="考试成绩查询" href="exam_result.php">考试成绩查询</a>
                    </li> 
                </ul>
            </div>
        </div>



           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
				    <div class="top f-cb j-top">
					   <h3 class="left f-thide j-cateTitle title">
					      <span class="f-fc6 f-fs1" id="j-catTitle">我的考试</span>
					   </h3>
					   <div class="j-nav nav f-cb"> 
					   </div>
					</div>

                     <div class="j-list lists" id="j-list"> 
                   <div class="u-content">
		         <h3 class="sub-simple u-course-title"></h3> 
                                <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                                    <tr>
                                        <th width="10%" align="center">序号</th>
                                        <th width="18%">竞赛名称</th>
                                        <th width="50%">描述</th>
                                        <th width="10%" align="center">考试项目</th>
                                        <th width="12%" align="center">查看所有考卷</th>
                                    </tr>
                                    <tr>
                                        <td colspan="10"><h3  class="sub-simple u-course-title"></h3> </td>
                                    </tr>
                         <?php
                                    if (is_array ( $personal_course_list ) && $personal_course_list) {
                                ?>
                                <?php
                                for($i=0;$i<count($personal_course_list);$i++){
                                  $count_users=Database::getval ( "SELECT COUNT(*) FROM exam_rel_user AS t1,exam_main AS t2 WHERE t1.user_id=".Database::escape ( $user_id )." AND t1.exam_id=t2.id AND t2.active=1 AND t2.type='".$personal_course_list[$i][0]."'", __FILE__, __LINE__ );
                                ?>
                                <tr class="u-course-time">
                                     <td align="center"><?=$personal_course_list[$i][0]?></td>
                                     <td><?=$personal_course_list[$i][1]?></td>
                                     <td><?=$personal_course_list[$i][2]?></td>
                                     <td align="center"><?=$count_users?></td>
                                     <td align="center">
                                         <?php
                                         if($count_users!=='0'){
                                             echo '<a href="exam_center.php?type='.$personal_course_list[$i][0].'">
                                             <img src="../../themes/img/crs_quiz.gif" width="24" height="24">
                                         </a>';
                                         }else{
                                            echo '<img src="../../themes/img/crs_quiz_na.gif" width="24" height="24"> '; 
                                         }
                                         ?>
                                     </td>
                                </tr>
                                <tr>
                                        <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                                </tr>
                                <?php
                                }
                                ?>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5">
                                            <span class="l">
                                                总计：<strong><?=$total_rows?></strong> 条记录
                                            </span>
                                            <span class="num">
                                                <ul class="pages">
                                                 <?php
                                                     echo $pagination->create_links ();
                                                 ?>
                                                </ul>
                                            </span>  
                                        </td>
                                    </tr>
                               </tfoot>
                            <?php
                                } else {
                            ?>
                                <tr>
                                    <td colspan="5" class="error">没有相关实验</td>
                                </tr>
                            <?php
                                    }
                            ?>
                            </table>
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