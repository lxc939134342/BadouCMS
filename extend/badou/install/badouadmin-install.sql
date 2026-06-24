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

 Date: 04/07/2025 07:32:55
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ----------------------------
-- Records of bd_admin
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `password`, `avatar`, `email`, `mobile`, `loginfailure`, `login_time`, `login_ip`, `create_time`, `update_time`, `token`, `status`) VALUES (1, 'admin', 'Admin', '$2y$10$9nPBBf2EDMvIb7yB3eAdwu2Y21frNNhzryXfyd4oWcCSh2cNlI5HS', '/uploads/20250620/0dcfaacfd8502183cd16ab3aad0475a3.png', 'admin@admin.com', '', 0, 1751554761, '127.0.0.1', 1491635035, 1751554761, 'ece18065-a00b-4b15-929f-05fac40c2179', 'normal');
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `password`, `avatar`, `email`, `mobile`, `loginfailure`, `login_time`, `login_ip`, `create_time`, `update_time`, `token`, `status`) VALUES (2, 'test', '管理员', '$2y$10$n9OPWfRDJ7sfK6lljmqZn.EhXMSPaBQ6Q.XlTCVS7G/ruVb10reZ6', '/assets/img/avatar.png', '123@qq.com', '', 0, 1751507189, '127.0.0.1', 1656558031, 1751507189, '02790f13-4889-40f9-8217-3e004ed50ed7', 'normal');
INSERT INTO `bd_admin` (`id`, `username`, `nickname`, `password`, `avatar`, `email`, `mobile`, `loginfailure`, `login_time`, `login_ip`, `create_time`, `update_time`, `token`, `status`) VALUES (3, 'test1', '测试1', '$2y$10$IzTG2BpNeW2YVDX8ru.Jw.fK3KDN7APXPc.C5pOFBeUS6JVgMtleG', '/assets/img/avatar.png', '123123123@123.com', '', 0, NULL, NULL, 1750390370, 1751005487, '', 'normal');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='分组表';

-- ----------------------------
-- Records of bd_admin_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `create_time`, `update_time`, `status`) VALUES (1, 0, 'Admin group', '*', 1491635035, 1491635035, 'normal');
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `create_time`, `update_time`, `status`) VALUES (2, 1, '二级分组', '117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,330,331,870,871,872,873,874,875,876,877,878,879,880,881,882,883,884,885,886,887,888,889,890,891,892,893,894,895,896,897,898,899,900,901,902,903,904,905,906,907,908,909,910,911,912,913,914,915,916,917,918,919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,935,936,937,938,939,940,941,942,943,944,945,946,947,948,949,950,951,952,953,954,955,956,957,958,959,960,961,962,963,964,965,966,967,968,969,970,971,972,973,974,975,976,977,978,979,980,981,982,983,984,985,986,987,988,989,990,991', 1750344547, 1751507180, 'normal');
INSERT INTO `bd_admin_group` (`id`, `pid`, `name`, `rules`, `create_time`, `update_time`, `status`) VALUES (3, 2, '三级', '', 1750347494, 1751507180, 'normal');
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
INSERT INTO `bd_admin_group_access` (`uid`, `group_id`) VALUES (2, 2);
INSERT INTO `bd_admin_group_access` (`uid`, `group_id`) VALUES (3, 3);
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
) ENGINE=InnoDB AUTO_INCREMENT=1715 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员日志表';

