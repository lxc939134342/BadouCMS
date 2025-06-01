/*
 Navicat Premium Dump SQL

 Source Server         : 本地电脑
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44)
 Source Host           : localhost:3306
 Source Schema         : badouadmin

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44)
 File Encoding         : 65001

 Date: 01/06/2025 09:54:11
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bd_admin
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin`;
CREATE TABLE `bd_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
  `password` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '手机号码',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `login_time` bigint(16) DEFAULT NULL COMMENT '登录时间',
  `login_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '登录IP',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ----------------------------
-- Records of bd_admin
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `password`, `avatar`, `email`, `mobile`, `loginfailure`, `login_time`, `login_ip`, `create_time`, `update_time`, `token`, `status`) VALUES (1, 'admin', 'Admin', '$2y$10$e6V08PMnfE39nI5jYwhkJOlyZYkABmloMxuTeYsbMiIuh6Tuvt5We', 'http://fastadmin.test/assets/img/avatar.png', 'admin@admin.com', '', 0, 1748735091, '127.0.0.1', 1491635035, 1748735091, 'a3e7f054-724b-4e64-973c-983f393a78f6', 'normal');
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `password`, `avatar`, `email`, `mobile`, `loginfailure`, `login_time`, `login_ip`, `create_time`, `update_time`, `token`, `status`) VALUES (2, 'test', '管理员', 'e97ff5ad73c6eb40a53b24f762294e7e', '/assets/img/avatar.png', '123@qq.com', '', 0, 1658394230, '192.168.32.1', 1656558031, 1658394258, '', 'normal');
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_group`;
CREATE TABLE `bd_admin_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) DEFAULT '' COMMENT '组名',
  `rules` text NOT NULL COMMENT '规则ID',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` varchar(30) DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='分组表';

-- ----------------------------
-- Records of bd_admin_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `create_time`, `update_time`, `status`) VALUES (1, 0, 'Admin group', '*', 1491635035, 1491635035, 'normal');
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_group_access
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_group_access`;
CREATE TABLE `bd_admin_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '会员ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限分组表';

-- ----------------------------
-- Records of bd_admin_group_access
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_group_access` (`uid`, `group_id`) VALUES (1, 1);
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_log`;
CREATE TABLE `bd_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '管理员名字',
  `url` varchar(1500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '日志标题',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'User-Agent',
  `create_time` bigint(16) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员日志表';

-- ----------------------------
-- Records of bd_admin_log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_rule
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_rule`;
CREATE TABLE `bd_admin_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('0','1') NOT NULL DEFAULT '1' COMMENT '类型:0=菜单目录,1=菜单项',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) DEFAULT '' COMMENT '规则名称',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `url` varchar(255) DEFAULT '' COMMENT '规则URL',
  `condition` varchar(255) DEFAULT '' COMMENT '条件',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `menutype` enum('_iframe','_blank') DEFAULT NULL COMMENT '菜单类型',
  `extend` varchar(255) DEFAULT '' COMMENT '扩展属性',
  `py` varchar(30) DEFAULT '' COMMENT '拼音首字母',
  `pinyin` varchar(100) DEFAULT '' COMMENT '拼音',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COMMENT='节点表';

-- ----------------------------
-- Records of bd_admin_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1, '1', 0, 'dashboard', 'Dashboard', 'layui-icon layui-icon-console', '', '', '', 1, '_iframe', '', '', '', 1747194640, 1748739820, 99999, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (5, '0', 0, 'auth', 'Auth', 'fa fa-group', '', '', '', 1, '_iframe', '', 'qxgl', 'quanxianguanli', 1491635035, 1747192496, 99, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (12, '1', 5, 'auth.rule', 'Rule', 'fa fa-bars', '', '', 'Rule tips', 1, NULL, '', 'cdgz', 'caidanguize', 1491635035, 1747187478, 104, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (51, '0', 12, 'auth.rule/index', 'View', 'fa fa-circle-o', '', '', 'Rule tips', 0, NULL, '', '', '', 1491635035, 1747147369, 103, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (52, '0', 12, 'auth.rule/add', 'Add', 'fa fa-circle-o', '', '', 'Rule tips', 0, NULL, '', '', '', 1491635035, 1491635035, 103, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (74, '1', 5, 'auth.group', 'Group', 'fa fa-bars', '', '', 'Rule tips', 1, NULL, '', 'cdgz', 'caidanguize', 1491635035, 1491635035, 104, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (75, '1', 5, 'auth.admin', '管理员', '', '', '', '', 1, '_iframe', '', '', '', 1747141652, 1747185809, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (76, '1', 75, 'rule.admin/index', 'View', '', '', '', '', 1, '_iframe', '', '', '', 1747142286, 1747142286, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (78, '1', 0, 'module', '插件市场', 'layui-icon layui-icon-component', '', '', '', 1, '_iframe', '', '', '', 1747228616, 1747228616, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (79, '0', 0, 'cms', 'CMS', 'layui-icon layui-icon-template', '', '', '', 1, '_iframe', '', '', '', 1747315213, 1748258741, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (80, '1', 79, 'cms.models', '模型管理', 'layui-icon layui-icon-template-1', '', '', '', 1, '_iframe', '', '', '', 1747316111, 1747316187, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (81, '1', 80, 'cms.models/index', 'View', '', '', '', '', 0, '_iframe', '', '', '', 1747316265, 1747316294, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (82, '0', 79, 'cms.content', '文章管理', 'layui-icon layui-icon-form', '', '', '', 1, '_iframe', '', '', '', 1747475630, 1747475630, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (90, '1', 82, 'cms.single/index/mcode/1', '专题内容', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1748572542, 1748572542, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (91, '1', 82, 'cms.content/index/mcode/2', '新闻内容', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1748572687, 1748572687, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (92, '0', 0, 'general', 'General', 'layui-icon layui-icon-set-fill', '', '', '', 1, '_iframe', '', '', '', 1748739734, 1748739830, 99998, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (93, '1', 92, 'general.attachment', 'Attachment', '', '', '', '', 1, '_iframe', '', '', '', 1748739781, 1748739964, 0, 'normal');
COMMIT;

-- ----------------------------
-- Table structure for bd_attachment
-- ----------------------------
DROP TABLE IF EXISTS `bd_attachment`;
CREATE TABLE `bd_attachment` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '类别',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '高度',
  `imagetype` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文件名称',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '透传数据',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建日期',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `upload_time` bigint(16) DEFAULT NULL COMMENT '上传时间',
  `storage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

-- ----------------------------
-- Records of bd_attachment
-- ----------------------------
BEGIN;
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (1, '', 1, 0, '/assets/img/qrcode.png', '150', '150', 'png', 0, 'qrcode.png', 21859, 'image/png', '', 1491635035, 1491635035, 1491635035, 'local', '17163603d0263e4838b9387ff2cd4877e8b018f6');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (2, '', 1, 0, '/uploads/20220624/33e400442ce20a37b40c8108d0f5b1b8.jpeg', '650', '325', 'jpeg', 0, '7361d799edde1f89c195011ca45333b9 (2) (1).jpeg', 30317, 'image/jpeg', '', 1656061389, 1656061389, 1656061389, 'local', '363b0ee42228bb44117db41165ae4b9eb785b6ee');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (3, '', 1, 0, '/uploads/20220624/ab2254f112427928f674bd50991e30e3.jpeg', '550', '347', 'jpeg', 0, '176f16143439c8f50a9ab3f059e193d4.jpeg', 40269, 'image/jpeg', '', 1656064841, 1656064841, 1656064841, 'local', 'c5c09dc1e8b8641836234af40463b1b382fe0307');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (4, '', 1, 0, '/uploads/20220625/8061940ca6545a6d5ea536f0c4a10fea.png', '828', '1792', 'png', 0, 'WX20220624-211816@2x.png', 2258448, 'image/png', '', 1656146356, 1656146356, 1656146356, 'local', '14fbefbb7a61bbab93fa7caa1c52d6c9e9f79490');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (5, '', 1, 0, '/uploads/20220707/f57ebce8a72b823912904fe76eda0909.png', '192', '192', 'png', 0, 'avatar.png', 15135, 'image/png', '', 1657176705, 1657176705, 1657176705, 'local', '9c39ed36543710c1ce4de7e0e56391c37ae58d56');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (6, '', 1, 0, '/uploads/20220721/7610180bc709f550bac83d2b950eeaa7.jpg', '400', '400', 'jpg', 0, '未标题-1.jpg', 36802, 'image/jpeg', '', 1658416473, 1658416473, 1658416473, 'local', '7c0c76d62193df3b6958d181fa20450a357363e3');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (7, '', 1, 0, '/uploads/20220721/cb0dee02d16f07ee4d8f089ca798aea1.jpg', '400', '400', 'jpg', 0, '未标题-1.jpg', 30723, 'image/jpeg', '', 1658416753, 1658416753, 1658416753, 'local', 'e25bbcce7c46839e68e25f37f4c1b5c98195834a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (8, '', 1, 0, '/uploads/20220721/f546e0f413b18df1776a3f64deabea38.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 33678, 'image/jpeg', '', 1658416931, 1658416931, 1658416931, 'local', '5e3624065f4dd064b3d7311c224674da34ed280a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (9, '', 1, 0, '/uploads/20220721/fb37988450be0384324e913632395cf4.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 30172, 'image/jpeg', '', 1658417209, 1658417209, 1658417208, 'local', '38e7672a2d24671956a5be726f4d4779f266c2fc');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (10, '', 1, 0, '/uploads/20220721/f4002f8ca77f1a0a8fe9d833b74d1c45.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 31433, 'image/jpeg', '', 1658417323, 1658417323, 1658417323, 'local', '065d6f106b1b009976a68fc38e0bc1b44c936ae4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (11, '', 1, 0, '/uploads/20220721/4f501d46640fe6f3e9a730ae8068eb9a.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 31167, 'image/jpeg', '', 1658417413, 1658417413, 1658417413, 'local', '7b2abaa0d7a70c0dce7588a48b149f669b72d442');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (12, '', 1, 0, '/uploads/20220721/fc8d366d56faede0fb09f374058c8810.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 29866, 'image/jpeg', '', 1658417505, 1658417505, 1658417505, 'local', 'd7843cd78e34e9c717ae314e0f45405fb10b0d6b');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (13, '', 1, 0, '/uploads/20220721/25e686f73e23794c163b33a6166cf384.jpg', '400', '350', 'jpg', 0, '未标题-1.jpg', 42232, 'image/jpeg', '', 1658417626, 1658417626, 1658417626, 'local', '7eace5ab6febe27e90e7658a7c88247a739472e7');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (14, '', 1, 0, '/uploads/20220721/1bd740c262831b0485fb66c15e46648b.jpg', '400', '350', 'jpg', 0, '相拥.jpg', 33370, 'image/jpeg', '', 1658418095, 1658418095, 1658418095, 'local', '24bb34aa4e4c8f7f2c56b418b19e0174cfb4a269');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (15, '', 1, 0, '/uploads/20220721/c64517e0c8c43df6e589567b0187f041.jpg', '400', '350', 'jpg', 0, '公主.jpg', 37002, 'image/jpeg', '', 1658418271, 1658418271, 1658418271, 'local', 'a1481bd073ced6086114db55db2f43de3a9272fe');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (16, '', 1, 0, '/uploads/20220721/79a03ec5fe813fee670c9cd3819d3fa4.jpg', '400', '350', 'jpg', 0, '婚礼进行典.jpg', 34467, 'image/jpeg', '', 1658418373, 1658418373, 1658418373, 'local', '7aa3e847ee0801fd1fe8ce472f6b214b3b34ce9b');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (17, '', 1, 0, '/uploads/20220721/7226590abde65dafaedde112cc868007.jpg', '400', '350', 'jpg', 0, '我愿意.jpg', 48056, 'image/jpeg', '', 1658418524, 1658418524, 1658418524, 'local', 'badf8da572d17583f0f2fa674947fe526ecbd4c4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (18, '', 1, 0, '/uploads/20220721/3cf21ea560f13c0d7622aaf385f6b105.jpg', '400', '350', 'jpg', 0, '心港湾.jpg', 34491, 'image/jpeg', '', 1658418674, 1658418674, 1658418674, 'local', 'ba2b5494cdb6b7204aeb061d556fe311137277bd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (19, '', 1, 0, '/uploads/20220721/fd95d9154362da72525416f73a980389.jpg', '400', '350', 'jpg', 0, '臻爱.jpg', 30751, 'image/jpeg', '', 1658418768, 1658418768, 1658418768, 'local', '83ff32e9436fd178dfe366e9d113aa4fa6f11d49');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (20, '', 1, 0, '/uploads/20220727/79a03ec5fe813fee670c9cd3819d3fa4.jpg', '400', '350', 'jpg', 0, '79a03ec5fe813fee670c9cd3819d3fa4.jpg', 34467, 'image/jpeg', '', 1658901295, 1658901295, 1658901295, 'local', '7aa3e847ee0801fd1fe8ce472f6b214b3b34ce9b');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (21, '', 1, 0, '/uploads/20220727/7314f2ce38887505eec83ba42fa53c27.jpg', '750', '1488', 'jpg', 0, '悦克拉-引导页.jpg', 223075, 'image/jpeg', '', 1658916721, 1658916721, 1658916720, 'local', '014791c0f27bae1cbeab0fb7e15a5b5f6b638e6d');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (22, '', 1, 0, '/uploads/20220728/614fe561dd7b5c46746419e22c59c162.jpg', '750', '1488', 'jpg', 0, 'WechatIMG1617.jpg', 51857, 'image/jpeg', '', 1658980151, 1658980151, 1658980151, 'local', 'b91fed3be231add14ac38a248a9f6101ac7e3b92');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (23, '', 1, 0, '/uploads/20220805/50ffecec4ef0f17835fe9f71b92e2004.png', '334', '328', 'png', 0, 'QQ20220805-172507@2x.png', 115203, 'image/png', '', 1659691532, 1659691532, 1659691532, 'local', 'bb13e4a57863eb818a78fed7b660db7f59fe6233');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (24, '', 1, 0, '/uploads/20220810/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1660143160, 1660143160, 1660143160, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (25, '', 1, 22, '/uploads/20220816/33e400442ce20a37b40c8108d0f5b1b8.jpeg', '650', '325', 'jpeg', 0, '7361d799edde1f89c195011ca45333b9 (2).jpeg', 30317, 'image/jpeg', '', 1660656844, 1660656844, 1660656844, 'local', '363b0ee42228bb44117db41165ae4b9eb785b6ee');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (26, '', 1, 0, '/uploads/20220821/be6e06bd406c600e28f4b3f80f4f35a1.jpeg', '1920', '1080', 'jpeg', 0, 'c58bc7002f42fe743245362abd1a7b2c.jpeg', 347325, 'image/jpeg', '', 1661092095, 1661092095, 1661092095, 'local', 'be9e1022acfbe7145bc34079da663885d58edb0a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (27, '', 1, 0, '/uploads/20220821/7414b08ce244d8dd64da5eb7366db375.jpeg', '1920', '1080', 'jpeg', 0, '0c8bc62dbc7dac574c0385af3c482f51.jpeg', 1504347, 'image/jpeg', '', 1661092142, 1661092142, 1661092142, 'local', '500b2ffc787b4c7e6463c818acf551fea5816c35');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (28, '', 1, 0, '/uploads/20220829/f3c8e3dfb0b0399dda5967c9fc0188c5.png', '120', '30', 'png', 0, 'logoa.png', 2198, 'image/png', '', 1661775136, 1661775136, 1661775136, 'local', '9a0a14a2605b4ddab956e7585f3d427721e15f90');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (29, '', 1, 0, '/uploads/20220829/4aec2f98f68c44b3c39472829723d2a6.jpeg', '1920', '1080', 'jpeg', 0, '181b68128a94a2d60de43b8393721646.jpeg', 1699311, 'image/jpeg', '', 1661777196, 1661777196, 1661777196, 'local', '28b3f81a065b9bfb892ffe94281d8902478a4b2d');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (30, '', 1, 0, '/uploads/20220829/ab2254f112427928f674bd50991e30e3.jpeg', '550', '347', 'jpeg', 0, '176f16143439c8f50a9ab3f059e193d4.jpeg', 40269, 'image/jpeg', '', 1661780453, 1661780453, 1661780453, 'local', 'c5c09dc1e8b8641836234af40463b1b382fe0307');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (31, '', 1, 0, '/uploads/20220830/438d658cc053e025223e3ac40e925464.jpeg', '1320', '417', 'jpeg', 0, 'bcc0d488bd01968765053444ef927f0d.jpeg', 130712, 'image/jpeg', '', 1661843905, 1661843905, 1661843905, 'local', '524c4ed4056506df4522cb8985e88606eb675ccc');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (32, '', 1, 0, '/uploads/20220830/2aa8cf8bd4094fe326c59d7dcfd458f1.jpeg', '2000', '434', 'jpeg', 0, 'ae7732584aa86f71768f440cf9168810.jpeg', 153172, 'image/jpeg', '', 1661863696, 1661863696, 1661863696, 'local', '4c0b022e42f0bf53d2ebba279a03eedecde705af');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (33, '', 1, 0, '/uploads/20220901/bb1b6473f66041e95e0f9aa071cf8984.jpeg', '680', '451', 'jpeg', 0, 'a1649f07504e14205fac64cc50cebfad.jpeg', 496717, 'image/jpeg', '', 1662041155, 1662041155, 1662041155, 'local', 'e2889dc884e87d5efe0a5ca1c53747836d41ce79');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (34, '', 1, 0, '/uploads/20220902/5342ff1ba21721566f34e4ba2e328af6.jpeg', '900', '600', 'jpeg', 0, '3ddf089e4ad569f2d2c91a9fc5ade680.jpeg', 100883, 'image/jpeg', '', 1662121494, 1662121494, 1662121494, 'local', '9488e5ed1319455f27d2e4e0385e7c0eb20523d8');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (35, '', 1, 0, '/uploads/20220902/17fcf4a44931fc48f49e55645e7f52c9.jpeg', '1920', '600', 'jpeg', 0, '048401861efac77b5564d1b72b9cc35f.jpeg', 39651, 'image/jpeg', '', 1662125831, 1662125831, 1662125831, 'local', 'a449abf3009ac41734351cb52ea15494b2bad37c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (36, '', 1, 0, '/uploads/20220902/9e77141e70175180e2189264ef81fc72.jpeg', '2000', '1160', 'jpeg', 0, '7b6670d385a4e8df1fe273449295afe1.jpeg', 314844, 'image/jpeg', '', 1662126160, 1662126160, 1662126160, 'local', 'b5d59b6cb5a50607327281d42270b300726b1f6f');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (37, '', 1, 0, '/uploads/20220903/bed5d9219bb0fc19797f12e48f2d4061.jpeg', '1024', '1369', 'jpeg', 0, '4aea136f654244139d84c7db4432fd0f.jpeg', 55869, 'image/jpeg', '', 1662166365, 1662166365, 1662166365, 'local', '4a19ddacfb8eee3a8322545cc2f33b9aac17dfec');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (38, '', 1, 0, '/uploads/20220903/d001d2286b4f64a242a72e3df07bdbbf.jpeg', '750', '1161', 'jpeg', 0, '52b68c9b056be3cb7355ee318237d2c1.jpeg', 71346, 'image/jpeg', '', 1662166365, 1662166365, 1662166365, 'local', '37c3598786d608921e71bdcd6ab46e1652896571');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (39, '', 1, 0, '/uploads/20220903/6d4a3d4a735bc25bb1255728d5b35505.jpeg', '400', '400', 'jpeg', 0, 'be2db49531964dedf47daa456a0daaca.jpeg', 22016, 'image/jpeg', '', 1662166380, 1662166380, 1662166380, 'local', '0989641aaa0aaf7428cce17c8afec17d44716478');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (40, '', 1, 0, '/uploads/20220903/28bc3c57cecf2f917d3254589f8965b1.jpeg', '790', '1096', 'jpeg', 0, 'dc6e34042bb5746661df698b8074a28a.jpeg', 79185, 'image/jpeg', '', 1662166380, 1662166380, 1662166380, 'local', 'f44c46b048b38181d66ebf038875bde1050bc6e2');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (41, '', 1, 0, '/uploads/20221013/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1665649203, 1665649203, 1665649203, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (42, '', 1, 0, '/uploads/20221013/7414b08ce244d8dd64da5eb7366db375.jpeg', '1920', '1080', 'jpeg', 0, '0c8bc62dbc7dac574c0385af3c482f51.jpeg', 1504347, 'image/jpeg', '', 1665661541, 1665661541, 1665661541, 'local', '500b2ffc787b4c7e6463c818acf551fea5816c35');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (43, '', 1, 0, '/uploads/20221013/ab2254f112427928f674bd50991e30e3.jpeg', '550', '347', 'jpeg', 0, '176f16143439c8f50a9ab3f059e193d4.jpeg', 40269, 'image/jpeg', '', 1665661643, 1665661643, 1665661643, 'local', 'c5c09dc1e8b8641836234af40463b1b382fe0307');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (44, '', 1, 0, '/uploads/20221013/33e400442ce20a37b40c8108d0f5b1b8.jpeg', '650', '325', 'jpeg', 0, '7361d799edde1f89c195011ca45333b9 (2).jpeg', 30317, 'image/jpeg', '', 1665661643, 1665661643, 1665661643, 'local', '363b0ee42228bb44117db41165ae4b9eb785b6ee');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (45, '', 1, 0, '/uploads/20221013/4b9ac54e21b6533c9f7551f7f1c71b88.png', '600', '320', 'png', 0, 'graft.png', 370344, 'image/png', '', 1665661747, 1665661747, 1665661747, 'local', '8acfd1963d6271cb0ee9cbe2963db1dea4de09cb');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (46, '', 1, 0, '/uploads/20221013/001a9fa35e385778bf566287e0bcf8fe.mp4', '', '', 'mp4', 0, '10月13日.mp4', 1825264, 'video/mp4', '', 1665662216, 1665662216, 1665662216, 'local', 'f94b438afda49fd17c62f0966ee8c17c84ca6700');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (47, '', 1, 0, '/uploads/20221013/dad2cbaa48e0b0a100db41a6c8f933e1.png', '960', '528', 'png', 0, 'cowboy-4-black_960x.png', 157854, 'image/png', '', 1665665618, 1665665618, 1665665618, 'local', '2a5f5f6cdc42d557b0f3c817cd075efa1c3ace28');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (48, '', 1, 0, '/uploads/20221216/fc38cb8dfddc19139d07cd44d69a2c6b.jpeg', '940', '627', 'jpeg', 0, '1874fd1282dad260898eebdb325be567.jpeg', 218882, 'image/jpeg', '', 1671183654, 1671183654, 1671183654, 'local', 'cb36238d30c498d01145b557b858ee5fd04631d1');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (49, '', 1, 0, '/uploads/20221216/48d610645fac72f2551cd8794a367805.jpeg', '2592', '1728', 'jpeg', 0, '4e9d41819cf0194df826281232b377be.jpeg', 785752, 'image/jpeg', '', 1671184385, 1671184385, 1671184385, 'local', '21fe144d071a61e697384c7d64fb087e817dc3c5');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (50, '', 1, 0, '/uploads/20230109/19436cd67d5aedc54f1bdaaa56c21087.png', '500', '333', 'png', 0, '19436cd67d5aedc54f1bdaaa56c21087.png', 208704, 'image/png', '', 1673247475, 1673247475, 1673247475, 'local', '3fe0f65a561ada6939da5358b8254d4a828381a3');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (51, '', 1, 0, '/uploads/20230109/393cf89c183ad1f896c916f0e623030a.png', '500', '375', 'png', 0, '393cf89c183ad1f896c916f0e623030a.png', 207204, 'image/png', '', 1673247567, 1673247567, 1673247567, 'local', 'a50d2386d74400824f0f5df554cfe59304bb4182');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (52, '', 1, 0, '/uploads/20230110/17fcf4a44931fc48f49e55645e7f52c9.jpeg', '1920', '600', 'jpeg', 0, '17fcf4a44931fc48f49e55645e7f52c9.jpeg', 39651, 'image/jpeg', '', 1673309228, 1673309228, 1673309228, 'local', 'a449abf3009ac41734351cb52ea15494b2bad37c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (53, '', 1, 0, '/uploads/20230112/d0a6124243b291e30be2b5df198d8871.jpeg', '1024', '1009', 'jpeg', 0, 'teams1.jpeg', 36090, 'image/jpeg', '', 1673530557, 1673530557, 1673530557, 'local', 'fd9aa705fe9f74c3ea8cbeb5719e75ff4043f26b');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (54, '', 1, 0, '/uploads/20230123/608f00f0ee34cd013d0931fa679c751c.png', '200', '200', 'png', 0, 'delete-themes.png', 7453, 'image/png', '', 1674471337, 1674471337, 1674471337, 'local', '1d53bcf4870d80aaf311efd3e0806ac0ccc26dbb');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (55, '', 1, 0, '/uploads/20230129/ab2254f112427928f674bd50991e30e3.jpeg', '550', '347', 'jpeg', 0, '176f16143439c8f50a9ab3f059e193d4.jpeg', 40269, 'image/jpeg', '', 1674954360, 1674954360, 1674954360, 'local', 'c5c09dc1e8b8641836234af40463b1b382fe0307');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (56, '', 1, 0, '/uploads/20230129/e44a067628a206ba997889c6f32c16f1.png', '1218', '1566', 'png', 0, '无标题.png', 271424, 'image/png', '', 1674954392, 1674954392, 1674954392, 'local', 'aedaf920ee845cf9430d2bd340c96435b3b07bc8');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (57, '', 1, 0, '/uploads/20230131/08017db93ec791329d36e04417d1a304.png', '256', '500', 'png', 0, '13_e6571eba-b27b-4b2b-9505-3f4ec8c310ac_256x.png', 62323, 'image/png', '', 1675149336, 1675149336, 1675149336, 'local', '4cf8a5fd186a90725c513989ca01514db122cb88');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (58, '', 1, 0, '/uploads/20230223/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1677113164, 1677113164, 1677113164, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (59, '', 0, 22, '/uploads/20230305/b68b241be523b4e9eda98dbfa1ff4e35.png', '375', '715', 'png', 0, 'Sun Mar 05 2023 19:56:40 GMT+0800 (中国标准时间).png', 295478, 'image/png', '', 1678017400, 1678017400, 1678017400, 'local', '3f5669c07ad3012a2380d64fb152bd3c70e6b10c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (60, '', 0, 22, '/uploads/20230306/ac6803c1321c6036f3512976256c6eca.png', '375', '715', 'png', 0, 'Mon Mar 06 2023 17:15:29 GMT+0800 (中国标准时间).png', 175500, 'image/png', '', 1678094130, 1678094130, 1678094130, 'local', 'a3932b0ac24185c15d6763b5fb3e83e9fc584361');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (61, '', 0, 22, '/uploads/20230306/984058140d12f32536ee06301c6f34b3.png', '375', '439', 'png', 0, 'Mon Mar 06 2023 17:15:56 GMT+0800 (中国标准时间).png', 163787, 'image/png', '', 1678094156, 1678094156, 1678094156, 'local', '9c2e7684b7ca26ec5ebb48d63a4eec760904d732');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (62, '', 0, 22, '/uploads/20230306/1f8b4d290452d4bbf6d0cb62ec4e6ca8.png', '375', '439', 'png', 0, 'Mon Mar 06 2023 17:16:13 GMT+0800 (中国标准时间).png', 163447, 'image/png', '', 1678094174, 1678094174, 1678094174, 'local', 'f24e2754ac25187ac09727f32a9a7762c8dd2f55');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (63, '', 0, 22, '/uploads/20230306/7f0a378750d4c1aceb80664fe0585c99.png', '375', '389', 'png', 0, 'Mon Mar 06 2023 17:17:16 GMT+0800 (中国标准时间).png', 184106, 'image/png', '', 1678094236, 1678094236, 1678094236, 'local', 'c7bc84700411cc252e95753a2a1b19c32cd610e1');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (64, '', 0, 22, '/uploads/20230306/88a8caf3aef1e6c9da92116d84530671.png', '375', '715', 'png', 0, 'Mon Mar 06 2023 18:05:55 GMT+0800 (中国标准时间).png', 242014, 'image/png', '', 1678097155, 1678097155, 1678097155, 'local', '31626bae48113eda06e6bcdeb1327f312acc08a3');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (65, '', 0, 22, '/uploads/20230306/3598ceb2804c8cc4c8f609b43d2d4a92.png', '375', '715', 'png', 0, 'Mon Mar 06 2023 18:10:01 GMT+0800 (中国标准时间).png', 243847, 'image/png', '', 1678097402, 1678097402, 1678097402, 'local', '206d810dae9fa4d363c467614b34ae6e6ff60772');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (66, '', 1, 0, '/uploads/20230306/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1678113243, 1678113243, 1678113243, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (67, '', 1, 0, '/uploads/20230331/3a26393686bb143c7a8476cbf5f85709.zip', '', '', 'zip', 0, 'ldcms-1.0.5.zip', 3787349, 'application/zip', '', 1680234197, 1680234197, 1680234197, 'local', '8540b67fffce2f784ae2b97030e060f6bd2948f7');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (68, '', 1, 0, '/uploads/20230331/13ed2982d79170cf0fa7138e823a4b27.zip', '', '', 'zip', 0, 'ldeditor-1.0.0.zip', 1638259, 'application/zip', '', 1680254155, 1680254155, 1680254155, 'local', '9ab7d15d37b6244cafa0007650ad0f4180f4872a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (69, '', 1, 0, '/uploads/20230401/3a26393686bb143c7a8476cbf5f85709.zip', '', '', 'zip', 0, 'ldcms-1.0.5.zip', 3787349, 'application/zip', '', 1680345962, 1680345962, 1680345962, 'local', '8540b67fffce2f784ae2b97030e060f6bd2948f7');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (70, '', 1, 0, '/uploads/20230401/c70f7962eace6bfbd9cee129cd96b415.zip', '', '', 'zip', 0, 'ldcms-1.0.4.zip', 3779310, 'application/zip', '', 1680359317, 1680359317, 1680359317, 'local', 'e7187575a2636ae22a69bc5fae969d74dfb275bb');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (71, '', 1, 0, '/uploads/20230401/13ed2982d79170cf0fa7138e823a4b27.zip', '', '', 'zip', 0, 'ldeditor-1.0.0.zip', 1638259, 'application/zip', '', 1680361682, 1680361682, 1680361682, 'local', '9ab7d15d37b6244cafa0007650ad0f4180f4872a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (72, '', 1, 0, '/uploads/20230428/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1682644000, 1682644000, 1682644000, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (73, '', 1, 22, '/uploads/20230627/9f2d9318c381939a13c6d8cd9bbebd6d.png', '500', '500', 'png', 0, '黑色拳击队队伍logo (3).png', 16706, 'image/png', '', 1687859017, 1687859017, 1687859017, 'local', '325831b8359321ee68bdc813643c16baa279e505');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (74, '', 1, 22, '/uploads/20230627/f57ebce8a72b823912904fe76eda0909.png', '192', '192', 'png', 0, 'avatar.png', 15135, 'image/png', '', 1687859065, 1687859065, 1687859065, 'local', '9c39ed36543710c1ce4de7e0e56391c37ae58d56');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (75, '', 1, 0, '/uploads/20230717/5ce75845920237330895e7e23f4813a0.png', '640', '640', 'png', 0, 'document_noimage.png', 16060, 'image/png', '', 1689554632, 1689554632, 1689554632, 'local', '1dc4b496536236d8dcc6577b383efee2abd46099');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (76, '', 1, 0, '/uploads/20230717/7316c27ec3a42e7ba67d5ea03d209cc1.png', '640', '640', 'png', 0, 'document_noimg.png', 16187, 'image/png', '', 1689555056, 1689555056, 1689555056, 'local', 'bb33378177ba8ccc354a5e2a1d267d72c22e4105');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (77, '', 1, 0, '/uploads/20230822/699a04e9ed380221ef107be40e67b76b.xlsx', '', '', 'xlsx', 0, '提货卡.xlsx', 8817, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '', 1692674450, 1692674450, 1692674450, 'local', 'ede4084001524ae1614e5183f9cb098394aa555d');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (78, '', 1, 0, '/uploads/20230822/512086f48ea55cdd68def6fd8ff573f1.xlsx', '', '', 'xlsx', 0, '提货卡.xlsx', 8833, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '', 1692675972, 1692675972, 1692675972, 'local', '522a1a1db53906b87ecb6b0861808487f50444e5');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (79, '', 1, 0, '/uploads/20230822/8cc7a4efebe07aa65bb10f6e8caa7f13.xlsx', '', '', 'xlsx', 0, '提货卡.xlsx', 8870, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '', 1692679602, 1692679602, 1692679602, 'local', '182fdf514d65e7963d32aeb1c3ab3aed1f735038');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (80, '', 1, 0, '/uploads/20230822/13cb05a9fb26fa034279e428f48c33ff.xlsx', '', '', 'xlsx', 0, '提货卡.xlsx', 8951, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '', 1692680007, 1692680007, 1692680007, 'local', 'd13c2ac677ae9ab419291d363f58bf88ceeb547e');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (81, '', 0, 20, '/uploads/20231207/7f764316716de5182e0791cfd6356e03.png', '750', '273', 'png', 0, 'Ja9XJMNKJwsV7f764316716de5182e0791cfd6356e03.png', 126075, 'image/png', '', 1701953500, 1701953500, 1701953500, 'local', '1afffbe49f1640fd9e99d5b15ea2c37061c5a624');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (82, '', 1, 0, '/uploads/20231216/ea5af5a5837d4fdc3ffeab7e1d22a56e.mp4', '', '', 'mp4', 0, 'QQ2023127-164143.mp4', 4279456, 'video/mp4', '', 1702725309, 1702725309, 1702725309, 'local', '71cf260f243d3a54c6904925f2da66664026ab03');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (83, '', 1, 0, '/uploads/20240110/76e29f12ae1f1e6e6438424f3c33d04d.jpg', '1920', '360', 'jpg', 0, 'WechatIMG191.jpg', 87034, 'image/jpeg', '', 1704872344, 1704872344, 1704872344, 'local', '8e86d3656ee2658612d0467bd744a1c78c0b5e1c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (84, '', 1, 0, '/uploads/20240527/07c55184ece36d905c11e3388b38ddab.jpeg', '1680', '800', 'jpeg', 0, 'banner3.jpeg', 214326, 'image/jpeg', '', 1716793403, 1716793403, 1716793403, 'local', '47491b1c8d21b9768f7a3b4bb8e9abb681a5f566');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (85, '', 1, 0, '/uploads/20240527/d99d55a2b2ebf0bdb00e155b5c8fee62.png', '3798', '1218', 'png', 0, 'image.png', 4066684, 'image/png', '', 1716796570, 1716796570, 1716796570, 'local', '692407243b5b63c89883c4fab4feac05645a51d4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (86, '', 1, 0, '/uploads/20240528/577a43d1730d8fb8aae352738e411abf.jpeg', '340', '280', 'jpeg', 0, 'b3.jpeg', 37559, 'image/jpeg', '', 1716905267, 1716905267, 1716905267, 'local', 'd1dc760aca9445023eeaee0e9a142c53b38a4c14');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (87, '', 1, 0, '/uploads/20241202/b8df77d29fb1afd3965235299f5d7362.zip', '', '', 'zip', 0, 'b8df77d29fb1afd3965235299f5d7362.zip', 1436987, 'application/zip', '', 1733139261, 1733139261, 1733139261, 'local', '49f72f3028668e4171daf545bc41c24d594125f0');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (88, '', 1, 0, '/uploads/20250325/0f12b511e508f35f6980cd4a8b44a05c.mp4', '', '', 'mp4', 0, '31b0f13a70db08d0.mp4', 3097460, 'video/mp4', '', 1742911287, 1742911287, 1742911287, 'local', '5ef72aeefb10cacf640451c0a1752247b13075e8');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (89, '', 1, 0, '/uploads/20250325/32e3cb24ad73d08a902c8180e9c20a86.mp4', '', '', 'mp4', 0, '2528b3b61f792167.mp4', 2038062, 'video/mp4', '', 1742911530, 1742911530, 1742911530, 'local', '4d97484e0da8cee026b0fe2dd1be953fdbf1b73c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (90, '', 1, 0, '/uploads/20250325/9f88b512ad772c771b357f9018cb8d86.mp4', '', '', 'mp4', 0, 'd1e056442ee3fe04.mp4', 580243, 'video/mp4', '', 1742911557, 1742911557, 1742911557, 'local', 'a4fefe1b9b83eb052e2b4b39c9ff709a5910d4f0');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (91, '', 1, 0, '/uploads/20250325/6f7d13092db1c3866c54af74ad5b0266.mp4', '', '', 'mp4', 0, 'banner-4-23.mp4', 1224155, 'video/mp4', '', 1742911668, 1742911668, 1742911668, 'local', '78d4e2c508ca1ff96a2b8c18c81313c53212bd67');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (92, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748688544, 1748688544, 1748688544, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (93, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748689721, 1748689721, 1748689721, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (94, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748690605, 1748690605, 1748690605, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (95, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748690694, 1748690694, 1748690694, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (96, '', 1, 1, '/uploads/20250531/cdfb3586a54bc21b540502f60fa84c05.jpg', '800', '401', 'jpg', 0, 'WX20241220-083101@2x.jpg', 45500, 'image/jpeg', '', 1748690715, 1748690715, 1748690715, 'local', '11c7657ec3588f78f5dc9bbd766a9cd6a0fcf139');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (97, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748690808, 1748690808, 1748690808, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (98, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693272, 1748693272, 1748693272, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (99, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693339, 1748693339, 1748693339, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (100, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693440, 1748693440, 1748693440, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (101, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693600, 1748693600, 1748693600, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (102, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748693614, 1748693614, 1748693614, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (103, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693645, 1748693645, 1748693645, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (104, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748693653, 1748693653, 1748693653, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (105, '', 1, 1, '/uploads/20250531/e45eb47ce7d0c8d770dcd644a59fee79.jpg', '800', '412', 'jpg', 0, 'WX20241220-083042@2x.jpg', 50600, 'image/jpeg', '', 1748693666, 1748693666, 1748693666, 'local', '199214dfabb793e24e523e20fd3bca6d881c221f');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (106, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748693680, 1748693680, 1748693680, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (107, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748694411, 1748694411, 1748694411, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (108, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748694440, 1748694440, 1748694440, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (109, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748694866, 1748694866, 1748694866, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (110, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748694875, 1748694875, 1748694875, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (111, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748695404, 1748695404, 1748695404, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (112, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748695444, 1748695444, 1748695444, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (113, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748695527, 1748695527, 1748695527, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (114, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748695552, 1748695552, 1748695552, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (115, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748696210, 1748696210, 1748696210, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (116, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748696344, 1748696344, 1748696344, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (117, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748696584, 1748696584, 1748696584, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (118, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748696592, 1748696592, 1748696592, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (119, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748696867, 1748696867, 1748696867, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (120, '', 1, 1, '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '3780', '1726', 'png', 0, 'B2FA3DAC61DA74C5858CDB7672523348.png', 537477, 'image/png', '', 1748699702, 1748699702, 1748699702, 'local', '8dc9fe6afb84f4bd35e620d21020d29a4170caf4');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (121, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748700114, 1748700114, 1748700114, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (122, '', 1, 1, '/uploads/20250531/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748700246, 1748700246, 1748700246, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (123, '', 1, 1, '/uploads/20250601/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748738223, 1748738223, 1748738223, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (124, '', 1, 1, '/uploads/20250601/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1748738713, 1748738713, 1748738713, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_area
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_area`;
CREATE TABLE `bd_cms_area` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `area_acode` (`acode`),
  KEY `area_pcode` (`pcode`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='区域管理';

-- ----------------------------
-- Records of bd_cms_area
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, 'en', '0', 'English', 'test.badoucms.test', '0', 'admin', 'Admin', '2023-02-02 21:20:40', '2025-04-21 18:59:23');
INSERT INTO `bd_cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, 'oe', '0', '德文', '', '0', 'admin', 'admin', '2024-08-19 06:06:25', '2024-08-19 06:06:25');
INSERT INTO `bd_cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', '', '中文', '', '1', 'Admin', 'admin', '2024-09-22 08:02:37', '2024-09-25 08:52:19');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_company
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_company`;
CREATE TABLE `bd_cms_company` (
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='公司信息';

-- ----------------------------
-- Records of bd_cms_company
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_company` (`id`, `acode`, `name`, `address`, `postcode`, `contact`, `mobile`, `phone`, `fax`, `email`, `qq`, `weixin`, `blicense`, `other`) VALUES (1, 'cn', 'xxx科技有限公司', '苏州市xxx区xx号', '215000', '李先生', '13988886666', '0512-88886666', '0512-88886666', 'admin@badoucms.com', '8888666', '/storage/default/20241030/badoucms.com.png', '999123456789', '');
INSERT INTO `bd_cms_company` (`id`, `acode`, `name`, `address`, `postcode`, `contact`, `mobile`, `phone`, `fax`, `email`, `qq`, `weixin`, `blicense`, `other`) VALUES (7, 'oe', '111', 'dsdf', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_company` (`id`, `acode`, `name`, `address`, `postcode`, `contact`, `mobile`, `phone`, `fax`, `email`, `qq`, `weixin`, `blicense`, `other`) VALUES (6, 'en', 'test english', '', '', '', '', '', '', '', '', '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_content
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_content`;
CREATE TABLE `bd_cms_content` (
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
  PRIMARY KEY (`id`),
  KEY `ay_content_scode` (`scode`),
  KEY `ay_content_subscode` (`subscode`),
  KEY `ay_content_acode` (`acode`),
  KEY `ay_content_filename` (`filename`),
  KEY `ay_content_date` (`date`),
  KEY `ay_content_sorting` (`sorting`),
  KEY `ay_content_status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COMMENT='CMS文章内容';

-- ----------------------------
-- Records of bd_cms_content
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (1, 'cn', '1', '', '公司简介', '#333333', '', '', 'admin', '本站', '', '2018-04-11 17:26:11', '', '', '', '<p><br/></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul class=\" list-paddingleft-2\"><li><p>区域管理（多语言）</p></li><li><p>模型管理（自定义内容模型）</p></li><li><p>模型字段管理（自定义模型字段）</p></li><li><p>栏目管理</p></li><li><p>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</p></li><li><p>站点配置、公司信息</p></li><li><p>定制标签（自定义前台标签）</p></li><li><p>前台模版标签</p></li><li><p>轮播图片</p></li><li><p>多条件筛选</p></li><li><p>网站地图（sitemap）</p></li><li><p>友情链接</p></li><li><p>自定义表单</p></li><li><p>留言信息</p></li><li><p>文章内链</p></li><li><p>多条件搜索</p></li><li><p>内容权限</p></li><li><p>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</p></li><li><p>会员字段</p></li><li><p>会员等级(设置栏目与内容浏览权限)</p></li><li><p>文章评论(回复、审核)</p></li><li><p>我的评论</p></li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 255, '1', '0', '0', '0', 1230, 0, 0, 'admin', 'Admin', '2018-04-11 17:26:11', '2025-05-28 10:23:32', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (3, 'cn', '11', '', '联系我们', '#333333', '', '', 'admin', '本站', '', '2018-04-11 17:31:29', '/static/upload/image/20180413/1523583018133454.png', '', '', '联系我们', '', '', '', '官方网站：www.badoucms.com技术交流群： 137083872www.badoucms.com我们一直秉承大道至简分享便可改变世界的理念，坚持做最简约灵活的badoucms开源软件！您的每一份帮助都将支持badoucms做的更好，走的更远！我们一直在坚持不懈地努力，并尽可能让badoucms完全开源免费，您的帮助将使我们更有动力和信心^_^！扫一扫官网付款', 255, '1', '0', '0', '0', 63, 0, 0, 'admin', 'Admin', '2018-04-11 17:31:29', '2025-04-14 19:17:16', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (44, 'cn', '31', '', '调研', '#333333', '', '', 'Admin', '本站', '', '2024-11-23 21:32:45', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 64, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2025-04-05 21:47:57', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (45, 'cn', '32', '', '留言', '#333333', '', '', 'Admin', '本站', '', '2024-11-24 10:58:17', '', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg,/storage/default/20241030/badoucms.com.png', '', '<p>sdf1111</p>', '', '', '', 'sdf', 255, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2025-04-12 09:40:25', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (46, 'cn', '6', '', 'xx医疗行业网站模板', '', '', '', 'Admin', '', '', '2024-11-24 20:58:20', '/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', '', '', '<p><img src=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" alt=\"医疗网站缩略图.jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" width=\"\" height=\"\"/></p><p><strong>一、整体风格<br/></strong></p><p>选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。<br/></p><p><strong>二、具体图片内容<br/></strong></p><ol class=\" list-paddingleft-2\"><li><p>一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。</p></li><li><p>医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。</p></li><li><p>患者在电脑或手机上使用在线预约系统的场景，旁边可以有日历和确认按钮等元素。</p></li><li><p>医生查看电子病历的画面，可以有一个大屏幕显示详细的病历信息和图表。</p></li><li><p>大数据分析的图表，如柱状图、折线图等，代表医疗数据的收集和分析。</p></li><li><p>医疗行业网站的标志和标语，突出其专业性和创新性。<br/></p></li></ol>', 'b', '', '', '一、整体风格选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。二、具体图片内容一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。患者在电脑或手机上', 251, '1', '0', '0', '0', 66, 0, 0, 'Admin', 'Admin', '2024-11-24 21:01:15', '2025-04-20 14:51:49', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (47, 'cn', '6', '', '外贸行业网站模板', '', '', '', 'Admin', '', '', '2024-11-24 20:58:20', '/storage/default/20241124/外贸网站缩略图4b812e48623d14f81d4e025ae42c061dad65588d.jpg', '', '', '<p><img src=\"https://p3-search.byteimg.com/obj/labis/0e409f67aba58e66c221e30d7483d51f\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. “快”—— 访问速度快</h3><p>外贸网站一般会使用海外服务器或加速节点，以此确保网站的打开速度和响应速度能与当地平均水平持平。这是因为如果网站速度过慢，会导致用户放弃访问。例如，在全球疫情的影响下，外贸企业纷纷转向外贸独立站，而网站速度慢直接影响用户体验和营销转化。目前有多种技术可以提升外贸网站的运行速度，如 Google AMP 框架，其网页可以在 Google 服务器里生成缓存，大大提升网站速度；Webp 无损压缩技术，能降低图片文件大小；Gzip 压缩技术以及 CDN 加速，通过寻找互联网上最快的访问节点来优化运行速度。此外，CDN 加速技术还能把放置于国内的网站复制一份到各个国家的服务器上，让各个国家的浏览者就近访问企业网站，提高访问响应速度。</p><p><br></p><h3>2. “简”—— 信息精简</h3><p>与国内企业网站相比，外贸网站信息量少。这一方面是因为欧美等发达国家受教育程度高的网民不追求过多过杂的信息，另一方面也与搜索引擎算法有关。谷歌不鼓励企业网站持续大量进行内容更新，尤其是无用信息的更新。外贸网站是为国外人阅读的网站，自然应该迎合他们的口味，减少信息量，避免像国内一些企业网站那样，为了迎合百度算法而持续大量更新内容，甚至包含一些无用信息。</p><p><br></p><h3>3. “直”—— 直接明了</h3><p>外贸网站的直接体现在两个方面。一是少有在线沟通工具，用户直接浏览栏目。这与欧美国家互联网环境有关，欧美用户对在线沟通工具似乎并不十分热衷，他们倾向于邮箱、电话交流。二是信息说明比较直接，少有拐弯抹角、云里雾里的 “忽悠式” 口号。这是因为欧美国家有一个较为诚信的互联网环境，用户对网站容易产生信任，所以网站在传递信息的时候不妨直来直去。</p><p><br></p><h3>4. “细”—— 做工细致</h3><p>从脚本、代码、图片、构架等方面看，欧美国家的企业网站似乎比国内一般的网站更为用心。这有两个原因，一是欧美国家非常流行工程师文化，工程师文化的一个特点就是 “抠细节”，所以外贸网站在作图、拍摄、LOGO、配色等方面要迎合工程师文化；二是欧美国家的整体代码水平和建站分工要比国内高一些。这两方面原因使得外贸网站的做工需要更细致一些。</p><p><br></p><h3>5. “严”—— 要求严格</h3><p>外贸网站对网站版权声明、个人隐私保护、用户数据泄露等方面更加重视。国内企业网站在建站时对这些方面往往不够上心，但外贸网站如果在这些方面做得不够或出现失误，有可能触碰法律风险，而且谷歌等欧美主流搜索引擎对法律条款不够完整的网站也不太友好。此外，外贸网站不允许出现错别字、语法错误和 BUG，因为这会直接导致用户对网站、企业和品牌的信任危机。</p><p><br></p><h2>二、外贸电商网站的特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/83f4c79ee7c66191137f10b53ba497cf\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 多语言支持</h3><p>外贸电商建立多语言网站至关重要，其能方便全球用户使用，打破语言障碍。建立多语言网站的步骤如下：首先要了解目标市场的语言需求和文化背景，根据不同市场特点选择需提供的语言版本；接着准备多语言网站的翻译内容，包括网站文本、图像、音频、视频等；然后选择合适的多语言网站管理平台，如 WordPress、Drupal 等，以便快速、方便地进行多语言网站的构建和管理；根据需要考虑使用专业的翻译服务、本地化工具或机器翻译等来实现网站内容的翻译；设计多语言导航和语言切换功能，使用户可以轻松切换网站语言版本；确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，以提高网站的可访问性和用户体验；最后逐步完善和优化多语言网站的内容和功能，以满足不同语言和文化需求的用户。</p><p>数据库级多语言支持可通过设计数据库结构实现，如单表存储在产品表中为每种语言添加独立列，或多表存储创建主表存储基本信息并关联翻译表；前端国际化框架依赖国际化库，将用户界面文本提取为语言文件并根据用户选择动态加载；内容管理系统（CMS）集成则可选择支持多语言的 CMS，如 WordPress、Drupal 或 Magento 等，方便用户管理不同语言的内容。</p><p><br></p><h3>2. 多货币支持</h3><p>外贸电商网站支持多货币便于用户付款和商家结算，适应跨境交易需求。不同国家和地区有不同的货币体系，多货币支持可以让用户在购物时选择自己熟悉的货币进行支付，提高购物的便利性和舒适度。同时，商家也可以更方便地进行结算，避免汇率波动带来的风险。</p><p><br></p><h3>3. 安全性和稳定性</h3><p>外贸电商网站的安全性和稳定性至关重要，它保障了用户和商家的利益，防止黑客攻击和信息泄露。使用安全证书是网站安全的基础，通过 HTTPS 协议和 SSL 加密技术，能有效防止黑客攻击和数据泄露；使用防火墙可以检测和阻止恶意流量，避免外部攻击和黑客入侵；定期备份数据能防止因病毒攻击、服务器崩溃等问题导致的数据丢失，提高数据恢复效率；加强密码安全，要求用户使用强密码并定期修改，管理员使用独特且复杂的密码并定期更换；限制对敏感信息的访问权限，只允许有必要权限的人员访问数据库和管理后台；进行安全培训，提高员工的安全意识，减少安全漏洞的发生。</p><p><br></p><h3>4. 良好的用户体验</h3><p>外贸电商网站的界面设计应简洁明了、易于操作，以提高用户满意度。简洁直观的导航能让用户轻松找到所需页面和功能；快速的加载速度优化图片、压缩代码、选择高效服务器等方式提升；响应式网站设计确保适应不同屏幕尺寸和设备；个性化推荐和定制化体验利用用户数据和历史行为分析为用户提供个性化推荐商品和定制化体验；清晰的产品信息和图片展示提供详细产品信息、规格、价格和清晰图片；简化的购物流程减少用户操作步骤，提供简单易用的结账和支付选项；安全的支付系统保护用户个人信息和支付安全；多语言支持满足不同用户语言需求；社交媒体整合方便用户分享和推荐产品；提供优质客户服务建立快速响应的客户服务渠道；用户评价和推荐增加信任和口碑效应；持续优化和改进根据用户行为和反馈数据不断提升用户体验。</p><p><br></p><h3>5. 强大的搜索功能</h3><p>外贸电商网站应具备快速高效的搜索功能，帮助用户快速找到所需商品和信息。默认全站搜索，然后通过结果分类导航，进行结果筛选、检索。提供 “相关搜索” 功能，帮访客找到更加的搜索词，还能给访客一些未想到的搜索提示。限定搜索的措施是自动提示，不仅能减少错误输入，还能帮助我们推荐产品与产品分类，避免 “无搜索结果” 的情况。</p><p><br></p><h3>6. 充足的商品信息</h3><p>外贸电商网站需提供详尽的商品介绍、规格、图片等，方便用户了解商品情况。清晰、高质量的产品图片和详细的描述信息能让用户全面了解产品特点和优势，提升购买决策的信心。</p><p><br></p><h3>7. 良好的客户服务</h3><p>外贸电商网站应提供多种渠道解答用户疑问，提升用户体验。建立快速响应的客户服务渠道，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。电子商务网站建设的售前服务包括认真回答消费者对商品的咨询、尺寸、码数、质量、售后等问题，及时回复、态度友好，提升用户满意度。</p><p><br></p><h3>8. 多样化的支付方式</h3><p>外贸电商网站支持多种支付方式，方便用户完成支付。常见的外贸电商网站支付方式有 PayPal、支付宝、银行电汇、信用卡支付等。不同国家和地区的支付习惯不同，选择合适的支付方式非常重要。要考虑支付方式的安全性、手续费用、方便用户操作等因素。</p><p><br></p><h3>9. 精准的数据分析</h3><p>外贸电商网站通过精准的数据分析了解用户需求和购买行为，优化网站设计和服务。利用用户数据和历史行为分析，为用户提供个性化的推荐商品和定制化的体验，增强用户的参与感和满意度。定期进行用户调研、用户体验测试和网站性能监测，根据反馈和数据进行改进和优化，不断提升用户体验。</p><p><br></p><h2>三、受国外喜欢的外贸网站特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/b6713d7f8b7c443fa779ddd46442deed\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 页面简洁明了</h3><p>外贸网站应避免繁琐复杂的页面设计，以简洁明了的布局吸引外国人的注意力。一个直观且简单易懂的界面可以让用户更快地了解网站的核心内容，提高用户的浏览效率。例如，减少不必要的装饰和复杂的动画效果，突出产品或服务的关键信息，使用户能够迅速找到所需内容。</p><p><br></p><h3>2. 多语言支持</h3><p>提供多语言支持可以提高网站的可访问性，满足不同国家和地区用户的语言需求。外贸网站可以根据目标市场的语言特点，提供相应的语言版本。具体实现方法包括了解目标市场的语言需求和文化背景，准备多语言网站的翻译内容，选择合适的多语言网站管理平台，如 WordPress、Drupal 等，考虑使用专业的翻译服务、本地化工具或机器翻译，设计多语言导航和语言切换功能，确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，逐步完善和优化多语言网站的内容和功能。</p><p><br></p><h3>3. 响应式网站设计</h3><p>随着移动设备的广泛使用，响应式网站设计成为外贸网站的重要特点。响应式设计能够确保网站在各种设备上，包括手机、平板和电脑，都能提供良好的用户体验。实现响应式设计可以设置关键断点，结合站点内容设置关键点，注意网站内容的有效传递；优先进行手机端设计，筛选出重要元素，避免使用大图，做垂直滚动，把搜索栏和主操作按钮放在醒目位置；扩大目标点击区域，方便用户点击；采用响应式图片或视频，避免显示不全、留白、模糊或失真的情况，可使用支持响应式的框架或设置图片属性，也可以使用 SVG 矢量图，对于视频可插入 FitVids 或 jQuery 插件实现自动缩放；进行恰当的视觉设计，注重色彩搭配，避免复杂的导航菜单、滑动效果和 Flash 动画，保证页面简洁优雅。</p><p><br></p><h3>4. 独特的视觉风格</h3><p>具有独特视觉风格的外贸网站更容易吸引外国人的关注。个性化和创新的设计能够突出网站在竞争激烈的市场中的独特性。可以从色彩搭配、字体选择、图片和视频的运用等方面打造独特的视觉效果，创造极强的视觉冲击力或营造舒适的氛围，具体取决于网站的主题内容。同时，要注意视觉设计与网站内容的协调性，确保用户在享受视觉盛宴的同时，能够轻松获取所需信息。</p><p><br></p><h3>5. 易于导航和使用</h3><p>清晰的导航结构和简单的操作流程是外贸网站受外国人喜欢的重要因素。网站应提供易于理解和直观的导航标签，帮助用户快速浏览和访问各个部分。例如，设置简洁明了的导航栏，分类合理，方便用户找到所需信息；简化购买流程，减少用户的购买障碍，提供清晰的购买按钮和操作指导；强化客户支持和沟通渠道，提供多种联系方式，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。</p><p><br></p><h3>6. 专业可信</h3><p>专业、可信的外贸网站更容易赢得外国人的信任。提供详细的产品信息、公司资质和客户评价等内容，有助于建立网站的可靠形象。展示清晰、高质量的产品图片和详细的描述信息，让用户全面了解产品特点和优势；强调公司的资质和荣誉，增强用户对公司的信心；允许用户对商品进行评价和打分，显示商品的用户反馈和满意度，积极回应用户评价，增强用户对商品的信任感。</p><p><br></p><h2>四、如何选择外贸行业网站</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/5bddf53b8ac68a48aaade06e5cc1cd90\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 国内外建站公司对比</h3><p><strong>1. 国外建站公司（以 Shopify 为例）：</strong></p><p>Shopify 是一个基于云端的电商平台，功能齐全，提供网站主机、购物车、支付处理、库存管理、订单跟踪和分析等功能，还有广泛的应用市场，允许商家使用各种应用程序来增强商店功能。其优势包括建站操作简单，拥有丰富的应用生态、引流渠道多且卖家相对自由等。但也存在一些劣势，如独立站本身没有流量需卖家自己推广，有交易费用，App 费用较高，网站程序采用小众的 Liquid 语言专业开发程序员少，备份转移不便，批发功能和多语言支持不够好等。</p><p><strong>2. 国内建站公司（以 Ueeshop、shopline、shopyy 等为例）：</strong></p><p>国内建站公司功能与国外建站公司不相上下，能满足独立站卖家需求。以 Ueeshop 为例，不抽取佣金只收年费，成本更低。语言相通，沟通方便，且一般会提供技术支持。Shopline 和 Shopyy 也有各自的特点，如免费试用时间不同、功能表和定价策略有所差异等。</p><p><br></p><h3>2. 独立站核心</h3><p>选择 SaaS 建站可节约成本和时间，将更多精力用于推广引流。SaaS 建站平台如独立站 SaaS，能够帮助企业快速搭建功能齐全的电商网站，降低启动成本和时间，提供高度定制化功能，打造独特品牌形象，增强市场竞争力。同时，通常集成多种营销工具和分析功能，有助于企业精准定位目标客户，提高营销效果和转化率。通过云端托管，确保网站高可靠性和安全性，企业无需担心服务器维护和数据安全问题。</p><p>而合适的建站公司能帮助卖家事半功倍。在选择建站公司时，要明确自己的业务需求和目标，考虑平台的稳定性和安全性、可扩展性和灵活性、用户体验和客户支持以及成本等因素。选择最适合自身需求的独立站 SaaS 服务，推动外贸业务的稳步发展。</p><p><br></p><h2>五、外贸行业网站的发展趋势</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/a363fbc361b840729a844f0d80f204a8\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 移动化趋势</h3><p>随着智能手机和移动互联网的普及，贸易活动将更多在移动端进行。如今，越来越多的消费者倾向于使用移动设备进行在线购物，这对外贸行业网站提出了新的要求。外贸网站需要适应移动化趋势，提供方便快捷的移动端服务，以满足用户随时随地进行贸易活动的需求。</p><p>例如，企业可以优化网站的移动端界面，确保在手机和平板等设备上能够流畅浏览和操作。同时，结合移动支付技术，为用户提供便捷的支付方式，提高交易效率。</p><p><br></p><h3>2. 数据驱动</h3><p>大数据分析和人工智能技术应用，提供个性化服务和推荐。在当今数字化时代，数据成为了外贸行业网站的重要资产。通过大数据分析，网站可以深入了解用户的行为、偏好和需求，从而为用户提供个性化的服务和推荐。</p><p>例如，利用用户的浏览历史、购买记录等数据，为用户推荐符合其兴趣的产品和服务。同时，人工智能技术可以帮助网站实现智能客服，自动回答用户的咨询，提高服务效率。</p><p>此外，大数据分析还可以用于优化供应链管理。通过分析销售数据和市场需求预测，企业可以实现库存的精准管理和优化，降低库存积压和滞销风险。</p><p><br></p><h3>3. 跨境电商的发展</h3><p>推动外贸网站发展，带来更多贸易机会和市场潜力。跨境电商的快速发展为外贸行业网站带来了新的机遇。随着全球贸易的日益频繁，跨境电商平台成为了企业拓展海外市场的重要渠道。</p><p>跨境电商平台具有全球性、便捷性、高效性、低成本等特点，能够满足消费者对多元化、个性化商品的需求，同时也为商家提供了更广阔的市场空间。例如，亚马逊、阿里巴巴等跨境电商平台已经成为全球贸易的重要组成部分，越来越多的企业和消费者开始使用平台进行交易。</p><p>外贸行业网站可以与跨境电商平台合作，借助平台的流量和资源，扩大自身的市场影响力。同时，网站也可以借鉴跨境电商平台的成功经验，优化自身的服务和功能，提高用户体验。</p><p><br></p><h3>4. 其他趋势</h3><p>如人工智能助力个性化用户体验、混合商务提供无缝连接客户旅程、增强现实和虚拟现实吸引观众等。</p><p>人工智能在个性化用户体验方面发挥着重要作用。通过机器学习和自然语言处理技术，网站可以更好地理解用户的需求和意图，为用户提供更加精准的推荐和服务。</p><p>混合商务模式将线上和线下渠道相结合，为用户提供无缝连接的客户旅程。外贸行业网站可以与线下实体店合作，实现线上线下融合，为用户提供更加便捷的购物体验。</p><p>增强现实和虚拟现实技术可以为用户带来更加沉浸式的购物体验。通过展示产品的 3D 模型和虚拟场景，用户可以更加直观地了解产品的特点和优势，提高购买决策的信心。</p><p><br></p><h2>六、外贸行业热门网站有哪些</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/51523bc6c767ed79db31cda3846406e1\" alt=\"\" data-href=\"\" /></p><p><br></p>', '', '', '', '外贸行业网站的特点', 253, '1', '0', '0', '0', 8, 0, 0, 'Admin', 'Admin', '2024-11-24 21:01:15', '2025-04-05 18:25:17', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (14, 'cn', '8', '', '常州xxx钢铁企业官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b4ad8ebe742c5f02e5df9e7d1a614a0a2daa308d93.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 252, '1', '0', '0', '0', 21, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2025-04-07 21:19:28', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (50, 'cn', '8', '', '无锡xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 251, '1', '0', '0', '0', 21, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2025-04-08 08:15:12', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (28, 'zh-cn', '29', '', 'accesser', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 21:53:08', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (29, 'zh-cn', '30', '', '单页测试1', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 21:54:08', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (31, 'zh-cn', '31', '', '单页测试2', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 22:02:35', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (48, 'cn', '6', '', '环保行业网站模板', '', '', '', 'Admin', '', '', '2024-11-25 07:43:57', '/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', '', '', '<p>环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。</p><p><img src=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" alt=\"医疗网站缩略图(1).jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" width=\"\" height=\"\" /></p><h3>（一）设计理念</h3><p>环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出环保主题，使网站在视觉上与众不同。同时，设计过程中应考虑网站的生命周期，确保其在使用过程中能够持续降低对环境的影响。例如，避免大面积使用深色背景，减少动画和视频的使用频率，以降低能源消耗。</p><p><br></p><h3>（二）节能优化</h3><p>能效优化是环保网站提升可持续性的重要手段。优化图片和多媒体文件的大小和格式，如使用现代的图像格式 WebP，能够在不损失质量的前提下减少文件大小，显著降低页面加载时间和服务器能源消耗。采用内容分发网络（CDN）技术，将网站内容分布到全球多个数据中心，减少用户访问的延迟和带宽消耗，提高用户体验的同时降低服务器的能源使用。选择绿色托管服务商也是关键一步，许多托管服务商已开始使用可再生能源为数据中心供电，有效减少网站的碳足迹。</p><p><br></p><h3>（三）可持续材料使用</h3><p>在网站建设中，可以选择可再生资源，如使用可再生能源供电、选择可再生材料制作硬件等。同时，资源循环利用也是实现可持续发展的重要途径，例如回收旧硬件、重复利用设计元素等，减少资源浪费。此外，通过选择高质量、耐用的硬件，减少硬件更换频率，可以有效减少电子垃圾的产生。</p><p><br></p><h3>（四）用户体验</h3><p>合理的导航和信息架构设计能帮助用户快速找到所需信息，减少不必要的点击和页面加载。响应式设计确保网站在各种设备上都能顺畅运行，提高用户满意度，减少因设备不兼容而产生的资源浪费。提供个性化和互动性的内容，增强用户的参与感和忠诚度。通过数据分析和用户反馈，不断优化网站功能和内容，提升整体用户体验。</p><p><br></p><h3>（五）内容管理</h3><p>网站内容应定期更新和优化，提供高质量的环保知识科普文章、视频和图片，以及及时发布环保新闻、报告等，确保内容的质量、准确性和实用性。使用内容管理系统（CMS），更高效地管理和更新网站内容。采用缓存技术减少服务器请求次数，提高网站加载速度，降低服务器负载和能源消耗。同时，对内容进行压缩与优化，如压缩图像、视频等多媒体文件，减少数据传输量，降低服务器负载和能耗。</p><p><br></p><h3>（六）社会责任</h3><p>环保网站不仅是技术平台，更是教育和意识提升的工具。通过设置专门的环保教育栏目，提供有关可持续发展的文章和资源，向用户传递环保知识和理念。设计在线活动或挑战赛，激励用户采取环保行动，如减少塑料使用或参与植树活动。还可以通过社交媒体传播环保成功案例和用户故事，扩大环保意识的影响力。积极与环保组织和专家合作，确保网站内容的科学性和权威性，为用户提供更准确和有用的环保信息。通过论坛和评论功能，鼓励用户分享经验和建议，形成积极的互动社区，提高用户参与度，为网站的持续改进提供宝贵反馈。跨行业合作也是推动环保网站设计创新的重要途径，与技术公司、教育机构和部门合作，获得更多资源和支持，共同推动可持续发展目标的实现。</p><p><br></p>', '', '', '', '环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。（一）设计理念环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出', 256, '1', '0', '0', '0', 4, 0, 0, 'Admin', 'Admin', '2024-11-25 08:02:29', '2024-12-15 17:19:58', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (18, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2022-12-17 09:42:05', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'Admin', '2022-12-17 09:42:18', '2025-04-18 21:53:37', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (19, 'cn', '10', '', 'test', '#333333', '', '', '超级管理员', '本站', '', '2023-01-05 15:46:46', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 234, 0, 0, 'admin', 'admin', '2023-01-05 15:46:46', '2025-04-14 19:17:15', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (20, 'en', '13', '', '在线留言', '#333333', '', '', '超级管理员', '本站', '', '2023-01-05 20:21:52', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2023-01-05 20:21:52', '2024-09-04 20:37:12', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (60, 'cn', '9', '', '淘宝运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg,/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg', '', '&lt;p&gt;&lt;strong&gt;岗位职责：&lt;/strong&gt;&lt;/p&gt;&lt;p style=&quot;text-align:center&quot;&gt;&lt;img src=&quot;/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg&quot; alt=&quot;teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;&lt;p&gt;1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；&lt;/p&gt;&lt;p&gt;2、 参与和优化部门业务操作流程，保证团队协同工作；&lt;/p&gt;&lt;p&gt;3、 为用户提供平台业务咨询服务；&lt;/p&gt;&lt;p&gt;4、 受理客户投诉，在授权范围内予以解决；&lt;/p&gt;&lt;p&gt;5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；&lt;/p&gt;&lt;p&gt;6、 与公司其他部门配合工作。&lt;/p&gt;&lt;p style=&quot;text-align:center&quot;&gt;&lt;img src=&quot;/storage/default/20241130/logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png&quot; alt=&quot;logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;strong&gt;任职要求：&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;1、 专科及以上学历，热爱互联网行业；&lt;/p&gt;&lt;p&gt;2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；&lt;/p&gt;&lt;p&gt;3、 形象佳，口齿伶俐，普通话标准；&lt;/p&gt;&lt;p&gt;4、 熟练使用电脑，经常上网，会使用office等相关办公软件；&lt;/p&gt;&lt;p&gt;5、 能适应白班、夜班倒班工作制；&lt;/p&gt;&lt;p&gt;注：根据个人能力和特长，公司给予更多的发展及晋升空间。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;&lt;p&gt;&lt;strong&gt;工作地址：&lt;/strong&gt;                        &lt;/p&gt;&lt;h2&gt;北京市朝阳区北苑路&lt;/h2&gt;', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 4, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2025-04-11 22:49:12', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (61, 'cn', '2', '', 'badoucms 正式上线1.0.0 版本', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg,/storage/default/20241126/teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg', '', '<p><img src=\"/storage/default/20241126/teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg\" alt=\"teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg\"/></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul class=\" list-paddingleft-2\"><li><p>区域管理（多语言）</p></li><li><p>模型管理（自定义内容模型）</p></li><li><p>模型字段管理（自定义模型字段）</p></li><li><p>栏目管理</p></li><li><p>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</p></li><li><p>站点配置、公司信息</p></li><li><p>定制标签（自定义前台标签）</p></li><li><p>前台模版标签</p></li><li><p>轮播图片</p></li><li><p>多条件筛选</p></li><li><p>网站地图（sitemap）</p></li><li><p>友情链接</p></li><li><p>自定义表单</p></li><li><p>留言信息</p></li><li><p>文章内链</p></li><li><p>多条件搜索</p></li><li><p>百度推送</p></li><li><p>内容权限</p></li><li><p>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</p></li><li><p>会员字段</p></li><li><p>会员等级(设置栏目与内容浏览权限)</p></li><li><p>文章评论(回复、审核)</p></li><li><p>我的评论</p></li></ul>', 'badoucms,a,b', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 1, '1', '0', '0', '0', 29, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2025-04-17 18:35:30', '4', '', '请先充值');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (59, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (57, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (58, 'cn', '9', '', '天猫运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', '', '', '<p><strong>岗位职责：</strong></p><p>1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p>2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p>3、 为用户提供平台业务咨询服务；</p><p>4、 受理客户投诉，在授权范围内予以解决；</p><p>5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p>6、 与公司其他部门配合工作。</p><p><br></p><p><strong>任职要求：</strong></p><p>1、 专科及以上学历，热爱互联网行业；</p><p>2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p>3、 形象佳，口齿伶俐，普通话标准；</p><p>4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p>5、 能适应白班、夜班倒班工作制；</p><p>注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p><br></p><p><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2>北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 3, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2025-04-08 08:10:34', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (55, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (56, 'cn', '9', '', '拼多多运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg', '', '', '<p><strong>岗位职责：</strong></p><p>1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p>2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p>3、 为用户提供平台业务咨询服务；</p><p>4、 受理客户投诉，在授权范围内予以解决；</p><p>5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p>6、 与公司其他部门配合工作。</p><p><br></p><p><strong>任职要求：</strong></p><p>1、 专科及以上学历，热爱互联网行业；</p><p>2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p>3、 形象佳，口齿伶俐，普通话标准；</p><p>4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p>5、 能适应白班、夜班倒班工作制；</p><p>注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p><br></p><p><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2>北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 3, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2025-04-08 08:09:40', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (54, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 2, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-28 07:38:47', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (52, 'cn', '8', '', '南京xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b1415ad46278103146361019859ee60a6e978d1a57.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 254, '1', '0', '0', '0', 35, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2025-04-07 20:52:21', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (53, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (51, 'cn', '8', '', '苏州xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 253, '1', '0', '0', '0', 20, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2025-04-08 08:10:42', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (49, 'cn', '6', '', '建筑行业网站模板', '', '', '', 'Admin', '', '', '2024-11-25 07:43:57', '/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', '', '', '', '', '', '', '在当今数字化时代，建筑行业网站的制作具有至关重要的意义。对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信', 252, '1', '0', '0', '0', 28, 0, 0, 'Admin', 'Admin', '2024-11-25 08:07:46', '2025-05-03 21:27:38', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (42, 'cn', '2', '', 'badoucms功能列表', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', '', '', '<p><br/></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul class=\" list-paddingleft-2\"><li><p>区域管理（多语言）</p></li><li><p>模型管理（自定义内容模型）</p></li><li><p>模型字段管理（自定义模型字段）</p></li><li><p>栏目管理</p></li><li><p>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</p></li><li><p>站点配置、公司信息</p></li><li><p>定制标签（自定义前台标签）</p></li><li><p>前台模版标签</p></li><li><p>轮播图片</p></li><li><p>多条件筛选</p></li><li><p>网站地图（sitemap）</p></li><li><p>友情链接</p></li><li><p>自定义表单</p></li><li><p>留言信息</p></li><li><p>文章内链</p></li><li><p>多条件搜索</p></li><li><p>百度推送</p></li><li><p>内容权限</p></li><li><p>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</p></li><li><p>会员字段</p></li><li><p>会员等级(设置栏目与内容浏览权限)</p></li><li><p>文章评论(回复、审核)</p></li><li><p>我的评论</p></li></ul>', 'bc', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 3, '1', '0', '0', '0', 26, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2025-04-08 11:36:24', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (63, 'cn', '2', '', 'badoucms基于thinkphp8+vue3的网站管理系统', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p><br/></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><p ><img src=\"/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg\" alt=\"teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg\"/></p><h3>功能列表</h3><ul class=\" list-paddingleft-2\"><li><p>区域管理（多语言）</p></li><li><p>模型管理（自定义内容模型）</p></li><li><p>模型字段管理（自定义模型字段）</p></li><li><p>栏目管理</p></li><li><p>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</p></li><li><p>站点配置、公司信息</p></li><li><p>定制标签（自定义前台标签）</p></li><li><p>前台模版标签</p></li><li><p>轮播图片</p></li><li><p>多条件筛选</p></li><li><p>网站地图（sitemap）</p></li><li><p>友情链接</p></li><li><p>自定义表单</p></li><li><p>留言信息</p></li><li><p>文章内链</p></li><li><p>多条件搜索</p></li><li><p>百度推送</p></li><li><p>内容权限</p></li><li><p>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</p></li><li><p>会员字段</p></li><li><p>会员等级(设置栏目与内容浏览权限)</p></li><li><p>文章评论(回复、审核)</p></li><li><p>我的评论</p></li></ul>', 'cms', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 2, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2025-04-23 17:50:59', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (65, 'cn', '2', '', 'badoucms 模板标签', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 百度推送</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 4, '1', '0', '0', '0', 8, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2025-04-15 08:46:04', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (66, 'cn', '2', '', 'BadouCMS快速入门', '', '', '', '', '', '', '2024-11-15 08:15:20', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p><br/></p><p><br/></p><h2>图片缩放函数：resize_img</h2><p><code>resize_img(string $src_image, int $max_width = 0, int $max_height = 0, int $img_quality = 90): string</code></p><p>参数：$src_image=源图片路径 （必填）</p><p>$max_width=缩放后的宽度</p><p>$max_height=缩放后的高度</p><p>$img_quality=图片质量</p><p>在标签中使用：<code>{$item.ico|resize_img=&#39;10&#39;,&#39;10&#39;}</code></p>', 'wy', '', '', '测试新闻1', 255, '1', '0', '0', '0', 54, 0, 0, 'Admin', 'Admin', '2024-11-15 08:18:25', '2025-04-08 11:38:59', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (68, 'cn', '7', '', 'xx医疗行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:28', '/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', '', '', '<p><img src=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" alt=\"医疗网站缩略图.jpg\" data-href=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" width=\"\" height=\"\" /></p><p><strong>一、整体风格<br></strong></p><p>选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。<br></p><p><strong>二、具体图片内容<br></strong></p><ol><li>一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。</li><li>医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。</li><li>患者在电脑或手机上使用在线预约系统的场景，旁边可以有日历和确认按钮等元素。</li><li>医生查看电子病历的画面，可以有一个大屏幕显示详细的病历信息和图表。</li><li>大数据分析的图表，如柱状图、折线图等，代表医疗数据的收集和分析。</li><li>医疗行业网站的标志和标语，突出其专业性和创新性。<br></li></ol>', '', '', '', '一、整体风格选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。二、具体图片内容一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。患者在电脑或手机上', 251, '1', '0', '0', '0', 72, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:28', '2025-04-21 20:48:05', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (69, 'cn', '7', '', '外贸行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:28', '/storage/default/20241124/外贸网站缩略图4b812e48623d14f81d4e025ae42c061dad65588d.jpg', '', '', '<p><img src=\"https://p3-search.byteimg.com/obj/labis/0e409f67aba58e66c221e30d7483d51f\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. “快”—— 访问速度快</h3><p>外贸网站一般会使用海外服务器或加速节点，以此确保网站的打开速度和响应速度能与当地平均水平持平。这是因为如果网站速度过慢，会导致用户放弃访问。例如，在全球疫情的影响下，外贸企业纷纷转向外贸独立站，而网站速度慢直接影响用户体验和营销转化。目前有多种技术可以提升外贸网站的运行速度，如 Google AMP 框架，其网页可以在 Google 服务器里生成缓存，大大提升网站速度；Webp 无损压缩技术，能降低图片文件大小；Gzip 压缩技术以及 CDN 加速，通过寻找互联网上最快的访问节点来优化运行速度。此外，CDN 加速技术还能把放置于国内的网站复制一份到各个国家的服务器上，让各个国家的浏览者就近访问企业网站，提高访问响应速度。</p><p><br></p><h3>2. “简”—— 信息精简</h3><p>与国内企业网站相比，外贸网站信息量少。这一方面是因为欧美等发达国家受教育程度高的网民不追求过多过杂的信息，另一方面也与搜索引擎算法有关。谷歌不鼓励企业网站持续大量进行内容更新，尤其是无用信息的更新。外贸网站是为国外人阅读的网站，自然应该迎合他们的口味，减少信息量，避免像国内一些企业网站那样，为了迎合百度算法而持续大量更新内容，甚至包含一些无用信息。</p><p><br></p><h3>3. “直”—— 直接明了</h3><p>外贸网站的直接体现在两个方面。一是少有在线沟通工具，用户直接浏览栏目。这与欧美国家互联网环境有关，欧美用户对在线沟通工具似乎并不十分热衷，他们倾向于邮箱、电话交流。二是信息说明比较直接，少有拐弯抹角、云里雾里的 “忽悠式” 口号。这是因为欧美国家有一个较为诚信的互联网环境，用户对网站容易产生信任，所以网站在传递信息的时候不妨直来直去。</p><p><br></p><h3>4. “细”—— 做工细致</h3><p>从脚本、代码、图片、构架等方面看，欧美国家的企业网站似乎比国内一般的网站更为用心。这有两个原因，一是欧美国家非常流行工程师文化，工程师文化的一个特点就是 “抠细节”，所以外贸网站在作图、拍摄、LOGO、配色等方面要迎合工程师文化；二是欧美国家的整体代码水平和建站分工要比国内高一些。这两方面原因使得外贸网站的做工需要更细致一些。</p><p><br></p><h3>5. “严”—— 要求严格</h3><p>外贸网站对网站版权声明、个人隐私保护、用户数据泄露等方面更加重视。国内企业网站在建站时对这些方面往往不够上心，但外贸网站如果在这些方面做得不够或出现失误，有可能触碰法律风险，而且谷歌等欧美主流搜索引擎对法律条款不够完整的网站也不太友好。此外，外贸网站不允许出现错别字、语法错误和 BUG，因为这会直接导致用户对网站、企业和品牌的信任危机。</p><p><br></p><h2>二、外贸电商网站的特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/83f4c79ee7c66191137f10b53ba497cf\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 多语言支持</h3><p>外贸电商建立多语言网站至关重要，其能方便全球用户使用，打破语言障碍。建立多语言网站的步骤如下：首先要了解目标市场的语言需求和文化背景，根据不同市场特点选择需提供的语言版本；接着准备多语言网站的翻译内容，包括网站文本、图像、音频、视频等；然后选择合适的多语言网站管理平台，如 WordPress、Drupal 等，以便快速、方便地进行多语言网站的构建和管理；根据需要考虑使用专业的翻译服务、本地化工具或机器翻译等来实现网站内容的翻译；设计多语言导航和语言切换功能，使用户可以轻松切换网站语言版本；确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，以提高网站的可访问性和用户体验；最后逐步完善和优化多语言网站的内容和功能，以满足不同语言和文化需求的用户。</p><p>数据库级多语言支持可通过设计数据库结构实现，如单表存储在产品表中为每种语言添加独立列，或多表存储创建主表存储基本信息并关联翻译表；前端国际化框架依赖国际化库，将用户界面文本提取为语言文件并根据用户选择动态加载；内容管理系统（CMS）集成则可选择支持多语言的 CMS，如 WordPress、Drupal 或 Magento 等，方便用户管理不同语言的内容。</p><p><br></p><h3>2. 多货币支持</h3><p>外贸电商网站支持多货币便于用户付款和商家结算，适应跨境交易需求。不同国家和地区有不同的货币体系，多货币支持可以让用户在购物时选择自己熟悉的货币进行支付，提高购物的便利性和舒适度。同时，商家也可以更方便地进行结算，避免汇率波动带来的风险。</p><p><br></p><h3>3. 安全性和稳定性</h3><p>外贸电商网站的安全性和稳定性至关重要，它保障了用户和商家的利益，防止黑客攻击和信息泄露。使用安全证书是网站安全的基础，通过 HTTPS 协议和 SSL 加密技术，能有效防止黑客攻击和数据泄露；使用防火墙可以检测和阻止恶意流量，避免外部攻击和黑客入侵；定期备份数据能防止因病毒攻击、服务器崩溃等问题导致的数据丢失，提高数据恢复效率；加强密码安全，要求用户使用强密码并定期修改，管理员使用独特且复杂的密码并定期更换；限制对敏感信息的访问权限，只允许有必要权限的人员访问数据库和管理后台；进行安全培训，提高员工的安全意识，减少安全漏洞的发生。</p><p><br></p><h3>4. 良好的用户体验</h3><p>外贸电商网站的界面设计应简洁明了、易于操作，以提高用户满意度。简洁直观的导航能让用户轻松找到所需页面和功能；快速的加载速度优化图片、压缩代码、选择高效服务器等方式提升；响应式网站设计确保适应不同屏幕尺寸和设备；个性化推荐和定制化体验利用用户数据和历史行为分析为用户提供个性化推荐商品和定制化体验；清晰的产品信息和图片展示提供详细产品信息、规格、价格和清晰图片；简化的购物流程减少用户操作步骤，提供简单易用的结账和支付选项；安全的支付系统保护用户个人信息和支付安全；多语言支持满足不同用户语言需求；社交媒体整合方便用户分享和推荐产品；提供优质客户服务建立快速响应的客户服务渠道；用户评价和推荐增加信任和口碑效应；持续优化和改进根据用户行为和反馈数据不断提升用户体验。</p><p><br></p><h3>5. 强大的搜索功能</h3><p>外贸电商网站应具备快速高效的搜索功能，帮助用户快速找到所需商品和信息。默认全站搜索，然后通过结果分类导航，进行结果筛选、检索。提供 “相关搜索” 功能，帮访客找到更加的搜索词，还能给访客一些未想到的搜索提示。限定搜索的措施是自动提示，不仅能减少错误输入，还能帮助我们推荐产品与产品分类，避免 “无搜索结果” 的情况。</p><p><br></p><h3>6. 充足的商品信息</h3><p>外贸电商网站需提供详尽的商品介绍、规格、图片等，方便用户了解商品情况。清晰、高质量的产品图片和详细的描述信息能让用户全面了解产品特点和优势，提升购买决策的信心。</p><p><br></p><h3>7. 良好的客户服务</h3><p>外贸电商网站应提供多种渠道解答用户疑问，提升用户体验。建立快速响应的客户服务渠道，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。电子商务网站建设的售前服务包括认真回答消费者对商品的咨询、尺寸、码数、质量、售后等问题，及时回复、态度友好，提升用户满意度。</p><p><br></p><h3>8. 多样化的支付方式</h3><p>外贸电商网站支持多种支付方式，方便用户完成支付。常见的外贸电商网站支付方式有 PayPal、支付宝、银行电汇、信用卡支付等。不同国家和地区的支付习惯不同，选择合适的支付方式非常重要。要考虑支付方式的安全性、手续费用、方便用户操作等因素。</p><p><br></p><h3>9. 精准的数据分析</h3><p>外贸电商网站通过精准的数据分析了解用户需求和购买行为，优化网站设计和服务。利用用户数据和历史行为分析，为用户提供个性化的推荐商品和定制化的体验，增强用户的参与感和满意度。定期进行用户调研、用户体验测试和网站性能监测，根据反馈和数据进行改进和优化，不断提升用户体验。</p><p><br></p><h2>三、受国外喜欢的外贸网站特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/b6713d7f8b7c443fa779ddd46442deed\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 页面简洁明了</h3><p>外贸网站应避免繁琐复杂的页面设计，以简洁明了的布局吸引外国人的注意力。一个直观且简单易懂的界面可以让用户更快地了解网站的核心内容，提高用户的浏览效率。例如，减少不必要的装饰和复杂的动画效果，突出产品或服务的关键信息，使用户能够迅速找到所需内容。</p><p><br></p><h3>2. 多语言支持</h3><p>提供多语言支持可以提高网站的可访问性，满足不同国家和地区用户的语言需求。外贸网站可以根据目标市场的语言特点，提供相应的语言版本。具体实现方法包括了解目标市场的语言需求和文化背景，准备多语言网站的翻译内容，选择合适的多语言网站管理平台，如 WordPress、Drupal 等，考虑使用专业的翻译服务、本地化工具或机器翻译，设计多语言导航和语言切换功能，确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，逐步完善和优化多语言网站的内容和功能。</p><p><br></p><h3>3. 响应式网站设计</h3><p>随着移动设备的广泛使用，响应式网站设计成为外贸网站的重要特点。响应式设计能够确保网站在各种设备上，包括手机、平板和电脑，都能提供良好的用户体验。实现响应式设计可以设置关键断点，结合站点内容设置关键点，注意网站内容的有效传递；优先进行手机端设计，筛选出重要元素，避免使用大图，做垂直滚动，把搜索栏和主操作按钮放在醒目位置；扩大目标点击区域，方便用户点击；采用响应式图片或视频，避免显示不全、留白、模糊或失真的情况，可使用支持响应式的框架或设置图片属性，也可以使用 SVG 矢量图，对于视频可插入 FitVids 或 jQuery 插件实现自动缩放；进行恰当的视觉设计，注重色彩搭配，避免复杂的导航菜单、滑动效果和 Flash 动画，保证页面简洁优雅。</p><p><br></p><h3>4. 独特的视觉风格</h3><p>具有独特视觉风格的外贸网站更容易吸引外国人的关注。个性化和创新的设计能够突出网站在竞争激烈的市场中的独特性。可以从色彩搭配、字体选择、图片和视频的运用等方面打造独特的视觉效果，创造极强的视觉冲击力或营造舒适的氛围，具体取决于网站的主题内容。同时，要注意视觉设计与网站内容的协调性，确保用户在享受视觉盛宴的同时，能够轻松获取所需信息。</p><p><br></p><h3>5. 易于导航和使用</h3><p>清晰的导航结构和简单的操作流程是外贸网站受外国人喜欢的重要因素。网站应提供易于理解和直观的导航标签，帮助用户快速浏览和访问各个部分。例如，设置简洁明了的导航栏，分类合理，方便用户找到所需信息；简化购买流程，减少用户的购买障碍，提供清晰的购买按钮和操作指导；强化客户支持和沟通渠道，提供多种联系方式，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。</p><p><br></p><h3>6. 专业可信</h3><p>专业、可信的外贸网站更容易赢得外国人的信任。提供详细的产品信息、公司资质和客户评价等内容，有助于建立网站的可靠形象。展示清晰、高质量的产品图片和详细的描述信息，让用户全面了解产品特点和优势；强调公司的资质和荣誉，增强用户对公司的信心；允许用户对商品进行评价和打分，显示商品的用户反馈和满意度，积极回应用户评价，增强用户对商品的信任感。</p><p><br></p><h2>四、如何选择外贸行业网站</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/5bddf53b8ac68a48aaade06e5cc1cd90\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 国内外建站公司对比</h3><p><strong>1. 国外建站公司（以 Shopify 为例）：</strong></p><p>Shopify 是一个基于云端的电商平台，功能齐全，提供网站主机、购物车、支付处理、库存管理、订单跟踪和分析等功能，还有广泛的应用市场，允许商家使用各种应用程序来增强商店功能。其优势包括建站操作简单，拥有丰富的应用生态、引流渠道多且卖家相对自由等。但也存在一些劣势，如独立站本身没有流量需卖家自己推广，有交易费用，App 费用较高，网站程序采用小众的 Liquid 语言专业开发程序员少，备份转移不便，批发功能和多语言支持不够好等。</p><p><strong>2. 国内建站公司（以 Ueeshop、shopline、shopyy 等为例）：</strong></p><p>国内建站公司功能与国外建站公司不相上下，能满足独立站卖家需求。以 Ueeshop 为例，不抽取佣金只收年费，成本更低。语言相通，沟通方便，且一般会提供技术支持。Shopline 和 Shopyy 也有各自的特点，如免费试用时间不同、功能表和定价策略有所差异等。</p><p><br></p><h3>2. 独立站核心</h3><p>选择 SaaS 建站可节约成本和时间，将更多精力用于推广引流。SaaS 建站平台如独立站 SaaS，能够帮助企业快速搭建功能齐全的电商网站，降低启动成本和时间，提供高度定制化功能，打造独特品牌形象，增强市场竞争力。同时，通常集成多种营销工具和分析功能，有助于企业精准定位目标客户，提高营销效果和转化率。通过云端托管，确保网站高可靠性和安全性，企业无需担心服务器维护和数据安全问题。</p><p>而合适的建站公司能帮助卖家事半功倍。在选择建站公司时，要明确自己的业务需求和目标，考虑平台的稳定性和安全性、可扩展性和灵活性、用户体验和客户支持以及成本等因素。选择最适合自身需求的独立站 SaaS 服务，推动外贸业务的稳步发展。</p><p><br></p><h2>五、外贸行业网站的发展趋势</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/a363fbc361b840729a844f0d80f204a8\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 移动化趋势</h3><p>随着智能手机和移动互联网的普及，贸易活动将更多在移动端进行。如今，越来越多的消费者倾向于使用移动设备进行在线购物，这对外贸行业网站提出了新的要求。外贸网站需要适应移动化趋势，提供方便快捷的移动端服务，以满足用户随时随地进行贸易活动的需求。</p><p>例如，企业可以优化网站的移动端界面，确保在手机和平板等设备上能够流畅浏览和操作。同时，结合移动支付技术，为用户提供便捷的支付方式，提高交易效率。</p><p><br></p><h3>2. 数据驱动</h3><p>大数据分析和人工智能技术应用，提供个性化服务和推荐。在当今数字化时代，数据成为了外贸行业网站的重要资产。通过大数据分析，网站可以深入了解用户的行为、偏好和需求，从而为用户提供个性化的服务和推荐。</p><p>例如，利用用户的浏览历史、购买记录等数据，为用户推荐符合其兴趣的产品和服务。同时，人工智能技术可以帮助网站实现智能客服，自动回答用户的咨询，提高服务效率。</p><p>此外，大数据分析还可以用于优化供应链管理。通过分析销售数据和市场需求预测，企业可以实现库存的精准管理和优化，降低库存积压和滞销风险。</p><p><br></p><h3>3. 跨境电商的发展</h3><p>推动外贸网站发展，带来更多贸易机会和市场潜力。跨境电商的快速发展为外贸行业网站带来了新的机遇。随着全球贸易的日益频繁，跨境电商平台成为了企业拓展海外市场的重要渠道。</p><p>跨境电商平台具有全球性、便捷性、高效性、低成本等特点，能够满足消费者对多元化、个性化商品的需求，同时也为商家提供了更广阔的市场空间。例如，亚马逊、阿里巴巴等跨境电商平台已经成为全球贸易的重要组成部分，越来越多的企业和消费者开始使用平台进行交易。</p><p>外贸行业网站可以与跨境电商平台合作，借助平台的流量和资源，扩大自身的市场影响力。同时，网站也可以借鉴跨境电商平台的成功经验，优化自身的服务和功能，提高用户体验。</p><p><br></p><h3>4. 其他趋势</h3><p>如人工智能助力个性化用户体验、混合商务提供无缝连接客户旅程、增强现实和虚拟现实吸引观众等。</p><p>人工智能在个性化用户体验方面发挥着重要作用。通过机器学习和自然语言处理技术，网站可以更好地理解用户的需求和意图，为用户提供更加精准的推荐和服务。</p><p>混合商务模式将线上和线下渠道相结合，为用户提供无缝连接的客户旅程。外贸行业网站可以与线下实体店合作，实现线上线下融合，为用户提供更加便捷的购物体验。</p><p>增强现实和虚拟现实技术可以为用户带来更加沉浸式的购物体验。通过展示产品的 3D 模型和虚拟场景，用户可以更加直观地了解产品的特点和优势，提高购买决策的信心。</p><p><br></p><h2>六、外贸行业热门网站有哪些</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/51523bc6c767ed79db31cda3846406e1\" alt=\"\" data-href=\"\" /></p><p><br></p>', '', '', '', '外贸行业网站的特点', 253, '1', '0', '0', '0', 8, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:28', '2025-04-08 19:45:28', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (70, 'cn', '7', '', '环保行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:28', '/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', '', '', '<p>环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。</p><p><img src=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" alt=\"医疗网站缩略图(1).jpg\" data-href=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" width=\"\" height=\"\" /></p><h3>（一）设计理念</h3><p>环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出环保主题，使网站在视觉上与众不同。同时，设计过程中应考虑网站的生命周期，确保其在使用过程中能够持续降低对环境的影响。例如，避免大面积使用深色背景，减少动画和视频的使用频率，以降低能源消耗。</p><p><br></p><h3>（二）节能优化</h3><p>能效优化是环保网站提升可持续性的重要手段。优化图片和多媒体文件的大小和格式，如使用现代的图像格式 WebP，能够在不损失质量的前提下减少文件大小，显著降低页面加载时间和服务器能源消耗。采用内容分发网络（CDN）技术，将网站内容分布到全球多个数据中心，减少用户访问的延迟和带宽消耗，提高用户体验的同时降低服务器的能源使用。选择绿色托管服务商也是关键一步，许多托管服务商已开始使用可再生能源为数据中心供电，有效减少网站的碳足迹。</p><p><br></p><h3>（三）可持续材料使用</h3><p>在网站建设中，可以选择可再生资源，如使用可再生能源供电、选择可再生材料制作硬件等。同时，资源循环利用也是实现可持续发展的重要途径，例如回收旧硬件、重复利用设计元素等，减少资源浪费。此外，通过选择高质量、耐用的硬件，减少硬件更换频率，可以有效减少电子垃圾的产生。</p><p><br></p><h3>（四）用户体验</h3><p>合理的导航和信息架构设计能帮助用户快速找到所需信息，减少不必要的点击和页面加载。响应式设计确保网站在各种设备上都能顺畅运行，提高用户满意度，减少因设备不兼容而产生的资源浪费。提供个性化和互动性的内容，增强用户的参与感和忠诚度。通过数据分析和用户反馈，不断优化网站功能和内容，提升整体用户体验。</p><p><br></p><h3>（五）内容管理</h3><p>网站内容应定期更新和优化，提供高质量的环保知识科普文章、视频和图片，以及及时发布环保新闻、报告等，确保内容的质量、准确性和实用性。使用内容管理系统（CMS），更高效地管理和更新网站内容。采用缓存技术减少服务器请求次数，提高网站加载速度，降低服务器负载和能源消耗。同时，对内容进行压缩与优化，如压缩图像、视频等多媒体文件，减少数据传输量，降低服务器负载和能耗。</p><p><br></p><h3>（六）社会责任</h3><p>环保网站不仅是技术平台，更是教育和意识提升的工具。通过设置专门的环保教育栏目，提供有关可持续发展的文章和资源，向用户传递环保知识和理念。设计在线活动或挑战赛，激励用户采取环保行动，如减少塑料使用或参与植树活动。还可以通过社交媒体传播环保成功案例和用户故事，扩大环保意识的影响力。积极与环保组织和专家合作，确保网站内容的科学性和权威性，为用户提供更准确和有用的环保信息。通过论坛和评论功能，鼓励用户分享经验和建议，形成积极的互动社区，提高用户参与度，为网站的持续改进提供宝贵反馈。跨行业合作也是推动环保网站设计创新的重要途径，与技术公司、教育机构和部门合作，获得更多资源和支持，共同推动可持续发展目标的实现。</p><p><br></p>', '', '', '', '环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。（一）设计理念环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出', 256, '1', '0', '0', '0', 4, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:28', '2025-04-08 19:45:28', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (71, 'cn', '7', '', '建筑行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:28', '/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', '', '', '<p>在当今数字化时代，建筑行业网站的制作具有至关重要的意义。<br><img src=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg\" alt=\"医疗网站缩略图(2).jpg\" data-href=\"http://badoucms.test/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg\" width=\"\" height=\"\" /></p><p>对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信度和美誉度。<br></p><p>对于行业从业者而言，建筑行业网站是获取信息和交流的平台。在这里，他们可以了解到最新的行业动态、政策法规、技术创新等信息，不断提升自己的专业素养。网站上的论坛和社区功能，还能让从业者们分享经验、交流心得，促进整个行业的共同进步。<br></p><p>从客户角度来看，建筑行业网站方便他们寻找可靠的建筑服务提供商。客户可以通过网站对比不同企业的优势和特点，选择最符合自己需求的合作伙伴。而且，网站上的在线咨询和预约服务，也为客户提供了便捷的沟通渠道。<br></p><p>此外，建筑行业网站还有助于提升行业的透明度和规范性。通过展示企业的资质证书、荣誉奖项等信息，让客户能够更加放心地选择合作对象。同时，也促使建筑企业不断提高自身的管理水平和服务质量，以在激烈的市场竞争中脱颖而出。<br></p><p>总之，建筑行业网站的制作是顺应时代发展的必然选择，它将为建筑行业的发展注入新的活力。</p>', '', '', '', '在当今数字化时代，建筑行业网站的制作具有至关重要的意义。对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信', 252, '1', '0', '0', '0', 29, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:28', '2025-04-14 19:17:06', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (72, 'cn', '6', '7', 'xx医疗行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:37', '/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', '', '', '<p><img src=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" alt=\"医疗网站缩略图.jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" width=\"\" height=\"\"/></p>', 'a', '', '', '一、整体风格选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。二、具体图片内容一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。患者在电脑或手机上', 251, '1', '0', '0', '0', 71, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:37', '2025-05-03 22:06:01', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (73, 'cn', '7', '', '外贸行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:37', '/storage/default/20241124/外贸网站缩略图4b812e48623d14f81d4e025ae42c061dad65588d.jpg', '', '', '<p><img src=\"https://p3-search.byteimg.com/obj/labis/0e409f67aba58e66c221e30d7483d51f\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. “快”—— 访问速度快</h3><p>外贸网站一般会使用海外服务器或加速节点，以此确保网站的打开速度和响应速度能与当地平均水平持平。这是因为如果网站速度过慢，会导致用户放弃访问。例如，在全球疫情的影响下，外贸企业纷纷转向外贸独立站，而网站速度慢直接影响用户体验和营销转化。目前有多种技术可以提升外贸网站的运行速度，如 Google AMP 框架，其网页可以在 Google 服务器里生成缓存，大大提升网站速度；Webp 无损压缩技术，能降低图片文件大小；Gzip 压缩技术以及 CDN 加速，通过寻找互联网上最快的访问节点来优化运行速度。此外，CDN 加速技术还能把放置于国内的网站复制一份到各个国家的服务器上，让各个国家的浏览者就近访问企业网站，提高访问响应速度。</p><p><br></p><h3>2. “简”—— 信息精简</h3><p>与国内企业网站相比，外贸网站信息量少。这一方面是因为欧美等发达国家受教育程度高的网民不追求过多过杂的信息，另一方面也与搜索引擎算法有关。谷歌不鼓励企业网站持续大量进行内容更新，尤其是无用信息的更新。外贸网站是为国外人阅读的网站，自然应该迎合他们的口味，减少信息量，避免像国内一些企业网站那样，为了迎合百度算法而持续大量更新内容，甚至包含一些无用信息。</p><p><br></p><h3>3. “直”—— 直接明了</h3><p>外贸网站的直接体现在两个方面。一是少有在线沟通工具，用户直接浏览栏目。这与欧美国家互联网环境有关，欧美用户对在线沟通工具似乎并不十分热衷，他们倾向于邮箱、电话交流。二是信息说明比较直接，少有拐弯抹角、云里雾里的 “忽悠式” 口号。这是因为欧美国家有一个较为诚信的互联网环境，用户对网站容易产生信任，所以网站在传递信息的时候不妨直来直去。</p><p><br></p><h3>4. “细”—— 做工细致</h3><p>从脚本、代码、图片、构架等方面看，欧美国家的企业网站似乎比国内一般的网站更为用心。这有两个原因，一是欧美国家非常流行工程师文化，工程师文化的一个特点就是 “抠细节”，所以外贸网站在作图、拍摄、LOGO、配色等方面要迎合工程师文化；二是欧美国家的整体代码水平和建站分工要比国内高一些。这两方面原因使得外贸网站的做工需要更细致一些。</p><p><br></p><h3>5. “严”—— 要求严格</h3><p>外贸网站对网站版权声明、个人隐私保护、用户数据泄露等方面更加重视。国内企业网站在建站时对这些方面往往不够上心，但外贸网站如果在这些方面做得不够或出现失误，有可能触碰法律风险，而且谷歌等欧美主流搜索引擎对法律条款不够完整的网站也不太友好。此外，外贸网站不允许出现错别字、语法错误和 BUG，因为这会直接导致用户对网站、企业和品牌的信任危机。</p><p><br></p><h2>二、外贸电商网站的特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/83f4c79ee7c66191137f10b53ba497cf\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 多语言支持</h3><p>外贸电商建立多语言网站至关重要，其能方便全球用户使用，打破语言障碍。建立多语言网站的步骤如下：首先要了解目标市场的语言需求和文化背景，根据不同市场特点选择需提供的语言版本；接着准备多语言网站的翻译内容，包括网站文本、图像、音频、视频等；然后选择合适的多语言网站管理平台，如 WordPress、Drupal 等，以便快速、方便地进行多语言网站的构建和管理；根据需要考虑使用专业的翻译服务、本地化工具或机器翻译等来实现网站内容的翻译；设计多语言导航和语言切换功能，使用户可以轻松切换网站语言版本；确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，以提高网站的可访问性和用户体验；最后逐步完善和优化多语言网站的内容和功能，以满足不同语言和文化需求的用户。</p><p>数据库级多语言支持可通过设计数据库结构实现，如单表存储在产品表中为每种语言添加独立列，或多表存储创建主表存储基本信息并关联翻译表；前端国际化框架依赖国际化库，将用户界面文本提取为语言文件并根据用户选择动态加载；内容管理系统（CMS）集成则可选择支持多语言的 CMS，如 WordPress、Drupal 或 Magento 等，方便用户管理不同语言的内容。</p><p><br></p><h3>2. 多货币支持</h3><p>外贸电商网站支持多货币便于用户付款和商家结算，适应跨境交易需求。不同国家和地区有不同的货币体系，多货币支持可以让用户在购物时选择自己熟悉的货币进行支付，提高购物的便利性和舒适度。同时，商家也可以更方便地进行结算，避免汇率波动带来的风险。</p><p><br></p><h3>3. 安全性和稳定性</h3><p>外贸电商网站的安全性和稳定性至关重要，它保障了用户和商家的利益，防止黑客攻击和信息泄露。使用安全证书是网站安全的基础，通过 HTTPS 协议和 SSL 加密技术，能有效防止黑客攻击和数据泄露；使用防火墙可以检测和阻止恶意流量，避免外部攻击和黑客入侵；定期备份数据能防止因病毒攻击、服务器崩溃等问题导致的数据丢失，提高数据恢复效率；加强密码安全，要求用户使用强密码并定期修改，管理员使用独特且复杂的密码并定期更换；限制对敏感信息的访问权限，只允许有必要权限的人员访问数据库和管理后台；进行安全培训，提高员工的安全意识，减少安全漏洞的发生。</p><p><br></p><h3>4. 良好的用户体验</h3><p>外贸电商网站的界面设计应简洁明了、易于操作，以提高用户满意度。简洁直观的导航能让用户轻松找到所需页面和功能；快速的加载速度优化图片、压缩代码、选择高效服务器等方式提升；响应式网站设计确保适应不同屏幕尺寸和设备；个性化推荐和定制化体验利用用户数据和历史行为分析为用户提供个性化推荐商品和定制化体验；清晰的产品信息和图片展示提供详细产品信息、规格、价格和清晰图片；简化的购物流程减少用户操作步骤，提供简单易用的结账和支付选项；安全的支付系统保护用户个人信息和支付安全；多语言支持满足不同用户语言需求；社交媒体整合方便用户分享和推荐产品；提供优质客户服务建立快速响应的客户服务渠道；用户评价和推荐增加信任和口碑效应；持续优化和改进根据用户行为和反馈数据不断提升用户体验。</p><p><br></p><h3>5. 强大的搜索功能</h3><p>外贸电商网站应具备快速高效的搜索功能，帮助用户快速找到所需商品和信息。默认全站搜索，然后通过结果分类导航，进行结果筛选、检索。提供 “相关搜索” 功能，帮访客找到更加的搜索词，还能给访客一些未想到的搜索提示。限定搜索的措施是自动提示，不仅能减少错误输入，还能帮助我们推荐产品与产品分类，避免 “无搜索结果” 的情况。</p><p><br></p><h3>6. 充足的商品信息</h3><p>外贸电商网站需提供详尽的商品介绍、规格、图片等，方便用户了解商品情况。清晰、高质量的产品图片和详细的描述信息能让用户全面了解产品特点和优势，提升购买决策的信心。</p><p><br></p><h3>7. 良好的客户服务</h3><p>外贸电商网站应提供多种渠道解答用户疑问，提升用户体验。建立快速响应的客户服务渠道，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。电子商务网站建设的售前服务包括认真回答消费者对商品的咨询、尺寸、码数、质量、售后等问题，及时回复、态度友好，提升用户满意度。</p><p><br></p><h3>8. 多样化的支付方式</h3><p>外贸电商网站支持多种支付方式，方便用户完成支付。常见的外贸电商网站支付方式有 PayPal、支付宝、银行电汇、信用卡支付等。不同国家和地区的支付习惯不同，选择合适的支付方式非常重要。要考虑支付方式的安全性、手续费用、方便用户操作等因素。</p><p><br></p><h3>9. 精准的数据分析</h3><p>外贸电商网站通过精准的数据分析了解用户需求和购买行为，优化网站设计和服务。利用用户数据和历史行为分析，为用户提供个性化的推荐商品和定制化的体验，增强用户的参与感和满意度。定期进行用户调研、用户体验测试和网站性能监测，根据反馈和数据进行改进和优化，不断提升用户体验。</p><p><br></p><h2>三、受国外喜欢的外贸网站特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/b6713d7f8b7c443fa779ddd46442deed\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 页面简洁明了</h3><p>外贸网站应避免繁琐复杂的页面设计，以简洁明了的布局吸引外国人的注意力。一个直观且简单易懂的界面可以让用户更快地了解网站的核心内容，提高用户的浏览效率。例如，减少不必要的装饰和复杂的动画效果，突出产品或服务的关键信息，使用户能够迅速找到所需内容。</p><p><br></p><h3>2. 多语言支持</h3><p>提供多语言支持可以提高网站的可访问性，满足不同国家和地区用户的语言需求。外贸网站可以根据目标市场的语言特点，提供相应的语言版本。具体实现方法包括了解目标市场的语言需求和文化背景，准备多语言网站的翻译内容，选择合适的多语言网站管理平台，如 WordPress、Drupal 等，考虑使用专业的翻译服务、本地化工具或机器翻译，设计多语言导航和语言切换功能，确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，逐步完善和优化多语言网站的内容和功能。</p><p><br></p><h3>3. 响应式网站设计</h3><p>随着移动设备的广泛使用，响应式网站设计成为外贸网站的重要特点。响应式设计能够确保网站在各种设备上，包括手机、平板和电脑，都能提供良好的用户体验。实现响应式设计可以设置关键断点，结合站点内容设置关键点，注意网站内容的有效传递；优先进行手机端设计，筛选出重要元素，避免使用大图，做垂直滚动，把搜索栏和主操作按钮放在醒目位置；扩大目标点击区域，方便用户点击；采用响应式图片或视频，避免显示不全、留白、模糊或失真的情况，可使用支持响应式的框架或设置图片属性，也可以使用 SVG 矢量图，对于视频可插入 FitVids 或 jQuery 插件实现自动缩放；进行恰当的视觉设计，注重色彩搭配，避免复杂的导航菜单、滑动效果和 Flash 动画，保证页面简洁优雅。</p><p><br></p><h3>4. 独特的视觉风格</h3><p>具有独特视觉风格的外贸网站更容易吸引外国人的关注。个性化和创新的设计能够突出网站在竞争激烈的市场中的独特性。可以从色彩搭配、字体选择、图片和视频的运用等方面打造独特的视觉效果，创造极强的视觉冲击力或营造舒适的氛围，具体取决于网站的主题内容。同时，要注意视觉设计与网站内容的协调性，确保用户在享受视觉盛宴的同时，能够轻松获取所需信息。</p><p><br></p><h3>5. 易于导航和使用</h3><p>清晰的导航结构和简单的操作流程是外贸网站受外国人喜欢的重要因素。网站应提供易于理解和直观的导航标签，帮助用户快速浏览和访问各个部分。例如，设置简洁明了的导航栏，分类合理，方便用户找到所需信息；简化购买流程，减少用户的购买障碍，提供清晰的购买按钮和操作指导；强化客户支持和沟通渠道，提供多种联系方式，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。</p><p><br></p><h3>6. 专业可信</h3><p>专业、可信的外贸网站更容易赢得外国人的信任。提供详细的产品信息、公司资质和客户评价等内容，有助于建立网站的可靠形象。展示清晰、高质量的产品图片和详细的描述信息，让用户全面了解产品特点和优势；强调公司的资质和荣誉，增强用户对公司的信心；允许用户对商品进行评价和打分，显示商品的用户反馈和满意度，积极回应用户评价，增强用户对商品的信任感。</p><p><br></p><h2>四、如何选择外贸行业网站</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/5bddf53b8ac68a48aaade06e5cc1cd90\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 国内外建站公司对比</h3><p><strong>1. 国外建站公司（以 Shopify 为例）：</strong></p><p>Shopify 是一个基于云端的电商平台，功能齐全，提供网站主机、购物车、支付处理、库存管理、订单跟踪和分析等功能，还有广泛的应用市场，允许商家使用各种应用程序来增强商店功能。其优势包括建站操作简单，拥有丰富的应用生态、引流渠道多且卖家相对自由等。但也存在一些劣势，如独立站本身没有流量需卖家自己推广，有交易费用，App 费用较高，网站程序采用小众的 Liquid 语言专业开发程序员少，备份转移不便，批发功能和多语言支持不够好等。</p><p><strong>2. 国内建站公司（以 Ueeshop、shopline、shopyy 等为例）：</strong></p><p>国内建站公司功能与国外建站公司不相上下，能满足独立站卖家需求。以 Ueeshop 为例，不抽取佣金只收年费，成本更低。语言相通，沟通方便，且一般会提供技术支持。Shopline 和 Shopyy 也有各自的特点，如免费试用时间不同、功能表和定价策略有所差异等。</p><p><br></p><h3>2. 独立站核心</h3><p>选择 SaaS 建站可节约成本和时间，将更多精力用于推广引流。SaaS 建站平台如独立站 SaaS，能够帮助企业快速搭建功能齐全的电商网站，降低启动成本和时间，提供高度定制化功能，打造独特品牌形象，增强市场竞争力。同时，通常集成多种营销工具和分析功能，有助于企业精准定位目标客户，提高营销效果和转化率。通过云端托管，确保网站高可靠性和安全性，企业无需担心服务器维护和数据安全问题。</p><p>而合适的建站公司能帮助卖家事半功倍。在选择建站公司时，要明确自己的业务需求和目标，考虑平台的稳定性和安全性、可扩展性和灵活性、用户体验和客户支持以及成本等因素。选择最适合自身需求的独立站 SaaS 服务，推动外贸业务的稳步发展。</p><p><br></p><h2>五、外贸行业网站的发展趋势</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/a363fbc361b840729a844f0d80f204a8\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 移动化趋势</h3><p>随着智能手机和移动互联网的普及，贸易活动将更多在移动端进行。如今，越来越多的消费者倾向于使用移动设备进行在线购物，这对外贸行业网站提出了新的要求。外贸网站需要适应移动化趋势，提供方便快捷的移动端服务，以满足用户随时随地进行贸易活动的需求。</p><p>例如，企业可以优化网站的移动端界面，确保在手机和平板等设备上能够流畅浏览和操作。同时，结合移动支付技术，为用户提供便捷的支付方式，提高交易效率。</p><p><br></p><h3>2. 数据驱动</h3><p>大数据分析和人工智能技术应用，提供个性化服务和推荐。在当今数字化时代，数据成为了外贸行业网站的重要资产。通过大数据分析，网站可以深入了解用户的行为、偏好和需求，从而为用户提供个性化的服务和推荐。</p><p>例如，利用用户的浏览历史、购买记录等数据，为用户推荐符合其兴趣的产品和服务。同时，人工智能技术可以帮助网站实现智能客服，自动回答用户的咨询，提高服务效率。</p><p>此外，大数据分析还可以用于优化供应链管理。通过分析销售数据和市场需求预测，企业可以实现库存的精准管理和优化，降低库存积压和滞销风险。</p><p><br></p><h3>3. 跨境电商的发展</h3><p>推动外贸网站发展，带来更多贸易机会和市场潜力。跨境电商的快速发展为外贸行业网站带来了新的机遇。随着全球贸易的日益频繁，跨境电商平台成为了企业拓展海外市场的重要渠道。</p><p>跨境电商平台具有全球性、便捷性、高效性、低成本等特点，能够满足消费者对多元化、个性化商品的需求，同时也为商家提供了更广阔的市场空间。例如，亚马逊、阿里巴巴等跨境电商平台已经成为全球贸易的重要组成部分，越来越多的企业和消费者开始使用平台进行交易。</p><p>外贸行业网站可以与跨境电商平台合作，借助平台的流量和资源，扩大自身的市场影响力。同时，网站也可以借鉴跨境电商平台的成功经验，优化自身的服务和功能，提高用户体验。</p><p><br></p><h3>4. 其他趋势</h3><p>如人工智能助力个性化用户体验、混合商务提供无缝连接客户旅程、增强现实和虚拟现实吸引观众等。</p><p>人工智能在个性化用户体验方面发挥着重要作用。通过机器学习和自然语言处理技术，网站可以更好地理解用户的需求和意图，为用户提供更加精准的推荐和服务。</p><p>混合商务模式将线上和线下渠道相结合，为用户提供无缝连接的客户旅程。外贸行业网站可以与线下实体店合作，实现线上线下融合，为用户提供更加便捷的购物体验。</p><p>增强现实和虚拟现实技术可以为用户带来更加沉浸式的购物体验。通过展示产品的 3D 模型和虚拟场景，用户可以更加直观地了解产品的特点和优势，提高购买决策的信心。</p><p><br></p><h2>六、外贸行业热门网站有哪些</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/51523bc6c767ed79db31cda3846406e1\" alt=\"\" data-href=\"\" /></p><p><br></p>', '', '', '', '外贸行业网站的特点', 253, '1', '0', '0', '0', 8, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:37', '2025-04-08 19:45:37', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (74, 'cn', '7', '', '环保行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:38', '/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', '', '', '<p>环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。</p><p><img src=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" alt=\"医疗网站缩略图(1).jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" width=\"\" height=\"\" /></p><h3>（一）设计理念</h3><p>环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出环保主题，使网站在视觉上与众不同。同时，设计过程中应考虑网站的生命周期，确保其在使用过程中能够持续降低对环境的影响。例如，避免大面积使用深色背景，减少动画和视频的使用频率，以降低能源消耗。</p><p><br></p><h3>（二）节能优化</h3><p>能效优化是环保网站提升可持续性的重要手段。优化图片和多媒体文件的大小和格式，如使用现代的图像格式 WebP，能够在不损失质量的前提下减少文件大小，显著降低页面加载时间和服务器能源消耗。采用内容分发网络（CDN）技术，将网站内容分布到全球多个数据中心，减少用户访问的延迟和带宽消耗，提高用户体验的同时降低服务器的能源使用。选择绿色托管服务商也是关键一步，许多托管服务商已开始使用可再生能源为数据中心供电，有效减少网站的碳足迹。</p><p><br></p><h3>（三）可持续材料使用</h3><p>在网站建设中，可以选择可再生资源，如使用可再生能源供电、选择可再生材料制作硬件等。同时，资源循环利用也是实现可持续发展的重要途径，例如回收旧硬件、重复利用设计元素等，减少资源浪费。此外，通过选择高质量、耐用的硬件，减少硬件更换频率，可以有效减少电子垃圾的产生。</p><p><br></p><h3>（四）用户体验</h3><p>合理的导航和信息架构设计能帮助用户快速找到所需信息，减少不必要的点击和页面加载。响应式设计确保网站在各种设备上都能顺畅运行，提高用户满意度，减少因设备不兼容而产生的资源浪费。提供个性化和互动性的内容，增强用户的参与感和忠诚度。通过数据分析和用户反馈，不断优化网站功能和内容，提升整体用户体验。</p><p><br></p><h3>（五）内容管理</h3><p>网站内容应定期更新和优化，提供高质量的环保知识科普文章、视频和图片，以及及时发布环保新闻、报告等，确保内容的质量、准确性和实用性。使用内容管理系统（CMS），更高效地管理和更新网站内容。采用缓存技术减少服务器请求次数，提高网站加载速度，降低服务器负载和能源消耗。同时，对内容进行压缩与优化，如压缩图像、视频等多媒体文件，减少数据传输量，降低服务器负载和能耗。</p><p><br></p><h3>（六）社会责任</h3><p>环保网站不仅是技术平台，更是教育和意识提升的工具。通过设置专门的环保教育栏目，提供有关可持续发展的文章和资源，向用户传递环保知识和理念。设计在线活动或挑战赛，激励用户采取环保行动，如减少塑料使用或参与植树活动。还可以通过社交媒体传播环保成功案例和用户故事，扩大环保意识的影响力。积极与环保组织和专家合作，确保网站内容的科学性和权威性，为用户提供更准确和有用的环保信息。通过论坛和评论功能，鼓励用户分享经验和建议，形成积极的互动社区，提高用户参与度，为网站的持续改进提供宝贵反馈。跨行业合作也是推动环保网站设计创新的重要途径，与技术公司、教育机构和部门合作，获得更多资源和支持，共同推动可持续发展目标的实现。</p><p><br></p>', '', '', '', '环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。（一）设计理念环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出', 256, '1', '0', '0', '0', 4, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:38', '2025-04-14 19:11:37', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (75, 'cn', '7', '', '建筑行业网站模板', '', '', '', 'Admin', '', '', '2025-04-08 19:45:38', '/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', '', '', '', '', '', '', '在当今数字化时代，建筑行业网站的制作具有至关重要的意义。对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信', 252, '1', '0', '0', '0', 35, 0, 0, 'Admin', 'Admin', '2025-04-08 19:45:38', '2025-05-03 21:52:11', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (76, 'cn', '2', '', '1111', '', '', '', 'Admin', '', '', '2025-04-11 19:12:09', '', '', '', '<p>1111</p>', '', '', '', '1111', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-11 21:56:21', '2025-04-11 21:56:32', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (77, 'cn', '34', '', 'aaa', '', '', '', 'Admin', '', '', '2025-04-11 21:56:26', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '', 0, 0, 0, 'Admin', 'Admin', '2025-04-11 21:57:56', '2025-04-11 21:57:56', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (78, 'cn', '34', '', '11231', '', '', '', 'Admin', '', '', '2025-04-11 21:56:26', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-11 21:59:05', '2025-04-11 21:59:05', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (79, 'cn', '34', '', 'aaa1', '', '', '', 'Admin', '', '', '2025-04-11 21:56:26', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2025-04-11 22:00:47', '2025-04-11 22:03:34', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (83, 'cn', '2', '', '1111', '', '', '', 'Admin', '', '', '2025-04-14 19:02:55', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p>sss</p>', '', '', '', 'sss', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-14 19:03:08', '2025-04-14 19:03:08', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (84, 'cn', '5', '', '1111', '', '', '', 'Admin', '', '', '2025-04-14 19:17:38', '/storage/default/20241030/badoucms.com.png', '', '', '<p>sdsdfsd</p>', '', '', '', 'sdsdfsd', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2025-04-14 19:17:49', '2025-04-15 08:49:58', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (85, 'en', '38', '', '111111', '', '', '', 'Admin', '', '', '2025-04-18 21:52:47', '', '', '', '<p>sdfsfdsdf</p>', '', '', '', 'sdfsfdsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:53:00', '2025-04-18 21:53:00', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (86, 'en', '12', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:53:13', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2025-04-18 21:53:13', '2025-04-18 21:53:13', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (87, 'en', '38', '', '212', '', '', '', 'Admin', '', '', '2025-04-18 21:52:47', '', '', '', '<p>adsdf</p>', '', '', '', 'adsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:53:48', '2025-04-18 21:53:48', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (88, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:53:55', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'Admin', '2025-04-18 21:53:55', '2025-04-18 21:53:55', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (89, 'en', '38', '', '111111', '', '', '', 'Admin', '', '', '2025-04-18 21:53:55', '', '', '', '<p>sdfsfdsdf</p>', '', '', '', 'sdfsfdsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:53:55', '2025-04-18 21:53:55', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (90, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:53:55', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2025-04-18 21:53:55', '2025-04-18 21:53:55', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (91, 'en', '38', '', '212', '', '', '', 'Admin', '', '', '2025-04-18 21:53:55', '', '', '', '<p>adsdf</p>', '', '', '', 'adsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:53:55', '2025-04-18 21:53:55', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (92, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:54:02', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'Admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (93, 'en', '38', '', '111111', '', '', '', 'Admin', '', '', '2025-04-18 21:54:02', '', '', '', '<p>sdfsfdsdf</p>', '', '', '', 'sdfsfdsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (94, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:54:02', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (95, 'en', '38', '', '212', '', '', '', 'Admin', '', '', '2025-04-18 21:54:02', '', '', '', '<p>adsdf</p>', '', '', '', 'adsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (96, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:54:02', '', '', '', '<p>sdfsdf</p>', '', '', '', 'sdfsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'Admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (97, 'en', '38', '', '111111', '', '', '', 'Admin', '', '', '2025-04-18 21:54:02', '', '', '', '<p>sdfsfdsdf</p>', '', '', '', 'sdfsfdsdf', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (98, 'en', '38', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2025-04-18 21:54:02', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2025-04-18 21:54:02', '2025-04-18 21:54:02', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (99, 'en', '38', '', '212', '', '', '', 'Admin', '', '', '2025-04-18 21:54:02', '', '', '', '<p>adsdf</p>', '', '', '', 'adsdf', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2025-04-18 21:54:02', '2025-05-26 10:09:58', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (100, 'oe', '41', '', '1111', '', '', '', 'Admin', '', '', '2025-04-21 18:16:30', '', '', '', '<p>ssdd</p>', '', '', '', 'ssdd', 255, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2025-04-21 18:16:40', '2025-04-21 18:22:49', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (103, 'en', '47', '', '111', '#333333', '', '', 'Admin', '本站', '', '2025-05-25 21:26:23', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-05-25 21:26:23', '2025-05-25 21:26:23', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (102, 'cn', '46', '', 'sdd', '#333333', '', '', 'Admin', '本站', '', '2025-05-25 21:09:18', '/uploads/20250531/b2fa3dac61da74c5858cdb7672523348.png', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2025-05-25 21:09:18', '2025-05-31 21:07:49', '4', '0', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_content_ext
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_content_ext`;
CREATE TABLE `bd_cms_content_ext` (
  `extid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contentid` int(10) unsigned NOT NULL,
  `ext_price` varchar(100) DEFAULT NULL COMMENT '产品价格',
  `ext_type` varchar(100) DEFAULT NULL COMMENT '类型',
  `ext_color` varchar(100) DEFAULT NULL COMMENT '颜色',
  `ext_aaa` varchar(255) DEFAULT '',
  `ext_aaaaa` varchar(255) DEFAULT '',
  `ext_aaaaaa` varchar(100) DEFAULT '',
  `ext_aaaaa1` varchar(100) DEFAULT '',
  `ext_aaaa` varchar(100) DEFAULT '',
  `ext_aaabbbb` varchar(100) DEFAULT '',
  `ext_a` varchar(100) DEFAULT '',
  `ext_ab` varchar(100) DEFAULT '',
  `ext_aa1` varchar(100) DEFAULT '',
  `ext_22` varchar(100) DEFAULT '',
  PRIMARY KEY (`extid`),
  KEY `ay_content_ext_contentid` (`contentid`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COMMENT='CMS文章内容-自定义字段';

-- ----------------------------
-- Records of bd_cms_content_ext
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (1, 9, '80', '专业版', '红色,黄色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (2, 10, '999', '基础版', '黄色,绿色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (3, 11, '1999', '旗舰版', '蓝色,紫色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (4, 12, '2999', '专业版', '黄色,绿色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (5, 13, '150', '基础版', '红色,橙色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (6, 18, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (7, 26, '999', '基础版,专业版', '红色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (8, 24, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (9, 23, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (10, 27, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (11, 25, NULL, '基础版', '', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (12, 14, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (13, 32, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (14, 41, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (15, 42, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (16, 43, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (17, 1, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (18, 3, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (19, 46, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (20, 47, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (21, 48, NULL, '基础版', '绿色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (22, 49, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (23, 52, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (24, 50, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (25, 53, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (26, 60, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (27, 56, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (28, 58, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (29, 61, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (30, 63, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (31, 65, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (32, 66, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (33, 67, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (34, 68, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (35, 69, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (36, 70, NULL, '基础版', '绿色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (37, 71, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (38, 72, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (39, 73, NULL, '基础版', '蓝色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (40, 74, NULL, '基础版', '绿色', '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (41, 75, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (42, 76, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (43, 77, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (44, 78, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (45, 79, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (46, 45, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (47, 80, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (48, 83, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (49, 84, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (50, 85, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (51, 86, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (52, 87, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (53, 88, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (54, 89, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (55, 90, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (56, 91, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (57, 92, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (58, 93, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (59, 94, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (60, 95, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (61, 96, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (62, 97, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (63, 98, '99', '基础版,专业版', NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (64, 99, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`, `ext_aaaaaa`, `ext_aaaaa1`, `ext_aaaa`, `ext_aaabbbb`, `ext_a`, `ext_ab`, `ext_aa1`, `ext_22`) VALUES (65, 100, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_content_sort
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_content_sort`;
CREATE TABLE `bd_cms_content_sort` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `scode` (`scode`),
  KEY `pcode` (`pcode`),
  KEY `acode` (`acode`),
  KEY `mcode` (`mcode`),
  KEY `filename` (`filename`),
  KEY `sorting` (`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='栏目表';

-- ----------------------------
-- Records of bd_cms_content_sort
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (1, 'cn', '1', '0', '1', '公司简介', '', 'about.html', '1', '', '网站建设「一站式」服务商', '', '', '', '', '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', '', '', 'aboutus', 99, 'admin', 'Admin', '2018-04-11 17:26:11', '2025-04-14 17:28:16', '', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (2, 'cn', '2', '0', '2', '新闻中心', 'newslist.html', 'news.html', '1', '', '了解最新公司动态及行业资讯', '', '', '', '', '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', '', '', 'article', 100, 'admin', 'Admin', '2018-04-11 17:26:46', '2024-11-24 20:56:12', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (5, 'cn', '3', '0', '5', '产品中心', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'product', 100, 'admin', 'Admin', '2018-04-11 17:27:54', '2024-11-30 18:19:44', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (6, 'cn', '3', '5', '6', '网站建设', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'website', 255, 'admin', 'Admin', '2018-04-11 17:28:19', '2024-11-30 17:56:51', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (7, 'cn', '3', '5', '7', '域名空间', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'domain', 255, 'admin', 'Admin', '2018-04-11 17:28:38', '2024-11-30 17:56:37', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (8, 'cn', '4', '0', '8', '服务案例', 'caselist.html', 'case.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241030/banner1a74d79711756abdf59740aa6be5750e81f8d0218.jpeg', '', '', '', 'case', 255, 'admin', 'Admin', '2018-04-11 17:29:16', '2024-11-25 21:50:55', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (9, 'cn', '5', '0', '9', '招贤纳士', 'joblist.html', 'job.html', '1', '', '诚聘优秀人士加入我们的团队', '', '', '', '', '/storage/default/20241125/ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', '', '', '', 'job', 255, 'admin', 'Admin', '2018-04-11 17:30:02', '2024-11-26 07:31:09', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (10, 'cn', '1', '0', '10', '在线留言', '', 'message.html', '1', '', '有什么问题欢迎您随时反馈', '', '', '', '', '', '', '', '', 'gbook', 800, 'admin', 'Admin', '2018-04-11 17:30:36', '2024-11-30 18:20:15', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (11, 'cn', '1', '0', '11', '联系我们', '', 'about.html', '1', '', '能为您服务是我们的荣幸', '', '', '', '', '', '', '', '', 'contact', 999, 'admin', 'Admin', '2018-04-11 17:31:29', '2025-04-11 23:19:26', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (13, 'en', '1', '0', '13', '在线留言', '', 'message.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'admin', 'admin', '2023-01-05 20:21:52', '2024-08-10 10:45:44', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (12, 'en', '3', '0', '12', 'test', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'admin', 'admin', '2022-12-17 09:42:01', '2022-12-17 09:42:01', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (32, 'cn', '1', '10', '31', '调研', '', 'message2.html', '1', '', '', '', '', '', '', '', '', '', '', 'diaoyan', 256, '', 'Admin', '2024-11-23 21:32:46', '2024-11-24 11:00:15', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (33, 'cn', '1', '10', '32', '留言', '', 'message.html', '1', '', '', '', '', '', '', '', '', '', '', 'gbook', 255, '', 'Admin', '2024-11-24 10:58:17', '2024-11-24 11:01:01', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (34, 'cn', '2', '2', '33', 'aaa', 'newslist.html', 'news.html', '1', '', '', '', '', '', '', '', '', '', '', 'ccc', 255, '', 'Admin', '2025-04-08 22:00:22', '2025-04-08 22:03:16', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (35, 'cn', '2', '2', '34', 'bbb', 'newslist.html', 'news.html', '1', '', '', '', '', '', '', '', '', '', '', 'bbb', 255, '', 'Admin', '2025-04-08 22:00:22', '2025-04-08 22:03:06', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (36, 'cn', '3', '5', '35', 'aaa', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, '', '', '2025-04-08 22:04:12', '2025-04-08 22:04:12', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (44, 'cn', '6', '0', '36', 'test', '', '', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-04-16 16:14:46', '2025-04-16 16:14:46', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (45, 'cn', '3', '12', '37', 'caa', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-04-18 21:33:46', '2025-04-18 21:33:46', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (46, 'en', '3', '12', '38', 'accc', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-04-18 21:41:57', '2025-04-18 21:41:57', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (47, 'en', '2', '0', '39', 'a', 'newslist.html', 'news.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, '', '', '2025-04-18 21:58:13', '2025-04-18 21:58:13', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (48, 'en', '2', '0', '40', 'b2', 'newslist.html', 'news.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, '', 'Admin', '2025-04-18 21:58:13', '2025-05-25 20:57:16', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (50, 'oe', '3', '0', '41', 'test', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-04-21 18:16:27', '2025-04-21 18:16:27', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (51, 'cn', '6', '40', '42', '啊啊啊', '', '', '1', '', '', '', '', '', '', '', '', '', '', 'aaa', 255, 'Admin', 'Admin', '2025-05-25 20:57:40', '2025-05-25 20:57:40', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (52, 'cn', '6', '40', '43', 'aaaa1', '', '', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-05-25 20:59:55', '2025-05-25 20:59:55', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (53, 'en', '6', '0', '44', 'sss', 'message.html', '', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-05-25 21:06:51', '2025-05-25 21:15:22', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (59, 'en', '1', '0', '47', '111', '', 'about.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-05-25 21:26:24', '2025-05-25 21:26:24', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (55, 'cn', '1', '44', '46', 'sdd', '', 'about.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'Admin', 'Admin', '2025-05-25 21:09:18', '2025-05-25 21:09:18', '4', '0', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_diy_aaa
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_diy_aaa`;
CREATE TABLE `bd_cms_diy_aaa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '区域编码',
  `create_time` datetime NOT NULL,
  `aaa` varchar(111) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'aaa',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='aa';

-- ----------------------------
-- Records of bd_cms_diy_aaa
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_diy_aaaa
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_diy_aaaa`;
CREATE TABLE `bd_cms_diy_aaaa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bd_cms_diy_aaaa
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_diy_acc
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_diy_acc`;
CREATE TABLE `bd_cms_diy_acc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '区域编码',
  `create_time` datetime NOT NULL,
  `acc` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'acc',
  `acc1` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'acc1',
  `222` varchar(111) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'acc',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='acc';

-- ----------------------------
-- Records of bd_cms_diy_acc
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_diy_baoming
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_diy_baoming`;
CREATE TABLE `bd_cms_diy_baoming` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL,
  `test` varchar(20) DEFAULT NULL COMMENT 'test',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bd_cms_diy_baoming
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_diy_telephone
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_diy_telephone`;
CREATE TABLE `bd_cms_diy_telephone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL,
  `tel` varchar(20) DEFAULT NULL COMMENT '电话号码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bd_cms_diy_telephone
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_extfield
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_extfield`;
CREATE TABLE `bd_cms_extfield` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `mcode` varchar(20) NOT NULL COMMENT '模型编码',
  `name` varchar(30) NOT NULL COMMENT '字段名称',
  `type` char(5) NOT NULL COMMENT '字段类型',
  `value` varchar(500) NOT NULL COMMENT '单选或多选值',
  `description` varchar(30) NOT NULL COMMENT '描述文本',
  `sorting` int(11) NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `extfield_mcode` (`mcode`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='模型字段';

-- ----------------------------
-- Records of bd_cms_extfield
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (1, '3', 'ext_price', '1', '', '产品价格', 255);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (2, '3', 'ext_type', '4', '基础版,专业版,旗舰版', '类型', 2);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (3, '3', 'ext_color', '4', '红色,橙色,黄色,绿色,蓝色,紫色', '颜色', 254);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (12, '2', 'ext_aaa', '6', '', '啊啊啊', 0);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (13, '1', 'ext_aaaaa', '6', '', 'aaa', 555);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (14, '3', 'ext_ab', '1', '', 'test', 0);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (16, '6', 'ext_aa1', '1', '', '啊啊啊', 0);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (17, '6', 'ext_22', '1', '', '22', 0);
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_form
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_form`;
CREATE TABLE `bd_cms_form` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `fcode` varchar(20) NOT NULL COMMENT '表单编码',
  `form_name` varchar(30) NOT NULL COMMENT '表单名称',
  `table_name` varchar(30) NOT NULL COMMENT '表名称',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ay_form_fcode` (`fcode`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='自定义表单';

-- ----------------------------
-- Records of bd_cms_form
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '在线留言', 'bd_cms_message', 'admin', 'Admin', '2018-04-11 17:31:29', '2025-04-10 22:14:10');
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '调研', 'bd_cms_form_data', 'admin', 'admin', '2024-10-25 10:47:51', '2024-10-25 10:47:51');
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (12, '4', 'aa', 'bd_cms_diy_aaa', 'admin', 'admin', '2025-04-14 18:53:22', '2025-04-14 18:53:22');
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (11, '3', 'acc', 'bd_cms_diy_acc', 'admin', 'Admin', '2025-04-11 08:54:04', '2025-04-11 08:56:13');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_form_data
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_form_data`;
CREATE TABLE `bd_cms_form_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '区域编码',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `tel` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '电话',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='调研';

-- ----------------------------
-- Records of bd_cms_form_data
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (2, 'cn', '2024-10-24 10:52:58', '1312345678');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (3, 'cn', '2024-10-23 10:52:58', '1312345678');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (4, 'cn', '2024-11-24 11:01:31', '啊啊啊');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (5, 'cn', '2025-04-16 21:47:58', '15800000000');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_form_field
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_form_field`;
CREATE TABLE `bd_cms_form_field` (
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
  KEY `ay_form_field_fcode` (`fcode`),
  KEY `ay_form_field_sorting` (`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='自定义表单-字段';

-- ----------------------------
-- Records of bd_cms_form_field
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', 'contacts', 10, '1', '联系人', 1, 'admin', 'admin', '2018-07-14 18:24:02', '2024-10-29 14:54:36');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '1', 'mobile', 12, '1', '手机', 2, 'admin', 'admin', '2018-07-14 18:24:02', '2024-10-29 14:54:40');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '1', 'content', 500, '0', '内容', 3, 'admin', 'Admin', '2018-07-14 18:24:02', '2024-11-23 16:27:34');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (13, '2', 'tel', 20, '1', '电话', 1, 'admin', 'Admin', '2024-10-25 10:49:32', '2025-04-10 22:14:32');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (22, '3', 'acc1', 11, '0', 'acc1', 255, 'admin', 'Admin', '2025-04-11 08:55:45', '2025-04-11 08:56:01');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (21, '1', 'aaa', 100, '0', 'aaa', 255, 'admin', 'Admin', '2025-04-10 22:15:41', '2025-04-10 22:15:49');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (23, '3', '222', 111, '0', 'acc', 255, 'admin', 'admin', '2025-04-11 08:56:22', '2025-04-11 08:56:22');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (24, '4', 'aaa', 111, '0', 'aaa', 255, 'admin', 'Admin', '2025-04-14 18:53:37', '2025-04-14 18:53:42');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (25, '1', 'abcc', 100, '0', '你好', 255, 'admin', 'Admin', '2025-05-06 21:18:58', '2025-05-06 21:19:38');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (26, '1', 'hello', 200, '0', '他好', 255, 'admin', 'Admin', '2025-05-06 21:19:52', '2025-05-06 21:19:55');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_label
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_label`;
CREATE TABLE `bd_cms_label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `value` varchar(500) NOT NULL COMMENT '值',
  `type` char(1) NOT NULL DEFAULT '1' COMMENT '字段类型',
  `description` varchar(30) NOT NULL COMMENT '描述',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(20) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='定制标签';

-- ----------------------------
-- Records of bd_cms_label
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_label` (`id`, `name`, `value`, `type`, `description`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (7, 'logo2', '/storage/default/20241130/logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png', '3', '第二logo', 'Admin', 'Admin', '2024-10-30 22:09:41', '2025-04-19 09:19:18');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_link
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_link`;
CREATE TABLE `bd_cms_link` (
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
  KEY `ay_link_acode` (`acode`),
  KEY `ay_link_gid` (`gid`),
  KEY `ay_link_sorting` (`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='友情链接';

-- ----------------------------
-- Records of bd_cms_link
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', 1, 'badoucms', 'https://www.badoucms.com', '/static/upload/image/20180412/1523501605180536.png', 1, 'admin', 'Admin', '2018-04-12 10:53:06', '2025-04-14 20:32:35');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, 'cn', 1, '百度', 'https://www.baidu.com/', '/storage/default/20240909/iShot_2024-09-0b7796dcdfe6d53c78e3a6c3cfa6df5a3ee22651d.png', 1, 'admin', 'admin', '2024-09-11 17:09:53', '2024-09-11 17:09:53');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, 'cn', 1, '新增分组 1', 'https://www.baidu.com/', '/storage/default/20240909/iShot_2024-09-0b7796dcdfe6d53c78e3a6c3cfa6df5a3ee22651d.png', 10, 'admin', 'admin', '2024-09-11 17:10:22', '2024-09-11 21:24:41');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, 'cn', 1, '百度', 'http://localhost/', '/storage/default/20240928/logo75358d77e096dc9e47a5b1425bf188d73717fac3.png', 2, 'admin', 'admin', '2024-09-28 10:10:01', '2024-09-28 10:10:01');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (6, 'cn', 1, '百度 1', 'http://localhost/', NULL, 1, 'admin', 'Admin', '2024-09-28 10:11:19', '2025-04-14 18:53:14');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_member_comment
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_member_comment`;
CREATE TABLE `bd_cms_member_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `contentid` int(10) unsigned NOT NULL COMMENT '内容ID',
  `comment` varchar(1000) NOT NULL DEFAULT '' COMMENT '评论',
  `uid` int(10) unsigned NOT NULL COMMENT '评论人',
  `puid` int(10) unsigned NOT NULL COMMENT '被评论人',
  `likes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `oppose` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '反对数',
  `status` char(1) NOT NULL DEFAULT '' COMMENT '状态',
  `user_ip` varchar(11) NOT NULL DEFAULT '' COMMENT '用户IP',
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
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='文章评论表';

-- ----------------------------
-- Records of bd_cms_member_comment
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (23, 22, 46, 'sdfdfs', 1, 1, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-12-05 21:42:22', '', '2024-12-05 21:42:33');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (22, 0, 46, 'test', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-12-05 21:35:58', '', '2024-12-05 21:42:05');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (16, 14, 43, '审核评论2', 1, 1, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 22:38:24', '', '2025-04-14 19:02:33');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (15, 0, 43, '审核回复', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 22:36:06', '', '2024-11-17 22:36:06');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (14, 0, 43, '审核', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 21:17:03', '', '2024-11-17 21:17:03');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (11, 0, 43, 'aaa', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 21:10:30', '', '2024-11-17 21:10:30');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (12, 0, 43, 'test', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 21:13:24', '', '2024-11-17 21:13:24');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (13, 0, 43, 'aaaa', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 21:15:19', '', '2024-11-17 21:15:19');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (21, 0, 14, '推特身体', 1, 0, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-19 19:23:47', '', '2024-11-19 19:23:47');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_member_field
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_member_field`;
CREATE TABLE `bd_cms_member_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段',
  `length` int(10) unsigned NOT NULL COMMENT '长度',
  `required` char(1) NOT NULL DEFAULT '' COMMENT '是否必填',
  `description` varchar(30) NOT NULL DEFAULT '' COMMENT '描述',
  `sorting` int(10) unsigned NOT NULL COMMENT '排序',
  `status` char(1) NOT NULL DEFAULT '' COMMENT '状态',
  `create_user` varchar(30) NOT NULL,
  `update_user` varchar(30) NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='会员字段';

-- ----------------------------
-- Records of bd_cms_member_field
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_member_field` (`id`, `name`, `length`, `required`, `description`, `sorting`, `status`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (26, 'level1', 20, '0', '等级', 255, '1', 'Admin', 'Admin', '2024-11-22 08:56:39', '2024-11-22 08:56:39');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_member_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_member_group`;
CREATE TABLE `bd_cms_member_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gcode` varchar(20) NOT NULL COMMENT '等级ID',
  `gname` varchar(100) NOT NULL COMMENT '等级名称',
  `description` varchar(200) NOT NULL COMMENT '描述',
  `status` varchar(1) NOT NULL COMMENT '状态',
  `lscore` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分下限',
  `uscore` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分上限\n',
  `create_user` varchar(30) NOT NULL,
  `update_user` varchar(30) NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_group_gcode` (`gcode`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='会员等级';

-- ----------------------------
-- Records of bd_cms_member_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_member_group` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '初级会员', '初级会员具备基本的权限', '1', 0, 999, 'admin', 'Admin', '2020-06-25 00:00:00', '2024-11-10 16:45:07');
INSERT INTO `bd_cms_member_group` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '中级会员', '中级会员具备部分特殊权限', '1', 1000, 9999, 'admin', 'admin', '2020-06-25 00:00:00', '2020-06-25 00:00:00');
INSERT INTO `bd_cms_member_group` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '3', '高级会员', '高级会员具备全部特殊权限', '1', 10000, 4294967295, 'admin', 'admin', '2020-06-25 00:00:00', '2020-06-25 00:00:00');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_message
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_message`;
CREATE TABLE `bd_cms_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域编码',
  `contacts` varchar(10) DEFAULT NULL COMMENT '联系人',
  `mobile` varchar(12) DEFAULT NULL COMMENT '联系电话',
  `content` varchar(500) DEFAULT NULL COMMENT '留言内容',
  `user_ip` varchar(11) NOT NULL DEFAULT '0' COMMENT 'IP地址',
  `user_os` varchar(30) NOT NULL COMMENT '操作系统',
  `user_bs` varchar(30) NOT NULL COMMENT '浏览器',
  `recontent` varchar(500) NOT NULL COMMENT '回复内容',
  `status` char(1) NOT NULL DEFAULT '1' COMMENT '是否前台显示',
  `create_user` varchar(30) NOT NULL COMMENT '创建人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID(默认匿名用户ID)',
  `aaa` varchar(100) NOT NULL COMMENT 'aaa',
  `abcc` varchar(100) NOT NULL COMMENT 'aa',
  `hello` varchar(200) NOT NULL COMMENT '他好',
  PRIMARY KEY (`id`),
  KEY `ay_message_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='在线留言';

-- ----------------------------
-- Records of bd_cms_message
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (1, 'cn', '星梦', '16888888888', 'badoucms真心很不错哦！', '2130706433', 'Windows 10', 'Firefox', '谢谢您对我们的大力支持与肯定！', '1', 'admin', 'admin', '2018-04-12 10:56:09', '2024-11-22 20:27:21', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (4, 'en', '111', '13112341234', '12121', '3232243713', 'Mac', 'Chrome', '测试回复一下', '1', 'guest', 'guest', '2023-01-05 20:32:03', '2024-10-25 10:04:11', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (6, '', 'test', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (7, '', 'aaa', 'aaa', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (8, '', 'aaa', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (9, '', 'aaa', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (14, 'cn', 'test', '18862132539', '1111', '2130706433', 'Mac', 'Chrome', '', '1', 'guest', 'guest', '2024-11-23 19:55:27', '2024-11-23 19:55:37', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (15, 'cn', '111', '111', '111', '2130706433', 'Mac', 'Chrome', 'aaa', '0', 'guest', 'guest', '2025-04-08 19:20:51', '2025-04-14 18:53:51', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (16, 'cn', '111', '111', '111', '2130706433', 'Mac', 'Chrome', '', '0', 'guest', 'guest', '2025-04-08 19:20:51', '2025-04-08 19:20:51', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (17, 'cn', 'api', '13212345678', 'api测试', '2130706433', 'Mac', 'Chrome', '', '0', 'guest', 'guest', '2025-04-16 21:24:38', '2025-04-16 21:24:38', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (18, 'cn', 'api', '13212345678', 'api测试', '2130706433', 'Mac', 'Chrome', '', '0', 'guest', 'guest', '2025-04-16 21:24:47', '2025-04-16 21:24:47', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (19, 'cn', 'api', '13212345678', 'api测试', '2130706433', 'Mac', 'Chrome', '', '0', 'guest', 'guest', '2025-04-16 21:25:06', '2025-04-16 21:25:06', 0, '', '', '');
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`, `aaa`, `abcc`, `hello`) VALUES (20, 'cn', 'api', '13212345678', 'api测试', '2130706433', 'Mac', 'Chrome', '', '0', 'guest', 'guest', '2025-04-16 21:29:52', '2025-04-16 21:29:52', 0, '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_model
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_model`;
CREATE TABLE `bd_cms_model` (
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
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='模型管理';

-- ----------------------------
-- Records of bd_cms_model
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '专题', '1', 'about', '', 'about.html', '1', '1', 'admin', 'admin', '2018-04-11 17:16:01', '2025-05-30 10:35:43');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '新闻', '2', 'list', 'newslist.html', 'news.html', '1', '1', 'admin', 'Admin', '2018-04-11 17:17:16', '2025-05-30 10:38:08');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '3', '产品', '2', 'list', 'productlist.html', 'product.html', '1', '0', 'admin', 'admin', '2018-04-11 17:17:46', '2024-09-21 18:16:55');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, '4', '案例', '2', 'list', 'caselist.html', 'case.html', '1', '0', 'admin', 'admin', '2018-04-11 17:19:53', '2024-09-21 18:16:56');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, '5', '招聘', '2', 'list', 'joblist.html', 'job.html', '1', '0', 'admin', 'admin', '2018-04-11 17:24:34', '2025-05-23 09:37:35');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_model_copy1
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_model_copy1`;
CREATE TABLE `bd_cms_model_copy1` (
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
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='模型管理';

-- ----------------------------
-- Records of bd_cms_model_copy1
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '专题', '1', 'about', '', 'about.html', '1', '1', 'admin', 'admin', '2018-04-11 17:16:01', '2024-09-13 21:18:57');
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '新闻', '2', 'list', 'newslist.html', 'news.html', '1', '1', 'admin', 'Admin', '2018-04-11 17:17:16', '2024-12-05 21:19:23');
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '3', '产品', '2', 'list', 'productlist.html', 'product.html', '1', '0', 'admin', 'admin', '2018-04-11 17:17:46', '2024-09-21 18:16:55');
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, '4', '案例', '2', 'list', 'caselist.html', 'case.html', '1', '0', 'admin', 'admin', '2018-04-11 17:19:53', '2024-09-21 18:16:56');
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, '5', '招聘', '2', 'list', 'joblist.html', 'job.html', '1', '0', 'admin', 'admin', '2018-04-11 17:24:34', '2024-09-21 18:16:56');
INSERT INTO `bd_cms_model_copy1` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (55, '6', '测试', '2', '', '', '', '1', '0', 'Admin', 'Admin', '2025-04-16 16:13:33', '2025-04-16 16:13:33');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_site
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_site`;
CREATE TABLE `bd_cms_site` (
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='站点配置';

-- ----------------------------
-- Records of bd_cms_site
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (1, 'cn', 'BadouCMS', '永久开源免费的PHP企业网站开发建设管理系统', '', '/storage/default/20241130/logo-a1f38377ae174272d0558a18e741d5fad8b08c480.png', 'cms,免费cms,开源cms,企业cms,建站cms', 'BadouCMS是一套全新内核且永久开源免费的PHP企业网站开发建设管理系统，是一套高效、简洁、 强悍的可免费商用的PHP CMS源码，能够满足各类企业网站开发建设的需要。系统采用简单到想哭的模板标签，只要懂HTML就可快速开发企业网站。官方提供了大量网站模板免费下载和使用，将致力于为广大开发者和企业提供最佳的网站开发建设解决方案。', '苏ICP备88888888号', 'default', '&lt;script charset=&quot;UTF-8&quot; id=&quot;LA_COLLECT&quot; src=&quot;//sdk.51.la/js-sdk-pro.min.js&quot;&gt;&lt;/script&gt; &lt;script&gt;LA.init({id:&quot;3LiZk2uiRiI8TQEG&quot;,ck:&quot;3LiZk2uiRiI8TQEG&quot;})&lt;/script&gt;', 'BadouCMS All Rights Reserved.');
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (2, 'en', '1111', '123', '123', '', '', '', '', 'default', '', '');
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (3, 'oe', '德文站点', '德文站点', '', '', '', '', '', 'default', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_slide
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_slide`;
CREATE TABLE `bd_cms_slide` (
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
  KEY `ay_slide_acode` (`acode`),
  KEY `ay_slide_gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='轮播图片';

-- ----------------------------
-- Records of bd_cms_slide
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', 1, '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', 'BADOUCMS', '基于Thinkphp8、Vue3等开源项目', 2, 'admin', 'Admin', '2018-03-01 16:19:03', '2025-04-14 18:48:46');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, 'cn', 1, '/storage/default/20241030/banner1a74d79711756abdf59740aa6be5750e81f8d0218.jpeg', '', 'BADOUCMS', '免费、开源PHP网站管理系统', 5, 'admin', 'Admin', '2018-04-12 10:46:07', '2025-05-03 22:08:40');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, 'cn', 1, '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', 'BADOUCMS', '海量模板选择、降低开发成本', 3, 'admin', 'Admin', '2024-11-24 20:14:03', '2025-05-03 22:08:53');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, 'cn', 2, '/storage/default/20241125/ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', '', '公司简介', '', 0, 'admin', 'Admin', '2024-11-25 20:28:39', '2025-04-14 17:28:50');
COMMIT;

-- ----------------------------
-- Table structure for bd_cms_tags
-- ----------------------------
DROP TABLE IF EXISTS `bd_cms_tags`;
CREATE TABLE `bd_cms_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `acode` varchar(20) NOT NULL COMMENT '区域',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `link` varchar(200) NOT NULL COMMENT '链接',
  `create_user` varchar(30) NOT NULL COMMENT '添加人员',
  `update_user` varchar(30) NOT NULL COMMENT '更新人员',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `ay_tags_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='文章内链';

-- ----------------------------
-- Records of bd_cms_tags
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_tags` (`id`, `acode`, `name`, `link`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', 'badoucms', 'https://www.badoucms.com', 'admin', 'admin', '2019-07-12 14:33:13', '2019-07-12 14:33:13');
COMMIT;

-- ----------------------------
-- Table structure for bd_config
-- ----------------------------
DROP TABLE IF EXISTS `bd_config`;
CREATE TABLE `bd_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量名',
  `group` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '分组',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `visible` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '可见条件',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '变量值',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '变量字典数据',
  `rule` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '扩展属性',
  `setting` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '配置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置';

-- ----------------------------
-- Records of bd_config
-- ----------------------------
BEGIN;
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (1, 'name', 'basic', 'Site name', '请填写站点名称', 'string', '', '竹子管理系统', '', 'required', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (2, 'beian', 'basic', 'Beian', '粤ICP备15000000号-1', 'string', '', '', '', '', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (3, 'cdnurl', 'basic', 'Cdn url', '如果全站静态资源使用第三方云储存请配置该值', 'string', '', '', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (4, 'version', 'basic', 'Version', '如果静态资源有变动请重新配置该值', 'string', '', '1.0.1', '', 'required', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (5, 'timezone', 'basic', 'Timezone', '', 'string', '', 'Asia/Shanghai', '', 'required', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (6, 'forbiddenip', 'basic', 'Forbidden ip', '一行一条记录', 'text', '', '', '', '', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (7, 'languages', 'basic', 'Languages', '', 'array', '', '{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}', '', 'required', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (8, 'fixedpage', 'basic', 'Fixed page', '请尽量输入左侧菜单栏存在的链接', 'string', '', '/dashboard', '', 'required', '', NULL);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (9, 'categorytype', 'dictionary', 'Category type', '', 'array', '', '{\"default\":\"Default\",\"page\":\"Page\",\"article\":\"Article\",\"test\":\"Test\"}', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (10, 'configgroup', 'dictionary', 'Config group', '', 'array', '', '{\"basic\":\"Basic\",\"email\":\"Email\",\"dictionary\":\"Dictionary\",\"user\":\"User\",\"example\":\"Example\"}', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (11, 'mail_type', 'email', 'Mail type', '选择邮件发送方式', 'select', '', '1', '[\"请选择\",\"SMTP\"]', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (12, 'mail_smtp_host', 'email', 'Mail smtp host', '错误的配置发送邮件会导致服务器超时', 'string', '', 'smtp.qq.com', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (13, 'mail_smtp_port', 'email', 'Mail smtp port', '(不加密默认25,SSL默认465,TLS默认587)', 'string', '', '465', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (14, 'mail_smtp_user', 'email', 'Mail smtp user', '（填写完整用户名）', 'string', '', '10000', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (15, 'mail_smtp_pass', 'email', 'Mail smtp password', '（填写您的密码或授权码）', 'string', '', 'password', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (16, 'mail_verify_type', 'email', 'Mail vertify type', '（SMTP验证方式[推荐SSL]）', 'select', '', '2', '[\"无\",\"TLS\",\"SSL\"]', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (17, 'mail_from', 'email', 'Mail from', '', 'string', '', '10000@qq.com', '', '', '', '');
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`) VALUES (18, 'attachmentcategory', 'dictionary', 'Attachment category', '', 'array', '', '{\"category1\":\"Category1\",\"category2\":\"Category2\",\"custom\":\"Custom\"}', '', '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_user
-- ----------------------------
DROP TABLE IF EXISTS `bd_user`;
CREATE TABLE `bd_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组别ID',
  `username` varchar(32) DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `password` varchar(64) DEFAULT '' COMMENT '密码',
  `email` varchar(100) DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `bio` varchar(100) DEFAULT '' COMMENT '格言',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `successions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '连续登录天数',
  `maxsuccessions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '最大连续登录天数',
  `prevtime` bigint(20) DEFAULT NULL COMMENT '上次登录时间',
  `logintime` bigint(20) DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `loginfailuretime` bigint(20) DEFAULT NULL COMMENT '最后登录失败时间',
  `joinip` varchar(50) DEFAULT '' COMMENT '加入IP',
  `jointime` bigint(20) DEFAULT NULL COMMENT '加入时间',
  `createtime` bigint(20) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(50) DEFAULT '' COMMENT 'Token',
  `status` varchar(30) DEFAULT '' COMMENT '状态',
  `verification` varchar(255) DEFAULT '' COMMENT '验证',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='会员表';

-- ----------------------------
-- Records of bd_user
-- ----------------------------
BEGIN;
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `password`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `bio`, `money`, `score`, `successions`, `maxsuccessions`, `prevtime`, `logintime`, `loginip`, `loginfailure`, `loginfailuretime`, `joinip`, `jointime`, `createtime`, `updatetime`, `token`, `status`, `verification`) VALUES (2, 1, 'wu', 'wuwu', '111', 'a@qq.com', '', '', 0, 0, NULL, '', 0.00, 0, 1, 1, NULL, NULL, '', 0, NULL, '', NULL, NULL, NULL, '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_group`;
CREATE TABLE `bd_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '组名',
  `rules` text COMMENT '权限节点',
  `createtime` bigint(20) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='会员组表';

-- ----------------------------
-- Records of bd_user_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_group` (`id`, `name`, `rules`, `createtime`, `updatetime`, `status`) VALUES (1, 'aaa', NULL, NULL, NULL, 'hidden');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_rule
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_rule`;
CREATE TABLE `bd_user_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '父ID',
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `title` varchar(50) DEFAULT '' COMMENT '标题',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `ismenu` tinyint(1) DEFAULT NULL COMMENT '是否菜单',
  `createtime` bigint(20) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(11) DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='会员规则表';

-- ----------------------------
-- Records of bd_user_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (1, 0, 'index', 'Frontend', '', 1, 1491635035, 1491635035, 1, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (2, 0, 'api', 'API Interface', '', 1, 1491635035, 1491635035, 2, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (3, 1, 'user', 'User Module', '', 1, 1491635035, 1491635035, 12, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (4, 2, 'user', 'User Module', '', 1, 1491635035, 1491635035, 11, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (5, 3, 'index/user/login', 'Login', '', 0, 1491635035, 1491635035, 5, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (6, 3, 'index/user/register', 'Register', '', 0, 1491635035, 1491635035, 7, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (7, 3, 'index/user/index', 'User Center', '', 0, 1491635035, 1491635035, 9, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (8, 3, 'index/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 4, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (9, 4, 'api/user/login', 'Login', '', 0, 1491635035, 1491635035, 6, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (10, 4, 'api/user/register', 'Register', '', 0, 1491635035, 1491635035, 8, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (11, 4, 'api/user/index', 'User Center', '', 0, 1491635035, 1491635035, 10, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES (12, 4, 'api/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 3, 'normal');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
