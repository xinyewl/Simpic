<?php
/**
 * 图片管理
 * User: WispX
 * Date: 2017/9/22
 * Time: 15:26
 * Link: http://gitee.com/wispx
 */
namespace app\admin\controller;

use think\Db;
use think\Request;
use Qiniu\Auth;
use Upyun\Upyun;

class Picture extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    public function getFileList($page = 0, $limit = 0, array $key = [])
    {
        $map['id'] = ['gt', 0];
        if(count($key) > 0) $map = $key;
        $file_db = Db::name('file');
        $file_list = $file_db->where($map)->order('upload_time desc')->page($page, $limit)->select();
        // 上传方案
        $scheme = getSchemeList();
        foreach ($file_list as &$val) {
            $val['upload_time'] = formatTime($val['upload_time']);
            $val['size'] = round(($val['size'] / 1024 / 1024), 2) . 'Mb';
            $val['user_id'] = $this->getUserName($val['user_id']);
            switch ($val['scheme_id']) {
                case 1:
                    $url = "{$this->web['domain']}/pic/{$val['path']}"; break;
                case 2:
                    $url = "{$scheme['qiniu']['domain']}/{$val['path']}"; break;
                case 3:
                    $url = "{$scheme['upyun']['domain']}/{$val['path']}"; break;
                // TODO case 4
            }
            $val['name'] = "<a target=\"_blank\" href=\"{$url}\">{$val['name']}</a>";
        }
        return parent::json(0, '', $file_list, '', $file_db->count());
    }

    /**
     * 删除文件及记录
     * @param $id
     * @return \think\response\Json
     */
    public function del($id)
    {
        if(Request::instance()->isAjax()) {
            Db::startTrans();
            try {
                $file_db = Db::name('file');
                $map = ['id' => $id];
                $file_info = $file_db->where($map)->find();
                switch ((int)$file_info['scheme_id']) {
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
    public function batchDel($array)
    {
        if(Request::instance()->isAjax()) {
            Db::startTrans();
            try {
                $file_db = Db::name('file');
                foreach ($array as $val) {
                    $file_info = $file_db->where(['id' => $val])->find();
                    switch ((int)$file_info['scheme_id']) {
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

    /**
     * 根据ID获取用户用户名
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getUserName($id)
    {
        return Db::name('user')->where('id', $id)->value('username');
    }

}
