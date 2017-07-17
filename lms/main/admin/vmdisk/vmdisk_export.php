<?php
/**
 *by changzf
 *on 2012/07/11
 *This is a page to export a single course
 */
header("content-type:text/html;charset=utf-8");
include_once ("../../inc/global.inc.php");
Display::display_header (null, FALSE );

//$path = getcwd();//获取当前系统目录
if($_POST['sub'])
{
    $tname = $_FILES["ufile"]["tmp_name"];
    $fname = $_FILES["ufile"]["name"];

    $path=URL_ROOT.'/www'.URL_APPEDND;

    $name=explode('.',$fname);
    $tar=substr(strrchr($fname,"."),1);
    if($tar=='tar'){
        move_uploaded_file($tname,"$path/storage/$fname");

        //解压tar包
       exec("cd $path/storage ;tar -xvf $path/storage/$fname");

        exec("chmod -R 777  $path/storage/$name[0] ");
       //删除tar包
       unlink($fname);

        //获取分类数据
        $ccc = file_get_contents("$path/storage/$name[0]/course_category");
        $aaa=unserialize($ccc);

        //查询是否有该类
        $s='select id from `vslab`.`course_category` where name="'.$aaa[3].'"';
        $cate=Database::getval ( $s, __FILE__, __LINE__ );
//echo $cate;

        if($cate==''){
            //插入新的分类
            $sq = "INSERT INTO  `vslab`.`course_category` ( `id` , `parent_id` , `sn` , `name` , `code` ,`tree_pos` , `children_count` ,`auth_cat_child` ,`last_updated_date` ,`org_id` ,`CourseDescription` ,`CurriculumStandards` ,`AssessmentCriteria` ,`TeachingProgress` ,`StudyGuide` ,`TeachingGuide` ,`InstructionalDesignEvaluation`)VALUES
(''".",'$aaa[1]'".",'','$aaa[3]'".",'$aaa[4]'".",'$aaa[5]'".",'$aaa[6]'".",'$aaa[7]'".",'$aaa[8]'".",'$aaa[9]'".",'$aaa[10]'".",'$aaa[11]'".",'$aaa[12]'".",'$aaa[13]'".",'$aaa[14]'".",'$aaa[15]'".",'$aaa[16]');";
            $res = api_sql_query ( $sq, __FILE__, __LINE__ );

            exec("rm -r /tmp/www/lms/storage/DATA/temp/");

        }
        //server;
        exec("mysql -u".DB_USER."  ".DB_NAME."< $path/storage/$name[0]/crs_courseware.sql");
        $course="UPDATE `vslab`.`course` SET `category_code` = '$cid' WHERE `course`.`code` = '$name[0]'";

        api_sql_query ( $course, __FILE__, __LINE__ );

        exec("rm -rf $path/storage/$name[0]");
        tb_close ('vmdisk_list.php');
        exit;
    }else{
        $a='<span style="font-size:14px;color:red;border:1px;float:left;line-height:14px;">&nbsp;上传的文件名只限于: *.tar</span>';
    }

}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
</head>
<style type="text/css">
	table{border-collapse:collapse;margin:0;}
	table,table tr td{border:1px dashed #CCCCCC;padding:0 10px 0 10px;}
</style>
<body>
    <div style="height:10px"></div>
    <form action="" method="post" enctype="multipart/form-data">
        <table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
            <tr>
                <td align="right" width="20%" bgcolor="#EEE" height="50px">选择导入文件:</td>
                <td><input type="file" name="ufile"><?php echo $a;?></td>
            </tr>
            <tr>
                <td align="right" width="20%" bgcolor="#EEE" height="50px">&nbsp;</td>
                <td>&nbsp;<input type="submit" name="sub" value="确定" class="inputSubmit">
&nbsp;<button type="button" class="cancel" onClick="javascript:self.parent.tb_remove();" name="cancle">取消</button> 

            </tr>
        </table>
    </form>
</body>
</html>
