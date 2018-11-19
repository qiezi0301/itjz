<?php
/**
 * 数组打印方法
 * @param  [type] $array [数组]
 * @return [type]        [description]
 */
function p($array){
    dump($array, 1, '<pre>', 0);
}

//去掉全部空格
function trimall($str) {
    if(is_array($str)){
        return array_map('trimall', $str);
    }
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str);
 }

 /*处理访问图片路径*/
function getImagePath($dir, $data){
	return __ROOT__ . '/Uploads/' . $dir . '/' . $data;
}

/*处理图片物理路径*/
function getThumbPath($dir, $data){
	return './Uploads/' . $dir . '/' . $data;
}

/*处理访问缩略图路径*/
function getThumbImg($dir, $data){
	$data= str_replace('/','/sm_',$data);
	return __ROOT__ . '/Uploads/' . $dir . '/' . $data;
}

/**
 * 获取对应组的联动列表
 * @param string $group 联动组名
 * @param integer $value 联动值
 * @return array
 */
function get_item($group = 'animal', $update = 0)
{

    //S方法的缓存名都带's'
    $itme_arr = S('sItem_' . $group);
    if ($update || !$itme_arr) {
        $itme_arr = array();
        $temp     = M('iteminfo')->where(array('group' => $group))->order('sort,id')->select();
        foreach ($temp as $key => $v) {
            $itme_arr[$v['value']] = $v['name'];

        }

        //S(缓存名称,缓存值,缓存有效时间[秒]);
        S('sItem_' . $group, $itme_arr, 48 * 60 * 60);
    }
    return $itme_arr;
}

/**
 * 获取上传最大值(字节数), KB转字节
 * @param integer $size 默认大小值
 * @param string $cfg 配置项值
 * @return integer
 */
function get_upload_maxsize($size = 2048, $cfg = 'CFG_UPLOAD_MAXSIZE')
{
    $maxsize = get_cfg_value($cfg);
    if (empty($maxsize)) {
        $maxsize = $size;
    }
    return $maxsize * 1024;
}

/**
 * 返回配置项数组或对应值(快速缓存)
 * @param string|integer $key 标识名,标识为空则返回所有配置项数组
 * @param string|integer $name 缓存名称
 * @return mixed
 */
function get_cfg_value($key = '', $name = 'site')
{
    if (empty($name)) {
        return array();
    }
    $sname = 'config/' . $name;
    $array = F($sname);
    if (!$array) {
        $data  = M('config')->field('name,value,typeid')->select();
        $array = array();
        if ($data) {
            foreach ($data as $value) {
                $array[$value['name']] = $value['value'];
            }
        }
        F($sname, $array);
    }
    if ($key == '') {
        return $array;
    } else {
        $value = isset($array[$key]) ? $array[$key] : '';
        return $value;
    }

}

/**
 * 返回从根目录开始的地址
 * @param string $path 子目录地址
 * @param boolean $domain 是否显示域名
 * @param string $path_root 网站根目录地址
 * @return mixed
 */
function get_url_path($path, $domain = false, $path_root = __ROOT__)
{

    $baseurl = ''; //域名地址
    if ($domain) {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $baseurl = 'http://' . $_SERVER['HTTP_HOST'];
        } else {
            $baseurl = rtrim("http://" . $_SERVER['SERVER_NAME'], '/');
        }

    }

    $path_sub = explode('/', $path);

    if ($path_sub[0] == '') {
        $baseurl = $baseurl . implode('/', $path_sub);
    } elseif (empty($path_root)) {
        foreach ($path_sub as $k => $v) {
            if ($v == '.' || $v == '..') {
                unset($path_sub[$k]);
            }
        }
        $baseurl .= '/' . implode('/', $path_sub);
    } else {
        $path_root_tmp   = explode('/', $path_root);
        $path_root_count = count($path_root_tmp);
        foreach ($path_sub as $k => $v) {
            if ($v == '.') {
                unset($path_sub[$k]);
            }
            if ($v == '..') {
                if ($path_root_count > 0) {
                    unset($path_root_tmp[$path_root_count - 1]);
                    --$path_root_count;
                }
                unset($path_sub[$k]);
            }
        }
        $baseurl .= implode('/', $path_root_tmp) . '/' . implode('/', $path_sub);
    }
    $baseurl = rtrim($baseurl, '/') . '/';
    return $baseurl;
}

