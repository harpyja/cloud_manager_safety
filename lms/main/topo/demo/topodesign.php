<?php
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;

require ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');

//require_once (api_get_path ( LIBRARY_PATH ) . 'networkmap.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$id =$_GET['id'];
$networkmap = Database::get_main_table ( networkmap);

$sql = "select id,xml  FROM  $networkmap   WHERE id = '{$id}'";

$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm = Database::fetch_row ( $res);


$device = "select * from device_type ";
$result = api_sql_query($device, __FILE__, __LINE__ );
while ( $rst = Database::fetch_row ( $result) ) {
    $ste [] = $rst;
}
foreach ( $ste as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $arr[$k1][]  = $v2;
    }
}
//var_dump($arr);


//from php to js
$helloJson = json_encode($arr);
echo  "<script type=\"text/javascript\">";
echo  "var json_js =$helloJson ; \n";

echo "</script>";

?>
<html>
<head>
<title>Topology design</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css" media="screen">
    BODY {
        font-family: Arial;
    }
    H1 {
        font-size: 18px;
    }
    H2 {
        font-size: 16px;
    }
    a{color:#333;text-decoration:none; }
    a:hover {color:#CC3300;text-decoration:underline;}
</style>

<!-- Sets the basepath for the library if not in same directory -->
<script type="text/javascript">
    mxBasePath = '../src';
</script>

<!-- Loads and initializes the library -->
<script type="text/javascript" src="../src/js/mxClient.js"></script>
<!-- 设备标识  -->
<script type="text/javascript">

</script>
<!-- Example code -->
<script type="text/javascript">
// Program starts here. Creates a sample graph in the
// DOM node with the specified ID. This function is invoked
// from the onLoad event handler of the document (see below).

var DeviceFlag = '';
var InterNum = '';



function testFoo(){
    document.getElementById( "DeviceMarking").style.display = "block";
    //document.execCommand("stop");
    //window.close();		}

}
function hideDiv(a)
{
    document.getElementById(a).style.display = "none";
}

function Pause(obj,iMinSecond){
    if (window.eventList==null) window.eventList=new Array();
    var ind=-1;
    for (var i=0;i<window.eventList.length;i++){
        if (window.eventList[i]==null) {
            window.eventList[i]=obj;
            ind=i;
            break;
        }
    }

    if (ind==-1){
        ind=window.eventList.length;
        window.eventList[ind]=obj;
    }
    //var view = document.getElementById("view").submit()

    //window.timer = setTimeout("GoOn(" + ind + ")",4000);
    timer = setInterval("GoOn(" + ind + ")",2000);
    //  clearInterval(timer);
//GoOn(ind);




    //foo(GoOn());
    // onclick="foo()"
    //GoOn(ind);
}

function GoOn(ind){
    var obj=window.eventList[ind];
    window.eventList[ind]=null;
    if (obj.NextStep) obj.NextStep();
    else obj();
}

function foo(){
    DeviceFlag = document.getElementById( "Flag").value;
    InterNum = document.getElementById( "InterNum").value;
    names = DeviceFlag;
    networkCounts = parseInt(InterNum);
    // alert('aaaaa');
    hideDiv("DeviceMarking");
    hideDiv("bgDiv");

    // clearTimeout(timer);
//aa();
//setTimeout(dd,1000);
    //  addSidebarIcon.dd();

    // cc();

    //setInterval("GoOn(" + ind + ")",1000);

}



function addToolbarButton(editor, toolbar, action, label, image, isTransparent)
{
    var button = document.createElement('button');
    button.style.fontSize = '10';
    if (image != null)
    {
        var img = document.createElement('img');
        img.setAttribute('src', image);
        img.style.width = '16px';
        img.style.height = '16px';
        img.style.verticalAlign = 'middle';
        img.style.marginRight = '2px';
        button.appendChild(img);
    }
    if (isTransparent)
    {
        button.style.background = 'transparent';
        button.style.color = '#FFFFFF';
        button.style.border = 'none';
    }
    mxEvent.addListener(button, 'click', function(evt)
    {
        editor.execute(action);
    });
    mxUtils.write(button, label);
    toolbar.appendChild(button);
};

function showModalWindow(graph, title, content, width, height)
{
    var background = document.createElement('div');
    background.style.position = 'absolute';
    background.style.left = '0px';
    background.style.top = '0px';
    background.style.right = '0px';
    background.style.bottom = '0px';
    background.style.background = 'black';
    mxUtils.setOpacity(background, 50);
    document.body.appendChild(background);

    if (mxClient.IS_IE)
    {
        new mxDivResizer(background);
    }

    var x = Math.max(0, document.body.scrollWidth/2-width/2);
    var y = Math.max(10, (document.body.scrollHeight ||
        document.documentElement.scrollHeight)/2-height*2/3);
    var wnd = new mxWindow(title, content, x, y, width, height, false, true);
    wnd.setClosable(true);

    // Fades the background out after after the window has been closed
    wnd.addListener(mxEvent.DESTROY, function(evt)
    {
        graph.setEnabled(true);
        mxEffects.fadeOut(background, 50, true,
            10, 30, true);
    });

    graph.setEnabled(false);
    graph.tooltipHandler.hide();
    wnd.setVisible(true);
};
//alert(initname);




function addSidebarIcon(graph, sidebar, label, initName, count, image)
{

    // Function that is executed when the image is dropped on
    // the graph. The cell argument points to the cell under
    // the mousepointer if there is one.

    var funct = function(graph, evt, cell, x, y)
    {



        var results = window.showModalDialog("viewdesign.php?initName="+encodeURI(initName),initName,"dialogWidth=300px;dialogHeight=200px;status=yes;help=no;scroll=no;location=no;");

        if(results != undefined){

//alert (results);
            name = results[0];
            count = count + 1;
            name  = name+'_'+count;
            networkCount = parseInt(results[1]);
            off_on = results[2];


        }
        if(off_on=='1'){
            la ='<br>'+
                '<a href=/lms/main/cloud/cloudvmstart.php?system='+name+'&nicnum='+networkCount+'  target=_blank>'+'打开控制台'+'</a><br>';

        }else{
            la = '';
        }
              var parent = graph.getDefaultParent();
        var model = graph.getModel();

        var v1 = null;

        model.beginUpdate();

        try
        {
            // NOTE: For non-HTML labels the image must be displayed via the style
            // rather than the label markup, so use 'image=' + image for the style.
            // as follows: v1 = graph.insertVertex(parent, null, label,
            // pt.x, pt.y, 120, 120, 'image=' + image);
                  
            //var name = names+count;
            //var networkCount = networkCounts;
            //        var name = mxUtils.prompt('请输入设备标识', initName + count);
            //        var networkCount = mxUtils.prompt('请输入设备接口数', '1');
            //       networkCount = parseInt(networkCount);
            

            var label2 = '<h1 style="margin:0px;">'+ name  +'</h1>'+la + label;
            v1 = graph.insertVertex(parent, null, label2, x, y, 120, 120);
            v1.setConnectable(false);

            // Presets the collapsed size
            v1.geometry.alternateBounds = new mxRectangle(0, 0, 120, 40);




            // Adds the ports at various relative locations

            if(networkCount >= 1)
            {
                var port = graph.insertVertex(v1, null, '接口0', 0, 0.25, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth0.png;align=right;imageAlign=right;spacingRight=18', true);
                port.geometry.offset = new mxPoint(-6, -8);
            }

            if(networkCount >= 2)
            {
                var port = graph.insertVertex(v1, null, '接口1', 0, 0.4, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth1.png;align=right;imageAlign=right;spacingRight=18', true);
                port.geometry.offset = new mxPoint(-6, -6);
            }

            if(networkCount >= 3)
            {
                var port = graph.insertVertex(v1, null, '接口2', 0, 0.55, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth2.png;align=right;imageAlign=right;spacingRight=18', true);
                port.geometry.offset = new mxPoint(-6, -5);
            }


            if(networkCount >= 4) {
                var port = graph.insertVertex(v1, null, '接口3', 0, 0.75, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth3.png;align=right;imageAlign=right;spacingRight=18', true);
                port.geometry.offset = new mxPoint(-6, -4);
            }

            if(networkCount >= 5) {
                var port = graph.insertVertex(v1, null, '接口4', 1, 0.25, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth4.png;spacingLeft=18', true);
                port.geometry.offset = new mxPoint(-8, -8);
            }

            if(networkCount >= 6) {
                var port = graph.insertVertex(v1, null, '接口5', 1, 0.4, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth5.png;spacingLeft=18', true);
                port.geometry.offset = new mxPoint(-8, -6);
            }

            if(networkCount >= 7) {
                var port = graph.insertVertex(v1, null, '接口6', 1, 0.55, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth6.png;spacingLeft=18', true);
                port.geometry.offset = new mxPoint(-8, -5);
            }

            if(networkCount >= 8) {
                var port = graph.insertVertex(v1, null, '接口7', 1, 0.75, 32, 22,
                    'port;image=/lms/main/topo/demo/editors/images/overlays/eth7.png;spacingLeft=18', true);
                port.geometry.offset = new mxPoint(-8, -4);
            }
        }
        finally
        {
            model.endUpdate();
        }

        graph.setSelectionCell(v1);

        //}

//        function Test(){
//            var timer = setTimeout(dd,1200);
//            // clearTimeout(timer);
//
//        }
//                function Test(){
//                    //alert("hellow");
//                    Pause(this,1000);//调用暂停函数
//
//                    this.NextStep=function(){
//                        aa();
//                    }
//                }
        //Test();

    }

    // Creates the image which is used as the sidebar icon (drag source)
    var img = document.createElement('img');
    img.setAttribute('src', image);
    img.style.width = '48px';
    img.style.height = '48px';
    img.title = '拖动图标创建实例';
    sidebar.appendChild(img);

    var dragElt = document.createElement('div');
    dragElt.style.border = 'dashed black 1px';
    dragElt.style.width = '120px';
    dragElt.style.height = '120px';

    // Creates the image which is used as the drag icon (preview)
    var ds = mxUtils.makeDraggable(img, graph, funct, dragElt, 0, 0, true, true);
    ds.setGuidesEnabled(true);
};

function main(container, outline, toolbar, sidebar, status)
{
    // Checks if the browser is supported
    if (!mxClient.isBrowserSupported())
    {
        // Displays an error message if the browser is not supported.
        mxUtils.error('Browser is not supported!', 200, false);
    }
    else
    {
        // Assigns some global constants for general behaviour, eg. minimum
        // size (in pixels) of the active region for triggering creation of
        // new connections, the portion (100%) of the cell area to be used
        // for triggering new connections, as well as some fading options for
        // windows and the rubberband selection.
        mxConstants.MIN_HOTSPOT_SIZE = 16;
        mxConstants.DEFAULT_HOTSPOT = 1;

        // Enables guides
        mxGraphHandler.prototype.guidesEnabled = true;

        // Alt disables guides
        mxGuide.prototype.isEnabledForEvent = function(evt)
        {
            return !mxEvent.isAltDown(evt);
        };

        // Enables snapping waypoints to terminals
        mxEdgeHandler.prototype.snapToTerminals = true;

        // Workaround for Internet Explorer ignoring certain CSS directives
        if (mxClient.IS_IE)
        {
            new mxDivResizer(container);
            //new mxDivResizer(outline);
            new mxDivResizer(toolbar);
            new mxDivResizer(sidebar);
            new mxDivResizer(status);
        }

        // Creates a wrapper editor with a graph inside the given container.
        // The editor is used to create certain functionality for the
        // graph, such as the rubberband selection, but most parts
        // of the UI are custom in this example.
        var editor = new mxEditor();
        var graph = editor.graph;
        var model = graph.getModel();

        // Disable highlight of cells when dragging from toolbar
        graph.setDropEnabled(false);

        // Uses the port icon while connections are previewed
        graph.connectionHandler.getConnectImage = function(state)
        {
            return new mxImage(state.style[mxConstants.STYLE_IMAGE], 32, 22);
        };

        // Centers the port icon on the target port
        graph.connectionHandler.targetConnectImage = true;

        // Does not allow dangling edges
        graph.setAllowDanglingEdges(false);

        // Sets the graph container and configures the editor
        editor.setGraphContainer(container);
        var config = mxUtils.load(
            'editors/config/keyhandler-commons.xml').
            getDocumentElement();
        editor.configure(config);

        // Defines the default group to be used for grouping. The
        // default group is a field in the mxEditor instance that
        // is supposed to be a cell which is cloned for new cells.
        // The groupBorderSize is used to define the spacing between
        // the children of a group and the group bounds.
        var group = new mxCell('Group', new mxGeometry(), 'group');
        group.setVertex(true);
        group.setConnectable(false);
        editor.defaultGroup = group;
        editor.groupBorderSize = 20;

        // Disables drag-and-drop into non-swimlanes.
        graph.isValidDropTarget = function(cell, cells, evt)
        {
            return this.isSwimlane(cell);
        };

        // Disables drilling into non-swimlanes.
        graph.isValidRoot = function(cell)
        {
            return this.isValidDropTarget(cell);
        }

        // Does not allow selection of locked cells
        graph.isCellSelectable = function(cell)
        {
            return !this.isCellLocked(cell);
        };

        // Returns a shorter label if the cell is collapsed and no
        // label for expanded groups
        graph.getLabel = function(cell)
        {
            var tmp = mxGraph.prototype.getLabel.apply(this, arguments); // "supercall"

            if (this.isCellLocked(cell))
            {
                // Returns an empty label but makes sure an HTML
                // element is created for the label (for event
                // processing wrt the parent label)
                return '';
            }
            else if (this.isCellCollapsed(cell))
            {
                var index = tmp.indexOf('</h1>');

                if (index > 0)
                {
                    tmp = tmp.substring(0, index+5);
                }
            }

            return tmp;
        }

        // Disables HTML labels for swimlanes to avoid conflict
        // for the event processing on the child cells. HTML
        // labels consume events before underlying cells get the
        // chance to process those events.
        //
        // NOTE: Use of HTML labels is only recommended if the specific
        // features of such labels are required, such as special label
        // styles or interactive form fields. Otherwise non-HTML labels
        // should be used by not overidding the following function.
        // See also: configureStylesheet.
        graph.isHtmlLabel = function(cell)
        {
            return !this.isSwimlane(cell);
        }

        // To disable the folding icon, use the following code:
        /*graph.isCellFoldable = function(cell)
        {
            return false;
        }*/

        // Shows a "modal" window when double clicking a vertex.
        graph.dblClick = function(evt, cell)
        {
            // Do not fire a DOUBLE_CLICK event here as mxEditor will
            // consume the event and start the in-place editor.
            if (this.isEnabled() &&
                !mxEvent.isConsumed(evt) &&
                cell != null &&
                this.isCellEditable(cell))
            {
                if (this.model.isEdge(cell) ||
                    !this.isHtmlLabel(cell))
                {
                    this.startEditingAtCell(cell);
                }
                else
                {
                    var content = document.createElement('div');
                    content.innerHTML = this.convertValueToString(cell);
                    showModalWindow(this, 'Properties', content, 400, 300);
                }
            }

            // Disables any default behaviour for the double click
            mxEvent.consume(evt);
        };

        // Enables new connections
        graph.setConnectable(true);

        // Adds all required styles to the graph (see below)
        configureStylesheet(graph);

        // Adds sidebar icons.
        //
        // NOTE: For non-HTML labels a simple string as the third argument
        // and the alternative style as shown in configureStylesheet should
        // be used. For example, the first call to addSidebar icon would
        // be as follows:
        // addSidebarIcon(graph, sidebar, 'Website', 'images/icons48/earth.png');


        //var c1 = 0, c2 = 0, c3 = 0, c4 = 0, c5 = 0,c6= 0;
//var ffs ='/lms/main/topo/demo/images/icons48/route.png';
        for(var i= 0; i<json_js.length; i++,x++){
            var x = 0;
            addSidebarIcon(graph, sidebar,
                // '<h1 style="margin:0px;">防火墙</h1><br>'+
                '<img src='+json_js[i][2]+' width="48" height="48">'+
                    '<br>'+'', json_js[i][1], x, json_js[i][2]
            );


        }
 
        // Displays useful hints in a small semi-transparent box.
        var hints = document.createElement('div');
        hints.style.position = 'absolute';
        hints.style.overflow = 'hidden';
        hints.style.width = '230px';
        hints.style.bottom = '56px';
        hints.style.height = '90px';
        hints.style.right = '20px';

        hints.style.background = 'black';
        hints.style.color = 'white';
        hints.style.fontFamily = 'Arial';
        hints.style.fontSize = '10px';
        hints.style.padding = '4px';

        mxUtils.setOpacity(hints, 50);

        mxUtils.writeln(hints, '- 从侧边栏拖动一个图形图像;');
        mxUtils.writeln(hints, '- 双击上的顶点或边编辑');
        mxUtils.writeln(hints, '- 移位或右击和拖拽平移');
        mxUtils.writeln(hints, '- 移动鼠标放在图片上，看到一个提示；');
        mxUtils.writeln(hints, '- 点击连接点，并进行拖动连接点，进行绘画连线；');


        document.body.appendChild(hints);

        // Creates a new DIV that is used as a toolbar and adds
        // toolbar buttons.
        var spacer = document.createElement('div');
        spacer.style.display = 'inline';
        spacer.style.padding = '8px';

        addToolbarButton(editor, toolbar, 'groupOrUngroup', '建立区域', 'images/group.png');

        // Defines a new action for deleting or ungrouping
        editor.addAction('groupOrUngroup', function(editor, cell)
        {
            cell = cell || editor.graph.getSelectionCell();
            if (cell != null && editor.graph.isSwimlane(cell))
            {
                editor.execute('ungroup', cell);
            }
            else
            {
                editor.execute('group');
            }
        });

        addToolbarButton(editor, toolbar, 'delete', '删除', 'images/delete2.png');

        toolbar.appendChild(spacer.cloneNode(true));

        addToolbarButton(editor, toolbar, 'cut', '剪切', 'images/cut.png');
        addToolbarButton(editor, toolbar, 'copy', '复制', 'images/copy.png');
        addToolbarButton(editor, toolbar, 'paste', '粘贴', 'images/paste.png');

        toolbar.appendChild(spacer.cloneNode(true));

        addToolbarButton(editor, toolbar, 'undo', '', 'images/undo.png');
        addToolbarButton(editor, toolbar, 'redo', '', 'images/redo.png');

        toolbar.appendChild(spacer.cloneNode(true));


        toolbar.appendChild(spacer.cloneNode(true));

        // Defines a new export action
        editor.addAction('export', function(editor, cell)
        {
            //var textarea = document.createElement('textarea');
            //textarea.style.width = '400px';
            //textarea.style.height = '400px';
            //var enc = new mxCodec(mxUtils.createXmlDocument());
            //var node = enc.encode(editor.graph.getModel());
            //textarea.value = mxUtils.getPrettyXml(node);
            //showModalWindow(graph, 'XML', textarea, 410, 440);

            var enc = new mxCodec(mxUtils.createXmlDocument());
            var node = enc.encode(editor.graph.getModel());
            window.strXml =  mxUtils.getPrettyXml(node)


            strXml =strXml.replace(/&/g, '%26');


            sendRequest();
            alert("sucess! ");
            //	showModalWindow(graph, 'XML', textarea, 410, 440);
        });


        editor.addAction('export2', function(editor, cell)
        {
            //var textarea = document.createElement('textarea');
            //textarea.style.width = '400px';
            //textarea.style.height = '400px';
            //var enc = new mxCodec(mxUtils.createXmlDocument());
            //var node = enc.encode(editor.graph.getModel());
            //textarea.value = mxUtils.getPrettyXml(node);
            //showModalWindow(graph, 'XML', textarea, 410, 440);

            var enc = new mxCodec(mxUtils.createXmlDocument());
            var node = enc.encode(editor.graph.getModel());
            window.strXml =  mxUtils.getPrettyXml(node)


            strXml =strXml.replace(/&/g, '%26');

            window.netname = prompt("请输入");
            sendRequest();
            /** 集成 php **/
            //alert("另存为: " + strXml);


            /** 集成 php **/
            //	showModalWindow(graph, 'XML', textarea, 410, 440);
        });

        var request;
        function sendRequest()
        {
            if(window.ActiveXObject)//IE
            {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            else//FF
            {
                request = new XMLHttpRequest();
            }
            request.onreadystatechange = process;
        <?php
        if($id){
            echo "request.open('post','check.php?id={$id}',true);";

        }else{
            echo 'request.open("post","check.php",true);';
        }

        ?>

            request.setRequestHeader("content-type","application/x-www-form-urlencoded");
            request.send("code="+ strXml);
        }
        function process()
        {
            if(request.readyState == 4)
            {
                var msg = request.responseText;
                document.getElementById("mySpan").innerHTML = msg;
            }
        }

        addToolbarButton(editor, toolbar, 'export', '保存', 'images/export1.png');
        //addToolbarButton(editor, toolbar, 'export2', '另存为', 'images/export1.png');

        // ---

        // Adds toolbar buttons into the status bar at the bottom
        // of the window.
        addToolbarButton(editor, status, 'collapseAll', 'Collapse All', 'images/navigate_minus.png', true);
        addToolbarButton(editor, status, 'expandAll', 'Expand All', 'images/navigate_plus.png', true);

        status.appendChild(spacer.cloneNode(true));

        // Creates the outline (navigator, overview) for moving
        // around the graph in the top, right corner of the window.
        //var outln = new mxOutline(graph, outline);

        // To show the images in the outline, uncomment the following code
        //outln.outline.labelsVisible = true;
        //outln.outline.setHtmlLabels(true);

        // Fades-out the splash screen after the UI has been loaded.
        var splash = document.getElementById('splash');
        if (splash != null)
        {
            try
            {
                mxEvent.release(splash);
                mxEffects.fadeOut(splash, 100, true);
            }
            catch (e)
            {
                // mxUtils is not available (library not loaded)
                splash.parentNode.removeChild(splash);
            }
        }

    <?php

//过滤特殊符号；
    $vm = str_replace (array('<','>'),array('"<','>"+'),$vm['1']);

    echo "var initXml =".$vm.'"";';

    ?>
        viewStringXML(initXml);
    }
    function viewStringXML(xml) {
        var xmlDocument = mxUtils.parseXml(xml);
        var decoder = new mxCodec(xmlDocument);
        var node = xmlDocument.documentElement;
        decoder.decode(node, graph.getModel());
    }
};

function configureStylesheet(graph)
{
    var style = new Object();
    style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_RECTANGLE;
    style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RectanglePerimeter;
    style[mxConstants.STYLE_ALIGN] = mxConstants.ALIGN_CENTER;
    style[mxConstants.STYLE_VERTICAL_ALIGN] = mxConstants.ALIGN_MIDDLE;
    style[mxConstants.STYLE_GRADIENTCOLOR] = '#41B9F5';
    style[mxConstants.STYLE_FILLCOLOR] = '#8CCDF5';
    style[mxConstants.STYLE_STROKECOLOR] = '#1B78C8';
    style[mxConstants.STYLE_FONTCOLOR] = '#000000';
    style[mxConstants.STYLE_ROUNDED] = true;
    style[mxConstants.STYLE_OPACITY] = '80';
    style[mxConstants.STYLE_FONTSIZE] = '12';
    style[mxConstants.STYLE_FONTSTYLE] = 0;
    style[mxConstants.STYLE_IMAGE_WIDTH] = '48';
    style[mxConstants.STYLE_IMAGE_HEIGHT] = '48';
    graph.getStylesheet().putDefaultVertexStyle(style);

    // NOTE: Alternative vertex style for non-HTML labels should be as
    // follows. This repaces the above style for HTML labels.
    /*var style = new Object();
             style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_LABEL;
             style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RectanglePerimeter;
             style[mxConstants.STYLE_VERTICAL_ALIGN] = mxConstants.ALIGN_TOP;
             style[mxConstants.STYLE_ALIGN] = mxConstants.ALIGN_CENTER;
             style[mxConstants.STYLE_IMAGE_ALIGN] = mxConstants.ALIGN_CENTER;
             style[mxConstants.STYLE_IMAGE_VERTICAL_ALIGN] = mxConstants.ALIGN_TOP;
             style[mxConstants.STYLE_SPACING_TOP] = '56';
             style[mxConstants.STYLE_GRADIENTCOLOR] = '#7d85df';
             style[mxConstants.STYLE_STROKECOLOR] = '#5d65df';
             style[mxConstants.STYLE_FILLCOLOR] = '#adc5ff';
             style[mxConstants.STYLE_FONTCOLOR] = '#1d258f';
             style[mxConstants.STYLE_FONTFAMILY] = 'Verdana';
             style[mxConstants.STYLE_FONTSIZE] = '12';
             style[mxConstants.STYLE_FONTSTYLE] = '1';
             style[mxConstants.STYLE_ROUNDED] = '1';
             style[mxConstants.STYLE_IMAGE_WIDTH] = '48';
             style[mxConstants.STYLE_IMAGE_HEIGHT] = '48';
             style[mxConstants.STYLE_OPACITY] = '80';
             graph.getStylesheet().putDefaultVertexStyle(style);*/

    style = new Object();
    style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_SWIMLANE;
    style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RectanglePerimeter;
    style[mxConstants.STYLE_ALIGN] = mxConstants.ALIGN_CENTER;
    style[mxConstants.STYLE_VERTICAL_ALIGN] = mxConstants.ALIGN_TOP;
    style[mxConstants.STYLE_FILLCOLOR] = '#FF9103';
    style[mxConstants.STYLE_GRADIENTCOLOR] = '#F8C48B';
    style[mxConstants.STYLE_STROKECOLOR] = '#E86A00';
    style[mxConstants.STYLE_FONTCOLOR] = '#000000';
    style[mxConstants.STYLE_ROUNDED] = true;
    style[mxConstants.STYLE_OPACITY] = '80';
    style[mxConstants.STYLE_STARTSIZE] = '30';
    style[mxConstants.STYLE_FONTSIZE] = '16';
    style[mxConstants.STYLE_FONTSTYLE] = 1;
    graph.getStylesheet().putCellStyle('group', style);

    style = new Object();
    style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_IMAGE;
    style[mxConstants.STYLE_FONTCOLOR] = '#774400';
    style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RectanglePerimeter;
    style[mxConstants.STYLE_PERIMETER_SPACING] = '6';
    style[mxConstants.STYLE_ALIGN] = mxConstants.ALIGN_LEFT;
    style[mxConstants.STYLE_VERTICAL_ALIGN] = mxConstants.ALIGN_MIDDLE;
    style[mxConstants.STYLE_FONTSIZE] = '10';
    style[mxConstants.STYLE_FONTSTYLE] = 2;
    style[mxConstants.STYLE_IMAGE_WIDTH] = '16';
    style[mxConstants.STYLE_IMAGE_HEIGHT] = '16';
    graph.getStylesheet().putCellStyle('port', style);

    style = graph.getStylesheet().getDefaultEdgeStyle();
    style[mxConstants.STYLE_LABEL_BACKGROUNDCOLOR] = '#FFFFFF';
    style[mxConstants.STYLE_STROKEWIDTH] = '2';
    style[mxConstants.STYLE_ROUNDED] = true;
    style[mxConstants.STYLE_EDGE] = mxEdgeStyle.EntityRelation;
};
</script>
</head>

<!-- Page passes the container for the graph to the grogram -->
<body onload="main(document.getElementById('graphContainer'),
			document.getElementById('outlineContainer'),
		 	document.getElementById('toolbarContainer'),
			document.getElementById('sidebarContainer'),
			document.getElementById('statusContainer'));">

<!-- Creates a container for the splash screen -->
<div id="splash"
     style="position:absolute;top:0px;left:0px;width:100%;height:100%;background:white;z-index:1;">
    <center id="splash" style="padding-top:230px;">
        <img src="editors/images/loading.gif">
    </center>
</div>

<!-- Creates a container for the sidebar -->
<div id="toolbarContainer"
     style="position:absolute;white-space:nowrap;overflow:hidden;top:0px;left:0px;max-height:24px;height:36px;right:0px;padding:6px;background-image:url('images/toolbar_bg.gif');">
</div>

<!-- Creates a container for the toolboox -->
<div id="sidebarContainer"
     style="position:absolute;overflow:hidden;top:36px;left:0px;bottom:36px;max-width:52px;width:56px;padding-top:10px;padding-left:4px;background-image:url('images/sidebar_bg.gif');">
</div>

<!-- Creates a container for the graph -->
<div id="graphContainer"
     style="position:absolute;overflow:hidden;top:36px;left:60px;bottom:36px;right:0px;background-image:url('editors/images/grid.gif');cursor:default;">
</div>

<!-- Creates a container for the outline -->
<!--
    <div id="outlineContainer"
        style="position:absolute;overflow:hidden;top:36px;right:0px;width:200px;height:140px;background:transparent;border-style:solid;border-color:black;">
    </div>
    -->

<!-- Creates a container for the sidebar -->
<div id="statusContainer"
     style="text-align:right;position:absolute;overflow:hidden;bottom:0px;left:0px;max-height:24px;height:36px;right:0px;color:white;padding:6px;background-image:url('images/toolbar_bg.gif');">
</div>
<!-- 设备标识及接口数 -->
<div id="DeviceMarking" style="z-index:9999;display:none;padding:20px 40px;fontSize:12px;border:1px solid blue; background-color:#fff;width:300px; height:180px;position:absolute;overflow:hidden;bottom:300px;left:500px; ">

    <label>请选择设备标识：</label>
    <form name="view">


        <select name='Flag' id='Flag'>
            <option value='标识一'>标识一</option>
            <option value='标识二'>标识二</option>
            <option value='标识三'>标识三</option>
            <option value='标识四'>标识四</option>
        </select><br/><br/>
        <label>请选择设备接口数：</label>
        <select name='InterNum' id='InterNum'>
            <option value='1'>1</option>
            <option value='2'>2</option>
            <option value='3'>3</option>
            <option value='4'>4</option>
            <option value='4'>5</option>
        </select><br/><br/><br/>
        <input type='button' name='sub' value='确定'  onclick="foo()"/>
        <input type='button' name='sub' value='取消'  onclick="hideDiv('DeviceMarking')" />
    </form>
</div>
</body>

<script type="text/javascript">
    function sAlert(){
        var sWidth,sHeight;
        sWidth=document.body.offsetWidth; //屏幕宽
        sHeight=screen.height; //屏幕高
        var bgObj=document.createElement("div"); //创建div
        bgObj.setAttribute('id','bgDiv'); //设置div的id为bgDiv
        bgObj.style.position="absolute";
        bgObj.style.top="0";
        bgObj.style.background="gray"; //笼罩层div背景色
        bgObj.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75";
        bgObj.style.opacity="0.6"; //设置为半透明
        bgObj.style.left="0";
        bgObj.style.width=sWidth + "px"; //设置div大小
        bgObj.style.height=sHeight + "px";
        bgObj.style.zIndex = "9998";
        bgObj.style.display = "block";
        document.body.appendChild(bgObj); //添加div

    }

</script>
</html>
