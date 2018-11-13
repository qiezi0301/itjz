<?php
namespace Admin\Model;
use Think\Model;
class CommonModel extends Model{

   protected $_auto = array (

         array('created_at','time',1,'function'), // 对update_time字段在更新的时候写入当前时间戳
         array('updated_at','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳
    );
}

