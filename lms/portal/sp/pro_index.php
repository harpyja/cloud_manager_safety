<?php
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ('../../main/exercice/exercise.class.php');

include_once ("inc/page_header.php");
$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz

$sql="SELECT * 
FROM  `project` 
WHERE  `release` =1";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
    $arr[]=$row;
    
}
 
?>
<style>
    ul,li{
        padding:0;
        margin:0;
    }
    li{
        list-style:none;
    }
      m-moclist  .j-list:after{
        display:block;
        clear:both;
        content:"";
    } 
    m-moclist  .u-content:after{
        display:block;
        clear:both;
        content:"";
    }  
    .safe-lists{
        padding:0;
        margin:0;
        width:100%;
    }
    .safe-lists li{
       border: 1px rgb(97, 97, 102);
        width: 29.5%;
        height: 100px;
        margin-left:15px;
        margin-right:15px;
        margin-bottom: 30px;
        display:inline-block;
    }
    .safe-lists li .img{
         width: 60px;
        height: 60px;
        margin-top: 20px;
        margin-left: 10px;
        margin-right:10px;
        float: left;
        display: inline;
    }
    .safe-lists li .biao{
        width: 190px;
        height: 25px;
        text-align: left;
        color: #7CB1D2;
        font-size: 13px;
        margin-top: 14px;
        float: left;
    }
    .safe-lists li .anniu{
        width: 90px;
        height: 40px;
        margin-top:5px;
        overflow: hidden;
        float: left;
        border: 0px solid black;
    }
     .safe-lists li .number{
        float: left;
        color: #939393;
        font-style: italic;
        font-size: 12px;
        height: 35px;
        line-height: 55px;
    }

  
</style>

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
                                         安全评估
                                    </span>
                                </h3>
                            </div>
                            <div class="j-list lists" id="j-list"> 
                                <!--<article class="lab-content study-list">-->
                                <div class="u-content">
                                    <ul id="list" class="safe-lists">
                                        <?php foreach ($arr as $val){  ?>   
                                        <li class="li"> 
                                            <div class="img"><img src="<?php echo $val['upfile'] ?>" width="55" height="55"></div>
                                            <h2 class="biao"><?php echo $val['name']?></h2>
                                            <div class="anniu"><a href="assess.php?pro_id=<?php echo $val['id'];?>">开始评估</a></div>
                                        </li> 
                                        <?php }?>
                                    </ul>
                                  <!--</article>-->
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