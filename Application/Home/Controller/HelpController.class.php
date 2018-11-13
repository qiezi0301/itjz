<?php
namespace Home\Controller;
use Think\Controller;
class HelpController extends CommonController {

    public function index(){
        $this->display();
    }
    public function points(){
        $this->display();
    }

    public function contact(){
        $ename = I('e', '', 'htmlspecialchars,trim');
        echo $ename;
        $this->display();
    }
    public function job(){
        $this->display();
    }
    public function template(){
        $this->display();
    }
}