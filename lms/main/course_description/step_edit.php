<?php
/*
 ==============================================================================
 教学大纲的编辑与显示
 ==============================================================================
 */
$language_file = array ('course_description' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

require_once (api_get_path(INCLUDE_PATH ).'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;
$lessonid = getgpc('cidReq','G');

$tbl_step = Database::get_main_table ( simulation );
$id =intval(getgpc('id','G'));


//总计录数
//$s_sql="SELECT COUNT( * ) FROM  $tbl_step WHERE course_id=".$lessonid;
//$step_num = Database::getval( $s_sql, __FILE__, __LINE__ );
//echo $step_num;


if($id){
    $sql="select * from $tbl_step where id = ".$id;

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$htmlHeadXtra [] = '<style>

#preview{width:802px;height:603px;border:1px solid #000;overflow:hidden;}

#imghead {filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=image);}

#popupcontent{   position: absolute;   visibility: hidden;   overflow: hidden;   border:1px solid #CCC;   background-color:#F9F9F9;   border:1px solid #333;   padding:5px;}
</style>
<script type="text/javascript">
	$(document).ready( function() {
		$("tr.containerBody:eq(5)").hide();
		$("tr.containerBody:eq(6)").hide();

		$("#left_key").click(function(){
			if($("#left_key").attr("checked")){ $("tr.containerBody:eq(5)").show();
			}else{ $("tr.containerBody:eq(5)").hide(); }
		});
		$("#right_key").click(function(){
			if($("#right_key").attr("checked")){ $("tr.containerBody:eq(6)").show();
			}else{ $("tr.containerBody:eq(6)").hide(); }
		});



	});
function check1()
{
document.getElementById("div15").style.display="block";
document.getElementById("div16").style.display="block";
//document.getElementById("div17").style.display="block";
//document.getElementById("div18").style.display="block";
//document.getElementById("div2").style.display="none";
document.getElementById("div3").style.display="none";
document.getElementById("div11").style.display="block";
document.getElementById("div12").style.display="block";
//document.getElementById("div13").style.display="block";
//document.getElementById("div14").style.display="block";

document.getElementById("div33").style.display="none";
}
function check2()
{
document.getElementById("div15").style.display="none";
document.getElementById("div16").style.display="none";
//document.getElementById("div17").style.display="none";
//document.getElementById("div18").style.display="none";
document.getElementById("div11").style.display="none";
document.getElementById("div12").style.display="none";
//document.getElementById("div13").style.display="none";
//document.getElementById("div14").style.display="none";

document.getElementById("div3").style.display="none";
document.getElementById("div11").style.display="none";

document.getElementById("div33").style.display="none";
}
function check3()
{
document.getElementById("div15").style.display="none";
document.getElementById("div16").style.display="none";
//document.getElementById("div17").style.display="none";
//document.getElementById("div18").style.display="none";
document.getElementById("div11").style.display="none";
document.getElementById("div12").style.display="none";
//document.getElementById("div13").style.display="none";
//document.getElementById("div14").style.display="none";

document.getElementById("div3").style.display="block";
document.getElementById("div11").style.display="none";

document.getElementById("div33").style.display="block";
}



function testFoo(){
            //document.getElementById( "localImag").style.display = "block";
            document.getElementById( "preview").style.display = "block";
            //document.execCommand("stop");
            //window.close();		}

        }
        function hideDiv()
        {


            document.getElementById("preview").style.display = "none";
            document.getElementById("Layer1").style.display = "none";

        }



function setImagePreview() {

        var docObj=document.getElementById("image_url");

        var imgObjPreview=document.getElementById("preview");
                if(docObj.files &&    docObj.files[0]){
                        //火狐下，直接设img属性
                        imgObjPreview.style.display = "block";
                        imgObjPreview.style.width = "600px";
                        imgObjPreview.style.height = "500px";
                        //imgObjPreview.src = docObj.files[0].getAsDataURL();

      //火狐7以上版本不能用上面的getAsDataURL()方式获取，需要一下方式
      imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);

                }else{
                        //IE下，使用滤镜
                        docObj.select();
                        var imgSrc = document.selection.createRange().text;
                        var localImagId = document.getElementById("popupcontent");
                        //必须设置初始大小
                        localImagId.style.width = "300px";
                        localImagId.style.height = "120px";
                        //图片异常的捕捉，防止用户修改后缀来伪造图片
try{
                                localImagId.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)";
                                localImagId.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
                        }catch(e){
                                alert("您上传的图片格式不正确，请重新选择!");
                                return false;
                        }
                        imgObjPreview.style.display = "none";
                        document.selection.empty();
                }


                return true;
        }

function setImage() {

        var docObj=document.getElementById("image_url");

        var imgObjPreview=document.getElementById("preview");
                if(docObj.files &&    docObj.files[0]){
                        //火狐下，直接设img属性
                        imgObjPreview.style.display = "block";
                        imgObjPreview.style.width = "600px";
                        imgObjPreview.style.height = "500px";
                        imgObjPreview.src = docObj.files[0].getAsDataURL();

      //火狐7以上版本不能用上面的getAsDataURL()方式获取，需要一下方式
      imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);

                }else{
                        //IE下，使用滤镜
                        docObj.select();
                        var imgSrc = document.selection.createRange().text;
                        var localImagId = document.getElementById("popupcontent");
                        //必须设置初始大小
                        localImagId.style.width = "300px";
                        localImagId.style.height = "120px";
                        //图片异常的捕捉，防止用户修改后缀来伪造图片
try{
                                localImagId.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)";
                                localImagId.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
                        }catch(e){
                                alert("您上传的图片格式不正确，请重新选择!");
                                return false;
                        }
                        imgObjPreview.style.display = "none";
                        document.selection.empty();
                }


                return true;
        }

