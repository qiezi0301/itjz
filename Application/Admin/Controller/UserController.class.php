<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends Controller {
    public function index(){
    		//实例化数据模型
    		$model=M('User');

            //查询数据
    		$tot=$model->count();

    		//实例化分页
    		$page=new \Think\Page($tot, 10);
            $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    		//获取当前页面
    		$p=isset($_GET['p'])?$_GET['p']:1;

    		$this->data=$model->order('id desc')->page($p.',100')->select();

    		//分配分页
    		$this->page=$page->show();
            $this->tot=$tot;
    		$this->display();

    }

    //重置密码
    public function reset($id){
        $model=M('User');

        $data['id']=$id;
        $data['pass']=md5('456');

        if ($model->save($data)) {
            $this->success('重置成功');
        } else {
            $this->error('重置失败');
        }

    }

    //ajax 获取用户基本信息

    public function ajax_user_info(){
        $id=$_POST['id'];

        $this->data=M('userinfo')->where("uid=$id")->find();

        if ($this->data) {
            echo $this->fetch();
        } else {
            echo "0";
        }
    }


    public function ajax_statu(){
        //实例化数据模型
        $model=M('User');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }
}