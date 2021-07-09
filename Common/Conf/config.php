<?php
return array(
	//'配置项'=>'配置值'
    // 设置禁止访问的模块列表
    'MODULE_DENY_LIST' => array('Common','Runtime','Api'),
    'MODULE_ALLOW_LIST' => array('Admin'),
    'LANG_SWITCH_ON'    =>    true,   //开启语言包功能
    'LANG_AUTO_DETECT'    =>    false, // 自动侦测语言
    'DEFAULT_LANG'  =>  'zh-cn',
    'DEFAULT_MODULE' => 'Admin',
    'TAGLIB_PRE_LOAD' => 'html',
    'LOAD_EXT_CONFIG' => 'db',
    'TMPL_ACTION_SUCCESS' => 'public:success',
    'TMPL_ACTION_ERROR' => 'public:error',
    'DATA_CACHE_SUBDIR' => true,
    'DATA_PATH_LEVEL' => 3,
    'DATA_CACHE_TIME' => 3600,
    'SHOW_ERROR_MSG'        =>  true,
    'DATA_CACHE_TYPE' => 'File',
    'CACHE_PREFIX' => '',
    'URL_MODEL' => 3,
    'TAG_DELI' => '#{cra}#'
);

?>