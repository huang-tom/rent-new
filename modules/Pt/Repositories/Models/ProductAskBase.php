<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductAskBase.
 *
 * 商品咨询（问答）主表 pt_product_ask_base
 *
 * [新增 2026-09-23]
 *   厂商只随包下发了后台页面 `views/pt/productAskBase/index.vue` + 4 条权限行
 *   （menu_id 4287/4289/4290/4291，且 4 个角色都已授权），但**整个后端缺失**：
 *   没有模型、没有 Service、没有 Controller、没有路由，接口全部 404。
 *   这里补齐。
 *
 * ⚠️ 该表所有业务列都是 NOT NULL 且无默认值（ask_type_id / product_id /
 *    store_id / user_id / user_nickname / ask_question / ask_answer /
 *    ask_answer_user_id / ask_answer_user_nickname），在
 *    STRICT_TRANS_TABLES 下漏字段会直接 SQL 报错，不会静默写 ''。
 *    所以写入必须在 Service 里把每个字段都补齐（见 ProductAskBaseService）。
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductAskBase extends Model
{
    protected $table = 'pt_product_ask_base';
    protected $primaryKey = 'ask_id';
    public $timestamps = false;

    protected $guarded = ['ask_id'];

    /** 是否回复：未回复 */
    const STATUS_UNANSWERED = 0;
    /** 是否回复：已回复 */
    const STATUS_ANSWERED = 1;

    /** 未回答时 ask_answer_time 的「空值」字面量（该列 NOT NULL 且默认就是这个值） */
    const TIME_EMPTY = '0000-00-00 00:00:00';
}