//坐标









//end




function preview(file)
{
function mousePosition(ev){
     if(ev.pageX || ev.pageY){
      return {x:ev.pageX, y:ev.pageY};
      }
      return {
       x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
       y:ev.clientY + document.body.scrollTop - document.body.clientTop
       };
}

function mouseMove(ev){
    ev = ev || window.event;
    var mousePos = mousePosition(ev);
    document.getElementById("x1").value = mousePos.x;
document.getElementById("y1").value = mousePos.y;
document.getElementById("x2").value = mousePos.x+60;
document.getElementById("y2").value = mousePos.y+60;
}
document.ondblclick = mouseMove;
document.getElementById("Layer1").style.display = "block";
document.getElementById("preview").style.display = "block";
  var MAXWIDTH  = 800;
  var MAXHEIGHT = 600;
  var div = document.getElementById("preview");
  if (file.files && file.files[0])
  {
      div.innerHTML = "<img id=imghead>";
      var img = document.getElementById("imghead");
      img.onload = function(){
        var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
        img.width = rect.width;
        img.height = rect.height;
        img.style.marginLeft = rect.left+"px";
        img.style.marginTop = rect.top+"px";
      }
      var reader = new FileReader();
      reader.onload = function(evt){img.src = evt.target.result;}
      reader.readAsDataURL(file.files[0]);
  }
  else
  {
    var sFilter="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src=\"";
    file.select();
    var src = document.selection.createRange().text;
    div.innerHTML = "<img id=imghead>";
    var img = document.getElementById("imghead");
    img.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = src;
    var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
    status =("rect:"+rect.top+","+rect.left+","+rect.width+","+rect.height);
    div.innerHTML = "<div id=divhead style=\"width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;margin-left:"+rect.left+"px;"+sFilter+src+"\"\"></div>";
  }
}
function prev(file)
{


        function mousePosition(ev){
     if(ev.pageX || ev.pageY){
      return {x:ev.pageX, y:ev.pageY};
      }
      return {
       x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
       y:ev.clientY + document.body.scrollTop - document.body.clientTop
       };
}

function mouseMove(ev){
    ev = ev || window.event;
    var mousePos = mousePosition(ev);
    document.getElementById("xxx").value = mousePos.x;
document.getElementById("yyy").value = mousePos.y;
}
document.ondblclick = mouseMove;




document.getElementById("Layer1").style.display = "block";
document.getElementById("preview").style.display = "block";



  var MAXWIDTH  = 800;
  var MAXHEIGHT = 600;
  var div = document.getElementById("preview");
  if (file.files && file.files[0])
  {
      div.innerHTML = "<img id=imghead>";
      var img = document.getElementById("imghead");
      img.onload = function(){
        var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
        img.width = rect.width;
        img.height = rect.height;
        img.style.marginLeft = rect.left+"px";
        img.style.marginTop = rect.top+"px";
      }
      var reader = new FileReader();
      reader.onload = function(evt){img.src = evt.target.result;}
      reader.readAsDataURL(file.files[0]);
  }
  else
  {
    var sFilter="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src=\"";
    file.select();
    var src = document.selection.createRange().text;
    div.innerHTML = "<img id=imghead>";
    var img = document.getElementById("imghead");
    img.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = src;
    var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
    status =("rect:"+rect.top+","+rect.left+","+rect.width+","+rect.height);
    div.innerHTML = "<div id=divhead style=\"width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;margin-left:"+rect.left+"px;"+sFilter+src+"\"\"></div>";
  }
}

