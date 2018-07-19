<?php

namespace app\admin\controller;

use think\Controller;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\Request;

class Common extends Controller
{

    protected $admin;
    protected $web;
    protected $conf;
    protected $scheme;

    public function _initialize()
    {
        if(empty(session('admin')) || empty(cookie('admin'))) return $this->redirect('login/');
        $this->admin = Db::name('user')->where('username', session('admin'))->find();
        if(count($this->admin) > 0) {
            $this->web = Config::get('web');
            $this->conf = getSystemConf();
            $this->conf['file_path'] = Config::get('file_path');
            $this->conf['theme_path'] = Config::get('theme_path');
            $this->scheme = getSchemeList();
            $this->assign('admin', $this->admin);
            $this->assign('web', $this->web);
            $this->assign('conf', $this->conf);
        } else {
            session('admin', null);
            cookie('admin', null);
            return $this->redirect('login/');
        }
    }

    public function index()
    {
        return $this->fetch();
    }

    /**
     * 自定义加密方式
     * @param $str
     * @return string
     */
    protected function md6($str)
    {
        return md5("LK{$str}");
    }

    /**
     * 直接返回json
     * @param int $code 状态码
     * @param string $msg 状态信息
     * @param string $data 返回数据(可选)
     * @return \think\response\Json
     */
    protected function json($code, $msg, $data = '', $url = '', $count = '')
    {
        $result = ['code' => $code, 'msg' => $msg];
        if(!empty($data)) $result['data'] = $data;
        if(!empty($url)) $result['url'] = $url;
        if(!empty($count)) $result['count'] = $count;
        return json($result);
    }

}