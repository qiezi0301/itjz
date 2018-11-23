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