function previewImage(file)
{
// document.getElementById("preview").style.display = "none";
// document.getElementById("Layer1").style.display = "none";
document.getElementById("Layer1").style.display = "block";
document.getElementById( "preview").style.display = "block";
  var MAXWIDTH  = 800;
  var MAXHEIGHT = 600;
  var div = document.getElementById("preview");
  if (file.files && file.files[0])
  {
      div.innerHTML = "<img id=imghead>";
      var img = document.getElementById("imghead");
      img.onload = function(){
        var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
        img.width = rect.width;
        img.height = rect.height;
        img.style.marginLeft = rect.left+"px";
        img.style.marginTop = rect.top+"px";
      }
      var reader = new FileReader();
      reader.onload = function(evt){img.src = evt.target.result;}
      reader.readAsDataURL(file.files[0]);
  }
  else
  {
    var sFilter="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src=\"";
    file.select();
    var src = document.selection.createRange().text;
    div.innerHTML = "<img id=imghead>";
    var img = document.getElementById("imghead");
    img.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = src;
    var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
    status =("rect:"+rect.top+","+rect.left+","+rect.width+","+rect.height);
    div.innerHTML = "<div id=divhead style=\"width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;margin-left:"+rect.left+"px;"+sFilter+src+"\"\"></div>";
  }
}
function clacImgZoomParam( maxWidth, maxHeight, width, height ){
    var param = {top:0, left:0, width:width, height:height};
    if( width>maxWidth || height>maxHeight )
    {
        rateWidth = width / maxWidth;
        rateHeight = height / maxHeight;

        if( rateWidth > rateHeight )
        {
            param.width =  maxWidth;
            param.height = Math.round(height / rateWidth);
        }else
        {
            param.width = Math.round(width / rateHeight);
            param.height = maxHeight;
        }
    }

    param.left = Math.round((maxWidth - param.width) / 2);
    param.top = Math.round((maxHeight - param.height) / 2);
     document.getElementById("preview").style.display = "none";
 document.getElementById("Layer1").style.display = "none";
    return param;
}

	</script>';

$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header(null,FALSE);





$form = new FormValidator ( 'course_step', 'POST', 'step_edit.php?id='.$id.'&cidReq=' . $lessonid, '' );
$renderer = $form->defaultRenderer ();

//步骤名称
$form->addElement ( 'text', 'step', '步骤名称',array('id' => 'step','type'=>'text','maxlength' => 20));

//图片onmouseover="showPopup(300,200);"
$image [] = $form->createElement ( 'file', 'image_url', '图片',array('id' => 'image_url','onchange' =>"previewImage(this)"));
//$image [] = $form->createElement ( 'file', 'image_url', '图片',array('id' => 'image_url','onchange'=>"javascript:setImagePreview()"));
//$image [] = $form-> createElement('button','image','上传',array('id' => 'image','onclick'=>'up()'));
//$image [] = $form-> createElement('button','image','上传',array('id' => 'image','onclick'=>'showPopup(600,500)'));
$form -> addGroup ($image,'image','图片','',false);
//热区
$form->addElement ( 'text', 'title_hot_x', null,array('id' => 'xxx','type'=>'text', 'style' => 'display:none'));
$form->addElement ( 'text', 'title_hot_y', null,array('id' => 'yyy','type'=>'text','style' => 'display:none'));

//步骤详解
$form->addElement ('textarea', 'title', '步骤详解',array('id' => 'title','type'=>'text','style' => 'width:70%;height:100px') );
$title_hot [] = $form-> createElement('button','title_button','详解热区',array('id' => 'title_hot','onclick'=>'prev(this)' ));
$form -> addGroup ($title_hot,'title_hot','详解热区','',false);




//动作
$group = array ();
$group [] = $form->createElement ( 'radio', 'action', null, '右键', '1' ,array ('id' => 'right_key','onclick'=>'check1()' ));
$group [] = $form->createElement ( 'radio', 'action', null, '左键', '2' ,array ('id' => 'left_key','onclick'=>'check2()' ));
$group [] = $form->createElement ( 'radio', 'action', null, '命令行', '3',array ('id' => 'command_line','onclick'=>'check3()' ) );
$form->addGroup ( $group, 'action', '动作', '&nbsp;&nbsp;&nbsp;&nbsp;', false );

$form->addElement ( 'text', 'right1', '<div id="div11" style="display:none">1</div>',array('id' => 'div15','type'=>'text','style'=>'display: none'));
$form->addElement ( 'text', 'right2', '<div id="div12" style="display:none">2</div>',array('id' => 'div16','type'=>'text','style'=>'display: none'));
//$form->addElement ( 'text', 'right3', '<div id="div13" style="display:none">3</div>',array('id' => 'div17','type'=>'text','style'=>'display: none'));
//$form->addElement ( 'text', 'right4', '<div id="div14" style="display:none">4</div>',array('id' => 'div18','type'=>'text','style'=>'display: none'));
//$form->addElement ( 'text', 'ff', '<div id="div11" style="display:none">AAA</div>',array('id' => 'div1','type'=>'text','style'=>'display: none'));
//$form->addElement ( 'text', 'ss', '<div id="div22" style="display:none"></div>',array('id' => 'div2','type'=>'text','style'=>'display: none'));
$form->addElement ( 'text', 'cmd', '<div id="div33" style="display:none">1</div>',array('id' => 'div3','type'=>'text','style'=>'display: none'));

