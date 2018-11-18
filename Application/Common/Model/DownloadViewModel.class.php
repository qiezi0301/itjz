<?php

namespace Common\Model;

use Think\Model\ViewModel;

//视图模型
class DownloadViewModel extends ViewModel
{

    protected $viewFields = array(
        'download' => array('*',
            '_type' => 'LEFT',
        ),
        'soft'   => array(
            'title'      => 'title',
            'tag'      => 'tag',
            'cid'      => 'cid',
            '_on'       => 'download.tid = soft.id', //_on 对应上面LEFT关联条件            
            '_type'     => 'LEFT',
        ),
        'classs'   => array(
            'cname'      => 'catename',
            'path'      => 'path',
            '_on'       => 'soft.cid = classs.id', //_on 对应上面LEFT关联条件
        ),

    );
}
