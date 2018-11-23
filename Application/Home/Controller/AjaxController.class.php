<?php
namespace Home\Controller;

class AjaxController extends CommonController{
	public function downloadZipBox(){
        $uid = get_cookie('uid');

        // $data['is_original'] = I('is_original', 0, 'intval');//是不是原创
        // $data['points_type'] = I('points_type', 0, 'intval');//点数类型
        // $data['points'] = I('points', 0, 'intval');//下载点数
        // $data['id'] = I('id', 0, 'intval');
        // $data['mtype'] = I('mtype', 0, 'intval');
        $money = M('member')->where("id = $uid")->getField('score');;

        echo json_encode(array("code" => 200, "userid" => $uid, "money" => $money)); 

    }
    
    //是否登录
    public function isLogin(){
		// dump($_COOKIE);
		if (!IS_AJAX) {
            exit();
        }
		
        $uid        = intval(get_cookie('uid'));
        $email      = get_cookie('email');
        $username   = get_cookie('username');
        $logintime  = get_cookie('logintime');
        $loginip    = get_cookie('loginip');
        $verifytime = intval(get_cookie('verifytime')); //上次登录时间

        $furl = '';

        $username = empty($username) ? $email : $username;

        if ($uid <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            del_cookie(array('name' => 'uid'));
            del_cookie(array('name' => 'username'));
            del_cookie(array('name' => 'verifytime'));
            del_cookie(array('name' => 'logintime'));
            //$this->error('请登录', ''); //支持ajax,$this->error(info,url,array);
			$this->ajaxReturn(0);
        }

		$user = M('member')->where(array('id' => $uid, 'email' => $email))->find();
		if (!$user) {
			del_cookie(array('name' => 'uid'));
			del_cookie(array('name' => 'username'));
			del_cookie(array('name' => 'verifytime'));
			del_cookie(array('name' => 'logintime'));
			//$this->error('请登录!', '');
			$this->ajaxReturn(0);
		}
		if (date('Y-m-d', $user['logintime']) != date('Y-m-d', time())) {
		
		//记录积分操作
		/*$log['uid'] = $uid;
		$log['scoreinfo'] = '+'.C('LOGIN_SCORE');
		$log['type'] = 1;
		$log['addtime'] = time();
		$log['descrip'] = '自动登录成功，积分+'.C('LOGIN_SCORE');
		M('member_slog')->add($log);
		M('member')->where(array('id' => $uid))->setInc('score',C('LOGIN_SCORE'));  //增加积分*/
		
			set_cookie(array('name' => 'verifytime', 'value' => time())); //本次状态
		}

        //$this->success('已登录', $furl, array('username' => $username));
		$data['username'] = $username;
		$data['avatar'] = $user['face']?$user['face']:'/Public/Home/images/avatar.jpg';
		$data['is_collect'] = 0;
		$data['msg_num'] = 0;
		$data['userid'] = $uid;
		$data['tip'] = '登录成功！';
		$this->ajaxReturn($data);
    }