/**
 * 获取指定长宽的图片[尺寸见后台设置]
 * @param string $str 图片地址
 * @param integer $width 长度
 * @param integer $height 高度
 * @param boolean $rnd 是否显示随机码
 * @return string
 */
function get_picture($str, $width = 0, $height = 0, $rnd = false)
{

    //$ext = end(explode('.', $str));
    $ext      = 'jpg'; //原文件后缀
    $ext_dest = 'jpg'; //生成缩略图格式
    $height   = $height == 0 ? '' : $height;
    if (!empty($str)) {
        $str = preg_replace('/!(\d+)X(\d+)\.' . $ext_dest . '$/i', '', $str); //清除缩略图的!200X200.jpg后缀

        $ext = explode('.', $str);
        $ext = end($ext);
    }
    if (empty($ext) || !in_array(strtolower($ext), array('jpg', 'gif', 'png', 'jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str      = __ROOT__ . '/uploads/system/nopic.png';
        $ext      = 'png';
        $ext_dest = 'png';
        $width    = 0;
    }
    if ($width == 0) {
        return $str;
    }

    $rndstr = $rnd ? '?random=' . time() : '';
    return $str . '!' . $width . 'X' . $height . '.' . $ext_dest . $rndstr;
}

//获取栏目属性
function get_catProperty($id = 0){
    if($id){
        $map['id'] = $id;
        $property = M('classs')->where($map)->getField('property');
    }else{
        $property = '';
    }
    return $property;
}

//获取属性值
function get_propvalue($id = 0){
    $valuelist = '';
    if($id){
        $map['prop_id'] = $id;
        $valuelist = M('PropertyValue')->where($map)->select();
    }
    return $valuelist;
}

/**
 * 获取点击次数(同时点击数增加1)
 * @param integer $id 文档id
 * @param string $tablename 表名
 * @return integer
 */
function get_click($id, $tablename)
{

    $id = intval($id);
    if (empty($id) || empty($tablename)) {
        return '--';
    }
    $num = M($tablename)->where(array('id' => $id))->getField('click');
    M($tablename)->where(array('id' => $id))->setInc('click');
    return "$num";
}


/*检查复选框是否被选中*/
function is_checked($str = '', $id = 0){
    if(empty($str) || empty($id)){
        return false;
    }
    
    $arry = explode(",",$str);
    
    if(is_array($arry)){
        $res = in_array($id, $arry);
    }else{
        if($arry == $id){
            $res = true;
        }else{
            $res = false;
        }
    }

    return $res;
}

//添加内容时自动给图片增加alt和title
function imgalt($title,$value){
    $htmls=$value;  
    $pattern = "/<img[^>]+>/";
    preg_match_all($pattern, $htmls, $matches);
    
    for($i=0; $i<=count($matches[0]); $i++) {  
        preg_match_all("/alt=\".+?\"/",$matches[0][$i],$altimg);
        preg_match_all("/title=\".+?\"/",$matches[0][$i],$titleimg);
        $t_alt=count($altimg[0]);
        $t_title=count($titleimg[0]); 
        if($t_alt==0 && $t_title==0){  
            $htmls=str_replace("<img","<img title='".$title."' alt='".$title."'",$htmls);
        }elseif($t_alt>0 && $t_title==0){
            $htmls=str_replace("<img","<img title='".$title."'",$htmls);
        }elseif($t_alt==0 && $t_title>0){
            $htmls=str_replace("<img","<img alt='".$title."'",$htmls);
        }
        
    } 
    
    return $htmls;  
}

//flag相加,返回数值，用于查询
function flag2sum($str, $delimiter = ',')
{
    if (empty($str)) {
        return 0;
    }
    $tmp_arr = array_filter(explode($delimiter, $str)); //去除空数组'',0,再使用sort()重建索引
    if (empty($tmp_arr)) {
        return 0;
    }

    $arr = array('a' => B_PIC, 'b' => B_TOP, 'c' => B_REC, 'd' => B_SREC, 'e' => B_SLIDE, 'f' => B_JUMP, 'g' => B_OTHER);
    $sum = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $tmp_arr)) {
            $sum += $v;
        }
    }

    return $sum;

}

