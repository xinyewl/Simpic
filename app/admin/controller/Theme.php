<?php
/**
 * 主题管理
 * User: WispX
 * Date: 2017/9/22
 * Time: 15:28
 * Link: http://gitee.com/wispx
 */
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Config;

class Theme extends Common
{
    public function index($theme = '')
    {
        if(Request::instance()->isAjax()) {
            if(!empty($theme)) {
                if(Db::name('config')->where('key', 'now_theme')->update(['value' => $theme, 'edit_time' => time()])) {
                    return parent::json(1, '成功');
                }
                return parent::json(0, '失败');
            }
        }
        $theme = $this->getTheme($this->conf['theme_path']);
        foreach ($theme as $key => $val) {
            $data[$val] =  Config::load("{$this->conf['theme_path']}/{$val}/config.php", '', 'Theme');
        }
        $this->assign('theme', $data);
        return $this->fetch();
    }

    /**
     * 获取所有主题文件夹
     * @param $dir 父目录路径
     * @return array
     */
    function getTheme($dir)
    {
        $subdirs = array();
        if(!$dh = opendir($dir))
            return $subdirs;
        $i = 0;
        while ($f = readdir($dh))
        {
            if($f =='.' || $f =='..')
                continue;
            $path = $f;
            $subdirs[$i] = $path;
            $i++;
        }
        return $subdirs;
    }
}
