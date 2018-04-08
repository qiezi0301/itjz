<?php
namespace Admin\Controller;
use Think\Controller;
class TypesController extends Controller {
    public function index(){

		//实例化数据模型
		$model=M('classs');
		//查询数据
		$this->data=$model->field(" * ,concat(path,id) pp")->order('pp asc')->select();


        $this->display();
    }

    //添加操作
    public function insert(){
    	//实例化数据模型
    	$model=M('classs');

    	if ($model->add($_POST)) {
    		$this->success('添加成功');
    	}else {
    		$this->error('添加失败');
    	}
    }
}