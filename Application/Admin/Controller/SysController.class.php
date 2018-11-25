<?php
namespace Admin\Controller;
use Think\Controller;
class SysController extends CommonController {
	//系统首页
    public function index(){
        if (IS_POST) {

            $data                      = I('config', array(), 'trim');
            $data['CFG_IMGTHUMB_SIZE'] = strtoupper($data['CFG_IMGTHUMB_SIZE']);
            if (empty($data['CFG_IMGTHUMB_SIZE'])) {
                $this->error('缩略图组尺寸不能为空');
            }
            $data['CFG_IMGTHUMB_SIZE']  = str_replace(array('，', 'Ｘ'), array(',', 'X'), $data['CFG_IMGTHUMB_SIZE']);
            $data['CFG_IMGTHUMB_WIDTH'] = str_replace(array('，', 'Ｘ'), array(',', 'X'), $data['CFG_IMGTHUMB_WIDTH']);

            foreach ($data as $k => $v) {
                $ret = M('config')->where(array('name' => $k))->save(array('value' => $v));
            }
            if ($ret !== false) {
                F('config/site', null);
                $this->success('修改成功', U('Sys/index'));

            } else {

                $this->error('修改失败！');
            }

            exit();
        }
        $vlist = M("config")->order('groupid,sort')->select();
        if (!$vlist) {
            $vlist = array();
        }
        $configgroup = get_item('configgroup');

        $glist = array();
        foreach ($configgroup as $k => $v) {
            $glist[$k] = array();
            foreach ($vlist as $k2 => $v2) {
                if ($k == $v2['groupid']) {
                    $glist[$k][] = $v2;
                    //unset($vlist[$k2]);
                }

            }
        }

        $this->assign('vlist', $glist);
        $this->assign('configgroup', $configgroup);
        $this->assign('groupnum', count($configgroup));
        $this->assign('configtype', get_item('configtype'));
        $this->display();
    }

    public function manage(){

        $groupid = I('groupid', 0, 'intval'); //类别ID
        $keyword = I('keyword', '', 'htmlspecialchars,trim'); //关键字

        $where = array('id' => array('GT', 0));
        if (!empty($groupid)) {
            $where['groupid'] = $groupid;
        }
        if (!empty($keyword)) {
            $where['name'] = array('LIKE', "%{$keyword}%");
        }

        $count = M("config")->where($where)->count();

        $page=new \Think\Page($count, 20);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $vlist = M("config")->where($where)->order('sort,id DESC')->limit($limit)->select();

        $this->assign('groupid', $groupid);
        $this->assign('keyword', $keyword);
        $this->assign('page', $page->show());
        $this->assign('vlist', $vlist);
        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('type', '配置项管理');
        $this->display();
    }

