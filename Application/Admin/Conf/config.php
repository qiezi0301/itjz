<?php
return array(
	//'配置项'=>'配置值'

	/* 配置文件夹路径 */
	'TMPL_PARSE_STRING' => array(
        '__STATIC__'  => __ROOT__ . '/Public/static',
		'__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
		'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
		'__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
		'__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
		'__FONTS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/fonts',
		'__FILES__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/files',
		'__HOME__'     => __ROOT__ . '/Public/' . MODULE_NAME,
        '__UPLOADS__' => __ROOT__ . '/Uploads/' ,
        '__PUBLIC__'  => __ROOT__ . '/Public'
	)
);