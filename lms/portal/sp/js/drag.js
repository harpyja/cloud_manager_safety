function dragMing(idORclass2){
    var obj=this;
    // this.idORclass1=idORclass1;
    this.idORclass2=idORclass2;
    this.deltaX=0;
    this.deltaY=0;

    function dragStart(dragEvent) {
        // right click
        if (dragEvent.button == 2) {
            var mouseKey = $("#rightDiv ul li:eq(" + currentStep + ")").find('a').data('hotArea').rect.mouseKey;

            if(mouseKey != "rightKey")
                return false;

            $("#rightMenu").css("left",(dragEvent.clientX)+"px").css("top", (dragEvent.clientY)+"px")
                .html("<ul><li id='m1'>下一步</li><li id='m2'>1</li><li id='m3'>2</li></ul>").show()
                .mouseout(function(e) {
                    $("#rightMenu ul li.menuItemFocus").removeClass("menuItemFocus");
                });

            $("#rightMenu ul li").click(function(e) {
                $("#rightMenu").hide();
                var element = $(this);
                var selectMenu =  element.attr("id");
                if(selectMenu == 'm1') {
                    nextStep(e);
                } else if(selectMenu == 'm2') {
                    alert("menu2 click");
                }  else if(selectMenu == 'm3') {
                    alert("menu3 click");
                }
            }).mousemove(function(e) {
                    var element = $(this);
                    element.attr("class", "menuItemFocus");
                    $("#rightMenu ul li.menuItemFocus").removeClass("menuItemFocus");
                    element.addClass("menuItemFocus");
                });

        }  else {
            obj.deltaX = dragEvent.clientX - $(obj.idORclass2).offset().left;
            obj.deltaY = dragEvent.clientY - $(obj.idORclass2).offset().top;
            $(document).bind("mousemove", dragMove);
            $(document).bind("mouseup", dragStop);
        }


        dragEvent.preventDefault();
    }
    function dragMove(dragEvent){
        $(obj.idORclass2).css({
            "left":(dragEvent.clientX-obj.deltaX)+"px",
            "top" :(dragEvent.clientY-obj.deltaY)+"px"
        }) ;
        dragEvent.preventDefault();
    }
    function dragStop(){
        $(document).unbind("mousemove",dragMove);
        $(document).unbind("mouseup",dragStop);
    }
    $(document).ready(function(){
        $(obj.idORclass2).bind("mousedown",dragStart);
    })
}

function dragMing2(idORclass2) {
    var obj = this;

    this.idORclass2 = idORclass2;
    this.deltaX = 0;
    this.deltaY = 0;
    function dragStart(dragEvent) {
        obj.deltaX = dragEvent.clientX - $(obj.idORclass2).offset().left;
        obj.deltaY = dragEvent.clientY - $(obj.idORclass2).offset().top;
        $(document).bind("mousemove", dragMove);
        $(document).bind("mouseup", dragStop);
        dragEvent.preventDefault();
    }

    function dragMove(dragEvent) {
        $(obj.idORclass2).css({
            "left":(dragEvent.clientX - obj.deltaX) + "px",
            "top" :(dragEvent.clientY - obj.deltaY) + "px"
        });
        dragEvent.preventDefault();
    }

    function dragStop() {
        $(document).unbind("mousemove", dragMove);
        $(document).unbind("mouseup", dragStop);
    }

    $(document).ready(function() {
        $(obj.idORclass2).bind("mousedown", dragStart);
    })
}