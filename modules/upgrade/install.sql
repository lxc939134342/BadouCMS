-- MySQL 5.7～8.4：新建表使用统一字符集和排序规则。
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for __PREFIX__user_level
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__user_level` (
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员等级';

-- ----------------------------
-- Records of __PREFIX__user_level
-- ----------------------------
BEGIN;
INSERT IGNORE INTO `__PREFIX__user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (1, 1, '初级会员', '初级会员具备基本的权限', '1', 0, 999, '2020-06-25 00:00:00', '2025-07-11 16:40:07');
INSERT IGNORE INTO `__PREFIX__user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (2, 2, '中级会员', '中级会员具备部分特殊权限', '1', 1000, 9999, '2020-06-25 00:00:00', '2020-06-25 00:00:00');
INSERT IGNORE INTO `__PREFIX__user_level` (`id`, `gcode`, `gname`, `description`, `status`, `lscore`, `uscore`, `create_time`, `update_time`) VALUES (3, 3, '高级会员', '高级会员具备全部特殊权限', '1', 10000, 4294967295, '2020-06-25 00:00:00', '2025-07-11 11:18:41');
COMMIT;


-- 2.0.6--

INSERT IGNORE INTO `__PREFIX__config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (39, 'close_site', 'basics', '网站状态', '', 'radio', '1', '[\"\\u5173\\u95ed\",\"\\u5f00\\u542f\"]', '', '', 1, 991);
INSERT IGNORE INTO `__PREFIX__config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (40, 'close_site_note', 'basics', '关站提示', '', 'textarea', '', '', '', '', 1, 990);

-- 2.0.7--

INSERT IGNORE INTO `__PREFIX__config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `allow_del`, `weigh`) VALUES (41, 'usercenter', 'user', '会员中心', '', 'switch', '1', '', '', '', 1, 0);


-- 2.2.6--

SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__admin_rule' AND COLUMN_NAME = 'is_quick'),
  'DO 0',
  'ALTER TABLE `__PREFIX__admin_rule` ADD COLUMN `is_quick` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否快捷菜单'' AFTER `ismenu`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
