<?php
namespace Admin\Controller;
use Common\Lib\Category;

class SearchController extends CommonController {
    public function index(){
    	$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

    	$where = array();
    	if (!empty($keyword)) {
    		$where['keyword'] = array('LIKE', "%{$keyword}%");
    	}

    	$count = M('search')->where($where)->count();

    	$page = new \Think\page($count,20);
    	$page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    	$limit = $page->firstRow . ',' . $page->listRows;
    	$list = M('search')->where($where)->order('id desc')->limit($limit)->select();

    	$this->assign('page', $page->show());
    	$this->assign('vlist', $list);
    	$this->assign('type', '搜索词列表');
    	$this->assign('keyword', $keyword);
        $this->display();
    }

    //编辑搜索词语
    public function edit(){
    	//获取关键字id
    	$id = I('id', 0, 'intval');

    	if (IS_POST) {
    		$this->editPost();
    		exit();
    	}

    	$this->vo = M('search')->find($id);
    	$this->assign('type','编辑搜索词');
    	$this->display();
    }

    //编辑处理

    public function editPost(){
    	$data = $_POST;
    	$data['keyword'] = trim($data['keyword']);
    	$data['id'] = $data['id']?$data['id']:I('id', 0, 'intval');
    	if (empty($data['keyword'])) {
    		$this->error('搜素词不能为空');
    	}

    	if (M('search')->save($data)) {
    		$this->success('修改成功', U('Search/index'));
    	} else {
    		$this->error('修改失败');
    	}
    }

    //删除关键词
    public function del(){
    	$id = I('id', 0, 'intval');

    	if (M('search')->delete($id)) {
    		$this->success('删除成功', U('Search/index'));
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

    	if (M('search')->where($where)->delete()) {
    		$this->success('删除成功', U('Search/index'));
    	} else {
    		$this->error('删除失败');
    	} 
	}
    //批量审核
    public function check(){
        $idArr = I('key', 0, 'intval');

        if (!is_array($idArr)) {
            $this->error('请选择要审核的项');
        }
        $where['id'] = array('in', $idArr);
        $result = M('search')->where($where)->select();
        foreach ($result as $list) {
            $map['id'] = $list['id'];
            if ($list['status'] == 0) {
                M('search')->where($map)->setField('status', 1);
            } else {
                M('search')->where($map)->setField('status', 0);
            }
            
        }
        $this->success('审核成功', U('Search/index'));
    }

    //批量推荐
    public function recommend(){
        $idArr = I('key', 0, 'intval');

        if (!is_array($idArr)) {
            $this->error('请选择要审核的项');
        }
        $where['id'] = array('in', $idArr);
        $result = M('search')->where($where)->select();
        foreach ($result as $list) {
            $map['id'] = $list['id'];
            if ($list['recommend'] == 0) {
                M('search')->where($map)->setField('recommend', 1);
            } else {
                M('search')->where($map)->setField('recommend', 0);
            }
            
        }
        $this->success('审核成功', U('Search/index'));
    }
}