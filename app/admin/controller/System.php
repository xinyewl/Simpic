<?php
/**
 * 系统管理
 * User: WispX
 * Date: 2017/9/22
 * Time: 15:27
 * Link: http://gitee.com/wispx
 */
namespace app\admin\controller;

use think\Db;
use think\Request;
use mail\Smtp;

class System extends Common
{
    public function index()
    {
        if(Request::instance()->isAjax()) {
            $data = Request::instance()->post();
            Db::startTrans();
            try {
                foreach ($data as $key => $val) {
                    Db::name('config')->where('key', $key)->update(['value' => $val, 'edit_time' => time()]);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return parent::json(0, '修改失败');
            }
            return parent::json(1, '修改成功');
        }
        $this->assign('scheme', getSchemeList());
        return $this->fetch();
    }

    /**
     * 修改上传方案配置
     * @return \think\response\Json
     */
    public function setScheme()
    {
        if(Request::instance()->isAjax()) {
            Db::startTrans();
            try {
                $input = Request::instance()->param();
                $input['edit_time'] = time();
                Db::name('config')->where('key', 'upload_scheme_id')->setField('value', $input['id']);
                Db::name('scheme')->where('id', $input['id'])->update($input);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return parent::json(0, "Code: {$e->getCode()} Msg: {$e->getMessage()}");
            }
            return parent::json(1, '修改成功');
        }
    }

    /**
     * 发送测试Email
     * @param $email
     * @return \think\response\Json\
     */
    public function sendTestEmail($email)
    {
        if(Request::instance()->isAjax()) {
            $smtp = new Smtp(
                $this->conf['smtp_host'],
                $this->conf['smtp_port'],
                $this->conf['smtp_auth'],
                $this->conf['smtp_user'],
                $this->conf['smtp_pass'],
                $this->conf['smtp_ssl']
            );
            $send = $smtp->send($email, $this->conf['smtp_user'], "「{$this->conf['web_title']}」", '这是一封测试邮件');
            if($send) {
                return parent::json(1, '发送成功');
            } else {
                return parent::json(0, '发送失败');
            }
        }
    }
}
