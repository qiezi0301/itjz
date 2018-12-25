<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends CommonController {
    public function index(){
    	$uid = intval(get_cookie('uid'));
    	if ($uid) {
    		$this->redirect('Index/index');
    	}
    	$this->assign('title','会员登录-');
        $this->display();
    }

    //ajax登录提交
    public function check(){
		$data['error'] = '';
		$data['tip'] = '';
		if (!IS_AJAX) {
			$data['error'] = '非法提交！';
			$this->ajaxReturn($data);
        }

        $email    = I('username', '', 'htmlspecialchars,trim');
        $password = I('pwd', '');
		$rememberme = I('rememberme', '');
		
        $verify = I('vcode', '', 'htmlspecialchars,trim');
        if (C('CFG_VERIFY_LOGIN') == 1 && !check_verify($verify)) {
            //$this->error('验证码不正确');
			$data['error'] = '验证码不正确！';
			$this->ajaxReturn($data);
        }

        if ($email == '') {
            //$this->error('请输入帐号！', '', array('input' => 'email')); //支持ajax,$this->error(info,url,array);
			$data['error'] = '邮箱不能为空！';
			$this->ajaxReturn($data);
        }

        /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$data['error'] = '邮箱格式不正确！';
			$this->ajaxReturn($data);
        }*/

        if (strlen($password) < 6 || strlen($password) > 20) {
            //$this->error('密码必须是6-20位的字符！', '', array('input' => 'password'));
			$data['error'] = '密码必须是6-20位的字符！';
			$this->ajaxReturn($data);
        }

        $user = M('member')->where(array('email' => $email))->find();
		if(empty($user)){
			$user = M('member')->where(array('username' => $email))->find();
		}

        if (!$user || ($user['password'] != get_password($password, $user['encrypt']))) {
            //$this->error('账号或密码错误', '', array('input' => 'password'));
			$data['error'] = '账号或密码错误！';
			$this->ajaxReturn($data);
        }

        if ($user['islock']) {
            //$this->error('用户被锁定！', '', array('input' => ''));
			$data['error'] = '用户被锁定！';
			$this->ajaxReturn($data);
        }
		
        //更新数据库的参数
        $rs = array(
			'id' => $user['id'], //保存时会自动为此ID的更新
            'logintime'        => time(),
            'loginip'          => get_client_ip(),
            'loginnum'         => $user['loginnum'] + 1,

        );
        //更新数据库
        M('member')->save($rs);
		
		if($rememberme){
			$expire = time()+3600*24*14;
		}else{
			$expire = 0;
		}
        //保存Session
        //session(C('USER_AUTH_KEY'), $user['id']);
        //保存到cookie
        set_cookie(array('name' => 'uid', 'value' => $user['id'], 'expire' => $expire));
        set_cookie(array('name' => 'email', 'value' => $user['email'], 'expire' => $expire));
        set_cookie(array('name' => 'username', 'value' => $user['username'], 'expire' => $expire));
        set_cookie(array('name' => 'groupid', 'value' => $user['groupid'], 'expire' => $expire)); //20140801
        set_cookie(array('name' => 'logintime', 'value' => date('Y-m-d H:i:s', $user['logintime']), 'expire' => $expire));
        set_cookie(array('name' => 'loginip', 'value' => $user['loginip'], 'expire' => $expire));
        set_cookie(array('name' => 'status', 'value' => $user['status'], 'expire' => $expire)); //激活状态
        set_cookie(array('name' => 'verifytime', 'value' => time(), 'expire' => $expire)); //激活状态
		//set_cookie(array('name' => 'expiretime', 'value' => $expire, 'expire' => $expire));

        //跳转
        //$this->redirect(MODULE_NAME.'/Member/index');
        //redirect(__MODULE__);
        //$this->success('登录成功', $furl, array('input' => ''));
		$data['username'] = $user['username'];
		$data['avatar'] = $user['face'];
		$data['is_collect'] = $user['username'];
		$data['msg_num'] = $user['message'];
        $data['userid'] = $user['id'];
		$data['score'] = C('LOGIN_SCORE');
		//同一天内登录只加一次积分
		if (date('Y-m-d', $user['logintime']) != date('Y-m-d', time())) {
			
			// //记录积分操作
			// $log['uid'] = $user['id'];
			// $log['scoreinfo'] = '+'.C('LOGIN_SCORE');
			// $log['type'] = 1;
			// $log['addtime'] = time();
   //          $log['title'] = '会员登录';
   //          $log['url'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
			// $log['descrip'] = '登录成功，积分+'.C('LOGIN_SCORE');
			// M('member_slog')->add($log);
			// M('member')->where(array('id' => $user['id']))->setInc('score',$data['score']);  //增加积分
			addPoints('会员登录', $data['score'], $data['userid'], "登录成功，积分+" . $data['score'], 1);
			$data['tip'] = '登录成功！积分+'.$data['score'];
		}else{
			$data['tip'] = '登录成功！';
		}
		
		$this->ajaxReturn($data);
    }
}