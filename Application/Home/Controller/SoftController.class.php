<?php
namespace Home\Controller;
use Common\Lib\Category;

class SoftController extends CommonController {

    public function index(){    	
    	//获取pid
    	$pid=$this->pid;
        $classs=$this->classs;
        $self=$this->self;
        $searchkey=$this->searchkey;
        $isindex     = I('isindex', 0, 'intval'); //类别ID
        $uid= 0;

        //获取搜索关键字
        $keyword = I('keyword', '', 'htmlspecialchars,trim'); //关键字
        $keyword = htmlspecialchars($keyword); //关键字

        if (!$pid && empty($keyword)) {
            $this->error('参数错误');
        }

        // echo $keyword;
        // die();

        if (!empty($keyword)) {
            //保存关键词
            //查询是否存在该关键字
            $mp['keyword'] = $keyword;
            $rs = M('search')->where($mp)->find();
            if(!$rs){
                $r['keyword'] = $keyword;
                $r['status'] = 0;
                $r['addtime'] = time();
                $r['userid'] = $uid;
                $r['ipaddr'] = $_SERVER["REMOTE_ADDR"]; 
                $r['referer'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
                $r['num'] = 1;
                M('search')->add($r);
            }else{
                $r['addtime'] = time();
                $r['userid'] = $uid;
                $r['ipaddr'] = $_SERVER["REMOTE_ADDR"]; 
                $r['referer'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
                $r['num'] = $rs['num']+1;
                M('search')->where($mp)->save($r);
            }

            //搜索条件
            $where['title|description|keywords'] =array('LIKE', '%' . $keyword . '%');
        }

        if ($pid) {         

            $tag = I('tag', '', 'htmlspecialchars,trim');
            $cid     = I('cid', 0, 'intval'); //类别ID
            $scid     = I('scid', 0, 'intval'); //类别ID
            //取出该pid下所有子孙分类
            $cate=M('classs')->where('FIND_IN_SET('.$pid.',path)')->select();

            if ($tag) {
                $where['tag']=array('like', '%'.$tag.'%');
            }else{
                if ($self['property']) {
                    $map['id'] = array('in',$self['property']);
                    //获取属性列表
                    $proplist = M('Property')->where($map)->order('listorder ASC')->select();

                    //获取属性值列表组成二维数组
                    foreach ($proplist as $key => &$value) {
                        $value['valuelist'] = M('PropertyValue')->where("prop_id=".$value['id'])->select();
                    }

                    if ($proplist) {
                        $m = 0;
                        for ($h=1; $h < count($proplist)+1; $h++) { 
                            $propid = intval($_GET['q'.$h]);
                            if ($propid>0) {
                                if ($m ==0 ) {
                                    $where['_string'].='FIND_IN_SET('.$propid.',prop_value)';
                                } else {
                                    $where['_string'].='AND FIND_IN_SET('.$propid.',prop_value)';
                                }
                                $m++;
                            }                    
                         } 
                    }
                }
                //获取$pid下一级分类
                $subcate = Category::getChilds2($cate, $pid); 
            }        

            //判断传递三级id的时候获取cid
            if ($scid) {
                //获取三级分类信息
                $cid = (Category::getSelf($cate, $scid))['pid'];
            }

            if ($cid) {
                //是否有子类
                if (Category::hasChild($cate, $cid)) {
                    //获取二级分类
                    $subcate2 = Category::getChilds2($cate, $cid); //子类
                    if ($scid) {
                        //取出该所有子类ID
                        $idarr = array($scid); 
                    } else {
                        //取出该所有子类ID
                        $idarr = Category::getChildsId($cate, $cid, 1); 
                    }   
                }else{
                    $idarr = Category::getChildsId($cate, $cid, 1); 
                }
            } else {
                $idarr = Category::getChildsId($cate, $pid, 1); 
            }
        
            $where['cid'] = array('IN',$idarr);
        }


        //查询条件
        $where['status'] = 1;

        //判断是否推荐
        if ($isindex) {
            $where['isindex'] = 1;
        }

        $actionName = strtolower(CONTROLLER_NAME);
        $model=M($actionName);

        //分页
        $count=$model->where($where)->count();

        //实例化分页控件
        $page=new \Common\Lib\Page($count, 20);
        $page->rollPage = 10;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $page->setConfig('header','<span class="rows">共 <font class="red">%TOTAL_ROW%</font> 个源码 </span>');

        //获取当前页面
        $limit = $page->firstRow . ',' . $page->listRows;

        //排序
        $order = isset($_GET['order'])?$_GET['order']:0;
        switch($order){
            case '1':
                $order = 'id DESC';
                break;
            case '2':
                $order = 'click DESC';
                break;
            case '3':
                $order = 'downnum DESC';
                break;
            default:
                $order = 'id DESC';
        }
        $soft=$model->where($where)->order($order)->limit($limit)->select();

        //分配数据
        $this->page=$page->show();
        $this->assign('cid', $cid);
        $this->assign('scid', $scid);
        $this->assign('tag', $tag);
        $this->assign('searchkey', $searchkey);
        $this->assign('subcate', $subcate);
        $this->assign('subcate2', $subcate2);
        $this->assign('soft',$soft);
        $this->assign('title',$self['cname']);
        $this->assign('proplist',$proplist);
        $this->assign('classstop',$self);
    	$this->assign('keyword',$keyword);
        $this->display();
    }

    //详情
    public function detail(){
        //顶级分类列表
        $classs=$this->classs;

        //获取顶级分类信息
        $self=$this->self;

        //获取id
    	$id=I('get.id', 0, 'intval');

        if ($id == 0) {
            $this->error('参数错误');
        }

        $actionName = strtolower(CONTROLLER_NAME);
        $model=M($actionName);        
        
        $model->where("id = %d",$id)->setInc('click',1);//自动添加点击数
        $data=$model->find($id);

        //取出该pid下所有子孙分类
        $cate=M('classs')->select();

        //根据$data['cid']获取父级分类
        $pcate = Category::getParents($cate, $data['cid'],1); 

        //获取pid
        foreach ($pcate as $vcate) {
            if ($vcate['pid'] == 0) {
                $pid=$vcate['id'];                
            }
        }

        //获取当前顶级分类信息
        $self=Category::getSelf($classs,$pid);

        /*内容属性*/
        //根据$data['prop_value']获取属性值

        if ($self['property']) {
            //获取属性列表
            $proplist = M('Property')->field(array('id','name'))->where(array('id'=>array('in',$self['property'])))->order('listorder ASC')->select();
            $cname = $self['info'];
            //获取属性值列表组成二维数组
            foreach ($proplist as &$propvalue) {
                $propvalue['name'] = str_replace(trim($cname), '', $propvalue['name']);
                $propvalue['value'] = M('PropertyValue')->field(array('id','prop_id','vname'))->where(array('prop_id'=>$propvalue['id'],'id'=>array('in',$data['prop_value'])))->select();
            }
        }

        if ($data['tag']) {
            /*右侧列表*/
            //相关内容列表
            $tag = array_filter(explode(',',$data['tag']));//去除空数组'',0,再使用sort()重建索引

            //相同字段不同查询条件
            $tagarr = array();
            foreach ($tag as $key => $value) {
                $tagarr[] = array('like', '%'.$value.'%');
            }
            $tagarr[] = 'or';
            $where['tag'] = $tagarr;
            $where['id'] = array('neq',$id);//排除自己
            $field = array('id','title','created_at','updated_at','isindex');
            $like_softlist=$model->field($field)->where($where)->order('click DESC')->limit(15)->select();
        }
        // $tag = array_filter(explode(',',$data['tag']));
        // p($tag);
        // p($data);

        //推荐内容列表
        $map['id'] = array('neq',$id);//排除自己
        $map['isindex'] = array('eq',1);
        $recommed_softlist=$model->field($field)->where($map)->order('click DESC')->limit(15)->select();

        //最新内容列表
        $new_softlist=$model->field($field)->where("id <> $id")->order('updated_at DESC, created_at DESC')->limit(15)->select();

        //标签云
        //取出所有PID子类id
        $idarr = Category::getChildsId($cate, $pid, 1); 
        $map1['tag_status'] = 1;
        $map1['typeid'] = array('in',$idarr);
        $tags = M('tag')->where($map1)->order('tag_click DESC')->limit(30)->select();

        /*上一篇*/
        //下一条记录

        $pre_data = $model->field($field)->where(array('status' => 1, 'cid' => array('in',$idarr), 'id' => array('lt',$data['id'])))->order('id desc')->find();
        $next_data = $model->field($field)->where(array('status' => 1, 'cid' => array('in',$idarr), 'id' => array('gt',$data['id'])))->order('id ASC')->find();

        $commentmodel = D('CommentView');
        $condition = array('postid' => $id,'pid' => 0);
        //评论内容
        $count = M('comment')->where($condition)->count();

        //实例化分页控件
        $page=new \Common\Lib\CommentPage($count, 10);
        $page->rollPage = 10;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $page->setConfig('header','<span class="rows">共 <font class="red">%TOTAL_ROW%</font> 个评论 </span>');

        //获取当前页面
        $limit = $page->firstRow . ',' . $page->listRows;

        $comments = M("comment")->field("id,userid,content,posttime,pid")->where($condition)->order("id DESC")->limit($limit)->select(); 
        foreach ($comments as $k => $v) { 
            $comments[$k]['sub'] = M("comment")->field("id,userid,content,pid")->where("postid = " . $id . " AND pid = " . $v['id'] . "")->order("id ASC")->select(); 
        } 
        

        $this->assign('tags', $tags);
        //分配数据
        $this->assign('data',$data);
        $this->assign('pid',$pid);
        $this->assign('comments',$comments);
        $this->assign('count',$count);
        $this->assign('page',$page->show());
        $this->assign('description',$data['description']);
        $this->assign('title',$data['title']);
        $this->assign('pcate',$pcate);
        $this->assign('tag',$tag);
        $this->assign('proplist',$proplist);
        $this->assign('like_softlist',$like_softlist);
        $this->assign('recommed_softlist',$recommed_softlist);
        $this->assign('new_softlist',$new_softlist);
        $this->assign('next_data',$next_data);
    	$this->assign('pre_data',$pre_data);
        $this->display();
    } 

    public function getdemo(){
        $id = I('id', 0, 'intval');
        if ($id == 0) {
            $this->error('参数错误');
        }
        $demo = M('soft')->where(array('status' => 1, 'id' => $id))->getField('demo');
        if(!$demo){
            $demo = '/';
        }
        redirect($demo);
    }

    public function comments() { 
        $id = I("get.id", 0, 'int'); 
        $mtype = I("get.mtype", 1, 'int'); 
        $p = I("get.p", 1, "int"); 
        $title = I('title', '');
        $totalnum = I("get.totalnum", 1, "int"); 
        $start = 5 * ($p - 1); 
        $sql = "postid = " . $id . " AND pid = 0"; 

        //实例化分页控件
        $page=new \Common\Lib\CommentPage($totalnum, 10);
        $page->rollPage = 10;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $page->setConfig('header','<span class="rows">共 <font class="red">%TOTAL_ROW%</font> 个评论 </span>');

        //获取当前页面
        $limit = $page->firstRow . ',' . $page->listRows;

        $comments = M("comment")->field("id,userid,content,posttime")->where($sql)->order("id DESC")->limit($limit)->select(); 
        // echo M("comment")->getlastsql(); 
        foreach ($comments as $k => $v) { 
            $comments[$k]['sub'] = M("comment")->field("id,userid,content,pid")->where("postid = " . $id . " AND pid = " . $v['id'] . "")->order("id ASC")->select(); 
        } 

        // p($comments);
        $this->assign("id", $id); 
        $this->assign("mtype", $mtype); 
        $this->assign("comments", $comments); 
        $this->assign("title", $title); 
        $this->assign("page", $page->show()); 
        $this->assign("comments_num", $totalnum - ($p - 1) * 10); 
        echo $this->fetch();
        // $this->display(); 
    }
}