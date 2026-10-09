<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Services\NewProductPromoService;

class NewProductPromoController extends BaseController
{
    private $newProductPromoService;

    public function __construct(NewProductPromoService $newProductPromoService)
    {
        $this->newProductPromoService = $newProductPromoService;
    }

    /**
     * 按 type(推广分类ID) 读取推广商品
     * 返回：product_id / product_number / product_image（另附 id、sort 便于管理）
     */
    public function list(Request $request)
    {
        $type = (int)$request->get('type', 0);
        $data = $this->newProductPromoService->listByType($type);

        return Respond::success($data);
    }

    /**
     * 绑定商品
     * type + product_ids(数组或逗号串) + sort(可选)
     */
    public function add(Request $request)
    {
        $type = (int)$request->input('type', 0);
        $product_ids = $request->input('product_ids', []);
        if (is_string($product_ids)) {
            $product_ids = array_filter(explode(',', $product_ids));
        }
        $sort = (int)$request->input('sort', 50);
        $count = $this->newProductPromoService->addProducts($type, $product_ids, $sort);

        return Respond::success($count, '成功绑定' . $count . '个商品');
    }

    /**
     * 软删除绑定
     */
    public function remove(Request $request)
    {
        $id = (int)$request->get('id', -1);
        $data = $this->newProductPromoService->removePromo($id);

        return Respond::success($data);
    }

    /**
     * 修改排序
     */
    public function editSort(Request $request)
    {
        $id = (int)$request->get('id', -1);
        $sort = (int)$request->input('sort', 50);
        $data = $this->newProductPromoService->editSort($id, $sort);

        return Respond::success($data);
    }
}