    public function add(){

        if (IS_POST) {
            $data            = I('post.');
            $data['groupid'] = I('groupid', 0, 'intval');
            $data['typeid']  = I('typeid', 0, 'intval');
            $data['sort']    = I('sort', 0, 'intval');
            $data['remark']    = I('remark', '', '');

            if (empty($data['name'])) {
                $this->error('请填写名称(标识)');
            }
            if (empty($data['title'])) {
                $this->error('请填写标题');
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['name'])) {
                $this->error('名称只能由字母、数字和"_"组成');
            }
            $data['name'] = strtoupper($data['name']);

            if (M('config')->where(array('name' => $data['name']))->find()) {
                $this->error('配置项名称(标识)已经存在，请更换');
            }

            if (M('config')->add($data)) {
                $this->success('添加成功', U('index'));
            } else {
                $this->error('添加失败');
            }

            exit();
        }

        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('configtype', get_item('configtype'));
        $this->assign('type', '添加配置项');
        $this->display();
    }

    public function edit()
    {
        $id = I('id', 0, 'intval');
        if (IS_POST) {
            $data            = I('post.');
            $id              = $data['id']              = I('id', 0, 'intval');
            $data['groupid'] = I('groupid', 0, 'intval');
            $data['typeid']  = I('typeid', 0, 'intval');
            $data['sort']    = I('sort', 0, 'intval');
            $data['remark']    = I('remark', '', '');

            if (empty($data['name'])) {
                $this->error('请填写名称(标识)');
            }
            if (empty($data['title'])) {
                $this->error('请填写标题');
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['name'])) {
                $this->error('名称只能由字母、数字和"_"组成');
            }
            $data['name'] = strtoupper($data['name']);

            if (M('config')->where(array('name' => $data['name'], 'id' => array('neq', $id)))->find()) {
                $this->error('配置项名称(标识)已经存在，请更换');
            }

            if (false !== M('config')->save($data)) {
                $this->success('修改成功', U('manage'));
            } else {
                $this->error('修改失败');
            }

            exit();
        }
        $vo          = M('config')->find($id);
        $vo['value'] = htmlspecialchars($vo['value']); //ueditor

        $this->assign('vo', $vo);
        $this->assign('type', '修改配置项');
        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('configtype', get_item('configtype'));
        $this->display();
    }

    //删除
    public function del()
    {

        $id      = I('id', 0, 'intval');
        $groupid = I('groupid', 0, '');
        //批量删除
        if (empty($id)) {
            $this->error('参数错误!');
        }

        if (M('config')->delete($id)) {
            $this->success('删除成功', U('manage', array('groupid' => $groupid)));

        } else {
            $this->error('删除失败');
        }
    }

    //批量更新排序
    public function sort()
    {
        $sortlist = I('sortlist', array(), 'intval');
        $groupid  = I('groupid', 0, 'intval');
        foreach ($sortlist as $k => $v) {
            $data = array(
                'id'   => $k,
                'sort' => $v,
            );
            M('config')->save($data);
        }
        $this->redirect('Sys/manage', array('groupid' => $groupid));
    }

    //清除缓存
    public function clearCache($dellog = false){
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码

        //清除缓存
        is_dir(DATA_PATH . '_fields/') && del_dir_file(DATA_PATH . '_fields/', false);
        is_dir(CACHE_PATH) && del_dir_file(CACHE_PATH, false); //模板缓存（混编后的）
        echo ('<p>清除模板缓存成功!</p>');
        is_dir(DATA_PATH) && del_dir_file(DATA_PATH, false); //项目数据（当使用快速缓存函数F的时候，缓存的数据）
        echo ('<p>清除项目数据成功!</p>');
        is_dir(TEMP_PATH) && del_dir_file(TEMP_PATH, false); //项目缓存（当S方法缓存类型为File的时候，这里每个文件存放的就是缓存的数据）
        echo ('<p>清除项目项目缓存成功!</p>');
        if ($dellog) {
            is_dir(LOG_PATH) && del_dir_file(LOG_PATH, false); //日志
        }
        is_file(RUNTIME_PATH . APP_MODE . '~runtime.php') && @unlink(RUNTIME_PATH . APP_MODE . '~runtime.php'); //RUNTIME_FILE

        echo '清除完成';
    }

    //轮播首页
    public function lunbo_list(){
    	//实例化模型
    	$model=M('slider');

    	$tot=$model->count();

    	$data=$model->order('sort desc')->select();
        foreach ($data as $key => $value) {
            $data[$key]['img'] = getImagePath('lunbo', str_replace("/","/sm_",$value['img']));
        }

    	$this->tot=$tot;
    	$this->data=$data;
    	$this->display();
    }

    //轮播添加页面
    public function lunbo_add(){
    	$this->display();
    }

    //轮播编辑页面
    public function lunbo_edit($id){
    	$model=M('slider');
    	$data=$model->find($id);
    	$data['sm_img'] = getImagePath('lunbo', str_replace("/","/sm_",$data['img']));
    	$this->data=$data;
    	$this->display();
    }

    //插入数据
    public function lunbo_insert(){
    	$model=D('slider');

		$data['name'] = trimall($_POST['name']);
		$data['sort'] = trimall($_POST['sort']);
		$data['url'] = trimall($_POST['url']);
		$data['img'] = $_POST['img'];

    	if ($model->create($data)) {
            $model->add();
    		$this->success('添加成功', U('lunbo_list'));
    	} else {
    		$this->error('添加失败');
    	}
    }

    public function lunbo_save(){
        $model=D('slider');

        if ($model->create($_POST,2)) {
            $model->save();
            if ($_POST['img']==$_POST['oldimg']) {
                # code...
            } else {
            unlink(getThumbPath('lunbo', $_POST['oldimg']));
            unlink(getThumbPath('lunbo', str_replace("/","/sm_",$_POST['oldimg'])));
            }
            $this->success('修改成功', U('lunbo_list'));
        } else {
            $this->error('修改失败');
        }

    }

    //ajax请求修改排序状态
    public function ajax_sorts(){
        $model=D('slider');
    	if ($model->create($_POST,2)) {
            $model->save();
    		echo "1";
    	} else {
    		echo "0";
    	}
    }

	//ajax删除数据
    public function ajax_dels(){
    	$id=$_POST['id'];
    	$model=M('slider');
		$data=$model->find($id);
    	if ($model->delete($id)) {
    		unlink(getThumbPath('lunbo', $data['img']));
    		unlink(getThumbPath('lunbo', str_replace("/","/sm_",$data['img'])));
    		echo "1";
    	} else {
    		echo "0";
    	}

    }

     /**
     * 上传图片
     */
    public function upload() {
        echo parent::uploadfile(array('jpg', 'gif', 'png', 'jpeg'), 'lunbo');
    }

     /**
     * 上传logo
     */
    public function uploadlogo() {
        echo parent::uploadfile(array('jpg', 'gif', 'png', 'jpeg'), 'logo');
    }
}