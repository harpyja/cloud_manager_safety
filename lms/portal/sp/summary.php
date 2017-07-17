<?php
/**----------------------------------------------------------------
liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("../../main/inc/global.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");
$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());
 
 
$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'exam_list.php', "name" => '大赛简介' );
display_tab ( TAB_LEARNING_CENTER );
?>
 <?php
   //获取简介内容
         	$sq="select `title`,`content` from `summary` limit 1";
         	$res = api_sql_query ( $sq, __FILE__, __LINE__ ); 
         	$content = Database::fetch_row ( $res );    
//          var_dump($content);  //一条记录
  ?>
 
<aside id="sidebar" class="column summary open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside><!-- end of sidebar -->

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?=display_interbreadcrumb ( $interbreadcrumb, null )?></h4>
    <article class="module width_full hidden"  >
        <header>
          <?php  echo $content[0];
          ?>
        </header><br>
         简介内容：
        <?php 
        echo  $content[1];
        ?>
    </article>
 
</section>


</body>
</html>
