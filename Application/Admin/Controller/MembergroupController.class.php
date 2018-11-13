<?php
namespace Admin\Controller;
use Common\Lib\Category;

class MembergroupController extends CommonController {
    public function index(){
    	$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

    	$where = array();
    	if (!empty($keyword)) {
    		$where['name'] = array('LIKE', "%{$keyword}%");
    	}

    	$count = M('membergroup')->where($where)->count();

    	$page = new \Think\page($count,20);
    	$page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    	$limit = $page->firstRow . ',' . $page->listRows;
    	$list = M('membergroup')->where($where)->order('rank, id')->limit($limit)->select();

    	$this->assign('page', $page->show());
    	$this->assign('vlist', $list);
    	$this->assign('type', '会员组管理');
    	$this->assign('keyword', $keyword);
        $this->display();
    }

    public function add(){
        if (IS_POST) {
            $this->addPost();
            exit();
        }
        $this->assign('type', '添加会员组');
        $this->display();
    }

    //添加会员组
    public function addPost(){
        //验证
        $validate = array(
            array('name', 'require', '会员组必须填写'),
            array('name', '', '会员组名已经存在！', 0, 'unique', 1),
        );
        $db = M('membergroup');
        if (!$db->validate($valite)->create()) {
            $this->error($db->getError());
        }
        if ($db->add()) {
            $this->success('添加成功', U('Membergroup/index'));
        } else {
            $this->error('添加失败');
        }
    }

    //编辑会员组
    public function edit(){
    	//获取关键字id
    	$id = I('id', 0, 'intval');


    	if (IS_POST) {
    		$this->editPost();
    		exit();
    	}

    	$this->vo = M('membergroup')->find($id);
    	$this->assign('type','编辑会员组');
    	$this->display();
    }

    //编辑处理

    public function editPost(){
        $name = I('name', '', 'trim');
        $id   = I('id', 0, 'intval');
        if (empty($name)) {
            $this->error('会员组名必须填写！');
        }

        if (M('membergroup')->where(array('name' => $name, 'id' => array('neq', $id)))->find()) {
            $this->error('会员组名已经存在！');
        }

        if (false !== M('membergroup')->save($_POST)) {
            $this->success('修改成功', U('Membergroup/index'));
        } else {

            $this->error('修改失败');
        }
    }

    //删除关键词
    public function del(){
    	$id = I('id', 0, 'intval');

    	if (M('membergroup')->delete($id)) {
    		$this->success('删除成功', U('Membergroup/index'));
    	} else {
    		$this->error('删除失败');
    	}    	
    }

    //批量删除
    public function delBatch(){
    	$idArr = I('key', 0, 'intval');

        if (!is_array($idArr)) {
            $this->error('请选择要彻底删除的项');
        }

    	$where['id'] = array('in', $idArr);

    	if (M('membergroup')->where($where)->delete()) {
    		$this->success('删除成功', U('Membergroup/index'));
    	} else {
    		$this->error('删除失败');
    	} 
	}
}