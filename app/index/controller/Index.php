<?php
namespace app\index\controller;

use think\Config;
use think\Db;
use think\Request;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Upyun\Upyun;

class Index extends Common
{

    public function index()
    {
        if(Request::instance()->isAjax()) {
            if(!$this->user) return $this->response(0, '你没有登录，无法上传图片');
            $file = Request::instance()->file('img');
            switch ((int)$this->conf['upload_scheme_id']) {
                // 本地
                case 1:
                    return $this->localUpload($file);
                    break;
                // 七牛云
                case 2:
                    return $this->qiniuUpload($file);
                    break;
                // 又拍云
                case 3:
                    return $this->upyunUpload($file);
                    break;
                // TODO 阿里OSS
                case 4:
                    break;
                default:
                    return $this->localUpload($file);
                    break;
            }
        }
        $this->conf['web_title'] = "{$this->conf['web_title']} - 免费的图片托管平台";
        $this->assign('conf', $this->conf);
        return $this->fetch();
    }

    /**
     * 本地上传
     * @param $file TP上传文件资源
     * @return string
     */
    private function localUpload($file)
    {
        if($file) {
            $date = date('Ymd', time());
            $info = $file->validate(['size'=> $this->conf['upload_max_filesize'] * 1024, 'ext'=> $this->conf['upload_images_ext']])->rule('uniqid')->move("{$this->conf['file_path']}/{$this->user['id']}/" . $date);
            if($info) {
                $file_path = str_replace("\\", "/", Config::get('web.domain') . "/pic/{$this->user['id']}/{$date}/{$info->getSaveName()}");
                return $this->saveImg(
                    1,
                    $info->getFilename(),
                    $file->getInfo('type'),
                    $info->getSize(),
                    $info->hash('sha1'),
                    str_replace("\\", "/", "{$this->user['id']}/{$date}/{$info->getSaveName()}"),
                    $file_path
                );
            } else {
                return $this->response(0, $file->getError());
            }
        }
    }

    /**
     * 七牛云上传
     * @param $file TP文件资源
     */
    private function qiniuUpload($file)
    {
        // TODO 图片合法性验证
        Db::startTrans();
        try {
            // 初始化签权对象
            $auth = new Auth($this->scheme['qiniu']['access_key'], $this->scheme['qiniu']['secret_key']);
            // 生成上传Token
            $token = $auth->uploadToken($this->scheme['qiniu']['bucket_name']);
            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();
            // 文件名
            $file_name = uniqid() . ".{$this->getFileExt($file->getInfo('name'))}";
            // 文件夹名
            $file_dir = "{$this->user['id']}/" . date('Y-m-d', time()) . "/";
            // 文件路径
            $file_path = $file_dir . $file_name;
            // 上传文件
            $upload = $uploadMgr->putFile($token, $file_path, $file->getInfo('tmp_name'));
            if($upload) {
                // 获取文件信息
                $config = new \Qiniu\Config();
                $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
                list($file_info, $err) = $bucketManager->stat($this->scheme['qiniu']['bucket_name'], $file_path);
                if ($err) {
                    //print_r($err);
                } else {
                    $this->saveImg(
                        $this->scheme['qiniu']['id'],
                        $file_name,
                        $file_info['mimeType'],
                        $file_info['fsize'],
                        $file_info['hash'],
                        $file_path,
                        "{$this->scheme['qiniu']['domain']}/{$file_path}"
                    );
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            die("Code {$e->getCode()} Msg: {$e->getMessage()}");
        }
    }

    private function upyunUpload($file)
    {
        // TODO 图片合法性验证
        Db::startTrans();
        try {
            // 创建实例
            $bucketConfig = new \Upyun\Config($this->scheme['upyun']['bucket_name'], $this->scheme['upyun']['access_key'], $this->scheme['upyun']['secret_key']);
            $client = new Upyun($bucketConfig);
            // 文件名
            $file_name = uniqid() . ".{$this->getFileExt($file->getInfo('name'))}";
            // 文件夹名
            $file_dir = "{$this->user['id']}/" . date('Y-m-d', time()) . "/";
            // 文件路径
            $file_path = $file_dir . $file_name;
            // 读文件
            $file_names = fopen($file->getInfo('tmp_name'), 'r');
            $res = $client->write($file_path, $file_names);
            if(count($res) > 0) {
                $this->saveImg(
                    $this->scheme['upyun']['id'],
                    $file_name,
                    $file->getInfo('type'),
                    $file->getInfo('size'),
                    $file->hash('sha1'),
                    $file_path,
                    "{$this->scheme['upyun']['domain']}/{$file_path}"
                );
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            die("Code {$e->getCode()} Msg: {$e->getMessage()}");
        }

    }

    /**
     * 保存图片数据
     * @param $scheme_id 文件储存方式，1：本地，2：七牛，3：又拍，4：阿里OSS
     * @param $name 文件名
     * @param $type 文件类型
     * @param $size 文件大小(kb)
     * @param $hash 文件hash值
     * @param $path 文件相对路径
     * @param $paths 文件绝对路径
     * @param $url 文件访问url
     * @return string
     */
    private function saveImg($scheme_id, $name, $type, $size, $hash, $path, $paths)
    {
        $ins = Db::name('file')->insert([
            'user_id'       => $this->user['id'],
            'scheme_id'     => $scheme_id,
            'name'          => $name,
            'type'          => $type,
            'size'          => $size,
            'hash'          => $hash,
            'path'          => $path,
            'upload_time'   => time()
        ]);
        if($ins) {
            return $this->response(true, 'success', [
                'ip'    => Request::instance()->ip(),
                'size'  => $size,
                'url'   => $paths
            ]);
        } else {
            unlink($path);
            return $this->response(0, '文件信息保存失败');
        }
    }

    /**
     * 获取文件后缀
     * @param $file 文件名
     * @return mixed
     */
    private function getFileExt($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * 返回上传数据
     * @param $code bool 状态
     * @param $data array 数据
     * @return string
     */
    private function response($code, $msg = '', array $data = [])
    {
        $response = [
            'code'  => is_numeric($code) ? $code : ($code ? 'success' : 'error'),
        ];
        if(!empty($msg)) $response['msg'] = $msg;
        if(count($data) > 0) $response['data'] = $data;
        echo(json_encode($response));
    }
}
