<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->display();
    }

    //登录验证
    public function login() {
    	if (!IS_POST) {
			E('页面不存在');
		}

		$username = I('username', '', 'htmlspecialchars,trim');
		$password = I('password', '');
		$verify = I('code', '', 'htmlspecialchars,trim');

		if (!check_verify($verify, 'a_login_1')) {
			$this->error('验证码不正确');
		}

		if ($username == '' || $password == '') {
			$this->error('账号或密码不能为空');
		}

		$user = M('admin')->where(array('username' => $username))->find();
		if (!$user || ($user['password'] != get_password($password, $user['encrypt']))) {
			$this->error('账号或密码错误');
		}

		//更新数据库的参数
		$data = array('id' => $user['id'], //保存时会自动为此ID的更新
			'logintime' => time(),
			'loginip' => get_client_ip(),
			'loginnum' => ($user['loginnum']+1),
		);

		//更新数据库
		M('admin')->save($data);

		//保存Session
		session(C('USER_AUTH_KEY'), $user['id']);
		session('yang_adm_username', $user['username']);
		session('yang_adm_logintime', date('Y-m-d H:i:s', $user['logintime']));
		session('yang_adm_loginip', $user['loginip']);
		session('yang_adm_loginnum', $user['loginnum']);

		//跳转
		$this->success('登录成功', U('Index/index'));

    }
	//登录验证码
	public function verify($id = '1') {

		//ob_clean();
		$config = array(
			'fontSize' => 18,
			'length' => 4,
			'imageW' => 230,
			'imageH' => 40,
			'bg' => array(255, 255, 255),
			// 'useCurve' => false,
			'useNoise' => false,
			'reset'     =>  false,
		);
		$verify = new \Think\Verify($config);
		$verify->entry($id);
	}

	//ajax验证码
	public function check_verify(){
		$verify = I('code', '', 'htmlspecialchars,trim');
		if (check_verify($verify, 'a_login_1')) {
			$this->ajaxReturn(1);
		}
	}

	//退出
	public function logout() {
		session_unset();
		session_destroy();
		$this->redirect('Login/index');
	}
}