//热区
$hot [] = $form-> createElement('button','action_button','动作热区',array('id' => 'action_hot','onclick'=>'preview(this)' ));
$form -> addGroup ($hot,'action_hot','动作热区','',false);

$form->addElement ( 'text', 'action_hot_x1', null,array('id' => 'x1','type'=>'text','style' => 'display:none'));
$form->addElement ( 'text', 'action_hot_y1', null,array('id' => 'y1','type'=>'text','style' => 'display:none'));
$form->addElement ( 'text', 'action_hot_x2', null,array('id' => 'x2','type'=>'text','style' => 'display:none'));
$form->addElement ( 'text', 'action_hot_y2', null,array('id' => 'y2','type'=>'text','style' => 'display:none'));



$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', '确认', '' );

//$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', '确认并继续添加','class="plus"' );

$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$default['course_id']=$_SESSION['lesson_id'];
$lessonid = $default['course_id'];
$image_l = $default['image_url'];

$action_answer = unserialize($default['action_answer']);
$default['right1']=$action_answer['right1'];
$default['right2']=$action_answer['right2'];
$default['cmd']=$action_answer['cmd'];

$form->setDefaults ( $default );

if ($form->validate ()) {
//var_dump($_FILES);

    $dir = '/tmp';
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间
    $image_url = $_FILES["image_url"]["tmp_name"];
    $image_name = $_FILES["image_url"]["name"];
    move_uploaded_file($image_url,"$dir/www$image_l");
    //move_uploaded_file($image_url,"$dir/www/lms/storage/courses/$code/$image_name");

    $step = $form->getSubmitValues ();
    $step_name = $step['step'];
    $step_title = $step['title'];


    $step_image = $step['image_url'];
    $step_answer['right1'] = $step['right1'];
    $step_answer['right2'] = $step['right2'];
//    $step_answer['right3'] = $step['right3'];
//    $step_answer['right4'] = $step['right4'];
    $step_answer['cmd'] = $step['cmd'];

    $step_hot_zone['xxx'] = $step['title_hot_x']-145;
    $step_hot_zone['yyy'] = $step['title_hot_y']+5;
    $action_hot_zone['x1'] = $step['action_hot_x1']-145;
    $action_hot_zone['y1'] = $step['action_hot_y1']+5;
    $action_hot_zone['x2'] = $step['action_hot_x2']-145;
    $action_hot_zone['y2'] = $step['action_hot_y2']+5;
    $step_action = $step['action'];
    //$step_action = $step['action'];

    $sql_data = array (
        'course_id' => $_SESSION['_cid'],
        'step_id'=>$step_id,
        'step' => $step_name,
        'title' => $step_title,
        'title_hot_zone' => serialize($step_hot_zone),
        'action_hot_zone' => serialize($action_hot_zone),
        'action' => $step_action,
        'action_answer' => serialize($step_answer),
        //'image_url' => "/lms/storage/courses/$lessonid/document/images/$pname.png",
    );


    $sql = Database::sql_update ( "simulation", $sql_data ,"id='$id'");

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );


    $redirect_url = "../../main/course_description/step.php?cidReq=" . $_SESSION['_cid'];
    tb_close ( $redirect_url );


}

echo '<div id="popupcontent"  >';

//echo '<div id="Layer1" style="position:absolute; width:860px; height:815px; z-index:12; left: 50px; top: 77px; filter:Alpha(opacity=30)">';
echo '</div>';
$form->display ();


//echo '<div id="vi" style="z-index:12;left: 10px;top: 10px; "><input type=\"button\" value=\"Close window\" onClick=\"hidePopup();\"></div>';
//echo ' <img id="preview" width=10px height=10px style="display:none; z-index:11; position: absolute;left: 150px;top: 0px; " ondblclick="hideDiv()" />';
echo '<div id="Layer1" style="display:none;position:absolute; width:802px; height:603px; z-index:13; left: 150px; top: 0px; filter:Alpha(opacity=30)" ondblclick="hideDiv()" > </div>';
echo ' <div id="preview" style="display:none; z-index:11;position: absolute;left: 150px;top: 0px; " ondblclick="hideDiv()">
    <img align="" src='.$image_l.'></div>';
Display::display_footer ();

?>
