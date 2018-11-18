<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends CommonController {
    public function _initialize(){
        parent::_initialize(); // 加上这一句，才执行父类的初始化函数

        //用户中心公共部分
        $uid = intval(get_cookie('uid'));
        if (empty($uid)) {
            $this->redirect(MODULE_NAME .'/Login/index');
        }
        $member = D('MemberView')->nofield('password,encrypt')->find($uid);
        
        if (!$member) {
            $this->error('请重新登录', U(MODULE_NAME .'/Login/index'));
        }
        $member['detail'] = M('memberdetail')->find($uid);
        if (empty($member['detail'])) {
            $member['detail'] = array(
                'realname' => '还没设置',
                'sex'      => '保密',
                'birthday' => '0000-00-00',
                'animal'   => '保密',
                'star'     => '保密',
                'province' => '保密',
                'area'     => '保密',
            );
        } else {
            $member['detail']['sex']    = $member['detail']['sex'] ? '女士' : '男士';
            $member['detail']['animal'] = get_item_value('animal', $member['detail']['animal']);
            $member['detail']['star']   = get_item_value('animal', $member['detail']['star']);
        }
        //自动升级会员组
        if($member['score'] > $member['rankend']){
            $gmap['id'] = array('gt',$member['groupid']);
            $upgroup = M('membergroup')->where($gmap)->order('id ASC')->find();
            M('member')->where("id=$uid")->setField('groupid',$upgroup['id']);
        }
        // p($member);
        $this->assign('member', $member);
        $this->assign('uid', $uid);
        
    }
    public function index(){
        $this->display();
    }
    public function info(){

        $this->display();
    }

    public function set(){
        $uid = get_cookie('uid');

        $userdetail = M('memberdetail')->where(array('userid' => $uid))->find();
        if (!$userdetail) {
            $userdetail = array(
                'uid'      => $uid,
                'email'    => get_cookie('email'),
                'realname' => '',
                'sex'      => 0,
                'birthday' => '1990-1-1',
                'address'  => '',
                'tel'      => '',
                'mobile'   => '',
                'qq'       => '',
                'maxim'    => '',
            );
            $userdetail['type'] = 1;
        } else {
            $userdetail['type'] = 0;
            $userdetail['uid']   = $uid;
            $userdetail['email'] = get_cookie('email');
        }
        //dump($userdetail);
        $this->assign('detail', $userdetail);
        $this->assign('title', '基本资料');
        $this->display();
    }

    public function comments(){
        $uid = get_cookie('uid');

        $map['userid'] = $uid;
        $count = D('CommentView')->where($map)->count();
        
        $thisPage = new \Common\Lib\Page($count, 15);
        $thisPage->rollPage = 3;
        $thisPage->setConfig('prev',"上一页");
        $thisPage->setConfig('next',"下一页");
        $thisPage->setConfig('theme', ' %HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $thisPage->firstRow . ',' . $thisPage->listRows;
        $page  = $thisPage->show();
        
        $list = D('CommentView')->where($map)->order('id desc')->limit($limit)->select();
        //dump($list);
        $this->assign('title', '我的评论');
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }


    public function downloads(){
        $uid = get_cookie('uid');
        echo $uid;
        $map['uid'] = $uid;
        $count = D('DownloadView')->where($map)->count();
        $thisPage = new \Common\Lib\Page($count, 15);
        $thisPage->rollPage = 3;
        $thisPage->setConfig('prev',"上一页");
        $thisPage->setConfig('next',"下一页");
        $thisPage->setConfig('theme', ' %HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $thisPage->firstRow . ',' . $thisPage->listRows;
        $page  = $thisPage->show();

        $list = D('DownloadView')->where($map)->order('id desc')->limit($limit)->select();
        foreach ($list as $key => $value) {
            //path中获取pid
            $list[$key]['pid'] = array_filter(explode(',',$value['path']))[1];
        }
        // p($list);
        //dump($list);
        $this->assign('title', '我的评论');
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }
    public function collects(){
        $this->display();
    }
    public function logs(){
        $uid = get_cookie('uid');
        $map['uid'] = $uid;
        $count = M('member_slog')->where($map)->count();
        $thisPage = new \Common\Lib\Page($count, 15);
        $thisPage->rollPage = 3;
        $thisPage->setConfig('prev',"上一页");
        $thisPage->setConfig('next',"下一页");
        $thisPage->setConfig('theme', ' %HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $thisPage->firstRow . ',' . $thisPage->listRows;
        $page  = $thisPage->show();

        $list = M('member_slog')->where($map)->order('id desc')->limit($limit)->select();

        $this->assign('lists', $list);
        $this->assign('page', $page);
        $this->assign('title', '积分收支明细');
        $this->display();
    }

    //Ajax检查密码是否正确
    public function checkPwd(){
        $uid = get_cookie('uid');
        $oldpassword = I('pwdOld', '');
        $self = M('member')->field(array('email', 'password', 'encrypt'))->where(array('id' => $uid))->find();

        if (!$self) {
            $this->ajaxReturn('用户不存在，请重新登录');
        }

        if (get_password($oldpassword, $self['encrypt']) != $self['password']) {
            $this->ajaxReturn(false);
        }else{
            $this->ajaxReturn(true);
        }
    }

    public function pwd_post(){
        $uid = get_cookie('uid');
        if (!IS_POST) {
            $this->error('非法请求');
        }

        $oldpassword = I('pwdOld', '');
        $password    = I('pwd', '');
        $repassword  = I('pwd2', '');

        if (empty($oldpassword)) {
            $this->error('请填写旧密码！');
        }
        if (empty($password)) {
            $this->error('请填写新密码！');
        }

        if ($password != $repassword) {
            $this->error('两次密码不一样，请重新填写！');
        }
        $self = M('member')->field(array('email', 'password', 'encrypt'))->where(array('id' => $uid))->find();
        if (!$self) {
            $this->error('用户不存在，请重新登录');
        }

        if (get_password($oldpassword, $self['encrypt']) != $self['password']) {
            $this->error('旧密码错误');
        }

        $passwordinfo = get_password($password);

        $data = array(
            'id'       => $uid,
            'password' => $passwordinfo['password'],
            'encrypt'  => $passwordinfo['encrypt'],
        );

        if (false !== M('member')->save($data)) {
            $this->success('修改密码成功', U(MODULE_NAME . '/Member/pwd'));
        } else {

            $this->error('修改密码失败');
        }
    }


    public function templates(){
        $this->display();
    }
    public function uploads(){
        $this->display();
    }

    public function avatar_post(){
        $uid = get_cookie('uid');

        if (IS_POST) {
            $data['face'] = I('base64url');
            $data['id'] = $uid;
            if (empty($data['face'])) {
               echo json_encode(array("code" => '400', "message" => '请上传图片')); 
            }

            $data['face'] = $this->Base64StrShangChuan($data['face']);

            if (M('member')->save($data) !== false) {
                //$this->success('保存成功',U('Member/avatar'));
                echo json_encode(array("code" => '200', "message" => '上传成功')); 
            }else{
                $this->error('保存失败！');
            }
            exit();
        }
    }

    /**
     * @param Base64图片上传方法
     *
     * @return 成功返回图片链接 失败返回false
    */
    public function Base64StrShangChuan($image){

        $imageName = "/25220_".date("His",time())."_".rand(1111,9999).'.png';  //图片名称   

        define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/../../../'))."/");

        $path = BASE_PATH.'/Public/images/'.date("Ymd");  //获取文件目录

        $return_url = '/Public/images/'.date("Ymd").$imageName;

        if (!is_dir($path)){     //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }

        $imageurl =  $path.$imageName;  

         //截取data:image/png;base64, 这个逗号后的字符如果前台传的base64字符串已经截取过，把这一行代码注释调掉就OK
        if (strstr($image,",")){
            $image = explode(',',$image);
            $image = $image[1];
        }

        $is_success = file_put_contents($imageurl, base64_decode($image));   //返回的是字节数 

        if($is_success > 0)
        {
            return $return_url;
        }
        else
        {
            return false;
        }

    }
}