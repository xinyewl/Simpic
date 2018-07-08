--
-- Table structure for table `lk_config`
--
DROP TABLE IF EXISTS `lk_config`;
CREATE TABLE `lk_config` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `key` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '配置项',
  `value` text COLLATE utf8_unicode_ci NOT NULL COMMENT '配置值',
  `text` text COLLATE utf8_unicode_ci NOT NULL COMMENT '可选',
  `notes` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '注释',
  `edit_time` int(11) NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='系统配置';

--
-- Dumping data for table `lk_config`
--
LOCK TABLES `lk_config` WRITE;
INSERT INTO `lk_config` VALUES (1,'web_title','兰空','','网站名称',1506570042),(2,'keywords','兰空,兰空图床,图片储存,免费图床,免费相册,免费图床程序,兰空IMG,www.lskys.cc,lskys','','网站首页关键字',1506570042),(3,'description','免费、快速、稳定、高效、全球CDN加速，支持外链、不限流量，支持粘贴上传、拖放上传，一键复制 markdown 链接的图床','','网站首页描述',1506570042),(4,'captcha_id','','','极检验证ID',1506570043),(5,'private_key','','','极检验证key',1506570043),(6,'upload_max_filesize','5120','','最大上传限制(kb)',1506570042),(7,'upload_max_file_count','10','','单次上传文件个数限制(最低10)',1506570042),(8,'upload_images_ext','jpeg,jpg,png,gif,bmp','','允许上传的图片拓展名',1506570042),(9,'flow_load_mode','1','','流加载方式，0:手动加载,1:下拉加载',1506570042),(10,'img_rows','20','','图片每页显示数量',1506570043),(11,'smtp_host','','','SMTP地址',0),(12,'smtp_port','465','','SMTP端口',0),(13,'smtp_auth','1','','SMTP认证',0),(14,'smtp_user','','','SMTP用户',0),(15,'smtp_pass','','','SMTP密码',0),(16,'smtp_ssl','1','','开启SMTP SSL',0),(17,'now_theme','default','','当前使用主题',0),(18,'custom_style','','','自定义style',1506570043),(19,'version','1.1','','当前版本',0),(20,'reg_close','0','','关闭注册',1506570042),(21,'upload_scheme_id','1','','上传方案',0);
UNLOCK TABLES;

--
-- Table structure for table `lk_file`
--
DROP TABLE IF EXISTS `lk_file`;
CREATE TABLE `lk_file` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` mediumint(8) NOT NULL COMMENT '用户ID',
  `scheme_id` tinyint(1) NOT NULL COMMENT '储存方式ID',
  `name` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `type` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件类型',
  `size` bigint(20) NOT NULL COMMENT '大小(KB)',
  `hash` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '散列值',
  `path` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件路径',
  `upload_time` int(11) NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文件表';

--
-- Dumping data for table `lk_file`
--
LOCK TABLES `lk_file` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `lk_scheme`
--
DROP TABLE IF EXISTS `lk_scheme`;
CREATE TABLE `lk_scheme` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `access_key` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT 'AK',
  `secret_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'SK',
  `bucket_name` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '空间名',
  `domain` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '加速域名',
  `edit_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='储存方案';

--
-- Dumping data for table `lk_scheme`
--
LOCK TABLES `lk_scheme` WRITE;
INSERT INTO `lk_scheme` VALUES (2,'','','','',0),(3,'','','','',0);
UNLOCK TABLES;

--
-- Table structure for table `lk_user`
--
DROP TABLE IF EXISTS `lk_user`;
CREATE TABLE `lk_user` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `email` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT 'email',
  `username` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '密码',
  `login_status` varchar(225) COLLATE utf8_unicode_ci NOT NULL COMMENT '登录状态',
  `login_time` int(11) NOT NULL COMMENT '最后登录时间',
  `login_ip` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '最后登录IP',
  `reg_ip` char(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '注册IP',
  `reg_time` int(11) NOT NULL COMMENT '注册时间',
  `edit_time` int(11) NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';

-- Dump completed on 2017-10-12 17:33:50
