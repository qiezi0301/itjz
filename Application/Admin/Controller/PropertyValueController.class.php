<?php
namespace Admin\Controller;
use Think\Controller;
class PropertyValueController extends Controller {
    public function index(){

		//实例化数据模型
		$model=M('propertyValue');
		$tot=$model->count();

		//实例化分页
		$page=new \Think\Page($tot, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

		//获取当前页面
		$p=isset($_GET['p'])?$_GET['p']:1;
		$this->data=$model->order('id desc')->page($p)->limit(20)->select();
        $proplist = M('property')->select();
        
		//分配数据
        $this->tot=$tot;
		$this->page=$page->show();
        $this->assign('type', '属性值列表');

        $this->assign('proplist', $proplist);
		$this->display();

    }

    //ajax添加数据
    public function ajax_add(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        if (empty($arr['vname'])) {
            $this->error('属性值名称不能为空');
        }
        $arr['vname']    = trim($arr['vname']);

        $arr['addtime']=time();
        //实例化数据模型
        $model=D('propertyValue');

        //插入数据
        if ($model->create($arr)) {
            $id = $model->add();
            $this->data=$model->find($id);
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
        $model=M('propertyValue');

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
        $model=M('propertyValue');

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
        $model=M('propertyValue');

        $proplist = M('property')->select();

        //查询数据
        $this->data=$model->find($id);
        $this->assign('proplist', $proplist);
        echo $this->fetch();
    }

    //ajax的更新数据
    public function ajax_save(){
        //数据格式化
        //先把实体转换
        $str = str_replace('&amp;', '&', I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        if (empty($arr['vname'])) {
            $this->error('属性值名称不能为空');
        }

        //实例化数据模型
        $model=D('propertyValue');

        //更新数据
        if ($model->create($arr)) {
            $id = $model->save();
            $this->data=$model->find($arr[id]);
            echo $this->fetch();
        } else {
            echo "0";
        }   
    }


}