<?php

namespace Modules\Shop\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Shop\Repositories\Criteria\UserFavoritesItemCriteria;
use Modules\Shop\Services\UserFavoritesItemService;

class FavoritesItemController extends BaseController
{
    private $userFavoritesItemService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserFavoritesItemService $userFavoritesItemService)
    {
        $this->userFavoritesItemService = $userFavoritesItemService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $user_id = checkLoginUserId();
        $request['user_id'] = $user_id;
        $data = $this->userFavoritesItemService->list($request, new UserFavoritesItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $user_id = checkLoginUserId();
        $row = [
            'user_id' => $user_id,   //用户ID
            'item_id' => $request->input('item_id', 0), //商品SKU编号
            'favorites_item_time' => getTime(),
        ];
        $data = $this->userFavoritesItemService->add($row);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_id = checkLoginUserId();
        $item_id = $request->input('item_id', 0);
        if ($request->has('favorites_item_id')) {
            $data = $this->userFavoritesItemService->remove($request);
        } else {
            $data = $this->userFavoritesItemService->removeByItemId($user_id, $item_id);
        }

        return Respond::success($data);
    }
}
