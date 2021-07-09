<?php
return array(
	//'配置项'=>'配置值'
	'TMPL_DETECT_THEME' => true,
    'BASIC_THEME' => 'default',
    'DEFAULT_THEME' => 'default',
    'DEFAULT_MODULE' =>'Admin',
    'TMPL_PARSE_STRING'=>array(
        '__public__'=> '/Apps/Admin/View/default/public/',   //模板公共文件夹
        '__TMPL__' => '/Apps/Admin/View/default/'
    ),
    'URL_MODEL'=>1,
    'URL_HTML_SUFFIX'=>'',
    'URL_CASE_INSENSITIVE'  =>  true,
);