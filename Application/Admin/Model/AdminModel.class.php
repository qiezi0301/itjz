<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends CommonModel{
   protected $_auto = array (
         array('created_at','time',1,'function'), // 对update_time字段在更新的时候写入当前时间戳
         array('updated_at','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳
    );

   //M验证
	protected $_validate = array(
        array('username', 'require', '用户名必须填写！'),
        array('username', '', '用户名已经存在！', 0, 'unique', 1),
    );

}