-- ----------------------------
-- Table structure for bd_admin_rule
-- ----------------------------
DROP TABLE IF EXISTS `bd_admin_rule`;
CREATE TABLE `bd_admin_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT '类型:0=菜单目录,1=菜单项,2=按钮',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) DEFAULT '' COMMENT '规则名称',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `url` varchar(255) DEFAULT '' COMMENT '规则URL',
  `condition` varchar(255) DEFAULT '' COMMENT '条件',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `is_quick` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否快捷菜单',
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
) ENGINE=InnoDB AUTO_INCREMENT=992 DEFAULT CHARSET=utf8mb4 COMMENT='节点表';

-- ----------------------------
-- Records of bd_admin_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1, '1', 0, 'dashboard', '控制台', 'layui-icon layui-icon-console', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995686, 99999, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (2, '1', 1, 'dashboard/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (117, '0', 0, 'auth', '权限管理', 'layui-icon layui-icon-auz', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995948, 99997, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (118, '1', 117, 'auth.group', '角色组', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1751008747, 98, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (119, '1', 118, 'auth.group/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (120, '1', 118, 'auth.group/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (121, '1', 118, 'auth.group/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (122, '1', 118, 'auth.group/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (123, '1', 118, 'auth.group/roletree', 'roletree', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (124, '1', 118, 'auth.group/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (125, '1', 117, 'auth.rule', '菜单规则', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1751008756, 97, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (126, '1', 125, 'auth.rule/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (127, '1', 125, 'auth.rule/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (128, '1', 125, 'auth.rule/selectpage', 'selectpage', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (129, '1', 125, 'auth.rule/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (130, '1', 125, 'auth.rule/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (131, '1', 125, 'auth.rule/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (132, '1', 117, 'auth.admin', '管理员', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1751008711, 99, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (133, '1', 132, 'auth.admin/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (134, '1', 132, 'auth.admin/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (135, '1', 132, 'auth.admin/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (136, '1', 132, 'auth.admin/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (137, '1', 132, 'auth.admin/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (138, '1', 132, 'auth.admin/getGroupList', 'getGroupList', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (139, '1', 117, 'auth.adminlog', 'adminlog', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (140, '1', 139, 'auth.adminlog/deletelog', 'deletelog', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (141, '1', 139, 'auth.adminlog/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (142, '1', 139, 'auth.adminlog/detail', 'detail', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (143, '1', 139, 'auth.adminlog/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (144, '1', 139, 'auth.adminlog/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (145, '1', 139, 'auth.adminlog/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (146, '1', 139, 'auth.adminlog/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (279, '0', 0, 'general', '常规设置', 'layui-icon layui-icon-set', '', '', '', 1, 0, '_iframe', '', '', '', 1750995040, 1751626087, 99998, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (280, '1', 279, 'general.config', '系统设置', 'layui-icon layui-icon-set', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1751893428, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (281, '1', 280, 'general.config/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (282, '1', 280, 'general.config/saveConfig', 'saveConfig', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (283, '1', 280, 'general.config/setting', 'setting', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (284, '1', 280, 'general.config/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (285, '1', 280, 'general.config/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (286, '1', 280, 'general.config/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (287, '1', 280, 'general.config/sendTestMail', 'sendTestMail', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (288, '1', 280, 'general.config/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (289, '1', 279, 'general.profile', 'profile', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (290, '1', 289, 'general.profile/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (291, '1', 289, 'general.profile/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (292, '1', 289, 'general.profile/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (293, '1', 289, 'general.profile/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (294, '1', 289, 'general.profile/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (295, '1', 279, 'general.attachment', 'attachment', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (296, '1', 295, 'general.attachment/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (297, '1', 295, 'general.attachment/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (298, '1', 295, 'general.attachment/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (299, '1', 295, 'general.attachment/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (300, '1', 295, 'general.attachment/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (301, '0', 0, 'user', 'User', 'layui-icon layui-icon-user', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1750995941, 99995, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (302, '1', 301, 'user.group', '角色组', 'fa fa-circle-o', '', '', '', 1, 0, NULL, '', '', '', 1750995040, 1751008865, 98, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (303, '1', 302, 'user.group/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (304, '1', 302, 'user.group/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (305, '1', 302, 'user.group/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (306, '1', 302, 'user.group/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (307, '1', 302, 'user.group/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (308, '1', 301, 'user.rule', '菜单规则', 'layui-icon layui-icon-form', '', '', '', 1, 1, NULL, '', '', '', 1750995040, 1782261924, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (309, '1', 308, 'user.rule/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (310, '1', 308, 'user.rule/selectpage', 'selectpage', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (311, '1', 308, 'user.rule/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (312, '1', 308, 'user.rule/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (313, '1', 308, 'user.rule/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (314, '1', 308, 'user.rule/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (315, '1', 301, 'user.user', '用户管理', 'layui-icon layui-icon-username', '', '', '', 1, 1, NULL, '', '', '', 1750995040, 1782261832, 99, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (316, '1', 315, 'user.user/index', 'index', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (317, '1', 315, 'user.user/add', 'add', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (318, '1', 315, 'user.user/edit', 'edit', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (319, '1', 315, 'user.user/del', 'del', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (320, '1', 315, 'user.user/multi', 'multi', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (345, '1', 0, 'module', '插件管理', 'layui-icon layui-icon-app', '', '', '', 1, 1, NULL, '', '', '', 1750995040, 1782261809, 99994, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (346, '2', 345, 'module/index', '查看', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750996393, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (347, '2', 345, 'module/info', '详情', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750996372, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (348, '2', 345, 'module/install', '安装', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750996126, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (349, '2', 345, 'module/uninstall', 'uninstall', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (350, '2', 345, 'module/state', 'state', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (351, '2', 345, 'module/upgrade', 'upgrade', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (352, '2', 345, 'module/testdata', 'testdata', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (353, '2', 345, 'module/isbuy', 'isbuy', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (354, '2', 345, 'module/authorization', 'authorization', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (355, '2', 345, 'module/getTableList', 'getTableList', 'fa fa-circle-o', '', '', '', 0, 0, NULL, '', '', '', 1750995040, 1750995040, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (992, '1', 301, 'user.level', '用户等级', '', '', '', '', 1, 0, '_iframe', '', '', '', 1752203016, 1752203034, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (993, '2', 992, 'user.level/index', '查看', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752203060, 1752203077, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (994, '2', 992, 'user.level/add', '添加', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752203113, 1752203113, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (995, '2', 992, 'user.level/edit', '编辑', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752203137, 1752203137, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (996, '2', 992, 'user.level/del', '删除', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752203162, 1752203162, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (997, '1', 992, 'user.level/multi', '批量更新', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752284573, 1752284573, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (998, '1', 279, 'database', '数据库管理', '', '', '', '', 1, 0, '_iframe', '', '', '', 1752370539, 1752370539, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (999, '2', 998, 'database/index', '查看', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752552921, 1752552921, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1000, '2', 998, 'database/backuplist', '备份/还原', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752552979, 1752552979, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1001, '2', 998, 'database/backup', '备份', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752553006, 1752553006, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1002, '2', 998, 'database/backupdel', '删除备份', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752553029, 1752553029, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1003, '2', 998, 'database/restore', '恢复', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752553058, 1752553058, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1004, '2', 998, 'database/optimize', '优化表', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752553086, 1752553086, 0, 'normal');
INSERT INTO `bd_admin_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `is_quick`, `menutype`, `extend`, `py`, `pinyin`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1005, '2', 998, 'database/repair', '修复表', '', '', '', '', 0, 0, '_iframe', '', '', '', 1752553110, 1752553110, 0, 'normal');
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
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

