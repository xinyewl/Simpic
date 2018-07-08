<?php
/**
 * index模块共用控制器
 * User: WispX
 * Date: 2017/9/15
 * Time: 15:32
 */
namespace app\index\controller;

use think\Config;
use think\Db;
use think\Controller;

class Common extends Controller
{

    protected $user = false;
    protected $conf;
    protected $web;
    protected $scheme;

    /**
     * 前置操作
     */
    public function _initialize()
    {
        // 检测域名授权
        //$auth = json_decode(curl('https://service.lskys.cc/server.php', ['action' => 'auth', 'domain' => $_SERVER['HTTP_HOST']]), true);
        //if(!$auth['code']) die('程序未授权，请联系QQ：<a href="http://wpa.qq.com/msgrd?v=3&uin=1591788658&site=qq&menu=yes">1591788658</a> 授权！');
        $this->web = Config::get('web');
        $this->conf = getSystemConf();
        $this->conf['file_path'] = Config::get('file_path');
        $this->conf['theme_path'] = Config::get('theme_path');
        $this->user = $this->getUser();
        $this->scheme = getSchemeList();
        $this->assign('web', $this->web);
        $this->assign('conf', $this->conf);
        $this->assign('user', $this->user);
    }

    /**
     * 未登录重定向
     * @return bool|void
     */
    protected function isLoginRedirect()
    {
        return $this->user ? true : $this->redirect('/login');
    }

    /**
     * 获取已登录会员信息
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public function getUser()
    {
        if(!$this->user) {
            $login_status = session('login_status');
            $user = Db::name('user')->where('login_status', $login_status)->find();
            return $user ? $user : false;
        }
        return false;
    }

    /**
     * 直接返回json
     * @param int $code 状态码
     * @param string $msg 状态信息
     * @param string $data 返回数据(可选)
     * @return \think\response\Json
     */
    protected function json($code, $msg, $data = '', $url = '')
    {
        $result = ['code' => $code, 'msg' => $msg];
        if(!empty($data)) $result['data'] = $data;
        if(!empty($url)) $result['url'] = $url;
        return json($result);
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
}