<?php
/**
 * index模块配置
 * User: WispX
 * Date: 2017/9/15
 * Time: 15:30
 */
return [
    'web'                   => [
        'domain'    => \think\Request::instance()->domain()
    ],
    'template'              => [
        'view_path' => '../app/index/view/theme/' . getSystemConf('now_theme') . '/'
    ],
];