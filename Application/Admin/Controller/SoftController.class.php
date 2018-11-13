<?php
namespace Admin\Controller;
use Common\Lib\Category;

class SoftController extends CommonController {
    public function index(){
        $keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

        $where = array();
        if (!empty($keyword)) {
            $where['title'] = array('LIKE', "%{$keyword}%");
        }

    	$pid     = I('get.pid', 0, 'intval'); //类别ID
        $type = M('classs')->find($pid);

        //取出所有分类
        $classs=M('classs')->field(" * ,concat(path,id) pp")->order('pp asc')->select();

        $idarr = Category::getChildsId($classs, $pid, 1); //所有子类ID
        //$where = array('soft.status' => 0, 'cid' => $pid);
        $where['cid'] = array('in', $idarr);
        $where['status'] = array('gt',0);
        
        $actionName = strtolower(CONTROLLER_NAME);
        $model=M($actionName);

        //分页
        $tot=$model->where($where)->count();

        //实例化分页
        $page=new \Think\Page($tot, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        //获取当前页面
        $p=isset($_GET['p'])?$_GET['p']:1;

        // p($where);

        $this->data=$model->where($where)->order('id desc')->page($p)->limit(20)->select();

        //分配数据

        $this->page=$page->show();

        $this->assign('type', $type['cname']);
    	$this->assign('pid', $pid);
        $this->display();
    }


    //软件添加页面
    public function add(){
        //当前控制器名称
        $actionName = strtolower(CONTROLLER_NAME);
    	$pid     = I('pid', 0, 'intval'); //类别ID

        if (IS_POST) {
            $this->addPost();
            exit();
        }


        $where = array('pid' => $pid);
        //取出所有分类
        $allclasss=M('classs')->field(" * ,concat(path,id) pp")->order('pp asc')->select();
        $classs = Category::getChilds($allclasss, $pid);

        $data = Category::getChilds2($classs, $pid);
        $this->assign('data', $data);
        $this->assign('class_son', Category::hasChild($classs, $classs[0][id])); //是否包含子类
    	$this->assign('pid', $pid);
        $this->assign('flagtypelist', get_item('flagtype')); //文档属性
        $this->assign('type', '添加软件下载');
        $this->display();
    }

    //添加操作
    public function addPost(){
        $data                = I('post.'); 
        $data['cid']         = I('cid', 0, 'intval');
        $data['title']       = I('title', '', 'htmlspecialchars,rtrim');
        $data['keywords']    = trim($data['keywords']);//移除字符串两侧的空格
        $data['content']     = I('content', '', '');
        $data['downlink']    = str_replace("\n", '|||', I('downlink', ''));
        $data['created_at'] = I('created_at', time(), 'strtotime');
        $data['prop_value']  = implode(',', I('prop_value', array()));//把数组元素组合为字符串
        $data['updated_at']  = time();
        $data['aid']         = session(C('USER_AUTH_KEY')) ? session(C('USER_AUTH_KEY')) : 0;

        //给图片自动添加alt和title
        $data['content'] = imgalt($data['title'],$data['content']);

        $actionName = strtolower(CONTROLLER_NAME);//把所有字符转换为小写
        $data['tag']    = I('tag', '', 'htmlspecialchars,trim');

        //保存标签
        if($data['tag']){
            $tags = explode(',',$data['tag']);//把字符串打散为数组
            if(count($tags) > 5){
                $this->error('最多5个标签，用空格分隔！');
            }
            if(count($tags) > 0){
                foreach($tags as $k=>$val){
                    $mp['tag_name'] = $val;
                    //$mp['tablename'] = $actionName;
                    $rs = M('tag')->where($mp)->find();
                    if(!$rs){
                        $r['tag_name'] = $val;
                        $r['typeid'] = $data['cid'];
                        $r['tag_sort'] = 1;
                        $r['tag_status'] = 1;
                        $r['posttime'] = time();
                        $r['tag_click'] = 1;
                        $r['tablename'] = $actionName;
                        M('tag')->add($r);
                    }else{
                        $r['tag_click'] = $rs['tag_click']+1;
                        $r['tablename'] = $actionName;
                        M('tag')->where($mp)->save($r);
                        //M('tag')->where($mp)->setInc('tag_click');
                    }
                }
            }
        }

        $pid   = intval($data['pid']);

        //整理图片集
        $pic   = $data['litpic'];

        if (empty($data['description'])) {
            $data['description'] = str2sub(strip_tags($data['content']), 120);
        }
        
        $pictureurls_arr = array();
        $imgPostUrls     = isset($data['pictureurls']) ? $data['pictureurls'] : '';
        if (is_array($imgPostUrls)) {
            foreach ($imgPostUrls as $k => $v) {
                //$pictureurls_arr[] = $v . '$$$' . '$$$';
                $picarry = explode('$$$',$v);
                if(!$picarry[1]){
                    $pictureurls_arr[] = $picarry[0].'$$$'.$data['title'].'('.$k.')';
                }else{
                    $pictureurls_arr[] = $v;
                }
                //缩略图
                if(!$data['litpic']){
                    if ($k == 0) {
                        $imgtbSize = explode(',', C('CFG_IMGTHUMB_SIZE')); //配置缩略图第一个参数
                        $imgTSize  = explode('X', $imgtbSize[0]);
                        //$picarry = explode('$$$',$v);
                        if (!empty($imgTSize)) {
                            $pic = get_picture($picarry[0], $imgTSize[0], $imgTSize[1]);
                        } else {
                            $pic = $picarry[0];
                        }
                    }
                }
            }
        }

        $data['pictureurls'] = join('|||', $pictureurls_arr);
        $data['litpic']      = isset($pic) ? $pic : '';


        $flags = I('flags', array(), 'intval');
        //图片标志
        if (!empty($pic) && !in_array(B_PIC, $flags)) {
            $flags[] = B_PIC;
        }
        $data['flag'] = 0;
        foreach ($flags as $v) {
            $data['flag'] += $v;
        }

        //保存
        if ($id = M('soft')->add($data)) {

            $firstpic  = '';
            $attid_arr = get_att_content($data['content'], $firstpic, empty($pic)); //内容中的图片

            //更新上传附件表
            if (!empty($pic)) {
                if (!empty($imgPostUrls)) {
                    $attid_arr = array_merge($attid_arr, get_att_attachment($pic, true), get_att_attachment($imgPostUrls));
                }else{
                    $attid_arr = array_merge($attid_arr, get_att_attachment($pic, true));
                }
            } else if (!empty($firstpic)) {
                //更新表字段
                $updata = array('id' => $id, 'litpic' => $firstpic);
                if (!in_array(B_PIC, $flags)) {
                    $updata['flag'] = array('exp', 'flag+' . B_PIC);
                }
                M('soft')->save($updata);
            }

            //attachment index入库
            insert_att_index($attid_arr, $id, $modelid);

            //更新静态缓存
            del_cache_html('List/index_' . $data['cid'], false, 'list:index');
            del_cache_html('Index_index', false, 'index:index');

            $this->success('添加成功', U('Soft/index', array('pid' => $pid)));
        } else {
            $this->error('添加失败');
        }
    }

    //ajax联动子分类
    public function ajax_select(){
        $pid=$_POST['id'];
        $model=M('classs');
        $data=$model->where("pid=%d",$pid)->select();
        
        $this->ajaxReturn($data);
    }

    //编辑
    public function edit(){
        //当前控制器名称
        $id         = I('id', 0, 'intval');
        $actionName = strtolower(CONTROLLER_NAME);
        $pid        = I('pid', 0, 'intval');

        if (IS_POST) {
            $this->editPost();
            exit();
        }

        $vo              = M($actionName)->find($id);
        // $vo['content']   = htmlspecialchars($vo['content']);

        $pictureurls = array();
        if (!empty($vo['pictureurls'])) {
            $temparr = explode('|||', $vo['pictureurls']);
            foreach ($temparr as $key => $v) {
                $temparr2      = explode('$$$', $v);
                $pictureurls[] = array('url' => '' . $temparr2[0], 'alt' => '' . $temparr2[1]);
            }
        }
        $vo['pictureurls'] = $pictureurls;

        //取出所有分类
        $allclasss=M('classs')->field(" * ,concat(path,id) pp")->order('pp asc')->select();
        $classs = Category::getChilds($allclasss, $pid);
        $cate = Category::getChilds2($classs, $pid);
        $cateSelf = Category::getSelf($classs, $vo['cid']);

        $this->assign('cate', $cate);
        $this->assign('cateSelf', $cateSelf);
        $this->assign('class_son', Category::hasChild($classs, $classs[0][id])); //是否包含子类
        $this->assign('pid', $pid);
        $this->assign('vo', $vo);
        $this->assign('flagtypelist', get_item('flagtype')); //文档属性
        $this->assign('type', '修改软件下载');
        $this->display();
    }

    //编辑处理
    public function editPost()
    {
        $data                = I('post.');
        $id                  = $data['id']                  = I('id', 0, 'intval');
        $data['cid']         = I('cid', 0, 'intval');
        $data['title']       = I('title', '', 'htmlspecialchars,rtrim');
        $data['keywords']    = trim($data['keywords']);
        $data['content']     = I('content', '', '');
        $data['downlink']    = str_replace("\n", '|||', I('downlink', ''));
        $data['created_at']  = I('created_at', time(), 'strtotime');
        $data['updated_at']  = time();
        $data['prop_value']  = implode(',', I('prop_value', array()));
        //$data['updatetime']  = I('updatetime', time(), 'strtotime');
        //给图片自动添加alt和title
        $data['content'] = imgalt($data['title'],$data['content']);

        $actionName = strtolower(CONTROLLER_NAME);
        $data['tag']    = I('tag', '', 'htmlspecialchars,trim');
        if($data['tag']){
            $tags = explode(',',$data['tag']);
            if(count($tags) > 5){
                $this->error('最多5个标签，用空格分隔！');
            }
            if(count($tags) > 0){
                foreach($tags as $k=>$val){
                    $mp['tag_name'] = $val;
                    //$mp['tablename'] = $actionName;
                    $rs = M('tag')->where($mp)->find();
                    if(!$rs){
                        $r['tag_name'] = $val;
                        $r['typeid'] = $data['cid'];
                        $r['tag_sort'] = 1;
                        $r['tag_status'] = 1;
                        $r['posttime'] = time();
                        $r['tag_click'] = 1;
                        $r['tablename'] = $actionName;
                        M('tag')->add($r);
                    }else{
                        $r['tag_click'] = $rs['tag_click']+1;
                        $r['tablename'] = $actionName;
                        M('tag')->where($mp)->save($r);
                        //M('tag')->where($mp)->setInc('tag_click');
                    }
                }
            }
        }

        $pid   = intval($data['pid']);
        $flags = I('flags', array(), 'intval');
        $pic   = $data['litpic'];

        if (empty($data['title'])) {
            $this->error('标题不能为空');
        }
        if (!$data['cid']) {
            $this->error('请选择栏目');
        }
        // $pid = $data['cid']; //转到自己的栏目

        if (empty($data['description'])) {
            $data['description'] = str2sub(strip_tags($data['content']), 120);
        }
        
        $pictureurls_arr = array();
        $imgPostUrls     = isset($data['pictureurls']) ? $data['pictureurls'] : '';



        if (false !== M('soft')->save($data)) {
            //del
            // M('attachmentindex')->where(array('arcid' => $id, 'modelid' => $modelid))->delete();

            $firstpic  = '';
            $attid_arr = get_att_content($data['content'], $firstpic, empty($pic)); //内容中的图片

            //更新上传附件表
            if (!empty($pic)) {
                //$attid_arr = array_merge($attid_arr, get_att_attachment($pic, true));
                if (!empty($imgPostUrls)) {
                    $attid_arr = array_merge($attid_arr, get_att_attachment($pic, true), get_att_attachment($imgPostUrls));
                }else{
                    $attid_arr = array_merge($attid_arr, get_att_attachment($pic, true));
                }
            } else if (!empty($firstpic)) {
                //更新表字段
                $updata = array('id' => $id, 'litpic' => $firstpic);
                if (!in_array(B_PIC, $flags)) {
                    $updata['flag'] = array('exp', 'flag+' . B_PIC);
                }
                M('soft')->save($updata);
            }

            //attachment index入库
            insert_att_index($attid_arr, $id, $modelid);

            //更新静态缓存
            del_cache_html('List/index_' . $data['cid'] . '_', false, 'list:index');
            del_cache_html('List/index_' . $selfCate['ename'], false, 'list:index'); //还有只有名称
            del_cache_html('Show/index_*_' . $id, false, 'show:index'); //不太精确，会删除其他模块同id文档
            
            $p = I('p', 1, 'intval');
            $this->success('修改成功', U('Soft/index', array('pid' => $pid,'p'=>$p)));
        } else {

            $this->error('修改失败');
        }
    }

    //ajax请求修改demo地址
    public function ajax_demos(){

        //实例化数据模型
        $model=D('soft');
        if ($model->create($_POST,2)) {
            $model->save();
            echo "1";
        } else {
            echo "0";
        }
    }

    //ajax请求修改down地址
    public function ajax_downs(){
        //实例化数据模型
        $model=D('soft');
        if ($model->create($_POST,2)) {
            $model->save();
            echo "1";
        } else {
            echo "0";
        }
    }

    //回收站文章列表
    public function trach()
    {
        $pid     = I('pid', 0, 'intval'); //类别ID

        //取出所有分类
        $classs=M('classs')->field(" * ,concat(path,id) pp")->order('pp asc')->select();

        $idarr = Category::getChildsId($classs, $pid, 1); //所有子类ID
        //$where = array('soft.status' => 0, 'cid' => $pid);
        $where = array('cid' => array('in', $idarr), 'status' => 0);
        
        $actionName = strtolower(CONTROLLER_NAME);
        $model=M($actionName);

        //分页
        $count=$model->where($where)->count();

        //实例化分页
        $page=new \Think\Page($count, 20);
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        //获取当前页面
        $p=isset($_GET['p'])?$_GET['p']:1;

        $this->data=$model->where($where)->order('id desc')->page($p)->limit(20)->select();

        //分配数据
        $this->page=$page->show();
        $this->assign('pid', $pid);
        $this->assign('subcate', '');
        $this->assign('type', '软件回收站');
        $this->display('index');
    }

    //删除软件到回收站
    public function del()
    {

        $id        = I('id', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->delBatch();
            return;
        }

        $pid = I('pid', 0, 'intval'); //单纯的GET没问题

        if (false !== M('soft')->where(array('id' => $id))->setField('status', 0)) {

            del_cache_html('Show/index_*_' . $id . '.', false, 'show:index');
            $this->success('删除成功', U('Soft/index', array('pid' => $pid)));

        } else {
            $this->error('删除失败');
        }
    }

    //批量删除到回收站
    public function delBatch()
    {

        $idArr = I('key', 0, 'intval');
        $pid   = I('get.pid', 0, 'intval');

        if (!is_array($idArr)) {
            $this->error('请选择要删除的项');
        }

        if (false !== M('soft')->where(array('id' => array('in', $idArr)))->setField('status', 0)) {

            //更新静态缓存
            foreach ($idArr as $v) {
                del_cache_html('Show/index_*_' . $v . '.', false, 'show:index');
            }
            $this->success('批量删除成功', U('Soft/index', array('pid' => $pid)));

        } else {
            $this->error('批量删除文失败');
        }
    }

    //还原
    public function restore(){
        $id = I('id', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');

        //批量删除
        if ($batchFlag) {
            $this->restoreBatch();
            return;
        }

        $pid = I('get.pid', 0, 'intval');

        if (M('soft')->where(array('id' => $id))->setField('status', 1)) {
            $this->success('还原成功', U('Soft/trach', array('pid' => $pid)));
        }else{
            $this->error('还原失败');
        }
    }

    //批量还原
    public function restoreBatch(){
        $idArr = I('key', 0, 'intval');
        $pid = I('get.pid', 0, 'intval');

        if (!is_array($idArr)) {             
            $this->error('请选择要还原的项');
        }

        if (M('soft')->where(array('id' => array('in', $idArr)))->setField('status', 1)) {
            $this->success('还原成功', U('trach', array('pid' => $pid)));
        } else {
            $this->error('还原失败');
        }
     
    }

    //彻底删除
    public function clear(){
        $id = I('id', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');

        //批量删除
        if ($batchFlag) {
            $this->clearBatch();
            return;
        }

        $pid = I('get.pid', 0, 'intval');

        if (M('soft')->delete($id)) {
            $this->success('彻底删除成功',U('trach', array('pid' => $pid)));
        } else {
            $this->error('彻底删除失败');
        }        
    }

    //批量彻底删除
    public function clearBatch(){
        $idArr = I('key', 0, 'intval');
        $pid = I('get.pid', 0, 'intval');
        if (!is_array($idArr)) {
            $this->error('请选择邀彻底删除的项');
        }

        $where = array('id' => array('in', $idArr));
        if (M('soft')->where($where)->delete()) {
           $this->success('彻底删除成功',U('trach', array('pid' => $pid)));
        } else {
            $this->error('彻底删除失败');
        }
        
    }

    public function ajax_isindex(){
        //实例化数据模型
        $model=M('soft');
        if ($model->save($_POST)) {
            # code...
            echo "1";
        }else {
            # code...
            echo "0";
        }
    }
}