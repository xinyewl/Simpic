<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

if (!file_exists("../app/db.php") || (file_exists("../app/db.php") && file_get_contents("../app/db.php") == "")) exit(header("location: install.php"));

// 定义应用目录
define('APP_PATH', __DIR__ . '/../app/');
// 绑定到index模块
define('BIND_MODULE', 'index');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
