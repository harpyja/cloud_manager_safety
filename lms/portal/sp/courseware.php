<?php
	header("content-type:text/html;charset=utf-8");
	$language_file = array ('admin', 'registration' );
	$cidReset = true;
	require ('../../main/inc/global.inc.php');
	require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
	require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
	require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
	$lessonid = getgpc('cidReq');
	
	//sql data
	$step = Database::get_main_table ( simulation);
	
	//step_num
	$step_num_sql="SELECT COUNT( * ) FROM  $step WHERE course_id='".$lessonid."'";
	$step_num = Database::getval( $step_num_sql, __FILE__, __LINE__ );
	
	//step content
	$step_sql = "select * FROM  $step where course_id='".$lessonid."'";
	$res = api_sql_query ( $step_sql, __FILE__, __LINE__ );
	$st= array ();
	while ( $st = Database::fetch_row ( $res) ) {
	$ste [] = $st;
	}
	
	foreach ( $ste as $k1 => $v1){
		foreach($v1 as $k2 => $v2){
			$arr[$k1][]  = $v2;
		}
	}
	
	//Coordinate_1
	for($i=0;$i< $step_num;$i++){$steps[]= $arr[$i][8];}
	for($j=0;$j<$step_num;$j++){$stepss[]=unserialize($steps[$j]);}
	
	$aa =json_encode($stepss);
	echo  "<script type=\"text/javascript\">";
	echo  "var aa =$aa ; \n";
	
	//action
	for($l=0;$l< $step_num;$l++){$n[]= $arr[$l][9];}
	for($m=0;$m<$step_num;$m++){$ns[]=unserialize($n[$m]);}
	
	$nss =json_encode($ns);
	echo  "var nss =$nss ; \n";
	
	//Coordinate_2
	for($q=0;$q< $step_num;$q++){$qq[]= $arr[$q][6];}
	for($p=0;$p<$step_num;$p++){$pp[]=unserialize($qq[$p]);} 
	
	$qqq =json_encode($pp);
	echo  "var qqq =$qqq ; \n";
	
	//from php to js
	$helloJson = json_encode($arr);
	echo  "var json_js =$helloJson ; \n";
	
	echo "</script>";
	//end 

?>