/**
 * 返回内容中附件id数组
 * @param string $content 内容 in
 * @param string $firstpic 第一张缩略图 out
 * @param boolean $flag 是否获取第一张缩略图
 * @return mixed
 */
function get_att_content(&$content, &$firstpic = null, $flag = false) {

    //内容中的图片
    $img_arr = array();
    $reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";     
    preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
    // 匹配出来的不重复图片
    $img_arr = array_unique($img_arr[1]);
    $attid_array = array();
    
    if (!empty($img_arr)) {

       
        $baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'), true);
        $baseurl2 = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));//不带域名
        /*
        foreach ($img_arr as $k => $v) {
            $img_arr[$k] = str_replace(array($baseurl,$baseurl2), array('',''), $v);//清除域名前缀                    
        }
        */
        $img_arr = str_replace(array($baseurl,$baseurl2), array('',''), $img_arr);//清除域名前缀  

       
        $attid = M('attachment')->field('id,filepath')->where(array('filepath' => array('in', $img_arr)))->select();
        
        if ($attid) {

            //只有缩略图为空时,才提取第一张图片
            if ($flag && isset($firstpic)) {
                //取出本站内的第一张图
                foreach ($img_arr as $v) {
                    foreach ($attid as $v2) {
                        if ($v == $v2['filepath']) {
                            $imgtbSize = explode(',', get_cfg_value('CFG_IMGTHUMB_SIZE'));//配置缩略图第一个参数
                            $imgTSize = explode('X', $imgtbSize[0]);
                            $firstpic =  get_picture($baseurl2.$v2['filepath'], intval($imgTSize[0]), intval($imgTSize[1]));
                            break 2;
                        }
                    }
                }
            }

            //attid 数组
            foreach ($attid as $v) {
                $attid_array[] = $v['id'];
            }
        }
        
    }

    return $attid_array;
}

/**
 * 返回附件id数组
 * @param string|array $attachment 附件内容
 * @param boolean $flag 是否是缩略图
 * @return mixed
 */
function get_att_attachment($attachment,$flag = false) {

    
    if (empty($attachment)) {
        return array();
    }
    $attid_array = array();
    $baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));

    //清除缩略图的!200X200.jpg后缀
    if ($flag) {
        $attachment = preg_replace(array('#!(\d+)X(\d+)\.jpg$#i','#^'.$baseurl.'#i'), array('',''), $attachment);
    }else {
        $attachment = str_replace($baseurl, '', $attachment);
    }
    
    $attid = M('attachment')->where(array('filepath' => array('IN', $attachment)))->getField('id',true);
    if($attid){
        $attid_array = $attid;
    }

    return $attid_array;
}

/**
 * 返回保存到attachmentindex表
 * @param integer|array $attid 附件id
 * @param integer $attid 附件id
 * @param integer $modelid 模型id 
 * @param string $modelname 模型名称(唯一标志符)
 * @return mixed
 */
function insert_att_index($attid, $arcid, $modelid, $modelname = '') {
    if (empty($attid) || empty($arcid)) {
        return false;
    }
    if (empty($modelid) && $modelname == '') {
        return false;
    }

    if (is_array($attid)) {
        $attid_array = array_unique($attid);
    } else {
        $attid_array = array($attid);
    }

    //mysql,支持addAll
    if (in_array(strtolower(C('DB_TYPE')), array('mysql','mysqli','mongo'))) {
        
        $dataAtt = array();
        foreach ($attid_array as $v) {
            if ($modelid>0) {
                $dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid);
            } else {
                $dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname);
            }       
        }
        M('attachmentindex')->addAll($dataAtt);
    } else {

        foreach ($attid_array as $v) {
            if ($modelid>0) {
                M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid));
            } else {
                M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname));
            }       
        }
    }
        

    return true;
}

/**
 * 删除静态缓存文件
 * @param string $str 缓存路径
 * @param boolean $isdir 是否是目录
 * @param string $rules 缓存规则名
 * @return mixed
 */
