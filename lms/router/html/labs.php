 <?php  
include('includes/conf.php');
//page_header();
$cidReset = true;
include_once ("../../portal/sp/inc/app.inc.php");
include_once ("../../portal/sp/inc/page_header.php");

$labs_category=trim(getgpc("labs_category","G"));
  
$sql='select * from labs_category where id='.$labs_category;
$category_res=  api_sql_query_array_assoc($sql);
$category_data=$category_res[0]; 
$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];
 
 
if(isset($_GET['labs_category']) && $_GET['labs_category']!==''){
    $labs_category= trim($_GET['labs_category']);
    $sql_where.="  AND  `labs_category`=".$labs_category;
    $param.='labs_category='.$labs_category;
}
if (is_not_blank ( $sel )) {
	$keyword = Database::escape_str ( urldecode ($sel ), TRUE );   
	$sql_where.= " AND ( `name`  LIKE '%" . trim($keyword) . "%')";
	$param .= "&keyword=" . urlencode ( $keyword );
}
if ($param {0} == "&") $param = substr ($param,1);

$sql1 = "SELECT COUNT(*) FROM `labs_labs`";
if ($sql_where){
	$sql1 .=' where 1'.$sql_where;
}
 
$total_rows = Database::get_scalar_value ( $sql1 );

$sql="select `id`,`name` ,`description`,`id` FROM `labs_labs`";  
if ($sql_where){    
    $sql.=' where 1'.$sql_where;
}
 
$offset = $_GET['offset'];
$sql .= " order by `id`";
if(!$sel){
$sql .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . ",10";     
}
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$arr= array ();

while ( $arr = Database::fetch_row ( $res) ) {
	$arrs [] = $arr;
}
$rtn_data=array ("data_list" => $arrs, "total_rows" => $total_rows );
$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];
$url ="labs.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

//all router  course
$sql = "SELECT `name` FROM `vslab`.`labs_category` WHERE parent_id=0 ORDER BY `tree_pos`";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
$j = 0;
while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    $category_tree[$j] = $vm[0];
}
?> 
<style type="text/css">
        body{height:100%;_height:100%;}
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
                               <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="路由课程" id="auto-id-D1Xl5FNIN6cSHqo0">
                                   <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" href="labs.php" title="路由课程">路由课程</a>
                               </li>
 <?php foreach ( $category_tree as $k1 => $v1){ 
                   $sqli="SELECT id FROM  `labs_category` where name='".$category_tree[$k1]."'";
    		   $ids= DATABASE::getval($sqli,__FILE__,__LINE__);
                   if($ids==$_GET['labs_category']){
                     $seelectd=" first cur";
                   }else{
                     $seelectd=" haschildren";
                   }
 ?>
                    <li class="navitm it f-f0 f-cb<?=$seelectd?>" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                                            <a class="f-thide f-f1" href="labs.php?labs_category=<?=$ids?>" title="<?=$category_tree[$k1]?>"><?=$category_tree[$k1]?></a>
                                        </li>
 <?php   }?>
                               </ul>
                        </div>
</div>



           <!--  右侧 -->
		   <div class="g-mn1" >
		        <div class="g-mn1c m-cnt" style="display:block;">
				    <div class="top f-cb j-top">
					   <h3 class="left f-thide j-cateTitle title">
					      <span class="f-fc6 f-fs1" id="j-catTitle">
                                                  <?php
                                                if(isset($_GET['labs_category']) && $labs_category!=''){
                                                    if($category_data["name"]!==''){
                                                        echo "路由实训 > ".$category_data["name"];
                                                    }else{
                                                        echo "路由实训"; 
                                                    } 
                                                }else{
                                                    echo "路由实训 > 全部路由课程";
                                                }
                                              ?></span>
					   </h3>
					   <div class="j-nav nav f-cb"> 
					   </div>
					</div>

                     <div class="j-list lists" id="j-list"> 
                   <div class="u-content">
		         <h3 class="sub-simple u-course-title"></h3> 
                                <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                                    <tr>
                                        <th height="35%">名称</th>
                                        <th width="45%">描述</th>
                                        <th width="8%">状态</th>
                                        <th width="5%">进入</th>
                                    </tr>
                                    <tr>
                                        <td colspan="10"><h3  class="sub-simple u-course-title"></h3> </td>
                                    </tr>
                         <?php
                                    if (is_array ( $personal_course_list ) && $personal_course_list) {
                                ?>
                                <?php
                                for($i=0;$i<count($personal_course_list);$i++){
                                ?>
                                <tr class="u-course-time">
                                    <td class="labs-table-name">
                                        <!--<a href="dynamic_maps.php?action=show&id=<?=$personal_course_list[$i][3]?>">-->
                                        <img src="images/lab.png" width="25" height="25" style="vertical-align: middle;">
                                        <?=api_trunc_str2($personal_course_list[$i][1],25)?>
                                        <!--</a>-->
                                    </td>
                                    <td><?=$personal_course_list[$i][2]?></td>
                                    <td>
                                    <?php
                                        $labsId=$personal_course_list[$i][3];
                                        $sql_d="select `name` from `labs_labs` where id=".$labsId;
                                        $names= DATABASE::getval($sql_d,__FILE__,__LINE__);
                                        $USERID=$_SESSION['_user']['user_id'];
                                        $sql1="select count(*) from `labs_run_devices` where `labs_name`='".$names."' and `USERID`=".$USERID;
                                        $run_devices_count=DATABASE::getval($sql1,__FILE__,__LINE__);
                                        if($run_devices_count==0){
                                            $return='未开启';
                                        }else{
                                            $return='<span style="color:red">已开启</span>';
                                        }
                                        echo $return;
                                    ?>
                                    </td>
                                    <td><a href="dynamic_maps.php?action=show&id=<?=$personal_course_list[$i][3]?>"><img alt="进入路由实验" title="进入路由实验" src="images/restore.png" width="32" height="32"></a></td>
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
        include_once('../../portal/sp/inc/page_footer.php');
?>
 </body>
</html>