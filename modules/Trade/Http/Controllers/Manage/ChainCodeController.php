<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Repositories\Models\ChainCode;

/**
 * 取货码（虚拟核销码）
 *
 * [新增 2026-09-22] 补齐 GET /manage/trade/chainCode/list。
 * 前端订单列表的取货码搜索一直在调它（views/trade/orderBase/index.vue:646），
 * 但后端从未实现 → 输入取货码就 404。
 */
class ChainCodeController extends BaseController
{

    /**
     * 列表（按取货码模糊查询）
     *
     * 前端契约：getCodeList({ chain_code }) → 读 data.items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $size = (int)$request->input('size', 20);
        $size = ($size > 0 && $size <= 200) ? $size : 20;

        $query = ChainCode::query();

        if ($chain_code = $request->input('chain_code')) {
            // 取货码是人工输入的一串字符，用 LIKE 更符合"边输边搜"的用法
            $query->where('chain_code', 'like', '%' . $chain_code . '%');
        }
        if ($request->filled('order_id')) {
            $query->where('order_id', (string)$request->input('order_id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', (int)$request->input('user_id'));
        }

        $records = (clone $query)->count();
        $items = $query->orderBy('chain_code_usetime', 'DESC')
            ->forPage($page, $size)
            ->get()
            ->toArray();

        return Respond::success([
            'items'   => $items,
            'records' => $records,
            'total'   => $records,
            'page'    => $page,
            'size'    => $size,
        ]);
    }

}