function del_cache_html($str, $isdir = false, $rules = '')
{
    //为空，且不是目录
    $delflag = true;
    if (empty($str) && !$isdir) {
        return;
    }
    $str_array = array();

    //更新静态缓存
    $html_cache_rules = get_meta_value('HTML_CACHE_RULES_COMMON');
    if (get_meta_value('HOME_HTML_CACHE_ON')) {
        $str_array[] = HTML_PATH . 'Home/' . $str;
    }

    if (get_meta_value('MOBILE_HTML_CACHE_ON')) {
        $str_array[] = HTML_PATH . 'Mobile/' . $str;
    }

    if (!empty($rules) && !isset($html_cache_rules[$rules])) {
        $delflag = false; //指定规则，如不存在则不用清除
    } else {
        $delflag = true;
    }

    if ($delflag) {
        foreach ($str_array as $v) {
            if ($isdir && is_dir($v)) {
                del_dir_file($v, false);
            } else {
                $list = glob($v . '*');
                for ($i = 0; $i < count($list); $i++) {
                    if (is_file($list[$i])) {
                        unlink($list[$i]);
                    }
                }
            }

        }

    }

}

/**
 * 返回数据元值表(Key-Value)
 * @param string|integer $key 标识名
 * @return mixed
 */
function get_meta_value($key)
{
    $sname = 'config/meta';
    if ($key == '') {
        return '';
    }
    $array = F($sname);
    if (!$array) {
        $data  = M('meta')->field('name,value')->select();
        $array = array();
        if ($data) {
            foreach ($data as $value) {
                $array[$value['name']] = $value['value'];
            }
        }

        //静态缓存规则
        $html_cache_rules = array();
        if (isset($array['HTML_CACHE_INDEX_ON']) && $array['HTML_CACHE_INDEX_ON'] == 1) {
            $html_cache_rules['index:index'] = array('{:module}/Index_{:action}', intval($array['HTML_CACHE_INDEX_TIME']));
        }
        if (isset($array['HTML_CACHE_LIST_ON']) && $array['HTML_CACHE_LIST_ON'] == 1) {
            $html_cache_rules['list:index'] = array('{:module}/List/{:action}_{e}{cid|intval}_{p|intval}', intval($array['HTML_CACHE_LIST_TIME']));
        }
        if (isset($array['HTML_CACHE_SHOW_ON']) && $array['HTML_CACHE_SHOW_ON'] == 1) {
            $html_cache_rules['show:index'] = array('{:module}/Show/{:action}_{e}{cid|intval}_{id|intval}', intval($array['HTML_CACHE_SHOW_TIME']));
        }
        if (isset($array['HTML_CACHE_SPECIAL_ON']) && $array['HTML_CACHE_SPECIAL_ON'] == 1) {
            $html_cache_rules['special:index'] = array('{:module}/Special/{:action}_{cid|intval}_{p|intval}', intval($array['HTML_CACHE_SPECIAL_TIME'])); //首页
            $html_cache_rules['special:shows'] = array('{:module}/Special/{:action}_{id|intval}', intval($array['HTML_CACHE_SPECIAL_TIME'])); //页面
        }
        $array['HTML_CACHE_RULES_COMMON'] = $html_cache_rules; //公共静态缓存规则

        //路由
        $tmp = explode("\n", str_replace(array("\r\n", "\t"), array("\n", ""), trim($array['HOME_URL_ROUTE_RULES'], "\r\n")));

        $url_route_rules = array();
        foreach ($tmp as $v) {
            $temparr = explode('=>', $v);
            if (empty($temparr[0]) || empty($temparr[1])) {
                continue;
            }
            $url_route_rules[$temparr[0]] = $temparr[1];
        }
        $array['HOME_URL_ROUTE_RULES'] = $url_route_rules; //公共静态缓存规则

        F($sname, $array);
    }

    $value = isset($array[$key]) ? $array[$key] : '';
    return $value;

}

/**
 * D2是D方法的扩展20140919
 * D2函数用于实例化Model 格式 项目://分组/模块
 * @param string $name Model资源地址
 * @param string $tableName 数据表名
 * @param string $layer 业务层名称
 * @return Model
 */
