-- MySQL 5.7～8.4：新建表使用统一字符集和排序规则。
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for bd_cms_area
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '区域编号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `pcode` varchar(20) NOT NULL COMMENT '区域父编码',
  `name` varchar(50) NOT NULL COMMENT '区域名称',
  `domain` varchar(100) NOT NULL COMMENT '区域绑定域名',
  `is_default` char(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `area_acode` (`acode`),
  KEY `area_pcode` (`pcode`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='区域管理';

-- ----------------------------
-- Records of bd_cms_area
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, 'en', '0', 'English', '', '0', 'admin', 'Admin', '2023-02-02 21:20:40', '2025-06-27 15:58:10');
INSERT IGNORE INTO `__PREFIX__cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', '', '中文', '', '1', 'Admin', 'admin', '2024-09-22 08:02:37', '2025-06-14 22:56:34');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_company
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点编号',
  `acode` varchar(20) NOT NULL COMMENT '区域代码',
  `name` varchar(100) NOT NULL COMMENT '公司名称',
  `address` varchar(200) NOT NULL COMMENT '公司地址',
  `postcode` varchar(6) NOT NULL COMMENT '邮政编码',
  `contact` varchar(10) NOT NULL COMMENT '公司联系人',
  `mobile` varchar(50) NOT NULL COMMENT '手机号码',
  `phone` varchar(50) NOT NULL COMMENT '电话号码',
  `fax` varchar(50) NOT NULL COMMENT '公司传真',
  `email` varchar(30) NOT NULL COMMENT '电子邮箱',
  `qq` varchar(50) NOT NULL COMMENT '公司QQ',
  `weixin` varchar(100) NOT NULL COMMENT '微信图标',
  `blicense` varchar(20) NOT NULL COMMENT '营业执照代码',
  `other` varchar(200) NOT NULL COMMENT '其他信息',
  PRIMARY KEY (`id`),
  KEY `company_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公司信息';

-- ----------------------------
-- Records of bd_cms_company
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__cms_company` (`id`, `acode`, `name`, `address`, `postcode`, `contact`, `mobile`, `phone`, `fax`, `email`, `qq`, `weixin`, `blicense`, `other`) VALUES (1, 'cn', 'xxx科技有限公司', '苏州市xxx区xx号', '215000', '李先生', '13988886666', '0512-88886666', '0512-88886666', 'admin@badoucms.com', '8888666', '/uploads/20250622/4e18ec9afb89e0e89f1158a6e34ebd92.png', '999123456789', '');
INSERT IGNORE INTO `__PREFIX__cms_company` (`id`, `acode`, `name`, `address`, `postcode`, `contact`, `mobile`, `phone`, `fax`, `email`, `qq`, `weixin`, `blicense`, `other`) VALUES (6, 'en', 'test english', '', '', '', '', '', '', '', '', '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_content
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域',
  `scode` varchar(20) NOT NULL COMMENT '内容栏目',
  `subscode` varchar(20) NOT NULL COMMENT '副栏目',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `titlecolor` varchar(7) NOT NULL COMMENT '标题颜色',
  `subtitle` varchar(100) NOT NULL COMMENT '副标题',
  `filename` varchar(50) NOT NULL COMMENT '自定义文件名',
  `author` varchar(30) NOT NULL COMMENT '作者',
  `source` varchar(30) NOT NULL COMMENT '来源',
  `outlink` varchar(100) NOT NULL COMMENT '外链地址',
  `date` datetime NOT NULL COMMENT '发布日期',
  `ico` varchar(100) NOT NULL COMMENT '缩略图',
  `pics` varchar(1000) NOT NULL COMMENT '多图片',
  `picstitle` varchar(1000) NOT NULL COMMENT '多图片标题',
  `content` mediumtext NOT NULL COMMENT '内容',
  `tags` varchar(500) NOT NULL COMMENT 'tag关键字',
  `enclosure` varchar(100) NOT NULL COMMENT '附件',
  `keywords` varchar(200) NOT NULL COMMENT '关键字',
  `description` varchar(500) NOT NULL COMMENT '描述',
  `sorting` int(10) unsigned NOT NULL DEFAULT '255' COMMENT '内容排序',
  `status` char(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `istop` char(1) NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `isrecommend` char(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `isheadline` char(1) NOT NULL DEFAULT '0' COMMENT '是否头条',
  `visits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问数',
  `likes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `oppose` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '反对数',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(20) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `gtype` char(1) NOT NULL DEFAULT '4',
  `gid` varchar(20) NOT NULL DEFAULT '',
  `gnote` varchar(100) NOT NULL DEFAULT '',
  `aucode` int(10) DEFAULT NULL COMMENT '语言统一code',
  `custom_tpl` varchar(255) DEFAULT '' COMMENT '详情页模版',
  PRIMARY KEY (`id`),
  KEY `content_scode` (`scode`),
  KEY `content_subscode` (`subscode`),
  KEY `content_acode` (`acode`),
  KEY `content_filename` (`filename`),
  KEY `content_date` (`date`),
  KEY `content_sorting` (`sorting`),
  KEY `content_status` (`status`),
  KEY `content_aucode` (`aucode`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS文章内容';


-- ----------------------------
-- Table structure for bd_cms_content_ext
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_ext` (
  `extid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contentid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`extid`),
  KEY `content_ext_contentid` (`contentid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS文章内容-自定义字段';


-- ----------------------------
-- Table structure for bd_cms_content_sort
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_sort` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `mcode` varchar(20) NOT NULL COMMENT '内容模型编码',
  `pcode` varchar(20) NOT NULL COMMENT '父编码',
  `scode` varchar(20) NOT NULL COMMENT '分类编码',
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `listtpl` varchar(50) NOT NULL COMMENT '列表页模板',
  `contenttpl` varchar(50) NOT NULL COMMENT '内容页模板',
  `status` char(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `outlink` varchar(100) NOT NULL COMMENT '转外链接',
  `subname` varchar(200) NOT NULL COMMENT '附加名称',
  `def1` varchar(1000) NOT NULL COMMENT '栏目描述1',
  `def2` varchar(1000) NOT NULL COMMENT '栏目描述2',
  `def3` varchar(1000) NOT NULL COMMENT '栏目描述3',
  `ico` varchar(100) NOT NULL COMMENT '分类缩略图',
  `pic` varchar(100) NOT NULL COMMENT '分类大图',
  `title` varchar(100) NOT NULL COMMENT 'seo标题',
  `keywords` varchar(200) NOT NULL COMMENT '分类关键字',
  `description` varchar(500) NOT NULL COMMENT '分类描述',
  `filename` varchar(30) NOT NULL COMMENT '自定义文件名',
  `sorting` int(10) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `gtype` char(1) NOT NULL DEFAULT '4',
  `gid` varchar(20) NOT NULL DEFAULT '',
  `gnote` varchar(100) NOT NULL DEFAULT '',
  `aucode` varchar(100) NOT NULL DEFAULT '' COMMENT '语言统一code',
  PRIMARY KEY (`id`),
  UNIQUE KEY `scode` (`scode`),
  KEY `pcode` (`pcode`),
  KEY `acode` (`acode`),
  KEY `mcode` (`mcode`),
  KEY `filename` (`filename`),
  KEY `sorting` (`sorting`),
  KEY `aucode` (`aucode`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='栏目表';


-- ----------------------------
-- Table structure for bd_cms_extfield
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_extfield` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `mcode` varchar(20) NOT NULL COMMENT '模型编码',
  `name` varchar(30) NOT NULL COMMENT '字段名称',
  `type` char(5) NOT NULL COMMENT '字段类型',
  `value` varchar(500) NOT NULL COMMENT '单选或多选值',
  `description` varchar(30) NOT NULL COMMENT '描述文本',
  `sorting` int(11) NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `extfield_mcode` (`mcode`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='模型字段';


-- ----------------------------
-- Table structure for bd_cms_form
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_form` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `fcode` varchar(20) NOT NULL COMMENT '表单编码',
  `form_name` varchar(30) NOT NULL COMMENT '表单名称',
  `table_name` varchar(30) NOT NULL COMMENT '表名称',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_fcode` (`fcode`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义表单';

-- ----------------------------
-- Records of bd_cms_form
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '在线留言', 'cms_message', 'Admin', 'Admin', '2025-06-12 08:50:10', '2025-06-12 08:50:10');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_form_data
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_form_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '区域编码',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `tel` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '电话',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='调研';

-- ----------------------------
-- Table structure for bd_cms_form_field
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_form_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `fcode` varchar(20) NOT NULL COMMENT '表单编码',
  `name` varchar(30) NOT NULL COMMENT '字段名称',
  `length` int(10) unsigned NOT NULL COMMENT '字段长度',
  `required` char(1) NOT NULL DEFAULT '0' COMMENT '是否必填',
  `description` varchar(30) NOT NULL COMMENT '描述文本',
  `sorting` int(10) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `form_field_fcode` (`fcode`),
  KEY `form_field_sorting` (`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义表单-字段';


-- ----------------------------
-- Table structure for bd_cms_label
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(100) DEFAULT 'cn',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `value` text NOT NULL COMMENT '值',
  `type` char(3) NOT NULL DEFAULT '1' COMMENT '字段类型',
  `description` varchar(30) NOT NULL COMMENT '描述',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(20) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='定制标签';


-- ----------------------------
-- Table structure for bd_cms_link
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `gid` int(10) unsigned NOT NULL COMMENT '分组序号',
  `name` varchar(50) NOT NULL COMMENT '链接名称',
  `link` varchar(100) NOT NULL COMMENT '跳转链接',
  `logo` varchar(100) DEFAULT NULL COMMENT '图片地址',
  `sorting` int(11) NOT NULL COMMENT '排序',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `link_acode` (`acode`),
  KEY `link_gid` (`gid`),
  KEY `link_sorting` (`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='友情链接';


-- ----------------------------
-- Table structure for bd_cms_member_comment
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_member_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `contentid` int(10) unsigned NOT NULL COMMENT '内容ID',
  `comment` varchar(1000) NOT NULL DEFAULT '' COMMENT '评论',
  `uid` int(10) unsigned NOT NULL COMMENT '评论人',
  `puid` int(10) unsigned NOT NULL COMMENT '被评论人',
  `likes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `oppose` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '反对数',
  `status` char(1) NOT NULL DEFAULT '' COMMENT '状态',
  `user_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '用户IP',
  `user_os` varchar(30) NOT NULL DEFAULT '' COMMENT '操作系统',
  `user_bs` varchar(30) NOT NULL DEFAULT '' COMMENT '浏览器',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_user` varchar(30) NOT NULL,
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `member_comment_pid` (`pid`),
  KEY `member_comment_contentid` (`contentid`),
  KEY `member_comment_uid` (`uid`),
  KEY `member_comment_puid` (`puid`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章评论表';


-- ----------------------------
-- Table structure for bd_cms_message
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `contacts` varchar(10) DEFAULT NULL COMMENT '联系人',
  `mobile` varchar(12) DEFAULT NULL COMMENT '联系电话',
  `content` varchar(500) DEFAULT NULL COMMENT '留言内容',
  `user_ip` varchar(45) NOT NULL DEFAULT '0' COMMENT 'IP地址',
  `user_os` varchar(45) NOT NULL COMMENT '操作系统',
  `user_bs` varchar(45) NOT NULL COMMENT '浏览器',
  `recontent` varchar(500) NOT NULL COMMENT '回复内容',
  `status` char(1) NOT NULL DEFAULT '1' COMMENT '是否前台显示',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID(默认匿名用户ID)',
  PRIMARY KEY (`id`),
  KEY `message_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='在线留言';


-- ----------------------------
-- Table structure for bd_cms_model
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_model` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `mcode` varchar(20) NOT NULL COMMENT '模型编号',
  `name` varchar(50) NOT NULL COMMENT '模型名称',
  `type` char(1) NOT NULL DEFAULT '2' COMMENT '是否列表类型',
  `urlname` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL名称',
  `listtpl` varchar(50) NOT NULL COMMENT '列表页模板',
  `contenttpl` varchar(50) NOT NULL COMMENT '内容页模板',
  `status` char(1) NOT NULL DEFAULT '1' COMMENT '模型状态',
  `issystem` char(1) NOT NULL DEFAULT '0' COMMENT '系统模型',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mcode` (`mcode`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='模型管理';

-- ----------------------------
-- Records of bd_cms_model
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '专题', '1', 'about', '', 'about.html', '1', '1', 'admin', 'admin', '2018-04-11 17:16:01', '2025-06-27 15:32:59');
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '新闻', '2', 'list', 'newslist.html', 'news.html', '1', '1', 'admin', 'Admin', '2018-04-11 17:17:16', '2025-06-27 15:32:58');
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '3', '产品', '2', 'list', 'productlist.html', 'product.html', '1', '0', 'admin', 'admin', '2018-04-11 17:17:46', '2025-06-27 15:32:57');
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, '4', '案例', '2', 'list', 'caselist.html', 'case.html', '1', '0', 'admin', 'admin', '2018-04-11 17:19:53', '2025-06-27 15:32:55');
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, '5', '招聘', '2', 'list', 'joblist.html', 'job.html', '1', '0', 'admin', 'admin', '2018-04-11 17:24:34', '2025-06-27 15:32:54');
INSERT IGNORE INTO `__PREFIX__cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (59, '6', '文档', '2', 'list', 'productlist.html', 'product.html', '1', '0', 'admin', 'admin', '2025-07-01 21:19:01', '2025-07-02 08:08:32');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_site
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_site` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点编号',
  `acode` varchar(20) NOT NULL COMMENT '区域代码',
  `title` varchar(100) NOT NULL COMMENT '站点标题',
  `subtitle` varchar(200) NOT NULL COMMENT '站点副标题',
  `domain` varchar(50) NOT NULL COMMENT '站点地址',
  `logo` varchar(100) NOT NULL COMMENT '站点LOGO地址',
  `keywords` varchar(200) NOT NULL COMMENT '站点关键字',
  `description` varchar(500) NOT NULL COMMENT '站点描述',
  `icp` varchar(30) NOT NULL COMMENT '站点备案',
  `theme` varchar(30) NOT NULL COMMENT '站点主题',
  `statistical` varchar(500) NOT NULL COMMENT '站点统计码',
  `copyright` varchar(200) NOT NULL COMMENT '版权信息',
  PRIMARY KEY (`id`),
  KEY `site_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='站点配置';

-- ----------------------------
-- Records of bd_cms_site
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (1, 'cn', 'badoucms网站管理系统', '永久开源免费的PHP企业网站开发建设管理系统', '', '/uploads/20250622/83f3aee5bdea54e652ef497fc45f4ab7.png', 'cms,免费cms,开源cms,企业cms,建站cms', 'BadouCMS是一套全新内核且永久开源免费的PHP企业网站开发建设管理系统，是一套高效、简洁、 强悍的可免费商用的PHP CMS源码，能够满足各类企业网站开发建设的需要。系统采用简单到想哭的模板标签，只要懂HTML就可快速开发企业网站。官方提供了大量网站模板免费下载和使用，将致力于为广大开发者和企业提供最佳的网站开发建设解决方案。', '苏ICP备88888888号', 'default', '', 'BadouCMS All Rights Reserved.');
INSERT IGNORE INTO `__PREFIX__cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (2, 'en', '1111', '123', '123', '', '', '', '', 'default', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_slide
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_slide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `gid` int(10) unsigned NOT NULL COMMENT '分组',
  `pic` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `link` varchar(100) NOT NULL COMMENT '跳转链接',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `subtitle` varchar(100) NOT NULL DEFAULT '' COMMENT '副标题',
  `sorting` int(11) NOT NULL COMMENT '排序',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `slide_acode` (`acode`),
  KEY `slide_gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='轮播图片';


-- ----------------------------
-- Table structure for bd_cms_tags
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `link` varchar(200) NOT NULL COMMENT '链接',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tags_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章内链';


-- 2.0.8 --
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_label' AND COLUMN_NAME = 'acode'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_label` ADD COLUMN `acode` varchar(100) DEFAULT ''cn'' COMMENT ''区域编码'' AFTER `id`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;


-- 2.0.10 --
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_content_sort' AND COLUMN_NAME = 'aucode'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_content_sort` ADD COLUMN `aucode` varchar(100) DEFAULT '''' COMMENT ''语言统一code'' AFTER `gnote`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;

-- 2.1.0 --
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_area' AND COLUMN_NAME = 'status'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_area` ADD COLUMN `status` int(1) NOT NULL DEFAULT ''1'' COMMENT ''状态'' AFTER `update_time`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_content' AND COLUMN_NAME = 'aucode'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_content` ADD COLUMN `aucode` int(10) DEFAULT NULL COMMENT ''语言统一code'' AFTER `gnote`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_content' AND INDEX_NAME = 'content_aucode'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_content` ADD INDEX `content_aucode` (`aucode`) USING BTREE'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;

-- 2.1.4 --
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__cms_content' AND COLUMN_NAME = 'custom_tpl'),
  'DO 0',
  'ALTER TABLE `__PREFIX__cms_content` ADD COLUMN `custom_tpl` varchar(255) DEFAULT '''' COMMENT ''详情页模版'' AFTER `aucode`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;

-- 2.2.2 --
ALTER TABLE `__PREFIX__cms_label` MODIFY COLUMN `type` char(3) NOT NULL DEFAULT '1' COMMENT '字段类型';
ALTER TABLE `__PREFIX__cms_label` MODIFY COLUMN `value` text NOT NULL COMMENT '值';

-- 2.2.3 --
ALTER TABLE `__PREFIX__cms_member_comment` MODIFY COLUMN `user_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '用户IP';
ALTER TABLE `__PREFIX__cms_message` MODIFY COLUMN `user_ip` varchar(45) NOT NULL DEFAULT '0' COMMENT 'IP地址';


-- ----------------------------
-- 2.4.0：CMS Agent 翻译检查记录（安装和升级共用）
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_agent_translation_review` (
  `pair_key` char(64) CHARACTER SET ascii NOT NULL PRIMARY KEY,
  `fields` text NOT NULL COMMENT '已检查字段的源文和目标值指纹及结果',
  `updated_at` bigint NOT NULL
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS Agent 翻译检查记录';
