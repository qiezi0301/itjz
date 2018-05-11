<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model{
   protected $_validate = array(
     array('username','','该用户已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
   );

   protected $_auto = array (
         array('created_at','time',1,'function'), // 对update_time字段在更新的时候写入当前时间戳
         array('updated_at','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳
    );
}

