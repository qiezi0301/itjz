/**
 * *******************************WebUpload 单文件上传begin****************************************
 */
 _extensions ='zip,rar';
 //选取文件时过滤非zip rar文件
 _mimeTypes ='application/x-zip-compressed, application/x-rar-compressed';

 $(function() {
    var $ = jQuery,
    $list = $('#thelist'),
    $btn = $('#ctlBtn'),
    uploader;

    // 实例化
    uploader = WebUploader.create({
        auto: false,
        // 是否自动上传
        pick: {
            id: '#attach',
            name: "file",
            // 这个地方 name没什么用，虽然打开调试器，input的名字确实改过来了。但是提交到后台取不到文件。如果想自定义file的name属性，还是要和fileVal  配合使用。
            label: '点击选择文件',
            multiple: false
            // 默认为true，就是可以多选
        },
        // swf文件路径
        swf:  $js + 'plugins/webuploader/Uploader.swf',

        server: $path_zip,

        //去重， 根据文件名字、文件大小和最后修改时间来生成hash Key.
        duplicate: true,

        // 是否可重复选择同一文件
        resize: false,

        //文件上传请求的参数表，每次发送都会发送此对象中的参数
        formData: {
            "status": "file",
            "contentsDto.contentsId": "0000004730",
            "uploadNum": "0000004730",
            "existFlg": 'false'
        },

        //不压缩
        compress: null,

        //是否要分片处理大文件上传
        chunked: true,

        // 分片处理
        chunkSize: 10 * 1024 * 1024,
        // 每片50M,经过测试，发现上传1G左右的视频大概每片50M速度比较快的，太大或者太小都对上传效率有影响

        chunkRetry: false,
        // 如果失败，则不重试

        threads: 1,
        // 上传并发数。允许同时最大上传进程数。
        // runtimeOrder: 'flash',
        disableGlobalDnd: true,

        accept: {
            title: 'files',
            //文字描述
            extensions: _extensions,
            //允许的文件后缀，不带点，多个用逗号分割。,jpg,png,
            mimeTypes: _mimeTypes,
            //多个用逗号分割。,
        }
    });

    // // 当有文件添加进来的时候
    // uploader.on("fileQueued", function(file) {
    //     console.log("fileQueued:" + file.name);
    //     $btn.removeClass('disabled');
    //     $list.html('<div id="' + file.id + '" class="item">' + '<h4 class="info">' + file.name + '</h4>' + '<p class="state">等待上传...</p>' + '</div>' );
    // });


        // 当有文件添加进来的时候
    uploader.onFileQueued = function( file ) {

        console.log("fileQueued:" + file.name);
        $btn.removeClass('disabled');
        $list.html('<div id="' + file.id + '" class="item">' + '<h4 class="info">' + file.name + '</h4>' + '<p class="state">等待上传...</p>' + '</div>' );
    };

    // 当某个文件上传到服务端响应后，会派送此事件来询问服务端响应是否有效。
    uploader.on("uploadAccept", function(object, ret) {
        // 服务器响应了
        var data = JSON.parse(ret._raw);
        console.log("uploadAccept:" + ret._raw);
        alert(data.status);
        if (data.status == "SUCCESS") {
            $("#fileUrl").val(data.url);
        } else {
            uploader.reset();
            alert("上传文件出现异常，请刷新后重新尝试。");
            return false;
        }
    });

    uploader.on('uploadProgress', function (file, percentage) {
        //进度条事件
        var $li = $list.find('#' + file.id), $percent = $li.find('#ProcessWD');
        // 避免重复创建
        if (!$percent.length) {
            $percent = $(' 上传进度' + '' + '' + '').appendTo($li).find('.progress - bar ');
        }
        $("#" + file.id).find("p.state").text('正在上传');
        $("#fileProcess").text(Math.round(percentage * 100) + ' % ');
        $("#ProcessWD").css('width ', percentage * 100 + ' % ');
    });

    // 当文件上传成功时触发。
    uploader.on("uploadSuccess", function(file, response) {

        var obj = $("#zip");
        obj.val(response.url);
        obj.change();

        console.log("uploadSuccess:" + response.url);
        $("#" + file.id).find("p.state").text("已上传成功");

    });



    // 当所有文件上传结束时触发
    uploader.on("uploadFinished", function() {
        $btn.addClass('disabled');
        console.log("uploadFinished:上传成功");
    });

    uploader.on("uploadError", function(file) {
        $("#" + file.id).find("p.state").attr("color","red");
        $("#" + file.id).find("p.state").text("上传出错");
        $btn.addClass('disabled');
        uploader.cancelFile(file);
        uploader.removeFile(file, true);
        uploader.reset();
    });

    /** * 验证文件格式以及文件大小 */
    uploader.on("error",function (type,handler){
        if (type=="Q_TYPE_DENIED"){
            alert('请上传zip或rar类型压缩文件');
        }
    });

    $("#ctlBtn").on("click", function() {
        if ( $(this).hasClass( 'disabled' ) ) {
            return false;
        }
        uploader.upload();

    });
});