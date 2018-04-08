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