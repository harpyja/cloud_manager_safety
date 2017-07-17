<?php
/**
 * User: changzf
 * Date: 13-4-15
 * Time: 下午5:23
 * To change this template use File | Settings | File Templates.
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../../inc/global.inc.php');
include_once ('exercise.lib.php');

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );
set_time_limit ( 0 );
include (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');

$alpha = array ('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );

$form = new FormValidator ( 'export_report' );
$form->addElement ( 'hidden', 'type', getgpc ( 'type', 'G' ) );
$form->addElement ( 'html', '<tr><td align="center"><h2>您确定要导出实验报告吗？</h2></td></tr>');
$form->addElement ( 'hidden', 'file_type', 'xls' );

include (api_get_path ( INCLUDE_PATH ) . "conf/templates.php");
$form->addElement ( 'html', '<tr><td align="center"><h2>
                                    <input class="inputSubmit" name="submit" value="确定" type="submit">&nbsp;<button type="button" class="cancel" onclick="javascript:self.parent.tb_remove();" name="cancle">取消</button>&nbsp;&nbsp;</h2>
                            </td></tr>' );
$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );
if ($form->validate ()) {
    $export = $form->exportValues ();
    $export = $form->getSubmitValues ();
    $file_type = $export ['file_type'];
    $question_type = $export ['question_type'];
    $data = array ();
    $data [] = array ('编号', "实验报告名称", "用户名","用户真实名称", "用户部门", "时间", "得分", "评语", "结果" );
    $filename = 'ExportReport_' . date ( 'YmdHis' ); //导出文件名
//    $sql = "select `id`,`report_name`,`user`,`submit_date`,`score`,`comment`,`return` from `vslab`.`report` where  `marking_status`=1";
    $sql = "select report.id,report.report_name,report.user,user.firstname,user.dept_id,report.submit_date,report.score,report.comment,
    report.return from user right join report on user.username=report.user where report.marking_status=1 ";

    // echo $sql;exit;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $questions = array ();
    $line = 2;
    while ( $row = Database::fetch_array ( $res) ) {
     // echo '<pre>';  var_dump($row);echo '</pre>';exit;
            $dept_name=Database::getval("select dept_name from sys_dept where id='".intval($row['dept_id'])."'",__FILE__,__LINE__);
            $row ['编号'] = intval($row['id']);
            $row ['实验报告名称'] = $row['report_name'];
            $row ["用户名"] = $row ["user"];
            $row ["用户真实名称"] = $row ["firstname"];
            $row ["用户部门"] = $dept_name;
            $row ["时间"] = $row ["submit_date"];
            $row ["得分"] = $row ["score"];
            $row ['评语'] = $row ["comment"];
            if($row ['return']==1){
                $return='通过';
            }else{
                $return='未通过';
            }
            $row ['结果'] = $return;
            unset ( $row ["id"], $row ["id"] );
            $questions [] = $row;
            $line ++;
    }

    foreach ( $questions as $question ) {
        $data [] = $question;
    }

     //  echo '<pre>';var_dump($data);echo '</pre>';  exit;
    switch ($file_type) {
        case 'xls' :
            Export::export_table_data ( $data, $filename, 'xls', false );
            break;
    }
    tb_close ( 'report.php' );
}
Display::display_header ( null, FALSE );

$form->display ();

Display::display_footer ();