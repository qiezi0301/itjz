<?php
namespace Admin\Controller;
use Think\Controller;
class TypesController extends Controller {
    public function index(){

		//实例化数据模型
		$model=M('classs');
        $tot=$model->count();

        //实例化分页
        $page=new \Think\Page($tot, 20);

        //获取当前页面
        $p=isset($_GET['p'])?$_GET['p']:1;
		//查询数据
		$this->data=$model->field(" * ,concat(path,id) pp")->page($p)->limit(20)->order('pp asc')->select();

        $proplist = M('property')->select();
        $this->assign('proplist', $proplist);

        $this->page=$page->show();
        $this->display();
    }

    //添加操作
    public function insert(){
    	//实例化数据模型
    	$model=D('classs');

    	if ($model->add($_POST)) {
    		$this->success('添加成功');
    	}else {
    		$this->error('添加失败');
    	}
    }

    //ajax添加数据
    public function ajax_add(){
        //数据格式化
        //先把实体转化
        $str = str_replace('&amp;','&',I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);
        //实例化数据模型
        $model=D('classs');

        //插入数据
        if ($model->create($arr)) {
            $id = $model->add();
            $this->data=$model->find($id);
            echo $this->fetch();
        }else{
            echo "0";
        }

    }

    //ajax的修改数据
    public function ajax_edit(){
        $id=$_POST['id'];

        //实例化数据模型
        $model=D('classs');

        $proplist = M('property')->select();
        $this->assign('proplist', $proplist);

        //查询数据
        $this->data=$model->find($id);
        echo $this->fetch();
    }

    //ajax更新数据
    public function ajax_save(){
        //数据格式化
        //先把实体转化
        $str = str_replace('&amp;','&',I('post.str'));

        //使用函数格式化字符串为数组
        parse_str($str, $arr);

        $arr['property'] = implode(',',$arr['property']);

        //实例化数据模型
        $model=D('classs');

        //更新数据
        if ($model->create($arr)) {
            $model->save();
            $this->vo=$model->find($arr[id]);
            echo $this->fetch();
        } else {
            echo "0";
        }
    }

    //ajax的删除数据
    public function del(){
        $id=$_POST['id'];

        //实例化数据模型
        $model=D('classs');

        //查询数据
        $this->data=$model->find($id);
        echo $this->fetch();
    }

    //删除操作
    public function ajax_del(){
        $id=$_POST['id'];

        //实例化数据模型
        $model=D('classs');
        $list = $model->where("pid in ($id)")->count();
        if ($list > 0) {
            echo "0";
        }else{
            if ($model->delete($id)) {
                # code...
                echo "1";
            } else {
                # code...
                echo "2";
            }            
        }
    }

    public function ajax_isindex(){
        //实例化数据模型
        $model=M('classs');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }
}