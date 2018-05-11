$(function() {
    $(".chosen-select").chosen();
    $("#module_list").dragsort({dragSelector: ".move-btn", dragBetween: true, dragEnd: saveOrder, placeHolderTemplate: "<li></li>"});
    dragImages($(".course_imgs"));
    $("#module_list textarea").textareaAutoHeight({maxHeight: 500});

    $("#table_course").on("click", ".delete-btn", function() {
        $(this).parents("li").animate({"opacity": 0, "height": 0}, function() {
            $(this).remove();
            saveOrder();
        })
    });
    $("#table_course").on("mouseover mouseout", ".thumb", function(event) {
        if (event.type == "mouseover") {
            $(this).find(".media-rm-btn").removeClass("hide")
        } else {
            $(this).find(".media-rm-btn").addClass("hide")
        }
    });

    $("#table_course").on("click", ".media-rm-btn", function() {
        $(this).parent(".thumb").animate({"opacity": 0.25, "width": 0, "height": 0}, 300, function() {
            $(this).remove();
        })
    })
    $("#table_course").on("click", ".beautify_btn", function() {
        var obj_li = $(this).parents("li");

        var obj_textarea = obj_li.find("textarea");
        var mtype = obj_li.find(".cats_type").find("input[type=hidden]").val();

        format(obj_textarea, mtype);
    })
    $("#table_course").on("click", ".add-btn", function() {
        var li = "<li class='current'  style='display:none'>\n\
        <div class='item_top clearfix'><div class='item_ico'>1</div><div class='item_content'><textarea name='contents[]' onkeyup=checkCourseContent($(this))></textarea></div>\n\
        <div class='item_nav'><a class='move-btn bg'></a><a class='delete-btn bg'></a><a class='add-btn bg'></a><a class='beautify_btn bg'></a<a class='plupload_btn'></a>\n\
        <ul class='js-upload-image-list upload-image-list clearfix' onclick=addWebuploadCurrent($(this))>\n\
        <li class='fileinput-button js-add-image webuploader-container' data-type='loading'>\n\
        <div class='webuploader-pick'><a class='fileinput-button-icon' href='javascript:;'></a></div></li></ul>\n\
        <div  class='cats_type clearfix'><a class='chose_type'>代码类型</a>\n\
        <a class='sub' style='display: none;' data-type=0>段落</a><a class='sub' style='display: none;' data-type=4>标题</a>\n\
        <a class='sub' style='display: none;' data-type=1>html</a><a class='sub' style='display: none;'data-type=2>js</a>\n\
        <a class='sub' style='display: none;' data-type=3>css</a><a class='sub' style='display: none;' data-type=6>PHP</a><a class='sub' style='display: none;' data-type=7>原样输出</a>\n\
        <a class='sub' style='display: none;' data-type=8>编辑器</a>\n\
        <span class='arrow arrow-up'></span><input type='hidden' name='types[]' value='' autocomplete='off'/>\n\
        </div></div></div><ul class='course_imgs clearfix'></ul></li>";
        //var li = $("#ul_course_modal").html();
        $(this).parents("li").after(li);

        webupload_pic();
        dragImages($(".current").find(".course_imgs"));
        $(".current").fadeIn();
        saveOrder();
        $("#module_list textarea").textareaAutoHeight({maxHeight: 500});
    })
    var zIndexs = new Array();
    $("#table_course").on("mouseover mouseout", ".cats_type", function(event) {
        if (event.type == "mouseover") {
            $(".cats_type").each(function() {
                zIndexs.push(parseInt($(this).css("z-index")));
            })
            var max = Math.max.apply(null, zIndexs);
            $(this).css({"z-index": max + 1})
            $(this).find(".sub").css({"display": "block"});
            $(this).find(".arrow").removeClass("arrow-up").addClass("arrow-down");
        } else {
            $(this).find(".sub").hide();
            $(this).find(".arrow").addClass("arrow-up").removeClass("arrow-down");
        }
    });

    $('#table_course').on("click", ".cats_type a", function() {
        var type = $(this).text();
        $(this).parent().children("a.chose_type").text(type);
        $(this).parent().find(".sub").hide();
        $(this).parent().find("input").val($(this).attr("data-type"))
    })

    webupload_pic();
})

