<?php
namespace Home\Controller;
class RegController extends CommonController {
    public function index(){
    	$uid = intval(get_cookie('uid'));
    	if ($uid) {
    		$this->redirect('Index/index');
    	}
    	$this->assign('title','会员注册-');
        $this->display();
    }

    //注册
    public function save(){
    	if (!IS_AJAX) {
			$this->error('非法注册！');
		}

        if (!IS_POST) {
			$this->error('非法注册！');
    	}

    	$code = I('code', '', 'htmlspecialchars,trim');	
    	$email = I('email', '', 'htmlspecialchars,trim');

    	//验证激活码
        $row = M('active')->where(array('expire' => array('gt', time()),'email' => $email))->find();

        if ($code != $row['code']) {
        	$data['error'] = '验证码过期或错误！';
            $this->ajaxReturn($data);
        }

        $db = M('member');
        $username = I('username', '', 'htmlspecialchars,trim');

        $where['username'] = $username; 
        $where['email'] = $email; 
        $where['_logic'] = 'or';

        $user = $db->where($where)->find();
        if ($user) {
        	$data['error'] = '邮箱／用户已存在';
            $this->ajaxReturn($data);
        }

        $notallowname = explode(',', C('CFG_MEMBER_NOTALLOW'));
        if (in_array($username, $notallowname)) {
        	$data['error'] = '此昵称系统禁用，请重新更换一个！';
            $this->ajaxReturn($data);
        }
        $membergroup = M('membergroup')->find(array('name' => '新手上路'));
        $data['groupid'] = $membergroup['id']; //注册会员	        
    	$data['email']    = $email;
    	$data['username'] = $username;
    	$data['regtime']  = time();
    	$passwordinfo     = I('pwd', '', 'get_password');
    	$data['password'] = $passwordinfo['password'];
    	$data['encrypt']  = $passwordinfo['encrypt'];

    	if ($id = $db->add($data)) {
    		$data['error'] = '';
        	$data['tip'] = '注册会员成功';
            $this->ajaxReturn($data);
    	} else {
        	$data['error'] = '注册失败';
            $this->ajaxReturn($data);
    	}
        	
	}    


    //发送邮件验证码
    public function send(){
        if(IS_AJAX){
            //接收邮箱
            $email = I('email', '', 'htmlspecialchars,trim');

            //验证邮箱格式
            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if(preg_match( $pattern, $email)){
                //邮箱格式正确下，判断用户是否存在
                $Manager = D('member');
                // $condition['display'] = 1;
                $condition['email'] = $email;
                $ManagerInfo = $Manager->where($condition)->find();
                if(!$ManagerInfo){
		            //查询active表，是否发送过注册验证码，发过，则不再重新生成新的验证码，直接发送
		        	$actinfo = M('active')->where(array('email' => $email, 'type' => 1, 'expire' => array('gt', time())))->order('expire DESC')->find();

		        	$data    = array();
		            $data['updatetime'] = time();
			        //有记录
			        if ($actinfo) {
			            $data['id']     = $actinfo['id'];
			            $data['userid'] = 0;
			            $data['code']   = $actinfo['code'];
			            $data['expire'] = $actinfo['expire'];
			            $data['type']   = $actinfo['type'];
			            //小于3分钟,则更新有效期(延长)
			            if ($data['expire'] - time() < 3 * 60) {
			                $data['expire'] = time() + 20 * 60; //20 minutes
			                M('active')->where(array('id' => $data['id']))->setField('expire', $data['expire']);
			            }
			        } else {
			            $data['userid'] = 0;
			            $data['code']   = get_random(6, '1234567890'); //产生数字
			            $data['expire'] = time() + 20 * 60; //20 minutes//strtotime("+2 day")  ;
			            $data['email']  = $email;
			            $data['type']   = 1;
			            $data['id'] = M('active')->add($data);
			        }

                    //设置参数
                    $title = "ITJZ用户注册验证码";    //邮件标题
                    $font = "欢迎您加入ITJZ，您在ITJZ的注册验证码是："; //邮件内容
                    $code = $data['code'];    //随机码
                    $content = $font.$code; //拼接

                    if(SendMail($email,$title,$content)) {
                    		//发送成功
                            $data['code'] = 200;
                            $data['result'] = 'ok';
                            $this->ajaxReturn($data,0);
                    }else{
                    	$data['result'] = "发送失败,请检查邮箱是否正确";
                        $this->ajaxReturn($data,0);
                    }

                }else{
                	//邮箱存在，不能注册
                	$data = array('code' => 'email_has');
                    $this->ajaxReturn($data,0);
                }
            }else{
            	$data = array('result' => '请核对邮箱信息');
                $this->ajaxReturn($data,0);
            }
        }else{
        	$data = array('result' => '邮箱格式不正确');
            $this->ajaxReturn($data,0);
        }
    }
}