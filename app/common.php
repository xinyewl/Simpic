<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 判断是否是https
 * @return boolean
 */
function is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}

/**
 * 二维数组去空格
 * @author WispX
 * @date 2017-09-04 9:44
 * @param array $array
 * @return string|boolean
 */
function trimArray($array)
{
    if(count($array) > 0) {
        foreach ($array as $key => &$val) {
            $array[$key] = trim($val);
        }
        return $array;
    }
    return false;
}

/**
 * 格式化时间
 * @param  [type] $unixTime [description]
 * @return [type]           [description]
 */
function formatTime($unixTime)
{
    $showTime = date('Y', $unixTime) . "年" . date('n', $unixTime) . "月" . date('j', $unixTime) . "日";
    if (date('Y', $unixTime) == date('Y')) {
        $showTime = date('n', $unixTime) . "月" . date('j', $unixTime) . "日 " . date('H:i', $unixTime);
        if (date('n.j', $unixTime) == date('n.j')) {
            $timeDifference = time() - $unixTime + 1;
            if ($timeDifference < 30) {
                return "刚刚";
            }
            if ($timeDifference >= 30 && $timeDifference < 60) {
                return $timeDifference . "秒前";
            }
            if ($timeDifference >= 60 && $timeDifference < 3600) {
                return floor($timeDifference / 60) . "分钟前";
            }
            return date('H:i', $unixTime);
        }
        if (date('n.j', ($unixTime + 86400)) == date('n.j')) {
            return "昨天 " . date('H:i', $unixTime);
        }
    }
    return $showTime;
}

/**
 * 获取系统配置
 * @return bool
 */
function getSystemConf($key = '')
{
    if(!empty($key)) {
        return \think\Db::name('config')->where('key', $key)->value('value');
    } else {
        $conf = \think\Db::name('config')->select();
        if($conf) {
            foreach ($conf as $val) {
                $_conf[$val['key']] = $val['value'];
            }
            return $_conf;
        }
        return false;
    }
}

/**
 * 生成验证码
 * @param number $length
 * @return string
 */
function getCode($length = 7)
{
    return substr(str_shuffle("012345678901234567890123456789"), 0, $length);
}

/**
 * curl
 * @param $url 请求地址
 * @param array $data 请求数据
 * @param array $header 请求header头
 * @param int $timeout 超时时间
 * @return mixed
 */
function curl($url, array $data = [], array $header = [], $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch);
    if($error = curl_error($ch)) {
        return $error;
    }
    curl_close($ch);
    return $response;
}

/**
 * 获取所有上传方案
 */
function getSchemeList()
{
    $scheme_list = \think\Db::name('scheme')->select();
    foreach ($scheme_list as $key => $val) {
        switch ($val['id']) {
            // 七牛云
            case 2:
                $scheme['qiniu'] = $val; break;
            // 又拍云
            case 3:
                $scheme['upyun'] = $val; break;
            // 阿里云OSS
            case 4:
                $scheme['alioss'] = $val; break;
        }
    }
    return $scheme;
}