function D2($name = '', $tableName = '', $layer = '')
{
    if (empty($name)) {
        return new \Think\Model;
    }

    static $_model = array();
    $layer         = $layer ?: C('DEFAULT_M_LAYER');
    if (isset($_model[$name . $layer . '\\' . $tableName])) {
        return $_model[$name . $layer . '\\' . $tableName];
    }

    $class = parse_res_name($name, $layer);
    if (class_exists($class)) {
        //$model      =   new $class(basename($name));
        $model = empty($tableName) ? new $class(basename($name)) : new $class(basename($tableName), $tableName);
    } elseif (false === strpos($name, '/')) {
        // 自动加载公共模块下面的模型
        if (!C('APP_USE_NAMESPACE')) {
            import('Common/' . $layer . '/' . $class);
        } else {
            $class = '\\Common\\' . $layer . '\\' . $name . $layer;
        }
        $model = class_exists($class) ? (empty($tableName) ? new $class(basename($name)) : new $class(basename($tableName), $tableName)) : new Think\Model($name);
    } else {
        Think\Log::record('D方法实例化没找到模型类' . $class, Think\Log::NOTICE);
        $model = new \Think\Model(basename($name));
    }
    $_model[$name . $layer . '\\' . $tableName] = $model;
    return $model;
}

//通过用户id获取用户名称
function get_username($userid = 0) {
    $user_name = '';
    if($userid > 0){
        $rs = M('member')->field('username')->find($userid);
        $user_name = $rs['username'];
    }else{
        $user_name = '管理员';
    }    
    return $user_name;
}

//通过分类id获取分类名称
function get_catename($cid = 0) {
    $user_name = '';
    if($cid > 0){
        $rs = M('classs')->field('cname')->find($cid);
        $cate_name = $rs['cname'];
    }else{
        $cate_name = '无分类名称';
    }    
    return $cate_name;
}

//通过属性id获取属性名称
function get_propname($prop_id = 0) {
    $user_name = '';
    if($prop_id > 0){
        $rs = M('property')->field('name')->find($prop_id);
        $prop_name = $rs['name'];
    }else{
        $prop_name = '无属性名称';
    }    
    return $prop_name;
}

