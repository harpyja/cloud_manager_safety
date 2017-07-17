;(function() {
    var nodes = [ ],
        endpoint = {
            Anchor : "Continuous",
            //Connector : [ "Bezier", { curviness: 50 } ],
            Connector : [ "Straight" ],
            Endpoint : "Blank",
            //	PaintStyle : { lineWidth : 10, strokeStyle : "#000000" },
            cssClass:"link"
        },
        netMap = [ ],
        currentNodeIndex = 0,
        sourcePort = "",targetPort = "",sourceNode = "",targetNode = "";
        var usedPorts = new HashMap();

    var endpointOptions = { isSource:true, isTarget:true },
    prepare = function(elId) {
        //	jsPlumbDemo.initHover(elId);
        return jsPlumb.addEndpoint(elId, endpoint);
    };


    /**
     *  <li> Mouse up listener
     * @param d
     */
    function addLiMouseUpListener(d){
          $(d).find("ul li").bind("mouseup", function() {
            targetNode = $(this).parent().parent().attr("id");
            targetPort = $(this).find("a").html();
            if (targetNode === sourceNode && sourcePort === targetPort) {
                $(".window ul").hide();
                return;
            }

            // Verify this node
            if (jsIOUWebNetMap.isUsedPort(targetNode + ":" + targetPort)) {
                sourceNode = "";
                sourcePort = "";
                alert("This target port is used!");
                return;
            }

            console.log("starting build connection: " + targetPort + sourcePort);

            jsPlumb.connect({
                source:sourceNode,
                target:targetNode,
                //     paintStyle:{lineWidth:3,strokeStyle:'rgb(0,0,0)'},
                overlays:[
                    [ "Label", {label:sourcePort, location:0.15, cssClass:"label"}],
                    [ "Label", {label: targetPort, location:0.85, cssClass:"label"}]
                ]
            });

            jsIOUWebNetMap.hidePopWindows();
            //jsIOUWebNetMap.resetCurrentPort();
        });
    }

    /**
     * <image> event listener
     * @param imgDom
     */
    function imageListener(imgDom){
          var currentNode =  $(imgDom).parent().attr("id");
            var ulDom =   $(imgDom).parent().find("ul");
            ulDom.show();     // show this router's popWin

            // set popWin used ports' color
             $.each(ulDom.find("li"), function(index, obj) {
                 var aDom =    $(obj).find("a");
                 var currentPort = aDom.html();

                 var nodePort = currentNode + ":" +  currentPort;

                 console.log("Port: " + nodePort + "isUsed: "+jsIOUWebNetMap.isUsedPort(nodePort));

                 if (jsIOUWebNetMap.isUsedPort(nodePort)) {
                     console.log("used port add color");
                     aDom.css("color", "green");
                 }
             });   // end each
    }

    /**
     * <li> mouse down listener
     */
    function liMouseDownListener(li) {
        sourceNode = $(li).parent().parent().attr("id");
        sourcePort = $(li).find("a").html();
        console.log(sourceNode + ":" + sourcePort);
        if (jsIOUWebNetMap.isUsedPort(sourceNode + ":" + sourcePort)) {
            sourceNode = "";
            sourcePort = "";
            alert("This source port is used!");
        }
    }

    window.jsIOUWebNetMap = {
        createRouter : function(device) {
            var id = currentNodeIndex++;
            var d = document.createElement("div");
            d.className = "window";                    //   +device.type
            var devHtml = '<img  src="images/devices/'+ device.type +'.png"  title="router"' + id + '><div class="name">'+device.type + id + '</div>';
            devHtml += '<ul style="display: none">';

            $.each(device.ports, function(portType, number) {
              //alert(portType + ': ' + number);
                for(var i = 0; i < number; i++) {
                    var portName =   portType +'0/'+ i;
                    devHtml += '<li class="eq"><a href="#" title="'+ portName +'">'+portName +'</a></li>';
                }
            });
            devHtml += '</ul>';
            $(d).html(devHtml);

            $(d).find("ul li.eq").each(function(i, e) {
                //  alert($(e).html());
                jsPlumb.makeSource($(e), {
                    parent: $(e).parent().parent(),
                    anchor:"Continuous",
                    connector:[ "Straight" ],
                    connectorStyle:{ strokeStyle:'rgb(243,230,18)', lineWidth:3 }/*,
                    maxConnections:5,
                    onMaxConnections:function(info, e) {
                        alert("Maximum connections (" + info.maxConnections + ") reached");
                    }*/
                });
            });


            $(d).find("ul li").bind("mousedown", function() {
                 liMouseDownListener(this);
            });

             addLiMouseUpListener(d);

            $(d).find("img").bind("mouseover", function() {
                if (sourcePort != "") {
                    $(this).parent().find("ul").show();
                    imageListener(this);
                }
            });

            $(d).find("img").bind("click", function() {
                jsIOUWebNetMap.hidePopWindows();
                imageListener(this);
            });

            document.getElementById("nodePanel").appendChild(d);

            id = "node" + id;
            //id = '' + ((new Date().getTime())),

            var _d = jsPlumb.CurrentLibrary.getElementObject(d);
            jsPlumb.CurrentLibrary.setAttribute(_d, "id", id);
            var w = 900, h = 600;
            var x = (0.2 * w) + Math.floor(Math.random() * (0.5 * w));
            var y = (0.2 * h) + Math.floor(Math.random() * (0.6 * h));
            d.style.top = y + 'px';
            d.style.left = x + 'px';

             return {d:d, id:id};
        },
        addRouter : function(id) {
            var info = jsIOUWebNetMap.createRouter(id);
           // var e = prepare(info.id);
            jsPlumb.draggable(info.id);
            nodes.push(info.id);
        },
        hidePopWindows : function() { $("div.window ul").hide(); },
        resetCurrentPort : function() {sourceNode = "", sourcePort = "",targetNode = "",targetPort = "";},
        getNodes : function () { return nodes; } ,
        getNetMap : function() { return netMap; },
        addNetMap : function(map) { netMap.push(map); },
        /*setSourcePort: function(port) { sourcePort = port;},
        getSourcePort : function() {return sourcePort;} ,
        setTargetPort : function(port) { targetPort = port; },
        getTargetPort : function() { return targetPort; },*/
        logUsePort : function (port) {console.log(port + " string log..."), usedPorts.put(port, ""); },
        isUsedPort : function(port) { return usedPorts.containsKey(port); }
    };
})();

 (function(win) {
    var ArrayList = function() {
        this.datas = [];
    };

    var proto = ArrayList.prototype;

    proto.size = function() {
        return this.datas.length;
    };

    proto.isEmpty = function() {
        return this.size() === 0;
    };

    proto.contains = function(value) {
        return this.datas.indexOf(value) !== -1;
    };

    proto.indexOf = function(value) {
        for ( var index in this.datas) {
            if (this.datas[index] === value) {
                return index;
            }
        }

        return -1;
    };

    proto.lastIndexOf = function(value) {
        for ( var index = this.size(); index >= 0; index--) {
            if (this.datas[index] === value) {
                return index;
            }
        }
    };

    proto.toArray = function() {
        return this.datas;
    };

    proto.outOfBound = function(index) {
        return index < 0 || index > (this.size() - 1);
    };

    proto.get = function(index) {
        if (this.outOfBound(index)) {
            return null;
        }

        return this.datas[index];
    };

    proto.set = function(index, value) {
        this.datas[index] = value;
    };

    proto.add = function(value) {
        this.datas.push(value);
    };

    proto.insert = function(index, value) {
        if (this.outOfBound(index)) {
            return;
        }

        this.datas.splice(index, 0, value);
    };

    proto.remove = function(index) {
        if (this.outOfBound(index)) {
            return false;
        }

        this.datas.splice(index, 1);
        return true;
    };

    proto.removeValue = function(value) {
        if (this.contains(value)) {
            this.remove(this.indexOf(value));
            return true;
        }
        return false;
    };

    proto.clear = function() {
        this.datas.splice(0, this.size());
    };

    proto.addAll = function(list) {
        if (!list instanceof ArrayList) {
            return false;
        }

        for ( var index in list.datas) {
            this.add(list.get(index));
        }

        return true;
    };

    proto.insertAll = function(index, list) {
        if (this.outOfBound(index)) {
            return false;
        }

        if (!list instanceof ArrayList) {
            return false;
        }

        var pos = index;
        for(var index in list.datas)
        {
            this.insert(pos++, list.get(index));
        }
        return true;
    };

    function numberorder(a, b) {
        return a - b;
    }

    proto.sort = function(isNumber){
        if(isNumber){
            this.datas.sort(numberorder);
            return;
        }

        this.datas.sort();
    };

    proto.toString = function(){
        return "[" + this.datas.join() + "]";
    };

    proto.valueOf = function(){
        return this.toString();
    };

    win.ArrayList = ArrayList;
})(window);

