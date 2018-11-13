<?php
namespace Admin\Controller;
use Think\Controller;
class TagController extends Controller {
    public function index(){
        $keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

        $where = array();
        if (!empty($keyword)) {
            $where['tag_name'] = array('LIKE', "%{$keyword}%");
        }

		//实例化数据模型
		$model=M('tag');
		$tot=$model->where($where)->count();

		//实例化分页
		$page=new \Think\Page($tot, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

		//获取当前页面
		$p=isset($_GET['p'])?$_GET['p']:1;
		$this->data=$model->where($where)->order('tag_id desc')->page($p)->limit(20)->select();

		//分配数据
        $this->tot=$tot;
		$this->page=$page->show();
        $this->assign('type', '属性列表');
		$this->display();

    }

    //ajax添加数据
    public function ajax_add(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        if (empty($arr['tag_name'])) {
            $this->error('标签名不能为空');
        }

        $arr['posttime']=time();
        //实例化数据模型
        $model=D('tag');

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
        $model=M('tag');

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
        $model=M('tag');

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
        $model=M('tag');

        //查询数据
        $this->data=$model->find($id);
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
        $model=D('tag');

        //更新数据
        if ($model->create($arr)) {
            $id = $model->save();
            $this->data=$model->find($arr[id]);
            echo $this->fetch();
        } else {
            echo "0";
        }   
    }

    public function ajax_statu(){
        //实例化数据模型
        $model=M('tag');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }

}