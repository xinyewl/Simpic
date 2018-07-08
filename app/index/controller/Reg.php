<?php
namespace app\index\controller;

use gt\gtCaptcha;
use think\Db;
use think\Request;
use think\Loader;

class Reg extends Common
{

    public function _initialize()
    {
        $this->conf = getSystemConf();
        $this->assign('conf', $this->conf);
        $this->assign('user', false);
    }

    public function index()
    {
        if(Request::instance()->isAjax()) {
            if($this->conf['reg_close']) return $this->error('注册功能已关闭');
            if($this->gtVerifyServlet()) {
                $input = trimArray(Request::instance()->param());
                $validate = Loader::validate('Reg');
                if(!$validate->check($input)) {
                    return parent::json(0, $validate->getError());
                } elseif ($input['password'] != $input['passwords']) {
                    return parent::json(0, '两次输入的密码不一致');
                }
                $data = [
                    'email'     => $input['email'],
                    'username'  => $input['username'],
                    'password'  => parent::md6($input['password']),
                    'reg_ip'    => Request::instance()->ip(),
                    'reg_time'  => time(),
                ];
                if(Db::name('user')->insert($data)) {
                    return parent::json(1, '注册成功');
                }
                return parent::json(0, '注册失败');
            }
            return parent::json(0, '人类验证失败，请刷新重试');
        }
        $this->conf['web_title'] = "注册 - {$this->conf['web_title']}";
        $this->assign('conf', $this->conf);
        return $this->fetch();
    }

    public function gtStartCaptchaServlet()
    {
        $GtSdk = new gtCaptcha($this->conf['captcha_id'], $this->conf['private_key']);
        $data = array(
            "uid"           => uniqid(),                    # 网站用户id
            "client_type"   => "web",                       #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address"    => Request::instance()->ip()    # 请在此处传输用户请求验证时所携带的IP
        );
        $status = $GtSdk->pre_process($data, 1);
        session('gtserver', $status);
        session('uid', $data['uid']);
        echo $GtSdk->get_response_str();
    }

    public function gtVerifyServlet()
    {
        $GtSdk = new gtCaptcha($this->conf['captcha_id'], $this->conf['private_key']);
        $data = array(
            "uid"           => session('uid'), # 网站用户id
            "client_type"   => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address"    => Request::instance()->ip() # 请在此处传输用户请求验证时所携带的IP
        );
        if (session('gtserver') == 1) {   //服务器正常
            $result = $GtSdk->success_validate(
                Request::instance()->param('geetest_challenge'),
                Request::instance()->param('geetest_validate'),
                Request::instance()->param('geetest_seccode'),
                $data);
            if ($result) {
                session(null);
                return $this->verifyStatus(true);
            } else {
                return $this->verifyStatus();
            }
        } else {  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate(
                Request::instance()->param('geetest_challenge'),
                Request::instance()->param('geetest_validate'),
                Request::instance()->param('geetest_seccode')
            )) {
                session(null);
                return $this->verifyStatus(true);
            } else {
                return $this->verifyStatus();
            }
        }
    }

    private function verifyStatus($status = false)
    {
        return $status;
        //echo json_encode(['status' => $status ? 'success' : 'fail']);

    }
}
