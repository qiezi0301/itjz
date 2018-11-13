<?php
namespace Admin\Controller;
use Common\Lib\Category;

class CommentController extends CommonController {
    public function index(){
        
    	$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

    	$where = array();
    	if (!empty($keyword)) {
    		$where['content'] = array('LIKE', "%{$keyword}%");
    	}

    	$count = D('CommentView')->where($where)->count();

    	$page = new \Think\page($count,20);
    	$page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    	$limit = $page->firstRow . ',' . $page->listRows;
    	$list = D('CommentView')->where($where)->order('id desc')->limit($limit)->select();
        // p($list);
        // die();
    	$this->assign('page', $page->show());
    	$this->assign('vlist', $list);
    	$this->assign('type', '评论管理');
    	$this->assign('keyword', $keyword);
        $this->display();
    }

    //编辑评论
    public function edit(){
    	//获取关键字id
    	$id = I('id', 0, 'intval');

    	if (IS_POST) {
    		$this->editPost();
    		exit();
    	}

    	$this->vo = M('comment')->find($id);
    	$this->assign('type','编辑评论');
    	$this->display();
    }

    //编辑处理

    public function editPost(){
    	$data = $_POST;
    	$data['content'] = trim($data['content']);
    	$data['id'] = $data['id']?$data['id']:I('id', 0, 'intval');

    	if (M('comment')->save($data)) {
    		$this->success('修改成功', U('Comment/index'));
    	} else {
    		$this->error('修改失败');
    	}
    }

    //删除关键词
    public function del(){
    	$id = I('id', 0, 'intval');

    	if (M('comment')->delete($id)) {
    		$this->success('删除成功', U('comment/index'));
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

    	if (M('comment')->where($where)->delete()) {
    		$this->success('删除成功', U('comment/index'));
    	} else {
    		$this->error('删除失败');
    	} 
	}
    
}