<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends Controller {
    public function index(){

		//实例化数据模型
		$model=M('admin');
		$tot=$model->count();

		//实例化分页
		$page=new \Think\Page($tot, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

		//获取当前页面
		$p=isset($_GET['p'])?$_GET['p']:1;
		$this->data=$model->order('id desc')->page($p)->limit(20)->select();

		//分配数据
        $this->tot=$tot;
		$this->page=$page->show();
		$this->display();

    }

    //ajax添加数据
    public function ajax_add(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        //实例化数据模型
        $model=D('admin');

        //插入数据
        if (!$model->create()) {
            exit($User->getError());
            // echo "0";
        }

        $passwordinfo = get_password($arr['password']);

        $userData     = array(
            'username'  => trim($arr['username']),
            'password'  => $passwordinfo['password'],
            'encrypt'   => $passwordinfo['encrypt'],
            'realname'  => trim($arr['realname']),
            'logintime' => time(),
            'created_at' => time(),
            'loginip'   => get_client_ip(),
            'statu'    => $arr['statu'],
        );

        if ($id = $model->add($userData)) {
            $this->data=$model->find($id);
            echo $this->fetch();
        }else{
            echo "0";
        }
        

    }

    //ajax删除单条数据
    public function ajax_del(){
        //接收ID
        $id=$_POST['id'];

        //实例化数据模型
        $model=M('admin');

        if ($model->delete($id)) {
            # code...
            echo "1";
        } else {
            echo "2";
        }
        
    }

    //批量删除数据
    public function ajax_delAll(){
        $str=$_POST[str];
        //实例化数据模型
        $model=M('admin');

        if ($data=$model->where("id in ($str)")->delete()) {
            # code...
            echo $data;
        } else {
            echo "0";
        }
    }

    //ajax的修改数据
    public function ajax_edit(){
        $id=$_POST['id'];

        //实例化数据模型
        $model=M('admin');

        //查询数据
        $user=$model->find($id);
        if ($user) {
            $user['password'] = '';
        }
        $this->data=$user;
        echo $this->fetch();
    }

    //ajax的更新数据
    public function ajax_save(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        //M验证
        $password = trim($arr['password']);
        $username = trim($arr['username']);
        $uid      = $arr[id];
        if (empty($username)) {
            $this->error('用户名必须填写！');
        }

        if (M('admin')->where(array('username' => $username, 'id' => array('neq', $uid)))->find()) {
            $this->error('用户名已经存在！');
        }

        $data = array(
            'id'        => $uid,
            'username'  => $username,
            'realname'  => trim($arr['realname']),
            'logintime' => time(),
            'statu'    => $arr['statu'],
        );

        //如果密码不为空，即是修改了密码
        if (!$password == '') {
            $passwordinfo     = get_password($password);
            $data['password'] = $passwordinfo['password'];
            $data['encrypt']  = $passwordinfo['encrypt'];
        }

        //实例化数据模型
        $model=D('admin');

        //更新数据
        if ($model->create($data)) {
            $id = $model->save();
            $this->data=$model->find($data[id]);
            echo $this->fetch();
        } else {
            echo "0";
        }   
    }

    public function ajax_statu(){
        //实例化数据模型
        $model=M('admin');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }

}