<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
		//实例化数据模型
		$model=M('soft');
		$soft_tot=$model->count();
		$this->soft_tot=$soft_tot;
        $this->display();
    }
}