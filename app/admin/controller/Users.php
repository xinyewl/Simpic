<?php
/**
 * 用户管理
 * User: WispX
 * Date: 2017/9/22
 * Time: 15:21
 * Link: http://gitee.com/wispx
 */
namespace app\admin\controller;

use think\Db;
use think\Request;

class Users extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 分页获取会员
     * @param int $page 页码
     * @param int $limit 每页显示数量
     * @param array $key 条件
     * @return \think\response\Json
     */
    public function getUserList($page = 0, $limit = 0, array $key = [])
    {
        $map['id'] = ['neq', 1];
        if(count($key) > 0) $map = $key;
        $user_db = Db::name('user');
        $user_list = $user_db->where($map)->order('reg_time desc')->page($page, $limit)->select();
        foreach ($user_list as $key => &$val) {
            $val['login_time'] = formatTime($val['login_time']);
            $val['reg_time'] = date('Y-m-d h:i:s', $val['reg_time']);
            unset($val['password']);
            unset($val['edit_time']);
        }
        return parent::json(0, '', $user_list, '', $user_db->count());
    }

    /**
     * 删除 And 批量删除会员
     * @param $id
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function del($id)
    {
        if(Request::instance()->isAjax()) {
            if(Db::name('user')->delete($id)) {
                return parent::json(1, '删除成功');
            }
            return parent::json(0, '删除失败');
        }
    }

}
