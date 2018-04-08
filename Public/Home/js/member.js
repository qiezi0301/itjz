$(function() {
    index = -1;
    var search_box = $(".search_box");
    $("#search_input").keydown(function(event) {
        var key = event.keyCode;
        var length = search_box.children("a").length;
        if (key == 13) {
            document.form_search.submit()
        }
        if (key == 38) {
            index--;
            if (index == -1) {
                index = length - 1
            }
        } else {
            if (key == 40) {
                index++;
                if (index == length) {
                    index = 0
                }
            }
        }
        if (key == 38 || key == 40) {
            search_box.children("a").removeClass("on");
            search_box.children("a:eq(" + index + ")").addClass("on");
            var chose = $("#search_box").children(".on").children("span.red").text();
            $("#search_input").val(chose);
            var mtype = $("#search_box").children(".on").attr("data-mtype");
            $("#mtype_index").val(mtype)
        }
        $(this).css({"color": "#333"})
    });
    $("#search_input").keyup(function(event) {
        var key = event.keyCode;
        if (key == 38 || key == 40) {
            return false
        }
        var keyword = $(this).val();

        if (keyword != "" && keyword != '请输入搜索内容') {
           var li = "<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=2' data-mtype=2> 搜索含<span class='red'>" + keyword + "</span>的jQuery特效</a>\n\
   <a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=1' data-mtype=1> 搜索含<span class='red'>" + keyword + "</span>的网站模板</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=20' data-mtype=20> 搜索含<span class='red'>" + keyword + "</span>的PHP源码</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=15' data-mtype=15> 搜索含<span class='red'>" + keyword + "</span>的整站源码</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=30' data-mtype=30> 搜索含<span class='red'>" + keyword + "</span>的视频教程</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=21' data-mtype=21> 搜索含<span class='red'>" + keyword + "</span>的话题</a>";
            $("#search_box").html(li).removeClass("hide")
        } else {
            $("#search_box").html("").addClass("hide")
        }
    });
    $("#search_input").focus(function(event) {
        var key = event.keyCode;
        if (key == 38 || key == 40) {
            return false
        }
        var keyword = $(this).val();

        if (keyword != "" && keyword != '请输入搜索内容') {
            var li = "<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=2' data-mtype=2> 搜索含<span class='red'>" + keyword + "</span>的jQuery特效</a>\n\
   <a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=1' data-mtype=1> 搜索含<span class='red'>" + keyword + "</span>的网站模板</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=20' data-mtype=20> 搜索含<span class='red'>" + keyword + "</span>的PHP</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=15' data-mtype=15> 搜索含<span class='red'>" + keyword + "</span>的整站源码</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=30' data-mtype=30> 搜索含<span class='red'>" + keyword + "</span>的视频教程</a>\n\
<a href='" + getUrl('') + "search.html?keyword=" + keyword + "&mtype=21' data-mtype=21> 搜索含<span class='red'>" + keyword + "</span>的话题</a>";
            $("#search_box").html(li).removeClass("hide")
        } else {
            $("#search_box").html("").addClass("hide")
        }
    });
    $("#search_input").blur(function() {
        var is_hover = $("#search_input").attr("data-hover");
        if (is_hover != 1) {
            $("#search_box").addClass("hide")
        }
    });
    $("#search_box").hover(function() {
        $("#search_input").attr("data-hover", 1)
    }, function() {
        $("#search_input").attr("data-hover", 0)
    });
    $("#signed_edit").click(function() {

        $("#signed_textarea").show().css("border-color", "#e6e8e9").removeAttr("disabled");
        $("#signed_textarea").focus().select();
    })
    $("#signed_textarea").blur(function() {
        var content = $("#signed_textarea").val();
        var len = content.length;
        if (len > 128) {
            $("#signed_error").html("个性签名不能超过128个字符~")
            return false;
        } else {
            $("#signed_error").empty();

            if (len <= 0) {
                content = "这位童鞋很懒，什么也没有留下～～！";
            }
            $.post(getUrl("Ajax/signature"), {content: content}, function(data) {
                $("#signed_textarea").val(content.replace(/</g, "&lt;").replace(/>/g, "&gt;")).css("border-color", "transparent").attr("disabled", "disabled");
                $(".textarea_signature").val(content.replace(/</g, "&lt;").replace(/>/g, "&gt;"));
            })
        }
    })
})
function getUrl(strs) {//获取参数
    var url = $("#footer").attr("data-url") + strs;
    return url;
}
function goUrl(url) {
    if (url == -1) {
        history.go(-1);
    } else {
        document.location.href = url;
    }
}


function checkInputBlur(obj) {
    var default_words = obj.attr("data-default");
    if (obj.val() == "") {
        obj.val(default_words);
        obj.css({"color": "#a9a9a9"})
    }
}
function checkInputFocus(obj) {
    var default_words = obj.attr("data-default");
    if (obj.val() == default_words) {
        obj.val("").css({"color": "#333333"})
    }
}
function blurInputLoginBox(obj) {
    var v = obj.val();
    if (v == "") {
        obj.removeClass("form_input-focus");
        obj.prev("div").removeClass("item_tip_focus")
    } else {
        obj.addClass("form_input-focus");
        obj.prev("div").addClass("item_tip_focus")
    }
}
function checkBefore(btn) {

    var val_default = $(btn).attr("data-default");
    if (val_default == '') {
        var val = $(btn).val();
        $(btn).attr("data-default", val);
    }
    $(btn).addClass("disabled").val('loading');

}
function checkAfter(btn) {

    var val_default = $(btn).attr("data-default");
    $(btn).removeClass("disabled").val(val_default);
}
function hideMsgBox() {
    $("#msg-box").fadeOut();
}
function showSuccessTip(data) {
    $("#msg-box").show();
    $("#msg-box-content").html(data);
    setTimeout("hideMsgBox()", 2000);
}
function getCollect(obj, id, mtype) {
    $.get(getUrl("Ajax/getCollect"), {mtype: mtype, id: id}, function(data) {
        location.reload();
    })
}
function signDay(obj) {
    $.post(getUrl("Member/signDay"), {key:$("#table_sign").attr("data-key")}, function(data) {
        if(data == 'key'){
            alert('key错误，请重新签到');
            location.reload();return false;
        }
        var num = obj.find("span").text();
        var td = "<td  style='background-color:navajowhite;navajowhite ;'>\n\
<div align='right' valign='top'><span style='position:relative;right:20px;'>" + num + "</span></div>\n\
<div align='left'><img width='35px' height='35px' src='" + getUrl('Public') + "/images/other/cart_3.gif' alt='已签到' style='position:relative;left:10px;'>\n\
  已签到</div></td>";
        obj.before(td);
        obj.remove();
        if(data>0){
            showSuccessTip("签到成功获取 "+data+" 积分");
        }else{
            alert("今天您已签到！");
        }
        
    })
}
function widthdraw_btn(obj){
    if(!obj.hasClass("disabled"))
    document.location.href=getUrl("Member/withdraw_apply");
}
function exchange_btn(obj){
     alert("兑换礼品功能即将开通，敬请期待")
}