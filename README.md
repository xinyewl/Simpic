## Simpic

本项目的起因是因为我想自建一个个人图床，在网上找了好久都没有合适的程序，比较好的也就Chevereto和ImgURL，可是这两个程序的上传路径都不符合我的口味，所以就舍弃了，我想要的是SM.MS这样的上传模式，本来想着自己写一个的，后来偶然在码云上面发现了兰空图床，发现很符合我的口味，于是就拿来修改了一下，终于改成我想要的样子了，看了下兰空图床的开源协议，是允许修改版权的，于是就有了 **Simpic**

## 原程序（停止维护）

 - 项目：兰空图床
 - 作者：WispX
 - 作者博客：https://www.wispx.cn/
 - 源项目：https://gitee.com/wispx/lsky

## 新特性

 - 修改了上传路径（模仿SM.MS格式）
 - 更新了Layui框架
 - 删除授权代码
 - 美化首页模板

## 前台演示

![](https://img.ikxin.com/2018/07/19/5b50624f3701f.png)

## 后台演示

![](https://img.ikxin.com/2018/07/19/5b505b3449550.png)

## 演示站

[Notte图床](https://i.5e.cx/ "Notte图床")（不开放注册）

## 伪静态规则

### Apache

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?s=$1 [QSA,PT,L]
</IfModule>
```

## Nginx

```
location / {
	if (!-e $request_filename) {
		rewrite ^(.*)$ /index.php?s=$1 last; break;
	}
}
```

## 反馈建议

你可以直接提交lssues来说明你的问题，如果有建议请戳[这里](https://github.com/xinyewl/Simpic/issues "这里")提交

## 更新记录

https://github.com/xinyewl/Simpic/releases