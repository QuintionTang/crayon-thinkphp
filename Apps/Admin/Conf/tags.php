<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 系统默认的核心行为扩展列表文件
return array(
    'app_init'      =>  array(
    ),
    'app_begin'     =>  array(
        'Behavior\ReadHtmlCacheBehavior', // 读取静态缓存
        'Behavior\CheckLangBehavior',
    ),
    'route_check'   =>  array(

    ),
    'app_end'       =>  array(),
    'path_info'     =>  array(),
    'action_begin'  =>  array(),
    'action_end'    =>  array(),
    'view_begin'    =>  array(),
    'view_template' =>  array(

    ),
    'view_parse'    =>  array(

    ),
    'view_filter'   =>  array(
    ),
    'view_end'      =>  array(
        'Behavior\ShowPageTraceBehavior', // 页面Trace显示
    )
);