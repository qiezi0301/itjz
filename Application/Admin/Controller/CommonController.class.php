<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {

    //_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
    public function _initialize()
    {
        if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->redirect(MODULE_NAME . '/Login/index');
        }
         C(get_cfg_value()); //添加配置
    }
}