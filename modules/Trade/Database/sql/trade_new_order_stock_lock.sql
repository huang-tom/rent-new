-- 订单库存占用（购买 / 租赁共用）
-- 已建过订单表时执行本文件；新建库若已用最新 trade_new_order.sql / trade_new_rent_order.sql，只需建占用表

CREATE TABLE IF NOT EXISTS `trade_new_order_stock_lock` (
  `lock_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '占用ID',
  `order_type` tinyint NOT NULL DEFAULT 1 COMMENT '订单类型:1购买;2租赁',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `order_number` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商品ID',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '占用数量',
  `lock_time` datetime DEFAULT NULL COMMENT '占用时间',
  `handle_status` tinyint NOT NULL DEFAULT 0 COMMENT '处理状态:0未处理;1已处理',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  `handle_remark` varchar(200) NOT NULL DEFAULT '' COMMENT '处理说明',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`lock_id`),
  KEY `idx_handle_lock_time` (`handle_status`, `lock_time`),
  KEY `idx_order` (`order_type`, `order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新订单库存占用记录';

-- 已有订单表补字段（列已存在时跳过对应 ALTER）
ALTER TABLE `trade_new_order` ADD COLUMN `stock_locked` tinyint NOT NULL DEFAULT 0 COMMENT '库存是否锁定:0未锁定;1已锁定' AFTER `assign_warehouse_time`;
ALTER TABLE `trade_new_order` MODIFY `order_status` tinyint NOT NULL DEFAULT 0 COMMENT '状态:0待支付;1已支付;2已发货;3已完成;4已退款;5已关闭';
ALTER TABLE `trade_new_rent_order` ADD COLUMN `stock_locked` tinyint NOT NULL DEFAULT 0 COMMENT '库存是否锁定:0未锁定;1已锁定' AFTER `buyout_time`;
ALTER TABLE `trade_new_rent_order` MODIFY `order_status` tinyint NOT NULL DEFAULT 0 COMMENT '状态:0待支付;1已支付;2已发货;3租赁中;4已归还;5已买断;6已退款;7已关闭';
