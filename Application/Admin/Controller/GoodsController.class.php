<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends CommonController {
    public function index(){
        $this->display();
    }

    //商品添加页面
    public function add(){
    	$this->display();
    }

	/**
	* 上传图片
	*/
    public function upload() {
        echo parent::uploadfile(array('jpg', 'gif', 'png', 'jpeg'), 'goods');
    }
}