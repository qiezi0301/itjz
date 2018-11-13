<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {

    //_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
    public function _initialize()
    {
         C(get_cfg_value()); //添加配置
    }

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

    /*文件上传方法*/
    protected function uploadZipFile($exts, $dir) {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   = 3145728 ;// 设置附件上传大小
        $upload->exts      = $exts;// 设置附件上传类型
        $upload->rootPath  = './Uploads/' . $dir . '/'; // 设置附件上传根目录
        $upload->savePath  = ''; // 设置附件上传（子）目录
        $upload->saveName = '';//保持上传名字不变

        // 上传文件
        $info = $upload->upload();

        if(!$info) {// 上传错误提示错误信息
            return json_encode(array(
                'status' => $upload->getError(),
                ));
        }else{
            // 上传成功
            $file = array_shift($info);

            $type_wj = pathinfo($file['savename'], PATHINFO_EXTENSION); //获取文件类型

            $zipFile = './Uploads/' . $dir . '/'.$file['savepath'].$file['savename'];

            return json_encode(array(
                'file'      => $zipFile,
                'url'       => $file['savepath'].$file['savename'],
                'status'    => 'SUCCESS'
                ));
        }
    }
}