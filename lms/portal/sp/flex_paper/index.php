<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

require ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');

$cc = $_GET['cc'];

$path  =  $_REQUEST['url'];
//var_dump($path);
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head> 
        <title>FlexPaper</title>         
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
        <style type="text/css" media="screen"> 
			html, body	{ height:100%; }
			body { margin:0; padding:0; overflow:auto; }   
			#flashContent { display:none; }
			.pdf{float:left;width:75%; overflow:hidden;}
			.pdfinfo{float:right;background:#FAFAFA; width:25%;height:100%;}
			.infos{padding:8px;}
			.infos h3{font-weight:bold; font-family:Georgia, "Times New Roman", Times, serif;}
			.infos ul{margin:0;padding:0;}
			.infos ul li{ list-style:none; height:35px;line-height:35px;border-bottom:1px dashed #999999;padding:0 10px;}
			.infos ul li a{ text-decoration:none;color:#000000;}
			.infos ul li a:hover{color:#FF0000;}
			.infos ul li span{float:right;}
			.infos ul li span a{font-size:12px;}
			.infos ul li:hover{background:#CCC;}
        </style> 
		
		<script type="text/javascript" src="js/flexpaper_flash.js"></script>
    </head> 
    <body> 
    	<div class="pdf">
	        <a id="viewerPlaceHolder" style="width:100%;height:100%;display:block"></a>

	        <script type="text/javascript">
				var fp = new FlexPaperViewer(
						 'FlexPaperViewer',
						 'viewerPlaceHolder', { config : {

						 SwfFile : escape("<?php print $path;?>"),
						 Scale : 0.6,
						 ZoomTransition : 'easeOut',
						 ZoomTime : 0.5,
						 ZoomInterval : 0.2,
						 FitPageOnLoad : true,
						 FitWidthOnLoad : false,
						 PrintEnabled : true,
						 FullScreenAsMaxWindow : false,
						 ProgressiveLoading : false,
						 MinZoomSize : 0.2,
						 MaxZoomSize : 5,
						 SearchMatchAll : false,
						 InitViewMode : 'Portrait',

						 ViewModeToolsVisible : true,
						 ZoomToolsVisible : true,
						 NavToolsVisible : true,
						 CursorToolsVisible : true,
						 SearchToolsVisible : true,

  						 localeChain: 'en_US'
						 }});
	        </script>
        </div>

		<!-- THE FOLLOWING CODE BLOCK CAN SAFELY BE REMOVED, IT IS ONLY PLACED HERE TO HELP YOU GET STARTED. -->

          <div class="pdfinfo">
          		<div class="infos">
                	<h3>相关课件列表</h3>
						<?php
                                    $sql = "SELECT title,path,id,attribute FROM crs_courseware WHERE cw_type='swf' AND visibility=1 AND cc=" .$cc. " ORDER BY display_order ASC,id DESC";
                        //$all_courseware = Database::fetch_row ( $sql, __FILE__, __LINE__ );
                       // $row_index = 0;
                        $all_courseware = api_sql_query ( $sql, __FILE__, __LINE__ );
                        $vm= array ();
        //var_dump($all_courseware);
                        while ( $vm = Database::fetch_row ( $all_courseware) ) {
                            $vms [] = $vm;
                        }
						echo "<ul>";
                        foreach ( $vms as $cw_in ) {
                            $down = dirname($cw_in[1]);
                           // var_dump($cw_in[1]);
                           $url = WEB_QH_PATH . 'flex_paper/index.php?cw_id=' . $cw_in[2] . '&url='.$cw_in[1].'&cc='.$cc;						
						    
                            echo "<li><a href=".$url.">".$cw_in[0]."</a><span><a href=".$down."/".$cw_in[3].">下载</a></span></li>";
						
                        }
						echo "</ul>";
                       ?>
                </div>	
          </div>
   </body> 
</html> 
