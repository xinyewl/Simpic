<?php
namespace app\index\controller;

use think\Request;
use think\File;
use think\Db;
use think\Loader;
use Qiniu\Auth;
use Upyun\Upyun;

class User extends Common
{

    public function _initialize()
    {
        parent::_initialize();
        parent::isLoginRedirect();
    }

    public function index()
    {
        $this->conf['web_title'] = "用户中心 - {$this->conf['web_title']}";
        $this->assign('conf', $this->conf);
        return $this->fetch();
    }

    /**
     * 分页获取图片数据
     * @param $page 页码
     * @param $limit 每页显示条数
     * @param array $so 搜索条件
     * @param string $sort 排序条件
     * @return \think\response\Json
     */
    function getImgList($page, $limit, array $so = [], $sort = '')
    {
        if(Request::instance()->isAjax()) {
            $where = ['id' => ['gt', 0]];
            $map = count($so) > 0 ? trimArray($so) : false;
            if($map) {
                if(!empty($map['date'])) {
                    $date = explode(' - ', $map['date']);
                    foreach ($date as $key => $val) {
                        switch ($key) {
                            case 0: $where['upload_time'] = ['gt', strtotime($val)];
                            case 1: $where['upload_time'] = ['lt', strtotime($val)];
                        }
                    }
                }
                if(!empty($map['search_val'])) $where['name'] = ['like', "%{$map['search_val']}%"];
            }
            // 上传方案
            $scheme = getSchemeList();
            $data = Db::name('file')->where('user_id', $this->user['id'])->where($where)->order(!empty($sort) ? $sort : 'upload_time desc')->page($page, $limit)->select();
            foreach ($data as &$val) {
                switch ($val['scheme_id']) {
                    case 1:
                        $val['url'] = "{$this->web['domain']}/pic/{$val['path']}"; break;
                    case 2:
                        $val['url'] = "{$scheme['qiniu']['domain']}/{$val['path']}"; break;
                    case 3:
                        $val['url'] = "{$scheme['upyun']['domain']}/{$val['path']}"; break;
                    // TODO case 4
                }
            }
            return parent::json(1, 'success', $data);
        }
    }

    /**
     * 编辑资料
     * @return \think\response\Json
     */
    public function edit()
    {
        if(Request::instance()->isAjax()) {
            $input = trimArray(Request::instance()->param());
            $validate = Loader::validate('Edit');
            if(!$validate->check($input)) return parent::json(0, $validate->getError());
            if(!empty($input['password'])) {
                if($input['password'] != $input['passwords']) {
                    return parent::json(0, '两次输入的密码不一致');
                }
                $input['password'] = parent::md6($input['password']);
            } else {
                unset($input['password']);
            }
            unset($input['passwords']);
            $input['edit_time'] = time();
            if(Db::name('user')->where('id', $this->user['id'])->update($input)) {
                return parent::json(1, '修改成功');
            }
            return parent::json(0, '修改失败');
        }
    }

    /**
     * 退出账号
     */
    public function logout()
    {
        if(Request::instance()->isAjax()) {
            session('login_status', null);
        }
    }

    /**
     * 删除文件及记录
     * @param $id
     * @return \think\response\Json
     */
    public function picDel($id)
    {
        if(Request::instance()->isAjax()) {
            Db::startTrans();
            try {
                $file_db = Db::name('file');
                $map = ['id' => $id, 'user_id' => $this->user['id']];
                $file_info = $file_db->where($map)->find();
                switch ($file_info['scheme_id']) {
                    case 1: // 删除本地文件
                        // 删除文件记录
                        if($file_db->where($map)->delete()) {
                            // 删除文件
                            @unlink("{$this->conf['file_path']}/{$file_info['path']}");
                        }
                        break;
                    case 2: // 删除七牛云文件
                        $auth = new Auth($this->scheme['qiniu']['access_key'], $this->scheme['qiniu']['secret_key']);
                        $config = new \Qiniu\Config();
                        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
                        $bucketManager->delete($this->scheme['qiniu']['bucket_name'], $file_info['path']);
                        $file_db->where($map)->delete();
                        break;
                    case 3: // 删除又拍云文件
                        if($file_db->where($map)->delete()) {
                            // 创建实例
                            $bucketConfig = new \Upyun\Config($this->scheme['upyun']['bucket_name'], $this->scheme['upyun']['access_key'], $this->scheme['upyun']['secret_key']);
                            $client = new Upyun($bucketConfig);
                            // 删除文件
                            $client->delete($file_info['path']);
                            // 删除目录
                            //$client->deleteDir(substr('/' . $file_info['path'], 0, strrpos($file_info['path'], '/')));
                        }
                        break;
                    case 4: // 删除阿里OSS文件
                        break;
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return parent::json(0, "删除失败，{$e->getMessage()}");
            }
            return parent::json(1, "删除成功");
        }
    }

    /**
     * 批量删除
     * @param $array
     * @return \think\response\Json
     */
    public function picBatchDel($array)
    {
        if(Request::instance()->isAjax()) {
            Db::startTrans();
            try {
                $file_db = Db::name('file');
                foreach ($array as $val) {
                    $file_info = $file_db->where(['id' => $val, 'user_id' => $this->user['id']])->find();
                    switch ($file_info['scheme_id']) {
                        case 1: // 删除本地文件
                            @unlink("{$this->conf['file_path']}/{$file_info['path']}");
                            $file_db->where('id', $val)->delete();
                            break;
                        case 2: // 删除七牛云文件
                            $auth = new Auth($this->scheme['qiniu']['access_key'], $this->scheme['qiniu']['secret_key']);
                            $config = new \Qiniu\Config();
                            $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
                            $bucketManager->delete($this->scheme['qiniu']['bucket_name'], $file_info['path']);
                            $file_db->where('id', $val)->delete();
                            break;
                        case 3: // 删除又拍云文件
                            $file_db->where('id', $val)->delete();
                            // 创建实例
                            $bucketConfig = new \Upyun\Config($this->scheme['upyun']['bucket_name'], $this->scheme['upyun']['access_key'], $this->scheme['upyun']['secret_key']);
                            $client = new Upyun($bucketConfig);
                            $client->delete($file_info['path']);
                            // 删除目录
                            //$client->deleteDir(substr('/' . $file_info['path'], 0, strrpos($file_info['path'], '/')));
                            break;
                        case 4: // 删除阿里OSS文件
                            //$file_db->where('id', $val)->delete();
                            break;
                    }
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return parent::json(0, "删除失败，{$e->getMessage()}");
            }
            return parent::json(1, '删除成功');
        }
    }
}