/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password($password, $encrypt = '')
{
    $pwd             = array();
    $pwd['encrypt']  = $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function get_randomstr($lenth = 6)
{
    return get_random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
 * 产生随机字符串
 *
 * @param    int        $length  输出长度
 * @param    string     $chars   可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function get_random($length, $chars = '0123456789')
{
    $hash = '';
    $max  = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/*
 * 发送邮件
 * @param $to string
 * @param $title string
 * @param $content string
 * @return bool
 * */
function sendMail($to, $title, $content) {
    Vendor('PHPMailer.class#phpmailer');

    $mail = new PHPMailer;
    //$mail->Priority = 3;
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //发件人邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //163邮箱发件人授权密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,"尊敬的客户");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    return($mail->Send());
}

/**
 * 得到指定cookie的值
 *
 * @param string $name
 */
//function get_cookie($name, $key = '@^%$y5fbl') {
function get_cookie($name, $key = '')
{

    if (!isset($_COOKIE[$name])) {
        return null;
    }
    $key = empty($key) ? C('CFG_COOKIE_ENCODE') : $key;

    $value = $_COOKIE[$name];
    $key   = md5($key);
    $sc    = new \Common\Lib\SysCrypt($key);
    $value = $sc->php_decrypt($value);
    return unserialize($value);
}

/**
 * 删除cookie
 * @param array $args
 * @return boolean
 */
function del_cookie($args)
{
    $name   = $args['name'];
    $domain = isset($args['domain']) ? $args['domain'] : null;
    return isset($_COOKIE[$name]) ? setcookie($name, '', time() - 86400, '/', $domain) : true;
}

/**
 * 设置cookie
 *
 * @param array $args
 * @return boolean
 */
//使用时修改密钥$key 涉及金额结算请重新设计cookie存储格式
//function set_cookie($args , $key = '@^%$y5fbl') {
function set_cookie($args, $key = '')
{
    $key = empty($key) ? C('CFG_COOKIE_ENCODE') : $key;

    $name   = $args['name'];
    $expire = isset($args['expire']) ? $args['expire'] : null;
    $path   = isset($args['path']) ? $args['path'] : '/';
    $domain = isset($args['domain']) ? $args['domain'] : null;
    $secure = isset($args['secure']) ? $args['secure'] : 0;
    $value  = serialize($args['value']);

    $key   = md5($key);
    $sc    = new \Common\Lib\SysCrypt($key);
    $value = $sc->php_encrypt($value);
    //setcookie($cookieName ,$cookie, time()+3600,'/','',false);
    return setcookie($name, $value, $expire, $path, $domain, $secure); //失效时间   0关闭浏览器即失效
}

/**
 * 检测验证码
 */
function check_verify($code, $id = 1)
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 获取指定大小的头像[必须系统有的]
 * @param string $str 头像地址
 * @param integer $size 尺寸长宽
 * @param boolean $rnd 是否显示随机码
 * @return string
 */
function get_avatar($str, $size = 160, $rnd = false)
{

    $ext = 'gif';
    if (!empty($str)) {
        $ext = explode('.', $str);
        $ext = end($ext);
    }

    if (empty($ext) || !in_array(strtolower($ext), array('jpg', 'gif', 'png', 'jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str = __ROOT__ . '/avatar/system/0.gif';
        $ext = 'gif';
        if ($size > 160 || $size < 30) {
            $size = 160;
        }
    }

    if ($size == 0) {
        return $str;
    }
    $rndstr = $rnd ? '?random=' . time() : '';
    return $str . '!' . $size . 'X' . $size . '.' . $ext . $rndstr;
}

//获取评论用户昵称
function get_nickname($userid = 0) {
    if($userid > 0){
        $map['id'] = $userid;
        $rs = D('MemberView')->nofield('password,encrypt')->where($map)->find();
        if($rs && $rs['nickname']){
            $nickname = $rs['nickname'];
        }elseif($rs && $rs['username']){
            $nickname = $rs['username'];
        }else{
            $nickname = '未知';
        }
    }else{
        $nickname = '游客';
    }
    return $nickname;
}

//获取评论用户头像
function get_face($userid = 0) {
    if($userid > 0){
        $map['id'] = $userid;
        $rs = D('MemberView')->nofield('password,encrypt')->where($map)->find();
        if($rs && $rs['face']){
            $avatar = $rs['face'];
        }else{
            $avatar = __ROOT__ . '/Public/Home/images/avatar.jpg';

        }
    }else{
        $avatar = __ROOT__ . '/Public/Home/images/1.gif';
    }
    return $avatar;
}

function check_badword($content)
{
    //定义处理违法关键字的方法
    $badword = C('CFG_BADWORD'); //定义敏感词

    if (empty($badword)) {
        return false;
    }
    $keyword = explode('|', $badword);
    $m       = 0;
    for ($i = 0; $i < count($keyword); $i++) {
        //根据数组元素数量执行for循环
        //应用substr_count检测文章的标题和内容中是否包含敏感词
        if (substr_count($content, $keyword[$i]) > 0) {
            //$m ++;
            return true;
        }
    }
    //return $m;              //返回变量值，根据变量值判断是否存在敏感词
    return false;
}

function addPoints($title, $points, $uid, $descrip, $type){
    //记录积分操作
    $log['uid'] = $uid;
    $log['scoreinfo'] = '+'.$points;
    $log['type'] = $type; //积分加减类型：0 不变，1 增加，2 减少
    $log['addtime'] = time();
    $log['title'] = $title;
    $log['url'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
    $log['descrip'] = $descrip;
    M('member_slog')->add($log);
    M('member')->where(array('id'=>$uid))->setInc('score',$points);
}

/**
 * 获取联动(字典)项的值
 * @param string $group 联动组名
 * @param integer $value 联动值
 * @return string
 */
function get_item_value($group, $value = 0)
{
    //return $value.'--<br>';
    ${'item_' . $group} = get_item($group);
    if (isset(${'item_' . $group}[$value])) {
        return ${'item_' . $group}[$value];
    } else {
        return "保密";
    }
}

function get_user(){
    $uid = intval(get_cookie('uid'));
    $res = '';
    if ($uid > 0) {
        $user = D('MemberView')->nofield('password,encrypt')->find($uid);
        if($user){
            $res = $user;
        }
    }
    return $res;
}
