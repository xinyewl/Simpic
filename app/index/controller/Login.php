<?php
namespace app\index\controller;

use think\Db;
use think\Request;
use mail\Smtp;

class Login extends Common
{
    public function index()
    {
        if(Request::instance()->isAjax()) {
            $input = trimArray(Request::instance()->param());
            $user_db = Db::name('user');
            if(filter_var($input['user'], FILTER_VALIDATE_EMAIL)) {
                // 验证邮箱
                $where = ['email' => $input['user']];
                $is_user = $user_db->where($where)->count() > 0 ? true : false;
                $user = '邮箱';
            } else {
                // 验证用户名
                $where = ['username' => $input['user']];
                $is_user = $user_db->where($where)->count() > 0 ? true : false;
                $user = '用户名';
            }
            if(!$is_user) return parent::json(0, "{$user}不存在!");
            $where['password'] = $this->md6($input['password']);
            if($user_db->where($where)->count() > 0 ? true : false) {
                $login_status = base64_encode(uniqid() . time());
                if($user_db->where($where)->update(['login_status' => $login_status, 'login_time' => time(), 'login_ip' => Request::instance()->ip()])) {
                    session('login_status', $login_status);
                    return parent::json(1, '登录成功');
                }
                return parent::json(0, '登录失败');
            }
            return parent::json(0, "{$user}或密码错误!");
        }
        $this->conf['web_title'] = "登录 - {$this->conf['web_title']}";
        $this->assign('conf', $this->conf);
        return $this->fetch();
    }

    /**
     * 获取邮箱验证码
     * @param $email
     * @return \think\response\Json
     */
    public function getEmailCode($email)
    {
        if(Request::instance()->isAjax()) {
            if(Db::name('user')->where('email', $email)->count() > 0 ? true : false) {
                $code = getCode();
                $smtp = new Smtp(
                    $this->conf['smtp_host'],
                    $this->conf['smtp_port'],
                    $this->conf['smtp_auth'],
                    $this->conf['smtp_user'],
                    $this->conf['smtp_pass'],
                    $this->conf['smtp_ssl']
                );
                $html = "<h2>重置密码</h2><hr><p>你本次的验证码是 <b>{$code}</b></p><p>(如果不是您本人发起的，请忽略此邮件。)</p><br><hr><p>「{$this->conf['web_title']}」</p>";
                $send = $smtp->send($email, $this->conf['smtp_user'], "「{$this->conf['web_title']}」重置密码", $html);
                if($send) {
                    session('reset_pass', ['reset_email' => $email, 'email_code' => $code]);
                } else {
                    return parent::json(0, '验证码发送失败');
                }
                return parent::json(1, '验证码发送成功');
            }
            return parent::json(0, '邮箱不存在');
        }
    }

    /**
     * 校检邮件验证码
     * @param $code
     * @return \think\response\Json
     */
    public function isEmailCode($code)
    {
        if(Request::instance()->isAjax()) {
            $reset_pass = session('reset_pass');
            if($code == $reset_pass['email_code']) {
                // 用户输入的验证码
                session('user_code', $code);
                return parent::json(1, '验证成功');
            }
            return parent::json(0, '验证码错误');
        }
    }

    /**
     * 重置密码
     * @param $password
     * @return \think\response\Json
     */
    public function resetPassWord($password) {
        if(Request::instance()->isAjax()) {
            $reset_pass = session('reset_pass');
            if(session('user_code') == $reset_pass['email_code']) {
                if(Db::name('user')->where('email', $reset_pass['reset_email'])->setField('password', parent::md6($password))) {
                    session('reset_pass', null);
                    session('user_code', null);
                    return parent::json(1, '重置成功');
                }
                return parent::json(0, '重置失败');
            }
        }
    }
}
