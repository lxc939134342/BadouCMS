CREATE TABLE IF NOT EXISTS `__PREFIX__agent_chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(120) NOT NULL DEFAULT '',
  `messages` longtext NOT NULL,
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_owner_updated` (`tenant_id`,`admin_id`,`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台 AI 助手会话';

CREATE TABLE IF NOT EXISTS `__PREFIX__agent_patch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0',
  `file` varchar(255) NOT NULL DEFAULT '',
  `old_string` longtext,
  `new_string` longtext,
  `diff` longtext,
  `backup` longtext,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `mode` varchar(10) NOT NULL DEFAULT 'manual',
  `review_reason` varchar(500) NOT NULL DEFAULT '',
  `create_time` bigint(20) DEFAULT NULL,
  `update_time` bigint(20) DEFAULT NULL,
  `apply_time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat` (`chat_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台 AI 助手代码改动补丁';

CREATE TABLE IF NOT EXISTS `__PREFIX__agent_workflow` (
  `partition` varchar(510) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `key` varchar(510) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `value` longtext CHARACTER SET ascii NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`partition`, `key`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `__PREFIX__agent_message` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` varbinary(255) NOT NULL,
  `message_id` varbinary(64) NOT NULL,
  `role` varchar(32) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta` longtext DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_thread_message` (`thread_id`, `message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `__PREFIX__agent_task` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` varchar(50) NOT NULL COMMENT '任务类型：cms.translate, order.export',
  `module` varchar(30) NOT NULL COMMENT '所属模块：cms, order, user',
  `tenant_id` bigint NOT NULL COMMENT '租户ID',
  `admin_id` bigint NOT NULL COMMENT '管理员ID',
  `chat_id` bigint NOT NULL COMMENT '对话ID',
  `request_key` char(64) NOT NULL COMMENT '防重复提交',
  `status` varchar(20) NOT NULL COMMENT '状态：queued, running, completed, failed, cancelled',
  `priority` tinyint NOT NULL DEFAULT 0 COMMENT '优先级：0-9，数字越大越优先',
  `payload` longtext NOT NULL COMMENT '任务参数 JSON',
  `progress` text NOT NULL COMMENT '进度 JSON: {processed, total, saved, skipped}',
  `result` text NOT NULL COMMENT '结果 JSON',
  `error` varchar(500) NOT NULL DEFAULT '' COMMENT '错误信息',
  `started_at` bigint NOT NULL DEFAULT 0 COMMENT '开始时间',
  `created_at` bigint NOT NULL COMMENT '创建时间',
  `updated_at` bigint NOT NULL COMMENT '更新时间',
  `heartbeat_at` bigint NOT NULL DEFAULT 0 COMMENT 'Worker 心跳时间',
  KEY `idx_queue` (`status`, `priority` DESC, `id`),
  KEY `idx_owner` (`tenant_id`, `admin_id`, `chat_id`),
  KEY `idx_type` (`module`, `type`),
  KEY `idx_heartbeat` (`status`, `heartbeat_at`),
  UNIQUE KEY `uk_request` (`request_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Agent 通用后台任务队列';
