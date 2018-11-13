<?php
namespace Home\Controller;

use Common\Lib\Category;

class IndexController extends CommonController {
    public function index(){
    	
    	//轮播广告
        $slider=M('slider')->order("sort desc")->select();

        //取出所有分类
        $classs=M('classs')->select();

    	//取出顶级分类-
        $classsTop=Category::getParentsTop($classs);

        $soft=M('soft');
        foreach ($classsTop as $key => &$value) {
        	$idarr = Category::getChildsId($classs, $value['id'], 1); //所有子类ID
        	$where = array('cid' => array('in', $idarr), 'status' => 1);
            $value['soft']=$soft->where($where)->limit(21)->order("updated_at desc")->select();
            $value['soft2']=$soft->where(array_merge($where,array('isindex' => 1)))->order("updated_at desc")->limit(15)->select();
            //取出推荐首页的分类
            $value['childs']=Category::getChildsIsindex($classs, $value['id']);
        }

        // p($classsTop);
        $this->classs=$classsTop;
        $this->slider=$slider;
        $this->display();
    }
}