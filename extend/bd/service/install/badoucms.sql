/*
 Navicat Premium Dump SQL

 Source Server         : 本地电脑
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44)
 Source Host           : localhost:3306
 Source Schema         : badoucms

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44)
 File Encoding         : 65001

 Date: 29/03/2025 18:41:03
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bd_admin
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin`;
CREATE TABLE `bd_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `login_failure` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `last_login_time` bigint(16) unsigned DEFAULT NULL COMMENT '上次登录时间',
  `last_login_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码盐',
  `motto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签名',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

-- ----------------------------
-- Records of bd_admin
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `avatar`, `email`, `mobile`, `login_failure`, `last_login_time`, `last_login_ip`, `password`, `salt`, `motto`, `status`, `update_time`, `create_time`) VALUES (1, 'admin', 'Admin', '', 'admin@buildadmin.com', '18888888888', 0, 1743244832, '127.0.0.1', '2d960f5f826c8729555e4e85b0b072b3', 'HDcnBjOyFdgTluk1', '', '1', 1743244832, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_group`;
CREATE TABLE `bd_admin_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级分组',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT '权限规则ID',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理分组表';

-- ----------------------------
-- Records of bd_admin_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES (1, 0, '超级管理组', '*', '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES (2, 1, '一级管理员', '1,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,77,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,89,499,500', '1', 1733538424, 1722731863);
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES (3, 2, '二级管理员', '21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43', '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES (4, 3, '三级管理员', '55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,499,500', '1', 1733538424, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_group_access
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_group_access`;
CREATE TABLE `bd_admin_group_access` (
  `uid` int(11) unsigned NOT NULL COMMENT '管理员ID',
  `group_id` int(11) unsigned NOT NULL COMMENT '分组ID',
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理分组映射表';

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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `url` varchar(1500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作Url',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志标题',
  `data` longtext COLLATE utf8mb4_unicode_ci COMMENT '请求数据',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7072 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员日志表';

-- ----------------------------
-- Records of bd_admin_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_log` (`id`, `admin_id`, `username`, `url`, `title`, `data`, `ip`, `useragent`, `create_time`) VALUES (7040, 0, '未知', '/admin/ajax/clearCache', '未知(clearcache)', '{\"type\":\"tp\"}', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 1743244513);
COMMIT;

-- ----------------------------
-- Table structure for bd_admin_rule
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_rule`;
CREATE TABLE `bd_admin_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` enum('menu_dir','menu','button') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `keepalive` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '缓存:0=关闭,1=开启',
  `extend` enum('none','add_rules_only','add_menu_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=506 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='菜单和权限规则表';

-- ----------------------------
-- Records of bd_admin_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (1, 0, 'menu', '控制台', 'dashboard', 'dashboard', 'fa fa-dashboard', 'tab', '', '/src/views/backend/dashboard.vue', 1, 'none', 'Remark lang', 999, '1', 1729671677, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (2, 0, 'menu_dir', '权限管理', 'auth', 'auth', 'fa fa-group', NULL, '', '', 0, 'none', '', 993, '1', 1732927911, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (3, 2, 'menu', '角色组管理', 'auth/group', 'auth/group', 'fa fa-group', 'tab', '', '/src/views/backend/auth/group/index.vue', 1, 'none', 'Remark lang', 99, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (4, 3, 'button', '查看', 'auth/group/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (5, 3, 'button', '添加', 'auth/group/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (6, 3, 'button', '编辑', 'auth/group/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (7, 3, 'button', '删除', 'auth/group/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (8, 2, 'menu', '管理员管理', 'auth/admin', 'auth/admin', 'el-icon-UserFilled', 'tab', '', '/src/views/backend/auth/admin/index.vue', 1, 'none', '', 98, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (9, 8, 'button', '查看', 'auth/admin/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (10, 8, 'button', '添加', 'auth/admin/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (11, 8, 'button', '编辑', 'auth/admin/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (12, 8, 'button', '删除', 'auth/admin/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (13, 2, 'menu', '菜单规则管理', 'auth/rule', 'auth/rule', 'el-icon-Grid', 'tab', '', '/src/views/backend/auth/rule/index.vue', 1, 'none', '', 97, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (14, 13, 'button', '查看', 'auth/rule/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (15, 13, 'button', '添加', 'auth/rule/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (16, 13, 'button', '编辑', 'auth/rule/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (17, 13, 'button', '删除', 'auth/rule/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (18, 13, 'button', '快速排序', 'auth/rule/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (19, 2, 'menu', '管理员日志管理', 'auth/adminLog', 'auth/adminLog', 'el-icon-List', 'tab', '', '/src/views/backend/auth/adminLog/index.vue', 1, 'none', '', 96, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (20, 19, 'button', '查看', 'auth/adminLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (21, 0, 'menu_dir', '会员管理', 'user', 'user', 'fa fa-drivers-license', NULL, '', '', 0, 'none', '', 994, '1', 1732927901, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (22, 21, 'menu', '会员管理', 'user/user', 'user/user', 'fa fa-user', 'tab', '', '/src/views/backend/user/user/index.vue', 1, 'none', '', 94, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (23, 22, 'button', '查看', 'user/user/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (24, 22, 'button', '添加', 'user/user/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (25, 22, 'button', '编辑', 'user/user/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (26, 22, 'button', '删除', 'user/user/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (27, 21, 'menu', '会员分组管理', 'user/group', 'user/group', 'fa fa-group', 'tab', '', '/src/views/backend/user/group/index.vue', 1, 'none', '', 93, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (28, 27, 'button', '查看', 'user/group/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (29, 27, 'button', '添加', 'user/group/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (30, 27, 'button', '编辑', 'user/group/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (31, 27, 'button', '删除', 'user/group/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (32, 21, 'menu', '会员规则管理', 'user/rule', 'user/rule', 'fa fa-th-list', 'tab', '', '/src/views/backend/user/rule/index.vue', 1, 'none', '', 92, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (33, 32, 'button', '查看', 'user/rule/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (34, 32, 'button', '添加', 'user/rule/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (35, 32, 'button', '编辑', 'user/rule/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (36, 32, 'button', '删除', 'user/rule/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (37, 32, 'button', '快速排序', 'user/rule/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (38, 21, 'menu', '会员余额管理', 'user/moneyLog', 'user/moneyLog', 'el-icon-Money', 'tab', '', '/src/views/backend/user/moneyLog/index.vue', 1, 'none', '', 91, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (39, 38, 'button', '查看', 'user/moneyLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (40, 38, 'button', '添加', 'user/moneyLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (41, 21, 'menu', '会员积分管理', 'user/scoreLog', 'user/scoreLog', 'el-icon-Discount', 'tab', '', '/src/views/backend/user/scoreLog/index.vue', 1, 'none', '', 90, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (42, 41, 'button', '查看', 'user/scoreLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (43, 41, 'button', '添加', 'user/scoreLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (44, 0, 'menu_dir', '全局配置', 'routine', 'routine', 'fa fa-cogs', NULL, '', '', 0, 'none', '', 998, '1', 1732927683, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (45, 44, 'menu', '系统配置', 'routine/config', 'routine/config', 'el-icon-Tools', 'tab', '', '/src/views/backend/routine/config/index.vue', 1, 'none', '', 99, '1', 1732930441, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (46, 45, 'button', '查看', 'routine/config/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (47, 45, 'button', '编辑', 'routine/config/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (48, 44, 'menu', '附件管理', 'routine/attachment', 'routine/attachment', 'fa fa-folder', 'tab', '', '/src/views/backend/routine/attachment/index.vue', 1, 'none', 'Remark lang', 87, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (49, 48, 'button', '查看', 'routine/attachment/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (50, 48, 'button', '编辑', 'routine/attachment/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (51, 48, 'button', '删除', 'routine/attachment/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (52, 44, 'menu', '个人资料', 'routine/adminInfo', 'routine/adminInfo', 'fa fa-user', 'tab', '', '/src/views/backend/routine/adminInfo.vue', 1, 'none', '', 86, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (53, 52, 'button', '查看', 'routine/adminInfo/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (54, 52, 'button', '编辑', 'routine/adminInfo/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (55, 0, 'menu_dir', '数据安全管理', 'security', 'security', 'fa fa-shield', NULL, '', '', 0, 'none', '', 85, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (56, 55, 'menu', '数据回收站', 'security/dataRecycleLog', 'security/dataRecycleLog', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycleLog/index.vue', 1, 'none', '', 84, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (57, 56, 'button', '查看', 'security/dataRecycleLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (58, 56, 'button', '删除', 'security/dataRecycleLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (59, 56, 'button', '还原', 'security/dataRecycleLog/restore', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (60, 56, 'button', '查看详情', 'security/dataRecycleLog/info', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (61, 55, 'menu', '敏感数据修改记录', 'security/sensitiveDataLog', 'security/sensitiveDataLog', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveDataLog/index.vue', 1, 'none', '', 83, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (62, 61, 'button', '查看', 'security/sensitiveDataLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (63, 61, 'button', '删除', 'security/sensitiveDataLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (64, 61, 'button', '回滚', 'security/sensitiveDataLog/rollback', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (65, 61, 'button', '查看详情', 'security/sensitiveDataLog/info', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (66, 55, 'menu', '数据回收规则管理', 'security/dataRecycle', 'security/dataRecycle', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycle/index.vue', 1, 'none', 'Remark lang', 82, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (67, 66, 'button', '查看', 'security/dataRecycle/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (68, 66, 'button', '添加', 'security/dataRecycle/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (69, 66, 'button', '编辑', 'security/dataRecycle/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (70, 66, 'button', '删除', 'security/dataRecycle/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (71, 55, 'menu', '敏感字段规则管理', 'security/sensitiveData', 'security/sensitiveData', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveData/index.vue', 1, 'none', 'Remark lang', 81, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (72, 71, 'button', '查看', 'security/sensitiveData/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (73, 71, 'button', '添加', 'security/sensitiveData/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (74, 71, 'button', '编辑', 'security/sensitiveData/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (75, 71, 'button', '删除', 'security/sensitiveData/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (77, 45, 'button', '添加', 'routine/config/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (84, 0, 'menu', 'CRUD代码生成', 'crud/crud', 'crud/crud', 'fa fa-code', 'tab', '', '/src/views/backend/crud/index.vue', 1, 'none', '', 80, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (85, 84, 'button', '查看', 'crud/crud/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (86, 84, 'button', '生成', 'crud/crud/generate', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (87, 84, 'button', '删除', 'crud/crud/delete', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (88, 45, 'button', '删除', 'routine/config/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731863, 1722731863);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (89, 1, 'button', '查看', 'dashboard/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1722731864, 1722731864);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (108, 0, 'menu_dir', 'CMS管理', 'cms', 'cms', 'el-icon-Monitor', NULL, '', '', 0, 'none', '', 996, '1', 1732927745, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (109, 108, 'menu', '栏目管理', 'cms/contentSort', 'cms/contentSort', 'fa fa-align-justify', 'tab', '', '/src/views/backend/cms/contentSort/index.vue', 1, 'none', '', 980, '1', 1732927542, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (110, 109, 'button', '查看', 'cms/contentSort/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723247879, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (111, 109, 'button', '添加', 'cms/contentSort/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723247879, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (112, 109, 'button', '编辑', 'cms/contentSort/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723247879, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (113, 109, 'button', '删除', 'cms/contentSort/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723247879, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (114, 109, 'button', '快速排序', 'cms/contentSort/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723247879, 1723247879);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (226, 44, 'menu', '模型管理', 'cms/models', 'cms/models', 'fa fa-codepen', 'tab', '', '/src/views/backend/cms/models/index.vue', 1, 'none', '', 78, '1', 1732930539, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (227, 226, 'button', '查看', 'cms/models/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723354404, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (228, 226, 'button', '添加', 'cms/models/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723354404, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (229, 226, 'button', '编辑', 'cms/models/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723354404, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (230, 226, 'button', '删除', 'cms/models/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723354404, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (231, 226, 'button', '快速排序', 'cms/models/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1723354404, 1723354404);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (232, 0, 'menu_dir', '文章内容', 'cms/content', 'cms/content', 'fa fa-file-text-o', 'tab', '', '/src/views/backend/cms/content/index.vue', 1, 'none', '', 995, '1', 1732927768, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (233, 232, 'button', '查看', 'cms/content/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1724770204, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (234, 232, 'button', '添加', 'cms/content/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1724770204, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (235, 232, 'button', '编辑', 'cms/content/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1724770204, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (236, 232, 'button', '删除', 'cms/content/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1724770204, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (237, 232, 'button', '快速排序', 'cms/content/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1724770204, 1724770204);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (244, 108, 'menu', '轮播图片', 'cms/slide', 'cms/slide', 'fa fa-photo', 'tab', '', '/src/views/backend/cms/slide/index.vue', 1, 'none', '', 0, '1', 1727422459, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (245, 244, 'button', '查看', 'cms/slide/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725197205, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (246, 244, 'button', '添加', 'cms/slide/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725197205, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (247, 244, 'button', '编辑', 'cms/slide/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725197205, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (248, 244, 'button', '删除', 'cms/slide/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725197205, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (249, 244, 'button', '快速排序', 'cms/slide/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725197205, 1725197205);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (282, 44, 'menu', '模型字段', 'cms/extfield', 'cms/extfield', 'el-icon-Memo', 'tab', '', '/src/views/backend/cms/extfield/index.vue', 1, 'none', '', 77, '1', 1732930575, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (283, 282, 'button', '查看', 'cms/extfield/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725673303, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (284, 282, 'button', '添加', 'cms/extfield/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725673303, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (285, 282, 'button', '编辑', 'cms/extfield/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725673303, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (286, 282, 'button', '删除', 'cms/extfield/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725673303, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (287, 282, 'button', '快速排序', 'cms/extfield/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1725673303, 1725673303);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (288, 108, 'menu', '友情链接', 'cms/link', 'cms/link', 'el-icon-Link', 'tab', '', '/src/views/backend/cms/link/index.vue', 1, 'none', '', 0, '1', 1727422660, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (289, 288, 'button', '查看', 'cms/link/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726042946, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (290, 288, 'button', '添加', 'cms/link/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726042946, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (291, 288, 'button', '编辑', 'cms/link/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726042946, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (292, 288, 'button', '删除', 'cms/link/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726042946, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (293, 288, 'button', '快速排序', 'cms/link/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726042946, 1726042946);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (294, 108, 'menu', '自定义表单', 'cms/form', 'cms/form', 'fa fa-plus-square-o', 'tab', '', '/src/views/backend/cms/form/index.vue', 0, 'none', '', 0, '1', 1730208492, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (295, 294, 'button', '查看', 'cms/form/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726046668, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (296, 294, 'button', '添加', 'cms/form/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726046668, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (297, 294, 'button', '编辑', 'cms/form/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726046668, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (298, 294, 'button', '删除', 'cms/form/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726046668, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (299, 294, 'button', '快速排序', 'cms/form/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726046668, 1726046668);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (342, 232, 'menu', '专题内容', 'cms/content/mcode/1', 'cms/content/mcode/1', '', 'tab', '', '/src/views/backend/cms/content/page/index.vue', 1, 'none', '', 0, '1', 1726233537, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (343, 342, 'button', '查看', 'cms/content/mcode/1/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726233535, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (344, 342, 'button', '添加', 'cms/content/mcode/1/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726233535, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (345, 342, 'button', '编辑', 'cms/content/mcode/1/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726233535, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (346, 342, 'button', '删除', 'cms/content/mcode/1/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726233535, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (347, 342, 'button', '快速排序', 'cms/content/mcode/1/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726233535, 1726233535);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (348, 232, 'menu', '新闻内容', 'cms/content/mcode/2', 'cms/content/mcode/2', '', 'tab', '', '/src/views/backend/cms/content/list/index.vue', 1, 'none', '', 0, '1', 1726913814, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (349, 348, 'button', '查看', 'cms/content/mcode/2/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234000, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (350, 348, 'button', '添加', 'cms/content/mcode/2/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234000, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (351, 348, 'button', '编辑', 'cms/content/mcode/2/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234000, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (352, 348, 'button', '删除', 'cms/content/mcode/2/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234000, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (353, 348, 'button', '快速排序', 'cms/content/mcode/2/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234000, 1726234000);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (354, 232, 'menu', '产品内容', 'cms/content/mcode/3', 'cms/content/mcode/3', '', 'tab', '', '/src/views/backend/cms/content/list/index.vue', 1, 'none', '', 0, '1', 1726913814, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (355, 354, 'button', '查看', 'cms/content/mcode/3/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234002, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (356, 354, 'button', '添加', 'cms/content/mcode/3/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234002, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (357, 354, 'button', '编辑', 'cms/content/mcode/3/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234002, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (358, 354, 'button', '删除', 'cms/content/mcode/3/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234002, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (359, 354, 'button', '快速排序', 'cms/content/mcode/3/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234002, 1726234002);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (360, 232, 'menu', '案例内容', 'cms/content/mcode/4', 'cms/content/mcode/4', '', 'tab', '', '/src/views/backend/cms/content/list/index.vue', 1, 'none', '', 0, '1', 1726913815, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (361, 360, 'button', '查看', 'cms/content/mcode/4/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (362, 360, 'button', '添加', 'cms/content/mcode/4/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (363, 360, 'button', '编辑', 'cms/content/mcode/4/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (364, 360, 'button', '删除', 'cms/content/mcode/4/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (365, 360, 'button', '快速排序', 'cms/content/mcode/4/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (366, 232, 'menu', '招聘内容', 'cms/content/mcode/5', 'cms/content/mcode/5', '', 'tab', '', '/src/views/backend/cms/content/list/index.vue', 1, 'none', '', 0, '1', 1726913816, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (367, 366, 'button', '查看', 'cms/content/mcode/5/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (368, 366, 'button', '添加', 'cms/content/mcode/5/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (369, 366, 'button', '编辑', 'cms/content/mcode/5/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (370, 366, 'button', '删除', 'cms/content/mcode/5/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (371, 366, 'button', '快速排序', 'cms/content/mcode/5/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726234003, 1726234003);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (378, 108, 'menu', '站点配置', 'cms/site', 'cms/site', 'el-icon-Setting', 'tab', '', '/src/views/backend/cms/site/index.vue', 1, 'none', '', 999, '1', 1732927508, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (379, 378, 'button', '查看', 'cms/site/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726404994, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (380, 378, 'button', '添加', 'cms/site/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726404994, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (381, 378, 'button', '编辑', 'cms/site/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726404994, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (382, 378, 'button', '删除', 'cms/site/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726404994, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (383, 378, 'button', '快速排序', 'cms/site/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726404994, 1726404994);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (385, 294, 'button', '自定义表单-字段', 'cms/formField', 'cms/formField', 'el-icon-SetUp', 'tab', '', '/src/views/backend/cms/formField/index.vue', 0, 'none', '', 0, '1', 1731405588, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (386, 385, 'button', '查看', 'cms/formField/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726753449, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (387, 385, 'button', '添加', 'cms/formField/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726753449, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (388, 385, 'button', '编辑', 'cms/formField/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726753449, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (389, 385, 'button', '删除', 'cms/formField/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726753449, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (390, 385, 'button', '快速排序', 'cms/formField/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726753449, 1726753449);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (391, 108, 'menu', '留言信息', 'cms/message', 'cms/message', 'fa fa-question-circle-o', 'tab', '', '/src/views/backend/cms/message/index.vue', 1, 'none', '', 0, '1', 1727422818, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (392, 391, 'button', '查看', 'cms/message/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726756494, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (393, 391, 'button', '添加', 'cms/message/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726756494, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (394, 391, 'button', '编辑', 'cms/message/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726756494, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (395, 391, 'button', '删除', 'cms/message/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726756494, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (396, 391, 'button', '快速排序', 'cms/message/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726756494, 1726756494);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (397, 108, 'menu', '文章内链', 'cms/tags', 'cms/tags', 'fa fa-random', 'tab', '', '/src/views/backend/cms/tags/index.vue', 1, 'none', '', 0, '1', 1727422706, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (398, 397, 'button', '查看', 'cms/tags/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726757483, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (399, 397, 'button', '添加', 'cms/tags/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726757483, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (400, 397, 'button', '编辑', 'cms/tags/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726757483, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (401, 397, 'button', '删除', 'cms/tags/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726757483, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (402, 397, 'button', '快速排序', 'cms/tags/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726757483, 1726757483);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (403, 232, 'button', '复制', 'cms/content/copy', '', 'fa fa-circle-o', 'tab', '', '', 0, 'none', '', 0, '1', 1726912339, 1726912339);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (404, 232, 'button', '移动', 'cms/content/move', '', 'fa fa-circle-o', 'tab', '', '', 0, 'none', '', 0, '1', 1726912541, 1726912541);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (415, 366, 'button', '复制', 'cms/content/mcode/5/copy', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913811, 1726913811);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (416, 366, 'button', '移动', 'cms/content/mcode/5/move', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913811, 1726913811);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (417, 360, 'button', '复制', 'cms/content/mcode/4/copy', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913812, 1726913812);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (418, 360, 'button', '移动', 'cms/content/mcode/4/move', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913812, 1726913812);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (419, 354, 'button', '复制', 'cms/content/mcode/3/copy', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913812, 1726913812);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (420, 354, 'button', '移动', 'cms/content/mcode/3/move', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913812, 1726913812);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (421, 348, 'button', '复制', 'cms/content/mcode/2/copy', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913813, 1726913813);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (422, 348, 'button', '移动', 'cms/content/mcode/2/move', '', '', 'tab', '', '', 1, 'none', '', 0, '1', 1726913813, 1726913813);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (447, 108, 'menu', '公司信息', 'cms/company', 'cms/company', 'fa fa-copyright', 'tab', '', '/src/views/backend/cms/company/index.vue', 1, 'none', '', 999, '1', 1732927528, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (448, 447, 'button', '查看', 'cms/company/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926876, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (449, 447, 'button', '添加', 'cms/company/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926876, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (450, 447, 'button', '编辑', 'cms/company/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926876, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (451, 447, 'button', '删除', 'cms/company/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926876, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (452, 447, 'button', '快速排序', 'cms/company/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926876, 1726926876);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (453, 108, 'menu', '区域管理', 'cms/area', 'cms/area', 'fa fa-sitemap', 'tab', '', '/src/views/backend/cms/area/index.vue', 1, 'none', '', 0, '1', 1727050911, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (454, 453, 'button', '查看', 'cms/area/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926913, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (455, 453, 'button', '添加', 'cms/area/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926913, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (456, 453, 'button', '编辑', 'cms/area/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926913, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (457, 453, 'button', '删除', 'cms/area/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926913, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (458, 453, 'button', '快速排序', 'cms/area/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726926913, 1726926913);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (459, 44, 'menu', '定制标签', 'cms/label', 'cms/label', 'fa fa-wrench', 'tab', '', '/src/views/backend/cms/label/index.vue', 1, 'none', '', 76, '1', 1732930591, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (460, 459, 'button', '查看', 'cms/label/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726927141, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (461, 459, 'button', '添加', 'cms/label/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726927141, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (462, 459, 'button', '编辑', 'cms/label/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726927141, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (463, 459, 'button', '删除', 'cms/label/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726927141, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (464, 459, 'button', '快速排序', 'cms/label/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1726927141, 1726927141);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (465, 21, 'menu', '会员等级', 'cms/memberGroup', 'cms/memberGroup', 'fa fa-signal', 'tab', '', '/src/views/backend/cms/memberGroup/index.vue', 1, 'none', '', 0, '1', 1731372472, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (466, 465, 'button', '查看', 'cms/memberGroup/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731195302, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (467, 465, 'button', '添加', 'cms/memberGroup/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731195302, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (468, 465, 'button', '编辑', 'cms/memberGroup/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731195302, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (469, 465, 'button', '删除', 'cms/memberGroup/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731195302, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (470, 465, 'button', '快速排序', 'cms/memberGroup/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731195302, 1731195302);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (478, 21, 'menu', '会员字段', 'cms/memberfield', 'cms/memberfield', 'fa fa-puzzle-piece', 'tab', '', '/src/views/backend/cms/memberfield/index.vue', 1, 'none', '', 0, '1', 1731372657, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (479, 478, 'button', '查看', 'cms/memberfield/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731329005, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (480, 478, 'button', '添加', 'cms/memberfield/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731329005, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (481, 478, 'button', '编辑', 'cms/memberfield/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731329005, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (482, 478, 'button', '删除', 'cms/memberfield/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731329005, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (483, 478, 'button', '快速排序', 'cms/memberfield/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731329005, 1731329005);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (484, 108, 'menu_dir', '自定义表单', '自定义表单', 'form', 'fa fa-plus-square-o', NULL, '', '', 0, 'none', '', 0, '0', 1730208453, 1729825923);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (485, 294, 'button', '自定义表单-数据', 'cms/formData', 'cms/formData', 'fa fa-database', 'tab', '', '/src/views/backend/cms/formData/index.vue', 0, 'none', '', 0, '1', 1731405609, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (486, 466, 'button', '查看', 'cms/formData/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1729825924, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (487, 466, 'button', '添加', 'cms/formData/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1729825924, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (488, 466, 'button', '编辑', 'cms/formData/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1729825924, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (489, 466, 'button', '删除', 'cms/formData/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1729825924, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (490, 466, 'button', '快速排序', 'cms/formData/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1729825924, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (491, 108, 'menu', '文章评论管理', 'cms/membercomment', 'cms/membercomment', 'fa fa-commenting', 'tab', '', '/src/views/backend/cms/membercomment/index.vue', 1, 'none', '', 0, '1', 1732928563, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (492, 491, 'button', '查看', 'cms/membercomment/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731505336, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (493, 491, 'button', '添加', 'cms/membercomment/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731505336, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (494, 491, 'button', '编辑', 'cms/membercomment/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731505336, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (495, 491, 'button', '删除', 'cms/membercomment/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731505336, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (496, 491, 'button', '快速排序', 'cms/membercomment/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731505336, 1731505336);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (497, 485, 'button', '查看', 'cms/formData/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731405609, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (498, 485, 'button', '删除', 'cms/formData/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1731405609, 1729825924);
INSERT INTO `bd_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (505, 0, 'menu', 'PbootCMS迁移', 'pboot/index', 'pboot/index', 'el-icon-Switch', 'tab', '', '/src/views/backend/pboot/index.vue', 0, 'none', '', 0, '1', 1743244823, 1743244716);
COMMIT;

-- ----------------------------
-- Table structure for bd_area
-- ----------------------------
DROP TABLE IF EXISTS `bd_area`;
CREATE TABLE `bd_area` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) unsigned DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '简称',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '全称',
  `level` tinyint(4) unsigned DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
  `pinyin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '长途区号',
  `zip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '首字母',
  `lng` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='省份地区表';

-- ----------------------------
-- Records of bd_area
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_attachment
-- ----------------------------
DROP TABLE IF EXISTS `bd_attachment`;
CREATE TABLE `bd_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `topic` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '细目',
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传管理员ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传用户ID',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物理路径',
  `width` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '宽度',
  `height` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '高度',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原始名称',
  `size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '大小',
  `mimetype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mime类型',
  `quote` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传(引用)次数',
  `storage` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '存储方式',
  `sha1` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'sha1编码',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  `last_upload_time` bigint(16) unsigned DEFAULT NULL COMMENT '最后上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

-- ----------------------------
-- Records of bd_attachment
-- ----------------------------
BEGIN;
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (7, 'default', 1, 0, '/storage/default/20241030/banner1a74d79711756abdf59740aa6be5750e81f8d0218.jpeg', 1680, 800, 'banner1.jpeg', 140675, 'image/jpeg', 1, 'local', 'a74d79711756abdf59740aa6be5750e81f8d0218', 1730297008, 1730297008);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (8, 'default', 1, 0, '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', 1680, 800, 'banner2.jpeg', 299157, 'image/jpeg', 1, 'local', 'd0fda8a3e11edf5116c34f0e20cedd4d56def65a', 1730297054, 1730297054);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (15, 'default', 1, 0, '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', 1680, 800, 'banner3.jpeg', 214326, 'image/jpeg', 1, 'local', '47491b1c8d21b9768f7a3b4bb8e9abb681a5f566', 1732450437, 1732450437);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (17, 'default', 1, 0, '/storage/default/20241124/外贸网站缩略图4b812e48623d14f81d4e025ae42c061dad65588d.jpg', 800, 800, '外贸网站缩略图.jpg', 1303167, 'image/jpeg', 1, 'local', '4b812e48623d14f81d4e025ae42c061dad65588d', 1732454731, 1732454731);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (18, 'default', 1, 0, '/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', 800, 800, '医疗网站缩略图.jpg', 1028449, 'image/jpeg', 2, 'local', '509e495580df27f55087b19ae3f99901c6e05da4', 1732492078, 1732492314);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (19, 'default', 1, 0, '/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', 800, 800, '医疗网站缩略图(1).jpg', 1617174, 'image/jpeg', 2, 'local', '9da009ac88c5a129c9e579f016eb1b8bc203ad7b', 1732492756, 1732492944);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (20, 'default', 1, 0, '/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', 800, 800, '医疗网站缩略图(2).jpg', 1415822, 'image/jpeg', 1, 'local', '7b56bddaec8c7a26099bc277ff78354660c80536', 1732493255, 1732493255);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (21, 'default', 1, 0, '/storage/default/20241125/ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', 1200, 600, 'ebg.jpg', 333923, 'image/jpeg', 1, 'local', 'edef959924d3813f9a24d9d45991cb4ac046571d', 1732537716, 1732537716);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (22, 'default', 1, 0, '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', 340, 280, 'b3.jpeg', 37559, 'image/jpeg', 1, 'local', 'd1dc760aca9445023eeaee0e9a142c53b38a4c14', 1732543006, 1732543006);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (23, 'default', 1, 0, '/storage/default/20241125/b1415ad46278103146361019859ee60a6e978d1a57.jpeg', 340, 280, 'b1.jpeg', 45718, 'image/jpeg', 1, 'local', '415ad46278103146361019859ee60a6e978d1a57', 1732543264, 1732543264);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (24, 'default', 1, 0, '/storage/default/20241125/b4ad8ebe742c5f02e5df9e7d1a614a0a2daa308d93.jpeg', 340, 280, 'b4.jpeg', 31874, 'image/jpeg', 1, 'local', 'ad8ebe742c5f02e5df9e7d1a614a0a2daa308d93', 1732543279, 1732543279);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (25, 'default', 1, 0, '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', 640, 640, 'teams1.jpeg', 76661, 'image/jpeg', 1, 'local', '7406d32c6971b1fd8b8e2550c6fc288a4b8730eb', 1732577197, 1732577197);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (26, 'default', 1, 0, '/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg', 640, 640, 'teams2.jpeg', 89171, 'image/jpeg', 2, 'local', '3ad8d1e14db9eb4ee9374c9a793c78e593829078', 1732577303, 1732577428);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (27, 'default', 1, 0, '/storage/default/20241126/teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg', 640, 640, 'teams3.jpeg', 101645, 'image/jpeg', 1, 'local', '9754bbeea387dc5326bde7c03b201b34036fac70', 1732577332, 1732577332);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (28, 'default', 1, 0, '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', 640, 640, 'teams4.jpeg', 88612, 'image/jpeg', 1, 'local', 'f3c94f4696e55452f157173f340e2d321252398a', 1732577438, 1732577438);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (46, 'default', 1, 0, '/storage/default/20241130/logo-a1f38377ae174272d0558a18e741d5fad8b08c480.png', 250, 60, 'logo-a.png', 4639, 'image/png', 1, 'local', '1f38377ae174272d0558a18e741d5fad8b08c480', 1732958950, 1732958950);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (47, 'default', 1, 0, '/storage/default/20241130/logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png', 250, 60, 'logo-b.png', 4466, 'image/png', 1, 'local', '834a5b93f4d5ee35f256198252216570f75fc9a0', 1732958981, 1732958981);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (51, 'default', 1, 0, '/storage/default/20241030/badoucms.com.png', 400, 400, 'badoucms.com.png', 7362, 'image/png', 1, 'local', '25e82fb566348949fa09809969d6a6a7b2700d9c', 1733224334, 1733224334);
INSERT INTO `bd_attachment` (`id`, `topic`, `admin_id`, `user_id`, `url`, `width`, `height`, `name`, `size`, `mimetype`, `quote`, `storage`, `sha1`, `create_time`, `last_upload_time`) VALUES (54, 'default', 1, 0, '/storage/default/20241130/logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png', 250, 60, 'logo-b.png', 4466, 'image/png', 1, 'local', '834a5b93f4d5ee35f256198252216570f75fc9a0', 1732958981, 1732958981);
COMMIT;

-- ----------------------------
-- Table structure for bd_captcha
-- ----------------------------
DROP TABLE IF EXISTS `bd_captcha`;
CREATE TABLE `bd_captcha` (
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证码Key',
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证码(加密后)',
  `captcha` text COLLATE utf8mb4_unicode_ci COMMENT '验证码数据',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  `expire_time` bigint(16) unsigned DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='验证码表';

-- ----------------------------
-- Records of bd_captcha
-- ----------------------------
BEGIN;
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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='区域管理';

-- ----------------------------
-- Records of bd_cms_area
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_area` (`id`, `acode`, `pcode`, `name`, `domain`, `is_default`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, 'en', '0', 'English', 'en.badoucms.test', '0', 'admin', 'admin', '2023-02-02 21:20:40', '2024-09-25 21:07:41');
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
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COMMENT='CMS文章内容';

-- ----------------------------
-- Records of bd_cms_content
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (1, 'cn', '1', '', '公司简介', '#333333', '', '', 'admin', '本站', '', '2018-04-11 17:26:11', '', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 255, '1', '0', '0', '0', 441, 0, 0, 'admin', 'Admin', '2018-04-11 17:26:11', '2025-03-29 18:35:20', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (3, 'cn', '11', '', '联系我们', '#333333', '', '', 'admin', '本站', '', '2018-04-11 17:31:29', '/static/upload/image/20180413/1523583018133454.png', '', '', '联系我们', '', '', '', '官方网站：www.badoucms.com技术交流群： 137083872www.badoucms.com我们一直秉承大道至简分享便可改变世界的理念，坚持做最简约灵活的badoucms开源软件！您的每一份帮助都将支持badoucms做的更好，走的更远！我们一直在坚持不懈地努力，并尽可能让badoucms完全开源免费，您的帮助将使我们更有动力和信心^_^！扫一扫官网付款', 255, '1', '0', '0', '0', 35, 0, 0, 'admin', 'Admin', '2018-04-11 17:31:29', '2024-12-15 17:19:51', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (44, 'cn', '31', '', '调研', '#333333', '', '', 'Admin', '本站', '', '2024-11-23 21:32:45', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 63, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-30 17:53:01', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (45, 'cn', '32', '', '留言', '#333333', '', '', 'Admin', '本站', '', '2024-11-24 10:58:17', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (46, 'cn', '6', '', 'xx医疗行业网站模板', '', '', '', 'Admin', '', '', '2024-11-24 20:58:20', '/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', '', '', '<p><img src=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" alt=\"医疗网站缩略图.jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg\" width=\"\" height=\"\" /></p><p><strong>一、整体风格<br></strong></p><p>选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。<br></p><p><strong>二、具体图片内容<br></strong></p><ol><li>一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。</li><li>医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。</li><li>患者在电脑或手机上使用在线预约系统的场景，旁边可以有日历和确认按钮等元素。</li><li>医生查看电子病历的画面，可以有一个大屏幕显示详细的病历信息和图表。</li><li>大数据分析的图表，如柱状图、折线图等，代表医疗数据的收集和分析。</li><li>医疗行业网站的标志和标语，突出其专业性和创新性。<br></li></ol>', '', '', '', '一、整体风格选择简洁、专业的医疗风格配色，如白色、蓝色、绿色等为主色调，营造出清新、可靠的感觉。二、具体图片内容一个设计精美的医疗行业网站首页截图，展示简洁的界面和清晰的导航栏，突出其技术感，比如现代化的图标和流畅的交互效果。医生和患者通过视频进行远程会诊的画面，体现远程医疗技术。患者在电脑或手机上', 251, '1', '0', '0', '0', 38, 0, 0, 'Admin', 'Admin', '2024-11-24 21:01:15', '2024-12-15 17:04:58', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (47, 'cn', '6', '', '外贸行业网站模板', '', '', '', 'Admin', '', '', '2024-11-24 20:58:20', '/storage/default/20241124/外贸网站缩略图4b812e48623d14f81d4e025ae42c061dad65588d.jpg', '', '', '<p><img src=\"https://p3-search.byteimg.com/obj/labis/0e409f67aba58e66c221e30d7483d51f\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. “快”—— 访问速度快</h3><p>外贸网站一般会使用海外服务器或加速节点，以此确保网站的打开速度和响应速度能与当地平均水平持平。这是因为如果网站速度过慢，会导致用户放弃访问。例如，在全球疫情的影响下，外贸企业纷纷转向外贸独立站，而网站速度慢直接影响用户体验和营销转化。目前有多种技术可以提升外贸网站的运行速度，如 Google AMP 框架，其网页可以在 Google 服务器里生成缓存，大大提升网站速度；Webp 无损压缩技术，能降低图片文件大小；Gzip 压缩技术以及 CDN 加速，通过寻找互联网上最快的访问节点来优化运行速度。此外，CDN 加速技术还能把放置于国内的网站复制一份到各个国家的服务器上，让各个国家的浏览者就近访问企业网站，提高访问响应速度。</p><p><br></p><h3>2. “简”—— 信息精简</h3><p>与国内企业网站相比，外贸网站信息量少。这一方面是因为欧美等发达国家受教育程度高的网民不追求过多过杂的信息，另一方面也与搜索引擎算法有关。谷歌不鼓励企业网站持续大量进行内容更新，尤其是无用信息的更新。外贸网站是为国外人阅读的网站，自然应该迎合他们的口味，减少信息量，避免像国内一些企业网站那样，为了迎合百度算法而持续大量更新内容，甚至包含一些无用信息。</p><p><br></p><h3>3. “直”—— 直接明了</h3><p>外贸网站的直接体现在两个方面。一是少有在线沟通工具，用户直接浏览栏目。这与欧美国家互联网环境有关，欧美用户对在线沟通工具似乎并不十分热衷，他们倾向于邮箱、电话交流。二是信息说明比较直接，少有拐弯抹角、云里雾里的 “忽悠式” 口号。这是因为欧美国家有一个较为诚信的互联网环境，用户对网站容易产生信任，所以网站在传递信息的时候不妨直来直去。</p><p><br></p><h3>4. “细”—— 做工细致</h3><p>从脚本、代码、图片、构架等方面看，欧美国家的企业网站似乎比国内一般的网站更为用心。这有两个原因，一是欧美国家非常流行工程师文化，工程师文化的一个特点就是 “抠细节”，所以外贸网站在作图、拍摄、LOGO、配色等方面要迎合工程师文化；二是欧美国家的整体代码水平和建站分工要比国内高一些。这两方面原因使得外贸网站的做工需要更细致一些。</p><p><br></p><h3>5. “严”—— 要求严格</h3><p>外贸网站对网站版权声明、个人隐私保护、用户数据泄露等方面更加重视。国内企业网站在建站时对这些方面往往不够上心，但外贸网站如果在这些方面做得不够或出现失误，有可能触碰法律风险，而且谷歌等欧美主流搜索引擎对法律条款不够完整的网站也不太友好。此外，外贸网站不允许出现错别字、语法错误和 BUG，因为这会直接导致用户对网站、企业和品牌的信任危机。</p><p><br></p><h2>二、外贸电商网站的特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/83f4c79ee7c66191137f10b53ba497cf\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 多语言支持</h3><p>外贸电商建立多语言网站至关重要，其能方便全球用户使用，打破语言障碍。建立多语言网站的步骤如下：首先要了解目标市场的语言需求和文化背景，根据不同市场特点选择需提供的语言版本；接着准备多语言网站的翻译内容，包括网站文本、图像、音频、视频等；然后选择合适的多语言网站管理平台，如 WordPress、Drupal 等，以便快速、方便地进行多语言网站的构建和管理；根据需要考虑使用专业的翻译服务、本地化工具或机器翻译等来实现网站内容的翻译；设计多语言导航和语言切换功能，使用户可以轻松切换网站语言版本；确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，以提高网站的可访问性和用户体验；最后逐步完善和优化多语言网站的内容和功能，以满足不同语言和文化需求的用户。</p><p>数据库级多语言支持可通过设计数据库结构实现，如单表存储在产品表中为每种语言添加独立列，或多表存储创建主表存储基本信息并关联翻译表；前端国际化框架依赖国际化库，将用户界面文本提取为语言文件并根据用户选择动态加载；内容管理系统（CMS）集成则可选择支持多语言的 CMS，如 WordPress、Drupal 或 Magento 等，方便用户管理不同语言的内容。</p><p><br></p><h3>2. 多货币支持</h3><p>外贸电商网站支持多货币便于用户付款和商家结算，适应跨境交易需求。不同国家和地区有不同的货币体系，多货币支持可以让用户在购物时选择自己熟悉的货币进行支付，提高购物的便利性和舒适度。同时，商家也可以更方便地进行结算，避免汇率波动带来的风险。</p><p><br></p><h3>3. 安全性和稳定性</h3><p>外贸电商网站的安全性和稳定性至关重要，它保障了用户和商家的利益，防止黑客攻击和信息泄露。使用安全证书是网站安全的基础，通过 HTTPS 协议和 SSL 加密技术，能有效防止黑客攻击和数据泄露；使用防火墙可以检测和阻止恶意流量，避免外部攻击和黑客入侵；定期备份数据能防止因病毒攻击、服务器崩溃等问题导致的数据丢失，提高数据恢复效率；加强密码安全，要求用户使用强密码并定期修改，管理员使用独特且复杂的密码并定期更换；限制对敏感信息的访问权限，只允许有必要权限的人员访问数据库和管理后台；进行安全培训，提高员工的安全意识，减少安全漏洞的发生。</p><p><br></p><h3>4. 良好的用户体验</h3><p>外贸电商网站的界面设计应简洁明了、易于操作，以提高用户满意度。简洁直观的导航能让用户轻松找到所需页面和功能；快速的加载速度优化图片、压缩代码、选择高效服务器等方式提升；响应式网站设计确保适应不同屏幕尺寸和设备；个性化推荐和定制化体验利用用户数据和历史行为分析为用户提供个性化推荐商品和定制化体验；清晰的产品信息和图片展示提供详细产品信息、规格、价格和清晰图片；简化的购物流程减少用户操作步骤，提供简单易用的结账和支付选项；安全的支付系统保护用户个人信息和支付安全；多语言支持满足不同用户语言需求；社交媒体整合方便用户分享和推荐产品；提供优质客户服务建立快速响应的客户服务渠道；用户评价和推荐增加信任和口碑效应；持续优化和改进根据用户行为和反馈数据不断提升用户体验。</p><p><br></p><h3>5. 强大的搜索功能</h3><p>外贸电商网站应具备快速高效的搜索功能，帮助用户快速找到所需商品和信息。默认全站搜索，然后通过结果分类导航，进行结果筛选、检索。提供 “相关搜索” 功能，帮访客找到更加的搜索词，还能给访客一些未想到的搜索提示。限定搜索的措施是自动提示，不仅能减少错误输入，还能帮助我们推荐产品与产品分类，避免 “无搜索结果” 的情况。</p><p><br></p><h3>6. 充足的商品信息</h3><p>外贸电商网站需提供详尽的商品介绍、规格、图片等，方便用户了解商品情况。清晰、高质量的产品图片和详细的描述信息能让用户全面了解产品特点和优势，提升购买决策的信心。</p><p><br></p><h3>7. 良好的客户服务</h3><p>外贸电商网站应提供多种渠道解答用户疑问，提升用户体验。建立快速响应的客户服务渠道，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。电子商务网站建设的售前服务包括认真回答消费者对商品的咨询、尺寸、码数、质量、售后等问题，及时回复、态度友好，提升用户满意度。</p><p><br></p><h3>8. 多样化的支付方式</h3><p>外贸电商网站支持多种支付方式，方便用户完成支付。常见的外贸电商网站支付方式有 PayPal、支付宝、银行电汇、信用卡支付等。不同国家和地区的支付习惯不同，选择合适的支付方式非常重要。要考虑支付方式的安全性、手续费用、方便用户操作等因素。</p><p><br></p><h3>9. 精准的数据分析</h3><p>外贸电商网站通过精准的数据分析了解用户需求和购买行为，优化网站设计和服务。利用用户数据和历史行为分析，为用户提供个性化的推荐商品和定制化的体验，增强用户的参与感和满意度。定期进行用户调研、用户体验测试和网站性能监测，根据反馈和数据进行改进和优化，不断提升用户体验。</p><p><br></p><h2>三、受国外喜欢的外贸网站特点</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/b6713d7f8b7c443fa779ddd46442deed\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 页面简洁明了</h3><p>外贸网站应避免繁琐复杂的页面设计，以简洁明了的布局吸引外国人的注意力。一个直观且简单易懂的界面可以让用户更快地了解网站的核心内容，提高用户的浏览效率。例如，减少不必要的装饰和复杂的动画效果，突出产品或服务的关键信息，使用户能够迅速找到所需内容。</p><p><br></p><h3>2. 多语言支持</h3><p>提供多语言支持可以提高网站的可访问性，满足不同国家和地区用户的语言需求。外贸网站可以根据目标市场的语言特点，提供相应的语言版本。具体实现方法包括了解目标市场的语言需求和文化背景，准备多语言网站的翻译内容，选择合适的多语言网站管理平台，如 WordPress、Drupal 等，考虑使用专业的翻译服务、本地化工具或机器翻译，设计多语言导航和语言切换功能，确保多语言网站的 SEO 和网站速度等方面与单语言网站相同，逐步完善和优化多语言网站的内容和功能。</p><p><br></p><h3>3. 响应式网站设计</h3><p>随着移动设备的广泛使用，响应式网站设计成为外贸网站的重要特点。响应式设计能够确保网站在各种设备上，包括手机、平板和电脑，都能提供良好的用户体验。实现响应式设计可以设置关键断点，结合站点内容设置关键点，注意网站内容的有效传递；优先进行手机端设计，筛选出重要元素，避免使用大图，做垂直滚动，把搜索栏和主操作按钮放在醒目位置；扩大目标点击区域，方便用户点击；采用响应式图片或视频，避免显示不全、留白、模糊或失真的情况，可使用支持响应式的框架或设置图片属性，也可以使用 SVG 矢量图，对于视频可插入 FitVids 或 jQuery 插件实现自动缩放；进行恰当的视觉设计，注重色彩搭配，避免复杂的导航菜单、滑动效果和 Flash 动画，保证页面简洁优雅。</p><p><br></p><h3>4. 独特的视觉风格</h3><p>具有独特视觉风格的外贸网站更容易吸引外国人的关注。个性化和创新的设计能够突出网站在竞争激烈的市场中的独特性。可以从色彩搭配、字体选择、图片和视频的运用等方面打造独特的视觉效果，创造极强的视觉冲击力或营造舒适的氛围，具体取决于网站的主题内容。同时，要注意视觉设计与网站内容的协调性，确保用户在享受视觉盛宴的同时，能够轻松获取所需信息。</p><p><br></p><h3>5. 易于导航和使用</h3><p>清晰的导航结构和简单的操作流程是外贸网站受外国人喜欢的重要因素。网站应提供易于理解和直观的导航标签，帮助用户快速浏览和访问各个部分。例如，设置简洁明了的导航栏，分类合理，方便用户找到所需信息；简化购买流程，减少用户的购买障碍，提供清晰的购买按钮和操作指导；强化客户支持和沟通渠道，提供多种联系方式，如在线客服、电话支持和电子邮件等，确保用户能够方便地联系到客服，并及时回复和解决问题。</p><p><br></p><h3>6. 专业可信</h3><p>专业、可信的外贸网站更容易赢得外国人的信任。提供详细的产品信息、公司资质和客户评价等内容，有助于建立网站的可靠形象。展示清晰、高质量的产品图片和详细的描述信息，让用户全面了解产品特点和优势；强调公司的资质和荣誉，增强用户对公司的信心；允许用户对商品进行评价和打分，显示商品的用户反馈和满意度，积极回应用户评价，增强用户对商品的信任感。</p><p><br></p><h2>四、如何选择外贸行业网站</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/5bddf53b8ac68a48aaade06e5cc1cd90\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 国内外建站公司对比</h3><p><strong>1. 国外建站公司（以 Shopify 为例）：</strong></p><p>Shopify 是一个基于云端的电商平台，功能齐全，提供网站主机、购物车、支付处理、库存管理、订单跟踪和分析等功能，还有广泛的应用市场，允许商家使用各种应用程序来增强商店功能。其优势包括建站操作简单，拥有丰富的应用生态、引流渠道多且卖家相对自由等。但也存在一些劣势，如独立站本身没有流量需卖家自己推广，有交易费用，App 费用较高，网站程序采用小众的 Liquid 语言专业开发程序员少，备份转移不便，批发功能和多语言支持不够好等。</p><p><strong>2. 国内建站公司（以 Ueeshop、shopline、shopyy 等为例）：</strong></p><p>国内建站公司功能与国外建站公司不相上下，能满足独立站卖家需求。以 Ueeshop 为例，不抽取佣金只收年费，成本更低。语言相通，沟通方便，且一般会提供技术支持。Shopline 和 Shopyy 也有各自的特点，如免费试用时间不同、功能表和定价策略有所差异等。</p><p><br></p><h3>2. 独立站核心</h3><p>选择 SaaS 建站可节约成本和时间，将更多精力用于推广引流。SaaS 建站平台如独立站 SaaS，能够帮助企业快速搭建功能齐全的电商网站，降低启动成本和时间，提供高度定制化功能，打造独特品牌形象，增强市场竞争力。同时，通常集成多种营销工具和分析功能，有助于企业精准定位目标客户，提高营销效果和转化率。通过云端托管，确保网站高可靠性和安全性，企业无需担心服务器维护和数据安全问题。</p><p>而合适的建站公司能帮助卖家事半功倍。在选择建站公司时，要明确自己的业务需求和目标，考虑平台的稳定性和安全性、可扩展性和灵活性、用户体验和客户支持以及成本等因素。选择最适合自身需求的独立站 SaaS 服务，推动外贸业务的稳步发展。</p><p><br></p><h2>五、外贸行业网站的发展趋势</h2><p><img src=\"https://p3-search.byteimg.com/obj/pgc-image/a363fbc361b840729a844f0d80f204a8\" alt=\"\" data-href=\"\" /></p><p><br></p><h3>1. 移动化趋势</h3><p>随着智能手机和移动互联网的普及，贸易活动将更多在移动端进行。如今，越来越多的消费者倾向于使用移动设备进行在线购物，这对外贸行业网站提出了新的要求。外贸网站需要适应移动化趋势，提供方便快捷的移动端服务，以满足用户随时随地进行贸易活动的需求。</p><p>例如，企业可以优化网站的移动端界面，确保在手机和平板等设备上能够流畅浏览和操作。同时，结合移动支付技术，为用户提供便捷的支付方式，提高交易效率。</p><p><br></p><h3>2. 数据驱动</h3><p>大数据分析和人工智能技术应用，提供个性化服务和推荐。在当今数字化时代，数据成为了外贸行业网站的重要资产。通过大数据分析，网站可以深入了解用户的行为、偏好和需求，从而为用户提供个性化的服务和推荐。</p><p>例如，利用用户的浏览历史、购买记录等数据，为用户推荐符合其兴趣的产品和服务。同时，人工智能技术可以帮助网站实现智能客服，自动回答用户的咨询，提高服务效率。</p><p>此外，大数据分析还可以用于优化供应链管理。通过分析销售数据和市场需求预测，企业可以实现库存的精准管理和优化，降低库存积压和滞销风险。</p><p><br></p><h3>3. 跨境电商的发展</h3><p>推动外贸网站发展，带来更多贸易机会和市场潜力。跨境电商的快速发展为外贸行业网站带来了新的机遇。随着全球贸易的日益频繁，跨境电商平台成为了企业拓展海外市场的重要渠道。</p><p>跨境电商平台具有全球性、便捷性、高效性、低成本等特点，能够满足消费者对多元化、个性化商品的需求，同时也为商家提供了更广阔的市场空间。例如，亚马逊、阿里巴巴等跨境电商平台已经成为全球贸易的重要组成部分，越来越多的企业和消费者开始使用平台进行交易。</p><p>外贸行业网站可以与跨境电商平台合作，借助平台的流量和资源，扩大自身的市场影响力。同时，网站也可以借鉴跨境电商平台的成功经验，优化自身的服务和功能，提高用户体验。</p><p><br></p><h3>4. 其他趋势</h3><p>如人工智能助力个性化用户体验、混合商务提供无缝连接客户旅程、增强现实和虚拟现实吸引观众等。</p><p>人工智能在个性化用户体验方面发挥着重要作用。通过机器学习和自然语言处理技术，网站可以更好地理解用户的需求和意图，为用户提供更加精准的推荐和服务。</p><p>混合商务模式将线上和线下渠道相结合，为用户提供无缝连接的客户旅程。外贸行业网站可以与线下实体店合作，实现线上线下融合，为用户提供更加便捷的购物体验。</p><p>增强现实和虚拟现实技术可以为用户带来更加沉浸式的购物体验。通过展示产品的 3D 模型和虚拟场景，用户可以更加直观地了解产品的特点和优势，提高购买决策的信心。</p><p><br></p><h2>六、外贸行业热门网站有哪些</h2><p><img src=\"https://p3-search.byteimg.com/obj/labis/51523bc6c767ed79db31cda3846406e1\" alt=\"\" data-href=\"\" /></p><p><br></p>', '', '', '', '外贸行业网站的特点', 253, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2024-11-24 21:01:15', '2024-12-15 17:05:22', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (14, 'cn', '8', '', '常州xxx钢铁企业官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b4ad8ebe742c5f02e5df9e7d1a614a0a2daa308d93.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 252, '1', '0', '0', '0', 19, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2024-11-30 17:57:22', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (50, 'cn', '8', '', '无锡xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 251, '1', '0', '0', '0', 19, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2024-11-28 07:42:51', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (28, 'zh-cn', '29', '', 'accesser', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 21:53:08', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (29, 'zh-cn', '30', '', '单页测试1', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 21:54:08', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (31, 'zh-cn', '31', '', '单页测试2', '#333333', '', '', 'Admin', '本站', '', '2024-09-14 22:02:35', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 0, 0, 0, 'Admin', 'Admin', '2024-11-24 11:01:31', '2024-11-24 11:01:31', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (48, 'cn', '6', '', '环保行业网站模板', '', '', '', 'Admin', '', '', '2024-11-25 07:43:57', '/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', '', '', '<p>环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。</p><p><img src=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" alt=\"医疗网站缩略图(1).jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg\" width=\"\" height=\"\" /></p><h3>（一）设计理念</h3><p>环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出环保主题，使网站在视觉上与众不同。同时，设计过程中应考虑网站的生命周期，确保其在使用过程中能够持续降低对环境的影响。例如，避免大面积使用深色背景，减少动画和视频的使用频率，以降低能源消耗。</p><p><br></p><h3>（二）节能优化</h3><p>能效优化是环保网站提升可持续性的重要手段。优化图片和多媒体文件的大小和格式，如使用现代的图像格式 WebP，能够在不损失质量的前提下减少文件大小，显著降低页面加载时间和服务器能源消耗。采用内容分发网络（CDN）技术，将网站内容分布到全球多个数据中心，减少用户访问的延迟和带宽消耗，提高用户体验的同时降低服务器的能源使用。选择绿色托管服务商也是关键一步，许多托管服务商已开始使用可再生能源为数据中心供电，有效减少网站的碳足迹。</p><p><br></p><h3>（三）可持续材料使用</h3><p>在网站建设中，可以选择可再生资源，如使用可再生能源供电、选择可再生材料制作硬件等。同时，资源循环利用也是实现可持续发展的重要途径，例如回收旧硬件、重复利用设计元素等，减少资源浪费。此外，通过选择高质量、耐用的硬件，减少硬件更换频率，可以有效减少电子垃圾的产生。</p><p><br></p><h3>（四）用户体验</h3><p>合理的导航和信息架构设计能帮助用户快速找到所需信息，减少不必要的点击和页面加载。响应式设计确保网站在各种设备上都能顺畅运行，提高用户满意度，减少因设备不兼容而产生的资源浪费。提供个性化和互动性的内容，增强用户的参与感和忠诚度。通过数据分析和用户反馈，不断优化网站功能和内容，提升整体用户体验。</p><p><br></p><h3>（五）内容管理</h3><p>网站内容应定期更新和优化，提供高质量的环保知识科普文章、视频和图片，以及及时发布环保新闻、报告等，确保内容的质量、准确性和实用性。使用内容管理系统（CMS），更高效地管理和更新网站内容。采用缓存技术减少服务器请求次数，提高网站加载速度，降低服务器负载和能源消耗。同时，对内容进行压缩与优化，如压缩图像、视频等多媒体文件，减少数据传输量，降低服务器负载和能耗。</p><p><br></p><h3>（六）社会责任</h3><p>环保网站不仅是技术平台，更是教育和意识提升的工具。通过设置专门的环保教育栏目，提供有关可持续发展的文章和资源，向用户传递环保知识和理念。设计在线活动或挑战赛，激励用户采取环保行动，如减少塑料使用或参与植树活动。还可以通过社交媒体传播环保成功案例和用户故事，扩大环保意识的影响力。积极与环保组织和专家合作，确保网站内容的科学性和权威性，为用户提供更准确和有用的环保信息。通过论坛和评论功能，鼓励用户分享经验和建议，形成积极的互动社区，提高用户参与度，为网站的持续改进提供宝贵反馈。跨行业合作也是推动环保网站设计创新的重要途径，与技术公司、教育机构和部门合作，获得更多资源和支持，共同推动可持续发展目标的实现。</p><p><br></p>', '', '', '', '环保行业网站在当今数字化时代具有至关重要的作用，其重要性体现在多个方面。（一）设计理念环保行业网站应遵循可持续设计原则，采用生态友好的设计理念。在色彩选择上，以清新自然的绿色为主色调，搭配蓝色、白色等辅助色彩，符合可持续发展概念。图标设计可采用循环箭头、绿色勾号、再生徽章等可持续发展相关的图标，突出', 256, '1', '0', '0', '0', 4, 0, 0, 'Admin', 'Admin', '2024-11-25 08:02:29', '2024-12-15 17:19:58', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (18, 'en', '12', '', 'tea', '#333333', '', '', '超级管理员', '本站', '', '2022-12-17 09:42:05', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2022-12-17 09:42:18', '2022-12-17 09:42:18', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (19, 'cn', '10', '', 'test', '#333333', '', '', '超级管理员', '本站', '', '2023-01-05 15:46:46', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 179, 0, 0, 'admin', 'admin', '2023-01-05 15:46:46', '2024-12-15 17:19:48', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (20, 'en', '13', '', '在线留言', '#333333', '', '', '超级管理员', '本站', '', '2023-01-05 20:21:52', '', '', '', '', '', '', '', '', 255, '1', '0', '0', '0', 1, 0, 0, 'admin', 'admin', '2023-01-05 20:21:52', '2024-09-04 20:37:12', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (60, 'cn', '9', '', '淘宝运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg', '', '', '<p><strong>岗位职责：</strong></p><p>1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p>2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p>3、 为用户提供平台业务咨询服务；</p><p>4、 受理客户投诉，在授权范围内予以解决；</p><p>5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p>6、 与公司其他部门配合工作。</p><p><br></p><p><strong>任职要求：</strong></p><p>1、 专科及以上学历，热爱互联网行业；</p><p>2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p>3、 形象佳，口齿伶俐，普通话标准；</p><p>4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p>5、 能适应白班、夜班倒班工作制；</p><p>注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p><br></p><p><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2>北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 2, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:29:57', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (61, 'cn', '2', '', 'badoucms 正式上线1.0.0 版本', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 百度推送</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 1, '1', '0', '0', '0', 7, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2024-11-30 17:52:17', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (59, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (57, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (58, 'cn', '9', '', '天猫运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', '', '', '<p><strong>岗位职责：</strong></p><p>1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p>2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p>3、 为用户提供平台业务咨询服务；</p><p>4、 受理客户投诉，在授权范围内予以解决；</p><p>5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p>6、 与公司其他部门配合工作。</p><p><br></p><p><strong>任职要求：</strong></p><p>1、 专科及以上学历，热爱互联网行业；</p><p>2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p>3、 形象佳，口齿伶俐，普通话标准；</p><p>4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p>5、 能适应白班、夜班倒班工作制；</p><p>注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p><br></p><p><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2>北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 2, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 20:55:54', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (55, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (56, 'cn', '9', '', '拼多多运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams39754bbeea387dc5326bde7c03b201b34036fac70.jpeg', '', '', '<p><strong>岗位职责：</strong></p><p>1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p>2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p>3、 为用户提供平台业务咨询服务；</p><p>4、 受理客户投诉，在授权范围内予以解决；</p><p>5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p>6、 与公司其他部门配合工作。</p><p><br></p><p><strong>任职要求：</strong></p><p>1、 专科及以上学历，热爱互联网行业；</p><p>2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p>3、 形象佳，口齿伶俐，普通话标准；</p><p>4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p>5、 能适应白班、夜班倒班工作制；</p><p>注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p><br></p><p><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2>北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 2, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:29:07', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (54, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 2, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-28 07:38:47', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (52, 'cn', '8', '', '南京xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b1415ad46278103146361019859ee60a6e978d1a57.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 254, '1', '0', '0', '0', 18, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2024-11-25 22:09:50', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (53, 'cn', '9', '', '平台运营', '', '', '', 'Admin', '', '', '2024-11-26 07:26:04', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p ><strong>岗位职责：</strong></p><p >1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；</p><p >2、 参与和优化部门业务操作流程，保证团队协同工作；</p><p >3、 为用户提供平台业务咨询服务；</p><p >4、 受理客户投诉，在授权范围内予以解决；</p><p >5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；</p><p >6、 与公司其他部门配合工作。</p><p ><br></p><p ><strong>任职要求：</strong></p><p >1、 专科及以上学历，热爱互联网行业；</p><p >2、 较强的工作责任心，踏实勤恳，积极向上，性格开朗；</p><p >3、 形象佳，口齿伶俐，普通话标准；</p><p >4、 熟练使用电脑，经常上网，会使用office等相关办公软件；</p><p >5、 能适应白班、夜班倒班工作制；</p><p >注：根据个人能力和特长，公司给予更多的发展及晋升空间。</p><p ><br></p><p ><strong>工作地址：</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><h2 >北京市朝阳区北苑路</h2>', '', '', '', '岗位职责：1、 负责平台运营的业务支撑工作，保证平台业务稳定发展；2、 参与和优化部门业务操作流程，保证团队协同工作；3、 为用户提供平台业务咨询服务；4、 受理客户投诉，在授权范围内予以解决；5、 网络活动视频录像与剪辑，挖掘优秀作品,后台信息简单编辑处理；6、 与公司其他部门配合工作。任职要求：1、 专科及以上...', 255, '1', '0', '0', '0', 1, 0, 0, 'Admin', 'Admin', '2024-11-26 07:26:42', '2024-11-26 07:26:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (51, 'cn', '8', '', '苏州xxx建筑公司官网', '#333333', '', '', 'admin', '本站', '', '2018-04-12 10:26:28', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li>区域管理（多语言）</li><li>模型管理（自定义内容模型）</li><li>模型字段管理（自定义模型字段）</li><li>栏目管理</li><li>内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li>站点配置、公司信息</li><li>定制标签（自定义前台标签）</li><li>前台模版标签</li><li>轮播图片</li><li>多条件筛选</li><li>网站地图（sitemap）</li><li>友情链接</li><li>自定义表单</li><li>留言信息</li><li>文章内链</li><li>多条件搜索</li><li>百度推送</li><li>内容权限</li><li>会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li>会员字段</li><li>会员等级(设置栏目与内容浏览权限)</li><li>文章评论(回复、审核)</li><li>我的评论</li></ul>', '', '', '', 'BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表区域管理（多语言）模型管理（自定义内容模型）模型字段管理（自定义模型字...', 253, '1', '0', '0', '0', 19, 0, 0, 'admin', 'Admin', '2018-04-12 10:32:52', '2024-11-27 21:15:41', '4', '0', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (49, 'cn', '6', '', '建筑行业网站模板', '', '', '', 'Admin', '', '', '2024-11-25 07:43:57', '/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', '', '', '<p>在当今数字化时代，建筑行业网站的制作具有至关重要的意义。<br><img src=\"/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg\" alt=\"医疗网站缩略图(2).jpg\" data-href=\"/storage/default/20241125/医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg\" width=\"\" height=\"\" /></p><p>对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信度和美誉度。<br></p><p>对于行业从业者而言，建筑行业网站是获取信息和交流的平台。在这里，他们可以了解到最新的行业动态、政策法规、技术创新等信息，不断提升自己的专业素养。网站上的论坛和社区功能，还能让从业者们分享经验、交流心得，促进整个行业的共同进步。<br></p><p>从客户角度来看，建筑行业网站方便他们寻找可靠的建筑服务提供商。客户可以通过网站对比不同企业的优势和特点，选择最符合自己需求的合作伙伴。而且，网站上的在线咨询和预约服务，也为客户提供了便捷的沟通渠道。<br></p><p>此外，建筑行业网站还有助于提升行业的透明度和规范性。通过展示企业的资质证书、荣誉奖项等信息，让客户能够更加放心地选择合作对象。同时，也促使建筑企业不断提高自身的管理水平和服务质量，以在激烈的市场竞争中脱颖而出。<br></p><p>总之，建筑行业网站的制作是顺应时代发展的必然选择，它将为建筑行业的发展注入新的活力。</p>', '', '', '', '在当今数字化时代，建筑行业网站的制作具有至关重要的意义。对于建筑企业来说，一个专业的网站是展示企业实力和形象的重要窗口。它可以详细展示企业的过往项目案例，包括精美的图片和详细的项目介绍，让潜在客户直观地了解企业的施工能力和质量水平。同时，网站还能介绍企业的核心团队、技术优势和服务理念，提升企业的可信', 252, '1', '0', '0', '0', 8, 0, 0, 'Admin', 'Admin', '2024-11-25 08:07:46', '2024-12-15 17:05:17', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (42, 'cn', '2', '', 'badoucms功能列表', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams4f3c94f4696e55452f157173f340e2d321252398a.jpeg', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 百度推送</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 3, '1', '0', '0', '0', 6, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2024-12-15 17:21:31', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (63, 'cn', '2', '', 'badoucms基于thinkphp8+vue3的网站管理系统', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams17406d32c6971b1fd8b8e2550c6fc288a4b8730eb.jpeg', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 百度推送</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 2, '1', '0', '0', '0', 5, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2024-11-26 07:39:50', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (65, 'cn', '2', '', 'badoucms 模板标签', '', '', '', '', '', '', '2024-11-15 08:13:41', '/storage/default/20241126/teams23ad8d1e14db9eb4ee9374c9a793c78e593829078.jpeg', '', '', '<p><br></p><h3>介绍</h3><p>BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。</p><h3>功能列表</h3><ul><li> 区域管理（多语言）</li><li> 模型管理（自定义内容模型）</li><li> 模型字段管理（自定义模型字段）</li><li> 栏目管理</li><li> 内容管理（单页内容、文章内容、产品内容...自定义内容模型）</li><li> 站点配置、公司信息</li><li> 定制标签（自定义前台标签）</li><li> 前台模版标签</li><li> 轮播图片</li><li> 多条件筛选</li><li> 网站地图（sitemap）</li><li> 友情链接</li><li> 自定义表单</li><li> 留言信息</li><li> 文章内链</li><li> 多条件搜索</li><li> 百度推送</li><li> 内容权限</li><li> 会员功能(登录、注册、找回密码、修改密码、余额、积分、退出)</li><li> 会员字段</li><li> 会员等级(设置栏目与内容浏览权限)</li><li> 文章评论(回复、审核)</li><li> 我的评论</li></ul>', '', '', '', '介绍BadouCMS 基于 Vue3.3 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的开源网站管理系统，支持多语言、多模型、多条件搜索、内容权限、会员功能、文章评论、文章内链、百度推送、轮播图、多条件筛选、网站地图等。功能列表 区域管理（多语言） 模型管理（自定义内容模型） 模型字段管理（自定义', 4, '1', '0', '0', '0', 5, 0, 0, 'Admin', 'Admin', '2024-11-15 08:14:50', '2024-11-26 07:40:24', '4', '', '');
INSERT INTO `bd_cms_content` (`id`, `acode`, `scode`, `subscode`, `title`, `titlecolor`, `subtitle`, `filename`, `author`, `source`, `outlink`, `date`, `ico`, `pics`, `picstitle`, `content`, `tags`, `enclosure`, `keywords`, `description`, `sorting`, `status`, `istop`, `isrecommend`, `isheadline`, `visits`, `likes`, `oppose`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (66, 'cn', '2', '', 'BadouCMS快速入门', '', '', '', '', '', '', '2024-11-15 08:15:20', '/storage/default/20241125/b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', '', '', '<p><br></p><p><br></p><h2 ><span >图片缩放函数：resize_img</span></h2><p><span ><code>resize_img(string $src_image, int $max_width = 0, int $max_height = 0, int $img_quality = 90): string</code></span></p><p>参数：$src_image=源图片路径 （必填）</p><p> $max_width=缩放后的宽度</p><p> $max_height=缩放后的高度</p><p> $img_quality=图片质量</p><p>在标签中使用：<span ><code>{$item.ico|resize_img=\'10\',\'10\'}</code></span></p>', '', '', '', '测试新闻1', 255, '1', '0', '0', '0', 54, 0, 0, 'Admin', 'Admin', '2024-11-15 08:18:25', '2024-11-27 21:15:31', '4', '', '');
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
  PRIMARY KEY (`extid`),
  KEY `ay_content_ext_contentid` (`contentid`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='CMS文章内容-自定义字段';

-- ----------------------------
-- Records of bd_cms_content_ext
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (1, 9, '80', '专业版', '红色,黄色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (2, 10, '999', '基础版', '黄色,绿色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (3, 11, '1999', '旗舰版', '蓝色,紫色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (4, 12, '2999', '专业版', '黄色,绿色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (5, 13, '150', '基础版', '红色,橙色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (6, 18, '99', '基础版,专业版', NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (7, 26, '999', '基础版,专业版', '红色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (8, 24, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (9, 23, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (10, 27, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (11, 25, NULL, '基础版', '', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (12, 14, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (13, 32, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (14, 41, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (15, 42, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (16, 43, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (17, 1, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (18, 3, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (19, 46, NULL, '基础版', '蓝色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (20, 47, NULL, '基础版', '蓝色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (21, 48, NULL, '基础版', '绿色', '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (22, 49, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (23, 52, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (24, 50, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (25, 53, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (26, 60, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (27, 56, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (28, 58, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (29, 61, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (30, 63, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (31, 65, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (32, 66, NULL, NULL, NULL, '', '');
INSERT INTO `bd_cms_content_ext` (`extid`, `contentid`, `ext_price`, `ext_type`, `ext_color`, `ext_aaa`, `ext_aaaaa`) VALUES (33, 67, NULL, NULL, NULL, '', '');
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
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='栏目表';

-- ----------------------------
-- Records of bd_cms_content_sort
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (1, 'cn', '1', '0', '1', '公司简介', '', 'about.html', '1', '', '网站建设「一站式」服务商', '', '', '', '', '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', '', '', 'aboutus', 99, 'admin', 'Admin', '2018-04-11 17:26:11', '2024-11-24 20:56:04', '', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (2, 'cn', '2', '0', '2', '新闻中心', 'newslist.html', 'news.html', '1', '', '了解最新公司动态及行业资讯', '', '', '', '', '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', '', '', 'article', 100, 'admin', 'Admin', '2018-04-11 17:26:46', '2024-11-24 20:56:12', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (5, 'cn', '3', '0', '5', '产品中心', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'product', 100, 'admin', 'Admin', '2018-04-11 17:27:54', '2024-11-30 18:19:44', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (6, 'cn', '3', '5', '6', '网站建设', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'website', 255, 'admin', 'Admin', '2018-04-11 17:28:19', '2024-11-30 17:56:51', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (7, 'cn', '3', '5', '7', '域名空间', 'productlist.html', 'product.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', '', '', 'domain', 255, 'admin', 'Admin', '2018-04-11 17:28:38', '2024-11-30 17:56:37', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (8, 'cn', '4', '0', '8', '服务案例', 'caselist.html', 'case.html', '1', '', '服务创造价值、存在造就未来', '', '', '', '', '/storage/default/20241030/banner1a74d79711756abdf59740aa6be5750e81f8d0218.jpeg', '', '', '', 'case', 255, 'admin', 'Admin', '2018-04-11 17:29:16', '2024-11-25 21:50:55', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (9, 'cn', '5', '0', '9', '招贤纳士', 'joblist.html', 'job.html', '1', '', '诚聘优秀人士加入我们的团队', '', '', '', '', '/storage/default/20241125/ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', '', '', '', 'job', 255, 'admin', 'Admin', '2018-04-11 17:30:02', '2024-11-26 07:31:09', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (10, 'cn', '1', '0', '10', '在线留言', '', 'message.html', '1', '', '有什么问题欢迎您随时反馈', '', '', '', '', '', '', '', '', 'gbook', 800, 'admin', 'Admin', '2018-04-11 17:30:36', '2024-11-30 18:20:15', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (11, 'cn', '1', '0', '11', '联系我们', '', 'about.html', '1', '', '能为您服务是我们的荣幸', '', '', '', '', '', '', '', '', 'contact', 999, 'admin', 'Admin', '2018-04-11 17:31:29', '2024-11-30 18:20:10', '4', '', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (13, 'en', '1', '0', '13', '在线留言', '', 'message.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'admin', 'admin', '2023-01-05 20:21:52', '2024-08-10 10:45:44', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (12, 'en', '3', '0', '12', 'test', 'productlist.html', 'product.html', '1', '', '', '', '', '', '', '', '', '', '', '', 255, 'admin', 'admin', '2022-12-17 09:42:01', '2022-12-17 09:42:01', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (32, 'cn', '1', '10', '31', '调研', '', 'message2.html', '1', '', '', '', '', '', '', '', '', '', '', 'diaoyan', 256, '', 'Admin', '2024-11-23 21:32:46', '2024-11-24 11:00:15', '4', '0', '');
INSERT INTO `bd_cms_content_sort` (`id`, `acode`, `mcode`, `pcode`, `scode`, `name`, `listtpl`, `contenttpl`, `status`, `outlink`, `subname`, `def1`, `def2`, `def3`, `ico`, `pic`, `title`, `keywords`, `description`, `filename`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`, `gtype`, `gid`, `gnote`) VALUES (33, 'cn', '1', '10', '32', '留言', '', 'message.html', '1', '', '', '', '', '', '', '', '', '', '', 'gbook', 255, '', 'Admin', '2024-11-24 10:58:17', '2024-11-24 11:01:01', '4', '0', '');
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='模型字段';

-- ----------------------------
-- Records of bd_cms_extfield
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (1, '3', 'ext_price', '1', '', '产品价格', 255);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (2, '3', 'ext_type', '4', '基础版,专业版,旗舰版', '类型', 2);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (3, '3', 'ext_color', '4', '红色,橙色,黄色,绿色,蓝色,紫色', '颜色', 254);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (12, '2', 'ext_aaa', '6', '', '啊啊啊', 0);
INSERT INTO `bd_cms_extfield` (`id`, `mcode`, `name`, `type`, `value`, `description`, `sorting`) VALUES (13, '1', 'ext_aaaaa', '6', '', 'aaa', 0);
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='自定义表单';

-- ----------------------------
-- Records of bd_cms_form
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '在线留言', 'bd_cms_message', 'admin', 'admin', '2018-04-11 17:31:29', '2024-10-23 09:43:57');
INSERT INTO `bd_cms_form` (`id`, `fcode`, `form_name`, `table_name`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '调研', 'bd_cms_form_data', 'admin', 'admin', '2024-10-25 10:47:51', '2024-10-25 10:47:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='调研';

-- ----------------------------
-- Records of bd_cms_form_data
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (1, 'cn', '2024-10-25 10:52:58', '1312345678');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (2, 'cn', '2024-10-24 10:52:58', '1312345678');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (3, 'cn', '2024-10-23 10:52:58', '1312345678');
INSERT INTO `bd_cms_form_data` (`id`, `acode`, `create_time`, `tel`) VALUES (4, 'cn', '2024-11-24 11:01:31', '啊啊啊');
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
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='自定义表单-字段';

-- ----------------------------
-- Records of bd_cms_form_field
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', 'contacts', 10, '1', '联系人', 1, 'admin', 'admin', '2018-07-14 18:24:02', '2024-10-29 14:54:36');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '1', 'mobile', 12, '1', '手机', 2, 'admin', 'admin', '2018-07-14 18:24:02', '2024-10-29 14:54:40');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '1', 'content', 500, '0', '内容', 3, 'admin', 'Admin', '2018-07-14 18:24:02', '2024-11-23 16:27:34');
INSERT INTO `bd_cms_form_field` (`id`, `fcode`, `name`, `length`, `required`, `description`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (13, '2', 'tel', 20, '1', '电话', 1, 'admin', 'admin', '2024-10-25 10:49:32', '2024-10-25 10:49:32');
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='定制标签';

-- ----------------------------
-- Records of bd_cms_label
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_label` (`id`, `name`, `value`, `type`, `description`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'downlink', 'https://gitee.com/hnaoyun/badoucms/releases', '1', '下载地址', 'admin', 'admin', '2018-04-11 16:52:19', '2024-11-30 17:29:43');
INSERT INTO `bd_cms_label` (`id`, `name`, `value`, `type`, `description`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (7, 'logo2', '/storage/default/20241130/logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png', '3', '第二logo', 'Admin', 'Admin', '2024-10-30 22:09:41', '2024-11-30 17:29:43');
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='友情链接';

-- ----------------------------
-- Records of bd_cms_link
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', 1, 'badoucms', 'https://www.badoucms.com', '/static/upload/image/20180412/1523501605180536.png', 255, 'admin', 'admin', '2018-04-12 10:53:06', '2018-04-12 10:53:26');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, 'cn', 1, '百度', 'https://www.baidu.com/', '/storage/default/20240909/iShot_2024-09-0b7796dcdfe6d53c78e3a6c3cfa6df5a3ee22651d.png', 1, 'admin', 'admin', '2024-09-11 17:09:53', '2024-09-11 17:09:53');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, 'cn', 1, '新增分组 1', 'https://www.baidu.com/', '/storage/default/20240909/iShot_2024-09-0b7796dcdfe6d53c78e3a6c3cfa6df5a3ee22651d.png', 10, 'admin', 'admin', '2024-09-11 17:10:22', '2024-09-11 21:24:41');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, 'cn', 1, '百度', 'http://localhost/', '/storage/default/20240928/logo75358d77e096dc9e47a5b1425bf188d73717fac3.png', 2, 'admin', 'admin', '2024-09-28 10:10:01', '2024-09-28 10:10:01');
INSERT INTO `bd_cms_link` (`id`, `acode`, `gid`, `name`, `link`, `logo`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (6, 'cn', 1, '百度 1', 'http://localhost/', NULL, 1, 'admin', 'admin', '2024-09-28 10:11:19', '2024-09-28 10:11:19');
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
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (16, 14, 43, '审核评论2', 1, 1, 0, 0, '1', '2130706433', 'Mac', 'Chrome', '2024-11-17 22:38:24', '', '2024-11-17 22:52:13');
INSERT INTO `bd_cms_member_comment` (`id`, `pid`, `contentid`, `comment`, `uid`, `puid`, `likes`, `oppose`, `status`, `user_ip`, `user_os`, `user_bs`, `create_time`, `update_user`, `update_time`) VALUES (15, 0, 43, '审核回复', 1, 0, 0, 0, '0', '2130706433', 'Mac', 'Chrome', '2024-11-17 22:36:06', '', '2024-11-17 22:36:06');
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
  PRIMARY KEY (`id`),
  KEY `ay_message_acode` (`acode`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='在线留言';

-- ----------------------------
-- Records of bd_cms_message
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (1, 'cn', '星梦', '16888888888', 'badoucms真心很不错哦！', '2130706433', 'Windows 10', 'Firefox', '谢谢您对我们的大力支持与肯定！', '1', 'admin', 'admin', '2018-04-12 10:56:09', '2024-11-22 20:27:21', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (4, 'en', '111', '13112341234', '12121', '3232243713', 'Mac', 'Chrome', '测试回复一下', '1', 'guest', 'guest', '2023-01-05 20:32:03', '2024-10-25 10:04:11', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (6, '', 'test', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (7, '', 'aaa', 'aaa', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (8, '', 'aaa', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (9, '', 'aaa', '13112341234', '', '0', '', '', '', '1', '', '', '2024-11-24 11:01:31', '2024-11-24 11:01:31', 0);
INSERT INTO `bd_cms_message` (`id`, `acode`, `contacts`, `mobile`, `content`, `user_ip`, `user_os`, `user_bs`, `recontent`, `status`, `create_user`, `update_user`, `create_time`, `update_time`, `uid`) VALUES (14, 'cn', 'test', '18862132539', '1111', '2130706433', 'Mac', 'Chrome', '', '1', 'guest', 'guest', '2024-11-23 19:55:27', '2024-11-23 19:55:37', 0);
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
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='模型管理';

-- ----------------------------
-- Records of bd_cms_model
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, '1', '专题', '1', 'about', '', 'about.html', '1', '1', 'admin', 'admin', '2018-04-11 17:16:01', '2024-09-13 21:18:57');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, '2', '新闻', '2', 'list', 'newslist.html', 'news.html', '1', '1', 'admin', 'Admin', '2018-04-11 17:17:16', '2024-12-05 21:19:23');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (3, '3', '产品', '2', 'list', 'productlist.html', 'product.html', '1', '0', 'admin', 'admin', '2018-04-11 17:17:46', '2024-09-21 18:16:55');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, '4', '案例', '2', 'list', 'caselist.html', 'case.html', '1', '0', 'admin', 'admin', '2018-04-11 17:19:53', '2024-09-21 18:16:56');
INSERT INTO `bd_cms_model` (`id`, `mcode`, `name`, `type`, `urlname`, `listtpl`, `contenttpl`, `status`, `issystem`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, '5', '招聘', '2', 'list', 'joblist.html', 'job.html', '1', '0', 'admin', 'admin', '2018-04-11 17:24:34', '2024-09-21 18:16:56');
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
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (1, 'cn', 'BadouCMS', '永久开源免费的PHP企业网站开发建设管理系统', '', '/storage/default/20241130/logo-a1f38377ae174272d0558a18e741d5fad8b08c480.png', 'cms,免费cms,开源cms,企业cms,建站cms', 'BadouCMS是一套全新内核且永久开源免费的PHP企业网站开发建设管理系统，是一套高效、简洁、 强悍的可免费商用的PHP CMS源码，能够满足各类企业网站开发建设的需要。系统采用简单到想哭的模板标签，只要懂HTML就可快速开发企业网站。官方提供了大量网站模板免费下载和使用，将致力于为广大开发者和企业提供最佳的网站开发建设解决方案。', '苏ICP备88888888号', 'default', '', 'BadouCMS All Rights Reserved.');
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (2, 'en', '1111', '123', '123', '', '', '', '', 'en', '', '');
INSERT INTO `bd_cms_site` (`id`, `acode`, `title`, `subtitle`, `domain`, `logo`, `keywords`, `description`, `icp`, `theme`, `statistical`, `copyright`) VALUES (3, 'oe', '德文站点', '德文站点', '', '', '', '', '', '', '', '');
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='轮播图片';

-- ----------------------------
-- Records of bd_cms_slide
-- ----------------------------
BEGIN;
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (1, 'cn', 1, '/storage/default/20241030/banner2d0fda8a3e11edf5116c34f0e20cedd4d56def65a.jpeg', '', 'BADOUCMS', '基于Thinkphp8、Vue3等开源项目', 2, 'admin', 'Admin', '2018-03-01 16:19:03', '2024-11-30 19:16:19');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (2, 'cn', 1, '/storage/default/20241030/banner1a74d79711756abdf59740aa6be5750e81f8d0218.jpeg', '', 'BADOUCMS', '免费、开源PHP网站管理系统', 1, 'admin', 'Admin', '2018-04-12 10:46:07', '2024-11-30 17:49:55');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (4, 'cn', 1, '/storage/default/20241124/banner347491b1c8d21b9768f7a3b4bb8e9abb681a5f566.jpeg', '', 'BADOUCMS', '海量模板选择、降低开发成本', 0, 'admin', 'Admin', '2024-11-24 20:14:03', '2024-11-30 17:49:50');
INSERT INTO `bd_cms_slide` (`id`, `acode`, `gid`, `pic`, `link`, `title`, `subtitle`, `sorting`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES (5, 'cn', 2, '/storage/default/20241125/ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', '', '公司简介', '', 0, 'admin', 'Admin', '2024-11-25 20:28:39', '2024-11-30 17:30:55');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='文章内链';

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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量输入组件类型',
  `value` longtext COLLATE utf8mb4_unicode_ci COMMENT '变量值',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT '字典数据',
  `rule` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展属性',
  `allow_del` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '允许删除:0=否,1=是',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='系统配置';

-- ----------------------------
-- Records of bd_config
-- ----------------------------
BEGIN;
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (1, 'config_group', 'other', 'Config group', '', 'array', '[{\"key\":\"basics\",\"value\":\"Basics\"},{\"key\":\"other\",\"value\":\"\\u5176\\u4ed6\\u914d\\u7f6e\"},{\"key\":\"mail\",\"value\":\"Mail\"},{\"key\":\"user\",\"value\":\"\\u4f1a\\u5458\\u914d\\u7f6e\"},{\"key\":\"message\",\"value\":\"\\u7559\\u8a00\\u914d\\u7f6e\"},{\"key\":\"webapi\",\"value\":\"WebApi\"}]', NULL, 'required', '', 0, -1);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (2, 'site_name', 'basics', 'Site Name', '', 'string', 'BADOUCMS', NULL, 'required', '', 0, 99);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (4, 'version', 'basics', 'Version number', '系统版本号', 'string', 'v1.0.0', NULL, 'required', '', 0, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (5, 'time_zone', 'basics', 'time zone', '', 'string', 'Asia/Shanghai', NULL, 'required', '', 0, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (6, 'no_access_ip', 'basics', 'No access ip', '禁止访问站点的ip列表,一行一个', 'textarea', '', NULL, '', '', 0, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (7, 'smtp_server', 'mail', 'smtp server', '', 'string', 'smtp.163.com', NULL, '', '', 0, 9);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (8, 'smtp_port', 'mail', 'smtp port', '', 'string', '465', NULL, '', '', 0, 8);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (9, 'smtp_user', 'mail', 'smtp user', '', 'string', 'badoucms@163.com', NULL, '', '', 0, 7);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (10, 'smtp_pass', 'mail', 'smtp pass', '', 'string', 'CGQB5CTiBbbnhyva', NULL, '', '', 0, 6);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (11, 'smtp_verification', 'mail', 'smtp verification', '', 'select', 'SSL', '{\"SSL\":\"SSL\",\"TLS\":\"TLS\"}', '', '', 0, 5);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (12, 'smtp_sender_mail', 'mail', 'smtp sender mail', '', 'string', 'badoucms@163.com', NULL, 'email', '', 0, 4);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (13, 'config_quick_entrance', 'other', 'Config Quick entrance', '', 'array', '[{\"key\":\"\\u6570\\u636e\\u56de\\u6536\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"security\\/dataRecycle\"},{\"key\":\"\\u654f\\u611f\\u6570\\u636e\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"security\\/sensitiveData\"},{\"key\":\"\\u6570\\u636e\\u5e93\\u5907\\u4efd\",\"value\":\"backupdb\"}]', NULL, '', '', 0, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (14, 'backend_entrance', 'other', 'Backend entrance', '请在vue开发模式下使用', 'string', '/admin', NULL, 'required', '', 0, 1);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (15, 'main_domain', 'basics', '网站主域名', '', 'string', '', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (16, 'commentstatus', 'user', '评论功能', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (17, 'commentcodestatus', 'user', '评论验证码', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (18, 'comment_verify', 'user', '评论审核', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (19, 'comment_send_mail', 'user', '评论是否发送邮件', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (21, 'message_send_to', 'mail', '信息接收邮箱', '', 'string', '939134342@qq.com', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (22, 'message_status', 'message', '开启留言', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (23, 'message_rqlogin', 'message', '留言需登录', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (24, 'message_check_code', 'message', '留言验证码', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (25, 'message_send_mail', 'message', '留言发送邮件', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (26, 'message_verify', 'message', '留言审核', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (27, 'form_status', 'message', '开启表单', '', 'switch', '1', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (28, 'form_check_code', 'message', '表单验证码', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (29, 'form_send_mail', 'message', '表单发送邮件', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (30, 'tpl_error', 'basics', '模版报错提醒', '', 'radio', '0', '', '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (32, 'api_open', 'webapi', 'API状态', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (33, 'api_auth', 'webapi', 'API强制认证', '', 'switch', '0', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (35, 'api_appid', 'webapi', 'API认证用户', '', 'string', 'apiadmin', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (36, 'api_secret', 'webapi', 'API认证密钥', '', 'string', 'apipass', NULL, '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (37, 'tpl_html_dir', 'basics', '模板子目录', '一定程度上防盗，如填 html，则默认模板情况下路径为 default/html 目录！', 'string', 'html', NULL, '', '', 1, 0);
COMMIT;
-- ----------------------------
-- Table structure for bd_crud_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_crud_log`;
CREATE TABLE `bd_crud_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `table_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表名',
  `table` text COLLATE utf8mb4_unicode_ci COMMENT '数据表数据',
  `fields` text COLLATE utf8mb4_unicode_ci COMMENT '字段数据',
  `status` enum('delete','success','error','start') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'start' COMMENT '状态:delete=已删除,success=成功,error=失败,start=生成中',
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `create_time` bigint(20) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='CRUD记录表';

-- ----------------------------
-- Records of bd_crud_log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bd_migrations
-- ----------------------------
DROP TABLE IF EXISTS `bd_migrations`;
CREATE TABLE `bd_migrations` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bd_migrations
-- ----------------------------
BEGIN;
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20230620180908, 'Install', '2024-08-04 08:37:43', '2024-08-04 08:37:43', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20230620180916, 'InstallData', '2024-08-04 08:37:43', '2024-08-04 08:37:43', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20230622221507, 'Version200', '2024-08-04 08:37:43', '2024-08-04 08:37:44', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20230719211338, 'Version201', '2024-08-04 08:37:44', '2024-08-04 08:37:44', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20230905060702, 'Version202', '2024-08-04 08:37:44', '2024-08-04 08:37:44', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20231112093414, 'Version205', '2024-08-04 08:37:44', '2024-08-04 08:37:44', 0);
INSERT INTO `bd_migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES (20231229043002, 'Version206', '2024-08-04 08:37:44', '2024-08-04 08:37:44', 0);
COMMIT;

-- ----------------------------
-- Table structure for bd_security_data_recycle
-- ----------------------------
DROP TABLE IF EXISTS `bd_security_data_recycle`;
CREATE TABLE `bd_security_data_recycle` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `controller` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `controller_as` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器别名',
  `data_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应数据表',
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='回收规则表';

-- ----------------------------
-- Records of bd_security_data_recycle
-- ----------------------------
BEGIN;
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (1, '管理员', 'auth/Admin.php', 'auth/admin', 'admin', '', 'id', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (2, '管理员日志', 'auth/AdminLog.php', 'auth/adminlog', 'admin_log', '', 'id', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (3, '菜单规则', 'auth/Menu.php', 'auth/menu', 'menu_rule', '', 'id', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (4, '系统配置项', 'routine/Config.php', 'routine/config', 'config', '', 'id', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (5, '会员', 'user/User.php', 'user/user', 'user', '', 'id', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_data_recycle` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `status`, `update_time`, `create_time`) VALUES (6, '数据回收规则', 'security/DataRecycle.php', 'security/datarecycle', 'security_data_recycle', '', 'id', '1', 1722731863, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_security_data_recycle_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_security_data_recycle_log`;
CREATE TABLE `bd_security_data_recycle_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `recycle_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回收规则ID',
  `data` text COLLATE utf8mb4_unicode_ci COMMENT '回收的数据',
  `data_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表',
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `is_restore` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否已还原:0=否,1=是',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作者IP',
  `useragent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='数据回收记录表';

-- ----------------------------
-- Records of bd_security_data_recycle_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_security_data_recycle_log` (`id`, `admin_id`, `recycle_id`, `data`, `data_table`, `connection`, `primary_key`, `is_restore`, `ip`, `useragent`, `create_time`) VALUES (1, 1, 4, '{\"id\":20,\"name\":\"message_send_to\",\"group\":\"user\",\"title\":\"信息接收邮箱\",\"tip\":\"\",\"type\":\"string\",\"value\":null,\"content\":null,\"rule\":\"\",\"extend\":\"\",\"allow_del\":1,\"weigh\":0}', 'config', '', 'id', 0, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 1731833781);
COMMIT;

-- ----------------------------
-- Table structure for bd_security_sensitive_data
-- ----------------------------
DROP TABLE IF EXISTS `bd_security_sensitive_data`;
CREATE TABLE `bd_security_sensitive_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `controller` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `controller_as` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器别名',
  `data_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应数据表',
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `data_fields` text COLLATE utf8mb4_unicode_ci COMMENT '敏感数据字段',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='敏感数据规则表';

-- ----------------------------
-- Records of bd_security_sensitive_data
-- ----------------------------
BEGIN;
INSERT INTO `bd_security_sensitive_data` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `data_fields`, `status`, `update_time`, `create_time`) VALUES (1, '管理员数据', 'auth/Admin.php', 'auth/admin', 'admin', '', 'id', '{\"username\":\"用户名\",\"mobile\":\"手机\",\"password\":\"密码\",\"status\":\"状态\"}', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_sensitive_data` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `data_fields`, `status`, `update_time`, `create_time`) VALUES (2, '会员数据', 'user/User.php', 'user/user', 'user', '', 'id', '{\"username\":\"用户名\",\"mobile\":\"手机号\",\"password\":\"密码\",\"status\":\"状态\",\"email\":\"邮箱地址\"}', '1', 1722731863, 1722731863);
INSERT INTO `bd_security_sensitive_data` (`id`, `name`, `controller`, `controller_as`, `data_table`, `connection`, `primary_key`, `data_fields`, `status`, `update_time`, `create_time`) VALUES (3, '管理员权限', 'auth/Group.php', 'auth/group', 'admin_group', '', 'id', '{\"rules\":\"权限规则ID\"}', '1', 1722731863, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_security_sensitive_data_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_security_sensitive_data_log`;
CREATE TABLE `bd_security_sensitive_data_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `sensitive_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '敏感数据规则ID',
  `data_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表',
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `data_field` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '被修改字段',
  `data_comment` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '被修改项',
  `id_value` int(11) NOT NULL DEFAULT '0' COMMENT '被修改项主键值',
  `before` text COLLATE utf8mb4_unicode_ci COMMENT '修改前',
  `after` text COLLATE utf8mb4_unicode_ci COMMENT '修改后',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作者IP',
  `useragent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `is_rollback` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否已回滚:0=否,1=是',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='敏感数据修改记录';

-- ----------------------------
-- Records of bd_security_sensitive_data_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (1, 1, 2, 'user', '', 'id', 'username', '用户名', 1, 'user', 'test', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730296909);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (2, 1, 2, 'user', '', 'id', 'password', '密码', 1, '55a90d20b3f2f6561aeb8ff14919e1e7', '******', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730296909);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (3, 1, 2, 'user', '', 'id', 'mobile', '手机号', 1, '18888888888', '', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730554090);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (4, 1, 2, 'user', '', 'id', 'email', '邮箱地址', 1, '18888888888@qq.com', '', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730554090);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (5, 1, 2, 'user', '', 'id', 'email', '邮箱地址', 1, '', '123123@qq.com', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730558125);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (6, 1, 2, 'user', '', 'id', 'email', '邮箱地址', 1, '123123@qq.com', 'badoucms@163.com', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730599047);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (7, 1, 2, 'user', '', 'id', 'email', '邮箱地址', 1, 'badoucms@163.com', '', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1730599803);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (8, 1, 2, 'user', '', 'id', 'status', '状态', 1, '0', 'enable', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1731286341);
INSERT INTO `bd_security_sensitive_data_log` (`id`, `admin_id`, `sensitive_id`, `data_table`, `connection`, `primary_key`, `data_field`, `data_comment`, `id_value`, `before`, `after`, `ip`, `useragent`, `is_rollback`, `create_time`) VALUES (9, 1, 2, 'user', '', 'id', 'status', '状态', 1, '', 'enable', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 0, 1731286448);
COMMIT;

-- ----------------------------
-- Table structure for bd_token
-- ----------------------------
DROP TABLE IF EXISTS `bd_token`;
CREATE TABLE `bd_token` (
  `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Token',
  `type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  `expire_time` bigint(16) unsigned DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户Token表';

-- ----------------------------
-- Records of bd_token
-- ----------------------------
BEGIN;
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('0192b7a587e688062ff86d586e2757d44656b9c8', 'admin', 1, 1734095250, 1734354450);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('0e628513ad4589d0edff51090b44cdacf450fd22', 'admin', 1, 1734001598, 1734260798);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('3791c954ec555c2287c923b3884e449399fd4d8b', 'admin', 1, 1734057161, 1734316361);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('3c43dcecd62d1b074eeaeee060b19759eec2a51e', 'admin-refresh', 1, 1731502735, 1734094735);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('44cea3f022511ae083447b688e19ce481c8b6f57', 'admin', 1, 1734049286, 1734308486);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('4bece54616bcc29b406968831e44fa5f35e786e0', 'admin', 1, 1733839337, 1734098537);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('7b95469bad96d2e48c193b716d7207f16c0dad22', 'admin-refresh', 1, 1731848715, 1734440715);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('7be350f6dafeb634d5de924474d59473c5fe16c1', 'admin', 1, 1743244613, 1743503813);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('a9168816f6c427e4628e7b1a97f726c5f26109ac', 'admin', 1, 1733917967, 1734177167);
COMMIT;

-- ----------------------------
-- Table structure for bd_user
-- ----------------------------
DROP TABLE IF EXISTS `bd_user`;
CREATE TABLE `bd_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
  `money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '余额',
  `score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `last_login_time` bigint(16) unsigned DEFAULT NULL COMMENT '上次登录时间',
  `last_login_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `login_failure` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `join_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '加入IP',
  `join_time` bigint(16) unsigned DEFAULT NULL COMMENT '加入时间',
  `motto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签名',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码盐',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  `level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `level1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员表';

-- ----------------------------
-- Records of bd_user
-- ----------------------------
BEGIN;
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `email`, `mobile`, `avatar`, `gender`, `money`, `score`, `last_login_time`, `last_login_ip`, `login_failure`, `join_ip`, `join_time`, `motto`, `password`, `salt`, `status`, `update_time`, `create_time`, `level`, `level1`) VALUES (1, 1, 'test', 'Test', 'badoucms@163.com', '', '/storage/default/20241102/WX20240729-20258d1082e4223840afdba0a611ac57ca8f1be6a50e.png', 1, 9900, 10, 1733406220, '127.0.0.1', 0, '', NULL, 'sdfsdf\n\nsdfs', '0e94728afd257c6d8bf23d1770058c7e', 'HsRzK4TxY0dkbUCy', 'enable', 1733406220, 1722731863, '1', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_group`;
CREATE TABLE `bd_user_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT '权限节点',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员组表';

-- ----------------------------
-- Records of bd_user_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_group` (`id`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES (1, '默认分组', '*', '1', 1722731863, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_user_money_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_money_log`;
CREATE TABLE `bd_user_money_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `money` int(11) NOT NULL DEFAULT '0' COMMENT '变更余额',
  `before` int(11) NOT NULL DEFAULT '0' COMMENT '变更前余额',
  `after` int(11) NOT NULL DEFAULT '0' COMMENT '变更后余额',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员余额变动表';

-- ----------------------------
-- Records of bd_user_money_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `create_time`) VALUES (1, 1, 9900, 0, 9900, 'aa', 1730636039);
COMMIT;

-- ----------------------------
-- Table structure for bd_user_rule
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_rule`;
CREATE TABLE `bd_user_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` enum('route','menu_dir','menu','nav_user_menu','nav','button') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏会员菜单下拉项,nav=顶栏菜单项,button=页面按钮',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tab' COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `no_login_valid` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '未登录有效:0=否,1=是',
  `extend` enum('none','add_rules_only','add_menu_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint(16) unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员菜单权限规则表';

-- ----------------------------
-- Records of bd_user_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (1, 0, 'menu_dir', '我的账户', 'account', 'account', 'fa fa-user-circle', 'tab', '', '', 0, 'none', '', 98, '1', 1722731863, 1722731863);
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (2, 1, 'menu', '账户概览', 'account/overview', 'account/overview', 'fa fa-home', 'tab', '', '/src/views/frontend/user/account/overview.vue', 0, 'none', '', 99, '1', 1722731863, 1722731863);
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (3, 1, 'menu', '个人资料', 'account/profile', 'account/profile', 'fa fa-user-circle-o', 'tab', '', '/src/views/frontend/user/account/profile.vue', 0, 'none', '', 98, '1', 1722731863, 1722731863);
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (4, 1, 'menu', '修改密码', 'account/changePassword', 'account/changePassword', 'fa fa-shield', 'tab', '', '/src/views/frontend/user/account/changePassword.vue', 0, 'none', '', 97, '1', 1722731863, 1722731863);
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (5, 1, 'menu', '积分记录', 'account/integral', 'account/integral', 'fa fa-tag', 'tab', '', '/src/views/frontend/user/account/integral.vue', 0, 'none', '', 96, '1', 1722731863, 1722731863);
INSERT INTO `bd_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `update_time`, `create_time`) VALUES (6, 1, 'menu', '余额记录', 'account/balance', 'account/balance', 'fa fa-money', 'tab', '', '/src/views/frontend/user/account/balance.vue', 0, 'none', '', 95, '1', 1722731863, 1722731863);
COMMIT;

-- ----------------------------
-- Table structure for bd_user_score_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_score_log`;
CREATE TABLE `bd_user_score_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '变更积分',
  `before` int(11) NOT NULL DEFAULT '0' COMMENT '变更前积分',
  `after` int(11) NOT NULL DEFAULT '0' COMMENT '变更后积分',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint(16) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员积分变动表';

-- ----------------------------
-- Records of bd_user_score_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_score_log` (`id`, `user_id`, `score`, `before`, `after`, `memo`, `create_time`) VALUES (1, 1, 10, 0, 10, '啊啊', 1730634815);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
