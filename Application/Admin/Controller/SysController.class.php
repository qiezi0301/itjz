<?php
namespace Admin\Controller;
use Think\Controller;
class SysController extends CommonController {
	//系统首页
    public function index(){

        if (IS_POST) {
            //获取公共配置文件汇总数据
            $arr = include(CONF_PATH.'sys_config.php');

            //判断是否修改logo
            if ($_POST['LOGO']!=$_POST['oldlogo']) {
                unlink(getThumbPath('lunbo', $_POST['oldlogo']));
                unlink(getThumbPath('lunbo', str_replace("/","/sm_",$_POST['oldlogo'])));
            }

            unset($_POST['oldlogo']);
            unset($_POST['file']);

            //合并数组
            $brr=array_merge($arr,$_POST);

            //遍历字符串写入源文件
            foreach ($brr as $key => $value) {
                $str.="\t'$key' => '$value',\n";
            }

            $newStr="<?php\n return array(\n$str);";
            file_put_contents(CONF_PATH.'sys_config.php',$newStr);
            $this->success('配置成功');
        } else {
          $this->display();
        }
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
    	$model=M('slider');

		$data['name'] = trimall($_POST['name']);
		$data['sort'] = trimall($_POST['sort']);
		$data['url'] = trimall($_POST['url']);
		$data['img'] = $_POST['img'];

    	if ($model->add($data)) {
    		$this->success('添加成功', U('lunbo_list'));
    	} else {
    		$this->error('添加失败');
    	}
    }

    public function lunbo_save(){
        $model=M('slider');

        if ($model->save($_POST)) {
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
    	if (M('slider')->save($_POST)) {
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