function saveOrder() {
    $("#module_list").children("li").each(function() {
        var index = $(this).index();
        $(this).find(".item_ico").text(index + 1)
    })
}
function changeCats(cat_id, mtype) {
    $.post(getUrl('Ajax/changeCats'), {cat_id: cat_id, mtype: mtype}, function(data) {
        var cat_sub_id = $("#cat_sub_id").attr('cat_sub_id');
        $("#cat_sub_id").html(data).val(cat_sub_id).trigger("chosen:updated");
    })
}

function addTags(id) {
    $.post(getUrl('Box/tags'), {id: id}, function(data) {

        $("#windown_box2").html(data);
        $("#box_modal").modal("show");
        var length = $("#tags_relative").children("a").length;
        var item = "";
        $("#tags_relative").children("a").each(function(k) {
            var tag = $(this).text();
            if (k == 0) {
                item = "已添加：" + item;
            }
            item += "<a>" + tag + "<span class='red-del' onclick=tag_remove($(this))></span></a>";
        })
        $("#tags_has").html(item);
        if (item) {
            $("#tag_add_tip").hide();
            $("#tags_has").show();
        } else {
            $("#tag_add_tip").show();
            $("#tags_has").hide();
        }
    })
}
function tag_remove(obj) {
    obj.parent("a").remove();
    var length = $("#tags_has").children("a").length;
    if (length == 0) {
        $("#tag_add_tip").show();
        $("#tags_has").hide();
    } else {
        $("#tag_add_tip").hide();
        $("#tags_has").show();
    }
}
function tag_add() {
    var tag = $("#input_tag").val();
    if (tag == '') {
        $("#input_tag").focus();
        return;
    }
    if (strlen(tag) > 14) {
        alert("不能超过7个中文字符");
        return;
    }
    var length = $("#tags_has").children('a').length;
    if (length > 8) {
        alert("最多添加8个标签");
        return;
    }
    var item = "<a>" + tag + "<span class='red-del' onclick=tag_remove($(this))></span></a>";
    if (length == 0) {
        item = "已添加：" + item;
    }

    $("#tags_has").append(item).show();
    $("#input_tag").val("");
    $("#tag_add_tip").remove();

}
function strlen(str) {
    var len = 0;
    for (var i = 0; i < str.length; i++) {
        var c = str.charCodeAt(i);
        //单字节加1
        if ((c >= 0x0001 && c <= 0x007e) || (0xff60 <= c && c <= 0xff9f)) {
            len++;
        }
        else {
            len += 2;
        }
    }
    return len;
}
function tags_confirm() {
    var item = "";
    var site_url = getUrl("");
    $("#tags_has").children("a").each(function() {
        var tag = $(this).text();
        item += "<a target='_blank' href='" + site_url + "/js/tag-" + tag + "'>" + tag + "</a>";
    })
    $("#tags_relative").html(item);
    $('#box_modal').modal('hide')
}

