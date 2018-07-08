<?php

namespace app\admin\controller;

use think\Db;

class Console extends Common
{
    public function index()
    {
        $file_db = Db::name('file');
        $user_db = Db::name('user');
        // 图片数量
        $data['img_num'] = $file_db->count();
        // 今日新增图片
        $data['add_img_num'] = $file_db->where(['upload_time' => ['gt', strtotime(date('Y-m-d', time()))]])->count();
        // 用户总数
        $data['user_num'] = $user_db->count();
        // 今日新增用户
        $data['add_user_num'] = $user_db->where(['reg_time' => ['gt', strtotime(date('Y-m-d', time()))]])->count();
        // 总占用内存
        $data['occupy'] = round($file_db->sum('size') / 1024 / 1024, 2);
        // 上传文件限制
        $data['upload_max_filesize'] = ini_get('upload_max_filesize');
        // 执行时间限制
        $data['max_execution_time'] = ini_get('max_execution_time');
        // 剩余空间
        $data['disk_free_space'] = round((disk_free_space(".") / (1024 * 1024)), 2);
        // 获取公告
        $data['notice'] = json_decode(curl('https://service.lskys.cc/server.php', ['action' => 'getNoticeAll']), true);
        $this->assign('data', $data);
        return $this->fetch();
    }
}
