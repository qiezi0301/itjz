<?php
namespace Admin\Controller;
use Think\Controller;
class IteminfoController extends Controller {
    public function index(){
        $group      = I('group', '', 'trim');

		//实例化数据模型
		$model=M('iteminfo');

        $where = array('group' => $group);
		$tot=$model->where($where)->count();

		//实例化分页
		$page=new \Think\Page($tot, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

		//获取当前页面
		$p=isset($_GET['p'])?$_GET['p']:1;
		$this->data=$model->where($where)->order('id')->page($p)->limit(20)->select();

        $list = M('itemgroup')->select();

		//分配数据
        $this->tot=$tot;
		$this->page=$page->show();
        $this->assign('type', '联动信息列表');
        $this->assign('vlist', $list);
        $this->assign('group', $group);
		$this->display();

    }

    //ajax添加数据
    public function ajax_add(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        if (empty($arr['name'])) {
            $this->error('名称不能为空');
        }
        if (empty($arr['group'])) {
            $this->error('请选择分组！');
        }
        $vo = M('iteminfo')->where(array('group' => $arr['group'], 'value' => $arr['value']))->find();
        if ($vo) {
            $this->error('枚举值已经存在，请重新填写');
        }
        //实例化数据模型
        $model=D('iteminfo');

        //插入数据
        if ($model->create($arr)) {
            $id = $model->add();
            $this->vo=$model->find($id);
            echo $this->fetch();
        } else {
            // echo $model->getError();
            echo "0";
        }        
    }

    //ajax删除单条数据
    public function ajax_del(){
        //接收ID
        $id=$_POST['id'];

        //实例化数据模型
        $model=M('iteminfo');

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
        $model=M('iteminfo');

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
        $model=M('iteminfo');
        $group = I('group', '', 'trim');
        $list  = M('itemgroup')->select();
        //查询数据
        $this->vo=$model->find($id);

        $this->assign('group', $group);
        $this->assign('vlist', $list);
        echo $this->fetch();
    }

    //ajax的更新数据
    public function ajax_save(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        if (empty($arr['name'])) {
            $this->error('属性不能为空');
        }

        //实例化数据模型
        $model=D('iteminfo');

        //更新数据
        if ($model->create($arr)) {
            $id = $model->save();
            $this->vo=$model->find($arr[id]);
            echo $this->fetch();
        } else {
            echo "0";
        }   
    }

    public function ajax_statu(){
        //实例化数据模型
        $model=M('iteminfo');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }

    //批量更新排序
    public function sort(){
        $group = I('get.group', '');
        //exit();
        foreach ($_POST as $k => $v) {
            if ($k == 'key') {
                continue;
            }
            M('iteminfo')->where(array('id' => $k))->setField('sort', $v);
        }
        $this->redirect('Iteminfo/index', array('group' => $group));
    }

}