    //评论
    public function subcomment(){
    	header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
    	if (!IS_AJAX || !IS_POST) {
    		$this->error('非法请求');
    	}

    	//验证
    	$data['postid'] = I('id', 0, 'intval');//文章id
    	$data['cid'] = I('mtype', 0, 'intval');
    	$data['pid'] = I('pid', 0, 'intval');
    	$data['pid_sub'] = I('pid_sub', 0, 'intval');
    	$data['title'] = I('title', '');
    	$data['content'] = addslashes(str_replace("\n", "<br />", $_POST['content'])); 
    	$data['posttime'] = time();
    	$data['ip'] = get_client_ip();
    	$data['agent'] = $_SERVER['HTTP_USER_AGENT'];


    	$uid = intval(get_cookie('uid')); //不能用empty(get_cookie('uid')),empty不能用于函数返回值

    	if ($uid == 0 ) {
    		//允许匿名评论
			$result['code'] = 'domain_times';
        	$result['error'] = '请登录后评论';
            $this->ajaxReturn($result);
    	}
 
        $info = M("comment")->field("id")->where("content='" . $data['content'] . "'")->find(); 
        if ($info['id']) { 
            echo json_encode(array("code" => "comment_repeat", "error" => "检测到重复评论，您似乎提交过这条评论了")); 
            exit; 
        } 

    	if (empty($data['postid']) || empty($data['cid'])) {
			$result['code'] = 'domain_times';
        	$result['error'] = '参数错误';
            $this->ajaxReturn($result);
    	}

    	if (empty($data['title'])) {
			$result['code'] = 'domain_times';
        	$result['error'] = '文章不正确，请刷新再评论';
            $this->ajaxReturn($result);
    	}

		if (strlen(preg_replace('/\[  [^\)]+?  \]/x', '', $data['content'])) < 10) { 
            echo json_encode(array("code" => "short than 10", "error" => "评论的内容不能少于10个字符。")); 
            exit; 
        } 

        if (time() - session("comment_time") < 60 && session("comment_time") > 0) {//2分钟以后发布 
            echo json_encode(array("code" => "fast", "error" => "您提交评论的速度太快了，请稍后再发表评论。")); 
            exit; 
        } 

    	if (check_badword($data['content'])) {
    		$result['code'] = 'domain_times';
        	$result['error'] = '评论内容包含非法信息，请认真填写！';
            $this->ajaxReturn($result);
    	}

    	if (!empty($uid)) {
    		$data['userid'] = $uid;
    		$data['email'] = get_cookie('email'); 
    		$data['username'] = get_nickname($uid);
    	} else {
            $data['userid']   = 0;
            $data['username'] = I('username', '游客');
    	}

    	if ($id = M('comment')->add($data)) {
    		$list = array(
    			'id' => $id,
    			'user_id' => $uid,
    			'review_id' => $data['pid'],
    			'username' => $data['username'],
    			'ico' => '',
    			'avatar' => get_avatar(get_cookie('face'), 0),
    			'content' => $data['content'],
    			'posttime' => date('Y-m-d H:i:s', time()),
    		);
    		$furl = $_SERVER['HTTP_REFERER'];
    		// $this->success('添加成功', $furl, $list);
    		session("comment_time", time()); 

    		$points_comment = C('REPLY_SCORE');

             //少于5条每天，则添加积分 
    		$day_start = strtotime(date("Y-m-d")); 
            $day_end = $day_start + 3600 * 24; 
            $comment_num_day = M("comment")->where("userid = " . $uid . " AND posttime between " . $day_start . " AND " . $day_end . "")->count();
            if ($comment_num_day <= 5) {
              	 addPoints($data['title'], $points_comment, $uid, "评论获得" . $points_comment . "积分", 1);               	
            } else{    
                $points_comment = 0;    	
            }
        	echo json_encode(array("code" => 200, "comment" => $data['content'], "points" => $points_comment)); 
    	} else {
    		echo json_encode(array("code" => 'domain_times', "error" => '添加失败'.M('comment')->getError())); 
    	}

    }

    //修改个人签名
    public function signature(){

        $uid = get_cookie('uid');

        if (!IS_AJAX || !IS_POST) {
            $this->error('非法请求');
        }

        $data['signature'] = I('content', '');
        $data['userid'] = $uid;

        if (M('memberdetail')->save($data) !== false) {
            echo json_encode(array("code" => '200', "message" => '保存成功')); 
        }else{
            echo json_encode(array("code" => '500', "message" => '保存失败')); 
        }        
    }

    //Ajax修改个人资料
    public function updateInfo(){
        $uid = get_cookie('uid');

        if (!IS_AJAX || !IS_POST) {
            $this->error('非法请求');
        }

        $data['nickname'] = I('nickname', '', 'htmlspecialchars,trim');
        $data['qq']       = I('qq_num', '');
        $data['job'] = I('job', '', 'htmlspecialchars,trim');
        $data['address']  = I('area', '');
        $data['sex']      = I('sex', 0, 'intval');
        // $data['birthday'] = I('birthday', '0000-00-00');
        // $data['tel']      = I('tel', '');
        $data['signature']   = I('signature', '');
        $data['maxim']    = I('maxim', '');

        $data['userid']     = $uid;
        $data['updatetime'] = time();
        $new                = I('new', 0, 'intval');
        if (empty($data['nickname'])) {
            $this->error('请输入姓名！');
        }

        $new                = M('memberdetail')->find($uid);

        $result = true;
        if (!$new) {
            $result = M('memberdetail')->add($data);
        } else {
            $result = M('memberdetail')->save($data);
        }

        if (false !== $result) {
            echo json_encode(array("code" => '200', "message" => '修改基本资料成功')); 
        } else {
            echo json_encode(array("code" => '500', "message" => '修改基本资料失败')); 
        }
        exit();
        
    }
}