function webupload_pic() {
    var maxsize = 5000;
    $.getScript("" + getUrl() + "/Public/js/plugins/webuploader/webuploader.js", function() {
//        $(".js-add-image").data("type", "load");
if (!WebUploader.Uploader.support()) {
    alert('您的浏览器不支持上传功能！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
    $('.widget-image,.modal-backdrop').remove();
}
var uploader = WebUploader.create({
    multiple: false,
    auto: true,
    swf: "" + getUrl() + "Public/js/plugins/webuploader/Uploader.swf",
    server: getUrl("Upload/cover_img"),
    pick: {
        id: '.js-add-image',
        innerHTML: ''
    },
    accept: {
        title: 'Images',
        extensions: 'gif,jpg,jpeg,png',
        mimeTypes: 'image/*'
    },
    fileSingleSizeLimit: maxsize * 1024 * 1024,
    duplicate: true
});

uploader.on('fileQueued', function(file) {
//            $("#").addClass('loading')
});
uploader.on('uploadProgress', function(file, percentage) {

});
uploader.on('uploadBeforeSend', function(block, data) {
    data.maxsize = maxsize;
});
uploader.on('uploadSuccess', function(file, response) {

    var obj_li = $(".webupload_current").parents("li");
    obj_li.find(".course_imgs").append("<li class='thumb'><img src=" + getUrl() + response.src + " data-pic=" + response.src + "><a class='media-rm-btn hide'></a></li>")
//$("#course_imgs").dragsort({dragSelector: ".thumb", dragBetween: false, dragEnd: saveOrder, placeHolderTemplate: "<p class='thumb'></p>"});


});

uploader.on('uploadError', function(file, reason) {
    alert("上传失败！请重试。");
});
});
}
function addWebuploadCurrent(obj) {
    $(".js-upload-image-list").removeClass("webupload_current");
    obj.addClass("webupload_current");
}
function getUrl(strs) {
    strs = (strs == undefined) ? "" : strs;
    var url = $("#footer").attr("data-url") + strs;
    return url
}
function dragImages(obj) {
    obj.dragsort({dragSelector: ".thumb", dragBetween: true, dragEnd: saveOrder, placeHolderTemplate: "<li class='thumb'></li>"});
}
function checkCourseContent(obj) {
    if (obj.val() != '') {
        obj.css({"border": "1px solid #d4d4d4"})
    }
}
function checkJsForm() {
    var name = $("#name").val();
    if (name == '') {
        alert("标题不能为空！");
        $("#name").focus()
        return false;
    }
    var description = $("#description").val();
    if (description.length < 20) {
        alert("描述内容不能少于20字符！");
        $("#description").focus()
        return false;
    }

//    if ($("#cat_id").length > 0) {
//        var cat_id = $("#cat_id").val();
//        if (cat_id == '') {
//            alert("请选择分类");
//            return false;
//        }
//    }
//    if ($("#cat_sub_id").length > 0) {
//        var cat_sub_id = $("#cat_sub_id").val();
//        if (cat_sub_id == '') {
//            alert("请选择分类");
//            return false;
//        }
//    }
var id = $("#form").attr("data-id");
    if (id == undefined) {
    //        var logo_big = $("input[name=logo_big]").val();
    //        if (logo_big == '') {
    //            alert("请上传大图！");
    //            return false;
    //        }
    var zip = $("#zip").val();
    if (zip == '') {
        alert("请上传源文件！");
        return false;
    }
}
var points_type = $("input[name=points_type]:checked").val();
var points = $("#points").val();

var tags = "";
$("#tags_relative").children("a").each(function() {
    tags += $(this).text() + ",";
})
$("#tags").val(tags);
var is_content_null = false;

$("#module_list").children("li").each(function(k) {
    var pics = "";
    var obj_thumb = $(this).find(".course_imgs").children(".thumb");
    if (obj_thumb.length > 0) {
        obj_thumb.each(function() {
            pics += $(this).children("img").attr("data-pic") + ",";

        })
        if ($(this).find("textarea").val() == '') {
            $(this).find("textarea").css({"border": "1px solid red"})
            is_content_null = true;
        }
    }
    if ($(this).children("input[type='hidden']").length == 0) {
        $(this).append("<input type='hidden' name=pics[] value=" + pics + ">");
    }
})

var qq_num = $("#qq_num").val();
var qq_pattern = /^\d{5,13}$/;

if (!qq_pattern.test(qq_num)) {
    alert("请输入你正确的QQ号");
    return false;
}

if (is_content_null == true) {
    alert("教程内容不能为空");
    return false;
}
if ($("#navbar").length > 0) {
    $("#form").submit();
}


$.post(getUrl("Ajax/login_state"), {}, function(data) {
    if (data > 0) {
        $("#form").submit();
    } else {
        showWindowBox();
        return false;
    }
    })
    return false;
}

//币种选择调用方法
function checkPointsType() {
    type = $("input[name=points_type]:checked").val();
    console.log(type);
    var title = "下载积分";
    var description = "<i class='fa fa-info-circle'></i> 适当的下载积分，赚取更多积分。";
    if (type == 1) {
        var points_type = $("input[name=points_type]:checked").val();
        if (points_type == 1) {
            title = " 下载火币";
            description = "<i class='fa fa-info-circle'></i> 赚取IT币可以”申请提现“。";
        }
    }
    $("#points_title").html(title);
    $("#points_description").html(description);
}