-- 新租赁订单（与旧 Trade / 新购买订单隔离）
-- 用户绑定登录后的 account_user_base.user_id，不另建用户表
-- 时间字段 datetime；执行前请确认库名

-- ---------- 租赁订单主表 ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order` (
  `order_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_number` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号RO-',
  `order_source` varchar(50) NOT NULL DEFAULT '' COMMENT '下单来源渠道',
  `order_store_name` varchar(100) NOT NULL DEFAULT '' COMMENT '下单门店名称',
  `fulfill_warehouse_code` varchar(50) NOT NULL DEFAULT '' COMMENT '履约分仓编码',
  `fulfill_warehouse_name` varchar(100) NOT NULL DEFAULT '' COMMENT '履约仓库名称',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '登录用户ID(account_user_base)',
  `order_status` tinyint NOT NULL DEFAULT 0 COMMENT '状态:0待支付;1已支付;2已发货;3租赁中;4已归还;5已买断;6已退款;7已关闭',
  `delivery_type` varchar(50) NOT NULL DEFAULT '' COMMENT '配送方式',
  `express_no` varchar(50) NOT NULL DEFAULT '' COMMENT '快递单号',
  `buyer_message` varchar(500) NOT NULL DEFAULT '' COMMENT '买家留言',
  `service_remark` varchar(1000) NOT NULL DEFAULT '' COMMENT '客服备注',
  `expect_delivery_time` datetime DEFAULT NULL COMMENT '期望送达时间',
  `expect_return_time` datetime DEFAULT NULL COMMENT '期望归还时间',
  `rent_days` int NOT NULL DEFAULT 0 COMMENT '租期天数(汇总)',
  `daily_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '日租金(汇总展示)',
  `product_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '租金金额(商品金额)',
  `freight_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '运费',
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '押金(冻结)',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠合计(占位)',
  `payable_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '应付金额',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `points_num` int NOT NULL DEFAULT 0 COMMENT '积分数量(占位，不计算)',
  `points_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '积分抵扣金额(占位)',
  `pay_method` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式文案',
  `equipment_original_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '设备原价(买断参考)',
  `buyout_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '买断价',
  `buyout_payable` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '买断应付',
  `order_time` datetime DEFAULT NULL COMMENT '下单时间',
  `pay_time` datetime DEFAULT NULL COMMENT '付款时间',
  `assign_warehouse_time` datetime DEFAULT NULL COMMENT '分仓时间',
  `ship_time` datetime DEFAULT NULL COMMENT '发货时间',
  `renting_time` datetime DEFAULT NULL COMMENT '进入租赁中时间',
  `return_time` datetime DEFAULT NULL COMMENT '归还结算时间',
  `buyout_time` datetime DEFAULT NULL COMMENT '买断时间',
  `stock_locked` tinyint NOT NULL DEFAULT 0 COMMENT '库存是否锁定:0未锁定;1已锁定',
  `is_deleted` tinyint NOT NULL DEFAULT 0 COMMENT '是否删除:0否;1是',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uk_order_number` (`order_number`),
  KEY `idx_deleted_status_time` (`is_deleted`, `order_status`, `order_time`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_source` (`order_source`),
  KEY `idx_rent_days` (`rent_days`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单主表';

-- ---------- 商品明细（租赁） ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_item` (
  `item_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商品ID',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `product_number` varchar(50) NOT NULL DEFAULT '' COMMENT '商品编号',
  `product_image` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图',
  `spec_name` varchar(100) NOT NULL DEFAULT '' COMMENT '规格',
  `daily_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '日租金',
  `rent_days` int NOT NULL DEFAULT 0 COMMENT '租期天数',
  `rent_start_date` date DEFAULT NULL COMMENT '起租日',
  `rent_end_date` date DEFAULT NULL COMMENT '到期日',
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '押金',
  `rent_subtotal` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '租金小计',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额(占位)',
  `quantity` int NOT NULL DEFAULT 1 COMMENT '数量',
  `locked_stock_qty` int NOT NULL DEFAULT 0 COMMENT '锁定库存数',
  `device_asset_no` varchar(100) NOT NULL DEFAULT '' COMMENT '设备资产编号',
  `serial_number` varchar(100) NOT NULL DEFAULT '' COMMENT '序列号',
  `equipment_original_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '设备原价',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`item_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单商品明细';

-- ---------- 收货地址（绑订单） ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_address` (
  `address_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '地址ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `consignee` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人',
  `consignee_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '收货电话',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '市',
  `district` varchar(50) NOT NULL DEFAULT '' COMMENT '区',
  `address_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `address_full` varchar(500) NOT NULL DEFAULT '' COMMENT '完整地址文案',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`address_id`),
  UNIQUE KEY `uk_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单收货地址';

-- ---------- 订单标签（人工输入，仅绑当前订单） ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_tag` (
  `tag_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `tag_name` varchar(50) NOT NULL DEFAULT '' COMMENT '标签名(人工输入)',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`tag_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单标签';

-- ---------- 退款记录 ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_refund` (
  `refund_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '退款ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `refund_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '退款金额',
  `refund_reason` varchar(500) NOT NULL DEFAULT '' COMMENT '退款原因',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`refund_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单退款记录';

-- ---------- 操作日志 ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_log` (
  `log_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `log_content` varchar(500) NOT NULL DEFAULT '' COMMENT '日志内容',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`log_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单操作日志';

-- ---------- 退租/续租/买断记录 ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_op` (
  `op_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `op_type` tinyint NOT NULL DEFAULT 0 COMMENT '类型:1退租;2续租;3买断',
  `op_date` date DEFAULT NULL COMMENT '业务日期(退租日等)',
  `days` int NOT NULL DEFAULT 0 COMMENT '天数(使用/续租)',
  `rent_refund` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '退还租金',
  `deposit_refund` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '退还/解冻押金',
  `renew_rent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '续租租金',
  `buyout_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '买断价',
  `deposit_offset` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '押金抵扣',
  `payable_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '本次应付/应退',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`op_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_op_type` (`op_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单退租续租买断记录';

-- ---------- 报修记录 ----------
CREATE TABLE IF NOT EXISTS `trade_new_rent_order_repair` (
  `repair_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '报修ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `fault_type` varchar(100) NOT NULL DEFAULT '' COMMENT '故障类型',
  `fault_desc` varchar(1000) NOT NULL DEFAULT '' COMMENT '故障描述',
  `handle_method` varchar(100) NOT NULL DEFAULT '' COMMENT '处理方式',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`repair_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新租赁订单报修记录';
