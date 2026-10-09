<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChainCode.
 *
 * [新增 2026-09-22] 取货码 / 虚拟核销码表模型。
 *
 * 背景：订单列表页的「取货码」下拉搜索会调 GET /manage/trade/chainCode/list
 *      （admin/src/views/trade/orderBase/index.vue:646 findRemoteCodeList），
 *      但后端从来没有 ChainCode 控制器/路由 → 输入取货码就是一次 404。
 *      表 trade_chain_code 是存在的（order_id 为主键，varchar），之前只是没人写接口。
 *
 * @package Modules\Trade\Repositories\Models
 */
class ChainCode extends Model
{

    protected $table = 'trade_chain_code';
    protected $primaryKey = 'order_id';
    // 主键是 varchar(50)，不是自增整型 —— 必须显式声明，否则 Eloquent 会按 int 处理
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

}
