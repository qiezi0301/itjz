<?php
namespace Admin\Model;
use Think\Model;
class TagModel extends CommonModel{
   protected $_validate = array(
     array('tag_name','','该标题已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
   );


}

