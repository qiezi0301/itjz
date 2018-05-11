<?php
namespace Admin\Controller;
use Think\Controller;

class TemplatesController extends CommonController {
    public function index(){
        $this->display();
    }

    //商品添加页面
    public function add(){
    	$this->display();
    }

    //添加操作
    public function insert(){

        $data = $_POST;

        //将数组转换成逗号隔开的字符串
        $color_id = ''; //变量赋值为空
        foreach ($_POST["color_id"] as $key => $value) {
            $color_id .= $value.',';
        }
        //将字符串重新赋值给color_id
        $data["color_id"] = $color_id;

        //实例化数据模型
        // $model=new \Admin\Model\TemplatesModel();
        $model = D('Templates');

        if ($model->create($data)) {
            $id = $model->add();
            p($model->find($id));
            // $this->success('添加成功');
        }else {
            // 对data数据进行验证
            exit($model->getError());
        }
    }

    /**
    * 上传图片
    */
    public function upload() {
        echo parent::uploadfile(array('jpg', 'gif', 'png', 'jpeg'), 'goods');
    }
	/**
	* 上传图片
	*/
    public function uploadZip() {
        echo parent::uploadfile(array('zip', 'rar'), 'goods');
    }
}