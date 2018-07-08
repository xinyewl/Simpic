<?php

namespace app\admin\controller;

use think\Db;
use think\Request;

class Login extends Common
{

    public function _initialize()
    {
    }

    public function index()
    {
        if(Request::instance()->isAjax()) {
            $input = trimArray(Request::instance()->post());
            $user_db = Db::name('user');
            if(filter_var($input['user'], FILTER_VALIDATE_EMAIL)) {
                // 验证邮箱
                $where = ['email' => $input['user'], 'id' => 1];
                $is_user = $user_db->where($where)->count() > 0 ? true : false;
                $user = '邮箱';
            } else {
                // 验证用户名
                $where = ['username' => $input['user'], 'id' => 1];
                $is_user = $user_db->where($where)->count() > 0 ? true : false;
                $user = '用户名';
            }
            if(!$is_user) return parent::json(0, "{$user}不存在!");
            $where['password'] = $this->md6($input['password']);
            $user = $user_db->where($where)->find();
            if($user) {
                session('admin', $user['username']);
                cookie('admin', $user['username']);
                return parent::json(1, '登录成功');
            }
            return parent::json(0, "{$user}或密码错误!");
        }
        return $this->fetch();
    }
}