<script type="text/javascript">
	var step_num ='<?php echo $step_num;?>';
	var jsonStr = '';
	for(var i=0; i<=step_num-1;i++){
		var d=i+1;
		var content0=json_js[i][3];
		var content0=content0.replace(/\"/g,"&#34;");
		var content0=content0.replace(/\'/g,"&#39;");
		var content0=content0.replace(/\>/g,"&gt;");
		var content0=content0.replace(/\</g,"&lt;");
		
		var content1=json_js[i][4];
		var content1=content1.replace(/\"/g,"&#34;");
		var content1=content1.replace(/\'/g,"&#39;");
		var content1=content1.replace(/\>/g,"&gt;");
		var content1=content1.replace(/\</g,"&lt;");
		
		if(step_num==1){
				if(json_js[i][7]==1){
						jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"rightKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}}\]";
						}if(json_js[i][7]==2){
						jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"leftKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}}\]";
						}if(json_js[i][7]==3){
						jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"cmd\", \"cmd\":  {\"val\":\""+nss[i]['cmd']+"\",\"cmdX\":\""+aa[i]['x1']+"\",\"cmdY\":\""+aa[i]['y1']+"\"}}}\]";
						}
						} else {
								if(i==0){
								if(json_js[i][7]==1){
										 jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'+1步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"rightKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}},";
										}if(json_js[i][7]==2){
										 jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"leftKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}},";
										}if(json_js[i][7]==3){
										 jsonStr=jsonStr + "\[{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"cmd\", \"cmd\":  {\"val\":\""+nss[i]['cmd']+"\",\"cmdX\":\""+aa[i]['x1']+"\",\"cmdY\":\""+aa[i]['y1']+"\"}}},";
										}
								}else if( i==step_num-1){
										if(json_js[i][7]==1){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"rightKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}}]";
										}if(json_js[i][7]==2){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"leftKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}}]";
										}if(json_js[i][7]==3){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"cmd\", \"cmd\":  {\"val\":\""+nss[i]['cmd']+"\",\"cmdX\":\""+aa[i]['x1']+"\",\"cmdY\":\""+aa[i]['y1']+"\"}}}]";
										}
								}else{
										if(json_js[i][7]==1){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"rightKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}},";
										}if(json_js[i][7]==2){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"rect\", \"rect\":{\"mouseKey\":\"leftKey\",\"coords\":\""+aa[i]['x1']+","+aa[i]['y1']+","+aa[i]['x2']+","+aa[i]['y2']+"\"}}},";
										}if(json_js[i][7]==3){
										 jsonStr=jsonStr + "{\"step\":"+i+", \"title\":\"第"+d+'步：'+content0+"\",\"content\":\""+content1+"\",\"contentX\":\""+qqq[i]['xxx']+"\",\"contentY\":\""+qqq[i]['yyy']+"\", \"image\":\""+json_js[i][5]+"\",\"hotArea\":{\"type\":\"cmd\", \"cmd\":  {\"val\":\""+nss[i]['cmd']+"\",\"cmdX\":\""+aa[i]['x1']+"\",\"cmdY\":\""+aa[i]['y1']+"\"}}},";
						}
				}
		}
	}
	//debug action
	//document.write(jsonStr);
</script> 

<html>
<head>
<title>simulation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="simulation.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="simulation.min.js"></script>
<script type="text/javascript" src="simulation.js"></script>


<script type="text/javascript">
var cJson = $.parseJSON(jsonStr);
var currentStep = 0;
var stepLength = 0;

$(function() {
var stepContainer = $("#rightDiv ul");

$.each(cJson, function(i, j) {
var tmp = $('<li><a>' + j.title + '</a></li>');

/* Setting the page data for each hyperlink: */
tmp.find('a').data('image', j.image).data('content', j.content).data('step', j.step)
.data('x', j.contentX).data('y', j.contentY)
.data('hotArea', j.hotArea);

/* Adding the tab to the UL container: */
stepContainer.append(tmp);
});

var stepLi = $("#rightDiv ul li");

stepLength = stepLi.length

stepLi.click(function(e) {
$("#rightMenu").hide();
var element = $(this);

if ((element.attr("class")) == "focus")return;

$("#rightDiv ul li.focus").removeClass("focus");
element.addClass("focus");

var image = element.find('a').data('image');
var content = element.find('a').data('content');
currentStep = element.find('a').data('step');

$("#cImg").attr("src", image);
$("#msgDiv").html(content);
$("#msgDiv").css("left", element.find('a').data('x') + "px").css("top", element.find('a').data('y') + "px");

var hotArea =   element.find('a').data('hotArea');


var hotAreaType =  hotArea.type;
if(hotArea.type == "cmd")  {
clearHotArea();
 $("#inputBox").show();
$("#inputCmd").focus();
var cmd =  hotArea.cmd;
$("#inputBox").css("left", cmd.cmdX + "px").css("top", cmd.cmdY + "px")

}  else if (hotArea.type == "rect")  {
$("#inputBox").hide();
var rect =  hotArea.rect;
var rectHotArea = $("#rectHotArea");
rectHotArea.attr("coords", rect.coords);
}

e.preventDefault();
});

// default click first <li>
stepLi.first().click();

// image click event handler
$("#cImg").click(function(e) {
$("#rightMenu").hide();
e.preventDefault();
});


$("#controlBtn").click(function(e) {
$("#rightMenu").hide();
var x = $("#x1").val();
var y = $("#y1").val();
$("#msgDiv").css("left", x + "px").css("top", y + "px");
});

$("#msgDiv").dblclick(
function(e) {
nextStep(e);
}).click(function(e) {
$("#rightMenu").hide();
});

 $("#inputCmd").keyup(function(e) {
 if (e.keyCode == 13) {
 var inputCmd = $("#inputCmd").val();
 var checkCmd = $("#rightDiv ul li:eq(" + currentStep + ")").find('a').data('hotArea').cmd.val;

 if (inputCmd == checkCmd) {
  cmdInit();
  nextStep(e);
 } else {
  $("#inputCmd").css("color", "red");
  setTimeout(cmdInit, 500)
 }

 }
 });

$("#rectHotArea").dblclick(function(e)  {
var mouseKey = $("#rightDiv ul li:eq(" + currentStep + ")").find('a').data('hotArea').rect.mouseKey;
if(e.button == 0 && mouseKey =="leftKey"){
nextStep();
}
});

function clearHotArea() {
	var rectHotArea = $("#rectHotArea");
	rectHotArea.attr("coords", "0 0 0 0");
}

});

function cmdInit() {
 $("#inputCmd").val("");
 $("#inputCmd").css("color", "black")
}

function nextStep(e) {
if (stepLength == currentStep) {
	alert("已经是最后一页!");
	return;
	} else {
	currentStep = currentStep + 1;
	$("#rightDiv ul li:eq(" + currentStep + ")").click();
	}
}

var drag = new dragMing2("#msgDiv");
var drag3 = new dragMing("#rectHotArea");
new dragMing2("#rightMenu");

function show_coords(event)
{
var x=event.clientX ;
var y=event.clientY;
$("#x1").val(x);
$("#y1").val(y);
}

function rectAreaClick() {
nextStep();
$("#rectHotArea").attr("coords", "0px 0px 0px 0px");
}
</script>
</head>
<body oncontextmenu="return false">
	<div id="leftDiv">
		<img id="cImg" style="z-index: 0;" width="803px" usemap="#demo" onmousemove="show_coords(event)">
		<map id="demo" name="demo" >
		<area id="rectHotArea" shape="rect" coords="" style="border:solid 1px red;" href="#" >
		</map>
		<div id="msgDiv"></div>
		<div id="rightMenu"></div>
		<div id="inputBox"><input type="text" id="inputCmd"></div>
	</div>
	<div id="rightDiv">
		<ul></ul>
	</div>
</body>
</html>