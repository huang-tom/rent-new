<?php

namespace Modules\Shop\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Shop\Services\UserProductBrowseService;

class ProductBrowseController extends BaseController
{
    private $userProductBrowseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserProductBrowseService $userProductBrowseService)
    {
        $this->userProductBrowseService = $userProductBrowseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $user_id = checkLoginUserId();
        $request['user_id'] = $user_id;
        $data = $this->userProductBrowseService->getList($request);

        return Respond::success($data);
    }


    public function removeBrowser(Request $request)
    {
        $user_id = checkLoginUserId();
        $item_id = $request->input('item_id', 0);
        $data = $this->userProductBrowseService->removeBrowser($item_id, $user_id);

        return Respond::success($data);
    }

}
