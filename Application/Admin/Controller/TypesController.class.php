<?php
namespace Admin\Controller;
use Think\Controller;
class TypesController extends Controller {
    public function index(){

		//实例化数据模型
		$model=M('classs');
        $tot=$model->count();

        //实例化分页
        $page=new \Think\Page($tot, 20);

        //获取当前页面
        $p=isset($_GET['p'])?$_GET['p']:1;
		//查询数据
		$this->data=$model->field(" * ,concat(path,id) pp")->page($p)->limit(20)->order('pp asc')->select();

        $this->page=$page->show();
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