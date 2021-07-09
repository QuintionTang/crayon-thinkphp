<?php
// +----------------------------------------------------------------------
// | Rusty Crayon WebThink[锈蜡笔Web基础架构]
// +----------------------------------------------------------------------
// | ProductionSteps：生产流程信息Model
// +----------------------------------------------------------------------
// | Copyright (c) rusty-crayon 2021-2023 https://www.rusty-crayon.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: QuintionTang <QuintionTang@gmail.com>
// +----------------------------------------------------------------------

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('VERSION','0.0.8');   //内部版本
define('COPY','2020-2025');    //发布版本
define('APP_DEBUG',true);

define('COMMON_PATH','./Common/');
define('APP_PATH','./Apps/');

define('SITE_ROOT','/');
define('APP_NAME', 'app');
define('DATA_PATH', './data/');
define('EXTEND_PATH', APP_PATH . 'Extend/');
define('CONF_PATH', COMMON_PATH . 'Conf/');
define('RUNTIME_PATH', DATA_PATH . 'runtime/');
define('BUILD_DIR_SECURE', false);    //定义是否需要在生成目录自动生成INDEX.HTML文件

// 定义应用绑定的名称
define('BIND_MODULE','Admin');
// 引入ThinkPHP入口文件
require './core/ThinkPHP.php';
?>