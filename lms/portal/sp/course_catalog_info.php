 <?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
if (api_get_setting ( 'enable_modules', 'course_center' ) != 'true') api_redirect ( 'learning_center.php' );

include_once ("inc/page_header.php");
$category_id = intval(getgpc ( "category_id", "G" ));
if($category_id){
    $sql="select * from course_category where id=".$category_id;
    $catagoryData       =api_sql_query_array_assoc($sql,__FILE__,__LINE__);
    $catagoryName       = $catagoryData[0]['name'];//分类名称
    $catagorycode      = $catagoryData[0]['code'];//分类名称
    $CourseDescription  = $catagoryData[0]['CourseDescription'];//课程介绍
    $CurriculumStandards= $catagoryData[0]['CurriculumStandards'];//课程标准
    $AssessmentCriteria = $catagoryData[0]['AssessmentCriteria'];//考核标准
    $TeachingProgress   = $catagoryData[0]['TeachingProgress'];//教学进度
    $StudyGuide         = $catagoryData[0]['StudyGuide'];//学习指导
    $TeachingGuide      = $catagoryData[0]['TeachingGuide'];//教学指导
    $InstructionalDesignEvaluation= $catagoryData[0]['InstructionalDesignEvaluation'];//教学设计评价
    
}
?>
	 <!-- 导航结束 -->
     <div class="b-60"></div> 
    <div class="g-doc">
     <div class="b-h20"></div>
	 <div class="f-bg m-top f-cb">
	    <div class="g-mn2 left">
		   <div class="g-mn2c">
		      <h2>
                          <a href="course_catalog.php?category_id=<?=$category_id?>"><?=$catagoryName?></a>
		      </h2>
			  <p class="f-fc6">
                             &nbsp;&nbsp;&nbsp;<?=$CourseDescription?> 
			  </p>
		   </div>
		</div>

		<div class="g-sd2">
		    <div class="m-recimg" style="border:1px solid rgb(224, 219, 219)">
                        <a href="course_catalog.php?category_id=<?=$category_id?>">
			  <img class="img" src="../../storage/category_pic/<?=$catagorycode?>" width="350" height="240" title="<?=$catagoryName?>" alt="<?=$catagoryName?>"> 
                        </a>
		    </div>
		</div>
	 </div>

	 <div class="b-40"></div>
	   <div class="g-wrap f-cb">
	       <div class="g-mn2 f-bg m-infomation">
<!--			  <div class="top">课程介绍</div>
			  <div class="bottom f-richEditorText">
                          <p><?=$CourseDescription?></p>
			  </div>-->

                          <div class="top">课程标准</div>
			  <div class="bottom f-richEditorText">
                              <pre><?php
                                    if($CurriculumStandards!==''){
                                        echo $CurriculumStandards;
                                    }else{
                                        echo "无";
                                    }
                                      ?></pre>
			  </div>


			  <div class="top">考核标准</div>
			  <div class="bottom f-richEditorText">
                            <pre><?php
                                    if($AssessmentCriteria!==''){
                                        echo $AssessmentCriteria;
                                    }else{
                                        echo "无";
                                    }
                                      ?> </pre>
			  </div>

			  <div class="top">教学进度</div>
			  <div class="bottom f-richEditorText">
                            <pre><?php
                                    if($TeachingProgress!==''){
                                        echo $TeachingProgress;
                                    }else{
                                        echo "无";
                                    }
                                      ?></pre>
			  </div>

			   <div class="top">学习指导</div>
			  <div class="bottom f-richEditorText">
                            <pre><?php
                                    if($StudyGuide!==''){
                                        echo $StudyGuide;
                                    }else{
                                        echo "无";
                                    }
                                      ?> </pre>
			  </div>

			  <div class="top">教学指导</div>
			  <div class="bottom f-richEditorText">
                            <pre><?php
                                    if($TeachingGuide!==''){
                                        echo $TeachingGuide;
                                    }else{
                                        echo "无";
                                    }
                                      ?> </pre>
			  </div>
             

			 <div class="top">教学设计评价</div>
			  <div class="bottom f-richEditorText">
                            <pre><?php
                                    if($InstructionalDesignEvaluation!==''){
                                        echo $InstructionalDesignEvaluation;
                                    }else{
                                        echo "无";
                                    }
                                      ?> </pre>
			  </div>
		   </div> 
	   </div>
   </div>
<?php
        include_once './inc/page_footer.php';
?>
 </body>
</html>