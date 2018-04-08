<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {

    /*文件上传方法*/
    protected function uploadFile($exts, $dir) {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   = 3145728 ;// 设置附件上传大小
        $upload->exts      = $exts;// 设置附件上传类型
        $upload->rootPath  = './Uploads/' . $dir . '/'; // 设置附件上传根目录
        $upload->savePath  = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();

        if(!$info) {// 上传错误提示错误信息
            return json_encode(array(
                'status' => $upload->getError(),
                ));
        }else{// 上传成功
            $file = array_shift($info);

            $image=new \Think\Image();
            // //打开文件-并进行缩放
            $image->open('./Uploads/' . $dir . '/'.$file['savepath'].$file['savename'])->thumb(100,100)->save('./Uploads/' . $dir . '/'.$file['savepath'].'sm_'.$file['savename']);

            return json_encode(array(
                'url'       => $file['savepath'].$file['savename'],
                'status'    => 'SUCCESS'
                ));
        }
    }
}