-- ----------------------------
-- Records of bd_attachment
-- ----------------------------
BEGIN;
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (139, '', 1, 1, '/uploads/20250622/83f70d6c57f86c51e8666b16afcb4830.jpeg', '340', '280', 'jpeg', 0, 'b1.jpeg', 45718, 'image/jpeg', '', 1750588619, 1750588619, 1750588619, 'local', '415ad46278103146361019859ee60a6e978d1a57');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (140, '', 1, 1, '/uploads/20250622/41d132cd2d284c5926635101aa2ac71f.jpeg', '340', '280', 'jpeg', 0, 'b2.jpeg', 21299, 'image/jpeg', '', 1750588634, 1750588634, 1750588634, 'local', '7d7f31daa1155042a5692aa5477fa4632c2a79f5');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (141, '', 1, 1, '/uploads/20250622/577a43d1730d8fb8aae352738e411abf.jpeg', '340', '280', 'jpeg', 0, 'b3.jpeg', 37559, 'image/jpeg', '', 1750588640, 1750588640, 1750588640, 'local', 'd1dc760aca9445023eeaee0e9a142c53b38a4c14');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (142, '', 1, 1, '/uploads/20250622/d80a893b09a22114734e001a00e4082d.jpeg', '340', '280', 'jpeg', 0, 'b4.jpeg', 31874, 'image/jpeg', '', 1750588658, 1750588658, 1750588658, 'local', 'ad8ebe742c5f02e5df9e7d1a614a0a2daa308d93');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (143, '', 1, 1, '/uploads/20250622/e011298e4ec8e8e20fe32e14431f9763.jpeg', '1024', '683', 'jpeg', 0, 'b5.jpeg', 47018, 'image/jpeg', '', 1750588666, 1750588666, 1750588666, 'local', '00a7692cd58454039c9d021157157df0aaddf980');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (144, '', 1, 1, '/uploads/20250622/11955a54eb5585a3bb436a69ffde7cc1.jpeg', '1024', '682', 'jpeg', 0, 'b6.jpeg', 43491, 'image/jpeg', '', 1750588738, 1750588738, 1750588738, 'local', '726b55d44cb4849a2fb9b386d3b35c30c42d579c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (145, '', 1, 1, '/uploads/20250622/d44eaddf613696fca97612b0891486e3.jpeg', '1024', '683', 'jpeg', 0, 'b7.jpeg', 42134, 'image/jpeg', '', 1750588744, 1750588744, 1750588744, 'local', '22fd41e4b444e52ddef26cdb51627cac2f43f45e');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (146, '', 1, 1, '/uploads/20250622/cd555fd12db37e3a91e97c17b13db4fa.jpeg', '1024', '683', 'jpeg', 0, 'b8.jpeg', 36505, 'image/jpeg', '', 1750588751, 1750588751, 1750588751, 'local', 'faf470970f3e64e9d439a85b6c96352db01316ba');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (147, '', 1, 1, '/uploads/20250622/cd555fd12db37e3a91e97c17b13db4fa.jpeg', '1024', '683', 'jpeg', 0, 'b8.jpeg', 36505, 'image/jpeg', '', 1750588758, 1750588758, 1750588758, 'local', 'faf470970f3e64e9d439a85b6c96352db01316ba');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (148, '', 1, 1, '/uploads/20250622/4772e65f8810a17712b564b838be7f18.jpeg', '1024', '683', 'jpeg', 0, 'b9.jpeg', 51929, 'image/jpeg', '', 1750588765, 1750588765, 1750588765, 'local', '91844ec5eba02f951f6133d02d3373ad6f36cd61');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (149, '', 1, 1, '/uploads/20250622/53b4a14293494ccf3e2f327ad39ce263.jpeg', '640', '640', 'jpeg', 0, 'teams1.jpeg', 76661, 'image/jpeg', '', 1750588904, 1750588904, 1750588904, 'local', '7406d32c6971b1fd8b8e2550c6fc288a4b8730eb');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (150, '', 1, 1, '/uploads/20250622/f5714df3144c33c96ef0fa0f53965a5f.jpeg', '640', '640', 'jpeg', 0, 'teams2.jpeg', 89171, 'image/jpeg', '', 1750588910, 1750588910, 1750588910, 'local', '3ad8d1e14db9eb4ee9374c9a793c78e593829078');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (151, '', 1, 1, '/uploads/20250622/6941ed287ece8bba00ad53273beadf45.jpeg', '640', '640', 'jpeg', 0, 'teams3.jpeg', 101645, 'image/jpeg', '', 1750588914, 1750588914, 1750588914, 'local', '9754bbeea387dc5326bde7c03b201b34036fac70');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (152, '', 1, 1, '/uploads/20250622/bce882d6ddebc9f7e91248392e891fe0.jpeg', '640', '640', 'jpeg', 0, 'teams4.jpeg', 88612, 'image/jpeg', '', 1750588918, 1750588918, 1750588918, 'local', 'f3c94f4696e55452f157173f340e2d321252398a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (153, '', 1, 1, '/uploads/20250622/d04eedf5fa381fbf8779592ad3783375.jpeg', '1920', '1280', 'jpeg', 0, 'about.jpeg', 122571, 'image/jpeg', '', 1750588988, 1750588988, 1750588988, 'local', '897ce8ef35007548ed6ca3c842af1a5ad67d57db');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (154, '', 1, 1, '/uploads/20250622/05edcfa87f12a77e3b9a8e975f2bf002.jpeg', '1680', '800', 'jpeg', 0, 'banner1.jpeg', 140675, 'image/jpeg', '', 1750589017, 1750589017, 1750589017, 'local', 'a74d79711756abdf59740aa6be5750e81f8d0218');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (155, '', 1, 1, '/uploads/20250622/0ccd327544a78cb55ef9cb29a06de378.jpeg', '1680', '800', 'jpeg', 0, 'banner2.jpeg', 299157, 'image/jpeg', '', 1750589026, 1750589026, 1750589026, 'local', 'd0fda8a3e11edf5116c34f0e20cedd4d56def65a');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (156, '', 1, 1, '/uploads/20250622/07c55184ece36d905c11e3388b38ddab.jpeg', '1680', '800', 'jpeg', 0, 'banner3.jpeg', 214326, 'image/jpeg', '', 1750589034, 1750589034, 1750589034, 'local', '47491b1c8d21b9768f7a3b4bb8e9abb681a5f566');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (157, '', 1, 1, '/uploads/20250622/2554b0ec020c9648f791057de5f4f3c2.png', '240', '60', 'png', 0, 'logo1.png', 2920, 'image/png', '', 1750589090, 1750589090, 1750589090, 'local', '9d5c9464ad1774a7dbebea3682128e8858e9e11b');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (160, '', 1, 1, '/uploads/20250622/151207d231eb9187c73bdaa5dc48719d.png', '240', '60', 'png', 0, 'logo2.png', 3018, 'image/png', '', 1750589309, 1750589309, 1750589309, 'local', '09bcde7464ea3835a41082cdae900d592ac97403');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (161, '', 1, 1, '/uploads/20250622/0dcfaacfd8502183cd16ab3aad0475a3.png', '300', '300', 'png', 0, 'logo.png', 9957, 'image/png', '', 1750591428, 1750591428, 1750591428, 'local', 'c223f3ecd6cc5ad2f2ab52091302413ee74012dd');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (162, '', 1, 1, '/uploads/20250622/f57ebce8a72b823912904fe76eda0909.png', '192', '192', 'png', 0, 'avatar.png', 15135, 'image/png', '', 1750591471, 1750591471, 1750591471, 'local', '9c39ed36543710c1ce4de7e0e56391c37ae58d56');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (163, '', 1, 1, '/uploads/20250622/4e18ec9afb89e0e89f1158a6e34ebd92.png', '400', '400', 'png', 0, 'badoucms.com.png', 7362, 'image/png', '', 1750591622, 1750591622, 1750591622, 'local', '25e82fb566348949fa09809969d6a6a7b2700d9c');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (165, '', 1, 1, '/uploads/20250622/09593ed643c8752b725307d1cea5a884.jpg', '1200', '600', 'jpg', 0, 'ebgedef959924d3813f9a24d9d45991cb4ac046571d.jpg', 333923, 'image/jpeg', '', 1750591723, 1750591723, 1750591723, 'local', 'edef959924d3813f9a24d9d45991cb4ac046571d');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (166, '', 1, 1, '/uploads/20250622/d5b47d3f753508adbb095aa1883b44b6.jpg', '800', '800', 'jpg', 0, '医疗网站缩略图(1)9da009ac88c5a129c9e579f016eb1b8bc203ad7b.jpg', 170318, 'image/jpeg', '', 1750592042, 1750592042, 1750592042, 'local', '109f758ce7f585a3e6d17c64dbea6f397e66fea2');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (167, '', 1, 1, '/uploads/20250622/7012b6e8afbaaec9eea58e161306e4fe.jpg', '800', '800', 'jpg', 0, '医疗网站缩略图(2)7b56bddaec8c7a26099bc277ff78354660c80536.jpg', 149696, 'image/jpeg', '', 1750592046, 1750592046, 1750592046, 'local', 'dca453c38e184b5eb00628e952c1a4adc9e26da1');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (168, '', 1, 1, '/uploads/20250622/dfd0c39345168c3bc085239ec741796b.jpg', '800', '800', 'jpg', 0, '医疗网站缩略图509e495580df27f55087b19ae3f99901c6e05da4.jpg', 91158, 'image/jpeg', '', 1750592051, 1750592051, 1750592051, 'local', '8c96958e254868dd6b29f66e5964342e700b7815');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (169, '', 1, 1, '/uploads/20250622/577a43d1730d8fb8aae352738e411abf.jpeg', '340', '280', 'jpeg', 0, 'b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', 37559, 'image/jpeg', '', 1750599845, 1750599845, 1750599845, 'local', 'd1dc760aca9445023eeaee0e9a142c53b38a4c14');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (170, '', 1, 1, '/uploads/20250622/577a43d1730d8fb8aae352738e411abf.jpeg', '340', '280', 'jpeg', 0, 'b3d1dc760aca9445023eeaee0e9a142c53b38a4c14.jpeg', 37559, 'image/jpeg', '', 1750600005, 1750600005, 1750600005, 'local', 'd1dc760aca9445023eeaee0e9a142c53b38a4c14');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (171, '', 1, 1, '/uploads/20250622/83f3aee5bdea54e652ef497fc45f4ab7.png', '250', '60', 'png', 0, 'logo-a1f38377ae174272d0558a18e741d5fad8b08c480.png', 4639, 'image/png', '', 1750600750, 1750600750, 1750600750, 'local', '1f38377ae174272d0558a18e741d5fad8b08c480');
INSERT INTO `bd_attachment` (`id`, `category`, `admin_id`, `user_id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filename`, `filesize`, `mimetype`, `extparam`, `create_time`, `update_time`, `upload_time`, `storage`, `sha1`) VALUES (172, '', 1, 1, '/uploads/20250622/8982f0a497e35b37b36bec84b0332d51.png', '250', '60', 'png', 0, 'logo-b834a5b93f4d5ee35f256198252216570f75fc9a0.png', 4466, 'image/png', '', 1750600754, 1750600754, 1750600754, 'local', '834a5b93f4d5ee35f256198252216570f75fc9a0');
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='系统配置';

-- ----------------------------
-- Records of bd_config
-- ----------------------------
BEGIN;
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (1, 'config_group', 'other', 'Config group', '', 'array', '[{\"key\":\"basics\",\"value\":\"Basics\"},{\"key\":\"other\",\"value\":\"\\u5176\\u4ed6\\u914d\\u7f6e\"},{\"key\":\"mail\",\"value\":\"Mail\"},{\"key\":\"user\",\"value\":\"\\u4f1a\\u5458\\u914d\\u7f6e\"}]', '', 'required', '', 1, -1);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (2, 'site_name', 'basics', 'Site Name', '', 'string', 'BADOUCMS', '', 'required', '', 1, 999);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (4, 'version', 'basics', 'Version number', '系统版本号', 'string', 'v2.0.0', '', 'required', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (5, 'time_zone', 'basics', 'time zone', '', 'string', 'Asia/Shanghai', '', 'required', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (6, 'no_access_ip', 'basics', 'No access ip', '禁止访问站点的ip列表,一行一个', 'textarea', '', '', '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (7, 'smtp_server', 'mail', 'smtp server', '', 'string', 'smtp.163.com', '', '', '', 1, 9);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (8, 'smtp_port', 'mail', 'smtp port', '', 'string', '465', '', '', '', 1, 8);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (9, 'smtp_user', 'mail', 'smtp user', '', 'string', '', '', '', '', 1, 7);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (10, 'smtp_pass', 'mail', 'smtp pass', '', 'string', '', '', '', '', 1, 6);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (11, 'smtp_verification', 'mail', 'smtp verification', '', 'select', 'SSL', '{\"SSL\":\"SSL\",\"TLS\":\"TLS\"}', '', '', 1, 5);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (12, 'smtp_sender_mail', 'mail', 'smtp sender mail', '', 'string', '', '', 'email', '', 1, 4);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (15, 'main_domain', 'basics', '网站主域名', '', 'string', '', '', '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (21, 'message_send_to', 'mail', '信息接收邮箱', '', 'string', '', '', '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (31, 'tpl_error', 'basics', '模板报错', '', 'switch', '0', '', '', '', 1, 0);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (39, 'close_site', 'basics', '网站状态', '', 'radio', '1', '[\"\\u5173\\u95ed\",\"\\u5f00\\u542f\"]', '', '', 1, 991);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (40, 'close_site_note', 'basics', '关站提示', '', 'textarea', '', '', '', '', 1, 990);
INSERT INTO `bd_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (41, 'usercenter', 'user', '会员中心', '', 'switch', '1', '', '', '', 1, 0);
COMMIT;

-- ----------------------------
-- Table structure for bd_ems
-- ----------------------------
DROP TABLE IF EXISTS `bd_ems`;
CREATE TABLE `bd_ems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '事件',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '邮箱',
  `code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '验证码',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'IP',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邮箱验证码表';

-- ----------------------------
-- Records of bd_ems
-- ----------------------------
BEGIN;
INSERT INTO `bd_ems` (`id`, `event`, `email`, `code`, `times`, `ip`, `create_time`) VALUES (1, 'resetpwd', '123@qq.com', '0673', 0, '127.0.0.1', 1750579525);
INSERT INTO `bd_ems` (`id`, `event`, `email`, `code`, `times`, `ip`, `create_time`) VALUES (20, 'forgetpass', '939134342@qq.com', '3809', 0, '127.0.0.1', 1750583640);
INSERT INTO `bd_ems` (`id`, `event`, `email`, `code`, `times`, `ip`, `create_time`) VALUES (21, 'forgetpass', '939134342@qq.com', '3028', 0, '127.0.0.1', 1750583711);
INSERT INTO `bd_ems` (`id`, `event`, `email`, `code`, `times`, `ip`, `create_time`) VALUES (22, 'forgetpass', '939134342@qq.com', '6597', 0, '127.0.0.1', 1750583774);
INSERT INTO `bd_ems` (`id`, `event`, `email`, `code`, `times`, `ip`, `create_time`) VALUES (26, 'changeemail', '131123412342@qq.cm', '6809', 0, '127.0.0.1', 1750776525);
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
COMMIT;

-- ----------------------------
-- Table structure for bd_sms
-- ----------------------------
DROP TABLE IF EXISTS `bd_sms`;
CREATE TABLE `bd_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '事件',
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '手机号',
  `code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '验证码',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'IP',
  `create_time` bigint(16) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短信验证码表';

-- ----------------------------
-- Records of bd_sms
-- ----------------------------
BEGIN;
INSERT INTO `bd_sms` (`id`, `event`, `mobile`, `code`, `times`, `ip`, `create_time`) VALUES (2, 'forgetpass', '18115681884', '5302', 0, '127.0.0.1', 1750583386);
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
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('0301f24f52f249c47bc4c92a691647545a143848', 'frontend', 2, 1750823888, 1753415888);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('2837a5406bf9c3a5104261b086ce1e47ebaaca33', 'frontend', 2, 1750513615, 1753105615);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('2cb08b43a2c8f65618fdd5f9f28ecdf367104a46', 'frontend', 2, 1750511657, 1753103657);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('340cdd3361b9a1298fcddecefd1f404cd35873da', 'frontend', 2, 1750511579, 1753103579);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('3e65fa359d2a8c2ae325e79e554eafc2c64f2391', 'frontend', 2, 1750507842, 1753099842);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('525624107182a150e5fc42b7294a79889757dab2', 'frontend', 2, 1750507762, 1753099762);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('5b05b7f5009439c279c523ad4b1a6542cc72dd65', 'frontend', 2, 1750511808, 1753103808);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('72f25dfe3c1c8fc3b53df773c2ea556807c1fc75', 'frontend', 2, 1750603503, 1753195503);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('76f6ce1629de7298b922e9ce402bc803a05d9ac3', 'frontend', 2, 1750508042, 1753100042);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('8c4d6ad1009001cb74f92c71961273789b5bcab9', 'frontend', 4, 1750516745, 1753108745);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('bd3b733fa77b28d3f2a4c82213fa5ede5c0fc7f0', 'frontend', 2, 1750507938, 1753099938);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('bdf1d07125ac87b80e77f43471650b0b905347ba', 'frontend', 2, 1750507822, 1753099822);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('c3fe37e8dd52a1d713bba179fcb21de973d138e9', 'frontend', 2, 1750507984, 1753099984);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('c959c6179272800f86e4e7a35509ccd8315ed7ef', 'frontend', 2, 1750855168, 1753447168);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('dd8a46f621a9a6a7a3161f9e2154de05d3a92587', 'frontend', 2, 1750511740, 1753103740);
INSERT INTO `bd_token` (`token`, `type`, `user_id`, `create_time`, `expire_time`) VALUES ('ddca0387eb763a0a6985144f9b59e734418f2aee', 'frontend', 2, 1750507847, 1753099847);
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
  `birthday` bigint(20) DEFAULT NULL COMMENT '生日',
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
  `create_time` bigint(20) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(50) DEFAULT '' COMMENT 'Token',
  `status` varchar(30) DEFAULT '' COMMENT '状态',
  `verification` varchar(255) DEFAULT '' COMMENT '验证',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='会员表';

-- ----------------------------
-- Records of bd_user
-- ----------------------------
BEGIN;
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `password`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `bio`, `money`, `score`, `successions`, `maxsuccessions`, `prevtime`, `logintime`, `loginip`, `loginfailure`, `loginfailuretime`, `joinip`, `jointime`, `create_time`, `update_time`, `token`, `status`, `verification`) VALUES (2, 5, 'test', 'test', '$2y$10$lRJxuCmNve0ZoQMQ3h7NrOoep6pR8h0ogkUfQt0x7MWRiYggtzLLy', '123@qq.com', '', '/uploads/20250624/0dcfaacfd8502183cd16ab3aad0475a3.png', 0, 1, 1750247036, 'aa', 10.00, 0, 2, 2, 1750823888, 1750855168, '127.0.0.1', 0, 1750582182, '', 1750247130, NULL, 1750855168, '', 'normal', '{\"email\":1,\"mobile\":0}');
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `password`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `bio`, `money`, `score`, `successions`, `maxsuccessions`, `prevtime`, `logintime`, `loginip`, `loginfailure`, `loginfailuretime`, `joinip`, `jointime`, `create_time`, `update_time`, `token`, `status`, `verification`) VALUES (5, 5, 'abc', 'abc', '$2y$10$9hObwG63RTm0b7Aw8W08GuQAD1553IHxGRmeVyhrSSeEP8Q8HgpRG', '939134342@qq.com', '13112312311', '/uploads/20250620/0dcfaacfd8502183cd16ab3aad0475a3.png', 1, 0, 1750579640, '', 0.00, 0, 2, 2, 1750582450, 1750582494, '127.0.0.1', 0, 1750582488, '127.0.0.1', 1750516859, 1750516859, 1750586863, '', 'normal', '');
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `password`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `bio`, `money`, `score`, `successions`, `maxsuccessions`, `prevtime`, `logintime`, `loginip`, `loginfailure`, `loginfailuretime`, `joinip`, `jointime`, `create_time`, `update_time`, `token`, `status`, `verification`) VALUES (6, 0, 'ab123', 'ab123', '$2y$10$fH2sY3KtRT3xUn/jQf/A/uJ219BzG8YvrAzvjgZXjppIW2xWTsimG', '1231@qq.com', '13112341234', '', 1, 0, NULL, '', 0.00, 0, 1, 1, 1750517449, 1750517449, '127.0.0.1', 0, NULL, '127.0.0.1', 1750517449, 1750517449, 1750517449, '', 'normal', '');
INSERT INTO `bd_user` (`id`, `group_id`, `username`, `nickname`, `password`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `bio`, `money`, `score`, `successions`, `maxsuccessions`, `prevtime`, `logintime`, `loginip`, `loginfailure`, `loginfailuretime`, `joinip`, `jointime`, `create_time`, `update_time`, `token`, `status`, `verification`) VALUES (7, 0, 'zssd', 'zssd', '$2y$10$yrKnnXKqK5ilmwcGpqZS1.hYNYDG79uh8CVGAjCSWUXaABPZefPOe', 'abc@qq.com', '13112341231', '', 1, 0, NULL, '', 0.00, 0, 1, 1, 1750517579, 1750517579, '127.0.0.1', 0, NULL, '127.0.0.1', 1750517579, 1750517579, 1750517579, '', 'normal', '');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_group
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_group`;
CREATE TABLE `bd_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '组名',
  `rules` text COMMENT '权限节点',
  `create_time` bigint(20) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='会员组表';

-- ----------------------------
-- Records of bd_user_group
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_group` (`id`, `name`, `rules`, `create_time`, `update_time`, `status`) VALUES (5, '普通', '2,4,11,10,9,12,1,3,7,6,5,8', 1750341203, 1750341203, 'normal');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_level
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_level`;
CREATE TABLE `bd_user_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gcode` int(11) NOT NULL COMMENT '等级ID',
  `gname` varchar(100) NOT NULL COMMENT '等级名称',
  `description` varchar(200) NOT NULL COMMENT '描述',
  `status` varchar(1) NOT NULL COMMENT '状态',
  `lscore` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分下限',
  `uscore` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分上限\n',
  `create_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_level_gcode` (`gcode`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员等级';

-- ----------------------------
-- Records of bd_user_level
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (1, 1, '初级会员', '初级会员具备基本的权限', '1', 0, 999, '2020-06-25 00:00:00', '2025-07-11 16:40:07');
INSERT INTO `bd_user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (2, 2, '中级会员', '中级会员具备部分特殊权限', '1', 1000, 9999, '2020-06-25 00:00:00', '2020-06-25 00:00:00');
INSERT INTO `bd_user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (3, 3, '高级会员', '高级会员具备全部特殊权限', '1', 10000, 4294967295, '2020-06-25 00:00:00', '2025-07-11 11:18:41');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_money_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_money_log`;
CREATE TABLE `bd_user_money_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更余额',
  `before` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更前余额',
  `after` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更后余额',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员余额变动表';

-- ----------------------------
-- Records of bd_user_money_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (1, 22, 10000.00, 0.00, 10000.00, '管理员变更金额', 1676879740);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (2, 22, -99.00, 10000.00, 9901.00, '购买付费文档:奥睿科ORICO HU3温室加湿器', 1698137423);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (3, 20, 1000.00, 0.00, 1000.00, '管理员变更金额', 1702373848);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (4, 20, -99.00, 1000.00, 901.00, '购买付费文档:test', 1702373854);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (5, 22, -9.00, 9901.00, 9892.00, '购买付费文档:奥睿科ORICO HU3温室加湿器', 1709542190);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (6, 1, 10.00, 0.00, 10.00, '管理员变更金额', 1750508578);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (7, 23, 10.00, 0.00, 10.00, '管理员变更金额', 1750508618);
INSERT INTO `bd_user_money_log` (`id`, `user_id`, `money`, `before`, `after`, `memo`, `createtime`) VALUES (8, 23, 5.00, 10.00, 15.00, '管理员变更金额', 1750508625);
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
  `create_time` bigint(20) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(11) DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='会员规则表';

-- ----------------------------
-- Records of bd_user_rule
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (1, 0, 'index', 'Frontend', '', 1, 1491635035, 1491635035, 1, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (2, 0, 'api', 'API Interface', '', 1, 1491635035, 1491635035, 2, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (3, 1, 'user', 'User Module', '', 1, 1491635035, 1491635035, 12, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (4, 2, 'user', 'User Module', '', 1, 1491635035, 1491635035, 11, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (5, 3, 'index/user/login', 'Login', '', 0, 1491635035, 1491635035, 5, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (6, 3, 'index/user/register', 'Register', '', 0, 1491635035, 1491635035, 7, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (7, 3, 'index/user/index', 'User Center', '', 0, 1491635035, 1491635035, 9, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (8, 3, 'index/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 4, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (9, 4, 'api/user/login', 'Login', '', 0, 1491635035, 1491635035, 6, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (10, 4, 'api/user/register', 'Register', '', 0, 1491635035, 1491635035, 8, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (11, 4, 'api/user/index', 'User Center', '', 0, 1491635035, 1491635035, 10, 'normal');
INSERT INTO `bd_user_rule` (`id`, `pid`, `name`, `title`, `remark`, `ismenu`, `create_time`, `update_time`, `weigh`, `status`) VALUES (12, 4, 'api/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 3, 'normal');
COMMIT;

-- ----------------------------
-- Table structure for bd_user_score_log
-- ----------------------------
DROP TABLE IF EXISTS `bd_user_score_log`;
CREATE TABLE `bd_user_score_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '变更积分',
  `before` int(10) NOT NULL DEFAULT '0' COMMENT '变更前积分',
  `after` int(10) NOT NULL DEFAULT '0' COMMENT '变更后积分',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员积分变动表';

-- ----------------------------
-- Records of bd_user_score_log
-- ----------------------------
BEGIN;
INSERT INTO `bd_user_score_log` (`id`, `user_id`, `score`, `before`, `after`, `memo`, `createtime`) VALUES (1, 22, 2, 0, 2, '发布文章', 1660656938);
INSERT INTO `bd_user_score_log` (`id`, `user_id`, `score`, `before`, `after`, `memo`, `createtime`) VALUES (2, 1, -2, 0, -2, '删除文章', 1660979054);
INSERT INTO `bd_user_score_log` (`id`, `user_id`, `score`, `before`, `after`, `memo`, `createtime`) VALUES (3, 20, 2, 0, 2, '发布文章', 1702119102);
INSERT INTO `bd_user_score_log` (`id`, `user_id`, `score`, `before`, `after`, `memo`, `createtime`) VALUES (4, 1, 4, -2, 2, '管理员变更积分', 1718269059);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
