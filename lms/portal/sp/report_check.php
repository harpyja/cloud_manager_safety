<?php
include_once ("inc/app.inc.php");
include_once ("inc/page_header_report.php"); 
$action=$_GET['action'];
$report_id=$_GET['id'];
$user = api_get_user_name ();
$sql="select * from `report` where `id` = '".$report_id."' and `user`='".$user."' and status = '1' ";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $data = Database::fetch_array ( $result, 'ASSOC' ) ) {   ?>

<!--        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告名称</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['report_name']?></h4>
                </div>
            </div>
        </div>-->
<style>
    body{
        padding:0 0 120px 0;
        background:#fff;
    }
   
</style>
        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告作者</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['user']?></h4>
                </div>
            </div>
        </div>

        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告课程</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['code']?></h4>
                </div>
            </div>
        </div>

        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告实验目的及要求</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['purpose']?></h4>
                </div>
            </div>
        </div>

        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告实验设备环境及要求</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['equipment']?></h4>
                </div>
            </div>
        </div>
        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告实验内容与步骤</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <?php 
                        $a=explode(";",$data['content']);
                        foreach($a as $key => $value){  
                            $b=explode("_",$value);
                            $c=$b['0'];
                    ?>
                    <h4 class="j-name name f-thide"><?= $c?></h4>
                    <?php }?>
                </div>
            </div>
        </div>
        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告实验结果</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-i d-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['result']?></h4>
                </div>
            </div>
        </div>
        <div class="m-learnChapterNormal f-pr">
            <div class="titleBox j-titleBox f-cb" id="auto-id-HIAtBpJJKXmR4sSU">
                 <h3 class="j-titleName name left f-thide">实验报告实验分析与讨论</h3>
            </div>
            <div class="f-pa line j-line"></div>
            <div class="lessonBox j-lessonBox">
                <div class="u-learnLesson normal f-cb f-pr last" id="auto-id-NERqH6rnJ2CidLD9">
                    <h4 class="j-name name f-thide"><?= $data['analysis']?></h4>
                </div>
            </div>
        </div>
<?php } ?>
