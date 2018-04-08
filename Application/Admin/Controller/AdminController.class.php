<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends Controller {
    public function index(){
    	if (IS_POST) {
    		//实例化数据模型
    		$model=D('admin');
    		$model->create();
    		$model->time=time();
    		$model->statu=0;
    		$model->password=md5($_POST['password']);

    		if ($model->add()) {
    			$this->success('添加成功');
    		} else {
    			$this->error('添加失败');
    		}

    	} else {
    		//实例化数据模型
    		$model=M('admin');
    		$tot=$model->count();

    		//实例化分页
    		$page=new \Think\Page($tot, 10);
    		//获取当前页面
    		$p=isset($_GET['p'])?$_GET['p']:1;

    		$this->data=$model->order('id desc')->select();

    		//分配分页
    		$this->page=$page->show();
    		$this->display();
    	}
    }
}