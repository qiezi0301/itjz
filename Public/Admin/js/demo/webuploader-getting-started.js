/**
 * *******************************WebUpload 单文件上传begin****************************************
 */
 _extensions ='zip,rar';
 _mimeTypes ='templates/*,php/*,js/*';

jQuery(function() {
    var $ = jQuery,
        $list = $('#thelist'),
        $btn = $('#ctlBtn'),
        state = 'pending',
        uploader;

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

        accept: {
            title: 'files',
            //文字描述
            extensions: _extensions,
            //允许的文件后缀，不带点，多个用逗号分割。,jpg,png,
            mimeTypes: _mimeTypes,
            //多个用逗号分割。
        },

        // swf文件路径
        swf:  $js + 'plugins/webuploader/Uploader.swf',

        server: $path,


        // 不压缩image
        resize: false,




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
        chunkSize: 50 * 1024 * 1024,
        // 每片50M,经过测试，发现上传1G左右的视频大概每片50M速度比较快的，太大或者太小都对上传效率有影响

        chunkRetry: false,
        // 如果失败，则不重试

        threads: 1,
        // 上传并发数。允许同时最大上传进程数。
        // runtimeOrder: 'flash',
        disableGlobalDnd: true,

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#picker'

    });

    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {
        $list.append( '<div id="' + file.id + '" class="item">' +
            '<h4 class="info">' + file.name + '</h4>' +
            '<p class="state">等待上传...</p>' +
        '</div>' );

    });

    // 文件上传过程中创建进度条实时显示。
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var $li = $( '#'+file.id ),
            $percent = $li.find('.progress .progress-bar');

        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<div class="progress progress-striped active">' +
              '<div class="progress-bar" role="progressbar" style="width: 0%">' +
              '</div>' +
            '</div>').appendTo( $li ).find('.progress-bar');
        }

        $li.find('p.state').text('上传中');

        $percent.css( 'width', percentage * 100 + '%' );
    });

    uploader.on( 'uploadSuccess', function( file ) {
        $( '#'+file.id ).find('p.state').text('已上传');
    });

    uploader.on( 'uploadError', function( file ) {
        $( '#'+file.id ).find('p.state').text('上传出错');
    });

    uploader.on( 'uploadComplete', function( file ) {
        $( '#'+file.id ).find('.progress').fadeOut();
    });

    uploader.on( 'all', function( type ) {
        if ( type === 'startUpload' ) {
            state = 'uploading';
        } else if ( type === 'stopUpload' ) {
            state = 'paused';
        } else if ( type === 'uploadFinished' ) {
            state = 'done';
        }

        if ( state === 'uploading' ) {
            $btn.text('暂停上传');
        } else {
            $btn.text('开始上传');
        }
    });

    $btn.on( 'click', function() {
        if ( state === 'uploading' ) {
            uploader.stop();
        } else {
            uploader.upload();
        }
    });

        /** * 验证文件格式以及文件大小 */
    uploader.on("error",function (type,handler){
        if (type=="Q_TYPE_DENIED"){
            $.messager.alert('提示窗口','请上传MP4格式的视频 ! ');
        }
    });
});