-- 新商品主表 / 租期档位 / 商品租期定价
-- 执行前请确认库名；与旧商品主链 pt_product_* 隔离
-- 时间字段统一为 datetime，库内可直接查看，无需时间戳转换

CREATE TABLE IF NOT EXISTS `pt_new_product` (
  `product_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `product_number` varchar(50) NOT NULL DEFAULT '' COMMENT '商品编号',
  `category_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分类ID，复用 pt_product_category',
  `warehouse_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库/门店ID，逗号分隔（先存字段）',
  `product_image` varchar(255) NOT NULL DEFAULT '' COMMENT '商品主图',
  `product_intro` text COMMENT '商品简介',
  `sale_mode` tinyint NOT NULL DEFAULT 1 COMMENT '销售方式:1仅购买;2仅租赁;3购买+租赁',
  `sale_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '销售价',
  `market_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '划线价',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '计量单位',
  `deposit` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '押金',
  `daily_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '日租金（商品基础日租）',
  `cycle_type` int NOT NULL DEFAULT 0 COMMENT '周期类型',
  `settle_policy` int NOT NULL DEFAULT 0 COMMENT '到期结算策略',
  `need_tier_price` tinyint NOT NULL DEFAULT 0 COMMENT '是否逐档配置:1是;0否',
  `stock_total` int NOT NULL DEFAULT 0 COMMENT '总库存（仅维护，本期不接业务）',
  `stock_warning` int NOT NULL DEFAULT 0 COMMENT '库存预警阈值（仅维护）',
  `sale_num` int NOT NULL DEFAULT 0 COMMENT '销量（仅维护）',
  `spec_json` text COMMENT '规格JSON，展示用，不关联库存',
  `product_state` tinyint NOT NULL DEFAULT 0 COMMENT '上下架:1上架;0下架',
  `audit_status` tinyint NOT NULL DEFAULT 0 COMMENT '审核状态:0待审核;1已通过',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `product_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `product_update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`product_id`),
  -- 列表默认：is_deleted=0 ORDER BY product_id DESC
  KEY `idx_deleted_id` (`is_deleted`, `product_id`),
  -- 分类筛选
  KEY `idx_deleted_category` (`is_deleted`, `category_id`, `product_id`),
  -- 销售方式 / 统计可购买可租赁
  KEY `idx_deleted_sale_mode` (`is_deleted`, `sale_mode`, `product_id`),
  -- 状态筛选 / 待审核 / 在售统计
  KEY `idx_deleted_audit_state` (`is_deleted`, `audit_status`, `product_state`, `sale_mode`),
  -- 编号精确/前缀查询（模糊两侧%无法走索引）
  KEY `idx_product_number` (`product_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新商品主表';

CREATE TABLE IF NOT EXISTS `pt_new_product_rent_period` (
  `period_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '档位ID',
  `period_name` varchar(50) NOT NULL DEFAULT '' COMMENT '档位名称',
  `period_days` int unsigned NOT NULL DEFAULT 0 COMMENT '天数',
  `period_enable` tinyint NOT NULL DEFAULT 1 COMMENT '是否启用:1启用;0禁用',
  `period_order` int NOT NULL DEFAULT 50 COMMENT '排序，越小越靠前',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`period_id`),
  KEY `idx_deleted_enable_order` (`is_deleted`, `period_enable`, `period_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品租期档位（全局）';

CREATE TABLE IF NOT EXISTS `pt_new_product_rent_price` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商品ID',
  `period_id` int unsigned NOT NULL DEFAULT 0 COMMENT '档位ID',
  `total_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '合计租金',
  `daily_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '日租金（折合）',
  PRIMARY KEY (`id`),
  -- 按商品查/覆盖写入；删档位前校验引用
  UNIQUE KEY `uk_product_period` (`product_id`, `period_id`),
  KEY `idx_period_id` (`period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新商品租期定价';

-- 若已按旧版建表，可执行以下增量（按需）：
-- ALTER TABLE `pt_new_product` ADD COLUMN `product_number` varchar(50) NOT NULL DEFAULT '' COMMENT '商品编号' AFTER `product_name`;
-- ALTER TABLE `pt_new_product` ADD COLUMN `sale_num` int NOT NULL DEFAULT 0 COMMENT '销量（仅维护）' AFTER `stock_warning`;
-- ALTER TABLE `pt_new_product` ADD COLUMN `audit_status` tinyint NOT NULL DEFAULT 0 COMMENT '审核状态:0待审核;1已通过' AFTER `product_state`;
-- ALTER TABLE `pt_new_product` MODIFY `product_state` tinyint NOT NULL DEFAULT 0 COMMENT '上下架:1上架;0下架';
-- ALTER TABLE `pt_new_product` DROP INDEX `idx_category_id`, DROP INDEX `idx_sale_mode`, DROP INDEX `idx_product_state`, DROP INDEX `idx_audit_status`, DROP INDEX `idx_is_deleted`;
-- ALTER TABLE `pt_new_product` ADD KEY `idx_deleted_id` (`is_deleted`, `product_id`);
-- ALTER TABLE `pt_new_product` ADD KEY `idx_deleted_category` (`is_deleted`, `category_id`, `product_id`);
-- ALTER TABLE `pt_new_product` ADD KEY `idx_deleted_sale_mode` (`is_deleted`, `sale_mode`, `product_id`);
-- ALTER TABLE `pt_new_product` ADD KEY `idx_deleted_audit_state` (`is_deleted`, `audit_status`, `product_state`, `sale_mode`);
-- ALTER TABLE `pt_new_product` ADD KEY `idx_product_number` (`product_number`);
-- ALTER TABLE `pt_new_product_rent_period` ADD COLUMN `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是' AFTER `period_order`;
-- ALTER TABLE `pt_new_product_rent_period` ADD KEY `idx_deleted_enable_order` (`is_deleted`, `period_enable`, `period_order`);
-- 时间字段改为 datetime（已建表时执行）：
-- ALTER TABLE `pt_new_product` MODIFY `product_add_time` datetime DEFAULT NULL COMMENT '添加时间', MODIFY `product_update_time` datetime DEFAULT NULL COMMENT '更新时间';
-- ALTER TABLE `pt_new_product_rent_period` MODIFY `add_time` datetime DEFAULT NULL COMMENT '添加时间', MODIFY `update_time` datetime DEFAULT NULL COMMENT '更新时间';
-- ALTER TABLE `pt_new_product_promo_cate` MODIFY `add_time` datetime DEFAULT NULL COMMENT '添加时间', MODIFY `update_time` datetime DEFAULT NULL COMMENT '更新时间';
-- ALTER TABLE `pt_new_product_promo` MODIFY `add_time` datetime DEFAULT NULL COMMENT '添加时间', MODIFY `update_time` datetime DEFAULT NULL COMMENT '更新时间';
-- ALTER TABLE `pt_new_product_comment` MODIFY `comment_time` datetime DEFAULT NULL COMMENT '评论时间（可指定）', MODIFY `add_time` datetime DEFAULT NULL COMMENT '创建时间', MODIFY `update_time` datetime DEFAULT NULL COMMENT '更新时间';
-- ALTER TABLE `pt_new_product_comment_reply` MODIFY `reply_time` datetime DEFAULT NULL COMMENT '回复时间（可指定）', MODIFY `add_time` datetime DEFAULT NULL COMMENT '创建时间', MODIFY `update_time` datetime DEFAULT NULL COMMENT '更新时间';

-- ---------- 推广分类 / 商品推广 ----------
CREATE TABLE IF NOT EXISTS `pt_new_product_promo_cate` (
  `cate_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '推广分类ID',
  `cate_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `cate_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `cate_type` tinyint NOT NULL DEFAULT 2 COMMENT '类型:1默认菜单;2分类菜单',
  `category_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联商品分类ID，分类菜单可用',
  `is_system` tinyint NOT NULL DEFAULT 0 COMMENT '系统内置:1是不可删;0否',
  `cate_enable` tinyint NOT NULL DEFAULT 1 COMMENT '是否启用:1启用;0禁用',
  `cate_sort` int NOT NULL DEFAULT 50 COMMENT '排序，越小越靠前',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`cate_id`),
  KEY `idx_deleted_sort` (`is_deleted`, `cate_sort`, `cate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品推广分类/菜单';

CREATE TABLE IF NOT EXISTS `pt_new_product_promo` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` int unsigned NOT NULL DEFAULT 0 COMMENT '推广分类ID(cate_id)',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商品ID',
  `sort` int NOT NULL DEFAULT 50 COMMENT '排序，越小越靠前',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type_sort` (`type`, `is_deleted`, `sort`, `id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品推广绑定';

-- 默认菜单示例（可按需执行）
-- INSERT INTO `pt_new_product_promo_cate` (`cate_name`,`cate_desc`,`cate_type`,`is_system`,`cate_enable`,`cate_sort`,`add_time`,`update_time`)
-- VALUES ('推荐','按运营策略排序展示',1,1,1,10,NOW(),NOW()),
--        ('猜你喜欢','按用户喜好排序展示',1,1,1,20,NOW(),NOW());

-- ---------- 新商品评论 ----------
CREATE TABLE IF NOT EXISTS `pt_new_product_comment` (
  `comment_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联商品ID',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称（后台手工录入）',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '客户姓名（后台录入）',
  `comment_scores` tinyint NOT NULL DEFAULT 5 COMMENT '评分1-5',
  `comment_content` varchar(1000) NOT NULL DEFAULT '' COMMENT '评论内容',
  `comment_image` varchar(1000) NOT NULL DEFAULT '' COMMENT '评论图片，逗号分隔',
  `audit_status` tinyint NOT NULL DEFAULT 0 COMMENT '审核:0待审核;1通过;2驳回',
  `comment_time` datetime DEFAULT NULL COMMENT '评论时间（可指定）',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`comment_id`),
  KEY `idx_deleted_status_time` (`is_deleted`, `audit_status`, `comment_time`),
  KEY `idx_scores` (`comment_scores`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新商品评论主表';

CREATE TABLE IF NOT EXISTS `pt_new_product_comment_reply` (
  `reply_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '回复ID',
  `comment_id` int unsigned NOT NULL DEFAULT 0 COMMENT '主评论ID',
  `reply_content` varchar(1000) NOT NULL DEFAULT '' COMMENT '回复内容',
  `reply_time` datetime DEFAULT NULL COMMENT '回复时间（可指定）',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`reply_id`),
  KEY `idx_comment_id` (`comment_id`, `is_deleted`, `reply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新商品评论回复';
