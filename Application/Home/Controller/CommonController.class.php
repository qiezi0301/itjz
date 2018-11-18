<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\Category;

class CommonController extends Controller {
    //_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
    protected function _initialize(){
        $pid=I('get.pid', 0, 'intval');
        //取出顶级分类
        $classs=M('classs')->where("pid = 0")->select();

        if ($pid) {

            //获取当前顶级分类信息
            $self=Category::getSelf($classs,$pid);
            $this->assign('pid', $pid);
            $this->assign('self', $self);
        }

        //取出热门关键字
		$map['status'] = 1;
        $searchkey = M('search')->where($map)->order('num DESC')->limit(18)->select();

        //分配数据
        $this->assign('classs', $classs);
        $this->assign('searchkey', $searchkey);
    }

    //退出
    public function logout()    {

        $furl = $_SERVER['HTTP_REFERER'];

        if (empty($furl) || strpos($furl, 'register') || strpos($furl, 'login') || strpos($furl, 'activate') || strpos($furl, 'sendActivate')) {
            $furl = U('Login/index');

        }

        //session_unset();
        //session_destroy();
        del_cookie(array('name' => 'uid'));
        del_cookie(array('name' => 'email'));
        del_cookie(array('name' => 'username'));
        del_cookie(array('name' => 'groupid'));
        del_cookie(array('name' => 'logintime'));
        del_cookie(array('name' => 'loginip'));
        del_cookie(array('name' => 'status'));

        //$this->redirect($furl);
        //$this->success('安全退出', $furl);
        header("Location:".$furl);
    }

}